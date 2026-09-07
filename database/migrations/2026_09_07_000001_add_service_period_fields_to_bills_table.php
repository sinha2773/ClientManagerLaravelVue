<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->date('service_started_date')->nullable()->after('service_id');
            $table->date('service_renewal_date')->nullable()->after('service_started_date');
            $table->timestamp('service_renewed_at')->nullable()->after('service_renewal_date');
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->date('last_billing_date')->nullable()->after('expiry_date');
        });

        Schema::table('hosting_services', function (Blueprint $table) {
            $table->date('last_billing_date')->nullable()->after('renewal_date');
        });

        Schema::table('ssl_certificates', function (Blueprint $table) {
            $table->date('last_billing_date')->nullable()->after('expiry_date');
        });

        $this->backfillExistingBills();
    }

    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn(['service_started_date', 'service_renewal_date', 'service_renewed_at']);
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->dropColumn('last_billing_date');
        });

        Schema::table('hosting_services', function (Blueprint $table) {
            $table->dropColumn('last_billing_date');
        });

        Schema::table('ssl_certificates', function (Blueprint $table) {
            $table->dropColumn('last_billing_date');
        });
    }

    private function backfillExistingBills(): void
    {
        $serviceMap = [
            'domain' => ['domains', 'expiry_date', 'registration_date'],
            'hosting' => ['hosting_services', 'renewal_date', 'start_date'],
            'ssl_certificate' => ['ssl_certificates', 'expiry_date', 'issue_date'],
        ];

        DB::table('bills')
            ->whereIn('service_type', array_keys($serviceMap))
            ->whereNull('service_started_date')
            ->orderBy('id')
            ->chunkById(200, function ($bills) use ($serviceMap): void {
                foreach ($bills as $bill) {
                    [$table, $renewalColumn, $initialColumn] = $serviceMap[$bill->service_type];
                    $service = DB::table($table)->find($bill->service_id);

                    if (! $service) {
                        continue;
                    }

                    $currentRenewal = $service->{$renewalColumn} ?: $service->{$initialColumn};

                    if (! $currentRenewal) {
                        continue;
                    }

                    $alreadyRenewed = $bill->payment_status === 'paid' && $bill->approved_by;
                    $currentRenewalDate = Carbon::parse($currentRenewal);
                    $startedDate = $alreadyRenewed
                        ? $currentRenewalDate->copy()->subYear()
                        : $currentRenewalDate->copy();
                    $renewalDate = $alreadyRenewed
                        ? $currentRenewalDate->copy()
                        : $currentRenewalDate->copy()->addYear();

                    DB::table('bills')->where('id', $bill->id)->update([
                        'service_started_date' => $startedDate->toDateString(),
                        'service_renewal_date' => $renewalDate->toDateString(),
                        'service_renewed_at' => $alreadyRenewed
                            ? ($bill->approved_at ?: $bill->paid_date ?: $bill->updated_at)
                            : null,
                    ]);

                    if ($alreadyRenewed) {
                        DB::table($table)->where('id', $bill->service_id)->update([
                            'last_billing_date' => $currentRenewalDate->toDateString(),
                        ]);
                    }
                }
            });
    }
};
