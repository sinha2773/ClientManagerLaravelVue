<?php

namespace App\Http\Controllers;

use App\Models\Bill;
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

        // Comprehensive billing analytics
        $billingReport = $this->getBillingReport($now);

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
            'billingReport' => $billingReport,
        ]);
    }

    /**
     * Get comprehensive billing report data
     */
    private function getBillingReport(Carbon $now)
    {
        // Current month billing stats
        $currentMonthStart = $now->copy()->startOfMonth();
        $currentMonthEnd = $now->copy()->endOfMonth();

        // Last month billing stats
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // Total bills and amounts
        $totalBills = Bill::count();
        $totalBilled = Bill::sum('amount');
        $totalPaid = Bill::sum('paid_amount');
        $totalOutstanding = $totalBilled - $totalPaid;

        // Payment status breakdown
        $paidBills = Bill::where('payment_status', 'paid')->count();
        $unpaidBills = Bill::where('payment_status', 'unpaid')->count();
        $partiallyPaidBills = Bill::where('payment_status', 'partially_paid')->count();

        // Overdue bills
        $overdueBills = Bill::where('due_date', '<', $now)
            ->where('payment_status', '!=', 'paid')
            ->with('client')
            ->get();

        // Current month statistics
        $currentMonthBills = Bill::whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
            ->selectRaw('
                COUNT(*) as total_bills,
                SUM(amount) as total_amount,
                SUM(paid_amount) as total_paid,
                SUM(CASE WHEN payment_status = "paid" THEN 1 ELSE 0 END) as paid_count,
                SUM(CASE WHEN payment_status = "unpaid" THEN 1 ELSE 0 END) as unpaid_count,
                SUM(CASE WHEN payment_status = "partially_paid" THEN 1 ELSE 0 END) as partial_count
            ')
            ->first();

        // Last month statistics for comparison
        $lastMonthBills = Bill::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->selectRaw('
                COUNT(*) as total_bills,
                SUM(amount) as total_amount,
                SUM(paid_amount) as total_paid
            ')
            ->first();

        // Monthly trend data (last 6 months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = $now->copy()->subMonths($i)->startOfMonth();
            $monthEnd = $now->copy()->subMonths($i)->endOfMonth();
            
            $monthData = Bill::whereBetween('created_at', [$monthStart, $monthEnd])
                ->selectRaw('
                    COUNT(*) as count,
                    SUM(amount) as billed,
                    SUM(paid_amount) as paid
                ')
                ->first();

            $monthlyTrend[] = [
                'month' => $monthStart->format('M Y'),
                'count' => $monthData->count ?? 0,
                'billed' => $monthData->billed ?? 0,
                'paid' => $monthData->paid ?? 0,
            ];
        }

        // Service type breakdown
        $serviceBreakdown = Bill::selectRaw('
                service_type,
                COUNT(*) as count,
                SUM(amount) as total_amount,
                SUM(paid_amount) as paid_amount
            ')
            ->groupBy('service_type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->service_type => [
                    'count' => $item->count,
                    'total_amount' => $item->total_amount ?? 0,
                    'paid_amount' => $item->paid_amount ?? 0,
                    'outstanding' => ($item->total_amount ?? 0) - ($item->paid_amount ?? 0),
                ]];
            });

        // Recent unpaid bills
        $recentUnpaidBills = Bill::where('payment_status', '!=', 'paid')
            ->with('client')
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($bill) {
                return [
                    'id' => $bill->id,
                    'bill_number' => $bill->bill_number,
                    'client_name' => $bill->client->name,
                    'amount' => $bill->amount,
                    'paid_amount' => $bill->paid_amount,
                    'remaining_amount' => $bill->remaining_amount,
                    'due_date' => $bill->due_date,
                    'payment_status' => $bill->payment_status,
                    'service_type' => $bill->service_type,
                    'is_overdue' => $bill->isOverdue(),
                ];
            });

        return [
            'overview' => [
                'total_bills' => $totalBills,
                'total_billed' => $totalBilled,
                'total_paid' => $totalPaid,
                'total_outstanding' => $totalOutstanding,
                'paid_bills' => $paidBills,
                'unpaid_bills' => $unpaidBills,
                'partially_paid_bills' => $partiallyPaidBills,
                'overdue_count' => $overdueBills->count(),
                'collection_rate' => $totalBilled > 0 ? ($totalPaid / $totalBilled) * 100 : 0,
            ],
            'current_month' => [
                'total_bills' => $currentMonthBills->total_bills ?? 0,
                'total_amount' => $currentMonthBills->total_amount ?? 0,
                'total_paid' => $currentMonthBills->total_paid ?? 0,
                'paid_count' => $currentMonthBills->paid_count ?? 0,
                'unpaid_count' => $currentMonthBills->unpaid_count ?? 0,
                'partial_count' => $currentMonthBills->partial_count ?? 0,
            ],
            'last_month' => [
                'total_bills' => $lastMonthBills->total_bills ?? 0,
                'total_amount' => $lastMonthBills->total_amount ?? 0,
                'total_paid' => $lastMonthBills->total_paid ?? 0,
            ],
            'monthly_trend' => $monthlyTrend,
            'service_breakdown' => $serviceBreakdown,
            'recent_unpaid' => $recentUnpaidBills,
            'overdue_bills' => $overdueBills->map(function ($bill) {
                return [
                    'id' => $bill->id,
                    'bill_number' => $bill->bill_number,
                    'client_name' => $bill->client->name,
                    'amount' => $bill->amount,
                    'remaining_amount' => $bill->remaining_amount,
                    'due_date' => $bill->due_date,
                    'days_overdue' => $bill->due_date->diffInDays(now()),
                ];
            }),
        ];
    }
} 