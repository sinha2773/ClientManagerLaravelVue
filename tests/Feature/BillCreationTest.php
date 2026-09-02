<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Client;
use App\Models\Domain;
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
