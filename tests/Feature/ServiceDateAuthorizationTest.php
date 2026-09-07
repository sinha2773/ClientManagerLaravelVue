<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Domain;
use App\Models\HostingService;
use App\Models\SslCertificate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceDateAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_manager_cannot_change_service_dates_even_with_a_crafted_request(): void
    {
        [$domain, $hosting, $ssl] = $this->createServices();
        $manager = User::factory()->create([
            'user_type' => 'account_manager',
            'is_active' => true,
        ]);

        $this->actingAs($manager)
            ->put(route('domains.update', $domain), $this->domainPayload($domain, [
                'registration_date' => '2030-01-01',
                'expiry_date' => '2031-01-01',
            ]))
            ->assertRedirect(route('domains.index'));

        $this->actingAs($manager)
            ->put(route('hosting-services.update', $hosting), $this->hostingPayload($hosting, [
                'start_date' => '2030-02-01',
                'renewal_date' => '2031-02-01',
            ]))
            ->assertRedirect(route('hosting-services.index'));

        $this->actingAs($manager)
            ->put(route('ssl-certificates.update', $ssl), $this->sslPayload($ssl, [
                'issue_date' => '2030-03-01',
                'expiry_date' => '2031-03-01',
            ]))
            ->assertRedirect(route('ssl-certificates.index'));

        $this->assertSame('2025-01-01', $domain->fresh()->registration_date->toDateString());
        $this->assertSame('2026-01-01', $domain->fresh()->expiry_date->toDateString());
        $this->assertSame('2025-02-01', $hosting->fresh()->start_date->toDateString());
        $this->assertSame('2026-02-01', $hosting->fresh()->renewal_date->toDateString());
        $this->assertSame('2025-03-01', $ssl->fresh()->issue_date->toDateString());
        $this->assertSame('2026-03-01', $ssl->fresh()->expiry_date->toDateString());
    }

    public function test_active_approver_can_change_renewal_but_not_original_start_dates(): void
    {
        [$domain, $hosting, $ssl] = $this->createServices();
        $approver = User::factory()->create([
            'user_type' => 'approver',
            'is_active' => true,
        ]);

        $this->actingAs($approver)->put(route('domains.update', $domain), $this->domainPayload($domain, [
            'registration_date' => '2030-01-01',
            'expiry_date' => '2031-01-01',
        ]));
        $this->actingAs($approver)->put(route('hosting-services.update', $hosting), $this->hostingPayload($hosting, [
            'start_date' => '2030-02-01',
            'renewal_date' => '2031-02-01',
        ]));
        $this->actingAs($approver)->put(route('ssl-certificates.update', $ssl), $this->sslPayload($ssl, [
            'issue_date' => '2030-03-01',
            'expiry_date' => '2031-03-01',
        ]));

        $this->assertSame('2025-01-01', $domain->fresh()->registration_date->toDateString());
        $this->assertSame('2031-01-01', $domain->fresh()->expiry_date->toDateString());
        $this->assertSame('2025-02-01', $hosting->fresh()->start_date->toDateString());
        $this->assertSame('2031-02-01', $hosting->fresh()->renewal_date->toDateString());
        $this->assertSame('2025-03-01', $ssl->fresh()->issue_date->toDateString());
        $this->assertSame('2031-03-01', $ssl->fresh()->expiry_date->toDateString());
    }

    public function test_account_manager_service_creation_forces_one_year_renewal_dates(): void
    {
        $manager = User::factory()->create([
            'user_type' => 'account_manager',
            'is_active' => true,
        ]);
        $client = Client::create([
            'name' => 'Creation Date Institute',
            'email' => 'creation-dates@example.edu',
            'status' => 'active',
            'client_type' => 'private',
            'eims_monthly' => 10,
        ]);

        $this->actingAs($manager)->post(route('domains.store'), [
            'client_id' => $client->id,
            'name' => 'created.example.com',
            'registrar' => 'Registrar',
            'registration_date' => '2024-02-29',
            'expiry_date' => '2099-12-31',
            'auto_renew' => false,
            'status' => 'active',
            'price' => 1000,
            'payment_status' => 'unpaid',
        ])->assertRedirect(route('domains.index'));
        $domain = Domain::where('name', 'created.example.com')->sole();

        $this->actingAs($manager)->post(route('hosting-services.store'), [
            'domain_id' => $domain->id,
            'provider' => 'Host',
            'package_name' => 'Annual',
            'start_date' => '2024-02-29',
            'renewal_date' => '2099-12-31',
            'price' => 2000,
            'payment_status' => 'unpaid',
            'status' => 'active',
            'username' => 'hosting-user',
            'password' => 'secret',
        ])->assertRedirect(route('hosting-services.index'));

        $this->actingAs($manager)->post(route('ssl-certificates.store'), [
            'domain_id' => $domain->id,
            'provider' => 'Certificate Provider',
            'type' => 'DV',
            'issue_date' => '2024-02-29',
            'expiry_date' => '2099-12-31',
            'price' => 500,
            'payment_status' => 'unpaid',
            'status' => 'active',
            'auto_renew' => false,
        ])->assertRedirect(route('ssl-certificates.index'));

        $this->assertSame('2025-02-28', $domain->expiry_date->toDateString());
        $this->assertSame('2025-02-28', HostingService::sole()->renewal_date->toDateString());
        $this->assertSame('2025-02-28', SslCertificate::sole()->expiry_date->toDateString());
    }

    private function createServices(): array
    {
        $client = Client::create([
            'name' => 'Service Date Institute',
            'email' => 'dates@example.edu',
            'status' => 'active',
            'client_type' => 'private',
            'eims_monthly' => 10,
        ]);
        $domain = Domain::create([
            'client_id' => $client->id,
            'name' => 'dates.example.com',
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

        return [$domain, $hosting, $ssl];
    }

    private function domainPayload(Domain $domain, array $overrides = []): array
    {
        return [
            'client_id' => $domain->client_id,
            'name' => $domain->name,
            'registrar' => $domain->registrar,
            'auto_renew' => false,
            'status' => $domain->status,
            'price' => $domain->price,
            'payment_status' => $domain->payment_status,
            ...$overrides,
        ];
    }

    private function hostingPayload(HostingService $hosting, array $overrides = []): array
    {
        return [
            'domain_id' => $hosting->domain_id,
            'provider' => $hosting->provider,
            'package_name' => $hosting->package_name,
            'status' => $hosting->status,
            'price' => $hosting->price,
            'payment_status' => $hosting->payment_status,
            'server_ip' => null,
            'control_panel_url' => null,
            'username' => $hosting->username,
            'password' => $hosting->password,
            ...$overrides,
        ];
    }

    private function sslPayload(SslCertificate $ssl, array $overrides = []): array
    {
        return [
            'domain_id' => $ssl->domain_id,
            'provider' => $ssl->provider,
            'type' => $ssl->type,
            'status' => $ssl->status,
            'price' => $ssl->price,
            'payment_status' => $ssl->payment_status,
            'auto_renew' => false,
            ...$overrides,
        ];
    }
}
