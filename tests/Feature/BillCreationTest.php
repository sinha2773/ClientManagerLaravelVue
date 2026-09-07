<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Client;
use App\Models\Domain;
use App\Models\HostingService;
use App\Models\SslCertificate;
use App\Models\User;
use App\Support\AcademicYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BillCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_page_prefills_client_and_fee_type(): void
    {
        $user = $this->createAccountManager();
        $client = $this->createClient();

        $response = $this->actingAs($user)->get(route('bills.create', [
            'client_id' => $client->id,
            'service_type' => 'domain',
        ]));

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Bills/Create')
            ->where('prefill.client_id', (string) $client->id)
            ->where('prefill.service_type', 'domain')
            ->where('academicYears', AcademicYear::options())
        );
    }

    public function test_bill_creation_persists_academic_year(): void
    {
        $user = $this->createAccountManager();
        $client = $this->createClient();
        $domain = Domain::create([
            'client_id' => $client->id,
            'name' => 'school.example.com',
            'registrar' => 'Example Registrar',
            'registration_date' => now()->subYear()->toDateString(),
            'expiry_date' => now()->addYear()->toDateString(),
            'auto_renew' => false,
            'status' => 'active',
            'price' => 2500,
            'payment_status' => 'unpaid',
        ]);

        $response = $this->actingAs($user)->post(route('bills.store'), [
            'client_id' => $client->id,
            'service_type' => 'domain',
            'service_id' => $domain->id,
            'service_started_date' => '1999-01-01',
            'service_renewal_date' => '2099-12-31',
            'description' => 'Domain renewal',
            'academic_year' => '2026',
            'amount' => 2500,
            'due_date' => now()->addMonth()->toDateString(),
            'notes' => null,
        ]);

        $bill = Bill::sole();

        $response->assertRedirect(route('bills.show', $bill));
        $this->assertSame('2026', $bill->academic_year);
        $this->assertSame($client->id, $bill->client_id);
        $this->assertSame('domain', $bill->service_type);
        $this->assertSame($domain->expiry_date->toDateString(), $bill->service_started_date->toDateString());
        $this->assertSame(
            $domain->expiry_date->copy()->addYearNoOverflow()->toDateString(),
            $bill->service_renewal_date->toDateString(),
        );
    }

    public function test_approver_can_set_a_custom_bill_renewal_date(): void
    {
        $approver = User::factory()->create([
            'user_type' => 'approver',
            'is_active' => true,
        ]);
        $client = $this->createClient();
        $domain = Domain::create([
            'client_id' => $client->id,
            'name' => 'custom-renewal.example.com',
            'registrar' => 'Example Registrar',
            'registration_date' => '2025-01-01',
            'expiry_date' => '2026-01-01',
            'auto_renew' => false,
            'status' => 'active',
            'price' => 2500,
            'payment_status' => 'unpaid',
        ]);

        $this->actingAs($approver)->post(route('bills.store'), [
            'client_id' => $client->id,
            'service_type' => 'domain',
            'service_id' => $domain->id,
            'service_started_date' => '1999-01-01',
            'service_renewal_date' => '2027-09-15',
            'description' => 'Custom renewal period',
            'academic_year' => '2026',
            'amount' => 2500,
            'due_date' => now()->addMonth()->toDateString(),
        ])->assertRedirect();

        $bill = Bill::sole();
        $this->assertSame('2026-01-01', $bill->service_started_date->toDateString());
        $this->assertSame('2027-09-15', $bill->service_renewal_date->toDateString());
    }

    public function test_account_manager_cannot_override_an_existing_bill_renewal_date(): void
    {
        $manager = $this->createAccountManager();
        $client = $this->createClient();
        $domain = Domain::create([
            'client_id' => $client->id,
            'name' => 'protected-renewal.example.com',
            'registrar' => 'Example Registrar',
            'registration_date' => '2025-01-01',
            'expiry_date' => '2026-01-01',
            'auto_renew' => false,
            'status' => 'active',
            'price' => 2500,
            'payment_status' => 'unpaid',
        ]);
        $bill = $this->createBill($manager, $client, [
            'service_id' => $domain->id,
            'service_started_date' => '2026-01-01',
            'service_renewal_date' => '2027-09-15',
        ]);

        $this->actingAs($manager)->patch(route('bills.update', $bill), [
            'client_id' => $client->id,
            'service_type' => 'domain',
            'service_id' => $domain->id,
            'service_started_date' => '1999-01-01',
            'service_renewal_date' => '2099-12-31',
            'description' => 'Updated description',
            'academic_year' => '2026',
            'amount' => 2500,
            'due_date' => now()->addMonth()->toDateString(),
        ])->assertRedirect(route('bills.show', $bill));

        $bill->refresh();
        $this->assertSame('2026-01-01', $bill->service_started_date->toDateString());
        $this->assertSame('2027-09-15', $bill->service_renewal_date->toDateString());
        $this->assertSame('Updated description', $bill->description);
    }

    public function test_paid_and_approved_bill_renews_service_once_using_bill_renewal_date(): void
    {
        $manager = $this->createAccountManager();
        $approver = User::factory()->create([
            'user_type' => 'approver',
            'is_active' => true,
        ]);
        $client = $this->createClient();
        $domain = Domain::create([
            'client_id' => $client->id,
            'name' => 'renew.example.com',
            'registrar' => 'Example Registrar',
            'registration_date' => '2025-06-30',
            'expiry_date' => '2026-06-30',
            'auto_renew' => false,
            'status' => 'active',
            'price' => 2500,
            'payment_status' => 'unpaid',
        ]);
        $bill = $this->createBill($manager, $client, [
            'service_id' => $domain->id,
            'service_started_date' => '2026-06-30',
            'service_renewal_date' => '2027-09-15',
        ]);

        $response = $this->actingAs($approver)->patch(route('bills.approve', $bill));

        $response->assertRedirect();
        $domain->refresh();
        $bill->refresh();
        $this->assertSame('2027-09-15', $domain->expiry_date->toDateString());
        $this->assertSame('2027-09-15', $domain->last_billing_date->toDateString());
        $this->assertSame('paid', $domain->payment_status);
        $this->assertNotNull($bill->service_renewed_at);

        $renewedAt = $bill->service_renewed_at->toISOString();
        $bill->renewService();

        $this->assertSame('2027-09-15', $domain->fresh()->expiry_date->toDateString());
        $this->assertSame($renewedAt, $bill->fresh()->service_renewed_at->toISOString());
    }

    public function test_approving_a_stale_bill_never_moves_service_renewal_backwards(): void
    {
        $manager = $this->createAccountManager();
        $approver = User::factory()->create([
            'user_type' => 'approver',
            'is_active' => true,
        ]);
        $client = $this->createClient();
        $domain = Domain::create([
            'client_id' => $client->id,
            'name' => 'monotonic.example.com',
            'registrar' => 'Example Registrar',
            'registration_date' => '2025-01-01',
            'expiry_date' => '2026-01-01',
            'auto_renew' => false,
            'status' => 'active',
            'price' => 2500,
            'payment_status' => 'unpaid',
        ]);
        $staleBill = $this->createBill($manager, $client, [
            'service_id' => $domain->id,
            'service_started_date' => '2026-01-01',
            'service_renewal_date' => '2027-01-01',
        ]);
        $domain->update([
            'expiry_date' => '2028-01-01',
            'last_billing_date' => '2028-01-01',
        ]);

        $this->actingAs($approver)->patch(route('bills.approve', $staleBill))->assertRedirect();

        $domain->refresh();
        $this->assertSame('2028-01-01', $domain->expiry_date->toDateString());
        $this->assertSame('2028-01-01', $domain->last_billing_date->toDateString());
        $this->assertNotNull($staleBill->fresh()->service_renewed_at);
    }

    public function test_payment_before_approval_does_not_renew_service(): void
    {
        $manager = $this->createAccountManager();
        $client = $this->createClient();
        $domain = Domain::create([
            'client_id' => $client->id,
            'name' => 'pending.example.com',
            'registrar' => 'Example Registrar',
            'registration_date' => '2025-06-30',
            'expiry_date' => '2026-06-30',
            'auto_renew' => false,
            'status' => 'active',
            'price' => 2500,
            'payment_status' => 'unpaid',
        ]);
        $bill = $this->createBill($manager, $client, [
            'service_id' => $domain->id,
            'service_started_date' => '2026-06-30',
            'service_renewal_date' => '2027-06-30',
        ]);

        $this->actingAs($manager)->patch(route('bills.update-payment', $bill), [
            'paid_amount' => 2500,
        ])->assertRedirect();

        $this->assertSame('2026-06-30', $domain->fresh()->expiry_date->toDateString());
        $this->assertNull($bill->fresh()->service_renewed_at);
    }

    public function test_approval_updates_hosting_and_ssl_renewal_dates(): void
    {
        $manager = $this->createAccountManager();
        $approver = User::factory()->create([
            'user_type' => 'approver',
            'is_active' => true,
        ]);
        $client = $this->createClient();
        $domain = Domain::create([
            'client_id' => $client->id,
            'name' => 'services.example.com',
            'registrar' => 'Example Registrar',
            'registration_date' => '2025-01-01',
            'expiry_date' => '2026-01-01',
            'auto_renew' => false,
            'status' => 'active',
            'price' => 2500,
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
        $hostingBill = $this->createBill($manager, $client, [
            'service_type' => 'hosting',
            'service_id' => $hosting->id,
            'service_started_date' => '2026-02-01',
            'service_renewal_date' => '2027-04-01',
        ]);
        $sslBill = $this->createBill($manager, $client, [
            'service_type' => 'ssl_certificate',
            'service_id' => $ssl->id,
            'service_started_date' => '2026-03-01',
            'service_renewal_date' => '2027-05-01',
        ]);

        $this->actingAs($approver)->patch(route('bills.approve', $hostingBill))->assertRedirect();
        $this->actingAs($approver)->patch(route('bills.approve', $sslBill))->assertRedirect();

        $this->assertSame('2027-04-01', $hosting->fresh()->renewal_date->toDateString());
        $this->assertSame('2027-04-01', $hosting->fresh()->last_billing_date->toDateString());
        $this->assertSame('2027-05-01', $ssl->fresh()->expiry_date->toDateString());
        $this->assertSame('2027-05-01', $ssl->fresh()->last_billing_date->toDateString());
    }

    public function test_academic_year_is_required_when_creating_a_bill(): void
    {
        $user = $this->createAccountManager();
        $client = $this->createClient();

        $response = $this->actingAs($user)
            ->from(route('bills.create'))
            ->post(route('bills.store'), [
                'client_id' => $client->id,
                'service_type' => 'domain',
                'description' => 'Domain renewal',
                'amount' => 2500,
                'due_date' => now()->addMonth()->toDateString(),
            ]);

        $response->assertRedirect(route('bills.create'));
        $response->assertSessionHasErrors('academic_year');
        $this->assertDatabaseCount('bills', 0);
    }

    public function test_bill_index_filters_by_academic_year(): void
    {
        $user = $this->createAccountManager();
        $client = $this->createClient();
        $matchingBill = $this->createBill($user, $client, ['academic_year' => '2025']);
        $this->createBill($user, $client, ['academic_year' => '2026']);

        $response = $this->actingAs($user)->get(route('bills.index', ['academic_year' => '2025']));

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Bills/Index')
            ->where('filters.academic_year', '2025')
            ->has('bills.data', 1)
            ->where('bills.data.0.id', $matchingBill->id)
        );
    }

    public function test_client_billing_history_filters_by_academic_year_and_type(): void
    {
        $user = $this->createAccountManager();
        $client = $this->createClient();
        $matchingBill = $this->createBill($user, $client, [
            'academic_year' => '2025',
            'service_type' => 'hosting',
        ]);
        $this->createBill($user, $client, [
            'academic_year' => '2025',
            'service_type' => 'domain',
        ]);
        $this->createBill($user, $client, [
            'academic_year' => '2026',
            'service_type' => 'hosting',
        ]);

        $response = $this->actingAs($user)->get(route('clients.show', [
            'client' => $client,
            'academic_year' => '2025',
            'billing_type' => 'hosting',
        ]));

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Clients/Show')
            ->where('billingFilters.academic_year', '2025')
            ->where('billingFilters.billing_type', 'hosting')
            ->has('billingHistory.data', 1)
            ->where('billingHistory.data.0.id', $matchingBill->id)
            ->where('academicYears', AcademicYear::options())
        );
    }

    private function createClient(): Client
    {
        return Client::create([
            'name' => 'Example Institute',
            'email' => 'accounts@example.edu',
            'status' => 'active',
            'client_type' => 'private',
            'eims_monthly' => 10,
        ]);
    }

    private function createAccountManager(): User
    {
        return User::factory()->create([
            'user_type' => 'account_manager',
            'is_active' => true,
        ]);
    }

    private function createBill(User $user, Client $client, array $attributes = []): Bill
    {
        return Bill::create([
            'client_id' => $client->id,
            'service_type' => 'domain',
            'service_id' => 1,
            'description' => 'Service renewal',
            'academic_year' => '2026',
            'amount' => 2500,
            'due_date' => now()->addMonth()->toDateString(),
            'created_by' => $user->id,
            ...$attributes,
        ]);
    }
}
