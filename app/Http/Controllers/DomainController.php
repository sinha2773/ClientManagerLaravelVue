<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DomainController extends Controller
{
    public function index()
    {
        $domains = Domain::with(['client', 'hostingService', 'sslCertificate'])
            ->latest()
            ->get()
            ->map(function ($domain) {
                return [
                    'id' => $domain->id,
                    'name' => $domain->name,
                    'client' => [
                        'id' => $domain->client->id,
                        'name' => $domain->client->name,
                    ],
                    'registrar' => $domain->registrar,
                    'registration_date' => $domain->registration_date->format('Y-m-d'),
                    'expiry_date' => $domain->expiry_date->format('Y-m-d'),
                    'auto_renew' => $domain->auto_renew,
                    'status' => $domain->status,
                    'price' => $domain->price,
                    'payment_status' => $domain->payment_status,
                    'has_hosting' => !is_null($domain->hostingService),
                    'has_ssl' => !is_null($domain->sslCertificate),
                    'is_expiring_soon' => $domain->isExpiringSoon(),
                ];
            });

        return Inertia::render('Domains/Index', [
            'domains' => $domains
        ]);
    }

    public function create()
    {
        return Inertia::render('Domains/Create', [
            'clients' => Client::select('id', 'name')->orderBy('name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
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
            'profit' => $domain->getProfit()
        ]);
    }

    public function edit(Domain $domain)
    {
        return Inertia::render('Domains/Edit', [
            'domain' => $domain,
            'clients' => Client::select('id', 'name')->orderBy('name')->get()
        ]);
    }

    public function update(Request $request, Domain $domain)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
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
        if (!$domain->payment_approved_level1) {
            return back()->with('error', 'Level 1 approval is required first.');
        }

        $domain->update(['payment_approved_level2' => true]);

        return back()->with('success', 'Payment fully approved.');
    }
} 