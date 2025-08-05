<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::with(['domains', 'hostingServices', 'sslCertificates'])
            ->latest()
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'company' => $client->company,
                    'status' => $client->status,
                    'domains_count' => $client->domains->count(),
                    'hosting_count' => $client->hostingServices->count(),
                    'ssl_count' => $client->sslCertificates->count(),
                    'created_at' => $client->created_at,
                ];
            });

        return Inertia::render('Clients/Index', [
            'clients' => $clients
        ]);
    }

    public function create()
    {
        return Inertia::render('Clients/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        Client::create($validated);

        return redirect()->route('clients.index')
            ->with('message', 'Client created successfully.');
    }

    public function show(Client $client)
    {
        $client->load(['domains', 'sslCertificates', 'hostingServices']);
        
        return Inertia::render('Clients/Show', [
            'client' => $client,
            'stats' => [
                'total_spent' => $client->getTotalSpent(),
                'active_services' => $client->getActiveServicesCount()
            ]
        ]);
    }

    public function edit(Client $client)
    {
        return Inertia::render('Clients/Edit', [
            'client' => $client
        ]);
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        $client->update($validated);

        return redirect()->route('clients.index')
            ->with('message', 'Client updated successfully.');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('clients.index')
            ->with('message', 'Client deleted successfully.');
    }
} 