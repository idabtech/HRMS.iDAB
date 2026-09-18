@extends('layouts.admin')

@section('page-title')
    {{ __('Settlement Details: ') . $settlement->settlement_number }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('settlement.index') }}">{{ __('Full & Final Settlements') }}</a></li>
    <li class="breadcrumb-item">{{ $settlement->settlement_number }}</li>
@endsection

@section('action-button')
    <div class="d-flex align-items-center flex-wrap gap-2">
        {{-- Copy Public Link --}}
        <button type="button" class="btn btn-sm btn-secondary copy-settlement-link"
            data-url="{{ $settlement->public_url }}">
            <i class="ti ti-link"></i> {{ __('Copy Secure Link') }}
        </button>

        {{-- WhatsApp Share --}}
        @php
            $waText = urlencode("Hello " . $settlement->employee_name . ", please review and complete your Full & Final Settlement & Departmental Clearance form: " . $settlement->public_url);
            $waUrl = "https://api.whatsapp.com/send?text=" . $waText;
        @endphp
        <a href="{{ $waUrl }}" target="_blank" class="btn btn-sm btn-success">
            <i class="ti ti-brand-whatsapp"></i> {{ __('Share on WhatsApp') }}
        </a>

        {{-- Send Mail --}}
        @can('Send Settlement Mail')
            <a href="{{ route('settlement.send.mail', $settlement->id) }}" class="btn btn-sm btn-warning text-white">
                <i class="ti ti-mail"></i> {{ __('Send to Employee') }}
            </a>
        @endcan

        {{-- Regenerate / Extend Link --}}
        @if(Gate::check('Edit Settlement') || \Auth::user()->can('Manage Settlement'))
            <a href="{{ route('settlement.regenerate.link', $settlement->id) }}" class="btn btn-sm btn-dark"
                data-bs-toggle="tooltip" title="{{ __('Renew link validity for next 30 days') }}">
                <i class="ti ti-refresh"></i> {{ __('Regenerate Link') }}
            </a>
        @endif

        {{-- Recall to Draft (if sent) --}}
        @if($settlement->status === 'sent' && (Gate::check('Edit Settlement') || \Auth::user()->can('Manage Settlement')))
            <a href="{{ route('settlement.recall.draft', $settlement->id) }}" class="btn btn-sm btn-outline-danger"
                onclick="return confirm('{{ __('Are you sure you want to recall this settlement to Draft? The employee link will be paused until re-sent.') }}');">
                <i class="ti ti-arrow-back-up"></i> {{ __('Recall to Draft') }}
            </a>
        @endif

        {{-- Download PDF --}}
        <a href="{{ route('settlement.download.pdf', $settlement->id) }}" target="_blank" class="btn btn-sm btn-info text-white">
            <i class="ti ti-download"></i> {{ __('Download / Print') }}
        </a>

        {{-- Edit --}}
        @can('Edit Settlement')
            <a href="{{ route('settlement.edit', $settlement->id) }}" class="btn btn-sm btn-primary">
                <i class="ti ti-pencil"></i> {{ __('Edit') }}
            </a>
        @endcan
    </div>
@endsection

@php
    $allCustomFields = $settlement->custom_fields_schema ?? [];
    $customValues = $settlement->custom_fields_data ?? [];

    $fieldsBySection = [];
    foreach ($allCustomFields as $f) {
        $sec = $f['section'] ?? 'general';
        $fieldsBySection[$sec][] = $f;
    }
@endphp

