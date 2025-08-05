<?php

namespace App\Http\Controllers;

use App\Models\SslCertificate;
use App\Models\Domain;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SslCertificateController extends Controller
{
    public function index()
    {
        $sslCertificates = SslCertificate::with('domain')
            ->latest()
            ->get()
            ->map(function ($certificate) {
                return [
                    'id' => $certificate->id,
                    'domain' => [
                        'id' => $certificate->domain->id,
                        'name' => $certificate->domain->name,
                    ],
                    'provider' => $certificate->provider,
                    'type' => $certificate->type,
                    'expiry_date' => $certificate->expiry_date->format('Y-m-d'),
                    'status' => $certificate->status,
                    'price' => $certificate->price,
                ];
            });

        return Inertia::render('SslCertificates/Index', [
            'sslCertificates' => $sslCertificates
        ]);
    }

    public function create()
    {
        return Inertia::render('SslCertificates/Create', [
            'domains' => Domain::select('id', 'name')->orderBy('name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'domain_id' => 'required|exists:domains,id',
            'provider' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'expiry_date' => 'required|date',
            'status' => 'required|in:active,inactive',
            'price' => 'required|numeric|min:0',
        ]);

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
                'expiry_date' => $sslCertificate->expiry_date->format('Y-m-d'),
                'status' => $sslCertificate->status,
                'price' => $sslCertificate->price,
            ],
            'domains' => Domain::select('id', 'name')->orderBy('name')->get()
        ]);
    }

    public function update(Request $request, SslCertificate $sslCertificate)
    {
        $validated = $request->validate([
            'domain_id' => 'required|exists:domains,id',
            'provider' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'expiry_date' => 'required|date',
            'status' => 'required|in:active,inactive',
            'price' => 'required|numeric|min:0',
        ]);

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