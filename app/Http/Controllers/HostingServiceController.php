<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Domain;
use App\Models\HostingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HostingServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = HostingService::with(['domain', 'client']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('provider', 'like', "%{$search}%")
                    ->orWhere('package_name', 'like', "%{$search}%")
                    ->orWhere('server_ip', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->input('client_id'));
        }

        if ($request->filled('domain_id')) {
            $query->where('domain_id', $request->input('domain_id'));
        }

        if ($request->filled('provider')) {
            $query->where('provider', $request->input('provider'));
        }

        $hostingServices = $query->latest()
            ->get()
            ->map(function ($service) {
                $daysUntilExpiry = (int) now()->diffInDays($service->renewal_date, false);

                return [
                    'id' => $service->id,
                    'domain' => [
                        'id' => $service->domain->id,
                        'name' => $service->domain->name,
                    ],
                    'client_id' => $service->client_id,
                    'provider' => $service->provider,
                    'package_name' => $service->package_name,
                    'renewal_date' => $service->renewal_date->format('Y-m-d'),
                    'status' => $service->status,
                    'price' => $service->price,
                    'payment_status' => $service->payment_status,
                    'is_expired' => $daysUntilExpiry < 0,
                    'days_until_expiry' => $daysUntilExpiry,
                ];
            });

        return Inertia::render('HostingServices/Index', [
            'hostingServices' => $hostingServices,
            'filters' => $request->only(['search', 'status', 'payment_status', 'client_id', 'domain_id', 'provider']),
            'clients' => Client::select('id', 'name')->orderBy('name')->get(),
            'domains' => Domain::select('id', 'name')->orderBy('name')->get(),
            'providers' => HostingService::select('provider')->distinct()->orderBy('provider')->pluck('provider'),
        ]);
    }

    public function create()
    {
        return Inertia::render('HostingServices/Create', [
            'domains' => Domain::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'domain_id' => 'required|exists:domains,id',
            'provider' => 'required|string|max:255',
            'package_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'renewal_date' => 'required|date',
            'status' => 'required|in:active,inactive',
            'price' => 'required|numeric|min:0',
            'payment_status' => 'required|in:paid,unpaid,partially_paid',
            'server_ip' => 'nullable|string|max:255',
            'control_panel_url' => 'nullable|url|max:255',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);

        // Get the domain and its associated client_id
        $domain = Domain::findOrFail($validated['domain_id']);
        $validated['client_id'] = $domain->client_id;

        HostingService::create($validated);

        return redirect()->route('hosting-services.index')
            ->with('message', 'Hosting service created successfully.');
    }

    public function edit(HostingService $hostingService)
    {
        return Inertia::render('HostingServices/Edit', [
            'hostingService' => [
                'id' => $hostingService->id,
                'domain_id' => $hostingService->domain_id,
                'provider' => $hostingService->provider,
                'package_name' => $hostingService->package_name,
                'start_date' => $hostingService->start_date->format('Y-m-d'),
                'renewal_date' => $hostingService->renewal_date->format('Y-m-d'),
                'status' => $hostingService->status,
                'payment_status' => $hostingService->payment_status,
                'price' => $hostingService->price,
                'username' => $hostingService->username,
                'password' => $hostingService->password,
                'server_ip' => $hostingService->server_ip,
                'control_panel_url' => $hostingService->control_panel_url,
            ],
            'domains' => Domain::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, HostingService $hostingService)
    {
        $validated = $request->validate([
            'domain_id' => 'required|exists:domains,id',
            'provider' => 'required|string|max:255',
            'package_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'renewal_date' => 'required|date',
            'status' => 'required|in:active,inactive',
            'price' => 'required|numeric|min:0',
            'payment_status' => 'required|in:paid,unpaid,partially_paid',
            'server_ip' => 'nullable|string|max:255',
            'control_panel_url' => 'nullable|url|max:255',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);

        // Get the domain and its associated client_id
        $domain = Domain::findOrFail($validated['domain_id']);
        $validated['client_id'] = $domain->client_id;

        $hostingService->update($validated);

        return redirect()->route('hosting-services.index')
            ->with('message', 'Hosting service updated successfully.');
    }

    public function destroy(HostingService $hostingService)
    {
        $hostingService->delete();

        return redirect()->route('hosting-services.index')
            ->with('message', 'Hosting service deleted successfully.');
    }
}
