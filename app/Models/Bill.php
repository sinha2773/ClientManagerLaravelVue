<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_number',
        'client_id',
        'service_type',
        'service_id',
        'description',
        'amount',
        'paid_amount',
        'payment_status',
        'due_date',
        'paid_date',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
        'total_students',
        'billing_months',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
        'approved_at' => 'datetime',
        'billing_months' => 'array',
    ];

    /**
     * Get the client that owns the bill
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the user who created the bill
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved the bill
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the related service (Domain, HostingService, or SslCertificate)
     */
    public function service()
    {
        switch ($this->service_type) {
            case 'domain':
                return $this->belongsTo(Domain::class, 'service_id');
            case 'hosting':
                return $this->belongsTo(HostingService::class, 'service_id');
            case 'ssl_certificate':
                return $this->belongsTo(SslCertificate::class, 'service_id');
            default:
                return null;
        }
    }

    /**
     * Get the remaining amount to be paid
     */
    public function getRemainingAmountAttribute(): float
    {
        return $this->amount - $this->paid_amount;
    }

    /**
     * Check if bill is overdue
     */
    public function isOverdue(): bool
    {
        return $this->due_date->isPast() && $this->payment_status !== 'paid';
    }

    /**
     * Check if bill is fully paid
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Check if bill is partially paid
     */
    public function isPartiallyPaid(): bool
    {
        return $this->payment_status === 'partially_paid';
    }

    /**
     * Update payment status based on paid amount
     */
    public function updatePaymentStatus(): void
    {
        $wasNotPaid = $this->payment_status !== 'paid';

        if ($this->paid_amount >= $this->amount) {
            $this->payment_status = 'paid';
            $this->paid_date = now();
        } elseif ($this->paid_amount > 0) {
            $this->payment_status = 'partially_paid';
        } else {
            $this->payment_status = 'unpaid';
            $this->paid_date = null;
        }

        $this->save();

        if ($wasNotPaid && $this->payment_status === 'paid') {
            $this->renewService();
        }
    }

    public function renewService(): void
    {
        if ($this->service_type === 'eims_fee') {
            return;
        }

        $service = match ($this->service_type) {
            'domain' => Domain::find($this->service_id),
            'hosting' => HostingService::find($this->service_id),
            'ssl_certificate' => SslCertificate::find($this->service_id),
            default => null,
        };

        if (! $service) {
            return;
        }

        $dateField = match ($this->service_type) {
            'domain' => 'expiry_date',
            'hosting' => 'renewal_date',
            'ssl_certificate' => 'expiry_date',
            default => null,
        };

        if ($dateField) {
            $service->update([$dateField => $service->$dateField->addYear()]);
            $service->update(['payment_status' => 'paid']);
        }
    }

    /**
     * Generate unique bill number
     */
    public static function generateBillNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $lastBill = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastBill ? intval(substr($lastBill->bill_number, -4)) + 1 : 1;

        return sprintf('BILL-%s%s-%04d', $year, $month, $sequence);
    }

    /**
     * Boot method to auto-generate bill number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bill) {
            if (! $bill->bill_number) {
                $bill->bill_number = self::generateBillNumber();
            }
        });
    }
}
