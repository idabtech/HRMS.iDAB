<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $settlement->settlement_number }} - Full & Final Settlement</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #1e293b; margin: 20px; }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; color: #0f172a; }
        .company-name { font-size: 13px; color: #475569; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        th { background-color: #f1f5f9; font-weight: bold; }
        .sec-title { background-color: #0f172a; color: #ffffff; padding: 5px 8px; font-weight: bold; font-size: 12px; margin-top: 15px; margin-bottom: 5px; }
        .net-box { background: #f8fafc; border: 2px solid #0f172a; padding: 10px; text-align: right; font-size: 14px; font-weight: bold; margin-bottom: 15px; }
        .declaration { font-size: 10px; color: #475569; line-height: 1.4; border: 1px solid #cbd5e1; padding: 8px; margin-bottom: 15px; background: #fafafa; }
        .sig-block { margin-top: 25px; }
        .text-end { text-align: right; }
    </style>
</head>
<body>

<div class="header">
    <table style="border: none; margin: 0;">
        <tr style="border: none;">
            <td style="border: none; width: 60%;">
                <div class="title">FULL & FINAL SETTLEMENT STATEMENT</div>
                <div class="company-name">{{ $company ? $company->name : 'Karma Mark Start Consultancy LLP' }}</div>
            </td>
            <td style="border: none; width: 40%; text-align: right;">
                <div><strong>Settlement #:</strong> {{ $settlement->settlement_number }}</div>
                <div><strong>Date:</strong> {{ $settlement->formatDate(now()) }}</div>
                <div><strong>Status:</strong> {{ ucfirst($settlement->status) }}</div>
            </td>
        </tr>
    </table>
</div>

<div class="sec-title">SECTION A: EMPLOYEE INFORMATION</div>
<table>
    <tr>
        <th width="20%">Employee Name:</th>
        <td width="30%"><strong>{{ $settlement->employee_name }}</strong></td>
        <th width="20%">Employee ID:</th>
        <td width="30%">{{ $settlement->employee_code ?: '-' }}</td>
    </tr>
    <tr>
        <th>Designation:</th>
        <td>{{ $settlement->designation ?: '-' }}</td>
        <th>Department:</th>
        <td>{{ $settlement->department ?: '-' }}</td>
    </tr>
    <tr>
        <th>Date of Joining:</th>
        <td>{{ $settlement->formatDate($settlement->date_of_joining) }}</td>
        <th>Last Working Day:</th>
        <td><strong>{{ $settlement->formatDate($settlement->last_working_day) }}</strong></td>
    </tr>
    <tr>
        <th>Reason for Separation:</th>
        <td colspan="3">{{ $settlement->reason_for_separation }}</td>
    </tr>
</table>

<div class="sec-title">SECTION B: FINANCIAL CLEARANCE BREAKDOWN</div>
<table style="border: none;">
    <tr style="border: none;">
        <td style="border: none; width: 50%; vertical-align: top; padding-right: 5px;">
            <table>
                <thead>
                    <tr><th>Earnings & Additions</th><th class="text-end">Amount</th></tr>
                </thead>
                <tbody>
                    @foreach ($settlement->earnings_data ?? [] as $earn)
                        <tr><td>{{ $earn['name'] }}</td><td class="text-end">{{ $settlement->formatPrice($earn['amount']) }}</td></tr>
                    @endforeach
                    <tr style="font-weight: bold; background: #f8fafc;">
                        <td>Total Gross Payable (A)</td>
                        <td class="text-end">{{ $settlement->formatPrice($settlement->gross_payable) }}</td>
                    </tr>
                </tbody>
            </table>
        </td>
        <td style="border: none; width: 50%; vertical-align: top; padding-left: 5px;">
            <table>
                <thead>
                    <tr><th>Deductions & Recoveries</th><th class="text-end">Amount</th></tr>
                </thead>
                <tbody>
                    @foreach ($settlement->deductions_data ?? [] as $ded)
                        <tr><td>{{ $ded['name'] }}</td><td class="text-end">- {{ $settlement->formatPrice($ded['amount']) }}</td></tr>
                    @endforeach
                    <tr style="font-weight: bold; background: #f8fafc;">
                        <td>Total Deductions (B)</td>
                        <td class="text-end">- {{ $settlement->formatPrice($settlement->total_deductions) }}</td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
</table>

<div class="net-box">
    NET FINAL SETTLEMENT AMOUNT: {{ $settlement->formatPrice($settlement->net_amount) }}
</div>

<div class="sec-title">SECTION C: DEPARTMENTAL & ASSET CLEARANCES</div>
<table>
    <thead>
        <tr>
            <th width="30%">Category</th>
            <th width="50%">Clearance Checkpoint / Asset</th>
            <th width="20%">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($settlement->clearance_data ?? [] as $chk)
            <tr>
                <td>{{ $chk['category'] }}</td>
                <td>
                    <strong>{{ $chk['item'] }}</strong>
                    @if(!empty($chk['remarks']))
                        <br><span style="font-size: 10px; color: #475569;"><em>Note: {{ $chk['remarks'] }}</em></span>
                    @endif
                </td>
                <td>{{ $chk['status'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

@if (!empty($settlement->custom_fields_schema))
<div class="sec-title">SECTION D: CUSTOM QUESTIONNAIRE & RECORDS</div>
<table>
    <thead>
        <tr>
            <th width="50%">Question / Field</th>
            <th width="50%">Response / Value</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($settlement->custom_fields_schema as $field)
            <tr>
                <td><strong>{{ $field['label'] }}</strong></td>
                <td>
                    @if(($field['type'] ?? '') === 'date' && !empty($settlement->custom_fields_data[$field['key']]))
                        {{ $settlement->formatDate($settlement->custom_fields_data[$field['key']]) }}
                    @else
                        {{ $settlement->custom_fields_data[$field['key']] ?? '-' }}
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif

<div class="sec-title">SECTION E: MULTI-STAGE SIGN-OFF & FINAL VERIFICATION</div>
<div class="declaration">
    I confirm that I have completed the required handover and returned all company property, software code, credentials, documents, and data in my possession. Except for the amount stated as payable in this settlement, I have no further claims against the Company. For a period of three (3) months, I will remain reasonably available for handover assistance. I undertake not to copy, replicate, or misuse any company code, designs, or proprietary material.
</div>

<table class="sig-block" style="border: none; width: 100%; margin-top: 15px;">
    <tr style="border: none;">
        {{-- Stage 1: Employee --}}
        <td style="border: 1px solid #cbd5e1; width: 33%; text-align: center; padding: 12px; vertical-align: bottom;">
            @if ($settlement->employee_signature)
                <img src="{{ $settlement->employee_signature }}" style="max-height: 55px; max-width: 90%;"><br>
            @else
                <div style="height: 55px;"></div>
            @endif
            <strong>{{ $settlement->employee_name }}</strong><br>
            <span style="font-size: 10px; color: #64748b;">(Separating Employee)</span><br>
            <span style="font-size: 9px; color: #64748b;">
                {{ $settlement->employee_signed_at ? 'Signed: ' . $settlement->formatDate($settlement->employee_signed_at, true) : 'Signature Pending' }}
            </span>
            @if($settlement->employee_signed_ip)
                <br><span style="font-size: 8px; color: #94a3b8;">IP: {{ $settlement->employee_signed_ip }}</span>
            @endif
        </td>

        {{-- Stage 2: Manager / HOD --}}
        <td style="border: 1px solid #cbd5e1; width: 33%; text-align: center; padding: 12px; vertical-align: bottom;">
            @if ($settlement->manager_signature)
                <img src="{{ $settlement->manager_signature }}" style="max-height: 55px; max-width: 90%;"><br>
            @else
                <div style="height: 55px;"></div>
            @endif
            <strong>{{ $settlement->manager_name ?: 'Department Manager / HOD' }}</strong><br>
            <span style="font-size: 10px; color: #64748b;">(Department Handover Verification)</span><br>
            <span style="font-size: 9px; color: #64748b;">
                {{ $settlement->manager_signed_at ? 'Countersigned: ' . $settlement->formatDate($settlement->manager_signed_at, true) : 'Countersign Pending' }}
            </span>
        </td>

        {{-- Stage 3: Management Signatory --}}
        <td style="border: 1px solid #cbd5e1; width: 34%; text-align: center; padding: 12px; vertical-align: bottom;">
            @if ($settlement->authorized_signature)
                <img src="{{ $settlement->authorized_signature }}" style="max-height: 55px; max-width: 90%;"><br>
            @else
                <div style="height: 55px;"></div>
            @endif
            <strong>{{ $settlement->authorized_signatory_name ?: 'Authorized Signatory' }}</strong><br>
            <span style="font-size: 10px; color: #64748b;">(Management / HR Final Sign-off)</span><br>
            <span style="font-size: 9px; color: #64748b;">
                {{ $settlement->final_settlement_status === 'Cleared' ? 'Cleared & Disbursed' : 'Disbursal Pending' }}
                @if($settlement->payment_date)
                    ({{ $settlement->formatDate($settlement->payment_date) }})
                @endif
            </span>
            @if($settlement->payment_reference_no)
                <br><span style="font-size: 8px; color: #64748b;">Ref: {{ $settlement->payment_reference_no }}</span>
            @endif
        </td>
    </tr>
</table>

<script>
    window.onload = function() { window.print(); }
</script>

</body>
</html>