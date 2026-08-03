<?php

use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\User;

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->user = User::factory()->create(['company_id' => $this->company->id]);
    $this->actingAs($this->user);
});

describe('PayrollItem Show', function () {
    test('can view payroll item slip gaji', function () {
        $payroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->user->id,
        ]);
        $employee = Employee::factory()->create(['company_id' => $this->company->id]);
        $employeeSalary = EmployeeSalary::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id,
        ]);
        $payrollItem = PayrollItem::factory()->create([
            'payroll_id' => $payroll->id,
            'employee_id' => $employee->id,
            'employee_salary_id' => $employeeSalary->id,
        ]);

        $response = $this->get(route('payroll-items.show', $payrollItem));

        $response->assertStatus(200);
        $response->assertViewIs('payroll-items.show');
        $response->assertViewHas('payrollItem');
    });

    test('cannot view payroll item from other company', function () {
        $otherCompany = Company::factory()->create();
        $otherUser = User::factory()->create(['company_id' => $otherCompany->id]);
        $otherPayroll = Payroll::factory()->create([
            'company_id' => $otherCompany->id,
            'created_by' => $otherUser->id,
        ]);
        $otherEmployee = Employee::factory()->create(['company_id' => $otherCompany->id]);
        $otherSalary = EmployeeSalary::factory()->create([
            'company_id' => $otherCompany->id,
            'employee_id' => $otherEmployee->id,
        ]);
        $otherPayrollItem = PayrollItem::factory()->create([
            'payroll_id' => $otherPayroll->id,
            'employee_id' => $otherEmployee->id,
            'employee_salary_id' => $otherSalary->id,
        ]);

        $response = $this->get(route('payroll-items.show', $otherPayrollItem));

        $response->assertStatus(404);
    });

    test('shows payroll item with earnings and deductions', function () {
        $payroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->user->id,
        ]);
        $employee = Employee::factory()->create(['company_id' => $this->company->id]);
        $employeeSalary = EmployeeSalary::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id,
        ]);
        $payrollItem = PayrollItem::factory()->create([
            'payroll_id' => $payroll->id,
            'employee_id' => $employee->id,
            'employee_salary_id' => $employeeSalary->id,
            'basic_salary' => 5000000,
            'total_earnings' => 1000000,
            'total_deductions' => 500000,
            'net_salary' => 5500000,
        ]);

        $payrollItem->addDetail(null, 'Tunjangan Makan', 'TM', 'earning', 'allowance', 500000);
        $payrollItem->addDetail(null, 'Tunjangan Transport', 'TT', 'earning', 'allowance', 500000);
        $payrollItem->addDetail(null, 'BPJS Kesehatan', 'BPJS', 'deduction', 'insurance', 250000);
        $payrollItem->addDetail(null, 'BPJS TK', 'BPJSTK', 'deduction', 'insurance', 250000);

        $response = $this->get(route('payroll-items.show', $payrollItem));

        $response->assertStatus(200);
        $response->assertSee('Tunjangan Makan');
        $response->assertSee('Tunjangan Transport');
        $response->assertSee('BPJS Kesehatan');
        $response->assertSee('BPJS TK');
    });

    test('shows employee information in slip gaji', function () {
        $payroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->user->id,
        ]);
        $employee = Employee::factory()->create(['company_id' => $this->company->id]);
        $employeeSalary = EmployeeSalary::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id,
        ]);
        $payrollItem = PayrollItem::factory()->create([
            'payroll_id' => $payroll->id,
            'employee_id' => $employee->id,
            'employee_salary_id' => $employeeSalary->id,
            'employee_name' => 'John Doe',
            'employee_number' => 'EMP001',
            'department_name' => 'IT',
            'position_name' => 'Developer',
        ]);

        $response = $this->get(route('payroll-items.show', $payrollItem));

        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertSee('EMP001');
        $response->assertSee('IT');
        $response->assertSee('Developer');
    });

    test('shows attendance summary', function () {
        $payroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->user->id,
        ]);
        $employee = Employee::factory()->create(['company_id' => $this->company->id]);
        $employeeSalary = EmployeeSalary::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id,
        ]);
        $payrollItem = PayrollItem::factory()->create([
            'payroll_id' => $payroll->id,
            'employee_id' => $employee->id,
            'employee_salary_id' => $employeeSalary->id,
            'working_days' => 22,
            'present_days' => 20,
            'absent_days' => 1,
            'late_days' => 2,
            'leave_days' => 1,
        ]);

        $response = $this->get(route('payroll-items.show', $payrollItem));

        $response->assertStatus(200);
        $response->assertSee('22'); // working days
        $response->assertSee('20'); // present days
    });
});
