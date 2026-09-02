<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    private const ACTIONS = ['created', 'updated', 'deleted', 'approved', 'logged_in', 'logged_out'];

    public function index(Request $request): Response
    {
        $user = $request->user();
        $canViewAll = $user->isApprover();
        $baseQuery = ActivityLog::query()
            ->when(! $canViewAll, fn (Builder $query) => $query->where('user_id', $user->id));

        $query = (clone $baseQuery)->with('user:id,name,email');

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();
            $query->where(function (Builder $query) use ($search): void {
                $query->where('description', 'like', "%{$search}%")
                    ->orWhere('actor_name', 'like', "%{$search}%")
                    ->orWhere('actor_email', 'like', "%{$search}%")
                    ->orWhere('subject_label', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if (in_array($request->input('action'), self::ACTIONS, true)) {
            $query->where('action', $request->input('action'));
        }

        if ($canViewAll && $request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->date('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }

        if ($request->date('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }

        return Inertia::render('ActivityLogs/Index', [
            'activityLogs' => $query->latest('id')->paginate(20)->withQueryString(),
            'filters' => $request->only(['search', 'action', 'user_id', 'date_from', 'date_to']),
            'canViewAll' => $canViewAll,
            'users' => $canViewAll
                ? User::query()->select('id', 'name', 'email')->orderBy('name')->get()
                : [],
            'stats' => [
                'total' => (clone $baseQuery)->count(),
                'today' => (clone $baseQuery)->whereDate('created_at', today())->count(),
                'approvals' => (clone $baseQuery)->where('action', 'approved')->count(),
                'deletions' => (clone $baseQuery)->where('action', 'deleted')->count(),
            ],
        ]);
    }
}
