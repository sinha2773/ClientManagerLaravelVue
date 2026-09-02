<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Bill;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_model_actions_are_logged_with_the_actor(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $client = $this->createClient();
        $client->update(['name' => 'Updated Institute']);
        $client->delete();

        foreach (['created', 'updated', 'deleted'] as $action) {
            $this->assertDatabaseHas('activity_logs', [
                'user_id' => $user->id,
                'action' => $action,
                'subject_type' => Client::class,
                'subject_id' => $client->id,
            ]);
        }

        $deletedLog = ActivityLog::query()->where('action', 'deleted')->sole();
        $this->assertSame('Updated Institute', $deletedLog->subject_label);
    }

    public function test_approval_updates_are_classified_as_approved(): void
    {
        $approver = $this->createUser(['user_type' => 'approver']);
        $client = $this->createClient();
        $this->actingAs($approver);

        $bill = Bill::create([
            'client_id' => $client->id,
            'service_type' => 'domain',
            'service_id' => 1,
            'description' => 'Domain renewal',
            'academic_year' => '2026',
            'amount' => 2500,
            'due_date' => now()->addMonth()->toDateString(),
            'created_by' => $approver->id,
        ]);

        $bill->update([
            'status' => 'sent',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $approver->id,
            'action' => 'approved',
            'subject_type' => Bill::class,
            'subject_id' => $bill->id,
        ]);
    }

    public function test_regular_users_only_see_their_logs_and_approvers_see_all_logs(): void
    {
        $firstUser = $this->createUser();
        $secondUser = $this->createUser(['email' => 'second@example.com']);
        $approver = $this->createUser(['email' => 'approver@example.com', 'user_type' => 'approver']);

        $this->createLog($firstUser, 'First user activity');
        $this->createLog($secondUser, 'Second user activity');

        $this->actingAs($firstUser)
            ->get(route('activity-logs.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('ActivityLogs/Index')
                ->where('canViewAll', false)
                ->has('activityLogs.data', 1)
                ->where('activityLogs.data.0.user_id', $firstUser->id)
                ->has('users', 0)
            );

        $this->actingAs($approver)
            ->get(route('activity-logs.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('ActivityLogs/Index')
                ->where('canViewAll', true)
                ->has('activityLogs.data', 2)
                ->has('users', 3)
            );
    }

    public function test_login_and_logout_are_logged(): void
    {
        $user = $this->createUser();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard', absolute: false));
        $this->post('/logout')->assertRedirect('/');

        $this->assertDatabaseHas('activity_logs', ['user_id' => $user->id, 'action' => 'logged_in']);
        $this->assertDatabaseHas('activity_logs', ['user_id' => $user->id, 'action' => 'logged_out']);
    }

    public function test_sensitive_values_are_redacted(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $user->update(['password' => 'new-password']);

        $log = ActivityLog::query()->where('action', 'updated')->sole();

        $this->assertSame('[redacted]', $log->metadata['changes']['password']['old']);
        $this->assertSame('[redacted]', $log->metadata['changes']['password']['new']);
    }

    private function createUser(array $attributes = []): User
    {
        return User::factory()->create([
            'user_type' => 'account_manager',
            'is_active' => true,
            ...$attributes,
        ]);
    }

    private function createClient(): Client
    {
        return Client::create([
            'name' => 'Example Institute',
            'email' => fake()->unique()->safeEmail(),
            'status' => 'active',
            'client_type' => 'private',
        ]);
    }

    private function createLog(User $user, string $description): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => $user->id,
            'actor_name' => $user->name,
            'actor_email' => $user->email,
            'action' => 'updated',
            'description' => $description,
            'created_at' => now(),
        ]);
    }
}
