<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'registrar',
        'registration_date',
        'expiry_date',
        'auto_renew',
        'status',
        'price',
        'payment_status',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'expiry_date' => 'date',
        'auto_renew' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function sslCertificate(): HasOne
    {
        return $this->hasOne(SslCertificate::class);
    }

    public function hostingService(): HasOne
    {
        return $this->hasOne(HostingService::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class, 'service_id')->where('service_type', 'domain');
    }

    public function isExpiringSoon()
    {
        return $this->expiry_date->diffInDays(now()) <= 30;
    }
} 