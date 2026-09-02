<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ActivityLogger
{
    private const IGNORED_FIELDS = ['created_at', 'updated_at', 'deleted_at'];

    public function logModelEvent(string $event, Model $model): void
    {
        if ($model instanceof ActivityLog || ! Auth::check()) {
            return;
        }

        $changes = $this->changesFor($event, $model);
        if ($event === 'updated' && $changes === []) {
            return;
        }

        $action = $event === 'updated' && $this->isApproval($model)
            ? 'approved'
            : $event;

        $this->log(
            action: $action,
            actor: Auth::user(),
            subject: $model,
            metadata: ['changes' => $changes],
        );
    }

    public function log(
        string $action,
        User $actor,
        ?Model $subject = null,
        array $metadata = [],
        ?string $description = null,
    ): ActivityLog {
        $subjectName = $subject ? class_basename($subject) : null;
        $subjectLabel = $subject ? $this->subjectLabel($subject) : null;
        $requestMetadata = $this->requestMetadata();

        return ActivityLog::create([
            'user_id' => User::query()->whereKey($actor->getKey())->exists() ? $actor->getKey() : null,
            'actor_name' => $actor->name,
            'actor_email' => $actor->email,
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'subject_label' => $subjectLabel,
            'description' => $description ?? $this->description($action, $subjectName, $subjectLabel),
            'ip_address' => $requestMetadata['ip_address'],
            'user_agent' => $requestMetadata['user_agent'],
            'metadata' => array_filter([
                ...$metadata,
                'route' => $requestMetadata['route'],
                'method' => $requestMetadata['method'],
            ], static fn ($value) => $value !== null && $value !== []),
            'created_at' => now(),
        ]);
    }

    private function changesFor(string $event, Model $model): array
    {
        $attributes = $event === 'updated' ? $model->getChanges() : $model->getAttributes();
        $changes = [];

        foreach ($attributes as $field => $newValue) {
            if (in_array($field, self::IGNORED_FIELDS, true)) {
                continue;
            }

            if ($this->isSensitive($field)) {
                $changes[$field] = ['old' => '[redacted]', 'new' => '[redacted]'];

                continue;
            }

            $changes[$field] = [
                'old' => $event === 'updated' ? $this->normalize($model->getRawOriginal($field)) : null,
                'new' => $event === 'deleted' ? null : $this->normalize($newValue),
            ];
        }

        return $changes;
    }

    private function isApproval(Model $model): bool
    {
        $changes = $model->getChanges();

        if (array_key_exists('approved_by', $changes) || array_key_exists('approved_at', $changes)) {
            return true;
        }

        foreach (['payment_approved_level1', 'payment_approved_level2'] as $field) {
            if (($changes[$field] ?? false) === true || ($changes[$field] ?? null) === 1) {
                return true;
            }
        }

        return false;
    }

    private function subjectLabel(Model $subject): string
    {
        foreach (['name', 'bill_number', 'package_name', 'type', 'email'] as $attribute) {
            $value = $subject->getAttribute($attribute);
            if (filled($value)) {
                return Str::limit((string) $value, 255, '…');
            }
        }

        return class_basename($subject).' #'.$subject->getKey();
    }

    private function description(string $action, ?string $subjectName, ?string $subjectLabel): string
    {
        $verb = match ($action) {
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'approved' => 'Approved',
            'logged_in' => 'Signed in',
            'logged_out' => 'Signed out',
            default => Str::headline($action),
        };

        return trim($verb.' '.collect([$subjectName, $subjectLabel])->filter()->implode(' — '));
    }

    private function requestMetadata(): array
    {
        if (! app()->bound('request')) {
            return ['ip_address' => null, 'user_agent' => null, 'route' => null, 'method' => null];
        }

        $request = request();

        return [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'route' => $request->route()?->getName(),
            'method' => $request->method(),
        ];
    }

    private function isSensitive(string $field): bool
    {
        return Str::contains(Str::lower($field), ['password', 'token', 'secret']);
    }

    private function normalize(mixed $value): mixed
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $value = $decoded;
            } else {
                return Str::limit($value, 500, '…');
            }
        }

        if (is_array($value)) {
            return collect($value)
                ->take(20)
                ->map(fn ($item) => is_scalar($item) || $item === null ? $item : '[complex value]')
                ->all();
        }

        return $value;
    }
}
