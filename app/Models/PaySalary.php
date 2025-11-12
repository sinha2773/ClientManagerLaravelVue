<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaySalary extends Model
{
    protected $fillable = [
        'employee_id',
        'salary_amount',
        'month_year',
        'salary_source',
        'is_paid',
        'is_partial',
        'is_due',
        'is_advance',
        'notes',
    ];

    protected $casts = [
        'salary_amount' => 'decimal:2',
        'is_paid' => 'boolean',
        'is_partial' => 'boolean',
        'is_due' => 'boolean',
        'is_advance' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
