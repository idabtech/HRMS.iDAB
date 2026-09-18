<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Full & Final Settlement') }} - {{ $settlement->settlement_number }}</title>
    @php
        $creatorId = $settlement->created_by ?: (\Auth::check() ? \Auth::user()->creatorId() : 1);
        $companySettings = \App\Models\Utility::getCompanySettings($creatorId);
        $companyName = !empty($companySettings['company_name']) ? $companySettings['company_name'] : ($company ? $company->name : 'N/A');
        $companyLogoBase = \App\Models\Utility::get_file('uploads/logo/');
        $companyLogoFile = !empty($companySettings['company_logo']) ? $companySettings['company_logo'] : (!empty($companySettings['dark_logo']) ? $companySettings['dark_logo'] : 'logo-dark.png');
        $companyLogoUrl = $companyLogoBase . $companyLogoFile;

        $color = !empty($companySettings['theme_color']) ? $companySettings['theme_color'] : 'theme-2';
        if (isset($companySettings['color_flag']) && $companySettings['color_flag'] == 'true') {
            $themeColorHex = !empty($companySettings['color']) ? $companySettings['color'] : (!empty($color) ? $color : '#584ed2');
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

        $isReadOnly = ($settlement->status === 'signed' || $settlement->status === 'cleared');
        $allCustomFields = $settlement->custom_fields_schema ?? [];
        $customValues = $settlement->custom_fields_data ?? [];

        $fieldsBySection = [
            'separation' => [],
            'financial' => [],
            'assets' => [],
            'employee' => [],
        ];
        foreach ($allCustomFields as $f) {
            $sec = $f['section'] ?? 'employee';
            if (isset($fieldsBySection[$sec])) {
                $fieldsBySection[$sec][] = $f;
            } else {
                $fieldsBySection['employee'][] = $f;
            }
        }
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/flatpickr.min.css') }}">
    <style>
        :root {
            --primary-color: {{ $themeColorHex }};
            --primary-light: #eef2ff;
            --success-color: #0ea5e9;
            --dark-color: #1e293b;
            --border-color: #e2e8f0;
            --bg-page: #f8fafc;
        }
        body {
            background-color: var(--bg-page);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #334155;
            -webkit-font-smoothing: antialiased;
        }
        .document-wrapper {
            max-width: 960px;
            margin: 35px auto 60px auto;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 10px 35px rgba(15, 23, 42, 0.08);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }
        .document-header {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
            color: #ffffff;
            padding: 35px 40px;
        }
        .document-body {
            padding: 40px;
        }
        .section-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            margin-bottom: 30px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        }
        .section-card-header {
            background-color: #f8fafc;
            border-bottom: 1px solid var(--border-color);
            padding: 16px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .section-card-header h5 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-card-body {
            padding: 24px;
        }
        .info-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .info-value {
            font-size: 0.95rem;
            color: #0f172a;
            font-weight: 600;
        }
        .table-financial {
            width: 100%;
            margin-bottom: 0;
        }
        .table-financial th {
            background: #f1f5f9;
            color: #475569;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 14px;
            border-bottom: 1px solid var(--border-color);
        }
        .table-financial td {
            padding: 10px 14px;
            font-size: 0.92rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .net-pay-strip {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: #ffffff;
            border-radius: 8px;
            padding: 20px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 20px;
        }
        .checklist-group {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 16px;
            overflow: hidden;
        }
        .checklist-group-header {
            background: #f8fafc;
            padding: 12px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #e2e8f0;
        }
        .checklist-item {
            padding: 14px 18px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 15px;
            transition: background-color 0.2s ease;
        }
        .checklist-item.is-cleared {
            background-color: #f0fdf4;
        }
        .checklist-item:last-child {
            border-bottom: none;
        }
        .clearance-chk {
            width: 20px;
            height: 20px;
            margin-top: 2px;
            cursor: pointer;
            accent-color: #10b981;
            flex-shrink: 0;
        }
        .clearance-comment-input {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 0.85rem;
            padding: 6px 12px;
            margin-top: 6px;
            width: 100%;
            max-width: 550px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .clearance-comment-input:focus {
            border-color: var(--primary-color, #584ed2);
            box-shadow: 0 0 0 3px rgba(88, 78, 210, 0.12);
        }
        .signature-tab-btn {
            border: 1px solid var(--border-color);
            background: #f8fafc;
            color: #475569;
            padding: 9px 18px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .signature-tab-btn.active {
            background: var(--primary-color, #584ed2);
            color: #ffffff;
            border-color: var(--primary-color, #584ed2);
        }
        .sig-canvas-wrapper {
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            position: relative;
            height: 180px;
        }
        .sig-canvas {
            width: 100%;
            height: 100%;
            cursor: crosshair;
            display: block;
            touch-action: none;
            user-select: none;
            -webkit-user-select: none;
        }
        .upload-dropzone {
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            background: #f8fafc;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.2s;
        }
        .upload-dropzone:hover {
            border-color: var(--primary-color, #584ed2);
            background: #f1f5f9;
        }
        .signature-preview-box {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            display: inline-block;
        }
        .undertaking-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #3b82f6;
            border-radius: 6px;
            padding: 18px 20px;
            font-size: 0.9rem;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

@php
    $clearanceList = $settlement->clearance_data ?? [];
    $totalChecklistItems = count($clearanceList);
    $clearedCount = 0;
    $checklistByCategory = [];

    foreach ($clearanceList as $idx => $chk) {
        $cat = !empty($chk['category']) ? $chk['category'] : __('General Clearance');
        $chk['_orig_idx'] = $idx;
        $checklistByCategory[$cat][] = $chk;
        if (($chk['status'] ?? '') === 'Returned') {
            $clearedCount++;
        }
    }

    $clearancePercentage = $totalChecklistItems > 0 ? round(($clearedCount / $totalChecklistItems) * 100) : 100;
@endphp

<div class="container">
    <div class="document-wrapper">
        {{-- Executive Header --}}
        <div class="document-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="d-flex align-items-center gap-3 mb-2 flex-wrap">
                        <img src="{{ $companyLogoUrl }}" alt="{{ $companyName }}" style="max-height: 42px; max-width: 170px; object-fit: contain; filter: brightness(0) invert(1);" onerror="this.style.display='none'">
                        <span class="badge bg-white text-dark px-3 py-1 fw-bold text-uppercase" style="letter-spacing: 0.5px;">
                            {{ __('Official Clearance Form') }}
                        </span>
                    </div>
                    <h2 class="fw-bold mb-1 text-white">{{ __('FULL & FINAL SETTLEMENT') }}</h2>
                    <div class="text-white-50 fs-6">
                        {{ $companyName }}
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0">
                    <div class="d-inline-block bg-white bg-opacity-10 rounded px-3 py-2 border border-white border-opacity-25">
                        <small class="text-white-50 d-block text-uppercase" style="font-size: 11px;">{{ __('Settlement Ref No.') }}</small>
                        <span class="fs-5 fw-bold text-white font-monospace">{{ $settlement->settlement_number }}</span>
                    </div>
                    <div class="mt-2 text-white-50 small">
                        <i class="ti ti-calendar me-1"></i> {{ __('Issued: ') . $settlement->formatDate($settlement->created_at) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="document-body">
            {{-- Friendly Status Notification --}}
            <div class="alert alert-primary d-flex align-items-center mb-4 py-3 px-4 border-0 rounded-3" style="background-color: #e0e7ff; color: #312e81;">
                <i class="ti ti-info-circle fs-2 me-3 flex-shrink-0"></i>
                <div>
                    <strong>{{ __('Dear ') . $settlement->employee_name . ',' }}</strong>
                    <div class="small mt-1" style="line-height: 1.5;">
                        {{ __('Please review your certified separation details, financial calculations, and departmental clearance checklist below. To complete your separation formalities, please leave any comments and submit your signature.') }}
                    </div>
                </div>
            </div>

            {{-- 1. EMPLOYEE & TENURE DETAILS --}}
            <div class="section-card">
                <div class="section-card-header">
                    <h5><i class="ti ti-user-circle text-primary fs-4"></i> {{ __('1. Employee & Separation Details') }}</h5>
                    <span class="badge bg-light-secondary text-secondary px-3 py-1">{{ __('Employee Profile') }}</span>
                </div>
                <div class="section-card-body">
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-4">
                            <div class="info-label">{{ __('Employee Name') }}</div>
                            <div class="info-value fw-bold text-dark">{{ $settlement->employee_name }}</div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="info-label">{{ __('Employee ID / Code') }}</div>
                            <div class="info-value font-monospace">{{ $settlement->employee_code ?: '—' }}</div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="info-label">{{ __('Designation') }}</div>
                            <div class="info-value">{{ $settlement->designation ?: '—' }}</div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="info-label">{{ __('Department') }}</div>
                            <div class="info-value">
                                <span class="badge bg-light text-primary border px-2.5 py-1">{{ $settlement->department ?: 'General' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="info-label">{{ __('Date of Joining') }}</div>
                            <div class="info-value">{{ $settlement->formatDate($settlement->date_of_joining) }}</div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="info-label">{{ __('Last Working Day') }}</div>
                            <div class="info-value text-danger">
                                <i class="ti ti-calendar-event me-1"></i>
                                {{ $settlement->formatDate($settlement->last_working_day) }}
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="info-label">{{ __('Separation Reason') }}</div>
                            <div class="info-value text-dark">{{ $settlement->reason_for_separation ?: __('Resignation / End of Tenure') }}</div>
                        </div>
                    </div>

                    {{-- Section 1 Custom Questions & Answers --}}
                    @if (!empty($fieldsBySection['separation']))
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="fw-bold text-dark mb-3"><i class="ti ti-forms text-primary me-2"></i>{{ __('Additional Separation Details:') }}</h6>
                            <div class="row g-3">
                                @foreach ($fieldsBySection['separation'] as $field)
                                    @php
                                        $isEmployeeTarget = ($field['target'] ?? 'hr') === 'employee';
                                        $val = $customValues[$field['key']] ?? '';
                                    @endphp
                                    <div class="col-md-{{ ($field['type'] ?? '') === 'textarea' ? '12' : '6' }}">
                                        @if (!$isReadOnly && $isEmployeeTarget)
                                            <label class="form-label fw-semibold small text-dark mb-1">
                                                {{ $field['label'] }}
                                                @if(!empty($field['required'])) <span class="text-danger">*</span> @endif
                                            </label>
                                            @if(($field['type'] ?? '') === 'textarea')
                                                <textarea name="custom_fields[{{ $field['key'] }}]" form="settlementClearanceForm" class="form-control form-control-sm" rows="2" placeholder="{{ __('Enter ') . strtolower($field['label']) }}" {{ !empty($field['required']) ? 'required' : '' }}>{{ $val }}</textarea>
                                            @elseif(($field['type'] ?? '') === 'select')
                                                <select name="custom_fields[{{ $field['key'] }}]" form="settlementClearanceForm" class="form-select form-select-sm" {{ !empty($field['required']) ? 'required' : '' }}>
                                                    <option value="">{{ __('Please select an option') }}</option>
                                                    @foreach($field['options'] ?? [] as $opt)
                                                        <option value="{{ $opt }}" {{ $val == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif(($field['type'] ?? '') === 'date')
                                                <input type="date" name="custom_fields[{{ $field['key'] }}]" form="settlementClearanceForm" class="form-control form-control-sm" value="{{ $val }}" {{ !empty($field['required']) ? 'required' : '' }}>
                                            @elseif(($field['type'] ?? '') === 'number')
                                                <input type="number" step="any" name="custom_fields[{{ $field['key'] }}]" form="settlementClearanceForm" class="form-control form-control-sm" placeholder="{{ __('e.g., 12345') }}" value="{{ $val }}" {{ !empty($field['required']) ? 'required' : '' }}>
                                            @else
                                                <input type="text" name="custom_fields[{{ $field['key'] }}]" form="settlementClearanceForm" class="form-control form-control-sm" placeholder="{{ __('Enter ') . strtolower($field['label']) }}" value="{{ $val }}" {{ !empty($field['required']) ? 'required' : '' }}>
                                            @endif
                                        @else
                                            <div class="p-2.5 bg-light rounded border">
                                                <small class="text-muted d-block mb-1">{{ $field['label'] }}:</small>
                                                <strong class="text-dark small">
                                                    @if(($field['type'] ?? '') === 'date' && !empty($val))
                                                        {{ $settlement->formatDate($val) }}
                                                    @else
                                                        {{ !empty($val) ? $val : '—' }}
                                                    @endif
                                                </strong>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 2. FINANCIAL STATEMENT --}}
            <div class="section-card">
                <div class="section-card-header">
                    <h5><i class="ti ti-calculator text-primary fs-4"></i> {{ __('2. Financial Clearance Breakdown') }}</h5>
                    <span class="badge bg-light-primary text-primary px-3 py-1">{{ __('Certified by Accounts') }}</span>
                </div>
                <div class="section-card-body">
                    <div class="row g-4">
                        {{-- Earnings --}}
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 bg-light bg-opacity-50">
                                <h6 class="fw-bold text-success mb-3 d-flex align-items-center">
                                    <i class="ti ti-circle-plus me-2"></i> {{ __('Earnings & Entitlements (A)') }}
                                </h6>
                                <table class="table-financial">
                                    <tbody>
                                        @forelse ($settlement->earnings_data ?? [] as $earn)
                                            <tr>
                                                <td class="text-secondary">{{ $earn['name'] }}</td>
                                                <td class="text-end fw-semibold">{{ $settlement->formatPrice($earn['amount']) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-muted text-center py-2">{{ __('No custom earnings specified') }}</td>
                                            </tr>
                                        @endforelse
                                        <tr class="border-top">
                                            <td class="fw-bold text-dark pt-2">{{ __('Gross Payable (A)') }}</td>
                                            <td class="text-end fw-bold text-success pt-2">{{ $settlement->formatPrice($settlement->gross_payable) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Deductions --}}
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 bg-light bg-opacity-50">
                                <h6 class="fw-bold text-danger mb-3 d-flex align-items-center">
                                    <i class="ti ti-circle-minus me-2"></i> {{ __('Deductions & Recoveries (B)') }}
                                </h6>
                                <table class="table-financial">
                                    <tbody>
                                        @forelse ($settlement->deductions_data ?? [] as $ded)
                                            <tr>
                                                <td class="text-secondary">{{ $ded['name'] }}</td>
                                                <td class="text-end fw-semibold text-danger">- {{ $settlement->formatPrice($ded['amount']) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-muted text-center py-2">{{ __('No deductions applicable') }}</td>
                                            </tr>
                                        @endforelse
                                        <tr class="border-top">
                                            <td class="fw-bold text-dark pt-2">{{ __('Total Deductions (B)') }}</td>
                                            <td class="text-end fw-bold text-danger pt-2">- {{ $settlement->formatPrice($settlement->total_deductions) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Net Payable Highlight Strip --}}
                    <div class="net-pay-strip">
                        <div>
                            <div class="text-uppercase small fw-semibold" style="letter-spacing: 0.5px; opacity: 0.9;">
                                {{ __('Net Final Payable Amount (A - B)') }}
                            </div>
                            <small class="opacity-75">{{ __('Final settlement balance calculated for transfer') }}</small>
                        </div>
                        <div class="fs-2 fw-bold text-white">
                            {{ $settlement->formatPrice($settlement->net_amount) }}
                        </div>
                    </div>

                    {{-- Section 2 Custom Questions & Answers --}}
                    @if (!empty($fieldsBySection['financial']))
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="fw-bold text-dark mb-3"><i class="ti ti-forms text-primary me-2"></i>{{ __('Additional Financial Details & Notes:') }}</h6>
                            <div class="row g-3">
                                @foreach ($fieldsBySection['financial'] as $field)
                                    @php
                                        $isEmployeeTarget = ($field['target'] ?? 'hr') === 'employee';
                                        $val = $customValues[$field['key']] ?? '';
                                    @endphp
                                    <div class="col-md-{{ ($field['type'] ?? '') === 'textarea' ? '12' : '6' }}">
                                        @if (!$isReadOnly && $isEmployeeTarget)
                                            <label class="form-label fw-semibold small text-dark mb-1">
                                                {{ $field['label'] }}
                                                @if(!empty($field['required'])) <span class="text-danger">*</span> @endif
                                            </label>
                                            @if(($field['type'] ?? '') === 'textarea')
                                                <textarea name="custom_fields[{{ $field['key'] }}]" form="settlementClearanceForm" class="form-control form-control-sm" rows="2" placeholder="{{ __('Enter ') . strtolower($field['label']) }}" {{ !empty($field['required']) ? 'required' : '' }}>{{ $val }}</textarea>
                                            @elseif(($field['type'] ?? '') === 'select')
                                                <select name="custom_fields[{{ $field['key'] }}]" form="settlementClearanceForm" class="form-select form-select-sm" {{ !empty($field['required']) ? 'required' : '' }}>
                                                    <option value="">{{ __('Please select an option') }}</option>
                                                    @foreach($field['options'] ?? [] as $opt)
                                                        <option value="{{ $opt }}" {{ $val == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif(($field['type'] ?? '') === 'date')
                                                <input type="date" name="custom_fields[{{ $field['key'] }}]" form="settlementClearanceForm" class="form-control form-control-sm" value="{{ $val }}" {{ !empty($field['required']) ? 'required' : '' }}>
                                            @elseif(($field['type'] ?? '') === 'number')
                                                <input type="number" step="any" name="custom_fields[{{ $field['key'] }}]" form="settlementClearanceForm" class="form-control form-control-sm" placeholder="{{ __('e.g., 12345') }}" value="{{ $val }}" {{ !empty($field['required']) ? 'required' : '' }}>
                                            @else
                                                <input type="text" name="custom_fields[{{ $field['key'] }}]" form="settlementClearanceForm" class="form-control form-control-sm" placeholder="{{ __('Enter ') . strtolower($field['label']) }}" value="{{ $val }}" {{ !empty($field['required']) ? 'required' : '' }}>
                                            @endif
                                        @else
                                            <div class="p-2.5 bg-light rounded border">
                                                <small class="text-muted d-block mb-1">{{ $field['label'] }}:</small>
                                                <strong class="text-dark small">
                                                    @if(($field['type'] ?? '') === 'date' && !empty($val))
                                                        {{ $settlement->formatDate($val) }}
                                                    @else
                                                        {{ !empty($val) ? $val : '—' }}
                                                    @endif
                                                </strong>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 3. SECTION-WISE DEPARTMENTAL CLEARANCE CHECKLIST --}}
            <div class="section-card">
                <div class="section-card-header">
                    <div>
                        <h5><i class="ti ti-checkbox text-primary fs-4"></i> {{ __('3. Section-Wise Clearance Checklist') }}</h5>
                        <small class="text-muted">{{ __('Check each completed item and add handover details or notes') }}</small>
                    </div>
                    <div class="text-end">
                        <span id="overallClearanceBadge" class="badge {{ $clearancePercentage === 100 ? 'bg-success' : 'bg-primary' }} fs-7 px-3 py-1.5 rounded-pill">
                            <i class="ti ti-circle-check me-1"></i> <span id="overallClearedCountText">{{ $clearedCount }}</span> / {{ $totalChecklistItems }} {{ __('Cleared') }} (<span id="overallPercentageText">{{ $clearancePercentage }}</span>%)
                        </span>
                    </div>
                </div>
                <div class="section-card-body">
                    {{-- Overall Progress Indicator --}}
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1 small text-muted">
                            <span>{{ __('Clearance Completion Progress') }}</span>
                            <span class="fw-bold text-dark" id="progressPercentageLabel">{{ $clearancePercentage }}%</span>
                        </div>
                        <div class="progress" style="height: 8px; border-radius: 4px; background: #e2e8f0;">
                            <div id="overallProgressBar" class="progress-bar {{ $clearancePercentage === 100 ? 'bg-success' : 'bg-primary' }}"
                                role="progressbar"
                                style="width: {{ $clearancePercentage }}%"
                                aria-valuenow="{{ $clearancePercentage }}"
                                aria-valuemin="0"
                                aria-valuemax="100">
                            </div>
                        </div>
                    </div>

                    {{-- Section-wise categories --}}
                    @if (empty($checklistByCategory))
                        <div class="text-center py-4 text-muted">
                            <i class="ti ti-clipboard-check fs-2 d-block mb-1 text-secondary"></i>
                            {{ __('No specific departmental checklist items configured for this settlement.') }}
                        </div>
                    @else
                        @foreach ($checklistByCategory as $categoryName => $items)
                            @php
                                $catSlug = Str::slug($categoryName, '_');
                                $catTotal = count($items);
                                $catCleared = collect($items)->where('status', 'Returned')->count();
                                $allCatCleared = ($catCleared === $catTotal);
                            @endphp
                            <div class="checklist-group" data-cat="{{ $catSlug }}">
                                <div class="checklist-group-header">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="ti {{ $allCatCleared ? 'ti-circle-check text-success' : 'ti-folder text-primary' }} fs-5 cat-icon-{{ $catSlug }}"></i>
                                        <strong class="text-dark">{{ $categoryName }}</strong>
                                    </div>
                                    <div>
                                        <span id="catBadge_{{ $catSlug }}" class="badge {{ $allCatCleared ? 'bg-light-success text-success border border-success' : 'bg-light text-secondary border' }} px-2 py-1 rounded">
                                            <span class="cat-cleared-count">{{ $catCleared }}</span> / {{ $catTotal }} {{ __('Cleared') }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    @foreach ($items as $chk)
                                        @php
                                            $origIdx = $chk['_orig_idx'];
                                            $isReturned = ($chk['status'] === 'Returned');
                                            $isReadOnly = ($settlement->status === 'signed' || $settlement->status === 'cleared');
                                        @endphp
                                        <div class="checklist-item {{ $isReturned ? 'is-cleared' : '' }}" id="item_row_{{ $origIdx }}">
                                            <div class="d-flex align-items-start gap-3 flex-grow-1">
                                                @if ($isReadOnly)
                                                    <div class="mt-1">
                                                        @if ($isReturned)
                                                            <span class="text-success fs-5"><i class="ti ti-circle-check"></i></span>
                                                        @elseif ($chk['status'] === 'Not Applicable')
                                                            <span class="text-secondary fs-5"><i class="ti ti-minus"></i></span>
                                                        @else
                                                            <span class="text-warning fs-5"><i class="ti ti-clock"></i></span>
                                                        @endif
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="fw-semibold text-dark">{{ $chk['item'] }}</div>
                                                        @if(!empty($chk['remarks']))
                                                            <small class="text-muted d-block mt-1">
                                                                <i class="ti ti-notes me-1 text-primary"></i><strong>{{ __('Handover Note:') }}</strong> {{ $chk['remarks'] }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                @else
                                                    {{-- Interactive Checkbox & Comment input for Employee/Customer filling --}}
                                                    <div class="mt-0.5">
                                                        <input type="checkbox"
                                                               class="form-check-input clearance-chk"
                                                               id="chk_{{ $origIdx }}"
                                                               data-idx="{{ $origIdx }}"
                                                               data-cat="{{ $catSlug }}"
                                                               {{ $isReturned ? 'checked' : '' }}
                                                               onchange="onClearanceItemToggle(this)">
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <label for="chk_{{ $origIdx }}" class="fw-semibold text-dark mb-0 cursor-pointer user-select-none" style="cursor: pointer;">
                                                            {{ $chk['item'] }}
                                                        </label>
                                                        <div class="mt-1">
                                                            <input type="text"
                                                                   class="form-control form-control-sm clearance-comment-input"
                                                                   data-idx="{{ $origIdx }}"
                                                                   placeholder="{{ __('e.g., Handed over to IT desk, Asset serial #, or notes (optional)') }}"
                                                                   value="{{ $chk['remarks'] ?? '' }}">
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="text-end flex-shrink-0 ms-2">
                                                @if ($isReadOnly)
                                                    @if ($isReturned)
                                                        <span class="badge bg-success p-2 px-3 rounded"><i class="ti ti-check me-1"></i>{{ __('Cleared / Handed Over') }}</span>
                                                    @elseif ($chk['status'] === 'Not Applicable')
                                                        <span class="badge bg-secondary p-2 px-3 rounded">{{ __('Not Applicable') }}</span>
                                                    @else
                                                        <span class="badge bg-warning text-dark p-2 px-3 rounded"><i class="ti ti-clock me-1"></i>{{ __('Pending Clearance') }}</span>
                                                    @endif
                                                @else
                                                    <span class="badge {{ $isReturned ? 'bg-success' : 'bg-warning text-dark' }} p-2 px-3 rounded status-badge-item" id="badge_{{ $origIdx }}">
                                                        <i class="ti {{ $isReturned ? 'ti-check' : 'ti-clock' }} me-1"></i>
                                                        <span class="badge-text">{{ $isReturned ? __('Completed / Handed Over') : __('Pending Clearance') }}</span>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @endif

                    {{-- Section 3 Custom Questions & Answers (Assets & Handover) --}}
                    @if (!empty($fieldsBySection['assets']))
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="fw-bold text-dark mb-3"><i class="ti ti-forms text-primary me-2"></i>{{ __('Additional Clearance & Handover Details:') }}</h6>
                            <div class="row g-3">
                                @foreach ($fieldsBySection['assets'] as $field)
                                    @php
                                        $isEmployeeTarget = ($field['target'] ?? 'hr') === 'employee';
                                        $val = $customValues[$field['key']] ?? '';
                                    @endphp
                                    <div class="col-md-{{ ($field['type'] ?? '') === 'textarea' ? '12' : '6' }}">
                                        @if (!$isReadOnly && $isEmployeeTarget)
                                            <label class="form-label fw-semibold small text-dark mb-1">
                                                {{ $field['label'] }}
                                                @if(!empty($field['required'])) <span class="text-danger">*</span> @endif
                                            </label>
                                            @if(($field['type'] ?? '') === 'textarea')
                                                <textarea name="custom_fields[{{ $field['key'] }}]" form="settlementClearanceForm" class="form-control form-control-sm" rows="2" placeholder="{{ __('Enter ') . strtolower($field['label']) }}" {{ !empty($field['required']) ? 'required' : '' }}>{{ $val }}</textarea>
                                            @elseif(($field['type'] ?? '') === 'select')
                                                <select name="custom_fields[{{ $field['key'] }}]" form="settlementClearanceForm" class="form-select form-select-sm" {{ !empty($field['required']) ? 'required' : '' }}>
                                                    <option value="">{{ __('Please select an option') }}</option>
                                                    @foreach($field['options'] ?? [] as $opt)
                                                        <option value="{{ $opt }}" {{ $val == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif(($field['type'] ?? '') === 'date')
                                                <input type="date" name="custom_fields[{{ $field['key'] }}]" form="settlementClearanceForm" class="form-control form-control-sm" value="{{ $val }}" {{ !empty($field['required']) ? 'required' : '' }}>
                                            @elseif(($field['type'] ?? '') === 'number')
                                                <input type="number" step="any" name="custom_fields[{{ $field['key'] }}]" form="settlementClearanceForm" class="form-control form-control-sm" placeholder="{{ __('e.g., 12345') }}" value="{{ $val }}" {{ !empty($field['required']) ? 'required' : '' }}>
                                            @else
                                                <input type="text" name="custom_fields[{{ $field['key'] }}]" form="settlementClearanceForm" class="form-control form-control-sm" placeholder="{{ __('Enter ') . strtolower($field['label']) }}" value="{{ $val }}" {{ !empty($field['required']) ? 'required' : '' }}>
                                            @endif
                                        @else
                                            <div class="p-2.5 bg-light rounded border">
                                                <small class="text-muted d-block mb-1">{{ $field['label'] }}:</small>
                                                <strong class="text-dark small">
                                                    @if(($field['type'] ?? '') === 'date' && !empty($val))
                                                        {{ $settlement->formatDate($val) }}
                                                    @else
                                                        {{ !empty($val) ? $val : '—' }}
                                                    @endif
                                                </strong>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 4. EMPLOYEE SIGNATURE & COMMENTS --}}
            @if ($settlement->status === 'signed' || $settlement->status === 'cleared')
                {{-- Already signed certificate state --}}
                <div class="card border-success bg-light bg-opacity-25 rounded-3 p-4 text-center">
                    <div class="mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle" style="width: 60px; height: 60px;">
                            <i class="ti ti-check fs-1"></i>
                        </span>
                    </div>
                    <h4 class="fw-bold text-success mb-1">{{ __('Settlement Signed & Confirmed') }}</h4>
                    <p class="text-muted mb-3">{{ __('Your acceptance, comments, and digital signature have been recorded successfully.') }}</p>
                    <div class="mb-4">
                        <a href="{{ route('settlement.clearance.download.pdf', $settlement->sharing_token) }}" target="_blank" class="btn btn-sm btn-outline-success px-3 shadow-sm">
                            <i class="ti ti-printer me-1"></i> {{ __('Download / Print Official Statement') }}
                        </a>
                    </div>

                    @php
                        $employeeCustomQuestions = collect($fieldsBySection['employee'] ?? []);
                    @endphp
                    @if($employeeCustomQuestions->count() > 0)
                        <div class="p-3 bg-white rounded border text-start mx-auto mb-3" style="max-width: 600px;">
                            <small class="text-muted d-block fw-bold mb-2">{{ __('Your Responses to Additional Information:') }}</small>
                            <div class="row g-2">
                                @foreach($employeeCustomQuestions as $eq)
                                    <div class="col-12">
                                        <span class="text-secondary small d-block">{{ $eq['label'] }}:</span>
                                        <strong class="text-dark small">
                                            @if(($eq['type'] ?? '') === 'date' && !empty($settlement->custom_fields_data[$eq['key']]))
                                                {{ $settlement->formatDate($settlement->custom_fields_data[$eq['key']]) }}
                                            @else
                                                {{ $settlement->custom_fields_data[$eq['key']] ?? '—' }}
                                            @endif
                                        </strong>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(!empty($settlement->employee_remarks))
                        <div class="p-3 bg-white rounded border text-start mx-auto mb-3" style="max-width: 600px;">
                            <small class="text-muted d-block fw-bold mb-1">{{ __('Your Comments / Handover Notes:') }}</small>
                            <p class="mb-0 text-dark">{{ $settlement->employee_remarks }}</p>
                        </div>
                    @endif

                    <div class="d-inline-block p-3 border rounded bg-white shadow-sm mb-3">
                        <img src="{{ $settlement->employee_signature }}" alt="Employee Digital Signature" style="max-height: 100px; max-width: 280px; object-fit: contain;">
                    </div>

                    <div class="small text-muted">
                        <div><i class="ti ti-calendar me-1"></i> {{ __('Signed On: ') }}<strong>{{ $settlement->formatDate($settlement->employee_signed_at, true) }}</strong></div>
                        @if($settlement->employee_signed_ip)
                            <div><i class="ti ti-shield-check me-1"></i> {{ __('Verified IP: ') }}<span class="font-monospace">{{ $settlement->employee_signed_ip }}</span></div>
                        @endif
                    </div>
                </div>
            @else
                {{-- Form for Employee Comments & Signature --}}
                <form id="settlementClearanceForm">
                    @csrf
                    <div class="section-card border-primary" style="border-width: 2px;">
                        <div class="section-card-header bg-primary bg-opacity-10 border-primary">
                            <h5 class="text-primary"><i class="ti ti-writing me-2"></i> {{ __('4. Employee Undertaking & Digital Sign-off') }}</h5>
                            <span class="badge bg-primary text-white">{{ __('Required Step') }}</span>
                        </div>
                        <div class="section-card-body">
                            {{-- Formal Undertaking Box --}}
                            <div class="undertaking-box">
                                <div class="fw-bold text-dark mb-2 d-flex align-items-center">
                                    <i class="ti ti-file-certificate me-2 text-primary fs-5"></i>
                                    {{ __('DECLARATION & UNDERTAKING') }}
                                </div>
                                <div class="declaration-paragraphs text-dark" style="line-height: 1.65; font-size: 0.92rem;">
                                    @php
                                        $paragraphs = array_filter(array_map('trim', explode("\n", $settlement->getDeclarationText())));
                                    @endphp
                                    @foreach($paragraphs as $p)
                                        <p class="mb-2">{{ $p }}</p>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Declaration Checkbox --}}
                            <div class="form-check mb-4 ps-4">
                                <input class="form-check-input" type="checkbox" id="employee_declaration" name="employee_declaration" value="1" required style="width: 1.25rem; height: 1.25rem; margin-left: -1.75rem; cursor: pointer;">
                                <label class="form-check-label fw-bold text-dark ms-2 cursor-pointer" for="employee_declaration" style="cursor: pointer; padding-top: 2px;">
                                    {{ __('I have read, understood, and agree to the declaration and undertaking terms above.') }} <span class="text-danger">*</span>
                                </label>
                            </div>

                            {{-- Section 4 Custom Questions (Undertaking / Employee) --}}
                            @php
                                $employeeFields = collect($fieldsBySection['employee'] ?? []);
                            @endphp
                            @if($employeeFields->count() > 0)
                                <div class="mb-4 p-3 bg-light rounded-3 border">
                                    <h6 class="fw-bold text-dark mb-3"><i class="ti ti-forms text-primary me-2"></i>{{ __('Additional Information Required from You:') }}</h6>
                                    <div class="row g-3">
                                        @foreach($employeeFields as $field)
                                            @php
                                                $isEmployeeTarget = ($field['target'] ?? 'employee') === 'employee';
                                                $val = $settlement->custom_fields_data[$field['key']] ?? '';
                                            @endphp
                                            <div class="col-md-{{ ($field['type'] ?? '') === 'textarea' ? '12' : '6' }}">
                                                @if($isEmployeeTarget)
                                                    <label class="form-label fw-semibold small text-dark mb-1">
                                                        {{ $field['label'] }}
                                                        @if(!empty($field['required'])) <span class="text-danger">*</span> @endif
                                                    </label>
                                                    @if(($field['type'] ?? '') === 'textarea')
                                                        <textarea name="custom_fields[{{ $field['key'] }}]" class="form-control form-control-sm" rows="2" placeholder="{{ __('e.g., Enter your details or remarks for ') . strtolower($field['label']) }}" {{ !empty($field['required']) ? 'required' : '' }}>{{ $val }}</textarea>
                                                    @elseif(($field['type'] ?? '') === 'select')
                                                        <select name="custom_fields[{{ $field['key'] }}]" class="form-select form-select-sm" {{ !empty($field['required']) ? 'required' : '' }}>
                                                            <option value="">{{ __('Please select an option') }}</option>
                                                            @foreach($field['options'] ?? [] as $opt)
                                                                <option value="{{ $opt }}" {{ $val == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                            @endforeach
                                                        </select>
                                                    @elseif(($field['type'] ?? '') === 'date')
                                                        <input type="date" name="custom_fields[{{ $field['key'] }}]" class="form-control form-control-sm" value="{{ $val }}" {{ !empty($field['required']) ? 'required' : '' }}>
                                                    @elseif(($field['type'] ?? '') === 'number')
                                                        <input type="number" step="any" name="custom_fields[{{ $field['key'] }}]" class="form-control form-control-sm" placeholder="{{ __('e.g., 12345') }}" value="{{ $val }}" {{ !empty($field['required']) ? 'required' : '' }}>
                                                    @else
                                                        <input type="text" name="custom_fields[{{ $field['key'] }}]" class="form-control form-control-sm" placeholder="{{ __('e.g., Enter ') . strtolower($field['label']) }}" value="{{ $val }}" {{ !empty($field['required']) ? 'required' : '' }}>
                                                    @endif
                                                @else
                                                    <div class="p-2.5 bg-white rounded border">
                                                        <small class="text-muted d-block mb-1">{{ $field['label'] }}:</small>
                                                        <strong class="text-dark small">
                                                            @if(($field['type'] ?? '') === 'date' && !empty($val))
                                                                {{ $settlement->formatDate($val) }}
                                                            @else
                                                                {{ !empty($val) ? $val : '—' }}
                                                            @endif
                                                        </strong>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Optional Employee Comments --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark">
                                    <i class="ti ti-message-dots text-primary me-1"></i> {{ __('Your Comments / Remarks (Optional)') }}
                                </label>
                                <textarea name="employee_remarks" id="employee_remarks" class="form-control" rows="3" placeholder="{{ __('e.g., Forwarding address, personal phone/email for tax/PF documents, or additional handover notes...') }}"></textarea>
                            </div>

                            {{-- Signature Method Switcher --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark d-block mb-2">
                                    <i class="ti ti-pencil text-primary me-1"></i> {{ __('Provide Your Digital Signature') }} <span class="text-danger">*</span>
                                </label>

                                <div class="d-flex gap-2 mb-3">
                                    <button type="button" class="signature-tab-btn active" id="tabDrawBtn" onclick="switchSignatureTab('draw')">
                                        <i class="ti ti-pencil me-1"></i> {{ __('Draw Signature') }}
                                    </button>
                                    <button type="button" class="signature-tab-btn" id="tabUploadBtn" onclick="switchSignatureTab('upload')">
                                        <i class="ti ti-upload me-1"></i> {{ __('Upload Signature Image') }}
                                    </button>
                                </div>

                                {{-- Tab 1: Draw Signature Canvas --}}
                                <div id="sigDrawContainer">
                                    <div class="sig-canvas-wrapper">
                                        <canvas id="sigCanvas" class="sig-canvas"></canvas>
                                    </div>
                                    <div class="mt-2 d-flex justify-content-between align-items-center">
                                        <button type="button" id="clearSigBtn" class="btn btn-sm btn-outline-danger">
                                            <i class="ti ti-eraser me-1"></i> {{ __('Clear & Redraw') }}
                                        </button>
                                        <small class="text-muted"><i class="ti ti-info-circle me-1"></i>{{ __('Use mouse, trackpad, or finger on touch devices') }}</small>
                                    </div>
                                </div>

                                {{-- Tab 2: Upload Signature File --}}
                                <div id="sigUploadContainer" style="display: none;">
                                    <div id="dropzoneArea" class="upload-dropzone" onclick="document.getElementById('sigFileInput').click()">
                                        <i class="ti ti-cloud-upload fs-1 text-primary mb-2 d-block"></i>
                                        <div class="fw-bold text-dark">{{ __('Click or Drag & Drop your Signature Image') }}</div>
                                        <small class="text-muted d-block mt-1">{{ __('Accepted formats: PNG, JPG, JPEG (transparent background recommended, max 5MB)') }}</small>
                                        <input type="file" id="sigFileInput" accept="image/png,image/jpeg,image/jpg" style="display: none;">
                                    </div>

                                    <div id="uploadedPreviewWrapper" class="mt-3" style="display: none;">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="signature-preview-box">
                                                <img id="sigImagePreview" src="" alt="Uploaded Signature Preview" style="max-height: 80px; max-width: 250px; object-fit: contain;">
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeUploadedSignature()">
                                                    <i class="ti ti-trash me-1"></i> {{ __('Remove & Choose Another') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Submit Button --}}
                            <div class="text-end pt-3 border-top">
                                <button type="submit" id="submitBtn" class="btn btn-primary btn-lg px-5 fw-bold shadow-sm">
                                    <i class="ti ti-check me-2"></i> {{ __('Sign & Submit Settlement') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            @endif

            {{-- Document Footer --}}
            <div class="text-center text-muted small mt-5 pt-3 border-top">
                <div class="fw-semibold">{{ $company ? $company->name : __('Karma Mark Start Consultancy LLP') }}</div>
                <div class="text-muted" style="font-size: 11px;">
                    {{ __('This clearance statement is securely encrypted and digitally verifiable.') }}
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/js/plugins/signature_pad/signature_pad.min.js') }}"></script>
<script>
    let currentSignatureTab = 'draw';
    let uploadedSignatureBase64 = null;
    let signaturePadInstance = null;

    const canvas = document.getElementById('sigCanvas');
    if (canvas) {
        function resizeCanvas() {
            const data = signaturePadInstance ? signaturePadInstance.toData() : null;
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            if (signaturePadInstance && data && data.length > 0) {
                signaturePadInstance.fromData(data);
            }
        }
        window.addEventListener("resize", resizeCanvas);
        resizeCanvas();

        signaturePadInstance = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(15, 23, 42)',
            minWidth: 1.5,
            maxWidth: 3.5
        });

        document.getElementById('clearSigBtn').addEventListener('click', function () {
            signaturePadInstance.clear();
        });
    }

    function switchSignatureTab(tab) {
        currentSignatureTab = tab;
        const drawBtn = document.getElementById('tabDrawBtn');
        const uploadBtn = document.getElementById('tabUploadBtn');
        const drawContainer = document.getElementById('sigDrawContainer');
        const uploadContainer = document.getElementById('sigUploadContainer');

        if (tab === 'draw') {
            drawBtn.classList.add('active');
            uploadBtn.classList.remove('active');
            drawContainer.style.display = 'block';
            uploadContainer.style.display = 'none';
            if (canvas) {
                setTimeout(function() {
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext("2d").scale(ratio, ratio);
                }, 50);
            }
        } else {
            uploadBtn.classList.add('active');
            drawBtn.classList.remove('active');
            drawContainer.style.display = 'none';
            uploadContainer.style.display = 'block';
        }
    }

    // Handle Signature File Upload
    const fileInput = document.getElementById('sigFileInput');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    alert('Signature file must be under 5MB.');
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(evt) {
                    uploadedSignatureBase64 = evt.target.result;
                    document.getElementById('sigImagePreview').src = uploadedSignatureBase64;
                    document.getElementById('dropzoneArea').style.display = 'none';
                    document.getElementById('uploadedPreviewWrapper').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // Drag and Drop
        const dropzone = document.getElementById('dropzoneArea');
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropzone.style.borderColor = '#584ed2';
                dropzone.style.backgroundColor = '#eef2ff';
            }, false);
        });
        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropzone.style.borderColor = '#cbd5e1';
                dropzone.style.backgroundColor = '#f8fafc';
            }, false);
        });
        dropzone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files[0]) {
                fileInput.files = files;
                const event = new Event('change');
                fileInput.dispatchEvent(event);
            }
        });
    }

    function removeUploadedSignature() {
        uploadedSignatureBase64 = null;
        if (fileInput) fileInput.value = '';
        document.getElementById('uploadedPreviewWrapper').style.display = 'none';
        document.getElementById('dropzoneArea').style.display = 'block';
    }

    function onClearanceItemToggle(chk) {
        const idx = chk.getAttribute('data-idx');
        const catSlug = chk.getAttribute('data-cat');
        const isChecked = chk.checked;
        const row = document.getElementById('item_row_' + idx);
        const badge = document.getElementById('badge_' + idx);

        if (row) {
            if (isChecked) {
                row.classList.add('is-cleared');
            } else {
                row.classList.remove('is-cleared');
            }
        }

        if (badge) {
            if (isChecked) {
                badge.className = 'badge bg-success p-2 px-3 rounded status-badge-item';
                badge.innerHTML = '<i class="ti ti-check me-1"></i><span class="badge-text">{{ __("Completed / Handed Over") }}</span>';
            } else {
                badge.className = 'badge bg-warning text-dark p-2 px-3 rounded status-badge-item';
                badge.innerHTML = '<i class="ti ti-clock me-1"></i><span class="badge-text">{{ __("Pending Clearance") }}</span>';
            }
        }

        // Recalculate Category Stats
        const groupEl = document.querySelector(`.checklist-group[data-cat="${catSlug}"]`);
        if (groupEl) {
            const catCheckboxes = groupEl.querySelectorAll('.clearance-chk');
            const catTotal = catCheckboxes.length;
            let catCleared = 0;
            catCheckboxes.forEach(c => { if (c.checked) catCleared++; });

            const catBadge = document.getElementById('catBadge_' + catSlug);
            const catIcon = groupEl.querySelector('.cat-icon-' + catSlug);
            if (catBadge) {
                catBadge.innerHTML = `<span class="cat-cleared-count">${catCleared}</span> / ${catTotal} {{ __("Cleared") }}`;
                if (catCleared === catTotal) {
                    catBadge.className = 'badge bg-light-success text-success border border-success px-2 py-1 rounded';
                } else {
                    catBadge.className = 'badge bg-light text-secondary border px-2 py-1 rounded';
                }
            }
            if (catIcon) {
                if (catCleared === catTotal) {
                    catIcon.className = `ti ti-circle-check text-success fs-5 cat-icon-${catSlug}`;
                } else {
                    catIcon.className = `ti ti-folder text-primary fs-5 cat-icon-${catSlug}`;
                }
            }
        }

        // Recalculate Overall Stats
        const allCheckboxes = document.querySelectorAll('.clearance-chk');
        const totalItems = allCheckboxes.length;
        let totalCleared = 0;
        allCheckboxes.forEach(c => { if (c.checked) totalCleared++; });

        const pct = totalItems > 0 ? Math.round((totalCleared / totalItems) * 100) : 100;
        
        const overallCountText = document.getElementById('overallClearedCountText');
        const overallPctText = document.getElementById('overallPercentageText');
        const overallBadge = document.getElementById('overallClearanceBadge');
        const progressLabel = document.getElementById('progressPercentageLabel');
        const progressBar = document.getElementById('overallProgressBar');

        if (overallCountText) overallCountText.innerText = totalCleared;
        if (overallPctText) overallPctText.innerText = pct;
        if (progressLabel) progressLabel.innerText = pct + '%';
        if (progressBar) {
            progressBar.style.width = pct + '%';
            progressBar.setAttribute('aria-valuenow', pct);
            if (pct === 100) {
                progressBar.className = 'progress-bar bg-success';
            } else {
                progressBar.className = 'progress-bar bg-primary';
            }
        }
        if (overallBadge) {
            if (pct === 100) {
                overallBadge.className = 'badge bg-success fs-7 px-3 py-1.5 rounded-pill';
            } else {
                overallBadge.className = 'badge bg-primary fs-7 px-3 py-1.5 rounded-pill';
            }
        }
    }

    // Form Submission
    const clearanceForm = document.getElementById('settlementClearanceForm');
    if (clearanceForm) {
        clearanceForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const declCheckbox = document.getElementById('employee_declaration');
            if (!declCheckbox.checked) {
                alert('Please check the declaration box to confirm your undertaking.');
                declCheckbox.focus();
                return;
            }

            // Check required custom fields across all sections
            let missingRequiredField = null;
            let missingFieldLabel = '';
            document.querySelectorAll('[name^="custom_fields["][required]').forEach(inp => {
                if (!missingRequiredField && !inp.value.trim()) {
                    missingRequiredField = inp;
                    const container = inp.closest('.col-md-6, .col-md-12');
                    const labelEl = container ? container.querySelector('label') : null;
                    missingFieldLabel = labelEl ? labelEl.innerText.replace('*', '').trim() : 'required field';
                }
            });

            if (missingRequiredField) {
                alert(`Please complete the required field "${missingFieldLabel}" before submitting.`);
                missingRequiredField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                missingRequiredField.focus();
                return;
            }

            let finalSignature = null;
            if (currentSignatureTab === 'draw') {
                if (!signaturePadInstance || signaturePadInstance.isEmpty()) {
                    alert('Please draw your digital signature or switch to upload tab.');
                    return;
                }
                finalSignature = signaturePadInstance.toDataURL('image/png');
            } else {
                if (!uploadedSignatureBase64) {
                    alert('Please select and upload a signature image file or switch to draw tab.');
                    return;
                }
                finalSignature = uploadedSignatureBase64;
            }

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Submitting...';

            const remarks = document.getElementById('employee_remarks') ? document.getElementById('employee_remarks').value : '';

            // Collect clearance checklist items with checked status and remarks
            const clearanceItems = [];
            document.querySelectorAll('.clearance-chk').forEach(chk => {
                const idx = chk.getAttribute('data-idx');
                const commentInput = document.querySelector(`.clearance-comment-input[data-idx="${idx}"]`);
                clearanceItems.push({
                    idx: parseInt(idx, 10),
                    checked: chk.checked ? 1 : 0,
                    remarks: commentInput ? commentInput.value.trim() : ''
                });
            });

            // Collect any custom employee field inputs
            const customFields = {};
            document.querySelectorAll('[name^="custom_fields["]').forEach(inp => {
                const key = inp.getAttribute('name').replace('custom_fields[', '').replace(']', '');
                customFields[key] = inp.value.trim();
            });

            fetch('{{ route("settlement.clearance.sign", $settlement->sharing_token) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    employee_declaration: 1,
                    employee_signature: finalSignature,
                    employee_remarks: remarks,
                    clearance_items: clearanceItems,
                    custom_fields: customFields
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || 'Submission failed. Please try again.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="ti ti-check me-2"></i> {{ __("Sign & Submit Settlement") }}';
                }
            })
            .catch(err => {
                alert('Network error occurred. Please try again.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="ti ti-check me-2"></i> {{ __("Sign & Submit Settlement") }}';
            });
        });
    }
</script>
<script src="{{ asset('assets/js/plugins/flatpickr.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @php
            $creatorId = $settlement->created_by ?: 1;
            $cSettings = \App\Models\Utility::getCompanySettings($creatorId);
            $dynamicDateFormat = !empty($cSettings['site_date_format']) ? $cSettings['site_date_format'] : 'd-m-Y';
        @endphp
        const siteDateFormat = "{{ $dynamicDateFormat }}";
        if (typeof flatpickr !== 'undefined') {
            flatpickr('input[type="date"]', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: siteDateFormat,
                allowInput: false,
                disableMobile: true
            });
        }
    });
</script>
</body>
</html>