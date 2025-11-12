<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'name',
        'email',
        'designation',
        'salary',
        'join_date',
        'end_date',
    ];

    protected $casts = [
        'join_date' => 'date',
        'end_date' => 'date',
        'salary' => 'decimal:2',
    ];

    public function paySalaries()
    {
        return $this->hasMany(PaySalary::class);
    }
}
