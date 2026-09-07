<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Domain;
use App\Models\Provider;
use App\Models\SslCertificate;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class SslCertificateController extends Controller
{
    public function index(Request $request)
    {
        $query = SslCertificate::with(['domain', 'client', 'providerRel']);

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

        if ($request->filled('provider_id')) {
            $query->where('provider_id', $request->input('provider_id'));
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
                    'provider_id' => $certificate->provider_id,
                    'providerRel' => $certificate->providerRel ? ['id' => $certificate->providerRel->id, 'name' => $certificate->providerRel->name] : null,
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
            'filters' => $request->only(['search', 'status', 'payment_status', 'client_id', 'domain_id', 'provider_id', 'type']),
            'clients' => Client::select('id', 'name')->orderBy('name')->get(),
            'domains' => Domain::select('id', 'name')->orderBy('name')->get(),
            'providers' => Provider::orderBy('name')->get(),
            'types' => SslCertificate::select('type')->distinct()->orderBy('type')->pluck('type'),
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('SslCertificates/Create', [
            'domains' => Domain::select('id', 'name')->orderBy('name')->get(),
            'providers' => Provider::orderBy('name')->get(),
            'canEditRenewal' => $request->user()->canApproveBills(),
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'domain_id' => 'required|exists:domains,id',
            'provider' => 'nullable|string|max:255',
            'provider_id' => 'nullable|exists:providers,id',
            'type' => 'required|string|max:255',
            'issue_date' => 'required|date',
            'status' => 'required|in:active,inactive',
            'price' => 'required|numeric|min:0',
            'payment_status' => 'required|in:paid,unpaid,partially_paid',
            'auto_renew' => 'boolean',
        ];
        $rules['expiry_date'] = $request->user()->canApproveBills()
            ? 'required|date|after:issue_date'
            : 'nullable|date';

        $validated = $request->validate($rules);

        if (! $request->user()->canApproveBills()) {
            $validated['expiry_date'] = Carbon::parse($validated['issue_date'])
                ->addYearNoOverflow()
                ->toDateString();
        }

        $domain = Domain::findOrFail($validated['domain_id']);
        $validated['client_id'] = $domain->client_id;

        if (empty($validated['provider']) && ! empty($validated['provider_id'])) {
            $validated['provider'] = Provider::find($validated['provider_id'])->name;
        }

        SslCertificate::create($validated);

        return redirect()->route('ssl-certificates.index')
            ->with('message', 'SSL certificate created successfully.');
    }

    public function edit(Request $request, SslCertificate $sslCertificate)
    {
        return Inertia::render('SslCertificates/Edit', [
            'sslCertificate' => [
                'id' => $sslCertificate->id,
                'domain_id' => $sslCertificate->domain_id,
                'provider' => $sslCertificate->provider,
                'provider_id' => $sslCertificate->provider_id,
                'type' => $sslCertificate->type,
                'issue_date' => $sslCertificate->issue_date->format('Y-m-d'),
                'expiry_date' => $sslCertificate->expiry_date->format('Y-m-d'),
                'status' => $sslCertificate->status,
                'price' => $sslCertificate->price,
                'payment_status' => $sslCertificate->payment_status,
                'auto_renew' => $sslCertificate->auto_renew,
            ],
            'domains' => Domain::select('id', 'name')->orderBy('name')->get(),
            'providers' => Provider::orderBy('name')->get(),
            'canEditRenewal' => $request->user()->canApproveBills(),
        ]);
    }

    public function update(Request $request, SslCertificate $sslCertificate)
    {
        $rules = [
            'domain_id' => 'required|exists:domains,id',
            'provider' => 'nullable|string|max:255',
            'provider_id' => 'nullable|exists:providers,id',
            'type' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'price' => 'required|numeric|min:0',
            'payment_status' => 'required|in:paid,unpaid,partially_paid',
            'auto_renew' => 'boolean',
        ];

        if ($request->user()->canApproveBills()) {
            $rules['expiry_date'] = 'required|date|after:'.$sslCertificate->issue_date->toDateString();
        }

        $validated = $request->validate($rules);

        $domain = Domain::findOrFail($validated['domain_id']);
        $validated['client_id'] = $domain->client_id;

        if (empty($validated['provider']) && ! empty($validated['provider_id'])) {
            $validated['provider'] = Provider::find($validated['provider_id'])->name;
        }

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
