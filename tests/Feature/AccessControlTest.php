<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Domain;
use App\Models\HostingService;
use App\Models\SslCertificate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_deactivated_mid_session_is_logged_out(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user)->get(route('dashboard'))->assertOk();

        $user->update(['is_active' => false]);

        $this->actingAs($user)->get(route('clients.index'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_only_approvers_can_delete_clients_and_services(): void
    {
        [$client, $domain, $hosting, $ssl] = $this->createServices();
        $manager = User::factory()->create(['user_type' => 'account_manager', 'is_active' => true]);
        $approver = User::factory()->create(['user_type' => 'approver', 'is_active' => true]);

        $routes = [
            route('ssl-certificates.destroy', $ssl),
            route('hosting-services.destroy', $hosting),
            route('domains.destroy', $domain),
            route('clients.destroy', $client),
        ];

        foreach ($routes as $url) {
            $this->actingAs($manager)->delete($url)->assertForbidden();
        }
        $this->assertDatabaseCount('clients', 1);
        $this->assertDatabaseCount('domains', 1);
        $this->assertDatabaseCount('hosting_services', 1);
        $this->assertDatabaseCount('ssl_certificates', 1);

        foreach ($routes as $url) {
            $this->actingAs($approver)->delete($url)->assertRedirect();
        }
        $this->assertDatabaseCount('clients', 0);
        $this->assertDatabaseCount('domains', 0);
        $this->assertDatabaseCount('hosting_services', 0);
        $this->assertDatabaseCount('ssl_certificates', 0);
    }

    public function test_student_summary_uses_any_configured_api_token(): void
    {
        [$client, $domain] = $this->createServices();
        $manager = User::factory()->create(['user_type' => 'account_manager', 'is_active' => true]);
        config(['services.client_management.api_token' => 'real-shared-token']);
        Http::fake([
            '*' => Http::response([
                'code' => 200,
                'status' => 1,
                'data' => ['year' => '2026', 'grand_totals' => ['regular' => 120], 'summary' => []],
            ]),
        ]);

        $this->actingAs($manager)->postJson(route('bills.fetch-student-summary'), [
            'client_id' => $client->id,
            'domain_id' => $domain->id,
            'year' => '2026',
        ])->assertOk()->assertJsonPath('grand_totals.regular', 120);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'api_token=real-shared-token'));
    }

    private function createServices(): array
    {
        $client = Client::create([
            'name' => 'Access Institute',
            'email' => 'access@example.edu',
            'status' => 'active',
            'client_type' => 'private',
            'eims_monthly' => 10,
        ]);
        $domain = Domain::create([
            'client_id' => $client->id,
            'name' => 'access.example.com',
            'registrar' => 'Registrar',
            'registration_date' => '2025-01-01',
            'expiry_date' => '2026-01-01',
            'auto_renew' => false,
            'status' => 'active',
            'price' => 1000,
            'payment_status' => 'unpaid',
        ]);
        $hosting = HostingService::create([
            'client_id' => $client->id,
            'domain_id' => $domain->id,
            'provider' => 'Host',
            'package_name' => 'Annual',
            'start_date' => '2025-02-01',
            'renewal_date' => '2026-02-01',
            'price' => 2000,
            'payment_status' => 'unpaid',
            'status' => 'active',
            'username' => 'hosting-user',
            'password' => 'secret',
        ]);
        $ssl = SslCertificate::create([
            'client_id' => $client->id,
            'domain_id' => $domain->id,
            'provider' => 'Certificate Provider',
            'type' => 'DV',
            'issue_date' => '2025-03-01',
            'expiry_date' => '2026-03-01',
            'price' => 500,
            'payment_status' => 'unpaid',
            'status' => 'active',
            'auto_renew' => false,
        ]);

        return [$client, $domain, $hosting, $ssl];
    }
}
