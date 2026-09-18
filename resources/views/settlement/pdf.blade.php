<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $settlement->settlement_number }} - Full & Final Settlement</title>
    @php
        $creatorId = $settlement->created_by ?: (\Auth::check() ? \Auth::user()->creatorId() : 1);
        $settings = \App\Models\Utility::getCompanySettings($creatorId);

        // 1. Company Name & Contact Info
        $companyName = !empty($settings['company_name']) ? $settings['company_name'] : ($company ? $company->name : 'N/A');
        $companyAddress = !empty($settings['company_address']) ? $settings['company_address'] : '';
        $companyCity = !empty($settings['company_city']) ? $settings['company_city'] : '';
        $companyState = !empty($settings['company_state']) ? $settings['company_state'] : '';
        $companyZip = !empty($settings['company_zipcode']) ? $settings['company_zipcode'] : '';
        $companyPhone = !empty($settings['company_telephone']) ? $settings['company_telephone'] : '';
        $companyEmail = !empty($settings['company_email']) ? $settings['company_email'] : '';

        $addrParts = array_filter([$companyAddress, $companyCity, $companyState . ($companyZip ? ' - ' . $companyZip : '')]);
        $fullAddress = implode(', ', $addrParts);

        // 2. Company Logo
        $logoBase = \App\Models\Utility::get_file('uploads/logo/');
        $logoFile = !empty($settings['company_logo']) ? $settings['company_logo'] : (!empty($settings['dark_logo']) ? $settings['dark_logo'] : 'logo-dark.png');
        $logoUrl = $logoBase . $logoFile;

        // 3. Dynamic Theme Color (Matching Company Admin Panel)
        $color = !empty($settings['theme_color']) ? $settings['theme_color'] : 'theme-2';
        if (isset($settings['color_flag']) && $settings['color_flag'] == 'true') {
            $themeColorHex = !empty($settings['color']) ? $settings['color'] : (!empty($color) ? $color : '#584ed2');
        } else {
            $themeHexMap = [
                'theme-1'  => '#0CAF60',
                'theme-2'  => '#584ED2',
                'theme-3'  => '#6FD943',
                'theme-4'  => '#145388',
                'theme-5'  => '#B94065',
                'theme-6'  => '#008ECB',
                'theme-7'  => '#7A3F93',
                'theme-8'  => '#C6A44E',
                'theme-9'  => '#42474C',
                'theme-10' => '#127384',
            ];
            $themeColorHex = isset($themeHexMap[$color]) ? $themeHexMap[$color] : '#584ed2';
        }

        // Convert HEX to RGB for background tints
        $cleanHex = ltrim($themeColorHex, '#');
        if (strlen($cleanHex) == 3) {
            $r = hexdec(substr($cleanHex, 0, 1) . substr($cleanHex, 0, 1));
            $g = hexdec(substr($cleanHex, 1, 1) . substr($cleanHex, 1, 1));
            $b = hexdec(substr($cleanHex, 2, 1) . substr($cleanHex, 2, 1));
        } else if (strlen($cleanHex) == 6) {
            $r = hexdec(substr($cleanHex, 0, 2));
            $g = hexdec(substr($cleanHex, 2, 2));
            $b = hexdec(substr($cleanHex, 4, 2));
        } else {
            $r = 88; $g = 78; $b = 210;
        }
        $themeRgb = "$r, $g, $b";
    @endphp
    <style>
        :root {
            --theme-color: {{ $themeColorHex }};
            --theme-color-rgb: {{ $themeRgb }};
            --theme-light: rgba({{ $themeRgb }}, 0.08);
            --theme-border: rgba({{ $themeRgb }}, 0.28);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif;
            font-size: 11.5px;
            color: #1e293b;
            margin: 0;
            padding: 25px 30px;
            background: #ffffff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Top Action Bar (hidden when printing) */
        .no-print-bar {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 10px 18px;
            border-radius: 8px;
            margin-bottom: 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid transparent;
            transition: all 0.2s;
        }
        .btn-print {
            background-color: var(--theme-color);
            color: #ffffff;
            border-color: var(--theme-color);
        }
        .btn-print:hover {
            opacity: 0.9;
        }
        .btn-close-win {
            background-color: #ffffff;
            color: #475569;
            border-color: #cbd5e1;
        }
        .btn-close-win:hover {
            background-color: #f1f5f9;
        }

        /* Document Header */
        .header {
            border-bottom: 2.5px solid var(--theme-color);
            padding-bottom: 14px;
            margin-bottom: 18px;
        }
        .company-name {
            font-size: 17px;
            font-weight: 800;
            color: var(--theme-color);
            line-height: 1.2;
            letter-spacing: -0.2px;
        }
        .doc-title {
            font-size: 15px;
            font-weight: 800;
            color: var(--theme-color);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Tables & Elements */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 6px 9px;
            text-align: left;
            vertical-align: middle;
        }
        th {
            background-color: var(--theme-light);
            color: var(--theme-color);
            font-weight: 700;
            font-size: 11px;
        }
        .sec-title {
            background-color: var(--theme-color);
            color: #ffffff;
            padding: 6px 10px;
            font-weight: 700;
            font-size: 11.5px;
            letter-spacing: 0.5px;
            margin-top: 16px;
            margin-bottom: 6px;
            border-radius: 3px;
        }
        .net-box {
            background: var(--theme-light);
            border: 2px solid var(--theme-color);
            color: var(--theme-color);
            padding: 12px 16px;
            text-align: right;
            font-size: 14px;
            font-weight: 800;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .status-pill {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            background-color: var(--theme-light);
            color: var(--theme-color);
            border: 1px solid var(--theme-border);
        }
        .declaration {
            font-size: 9.5px;
            color: #475569;
            line-height: 1.45;
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            margin-bottom: 14px;
            background: #fafafa;
            border-radius: 4px;
        }
        .sig-block {
            margin-top: 20px;
        }
        .text-end {
            text-align: right;
        }

        @media print {
            .no-print, .no-print-bar {
                display: none !important;
            }
            body {
                padding: 0;
                margin: 0;
            }
            @page {
                margin: 12mm 14mm;
                size: auto;
            }
            .sec-title, th, .net-box, .status-pill {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

{{-- Non-printable floating toolbar --}}
<div class="no-print-bar no-print">
    <div style="font-size: 12px; color: #475569;">
        <strong>{{ __('Full & Final Settlement Statement') }}</strong> &mdash; <span>{{ $settlement->settlement_number }}</span>
    </div>
    <div style="display: flex; gap: 8px;">
        <button type="button" class="btn-action btn-print" onclick="window.print()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
            {{ __('Print / Save as PDF') }}
        </button>
        <button type="button" class="btn-action btn-close-win" onclick="window.close()">
            {{ __('Close') }}
        </button>
    </div>
</div>

{{-- Header: Company Info, Logo & Settlement Metadata --}}
<div class="header">
    <table style="border: none; margin: 0; border-collapse: collapse;">
        <tr style="border: none;">
            {{-- Left: Logo + Company Info --}}
            <td style="border: none; width: 62%; vertical-align: top; padding: 0;">
                <table style="border: none; margin: 0; border-collapse: collapse;">
                    <tr style="border: none;">
                        <td style="border: none; padding: 0 14px 0 0; vertical-align: middle; width: 1%;">
                            <img src="{{ $logoUrl }}"
                                 alt="{{ $companyName }}"
                                 style="max-height: 54px; max-width: 170px; object-fit: contain; display: block;"
                                 onerror="this.style.display='none';">
                        </td>
                        <td style="border: none; padding: 0; vertical-align: middle;">
                            <div class="company-name">{{ $companyName }}</div>
                            @if(!empty($fullAddress))
                                <div style="font-size: 10px; color: #64748b; margin-top: 3px; line-height: 1.35;">{{ $fullAddress }}</div>
                            @endif
                            @if(!empty($companyPhone) || !empty($companyEmail))
                                <div style="font-size: 10px; color: #64748b; margin-top: 2px;">
                                    @if(!empty($companyPhone)) <span><strong>Phone:</strong> {{ $companyPhone }}</span> @endif
                                    @if(!empty($companyPhone) && !empty($companyEmail)) <span> &bull; </span> @endif
                                    @if(!empty($companyEmail)) <span><strong>Email:</strong> {{ $companyEmail }}</span> @endif
                                </div>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>

            {{-- Right: Document Title & Reference Details --}}
            <td style="border: none; width: 38%; text-align: right; vertical-align: top; padding: 0;">
                <div class="doc-title">{{ __('Full & Final Settlement') }}</div>
                <div style="margin-top: 5px; font-size: 11px; line-height: 1.55; color: #334155;">
                    <div><strong>Settlement Ref:</strong> <span style="font-family: monospace; font-weight: bold; color: var(--theme-color);">{{ $settlement->settlement_number }}</span></div>
                    <div><strong>Statement Date:</strong> {{ $settlement->formatDate(now()) }}</div>
                    <div style="margin-top: 3px;">
                        <strong>Status:</strong>
                        <span class="status-pill">{{ ucfirst($settlement->status) }}</span>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- SECTION A: EMPLOYEE INFORMATION --}}
<div class="sec-title">SECTION A: EMPLOYEE & SEPARATION INFORMATION</div>
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
        <td><strong style="color: #dc2626;">{{ $settlement->formatDate($settlement->last_working_day) }}</strong></td>
    </tr>
    <tr>
        <th>Reason for Separation:</th>
        <td colspan="3">{{ $settlement->reason_for_separation ?: 'End of Tenure / Resignation' }}</td>
    </tr>
</table>

{{-- SECTION B: FINANCIAL CLEARANCE BREAKDOWN --}}
<div class="sec-title">SECTION B: FINANCIAL CLEARANCE BREAKDOWN</div>
<table style="border: none; margin-bottom: 8px;">
    <tr style="border: none;">
        <td style="border: none; width: 50%; vertical-align: top; padding: 0 5px 0 0;">
            <table>
                <thead>
                    <tr><th>Earnings & Additions (A)</th><th class="text-end">Amount</th></tr>
                </thead>
                <tbody>
                    @foreach ($settlement->earnings_data ?? [] as $earn)
                        <tr><td>{{ $earn['name'] }}</td><td class="text-end">{{ $settlement->formatPrice($earn['amount']) }}</td></tr>
                    @endforeach
                    <tr style="font-weight: bold; background: rgba(16, 185, 129, 0.08);">
                        <td>Total Gross Payable (A)</td>
                        <td class="text-end" style="color: #047857;">{{ $settlement->formatPrice($settlement->gross_payable) }}</td>
                    </tr>
                </tbody>
            </table>
        </td>
        <td style="border: none; width: 50%; vertical-align: top; padding: 0 0 0 5px;">
            <table>
                <thead>
                    <tr><th>Deductions & Recoveries (B)</th><th class="text-end">Amount</th></tr>
                </thead>
                <tbody>
                    @foreach ($settlement->deductions_data ?? [] as $ded)
                        <tr><td>{{ $ded['name'] }}</td><td class="text-end">- {{ $settlement->formatPrice($ded['amount']) }}</td></tr>
                    @endforeach
                    <tr style="font-weight: bold; background: rgba(239, 68, 68, 0.08);">
                        <td>Total Deductions (B)</td>
                        <td class="text-end" style="color: #b91c1c;">- {{ $settlement->formatPrice($settlement->total_deductions) }}</td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
</table>

<div class="net-box">
    NET FINAL SETTLEMENT PAYABLE: {{ $settlement->formatPrice($settlement->net_amount) }}
</div>

{{-- SECTION C: DEPARTMENTAL & ASSET CLEARANCES --}}
<div class="sec-title">SECTION C: DEPARTMENTAL & ASSET CLEARANCES</div>
<table>
    <thead>
        <tr>
            <th width="28%">Category</th>
            <th width="52%">Clearance Checkpoint / Asset</th>
            <th width="20%">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($settlement->clearance_data ?? [] as $chk)
            <tr>
                <td><strong>{{ $chk['category'] }}</strong></td>
                <td>
                    <strong>{{ $chk['item'] }}</strong>
                    @if(!empty($chk['remarks']))
                        <br><span style="font-size: 9.5px; color: #64748b;"><em>Handover Note: {{ $chk['remarks'] }}</em></span>
                    @endif
                </td>
                <td>
                    @if($chk['status'] === 'Returned')
                        <span style="color: #047857; font-weight: 700;">&#10003; {{ __('Returned / Cleared') }}</span>
                    @elseif($chk['status'] === 'Not Applicable')
                        <span style="color: #64748b;">{{ __('Not Applicable') }}</span>
                    @else
                        <span style="color: #d97706; font-weight: 700;">&#9203; {{ __('Pending') }}</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- SECTION D: CUSTOM QUESTIONNAIRE & RECORDS (If configured) --}}
@if (!empty($settlement->custom_fields_schema))
<div class="sec-title">SECTION D: CUSTOM QUESTIONNAIRE & ADDITIONAL RECORDS</div>
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

{{-- SECTION E: MULTI-STAGE SIGN-OFF & FINAL VERIFICATION --}}
<div class="sec-title">SECTION E: MULTI-STAGE SIGN-OFF & FINAL VERIFICATION</div>
<div class="declaration">
    I confirm that I have completed the required handover and returned all company property, software code, credentials, documents, and data in my possession. Except for the amount stated as payable in this settlement, I have no further claims against the Company. For a period of three (3) months, I will remain reasonably available for handover assistance. I undertake not to copy, replicate, or misuse any company code, designs, or proprietary material.
</div>

<table class="sig-block" style="border: none; width: 100%; margin-top: 12px; border-collapse: collapse;">
    <tr style="border: none;">
        {{-- Stage 1: Employee --}}
        <td style="border: 1px solid #cbd5e1; width: 33.33%; text-align: center; padding: 10px; vertical-align: bottom; background: #ffffff;">
            @if ($settlement->employee_signature)
                <img src="{{ $settlement->employee_signature }}" style="max-height: 50px; max-width: 90%; object-fit: contain;"><br>
            @else
                <div style="height: 50px; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 11px;">[ {{ __('Pending') }} ]</div>
            @endif
            <strong style="color: #1e293b;">{{ $settlement->employee_name }}</strong><br>
            <span style="font-size: 9.5px; color: #64748b;">(Separating Employee)</span><br>
            <span style="font-size: 9px; color: #64748b;">
                {{ $settlement->employee_signed_at ? 'Signed: ' . $settlement->formatDate($settlement->employee_signed_at, true) : 'Signature Pending' }}
            </span>
            @if($settlement->employee_signed_ip)
                <br><span style="font-size: 8px; color: #94a3b8;">IP: {{ $settlement->employee_signed_ip }}</span>
            @endif
        </td>

        {{-- Stage 2: Manager / HOD --}}
        <td style="border: 1px solid #cbd5e1; width: 33.33%; text-align: center; padding: 10px; vertical-align: bottom; background: #ffffff;">
            @if ($settlement->manager_signature)
                <img src="{{ $settlement->manager_signature }}" style="max-height: 50px; max-width: 90%; object-fit: contain;"><br>
            @else
                <div style="height: 50px; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 11px;">[ {{ __('Pending') }} ]</div>
            @endif
            <strong style="color: #1e293b;">{{ $settlement->manager_name ?: 'Department Manager / HOD' }}</strong><br>
            <span style="font-size: 9.5px; color: #64748b;">(Department Verification)</span><br>
            <span style="font-size: 9px; color: #64748b;">
                {{ $settlement->manager_signed_at ? 'Countersigned: ' . $settlement->formatDate($settlement->manager_signed_at, true) : 'Countersign Pending' }}
            </span>
        </td>

        {{-- Stage 3: Management Signatory --}}
        <td style="border: 1px solid #cbd5e1; width: 33.34%; text-align: center; padding: 10px; vertical-align: bottom; background: #ffffff;">
            @if ($settlement->authorized_signature)
                <img src="{{ $settlement->authorized_signature }}" style="max-height: 50px; max-width: 90%; object-fit: contain;"><br>
            @else
                <div style="height: 50px; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 11px;">[ {{ __('Pending') }} ]</div>
            @endif
            <strong style="color: #1e293b;">{{ $settlement->authorized_signatory_name ?: 'Authorized Signatory' }}</strong><br>
            <span style="font-size: 9.5px; color: #64748b;">(Management / Finance Sign-off)</span><br>
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
    window.onload = function() {
        // Automatically trigger print dialog after page renders
        window.print();
    }
</script>

</body>
</html>