<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use SebastianBergmann\CodeCoverage\Percentage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Employee extends Model
{
    protected $table = 'employees';
    protected $fillable = [
        'user_id',
        'name',
        'last_name',
        'dob',
        'gender',
        'phone',
        'address',
        'email',
        'password',
        'employee_id',
        'branch_id',
        'department_id',
        'subdepartment_id',
        'designation_id',
        'shift_id',
        'company_doj',
        'company_start_time',
        'company_end_time',
        'documents',
        'account_holder_name',
        'account_number',
        'bank_name',
        'bank_identifier_code',
        'branch_location',
        'employment_type_id',
        'shift_break_type',
        'lunch_start',
        'lunch_end',
        'lunch_minutes',
        'tea_start',
        'tea_end',
        'tea_minutes',
        'refresh_type',
        'refresh_start',
        'refresh_end',
        'refresh_minutes',
        'tax_payer_id',
        'ni_number',
        'ni_table_letter',
        'payment_method',
        'pf_id',
        'esic_id',
        'salary_type',
        'account_type',
        'salary',
        'basic_salary',
        'hra',
        'da',
        'created_by',
        'is_admin_staff',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function upcomingShift()
    {
        return $this->belongsTo(Shift::class, 'upcoming_shift_id');
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class, 'employee_id', 'employee_id');
    }

    public function fileDocuments()
    {
        return $this->hasMany(EmployeeFileDocument::class, 'employee_id', 'id');
    }

    public function salary_type()
    {
        return $this->hasOne(PayslipType::class, 'id', 'salary_type')->pluck('name')->first();
    }

    public function account_type()
    {
        return $this->hasOne(AccountList::class, 'id', 'account_type')->pluck('account_name')->first();
    }

    public function allowances()
    {
        return $this->hasMany(Allowance::class, 'employee_id');
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class, 'employee_id');
    }

    public function bonuses()
    {
        return $this->hasMany(Bonous::class, 'employee_id');
    }

    public function pearks()
    {
        return $this->hasMany(Peark::class, 'employee_id');
    }

    public function loans()
    {
        return $this->hasMany(Loan::class, 'employee_id');
    }

    public function saturationDeductions()
    {
        return $this->hasMany(SaturationDeduction::class, 'employee_id');
    }

    public function otherPayments()
    {
        return $this->hasMany(OtherPayment::class, 'employee_id');
    }

    public function pensions()
    {
        return $this->hasMany(Pension::class, 'employee_id');
    }

    public function overtimes()
    {
        return $this->hasMany(Overtime::class, 'employee_id');
    }

    public function attendances()
    {
        return $this->hasMany(AttendanceEmployee::class, 'employee_id');
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class, 'employee_id');
    }

    /**
     * Leave types assigned to this employee (pivot).
     */
    public function leaveTypes()
    {
        return $this->belongsToMany(LeaveType::class, 'employee_leave_types', 'employee_id', 'leave_type_id')
                    ->withPivot('total_days', 'is_paid')
                    ->withTimestamps();
    }

    public function get_net_hra()
    {
        $hra = $this->hra * $this->basic_salary / 100;
        return round($hra, 2);
    }
    public function get_net_da()
    {
        $da = $this->da * $this->basic_salary / 100;
        return round($da, 2);
    }
    public function get_net_salary()
    {
        $employee = Employee::with([
            'allowances',
            'commissions',
            'bonuses',
            'pearks',
            'loans',
            'saturationDeductions',
            'otherPayments',
            'pensions',
            'overtimes',
            'salaryType'
        ])->find($this->id);

        $storedSalary = (float) $employee->salary;

        // ── Resolve the monthly gross base ────────────────────────────────────
        // For monthly employees: stored salary IS the monthly gross.
        // For hourly employees:  stored salary is the hourly RATE.
        //   → project the full-month gross = rate × hours/day × working days/month.
        $isHourly = false;
        if ($employee->salaryType) {
            $isHourly = ($employee->salaryType->salary_basis ?? 'monthly') === 'hourly';
        }

        if ($isHourly) {
            // Resolve working hours per day (same priority as Utility::employeePayslipDetail)
            $companySettings = \App\Models\Utility::settings();

            $hoursPerDay = isset($companySettings['working_hours_per_day'])
                ? (float) $companySettings['working_hours_per_day']
                : 0;

            if ($hoursPerDay <= 0) {
                $shiftStart = $companySettings['company_start_time'] ?? '';
                $shiftEnd   = $companySettings['company_end_time']   ?? '';
                if (!empty($shiftStart) && !empty($shiftEnd)) {
                    try {
                        $diffMins    = \Carbon\Carbon::parse($shiftStart)->diffInMinutes(\Carbon\Carbon::parse($shiftEnd));
                        $hoursPerDay = $diffMins > 0 ? round($diffMins / 60, 2) : 0;
                    } catch (\Throwable $_e) {
                    }
                }
            }

            if ($hoursPerDay <= 0) {
                $hoursPerDay = 8.0;
            }

            // Working days in current month
            $now             = \Carbon\Carbon::now();
            $workingDays     = \App\Models\Utility::getWorkingDaysInMonth(
                $now->year,
                $now->month,
                $employee->created_by
            );

            // Projected monthly gross for the preview
            $salary = round($storedSalary * $hoursPerDay * $workingDays, 2);
        } else {
            $salary = $storedSalary;
        }

        // Percentage-based allowances/deductions are calculated against basic_salary
        $basicSalary = (float) $employee->basic_salary;

        // ★ FIX: For hourly employees, use the projected monthly gross
        // (hourly_rate × hours_per_day × working_days) as the base
        // instead of the stale employees.basic_salary column.
        // $salary was already projected above in the hourly block.
        if ($isHourly) {
            $basicSalary = $salary;
        }

        $calcTotal = function ($items) use ($basicSalary) {
            return $items->sum(function ($item) use ($basicSalary) {
                if ($item->type === 'percentage') {
                    if (strtolower($item->title) == 'epf') {
                        // EPF = 12% of basic_salary
                        $empdeduction = $basicSalary * 12 / 100;
                    } elseif (strtolower($item->title) == 'gpf') {
                        // GPF = 6% of basic_salary
                        $empdeduction = $basicSalary * 6 / 100;
                    } else {
                        $empdeduction = $item->amount * $basicSalary / 100;
                    }
                    return $empdeduction;
                } else {
                    return $item->amount;
                }
            });
        };

        $total_allowance            = $calcTotal($employee->allowances);
        $total_commission           = $calcTotal($employee->commissions);
        $total_bonus                = $calcTotal($employee->bonuses);
        $total_pearks               = $calcTotal($employee->pearks);
        $total_loan                 = $calcTotal($employee->loans);
        $total_saturation_deduction = $calcTotal($employee->saturationDeductions);
        $total_other_payment        = $calcTotal($employee->otherPayments);
        $total_pension              = $calcTotal($employee->pensions);
        // Overtime
        $total_overtime = $employee->overtimes->sum(function ($ot) {
            return $ot->number_of_days * $ot->hours * $ot->rate;
        });

        // Net Pay = basic_salary + allowances − deductions (no HRA/DA — stored as allowances)
        $netSalary = $basicSalary
            + $total_allowance + $total_other_payment + $total_commission + $total_bonus
            - $total_loan - $total_pension - $total_saturation_deduction;

        // return round($salary + $adjustments, 2);
        return round($netSalary, 2);
    }

    public static function allowance($id)
    {
        //allowance
        $allowances = Allowance::where('employee_id', '=', $id)->get();
        $total_allowance = 0;
        foreach ($allowances as $allowance) {
            $total_allowance = $allowance->amount + $total_allowance;
        }

        $allowance_json = json_encode($allowances);

        return $allowance_json;
    }

    public static function commission($id)
    {
        //commission
        $commissions = Commission::where('employee_id', '=', $id)->get();
        $total_commission = 0;

        foreach ($commissions as $commission) {
            $total_commission = $commission->amount + $total_commission;
        }
        $commission_json = json_encode($commissions);

        return $commission_json;
    }

    public static function loan($id)
    {
        //Loan
        $loans = Loan::where('employee_id', '=', $id)->get();
        $total_loan = 0;
        foreach ($loans as $loan) {
            $total_loan = $loan->amount + $total_loan;
        }
        $loan_json = json_encode($loans);

        return $loan_json;
    }

    public static function saturation_deduction($id)
    {
        //Statutory Deductions
        $saturation_deductions = SaturationDeduction::where('employee_id', '=', $id)->get();
        $total_saturation_deduction = 0;
        foreach ($saturation_deductions as $saturation_deduction) {
            $total_saturation_deduction = $saturation_deduction->amount + $total_saturation_deduction;

            // Use the current DeductionOption name as title (reflects admin renames).
            // Falls back to the stored title if no linked option exists.
            if (!empty($saturation_deduction->deduction_option)) {
                $optionName = DeductionOption::where('id', $saturation_deduction->deduction_option)->value('name');
                if ($optionName) {
                    $saturation_deduction->title = $optionName;
                }
            }
        }
        $saturation_deduction_json = json_encode($saturation_deductions);

        return $saturation_deduction_json;
    }

    public static function other_payment($id)
    {
        //OtherPayment
        $other_payments = OtherPayment::where('employee_id', '=', $id)->get();
        $total_other_payment = 0;
        foreach ($other_payments as $other_payment) {
            $total_other_payment = $other_payment->amount + $total_other_payment;
        }
        $other_payment_json = json_encode($other_payments);

        return $other_payment_json;
    }

    public static function overtime($id)
    {
        //Overtime
        $over_times = Overtime::where('employee_id', '=', $id)->get();
        $total_over_time = 0;
        foreach ($over_times as $over_time) {
            $total_work = $over_time->number_of_days * $over_time->hours;
            $amount = $total_work * $over_time->rate;
            $total_over_time = $amount + $total_over_time;
        }
        $over_time_json = json_encode($over_times);

        return $over_time_json;
    }

    public static function employee_id()
    {
        $employee = Employee::latest()->first();

        return !empty($employee) ? $employee->id + 1 : 1;
    }

    public function branch()
    {
        return $this->hasOne(Branch::class, 'id', 'branch_id');
    }

    public function phone()
    {
        return $this->hasOne(Employee::class, 'id', 'phone');
    }

    public function department()
    {
        return $this->hasOne(Department::class, 'id', 'department_id');
    }

    public function designation()
    {
        return $this->hasOne(Designation::class, 'id', 'designation_id');
    }

    public function salaryType()
    {
        return $this->hasOne(PayslipType::class, 'id', 'salary_type');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function paySlip()
    {
        return $this->hasOne(PaySlip::class, 'id', 'employee_id');
    }


    public function present_status($employee_id, $data)
    {
        return AttendanceEmployee::where('employee_id', $employee_id)->where('date', $data)->first();
    }
    public static function employee_name($name)
    {

        $employee = Employee::where('id', $name)->first();
        if (!empty($employee)) {
            return $employee->name;
        }
    }


    public static function login_user($name)
    {
        $user = User::where('id', $name)->first();
        return $user->name;
    }

    public static function employee_salary($salary)
    {

        $employee = Employee::where("salary", $salary)->first();
        if ($employee->salary == '0' || $employee->salary == '0.0') {
            return "-";
        } else {
            return $employee->salary;
        }
    }
}
