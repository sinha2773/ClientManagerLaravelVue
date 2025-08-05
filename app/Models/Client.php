<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'address',
        'status',
    ];

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    public function hostingServices(): HasMany
    {
        return $this->hasMany(HostingService::class);
    }

    public function sslCertificates(): HasMany
    {
        return $this->hasMany(SslCertificate::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function getTotalSpent(): float
    {
        $domainTotal = $this->domains()
            ->where('payment_status', 'paid')
            ->sum('price');

        $sslTotal = $this->sslCertificates()
            ->where('payment_status', 'paid')
            ->sum('price');

        $hostingTotal = $this->hostingServices()
            ->where('payment_status', 'paid')
            ->sum('price');

        return $domainTotal + $sslTotal + $hostingTotal;
    }

    public function getActiveServicesCount(): int
    {
        $activeDomains = $this->domains()
            ->where('status', 'active')
            ->count();

        $activeSslCertificates = $this->sslCertificates()
            ->where('status', 'active')
            ->count();

        $activeHostingServices = $this->hostingServices()
            ->where('status', 'active')
            ->count();

        return $activeDomains + $activeSslCertificates + $activeHostingServices;
    }
} 