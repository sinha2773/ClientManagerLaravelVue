<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PaySalary;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PaySalaryController extends Controller
{
    public function index(Request $request)
    {
        $query = PaySalary::with('employee');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }

        if ($request->filled('salary_source')) {
            $query->where('salary_source', $request->input('salary_source'));
        }

        if ($request->filled('month_year')) {
            $query->where('month_year', $request->input('month_year'));
        }

        if ($request->filled('is_paid')) {
            $query->where('is_paid', filter_var($request->input('is_paid'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('is_partial')) {
            $query->where('is_partial', filter_var($request->input('is_partial'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('is_due')) {
            $query->where('is_due', filter_var($request->input('is_due'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('is_advance')) {
            $query->where('is_advance', filter_var($request->input('is_advance'), FILTER_VALIDATE_BOOLEAN));
        }

        $paySalaries = $query->orderBy('created_at', 'desc')->get();

        $employees = Employee::all();

        return Inertia::render('Payroll/Index', [
            'paySalaries' => $paySalaries,
            'employees' => $employees,
            'filters' => $request->only(['search', 'employee_id', 'salary_source', 'month_year', 'is_paid', 'is_partial', 'is_due', 'is_advance']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'salary_amount' => 'required|numeric|min:0',
            'month_year' => 'required|string',
            'salary_source' => 'required|in:CodeGaon,MSBJBD,SinhdBD',
            'is_paid' => 'boolean',
            'is_partial' => 'boolean',
            'is_due' => 'boolean',
            'is_advance' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        PaySalary::create($validated);

        return redirect()->route('payroll.index')->with('success', 'Salary payment recorded successfully.');
    }

    public function update(Request $request, PaySalary $paySalary)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'salary_amount' => 'required|numeric|min:0',
            'month_year' => 'required|string',
            'salary_source' => 'required|in:CodeGaon,MSBJBD,SinhdBD',
            'is_paid' => 'boolean',
            'is_partial' => 'boolean',
            'is_due' => 'boolean',
            'is_advance' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $paySalary->update($validated);

        return redirect()->route('payroll.index')->with('success', 'Salary payment updated successfully.');
    }

    public function destroy(PaySalary $paySalary)
    {
        $paySalary->delete();
        return redirect()->route('payroll.index')->with('success', 'Salary payment deleted successfully.');
    }

    public function report(Request $request)
    {
        $query = PaySalary::with('employee');

        // Filter by month/year
        if ($request->has('month_year') && $request->month_year) {
            $query->where('month_year', $request->month_year);
        }

        // Filter by employee
        if ($request->has('employee_id') && $request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by salary source
        if ($request->has('salary_source') && $request->salary_source) {
            $query->where('salary_source', $request->salary_source);
        }

        // Filter by payment status
        if ($request->has('is_paid')) {
            $query->where('is_paid', $request->is_paid);
        }

        $paySalaries = $query->orderBy('month_year', 'desc')->get();

        // Calculate summary
        $summary = [
            'total_amount' => $paySalaries->sum('salary_amount'),
            'paid_amount' => $paySalaries->where('is_paid', true)->sum('salary_amount'),
            'due_amount' => $paySalaries->where('is_due', true)->sum('salary_amount'),
            'partial_amount' => $paySalaries->where('is_partial', true)->sum('salary_amount'),
            'advance_amount' => $paySalaries->where('is_advance', true)->sum('salary_amount'),
        ];

        // Group by source
        $bySource = $paySalaries->groupBy('salary_source')->map(function ($items) {
            return [
                'total' => $items->sum('salary_amount'),
                'count' => $items->count(),
            ];
        });

        // Group by month
        $byMonth = $paySalaries->groupBy('month_year')->map(function ($items) {
            return [
                'total' => $items->sum('salary_amount'),
                'count' => $items->count(),
            ];
        });

        $employees = Employee::all();

        return Inertia::render('Payroll/Report', [
            'paySalaries' => $paySalaries,
            'summary' => $summary,
            'bySource' => $bySource,
            'byMonth' => $byMonth,
            'employees' => $employees,
            'filters' => $request->only(['month_year', 'employee_id', 'salary_source', 'is_paid'])
        ]);
    }
}
