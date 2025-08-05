<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SslCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'domain_id',
        'provider',
        'type',
        'expiry_date',
        'status',
        'price',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class, 'service_id')->where('service_type', 'ssl_certificate');
    }

    public function isExpiringSoon()
    {
        return $this->expiry_date->diffInDays(now()) <= 30;
    }
} 