<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Client;
use App\Models\Domain;
use App\Models\HostingService;
use App\Models\SslCertificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class BillController extends Controller
{
    /**
     * Display a listing of the bills
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (! $user->canManageBills()) {
            abort(403, 'Unauthorized to access bills.');
        }

        $query = Bill::with(['client', 'creator', 'approver']);

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by service type
        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        // Search by bill number or client name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bill_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($clientQuery) use ($search) {
                        $clientQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $bills = $query->orderBy('created_at', 'desc')->paginate(15);

        return Inertia::render('Bills/Index', [
            'bills' => $bills,
            'filters' => $request->only(['payment_status', 'status', 'service_type', 'search']),
            'canApprove' => $user->canApproveBills(),
        ]);
    }

    /**
     * Show the form for creating a new bill
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        if (! $user->canManageBills()) {
            abort(403, 'Unauthorized to create bills.');
        }

        $clients = Client::select('id', 'name', 'email', 'eims_monthly')->get();
        $domains = Domain::with('client:id,name,eims_monthly')->select('id', 'name', 'client_id', 'price')->get();
        $hostingServices = HostingService::with(['client', 'domain'])->select('id', 'package_name', 'client_id', 'domain_id', 'price')->get();
        $sslCertificates = SslCertificate::with(['client', 'domain'])->select('id', 'type', 'provider', 'client_id', 'domain_id', 'price')->get();

        return Inertia::render('Bills/Create', [
            'clients' => $clients,
            'domains' => $domains,
            'hostingServices' => $hostingServices,
            'sslCertificates' => $sslCertificates,
            'prefill' => [
                'client_id' => $request->query('client_id'),
                'service_type' => $request->query('service_type'),
                'service_id' => $request->query('service_id'),
            ],
        ]);
    }

    /**
     * Store a newly created bill in storage
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (! $user->canManageBills()) {
            abort(403, 'Unauthorized to create bills.');
        }

        $validated = $this->validateBillData($request);

        // Validate that the service exists and belongs to the client (only for non-EIMS Fee)
        if ($validated['service_type'] !== 'eims_fee') {
            $this->validateService($validated['service_type'], $validated['service_id'], $validated['client_id']);
        }

        $bill = Bill::create([
            ...$validated,
            'created_by' => $user->id,
        ]);

        return redirect()->route('bills.show', $bill)->with('success', 'Bill created successfully.');
    }

    /**
     * Display the specified bill
     */
    public function show(Bill $bill)
    {
        $user = Auth::user();

        if (! $user->canManageBills()) {
            abort(403, 'Unauthorized to view bills.');
        }

        $bill->load(['client', 'creator', 'approver']);

        // Get the related service details
        $service = null;
        if ($bill->service_type === 'eims_fee' && $bill->service_id) {
            $service = Domain::find($bill->service_id);
        } elseif ($bill->service_type !== 'eims_fee') {
            switch ($bill->service_type) {
                case 'domain':
                    $service = Domain::find($bill->service_id);
                    break;
                case 'hosting':
                    $service = HostingService::with('domain')->find($bill->service_id);
                    break;
                case 'ssl_certificate':
                    $service = SslCertificate::with('domain')->find($bill->service_id);
                    break;
            }
        }

        return Inertia::render('Bills/Show', [
            'bill' => $bill,
            'service' => $service,
            'canApprove' => $user->canApproveBills(),
            'canEdit' => $bill->status === 'draft',
        ]);
    }

    /**
     * Show the form for editing the specified bill
     */
    public function edit(Bill $bill)
    {
        $user = Auth::user();

        if (! $user->canManageBills() || $bill->status !== 'draft') {
            abort(403, 'Unauthorized to edit this bill.');
        }

        $clients = Client::select('id', 'name', 'email', 'eims_monthly')->get();
        $domains = Domain::with('client:id,name,eims_monthly')->select('id', 'name', 'client_id', 'price')->get();
        $hostingServices = HostingService::with(['client', 'domain'])->select('id', 'package_name', 'client_id', 'domain_id', 'price')->get();
        $sslCertificates = SslCertificate::with(['client', 'domain'])->select('id', 'type', 'provider', 'client_id', 'domain_id', 'price')->get();

        return Inertia::render('Bills/Edit', [
            'bill' => $bill,
            'clients' => $clients,
            'domains' => $domains,
            'hostingServices' => $hostingServices,
            'sslCertificates' => $sslCertificates,
        ]);
    }

    /**
     * Update the specified bill in storage
     */
    public function update(Request $request, Bill $bill)
    {
        $user = Auth::user();

        if (! $user->canManageBills() || $bill->status !== 'draft') {
            abort(403, 'Unauthorized to edit this bill.');
        }

        $validated = $this->validateBillData($request, $bill);

        // Validate that the service exists and belongs to the client (only for non-EIMS Fee)
        if ($validated['service_type'] !== 'eims_fee') {
            $this->validateService($validated['service_type'], $validated['service_id'], $validated['client_id']);
        }

        $bill->update($validated);

        return redirect()->route('bills.show', $bill)->with('success', 'Bill updated successfully.');
    }

    /**
     * Remove the specified bill from storage
     */
    public function destroy(Bill $bill)
    {
        $user = Auth::user();

        if (! $user->canManageBills() || $bill->status !== 'draft') {
            abort(403, 'Unauthorized to delete this bill.');
        }

        $bill->delete();

        return redirect()->route('bills.index')->with('success', 'Bill deleted successfully.');
    }

    /**
     * Approve a bill
     */
    public function approve(Request $request, Bill $bill)
    {
        $user = Auth::user();

        if (! $user->canApproveBills()) {
            abort(403, 'Unauthorized to approve bills.');
        }

        if ($bill->status !== 'draft') {
            return back()->with('error', 'Only draft bills can be approved.');
        }

        $bill->update([
            'status' => 'sent',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'paid_amount' => $bill->amount,
            'payment_status' => 'paid',
            'paid_date' => now(),
        ]);

        $bill->renewService();

        return back()->with('success', 'Bill approved and marked as paid successfully.');
    }

    /**
     * Update payment status
     */
    public function updatePayment(Request $request, Bill $bill)
    {
        $user = Auth::user();

        if (! $user->canManageBills()) {
            abort(403, 'Unauthorized to update payment status.');
        }

        $validated = $request->validate([
            'paid_amount' => 'required|numeric|min:0|max:'.$bill->amount,
            'notes' => 'nullable|string|max:1000',
        ]);

        $bill->update([
            'paid_amount' => $validated['paid_amount'],
            'notes' => $validated['notes'] ?? $bill->notes,
        ]);

        $bill->updatePaymentStatus();

        return back()->with('success', 'Payment status updated successfully.');
    }

    public function fetchStudentSummary(Request $request)
    {
        $user = Auth::user();

        if (! $user->canManageBills()) {
            abort(403, 'Unauthorized to fetch student summaries.');
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'domain_id' => 'required|exists:domains,id',
            'year' => 'required|digits:4',
        ]);

        $domain = Domain::where('id', $validated['domain_id'])
            ->where('client_id', $validated['client_id'])
            ->firstOrFail();

        $baseUrl = $this->buildStudentManagementBaseUrl($domain->name);
        $apiToken = config('services.client_management.api_token');

        if (blank($apiToken) || $apiToken !== 'client_management_api_token') {
            Log::warning('Student summary fetch blocked: missing client management API token.', [
                'domain_id' => $domain->id,
                'domain' => $domain->name,
                'year' => $validated['year'],
            ]);

            return response()->json([
                'message' => 'Client management API token is not configured on this server.',
            ], 422);
        }

        try {
            $response = Http::timeout(20)
                ->acceptJson()
                ->withUserAgent('ClientManager/1.0 (+https://clientmanager.codegaon.com)')
                ->get($baseUrl.'/api/ClntManApi.php', [
                    'action' => 'getTotalStudent',
                    'api_token' => $apiToken,
                    'year' => $validated['year'],
                ]);
        } catch (\Throwable $exception) {
            Log::warning('Student summary fetch failed: EIMS request exception.', [
                'domain_id' => $domain->id,
                'domain' => $domain->name,
                'url' => $baseUrl.'/api/ClntManApi.php',
                'year' => $validated['year'],
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to connect to the selected EIMS domain.',
            ], 422);
        }

        if (! $response->successful()) {
            Log::warning('Student summary fetch failed: EIMS returned an HTTP error.', [
                'domain_id' => $domain->id,
                'domain' => $domain->name,
                'url' => $baseUrl.'/api/ClntManApi.php',
                'year' => $validated['year'],
                'status' => $response->status(),
                'body' => str($response->body())->limit(500)->toString(),
            ]);

            return response()->json([
                'message' => 'Unable to fetch student summary from the selected domain.',
            ], 422);
        }

        $payload = $response->json();

        if (($payload['code'] ?? null) !== 200 || (int) ($payload['status'] ?? 0) !== 1) {
            Log::warning('Student summary fetch failed: EIMS returned an application error.', [
                'domain_id' => $domain->id,
                'domain' => $domain->name,
                'url' => $baseUrl.'/api/ClntManApi.php',
                'year' => $validated['year'],
                'code' => $payload['code'] ?? null,
                'status' => $payload['status'] ?? null,
                'message' => $payload['message'] ?? null,
            ]);

            return response()->json([
                'message' => $payload['message'] ?? 'Student summary request failed.',
            ], 422);
        }

        return response()->json([
            'year' => data_get($payload, 'data.year', $validated['year']),
            'grand_totals' => data_get($payload, 'data.grand_totals', []),
            'summary' => data_get($payload, 'data.summary', []),
            'message' => $payload['message'] ?? 'Student summary fetched successfully.',
        ]);
    }

    /**
     * Validate that the service exists and belongs to the client
     */
    private function validateService(string $serviceType, int $serviceId, int $clientId): void
    {
        switch ($serviceType) {
            case 'domain':
                $service = Domain::where('id', $serviceId)->where('client_id', $clientId)->first();
                break;
            case 'hosting':
                $service = HostingService::where('id', $serviceId)->where('client_id', $clientId)->first();
                break;
            case 'ssl_certificate':
                $service = SslCertificate::whereHas('domain', function ($query) use ($clientId) {
                    $query->where('client_id', $clientId);
                })->where('id', $serviceId)->first();
                break;
            default:
                throw new \InvalidArgumentException('Invalid service type.');
        }

        if (! $service) {
            abort(422, 'The selected service does not exist or does not belong to the specified client.');
        }
    }

    /**
     * Common validation rules for bill data
     */
    private function validateBillData(Request $request, ?Bill $bill = null): array
    {
        $rules = [
            'client_id' => 'required|exists:clients,id',
            'service_type' => 'required|in:domain,hosting,ssl_certificate,eims_fee',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string|max:1000',
        ];

        // Service ID is required only for non-EIMS Fee types
        if ($request->service_type !== 'eims_fee') {
            $rules['service_id'] = 'required|integer';
        } else {
            $rules['service_id'] = 'required|integer|exists:domains,id';
            $rules['total_students'] = 'required|integer|min:1';
            $rules['eims_monthly'] = 'required|numeric|min:0|max:99999999.99';
            $rules['discount'] = 'nullable|numeric|min:0|max:99999999.99';
            $rules['billing_months'] = 'required|array|min:1';
            $rules['billing_months.*'] = 'required|string|date_format:Y-m';
            $rules['student_summary_year'] = 'nullable|digits:4';
            $rules['student_grand_totals'] = 'nullable|array';
            $rules['student_summary'] = 'nullable|array';
        }

        $validated = $request->validate($rules);

        if (($validated['service_type'] ?? null) === 'eims_fee') {
            $this->validateService('domain', (int) $validated['service_id'], (int) $validated['client_id']);
            $validated['discount'] = $validated['discount'] ?? 0;
        }

        return $validated;
    }

    private function buildStudentManagementBaseUrl(string $domainName): string
    {
        $domainName = trim($domainName);
        $hasScheme = preg_match('/^https?:\/\//i', $domainName);
        $localHosts = ['localhost', '127.0.0.1', '0.0.0.0'];
        $rawHost = strtolower(parse_url($hasScheme ? $domainName : 'http://'.$domainName, PHP_URL_HOST) ?? '');
        $scheme = in_array($rawHost, $localHosts, true) ? 'http://' : 'https://';
        $baseUrl = $hasScheme ? $domainName : $scheme.$domainName;
        $parts = parse_url($baseUrl);

        if (! $parts || empty($parts['host'])) {
            abort(422, 'The selected domain is not a valid URL host.');
        }

        $host = strtolower($parts['host']);
        if (in_array($host, $localHosts, true) && ! app()->environment(['local', 'development', 'testing'])) {
            abort(422, 'Local domains cannot be used for student summary fetching.');
        }

        return rtrim(($parts['scheme'] ?? 'https').'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : ''), '/');
    }
}