@section('content')
    <div class="row">
        {{-- Status Alert & Link Details Bar --}}
        <div class="col-12 mb-4">
            <div class="card bg-light border shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <span class="text-muted small d-block">{{ __('Current Settlement Status') }}</span>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                @if ($settlement->status === 'draft')
                                    <span class="badge bg-secondary fs-6 px-3 py-2"><i class="ti ti-file me-1"></i>{{ __('Draft (Not Sent Yet)') }}</span>
                                @elseif ($settlement->status === 'sent')
                                    <span class="badge bg-info fs-6 px-3 py-2"><i class="ti ti-send me-1"></i>{{ __('Sent to Employee for Digital Signature') }}</span>
                                @elseif ($settlement->status === 'signed')
                                    <span class="badge bg-primary fs-6 px-3 py-2"><i class="ti ti-writing me-1"></i>{{ __('Signed by Employee (Pending Management Clearance)') }}</span>
                                @elseif ($settlement->status === 'cleared')
                                    <span class="badge bg-success fs-6 px-3 py-2"><i class="ti ti-circle-check me-1"></i>{{ __('Fully Cleared & Disbursed') }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Token Expiry Status --}}
                        <div>
                            <span class="text-muted small d-block">{{ __('Clearance Link Status') }}</span>
                            <div class="mt-1">
                                @if($settlement->isLinkExpired())
                                    <span class="badge bg-danger fs-6 px-3 py-2"><i class="ti ti-alert-triangle me-1"></i>{{ __('Link Expired') }}</span>
                                    <a href="{{ route('settlement.regenerate.link', $settlement->id) }}" class="btn btn-xs btn-outline-danger ms-2">
                                        {{ __('Renew Now') }}
                                    </a>
                                @else
                                    <span class="badge bg-light text-success border border-success fs-6 px-3 py-2">
                                        <i class="ti ti-shield-check me-1"></i>{{ __('Active') }} ({{ $settlement->daysUntilExpiry() }} {{ __('days remaining') }})
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Direct URL link --}}
                        <div class="text-end">
                            <span class="text-muted small d-block">{{ __('Public Clearance Form URL') }}</span>
                            <a href="{{ $settlement->public_url }}" target="_blank" class="fw-bold text-primary text-decoration-underline mt-1 d-inline-block">
                                {{ __('Open Public Form') }} <i class="ti ti-external-link"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 1: Employee & Separation Information --}}
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0 text-white"><i class="ti ti-user me-2"></i>{{ __('1. Employee & Separation Details') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <span class="text-muted small">{{ __('Employee Name') }}</span>
                            <div class="fw-bold fs-6">{{ $settlement->employee_name }}</div>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small">{{ __('Employee ID') }}</span>
                            <div class="fw-bold">{{ $settlement->employee_code ?: '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small">{{ __('Designation & Department') }}</span>
                            <div class="fw-bold">{{ $settlement->designation ?: '-' }} ({{ $settlement->department ?: '-' }})</div>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small">{{ __('Industry Template') }}</span>
                            <div class="badge bg-light text-dark text-uppercase">{{ $settlement->industry_type }}</div>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small">{{ __('Date of Joining (DOJ)') }}</span>
                            <div>{{ $settlement->date_of_joining ? $settlement->formatDate($settlement->date_of_joining) : '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small">{{ __('Last Working Day (LWD)') }}</span>
                            <div class="fw-bold text-danger">{{ $settlement->last_working_day ? $settlement->formatDate($settlement->last_working_day) : '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted small">{{ __('Reason for Separation') }}</span>
                            <div>{{ $settlement->reason_for_separation }}</div>
                        </div>

                        {{-- Section 1 Custom Questions --}}
                        @if (!empty($fieldsBySection['separation']))
                            <div class="col-12 mt-3 pt-3 border-top">
                                <h6 class="fw-bold text-primary mb-2"><i class="ti ti-forms me-1"></i>{{ __('Custom Section Questions & Answers:') }}</h6>
                                <div class="row g-2">
                                    @foreach ($fieldsBySection['separation'] as $f)
                                        <div class="col-md-6">
                                            <div class="p-2 bg-light rounded border">
                                                <small class="text-muted d-block">{{ $f['label'] }}</small>
                                                <strong class="text-dark">
                                                    @if(($f['type'] ?? '') === 'date' && !empty($customValues[$f['key']]))
                                                        {{ $settlement->formatDate($customValues[$f['key']]) }}
                                                    @else
                                                        {{ $customValues[$f['key']] ?? '—' }}
                                                    @endif
                                                </strong>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 2: Financial Clearance --}}
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white"><i class="ti ti-receipt-2 me-2"></i>{{ __('2. Financial Clearance Breakdown') }}</h5>
                    <span class="badge bg-warning text-dark">{{ __('HR / Finance Certified') }}</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 border-end">
                            <h6 class="fw-bold text-success mb-3">{{ __('Earnings & Payables (A)') }}</h6>
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('Particulars') }}</th>
                                        <th class="text-end">{{ __('Amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($settlement->earnings_data ?? [] as $earn)
                                        <tr>
                                            <td>{{ $earn['name'] }}</td>
                                            <td class="text-end fw-bold">{{ \Auth::user()->priceFormat($earn['amount']) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2" class="text-muted text-center">{{ __('No earnings line items.') }}</td></tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="table-success fw-bold">
                                        <td>{{ __('Gross Amount Payable') }}</td>
                                        <td class="text-end">{{ \Auth::user()->priceFormat($settlement->gross_payable) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h6 class="fw-bold text-danger mb-3">{{ __('Deductions & Recoveries (B)') }}</h6>
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('Particulars') }}</th>
                                        <th class="text-end">{{ __('Amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($settlement->deductions_data ?? [] as $ded)
                                        <tr>
                                            <td>{{ $ded['name'] }}</td>
                                            <td class="text-end fw-bold">{{ \Auth::user()->priceFormat($ded['amount']) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2" class="text-muted text-center">{{ __('No deduction items.') }}</td></tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="table-danger fw-bold">
                                        <td>{{ __('Total Deductions') }}</td>
                                        <td class="text-end">{{ \Auth::user()->priceFormat($settlement->total_deductions) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-3 mt-3 d-flex justify-content-between align-items-center border">
                        <div>
                            <h4 class="mb-0 fw-bold text-dark">{{ __('NET FINAL SETTLEMENT AMOUNT:') }}</h4>
                            <small class="text-muted">{{ __('Gross Earnings minus Total Deductions') }}</small>
                        </div>
                        <div class="fs-2 fw-bold text-primary">
                            {{ \Auth::user()->priceFormat($settlement->net_amount) }}
                        </div>
                    </div>

                    {{-- Section 2 Custom Questions --}}
                    @if (!empty($fieldsBySection['financial']))
                        <div class="mt-3 pt-3 border-top">
                            <h6 class="fw-bold text-primary mb-2"><i class="ti ti-forms me-1"></i>{{ __('Custom Financial Questions & Answers:') }}</h6>
                            <div class="row g-2">
                                @foreach ($fieldsBySection['financial'] as $f)
                                    <div class="col-md-6">
                                        <div class="p-2 bg-light rounded border">
                                            <small class="text-muted d-block">{{ $f['label'] }}</small>
                                            <strong class="text-dark">
                                                @if(($f['type'] ?? '') === 'date' && !empty($customValues[$f['key']]))
                                                    {{ $settlement->formatDate($customValues[$f['key']]) }}
                                                @else
                                                    {{ $customValues[$f['key']] ?? '—' }}
                                                @endif
                                            </strong>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Section 3: Departmental Clearance Matrix --}}
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0 text-white"><i class="ti ti-checklist me-2"></i>{{ __('3. Departmental & Asset Clearances Checklist') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th width="25%">{{ __('Category') }}</th>
                                    <th width="50%">{{ __('Clearance Checkpoint / Asset') }}</th>
                                    <th width="25%">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($settlement->clearance_data ?? [] as $chk)
                                    <tr>
                                        <td><strong>{{ $chk['category'] }}</strong></td>
                                        <td>
                                            <div class="fw-semibold">{{ $chk['item'] }}</div>
                                            @if(!empty($chk['remarks']))
                                                <div class="text-muted small mt-1"><i class="ti ti-notes me-1 text-primary"></i><strong>{{ __('Handover Note:') }}</strong> {{ $chk['remarks'] }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($chk['status'] === 'Returned')
                                                <span class="badge bg-success"><i class="ti ti-check me-1"></i>{{ __('Returned / Cleared') }}</span>
                                            @elseif ($chk['status'] === 'Not Applicable')
                                                <span class="badge bg-secondary">{{ __('Not Applicable') }}</span>
                                            @else
                                                <span class="badge bg-warning text-dark"><i class="ti ti-clock me-1"></i>{{ __('Pending') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">{{ __('No clearance checkpoints recorded.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Section 3 Custom Questions --}}
                    @if (!empty($fieldsBySection['assets']))
                        <div class="mt-3 pt-3 border-top">
                            <h6 class="fw-bold text-primary mb-2"><i class="ti ti-forms me-1"></i>{{ __('Custom Asset Clearance Questions & Answers:') }}</h6>
                            <div class="row g-2">
                                @foreach ($fieldsBySection['assets'] as $f)
                                    <div class="col-md-6">
                                        <div class="p-2 bg-light rounded border">
                                            <small class="text-muted d-block">{{ $f['label'] }}</small>
                                            <strong class="text-dark">
                                                @if(($f['type'] ?? '') === 'date' && !empty($customValues[$f['key']]))
                                                    {{ $settlement->formatDate($customValues[$f['key']]) }}
                                                @else
                                                    {{ $customValues[$f['key']] ?? '—' }}
                                                @endif
                                            </strong>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Section 4: Stage 1 — Employee Handover & Signature --}}
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark"><i class="ti ti-writing me-2 text-primary"></i>{{ __('4. Stage 1: Employee Clearance & Signature') }}</h5>
                    @if ($settlement->employee_declaration_accepted)
                        <span class="badge bg-success"><i class="ti ti-check me-1"></i>{{ __('Signed') }}</span>
                    @else
                        <span class="badge bg-warning text-dark"><i class="ti ti-clock me-1"></i>{{ __('Pending') }}</span>
                    @endif
                </div>
                <div class="card-body">
                    {{-- Legal Undertaking Terms --}}
                    <div class="mb-3">
                        <span class="text-muted small d-block mb-1">
                            <i class="ti ti-file-certificate text-primary me-1"></i><strong>{{ __('Legal Declaration & Undertaking Terms:') }}</strong>
                        </span>
                        <div class="p-2.5 bg-light rounded border small text-dark" style="max-height: 120px; overflow-y: auto; line-height: 1.55;">
                            @php
                                $showParagraphs = array_filter(array_map('trim', explode("\n", $settlement->getDeclarationText())));
                            @endphp
                            @foreach($showParagraphs as $p)
                                <p class="mb-1">{{ $p }}</p>
                            @endforeach
                        </div>
                    </div>

                    {{-- Custom Employee Questions --}}
                    @if (!empty($fieldsBySection['employee']))
                        <div class="mb-3">
                            <h6 class="fw-bold text-dark mb-2"><i class="ti ti-forms text-primary me-1"></i>{{ __('Employee Question Responses:') }}</h6>
                            @foreach ($fieldsBySection['employee'] as $f)
                                <div class="p-2 bg-light rounded border mb-2">
                                    <small class="text-muted d-block">{{ $f['label'] }}</small>
                                    <strong class="text-dark">
                                        @if(($f['type'] ?? '') === 'date' && !empty($customValues[$f['key']]))
                                            {{ $settlement->formatDate($customValues[$f['key']]) }}
                                        @else
                                            {{ $customValues[$f['key']] ?? '— (Not answered yet)' }}
                                        @endif
                                    </strong>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if ($settlement->employee_declaration_accepted)
                        <div class="alert alert-success mb-3 py-2">
                            <i class="ti ti-check-circle me-1"></i> {{ __('Employee confirmed compliance with IP, non-disclosure & data removal.') }}
                        </div>
                        <div class="mb-3">
                            <span class="text-muted small">{{ __('Employee Digital Signature:') }}</span>
                            <div class="border rounded p-2 text-center bg-light mt-1">
                                @if ($settlement->employee_signature)
                                    <img src="{{ $settlement->employee_signature }}" alt="Employee Signature" style="max-height: 90px; max-width: 100%;">
                                @else
                                    <span class="text-muted">{{ __('No signature image') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="small text-muted">
                            <div><strong>{{ __('Signed Timestamp: ') }}</strong>{{ $settlement->employee_signed_at ? $settlement->formatDate($settlement->employee_signed_at, true) : '-' }}</div>
                            <div><strong>{{ __('IP Address: ') }}</strong>{{ $settlement->employee_signed_ip ?: '-' }}</div>
                            @if ($settlement->employee_remarks)
                                <div class="mt-1"><strong>{{ __('Handover Remarks: ') }}</strong>{{ $settlement->employee_remarks }}</div>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-warning mb-0 text-center py-4">
                            <i class="ti ti-clock fs-1 d-block mb-2 text-warning"></i>
                            <h6>{{ __('Awaiting Employee Digital Signature') }}</h6>
                            <p class="small text-muted mb-3">{{ __('The employee has not submitted their clearance sign-off yet. Share the link via WhatsApp or email.') }}</p>
                            <div class="d-flex justify-content-center gap-2">
                                <button type="button" class="btn btn-sm btn-primary copy-settlement-link" data-url="{{ $settlement->public_url }}">
                                    <i class="ti ti-link"></i> {{ __('Copy Link') }}
                                </button>
                                <a href="{{ route('settlement.send.mail', $settlement->id) }}" class="btn btn-sm btn-warning text-white">
                                    <i class="ti ti-mail"></i> {{ __('Send Email') }}
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Section 5: Stage 2 — Department Manager / HOD Countersign --}}
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark"><i class="ti ti-user-check me-2 text-info"></i>{{ __('5. Stage 2: Manager / HOD Countersign') }}</h5>
                    @if ($settlement->manager_signed_at)
                        <span class="badge bg-success"><i class="ti ti-check me-1"></i>{{ __('Verified & Signed') }}</span>
                    @else
                        <span class="badge bg-secondary"><i class="ti ti-clock me-1"></i>{{ __('Pending') }}</span>
                    @endif
                </div>
                <div class="card-body">
                    @if ($settlement->manager_signed_at)
                        <div class="alert alert-success mb-3 py-2">
                            <i class="ti ti-check-circle me-1"></i> {{ __('Handover items verified and countersigned by Department Manager.') }}
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <span class="text-muted small">{{ __('Manager / HOD Name') }}</span>
                                <div class="fw-bold">{{ $settlement->manager_name }}</div>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small">{{ __('Countersigned Date') }}</span>
                                <div class="fw-bold">{{ $settlement->formatDate($settlement->manager_signed_at, true) }}</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <span class="text-muted small">{{ __('Manager Signature:') }}</span>
                            <div class="border rounded p-2 text-center bg-light mt-1">
                                @if ($settlement->manager_signature)
                                    <img src="{{ $settlement->manager_signature }}" alt="Manager Signature" style="max-height: 90px; max-width: 100%;">
                                @else
                                    <span class="text-muted">{{ __('No signature recorded') }}</span>
                                @endif
                            </div>
                        </div>
                        @if ($settlement->manager_remarks)
                            <div class="small text-muted">
                                <strong>{{ __('Manager Verification Remarks: ') }}</strong>{{ $settlement->manager_remarks }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="ti ti-signature fs-1 d-block mb-2 text-info"></i>
                            <h6>{{ __('Manager Handover Verification') }}</h6>
                            <p class="small text-muted mb-3">{{ __('Department Manager or HOD reviews asset handover and provides digital verification countersignature.') }}</p>
                            @if(Gate::check('Edit Settlement') || \Auth::user()->can('Manage Settlement'))
                                <button type="button" class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#managerCountersignModal">
                                    <i class="ti ti-pencil me-1"></i> {{ __('Countersign as Manager / HOD') }}
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Section 6: Stage 3 — Management Sign-off & Final Disbursal --}}
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark"><i class="ti ti-shield-check me-2 text-success"></i>{{ __('6. Stage 3: Management Clearance & Payment Disbursal') }}</h5>
                    @if ($settlement->final_settlement_status === 'Cleared')
                        <span class="badge bg-success fs-6"><i class="ti ti-check me-1"></i>{{ __('Fully Cleared & Disbursed') }}</span>
                    @else
                        <span class="badge bg-warning text-dark fs-6"><i class="ti ti-clock me-1"></i>{{ __('Disbursal Pending') }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <span class="text-muted small">{{ __('Settlement Disbursal Status') }}</span>
                            <div class="mt-1">
                                @if ($settlement->final_settlement_status === 'Cleared')
                                    <span class="badge bg-success fs-6">{{ __('Cleared & Paid') }}</span>
                                @else
                                    <span class="badge bg-warning text-dark fs-6">{{ __('Pending Payment') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small">{{ __('Payment Mode') }}</span>
                            <div class="fw-bold fs-6">{{ $settlement->payment_mode ?: '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small">{{ __('Payment Disbursed Date') }}</span>
                            <div class="fw-bold fs-6">{{ $settlement->payment_date ? $settlement->formatDate($settlement->payment_date) : '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small">{{ __('UTR / Transaction Reference No.') }}</span>
                            <div class="fw-bold fs-6 text-primary">{{ $settlement->payment_reference_no ?: '—' }}</div>
                        </div>

                        <div class="col-md-6 border-top pt-3">
                            <span class="text-muted small">{{ __('HR Representative') }}</span>
                            <div class="fw-bold">{{ $settlement->hr_representative_name ?: '—' }}</div>
                            @if($settlement->hr_cleared_at)
                                <small class="text-muted">{{ __('Cleared on: ') . $settlement->formatDate($settlement->hr_cleared_at, true) }}</small>
                            @endif
                        </div>
                        <div class="col-md-6 border-top pt-3">
                            <span class="text-muted small">{{ __('Authorized Signatory') }}</span>
                            <div class="fw-bold">{{ $settlement->authorized_signatory_name ?: '—' }}</div>
                            @if($settlement->authorized_date)
                                <small class="text-muted">{{ __('Signed on: ') . $settlement->formatDate($settlement->authorized_date) }}</small>
                            @endif
                            @if($settlement->authorized_signature)
                                <div class="mt-2">
                                    <img src="{{ $settlement->authorized_signature }}" alt="Authorized Signature" style="max-height: 60px; max-width: 200px;" class="border p-1 bg-white rounded">
                                </div>
                            @endif
                        </div>
                    </div>

                    @if ($settlement->final_settlement_status !== 'Cleared' && (Gate::check('Edit Settlement') || \Auth::user()->can('Manage Settlement')))
                        <div class="mt-4 pt-3 border-top text-end">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#managementSignoffModal">
                                <i class="ti ti-check me-1"></i> {{ __('Record Payment & Complete Management Sign-off') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Section 7: Audit Trail & Activity Log Timeline --}}
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0 text-dark"><i class="ti ti-history me-2 text-primary"></i>{{ __('7. Activity Log & Audit Trail') }}</h5>
                </div>
                <div class="card-body">
                    @php
                        $logs = array_reverse($settlement->activity_logs ?? []);
                    @endphp
                    @if (empty($logs))
                        <p class="text-muted mb-0 small">{{ __('No audit events recorded yet.') }}</p>
                    @else
                        <div class="timeline-list">
                            @foreach ($logs as $log)
                                <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom">
                                    <div class="bg-light-primary text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                        <i class="ti ti-check fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <strong class="text-dark">{{ $log['action'] ?? __('Action') }}</strong>
                                            <small class="text-muted">{{ !empty($log['timestamp']) ? $settlement->formatDate($log['timestamp'], true) : ($log['formatted_time'] ?? '') }}</small>
                                        </div>
                                        <div class="text-muted small mt-1">{{ $log['description'] ?? '' }}</div>
                                        <small class="badge bg-light text-secondary border mt-1">
                                            <i class="ti ti-user me-1"></i>{{ $log['user'] ?? __('System') }}
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL 1: Manager Countersign Modal --}}
    <div class="modal fade" id="managerCountersignModal" tabindex="-1" aria-labelledby="managerCountersignModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <form action="{{ route('settlement.manager.countersign', $settlement->id) }}" method="POST" id="managerCountersignForm">
                    @csrf
                    <div class="modal-header bg-light">
                        <h5 class="modal-title" id="managerCountersignModalLabel"><i class="ti ti-user-check me-2 text-info"></i>{{ __('Manager / HOD Countersign') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('Manager / HOD Full Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="manager_name" class="form-control" value="{{ \Auth::user()->name }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('Verification Remarks / Handover Notes') }}</label>
                            <textarea name="manager_remarks" class="form-control" rows="2" placeholder="{{ __('e.g., Verified laptop, keys and code repository access revoked on last working day.') }}"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold d-block">{{ __('Draw Your Digital Signature') }} <span class="text-danger">*</span></label>
                            <div class="border rounded bg-light p-1">
                                <canvas id="managerSigCanvas" width="450" height="130" style="width: 100%; height: 130px; background: #fff; cursor: crosshair; touch-action: none; border-radius: 4px;"></canvas>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <button type="button" id="clearManagerSigBtn" class="btn btn-xs btn-outline-danger">
                                    <i class="ti ti-eraser"></i> {{ __('Clear') }}
                                </button>
                                <small class="text-muted">{{ __('Draw using mouse or touchscreen') }}</small>
                            </div>
                            <input type="hidden" name="manager_signature" id="manager_signature_input" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-info text-white" id="submitManagerSigBtn">{{ __('Confirm & Sign') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL 2: Management Final Disbursal Sign-off Modal --}}
    <div class="modal fade" id="managementSignoffModal" tabindex="-1" aria-labelledby="managementSignoffModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('settlement.management.signoff', $settlement->id) }}" method="POST" id="managementSignoffForm">
                    @csrf
                    <div class="modal-header bg-light">
                        <h5 class="modal-title" id="managementSignoffModalLabel"><i class="ti ti-shield-check me-2 text-success"></i>{{ __('Record Final Payment & Management Sign-off') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info py-2">
                            <i class="ti ti-info-circle me-1"></i> {{ __('Net settlement amount payable to employee: ') }}
                            <strong>{{ \Auth::user()->priceFormat($settlement->net_amount) }}</strong>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">{{ __('Payment Mode') }} <span class="text-danger">*</span></label>
                                <select name="payment_mode" class="form-select" required>
                                    <option value="Bank Transfer (NEFT/RTGS/IMPS)">Bank Transfer (NEFT/RTGS/IMPS)</option>
                                    <option value="Cheque">Cheque</option>
                                    <option value="UPI / Online">UPI / Online</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">{{ __('Payment Date') }} <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">{{ __('Transaction Ref (UTR / Cheque No.)') }} <span class="text-danger">*</span></label>
                                <input type="text" name="payment_reference_no" class="form-control" placeholder="{{ __('e.g., UTR1234567890') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">{{ __('HR Representative Name') }}</label>
                                <input type="text" name="hr_representative_name" class="form-control" value="{{ \Auth::user()->name }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">{{ __('Authorized Signatory Name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="authorized_signatory_name" class="form-control" value="{{ \Auth::user()->name }}" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold d-block">{{ __('Authorized Signature') }} <span class="text-danger">*</span></label>
                                <div class="border rounded bg-light p-1">
                                    <canvas id="managementSigCanvas" width="600" height="130" style="width: 100%; height: 130px; background: #fff; cursor: crosshair; touch-action: none; border-radius: 4px;"></canvas>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <button type="button" id="clearManagementSigBtn" class="btn btn-xs btn-outline-danger">
                                        <i class="ti ti-eraser"></i> {{ __('Clear') }}
                                    </button>
                                    <small class="text-muted">{{ __('Draw authorized signature using mouse or touchscreen') }}</small>
                                </div>
                                <input type="hidden" name="authorized_signature" id="authorized_signature_input" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-success" id="submitManagementSigBtn">{{ __('Confirm Disbursal & Mark Cleared') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
<script>
    $(document).on('click', '.copy-settlement-link', function () {
        var url = $(this).attr('data-url');
        navigator.clipboard.writeText(url).then(function () {
            show_toastr('Success', '{{ __("Secure settlement form link copied to clipboard!") }}', 'success');
        });
    });

    // Setup signature canvas helper
    function initCanvasPad(canvasId, clearBtnId, hiddenInputId) {
        var canvas = document.getElementById(canvasId);
        if (!canvas) return;
        var ctx = canvas.getContext('2d');
        var isDrawing = false;
        var hasDrawn = false;

        function getPos(e) {
            var rect = canvas.getBoundingClientRect();
            var clientX = e.touches ? e.touches[0].clientX : e.clientX;
            var clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: (clientX - rect.left) * (canvas.width / rect.width),
                y: (clientY - rect.top) * (canvas.height / rect.height)
            };
        }

        function start(e) {
            e.preventDefault();
            isDrawing = true;
            var p = getPos(e);
            ctx.beginPath();
            ctx.moveTo(p.x, p.y);
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = '#0f172a';
        }

        function move(e) {
            if (!isDrawing) return;
            e.preventDefault();
            var p = getPos(e);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
            hasDrawn = true;
        }

        function stop(e) {
            if (isDrawing) {
                isDrawing = false;
                if (hasDrawn) {
                    document.getElementById(hiddenInputId).value = canvas.toDataURL('image/png');
                }
            }
        }

        canvas.addEventListener('mousedown', start);
        canvas.addEventListener('mousemove', move);
        window.addEventListener('mouseup', stop);

        canvas.addEventListener('touchstart', start, { passive: false });
        canvas.addEventListener('touchmove', move, { passive: false });
        window.addEventListener('touchend', stop);

        var clearBtn = document.getElementById(clearBtnId);
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                hasDrawn = false;
                document.getElementById(hiddenInputId).value = '';
            });
        }
    }

    $('#managerCountersignModal').on('shown.bs.modal', function () {
        initCanvasPad('managerSigCanvas', 'clearManagerSigBtn', 'manager_signature_input');
    });

    $('#managementSignoffModal').on('shown.bs.modal', function () {
        initCanvasPad('managementSigCanvas', 'clearManagementSigBtn', 'authorized_signature_input');
    });

    $('#managerCountersignForm').on('submit', function (e) {
        if (!document.getElementById('manager_signature_input').value) {
            e.preventDefault();
            alert('{{ __("Please draw your signature before submitting.") }}');
        }
    });

    $('#managementSignoffForm').on('submit', function (e) {
        if (!document.getElementById('authorized_signature_input').value) {
            e.preventDefault();
            alert('{{ __("Please draw your authorized signature before confirming disbursal.") }}');
        }
    });
</script>
@endpush