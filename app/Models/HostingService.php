<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HostingService extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'domain_id',
        'provider',
        'package_name',
        'start_date',
        'renewal_date',
        'price',
        'payment_status',
        'status',
        'server_ip',
        'control_panel_url',
        'username',
        'password',
    ];

    protected $casts = [
        'start_date' => 'date',
        'renewal_date' => 'date',
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
        return $this->hasMany(Bill::class, 'service_id')->where('service_type', 'hosting');
    }
} 