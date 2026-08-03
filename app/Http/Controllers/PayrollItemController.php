<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PayrollItemController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = PayrollItem::with(['payroll', 'employee'])
            ->whereHas('payroll', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by year
        if ($request->filled('year')) {
            $query->whereHas('payroll', function ($q) use ($request) {
                $q->where('period_year', $request->year);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payrollItems = $query->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $employees = Employee::where('company_id', $companyId)
            ->orderBy('first_name')
            ->get();

        $years = PayrollItem::whereHas('payroll', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })
            ->join('payrolls', 'payroll_items.payroll_id', '=', 'payrolls.id')
            ->select('payrolls.period_year')
            ->distinct()
            ->orderByDesc('period_year')
            ->pluck('period_year');

        return view('payroll-items.index', compact('payrollItems', 'employees', 'years'));
    }


    public function show(PayrollItem $payrollItem)
    {
        // Ensure user can only view payroll items from their company
        if ($payrollItem->payroll->company_id !== auth()->user()->company_id) {
            abort(404);
        }

        $payrollItem->load(['payroll', 'employee', 'details']);

        return view('payroll-items.show', compact('payrollItem'));
    }

    public function pdf(PayrollItem $payrollItem)
    {
        // Ensure user can only view payroll items from their company
        if ($payrollItem->payroll->company_id !== auth()->user()->company_id) {
            abort(404);
        }

        $payrollItem->load(['payroll', 'employee', 'details']);

        $company = auth()->user()->company;

        $pdf = Pdf::loadView('payroll-items.pdf', compact('payrollItem', 'company'));

        $filename = 'slip-gaji-' . $payrollItem->employee_number . '-' . $payrollItem->payroll->period_label . '.pdf';

        return $pdf->download($filename);
    }
}
