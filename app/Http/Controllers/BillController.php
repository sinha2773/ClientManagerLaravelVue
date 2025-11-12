<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Client;
use App\Models\Domain;
use App\Models\HostingService;
use App\Models\SslCertificate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BillController extends Controller
{
    /**
     * Display a listing of the bills
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->canManageBills()) {
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
    public function create()
    {
        $user = Auth::user();
        
        if (!$user->canManageBills()) {
            abort(403, 'Unauthorized to create bills.');
        }

        $clients = Client::select('id', 'name', 'email')->get();
        $domains = Domain::with('client')->select('id', 'name', 'client_id', 'price')->get();
        $hostingServices = HostingService::with(['client', 'domain'])->select('id', 'package_name', 'client_id', 'domain_id', 'price')->get();
        $sslCertificates = SslCertificate::with(['client', 'domain'])->select('id', 'type', 'provider', 'domain_id', 'price')->get();

        return Inertia::render('Bills/Create', [
            'clients' => $clients,
            'domains' => $domains,
            'hostingServices' => $hostingServices,
            'sslCertificates' => $sslCertificates,
        ]);
    }

        /**
     * Store a newly created bill in storage
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->canManageBills()) {
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
        
        if (!$user->canManageBills()) {
            abort(403, 'Unauthorized to view bills.');
        }

        $bill->load(['client', 'creator', 'approver']);
        
        // Get the related service details (not applicable for EIMS Fee)
        $service = null;
        if ($bill->service_type !== 'eims_fee') {
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
        
        if (!$user->canManageBills() || $bill->status !== 'draft') {
            abort(403, 'Unauthorized to edit this bill.');
        }

        $clients = Client::select('id', 'name', 'email')->get();
        $domains = Domain::with('client')->select('id', 'name', 'client_id', 'price')->get();
        $hostingServices = HostingService::with(['client', 'domain'])->select('id', 'package_name', 'client_id', 'domain_id', 'price')->get();
        $sslCertificates = SslCertificate::with(['client', 'domain'])->select('id', 'type', 'provider', 'domain_id', 'price')->get();

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
        
        if (!$user->canManageBills() || $bill->status !== 'draft') {
            abort(403, 'Unauthorized to edit this bill.');
        }

        $validated = $this->validateBillData($request, $bill);

        // Validate that the service exists and belongs to the client (only for non-EIMS Fee)
        if ($validated['service_type'] !== 'eims_fee') {
            $this->validateService($validated['service_type'], $validated['service_id'], $validated['client_id']);
        }

        $bill->update($validated);

        return redirect()->route('bills.show', $bill)->with('success', 'Bill updated successfully.');
    }    /**
     * Remove the specified bill from storage
     */
    public function destroy(Bill $bill)
    {
        $user = Auth::user();
        
        if (!$user->canManageBills() || $bill->status !== 'draft') {
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
        
        if (!$user->canApproveBills()) {
            abort(403, 'Unauthorized to approve bills.');
        }

        if ($bill->status !== 'draft') {
            return back()->with('error', 'Only draft bills can be approved.');
        }

        $bill->update([
            'status' => 'sent',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Bill approved successfully.');
    }

    /**
     * Update payment status
     */
    public function updatePayment(Request $request, Bill $bill)
    {
        $user = Auth::user();
        
        if (!$user->canManageBills()) {
            abort(403, 'Unauthorized to update payment status.');
        }

        $validated = $request->validate([
            'paid_amount' => 'required|numeric|min:0|max:' . $bill->amount,
            'notes' => 'nullable|string|max:1000',
        ]);

        $bill->update([
            'paid_amount' => $validated['paid_amount'],
            'notes' => $validated['notes'] ?? $bill->notes,
        ]);

        $bill->updatePaymentStatus();

        return back()->with('success', 'Payment status updated successfully.');
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

        if (!$service) {
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
            $rules['service_id'] = 'nullable|integer';
            $rules['total_students'] = 'required|integer|min:1';
            $rules['billing_months'] = 'required|array|min:1';
            $rules['billing_months.*'] = 'required|string|date_format:Y-m';
        }

        return $request->validate($rules);
    }
}
