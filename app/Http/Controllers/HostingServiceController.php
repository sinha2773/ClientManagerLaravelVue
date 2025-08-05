<?php

namespace App\Http\Controllers;

use App\Models\HostingService;
use App\Models\Domain;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HostingServiceController extends Controller
{
    public function index()
    {
        $hostingServices = HostingService::with('domain')
            ->latest()
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'domain' => [
                        'id' => $service->domain->id,
                        'name' => $service->domain->name,
                    ],
                    'provider' => $service->provider,
                    'package_name' => $service->package_name,
                    'renewal_date' => $service->renewal_date->format('Y-m-d'),
                    'status' => $service->status,
                    'price' => $service->price,
                ];
            });

        return Inertia::render('HostingServices/Index', [
            'hostingServices' => $hostingServices
        ]);
    }

    public function create()
    {
        return Inertia::render('HostingServices/Create', [
            'domains' => Domain::select('id', 'name')->orderBy('name')->get()
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
            'domains' => Domain::select('id', 'name')->orderBy('name')->get()
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