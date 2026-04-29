<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Domain;
use App\Models\Provider;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DomainController extends Controller
{
    public function index(Request $request)
    {
        $query = Domain::with(['client', 'hostingService', 'sslCertificate', 'provider']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('registrar', 'like', "%{$search}%");
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

        if ($request->filled('registrar')) {
            $query->where('registrar', $request->input('registrar'));
        }

        $domains = $query->latest()
            ->get()
            ->map(function ($domain) {
                $daysUntilExpiry = (int) now()->diffInDays($domain->expiry_date, false);

                return [
                    'id' => $domain->id,
                    'name' => $domain->name,
                    'client' => [
                        'id' => $domain->client->id,
                        'name' => $domain->client->name,
                    ],
                    'client_id' => $domain->client_id,
                    'provider_id' => $domain->provider_id,
                    'providerData' => $domain->provider ? ['id' => $domain->provider->id, 'name' => $domain->provider->name] : null,
                    'registrar' => $domain->registrar,
                    'registration_date' => $domain->registration_date->format('Y-m-d'),
                    'expiry_date' => $domain->expiry_date->format('Y-m-d'),
                    'auto_renew' => $domain->auto_renew,
                    'status' => $domain->status,
                    'price' => $domain->price,
                    'payment_status' => $domain->payment_status,
                    'has_hosting' => ! is_null($domain->hostingService),
                    'has_ssl' => ! is_null($domain->sslCertificate),
                    'is_expiring_soon' => $domain->isExpiringSoon(),
                    'is_expired' => $daysUntilExpiry < 0,
                    'days_until_expiry' => $daysUntilExpiry,
                ];
            });

        return Inertia::render('Domains/Index', [
            'domains' => $domains,
            'filters' => $request->only(['search', 'status', 'payment_status', 'client_id', 'registrar']),
            'clients' => Client::select('id', 'name')->orderBy('name')->get(),
            'registrars' => Domain::select('registrar')->distinct()->orderBy('registrar')->pluck('registrar'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Domains/Create', [
            'clients' => Client::select('id', 'name')->orderBy('name')->get(),
            'providers' => Provider::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'provider_id' => 'nullable|exists:providers,id',
            'registrar' => 'required|string|max:255',
            'registration_date' => 'required|date',
            'expiry_date' => 'required|date|after:registration_date',
            'auto_renew' => 'boolean',
            'status' => 'required|in:active,inactive',
            'price' => 'required|numeric|min:0',
            'payment_status' => 'required|in:paid,unpaid,partial',
        ]);

        Domain::create($validated);

        return redirect()->route('domains.index')
            ->with('message', 'Domain created successfully.');
    }

    public function show(Domain $domain)
    {
        $domain->load('client');

        return Inertia::render('Domains/Show', [
            'domain' => $domain,
        ]);
    }

    public function edit(Domain $domain)
    {
        return Inertia::render('Domains/Edit', [
            'domain' => $domain,
            'clients' => Client::select('id', 'name')->orderBy('name')->get(),
            'providers' => Provider::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Domain $domain)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'provider_id' => 'nullable|exists:providers,id',
            'registrar' => 'required|string|max:255',
            'registration_date' => 'required|date',
            'expiry_date' => 'required|date|after:registration_date',
            'auto_renew' => 'boolean',
            'status' => 'required|in:active,inactive',
            'price' => 'required|numeric|min:0',
            'payment_status' => 'required|in:paid,unpaid,partial',
        ]);

        $domain->update($validated);

        return redirect()->route('domains.index')
            ->with('message', 'Domain updated successfully.');
    }

    public function destroy(Domain $domain)
    {
        $domain->delete();

        return redirect()->route('domains.index')
            ->with('message', 'Domain deleted successfully.');
    }

    public function approvePaymentLevel1(Domain $domain)
    {
        $domain->update(['payment_approved_level1' => true]);

        return back()->with('success', 'Payment approved at Level 1.');
    }

    public function approvePaymentLevel2(Domain $domain)
    {
        if (! $domain->payment_approved_level1) {
            return back()->with('error', 'Level 1 approval is required first.');
        }

        $domain->update(['payment_approved_level2' => true]);

        return back()->with('success', 'Payment fully approved.');
    }
}
