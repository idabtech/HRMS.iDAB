<?php

namespace App\Exports;

use App\Models\FullAndFinalSettlement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SettlementExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $creatorId;
    protected $filters;

    public function __construct($creatorId, $filters = [])
    {
        $this->creatorId = $creatorId;
        $this->filters = is_array($filters) ? $filters : ['status' => $filters];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = FullAndFinalSettlement::where('created_by', $this->creatorId)
            ->with(['employee'])
            ->orderBy('id', 'desc');

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['employee_id'])) {
            $query->where('employee_id', $this->filters['employee_id']);
        }

        if (!empty($this->filters['disbursal_status'])) {
            $query->where('final_settlement_status', $this->filters['disbursal_status']);
        }

        if (!empty($this->filters['start_date'])) {
            $query->whereDate('last_working_day', '>=', $this->filters['start_date']);
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereDate('last_working_day', '<=', $this->filters['end_date']);
        }

        if (!empty($this->filters['search'])) {
            $term = $this->filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('settlement_number', 'like', "%{$term}%")
                  ->orWhere('employee_name', 'like', "%{$term}%")
                  ->orWhere('employee_code', 'like', "%{$term}%")
                  ->orWhere('department', 'like', "%{$term}%");
            });
        }

        $records = $query->get();

        return $records->map(function ($s) {
            return [
                'settlement_number' => $s->settlement_number,
                'employee_code' => $s->employee_code ?: ($s->employee?->employee_id ?? '-'),
                'employee_name' => $s->employee_name,
                'department' => $s->department ?: '-',
                'designation' => $s->designation ?: '-',
                'date_of_joining' => $s->date_of_joining ? $s->date_of_joining->format('Y-m-d') : '-',
                'last_working_day' => $s->last_working_day ? $s->last_working_day->format('Y-m-d') : '-',
                'gross_payable' => number_format((float)$s->gross_payable, 2, '.', ''),
                'total_deductions' => number_format((float)$s->total_deductions, 2, '.', ''),
                'net_amount' => number_format((float)$s->net_amount, 2, '.', ''),
                'status' => ucfirst($s->status),
                'employee_signed_at' => $s->employee_signed_at ? $s->employee_signed_at->format('Y-m-d H:i') : 'Pending',
                'manager_signoff' => $s->manager_signed_at ? ($s->manager_name . ' (' . $s->manager_signed_at->format('Y-m-d') . ')') : 'Pending',
                'payment_status' => $s->final_settlement_status ?: 'Pending',
                'payment_mode' => $s->payment_mode ?: '-',
                'payment_date' => $s->payment_date ? $s->payment_date->format('Y-m-d') : '-',
                'payment_reference_no' => $s->payment_reference_no ?: '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Settlement #',
            'Employee Code',
            'Employee Name',
            'Department',
            'Designation',
            'Date of Joining',
            'Last Working Day',
            'Gross Payable',
            'Total Deductions',
            'Net Amount',
            'Settlement Status',
            'Employee Signed At',
            'Manager Countersign',
            'Disbursal Status',
            'Payment Mode',
            'Payment Date',
            'Transaction Ref (UTR)',
        ];
    }
}
