<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Domain;
use App\Models\HostingService;
use App\Models\SslCertificate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $thirtyDaysFromNow = $now->copy()->addDays(30);

        // Expiring domains in next 30 days
        $expiringDomains = Domain::with('client')
            ->whereBetween('expiry_date', [$now, $thirtyDaysFromNow])
            ->get();

        // Expiring SSL certificates in next 30 days
        $expiringSslCertificates = SslCertificate::with('client', 'domain')
            ->whereBetween('expiry_date', [$now, $thirtyDaysFromNow])
            ->get();

        // Hosting services due for renewal in next 30 days
        $dueHostingServices = HostingService::with('client', 'domain')
            ->whereBetween('renewal_date', [$now, $thirtyDaysFromNow])
            ->get();

        // Summary counts
        $summary = [
            'total_clients' => Client::count(),
            'total_domains' => Domain::count(),
            'total_ssl_certificates' => SslCertificate::count(),
            'total_hosting_services' => HostingService::count(),
            'expiring_domains_count' => $expiringDomains->count(),
            'expiring_ssl_count' => $expiringSslCertificates->count(),
            'due_hosting_count' => $dueHostingServices->count(),
        ];

        // Payment status summaries
        $unpaidDomains = Domain::where('payment_status', 'unpaid')->count();
        $unpaidSsl = SslCertificate::where('payment_status', 'unpaid')->count();
        $unpaidHosting = HostingService::where('payment_status', 'unpaid')->count();

        // Monthly revenue tracking
        $monthlyRevenue = [
            'domains' => Domain::whereYear('created_at', $now->year)
                ->whereMonth('created_at', $now->month)
                ->sum('price'),
            'ssl' => SslCertificate::whereYear('created_at', $now->year)
                ->whereMonth('created_at', $now->month)
                ->sum('price'),
            'hosting' => HostingService::whereYear('created_at', $now->year)
                ->whereMonth('created_at', $now->month)
                ->sum('price'),
        ];

        return Inertia::render('Dashboard', [
            'summary' => $summary,
            'expiringDomains' => $expiringDomains,
            'expiringSslCertificates' => $expiringSslCertificates,
            'dueHostingServices' => $dueHostingServices,
            'unpaidSummary' => [
                'domains' => $unpaidDomains,
                'ssl' => $unpaidSsl,
                'hosting' => $unpaidHosting,
            ],
            'monthlyRevenue' => $monthlyRevenue,
        ]);
    }
} 