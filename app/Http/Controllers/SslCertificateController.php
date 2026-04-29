<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Domain;
use App\Models\SslCertificate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SslCertificateController extends Controller
{
    public function index(Request $request)
    {
        $query = SslCertificate::with(['domain', 'client']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('provider', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
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

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $sslCertificates = $query->latest()
            ->get()
            ->map(function ($certificate) {
                $daysUntilExpiry = (int) now()->diffInDays($certificate->expiry_date, false);

                return [
                    'id' => $certificate->id,
                    'domain' => [
                        'id' => $certificate->domain->id,
                        'name' => $certificate->domain->name,
                    ],
                    'client_id' => $certificate->client_id,
                    'provider' => $certificate->provider,
                    'type' => $certificate->type,
                    'issue_date' => $certificate->issue_date->format('Y-m-d'),
                    'expiry_date' => $certificate->expiry_date->format('Y-m-d'),
                    'status' => $certificate->status,
                    'price' => $certificate->price,
                    'payment_status' => $certificate->payment_status,
                    'auto_renew' => $certificate->auto_renew,
                    'is_expired' => $daysUntilExpiry < 0,
                    'days_until_expiry' => $daysUntilExpiry,
                ];
            });

        return Inertia::render('SslCertificates/Index', [
            'sslCertificates' => $sslCertificates,
            'filters' => $request->only(['search', 'status', 'payment_status', 'client_id', 'domain_id', 'provider', 'type']),
            'clients' => Client::select('id', 'name')->orderBy('name')->get(),
            'domains' => Domain::select('id', 'name')->orderBy('name')->get(),
            'providers' => SslCertificate::select('provider')->distinct()->orderBy('provider')->pluck('provider'),
            'types' => SslCertificate::select('type')->distinct()->orderBy('type')->pluck('type'),
        ]);
    }

    public function create()
    {
        return Inertia::render('SslCertificates/Create', [
            'domains' => Domain::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'domain_id' => 'required|exists:domains,id',
            'provider' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date',
            'status' => 'required|in:active,inactive',
            'price' => 'required|numeric|min:0',
            'payment_status' => 'required|in:paid,unpaid,partially_paid',
            'auto_renew' => 'boolean',
        ]);

        // Get the client_id from the selected domain
        $domain = Domain::findOrFail($validated['domain_id']);
        $validated['client_id'] = $domain->client_id;

        SslCertificate::create($validated);

        return redirect()->route('ssl-certificates.index')
            ->with('message', 'SSL certificate created successfully.');
    }

    public function edit(SslCertificate $sslCertificate)
    {
        return Inertia::render('SslCertificates/Edit', [
            'sslCertificate' => [
                'id' => $sslCertificate->id,
                'domain_id' => $sslCertificate->domain_id,
                'provider' => $sslCertificate->provider,
                'type' => $sslCertificate->type,
                'issue_date' => $sslCertificate->issue_date->format('Y-m-d'),
                'expiry_date' => $sslCertificate->expiry_date->format('Y-m-d'),
                'status' => $sslCertificate->status,
                'price' => $sslCertificate->price,
                'payment_status' => $sslCertificate->payment_status,
                'auto_renew' => $sslCertificate->auto_renew,
            ],
            'domains' => Domain::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, SslCertificate $sslCertificate)
    {
        $validated = $request->validate([
            'domain_id' => 'required|exists:domains,id',
            'provider' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date',
            'status' => 'required|in:active,inactive',
            'price' => 'required|numeric|min:0',
            'payment_status' => 'required|in:paid,unpaid,partially_paid',
            'auto_renew' => 'boolean',
        ]);

        // Get the client_id from the selected domain
        $domain = Domain::findOrFail($validated['domain_id']);
        $validated['client_id'] = $domain->client_id;

        $sslCertificate->update($validated);

        return redirect()->route('ssl-certificates.index')
            ->with('message', 'SSL certificate updated successfully.');
    }

    public function destroy(SslCertificate $sslCertificate)
    {
        $sslCertificate->delete();

        return redirect()->route('ssl-certificates.index')
            ->with('message', 'SSL certificate deleted successfully.');
    }
}
