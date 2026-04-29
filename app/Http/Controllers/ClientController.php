<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::with(['domains', 'hostingServices', 'sslCertificates', 'category']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('short_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('client_type')) {
            $query->where('client_type', $request->input('client_type'));
        }

        if ($request->filled('client_category_id')) {
            $query->where('client_category_id', $request->input('client_category_id'));
        }

        $clients = $query->latest()
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'short_name' => $client->short_name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'company' => $client->company,
                    'status' => $client->status,
                    'client_category_id' => $client->client_category_id,
                    'client_type' => $client->client_type,
                    'category' => $client->category,
                    'domains_count' => $client->domains->count(),
                    'hosting_count' => $client->hostingServices->count(),
                    'ssl_count' => $client->sslCertificates->count(),
                    'created_at' => $client->created_at,
                ];
            });

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'filters' => $request->only(['search', 'status', 'client_type', 'client_category_id']),
            'categories' => \App\Models\ClientCategory::where('status', 'active')->get(),
        ]);
    }

    public function create()
    {
        $categories = \App\Models\ClientCategory::where('status', 'active')->get();

        return Inertia::render('Clients/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
            'client_category_id' => 'nullable|exists:client_categories,id',
            'client_type' => 'required|in:government,private,other',
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
                'active_services' => $client->getActiveServicesCount(),
            ],
        ]);
    }

    public function edit(Client $client)
    {
        $categories = \App\Models\ClientCategory::where('status', 'active')->get();

        return Inertia::render('Clients/Edit', [
            'client' => $client,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
            'client_category_id' => 'nullable|exists:client_categories,id',
            'client_type' => 'required|in:government,private,other',
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
