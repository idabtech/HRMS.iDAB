@extends('layouts.admin')

@section('page-title')
    {{ __('Settlement Details: ') . $settlement->settlement_number }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('settlement.index') }}">{{ __('Full & Final Settlements') }}</a></li>
    <li class="breadcrumb-item">{{ $settlement->settlement_number }}</li>
@endsection

@push('css-page')
<style>
    .sig-tab-btn {
        padding: 5px 12px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .sig-tab-btn.active {
        background: var(--bs-primary, #584ed2);
        color: #ffffff;
        border-color: var(--bs-primary, #584ed2);
    }
    .upload-sig-dropzone {
        border: 2px dashed #cbd5e1;
        border-radius: 8px;
        padding: 16px;
        text-align: center;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .upload-sig-dropzone:hover {
        border-color: var(--bs-primary, #584ed2);
        background: #f1f5f9;
    }
</style>
@endpush

@section('action-button')
    <div class="d-inline-flex align-items-center gap-1">
        {{-- Copy Public Link --}}
        <a href="javascript:void(0)" class="btn btn-sm btn-secondary copy-settlement-link"
            data-url="{{ $settlement->public_url }}"
            data-bs-toggle="tooltip" title="" data-bs-original-title="{{ __('Copy Secure Link') }}">
            <i class="ti ti-link"></i>
        </a>

        {{-- WhatsApp Share --}}
        <a href="javascript:void(0)" class="btn btn-sm btn-success"
            onclick="$('#whatsappShareModal').modal('show');"
            data-bs-toggle="tooltip" title="" data-bs-original-title="{{ __('Share on WhatsApp') }}">
            <i class="ti ti-brand-whatsapp"></i>
        </a>

        {{-- Send Mail --}}
        @can('Send Settlement Mail')
            <a href="javascript:void(0)" class="btn btn-sm btn-warning text-white"
                onclick="$('#emailShareModal').modal('show');"
                data-bs-toggle="tooltip" title="" data-bs-original-title="{{ __('Send Email to Employee') }}">
                <i class="ti ti-mail"></i>
            </a>
        @endcan

        {{-- Regenerate / Extend Link --}}
        @if(Gate::check('Edit Settlement') || \Auth::user()->can('Manage Settlement'))
            <a href="{{ route('settlement.regenerate.link', $settlement->id) }}" class="btn btn-sm btn-dark"
                data-bs-toggle="tooltip" title="" data-bs-original-title="{{ __('Regenerate Link (Renew 30 Days)') }}">
                <i class="ti ti-refresh"></i>
            </a>
        @endif

        {{-- Recall to Draft (if sent) --}}
        @if($settlement->status === 'sent' && (Gate::check('Edit Settlement') || \Auth::user()->can('Manage Settlement')))
            <a href="{{ route('settlement.recall.draft', $settlement->id) }}" class="btn btn-sm btn-danger"
                data-bs-toggle="tooltip" title="" data-bs-original-title="{{ __('Recall to Draft') }}"
                onclick="return confirm('{{ __('Are you sure you want to recall this settlement to Draft? The employee link will be paused until re-sent.') }}');">
                <i class="ti ti-arrow-back-up"></i>
            </a>
        @endif

        {{-- Download PDF --}}
        <a href="{{ route('settlement.download.pdf', $settlement->id) }}" target="_blank" class="btn btn-sm btn-info text-white"
            data-bs-toggle="tooltip" title="" data-bs-original-title="{{ __('Download / Print PDF') }}">
            <i class="ti ti-download"></i>
        </a>

        {{-- Edit --}}
        @can('Edit Settlement')
            <a href="{{ route('settlement.edit', $settlement->id) }}" class="btn btn-sm btn-primary"
                data-bs-toggle="tooltip" title="" data-bs-original-title="{{ __('Edit') }}">
                <i class="ti ti-pencil"></i>
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
                                                    @elseif(($f['type'] ?? '') === 'file' && !empty($customValues[$f['key']]))
                                                        <a href="{{ $settlement->getAttachmentUrl($customValues[$f['key']]) }}" target="_blank" class="badge bg-light text-primary border text-decoration-none d-inline-flex align-items-center gap-1" style="font-size: 11px;">
                                                            <i class="ti ti-paperclip"></i> {{ __('View Attachment') }}
                                                        </a>
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
                                                @elseif(($f['type'] ?? '') === 'file' && !empty($customValues[$f['key']]))
                                                    <a href="{{ $settlement->getAttachmentUrl($customValues[$f['key']]) }}" target="_blank" class="badge bg-light text-primary border text-decoration-none d-inline-flex align-items-center gap-1" style="font-size: 11px;">
                                                        <i class="ti ti-paperclip"></i> {{ __('View Attachment') }}
                                                    </a>
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
        @php
            $clearanceItems = $settlement->clearance_data ?? [];
            $totalClearanceCount = count($clearanceItems);
            $clearedClearanceCount = count(array_filter($clearanceItems, fn($c) => ($c['status'] ?? '') === 'Returned'));
        @endphp
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="mb-0 text-dark">
                            <i class="ti ti-checklist me-2 text-primary"></i>{{ __('3. Departmental & Asset Clearances Checklist') }}
                        </h5>
                        <span class="badge {{ $clearedClearanceCount === $totalClearanceCount && $totalClearanceCount > 0 ? 'bg-success' : 'bg-primary-subtle text-primary border border-primary-subtle' }} px-2.5 py-1.5" id="clearanceSummaryBadge">
                            <i class="ti ti-checks me-1"></i><span id="clearedCountText">{{ $clearedClearanceCount }}</span> / {{ $totalClearanceCount }} {{ __('Cleared') }}
                        </span>
                    </div>
                    @if (\Auth::user()->can('Manage Settlement') || \Auth::user()->can('Edit Settlement'))
                        <div class="d-flex align-items-center gap-2">
                            <form action="{{ route('settlement.clearance.update', $settlement->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Mark all departmental clearance items as Returned / Cleared?') }}');">
                                @csrf
                                <input type="hidden" name="mark_all_cleared" value="1">
                                <button type="submit" class="btn btn-sm btn-outline-success shadow-none">
                                    <i class="ti ti-checks me-1"></i> {{ __('Mark All Cleared') }}
                                </button>
                            </form>
                            <button type="button" class="btn btn-sm btn-primary shadow-none" data-bs-toggle="modal" data-bs-target="#editClearanceModal">
                                <i class="ti ti-edit me-1"></i> {{ __('Update Checklist') }}
                            </button>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="18%">{{ __('Category') }}</th>
                                    <th width="42%">{{ __('Clearance Checkpoint / Handover Note') }}</th>
                                    <th width="18%">{{ __('Current Status') }}</th>
                                    @if (\Auth::user()->can('Manage Settlement') || \Auth::user()->can('Edit Settlement'))
                                        <th width="22%">{{ __('Verify / Quick Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($clearanceItems as $chk)
                                    <tr id="clearance_row_{{ $loop->index }}">
                                        <td>
                                            <span class="badge bg-light text-dark border font-monospace">{{ $chk['category'] ?? 'General' }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $chk['item'] }}</div>
                                            @if(!empty($chk['remarks']))
                                                <div class="p-2 bg-light rounded border border-primary border-opacity-25 small mt-1 text-dark">
                                                    <i class="ti ti-notes me-1 text-primary"></i><strong class="text-secondary">{{ __('Handover Note:') }}</strong> {{ $chk['remarks'] }}
                                                </div>
                                            @endif
                                            @if(!empty($chk['attachment']))
                                                <div class="mt-1">
                                                    <a href="{{ $settlement->getAttachmentUrl($chk['attachment']) }}" target="_blank" class="badge bg-light text-primary border text-decoration-none d-inline-flex align-items-center gap-1" style="font-size: 11px;">
                                                        <i class="ti ti-file-check"></i> {{ __('Attachment: ') . \Illuminate\Support\Str::limit($chk['attachment_name'] ?? $chk['attachment'], 24) }}
                                                    </a>
                                                </div>
                                            @endif
                                        </td>
                                        <td id="clearance_status_badge_{{ $loop->index }}">
                                            @if (($chk['status'] ?? '') === 'Returned')
                                                <span class="badge bg-success"><i class="ti ti-check me-1"></i>{{ __('Returned / Cleared') }}</span>
                                            @elseif (($chk['status'] ?? '') === 'Not Applicable')
                                                <span class="badge bg-secondary">{{ __('Not Applicable') }}</span>
                                            @else
                                                <span class="badge bg-warning text-dark"><i class="ti ti-clock me-1"></i>{{ __('Pending') }}</span>
                                            @endif
                                        </td>
                                        @if (\Auth::user()->can('Manage Settlement') || \Auth::user()->can('Edit Settlement'))
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <select class="form-select form-select-sm quick-clearance-status-select"
                                                            data-idx="{{ $loop->index }}"
                                                            data-url="{{ route('settlement.clearance.update', $settlement->id) }}"
                                                            style="min-width: 140px; font-size: 12px;">
                                                        <option value="Pending" {{ ($chk['status'] ?? '') === 'Pending' ? 'selected' : '' }}>⏱ {{ __('Pending') }}</option>
                                                        <option value="Returned" {{ ($chk['status'] ?? '') === 'Returned' ? 'selected' : '' }}>✓ {{ __('Returned / Cleared') }}</option>
                                                        <option value="Not Applicable" {{ ($chk['status'] ?? '') === 'Not Applicable' ? 'selected' : '' }}>— {{ __('Not Applicable') }}</option>
                                                    </select>
                                                    @if (($chk['status'] ?? '') !== 'Returned')
                                                        <button type="button"
                                                                class="btn btn-sm btn-success quick-mark-cleared-btn flex-shrink-0 text-white"
                                                                data-idx="{{ $loop->index }}"
                                                                data-url="{{ route('settlement.clearance.update', $settlement->id) }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('1-Click Mark Cleared') }}">
                                                            <i class="ti ti-check"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr><td colspan="{{ (\Auth::user()->can('Manage Settlement') || \Auth::user()->can('Edit Settlement')) ? '4' : '3' }}" class="text-center text-muted py-3">{{ __('No clearance checkpoints recorded.') }}</td></tr>
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
                                                @elseif(($f['type'] ?? '') === 'file' && !empty($customValues[$f['key']]))
                                                    <a href="{{ $settlement->getAttachmentUrl($customValues[$f['key']]) }}" target="_blank" class="badge bg-light text-primary border text-decoration-none d-inline-flex align-items-center gap-1" style="font-size: 11px;">
                                                        <i class="ti ti-paperclip"></i> {{ __('View Attachment') }}
                                                    </a>
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
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 text-dark">
                        <i class="ti ti-writing me-2 text-primary"></i>{{ __('4. Stage 1: Employee Clearance & Signature') }}
                    </h5>
                    @if ($settlement->employee_declaration_accepted)
                        <span class="badge bg-success fs-7"><i class="ti ti-check me-1"></i>{{ __('Signed & Undertaking Accepted') }}</span>
                    @else
                        <span class="badge bg-warning text-dark fs-7"><i class="ti ti-clock me-1"></i>{{ __('Awaiting Employee Sign-off') }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        {{-- Left Column: Legal Declaration, Policy Rules & Custom Questions --}}
                        <div class="col-lg-7 border-end">
                            {{-- Legal Undertaking Terms --}}
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-1.5">
                                    <span class="text-dark fw-bold small">
                                        <i class="ti ti-file-certificate text-primary me-1"></i>{{ __('Legal Declaration & Undertaking Terms:') }}
                                    </span>
                                    <span class="badge bg-light text-muted border font-monospace" style="font-size: 10px;">{{ __('Standard NDA / Exit Terms') }}</span>
                                </div>
                                <div class="p-3 bg-light rounded border small text-dark" style="max-height: 135px; overflow-y: auto; line-height: 1.6;">
                                    @php
                                        $showParagraphs = array_filter(array_map('trim', explode("\n", $settlement->getDeclarationText())));
                                    @endphp
                                    @foreach($showParagraphs as $p)
                                        <p class="mb-1.5 text-secondary">{{ $p }}</p>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Policy & Rules Information --}}
                            <div class="p-3 rounded bg-light border mb-3">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div class="d-flex align-items-start gap-2.5">
                                        <div class="p-2 bg-primary-subtle text-primary rounded mt-0.5">
                                            <i class="ti ti-shield-check fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark small">
                                                {{ __('Company Policy & Separation Rules') }}
                                                @if($settlement->policy_rules_accepted || $settlement->employee_declaration_accepted)
                                                    <span class="badge bg-success-subtle text-success ms-1"><i class="ti ti-check me-0.5"></i>{{ __('Accepted by Employee') }}</span>
                                                @endif
                                            </div>
                                            <div class="text-muted small mt-0.5">{{ $settlement->getPolicyRulesText() }}</div>
                                        </div>
                                    </div>
                                    @if(!empty($settlement->policy_rules_link))
                                        <a href="{{ $settlement->policy_rules_link }}" target="_blank" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                            <i class="ti ti-external-link me-1"></i>{{ $settlement->policy_rules_title ?: __('View Policy') }}
                                        </a>
                                    @endif
                                </div>
                            </div>

                            {{-- Custom Employee Questions --}}
                            @if (!empty($fieldsBySection['employee']))
                                <div>
                                    <h6 class="fw-bold text-dark mb-2"><i class="ti ti-forms text-primary me-1"></i>{{ __('Employee Question Responses:') }}</h6>
                                    <div class="row g-2">
                                        @foreach ($fieldsBySection['employee'] as $f)
                                            <div class="col-md-6">
                                                <div class="p-2.5 bg-light rounded border h-100">
                                                    <small class="text-muted d-block">{{ $f['label'] }}</small>
                                                    <strong class="text-dark">
                                                        @if(($f['type'] ?? '') === 'date' && !empty($customValues[$f['key']]))
                                                            {{ $settlement->formatDate($customValues[$f['key']]) }}
                                                        @elseif(($f['type'] ?? '') === 'file' && !empty($customValues[$f['key']]))
                                                            <a href="{{ $settlement->getAttachmentUrl($customValues[$f['key']]) }}" target="_blank" class="badge bg-light text-primary border text-decoration-none d-inline-flex align-items-center gap-1" style="font-size: 11px;">
                                                                <i class="ti ti-paperclip"></i> {{ __('View Attachment') }}
                                                            </a>
                                                        @else
                                                            {{ $customValues[$f['key']] ?? '— (Not answered yet)' }}
                                                        @endif
                                                    </strong>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Right Column: Employee Digital Signature Status --}}
                        <div class="col-lg-5">
                            @if ($settlement->employee_declaration_accepted)
                                <div class="alert alert-success d-flex align-items-center gap-2 mb-3 py-2.5">
                                    <i class="ti ti-check-circle fs-4 text-success flex-shrink-0"></i>
                                    <div class="small">
                                        <strong>{{ __('Clearance Undertaking Confirmed') }}</strong><br>
                                        {{ __('Employee confirmed compliance with IP, non-disclosure & asset handover.') }}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <span class="text-muted small d-block mb-1">{{ __('Employee Digital Signature:') }}</span>
                                    <div class="border rounded p-3 text-center bg-white shadow-xs">
                                        @if ($settlement->employee_signature)
                                            <img src="{{ $settlement->employee_signature }}" alt="Employee Signature" style="max-height: 95px; max-width: 100%; object-fit: contain;">
                                        @else
                                            <span class="text-muted small">{{ __('No signature image recorded') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="p-3 bg-light rounded border small">
                                    <div class="mb-1.5"><strong><i class="ti ti-calendar-time text-primary me-1"></i>{{ __('Signed Timestamp:') }}</strong> {{ $settlement->employee_signed_at ? $settlement->formatDate($settlement->employee_signed_at, true) : '-' }}</div>
                                    <div class="mb-1.5"><strong><i class="ti ti-map-pin text-primary me-1"></i>{{ __('IP Address:') }}</strong> <span class="font-monospace text-dark">{{ $settlement->employee_signed_ip ?: '-' }}</span></div>
                                    @if ($settlement->employee_remarks)
                                        <div class="mt-2 pt-2 border-top">
                                            <strong><i class="ti ti-message text-primary me-1"></i>{{ __('Handover Remarks:') }}</strong>
                                            <div class="mt-1 text-secondary fst-italic">"{{ $settlement->employee_remarks }}"</div>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="border rounded p-4 text-center bg-light h-100 d-flex flex-column justify-content-center align-items-center">
                                    <div class="p-3 bg-warning-subtle text-warning rounded-circle d-inline-block mb-3">
                                        <i class="ti ti-clock fs-1 text-warning"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">{{ __('Awaiting Employee Digital Signature') }}</h6>
                                    <p class="small text-muted mb-3" style="max-width: 320px;">
                                        {{ __('The employee has not submitted their clearance sign-off yet. Share the secure link via WhatsApp or email.') }}
                                    </p>
                                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                                        <button type="button" class="btn btn-sm btn-primary copy-settlement-link" data-url="{{ $settlement->public_url }}">
                                            <i class="ti ti-link me-1"></i> {{ __('Copy Link') }}
                                        </button>
                                        <button type="button" class="btn btn-sm btn-success" onclick="$('#whatsappShareModal').modal('show');">
                                            <i class="ti ti-brand-whatsapp me-1"></i> {{ __('WhatsApp') }}
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning text-white" onclick="$('#emailShareModal').modal('show');">
                                            <i class="ti ti-mail me-1"></i> {{ __('Email') }}
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 5: Stage 2 — Department Manager / HOD Countersign --}}
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 text-dark"><i class="ti ti-user-check me-2 text-info"></i>{{ __('5. Stage 2: Manager / HOD Countersign') }}</h5>
                    @if ($settlement->manager_signed_at)
                        <span class="badge bg-success fs-7"><i class="ti ti-check me-1"></i>{{ __('Verified & Signed') }}</span>
                    @else
                        <span class="badge bg-secondary fs-7"><i class="ti ti-clock me-1"></i>{{ __('Pending') }}</span>
                    @endif
                </div>
                <div class="card-body">
                    @if ($settlement->manager_signed_at)
                        <div class="alert alert-success mb-3 py-2">
                            <i class="ti ti-check-circle me-1"></i> {{ __('Handover items verified and countersigned by Department Manager.') }}
                        </div>
                        <div class="row g-4 align-items-center">
                            <div class="col-lg-7 border-end">
                                <div class="row g-3 mb-3">
                                    <div class="col-sm-6">
                                        <span class="text-muted small d-block">{{ __('Manager / HOD Name') }}</span>
                                        <div class="fw-bold fs-6 text-dark">{{ $settlement->manager_name }}</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted small d-block">{{ __('Countersigned Date') }}</span>
                                        <div class="fw-bold fs-6 text-dark">{{ $settlement->formatDate($settlement->manager_signed_at, true) }}</div>
                                    </div>
                                </div>
                                @if ($settlement->manager_remarks)
                                    <div class="p-3 bg-light rounded border small text-dark">
                                        <strong><i class="ti ti-notes text-info me-1"></i>{{ __('Manager Verification Remarks:') }}</strong>
                                        <div class="mt-1 text-secondary">{{ $settlement->manager_remarks }}</div>
                                    </div>
                                @endif
                            </div>
                            <div class="col-lg-5">
                                <span class="text-muted small d-block mb-1">{{ __('Manager Signature:') }}</span>
                                <div class="border rounded p-3 text-center bg-white shadow-xs">
                                    @if ($settlement->manager_signature)
                                        <img src="{{ $settlement->manager_signature }}" alt="Manager Signature" style="max-height: 90px; max-width: 100%; object-fit: contain;">
                                    @else
                                        <span class="text-muted small">{{ __('No signature recorded') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="ti ti-signature fs-1 d-block mb-2 text-info"></i>
                            <h6 class="fw-bold text-dark">{{ __('Manager Handover Verification') }}</h6>
                            <p class="small text-muted mb-3" style="max-width: 480px; margin: 0 auto;">{{ __('Department Manager or HOD reviews asset handover and provides digital verification countersignature.') }}</p>
                            @if(\Auth::user()->can('Manage Settlement') || \Auth::user()->can('Edit Settlement'))
                                <button type="button" class="btn btn-sm btn-info text-white shadow-none" data-bs-toggle="modal" data-bs-target="#managerCountersignModal">
                                    <i class="ti ti-pencil me-1"></i> {{ __('Countersign as Manager / HOD') }}
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Section 6: Stage 3 — Company Authorized Signatory & Disbursal Approval --}}
        @php
            $companySettings = \App\Models\Utility::getCompanySettings($settlement->created_by);
            $companyName = !empty($companySettings['company_name']) ? $companySettings['company_name'] : ($settlement->creator?->name ?? 'Company');
        @endphp
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark"><i class="ti ti-shield-check me-2 text-success"></i>{{ __('6. Stage 3: Company Authorized Signatory & Disbursal Approval') }}</h5>
                    @if ($settlement->final_settlement_status === 'Cleared')
                        <span class="badge bg-success fs-6"><i class="ti ti-check me-1"></i>{{ __('Fully Cleared & Disbursed') }}</span>
                    @else
                        <span class="badge bg-warning text-dark fs-6"><i class="ti ti-clock me-1"></i>{{ __('Disbursal Pending') }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        {{-- Left Column: Company Authorized Signatory Endorsement --}}
                        <div class="col-md-6 border-end">
                            <span class="badge bg-primary-subtle text-primary border mb-2 px-2.5 py-1">
                                <i class="ti ti-certificate me-1"></i>{{ __('Company Authorized Sign-off') }}
                            </span>
                            <div class="mb-2">
                                <small class="text-muted d-block">{{ __('For & On Behalf of:') }}</small>
                                <strong class="text-dark fs-6">{{ $companyName }}</strong>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted d-block">{{ __('Authorized Signatory Name:') }}</small>
                                <strong class="text-dark">{{ $settlement->authorized_signatory_name ?: __('Pending Sign-off') }}</strong>
                                <div class="text-muted small">{{ __('Company Authorized Signatory') }}</div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted d-block">{{ __('Authorization Date:') }}</small>
                                <strong class="text-dark">
                                    {{ $settlement->authorized_date ? $settlement->formatDate($settlement->authorized_date) : ($settlement->payment_date ? $settlement->formatDate($settlement->payment_date) : __('Pending')) }}
                                </strong>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted d-block mb-1">{{ __('Authorized Digital Signature:') }}</small>
                                <div class="p-2 border rounded bg-white text-center" style="max-width: 320px;">
                                    @if($settlement->authorized_signature)
                                        <img src="{{ $settlement->authorized_signature }}" alt="Company Authorized Signature" style="max-height: 70px; max-width: 100%;">
                                        <div class="mt-1">
                                            <span class="badge bg-light text-primary border font-monospace" style="font-size: 10px;">
                                                <i class="ti ti-shield-check me-1"></i>{{ __('OFFICIAL SEAL & SIGN-OFF') }}
                                            </span>
                                        </div>
                                    @else
                                        <div class="py-3 text-muted small">
                                            <i class="ti ti-pencil me-1"></i>{{ __('Authorized digital signature pending') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Right Column: Disbursal & Payment Settlement Record --}}
                        <div class="col-md-6">
                            <span class="badge bg-success-subtle text-success border mb-2 px-2.5 py-1">
                                <i class="ti ti-wallet me-1"></i>{{ __('Disbursal & Payment Record') }}
                            </span>
                            <div class="row g-3">
                                <div class="col-6">
                                    <small class="text-muted d-block">{{ __('Settlement Status') }}</small>
                                    <div class="mt-1">
                                        @if ($settlement->final_settlement_status === 'Cleared')
                                            <span class="badge bg-success fs-7">{{ __('Cleared & Paid') }}</span>
                                        @else
                                            <span class="badge bg-warning text-dark fs-7">{{ __('Pending Payment') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">{{ __('Net Amount Disbursed') }}</small>
                                    <div class="fw-bold fs-5 text-success">{{ \Auth::user()->priceFormat($settlement->net_amount) }}</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">{{ __('Payment Mode') }}</small>
                                    <div class="fw-bold text-dark">{{ $settlement->payment_mode ?: '—' }}</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">{{ __('Disbursal Date') }}</small>
                                    <div class="fw-bold text-dark">{{ $settlement->payment_date ? $settlement->formatDate($settlement->payment_date) : '—' }}</div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted d-block">{{ __('UTR / Transaction Reference No.') }}</small>
                                    <div class="fw-bold text-primary font-monospace fs-6">{{ $settlement->payment_reference_no ?: '—' }}</div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted d-block">{{ __('HR Verification Desk') }}</small>
                                    <div class="text-dark">{{ $settlement->hr_representative_name ?: '—' }}</div>
                                    @if($settlement->hr_cleared_at)
                                        <small class="text-muted">{{ __('Cleared on: ') . $settlement->formatDate($settlement->hr_cleared_at, true) }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($settlement->final_settlement_status !== 'Cleared' && (Gate::check('Edit Settlement') || \Auth::user()->can('Manage Settlement')))
                        <div class="mt-4 pt-3 border-top text-end">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#managementSignoffModal">
                                <i class="ti ti-check me-1"></i> {{ __('Authorize Settlement & Confirm Disbursal') }}
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
                                            <i class="ti ti-user me-1"></i>{{ $log['performed_by'] ?? __('System') }}
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

    {{-- MODAL 1: Manager Handover Countersign Modal --}}
    <div class="modal fade" id="managerCountersignModal" tabindex="-1" aria-labelledby="managerCountersignModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('settlement.manager.countersign', $settlement->id) }}" method="POST" id="managerCountersignForm">
                    @csrf
                    <div class="modal-header bg-light">
                        <h5 class="modal-title" id="managerCountersignModalLabel"><i class="ti ti-user-check me-2 text-info"></i>{{ __('Manager / HOD Handover Countersign') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('Manager / HOD Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="manager_name" class="form-control" value="{{ \Auth::user()->name }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('Verification Remarks / Handover Notes') }}</label>
                            <textarea name="manager_remarks" class="form-control" rows="2" placeholder="{{ __('e.g., All physical assets, credentials, and documentation verified and accepted in good order.') }}"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold d-block mb-2">{{ __('Manager Digital Signature') }} <span class="text-danger">*</span></label>
                            
                            {{-- Tab Switcher --}}
                            <div class="d-flex gap-2 mb-2">
                                <button type="button" class="sig-tab-btn active" id="managerTabDrawBtn" onclick="switchSigMode('manager', 'draw')">
                                    <i class="ti ti-pencil me-1"></i> {{ __('Draw Signature') }}
                                </button>
                                <button type="button" class="sig-tab-btn" id="managerTabUploadBtn" onclick="switchSigMode('manager', 'upload')">
                                    <i class="ti ti-upload me-1"></i> {{ __('Upload Signature Image') }}
                                </button>
                            </div>

                            {{-- Draw Canvas Container --}}
                            <div id="managerSigDrawContainer">
                                <div class="border rounded bg-light p-1">
                                    <canvas id="managerSigCanvas" width="450" height="120" style="width: 100%; height: 120px; background: #fff; cursor: crosshair; touch-action: none; border-radius: 4px;"></canvas>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <button type="button" id="clearManagerSigBtn" class="btn btn-xs btn-outline-danger">
                                        <i class="ti ti-eraser me-1"></i> {{ __('Clear & Redraw') }}
                                    </button>
                                    <small class="text-muted">{{ __('Draw signature using mouse, trackpad, or touch') }}</small>
                                </div>
                            </div>

                            {{-- Upload File Container --}}
                            <div id="managerSigUploadContainer" style="display: none;">
                                <div id="managerDropzoneArea" class="upload-sig-dropzone" onclick="document.getElementById('managerSigFileInput').click()">
                                    <i class="ti ti-cloud-upload fs-1 text-primary mb-1 d-block"></i>
                                    <span class="fw-bold text-dark d-block">{{ __('Click or Drag & Drop Signature Image') }}</span>
                                    <small class="text-muted">{{ __('Accepted: PNG, JPG, JPEG (Max 5MB)') }}</small>
                                    <input type="file" id="managerSigFileInput" accept="image/png,image/jpeg,image/jpg" style="display: none;">
                                </div>
                                <div id="managerUploadedPreviewWrapper" class="mt-2 text-center" style="display: none;">
                                    <div class="p-2 border rounded bg-white d-inline-block shadow-xs mb-2">
                                        <img id="managerSigImagePreview" src="" alt="Signature Preview" style="max-height: 80px; max-width: 260px; object-fit: contain;">
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-xs btn-outline-danger" onclick="removeUploadedSig('manager')">
                                            <i class="ti ti-trash me-1"></i> {{ __('Remove & Choose Another') }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="manager_signature" id="manager_signature_input" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-info text-white" id="submitManagerSigBtn">{{ __('Confirm Countersign') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL 2: Company Authorized Signatory & Disbursal Approval Modal --}}
    <div class="modal fade" id="managementSignoffModal" tabindex="-1" aria-labelledby="managementSignoffModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('settlement.management.signoff', $settlement->id) }}" method="POST" id="managementSignoffForm">
                    @csrf
                    <div class="modal-header bg-light">
                        <h5 class="modal-title" id="managementSignoffModalLabel"><i class="ti ti-shield-check me-2 text-success"></i>{{ __('Company Authorized Sign-off & Disbursal Approval') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info py-2 mb-3">
                            <i class="ti ti-info-circle me-1"></i> {{ __('Net settlement amount payable to employee: ') }}
                            <strong>{{ \Auth::user()->priceFormat($settlement->net_amount) }}</strong>
                        </div>

                        {{-- Section A: Payment Disbursal Records --}}
                        <div class="p-3 bg-light rounded border mb-3">
                            <h6 class="fw-bold text-dark mb-2"><i class="ti ti-wallet text-success me-1"></i>{{ __('1. Payment Disbursal Particulars') }}</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small">{{ __('Payment Mode') }} <span class="text-danger">*</span></label>
                                    <select name="payment_mode" class="form-select form-select-sm" required>
                                        <option value="Bank Transfer (NEFT/RTGS/IMPS)">Bank Transfer (NEFT/RTGS/IMPS)</option>
                                        <option value="Cheque">Cheque</option>
                                        <option value="UPI / Online">UPI / Online</option>
                                        <option value="Cash">Cash</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small">{{ __('Payment Date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="payment_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small">{{ __('Transaction Ref (UTR / Cheque No.)') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="payment_reference_no" class="form-control form-control-sm" placeholder="{{ __('e.g., UTR1234567890') }}" required>
                                </div>
                            </div>
                        </div>

                        {{-- Section B: Company Authorized Signatory Endorsement --}}
                        <div class="p-3 bg-light rounded border">
                            <h6 class="fw-bold text-dark mb-2"><i class="ti ti-certificate text-primary me-1"></i>{{ __('2. Company Authorized Signatory Endorsement') }}</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">{{ __('Authorized Signatory Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="authorized_signatory_name" class="form-control form-control-sm" value="{{ \Auth::user()->name }}" placeholder="{{ __('Director / Authorized Officer') }}" required>
                                    <small class="text-muted">{{ __('Signing on behalf of: ') }}<strong>{{ $companyName }}</strong></small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">{{ __('HR Representative Name') }}</label>
                                    <input type="text" name="hr_representative_name" class="form-control form-control-sm" value="{{ \Auth::user()->name }}">
                                    <small class="text-muted">{{ __('HR Representative processing verification') }}</small>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold small d-block mb-2">{{ __('Authorized Digital Signature') }} <span class="text-danger">*</span></label>
                                    
                                    {{-- Tab Switcher --}}
                                    <div class="d-flex gap-2 mb-2">
                                        <button type="button" class="sig-tab-btn active" id="authTabDrawBtn" onclick="switchSigMode('auth', 'draw')">
                                            <i class="ti ti-pencil me-1"></i> {{ __('Draw Signature') }}
                                        </button>
                                        <button type="button" class="sig-tab-btn" id="authTabUploadBtn" onclick="switchSigMode('auth', 'upload')">
                                            <i class="ti ti-upload me-1"></i> {{ __('Upload Signature Image') }}
                                        </button>
                                    </div>

                                    {{-- Draw Canvas Container --}}
                                    <div id="authSigDrawContainer">
                                        <div class="border rounded bg-white p-1">
                                            <canvas id="managementSigCanvas" width="600" height="130" style="width: 100%; height: 130px; background: #fff; cursor: crosshair; touch-action: none; border-radius: 4px;"></canvas>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-1">
                                            <button type="button" id="clearManagementSigBtn" class="btn btn-xs btn-outline-danger">
                                                <i class="ti ti-eraser me-1"></i> {{ __('Clear & Redraw') }}
                                            </button>
                                            <small class="text-muted">{{ __('Draw authorized signature using mouse, trackpad, or touch') }}</small>
                                        </div>
                                    </div>

                                    {{-- Upload File Container --}}
                                    <div id="authSigUploadContainer" style="display: none;">
                                        <div id="authDropzoneArea" class="upload-sig-dropzone" onclick="document.getElementById('authSigFileInput').click()">
                                            <i class="ti ti-cloud-upload fs-1 text-primary mb-1 d-block"></i>
                                            <span class="fw-bold text-dark d-block">{{ __('Click or Drag & Drop Authorized Signature Image') }}</span>
                                            <small class="text-muted">{{ __('Accepted: PNG, JPG, JPEG (Max 5MB)') }}</small>
                                            <input type="file" id="authSigFileInput" accept="image/png,image/jpeg,image/jpg" style="display: none;">
                                        </div>
                                        <div id="authUploadedPreviewWrapper" class="mt-2 text-center" style="display: none;">
                                            <div class="p-2 border rounded bg-white d-inline-block shadow-xs mb-2">
                                                <img id="authSigImagePreview" src="" alt="Authorized Signature Preview" style="max-height: 80px; max-width: 260px; object-fit: contain;">
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-xs btn-outline-danger" onclick="removeUploadedSig('auth')">
                                                    <i class="ti ti-trash me-1"></i> {{ __('Remove & Choose Another') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <input type="hidden" name="authorized_signature" id="authorized_signature_input" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-success" id="submitManagementSigBtn">
                            <i class="ti ti-check me-1"></i> {{ __('Authorize Settlement & Record Disbursal') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: Update Clearance Checklist (Batch) --}}
    <div class="modal fade" id="editClearanceModal" tabindex="-1" aria-labelledby="editClearanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content shadow-lg border-0">
                <form action="{{ route('settlement.clearance.update', $settlement->id) }}" method="POST" id="editClearanceChecklistForm">
                    @csrf
                    <div class="modal-header bg-primary text-white py-3">
                        <h5 class="modal-title text-white d-flex align-items-center gap-2" id="editClearanceModalLabel">
                            <i class="ti ti-checklist fs-3"></i> {{ __('Update Departmental Clearances & Verification') }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
                        <div class="alert alert-info py-2 mb-3 small">
                            <i class="ti ti-info-circle me-1"></i> {{ __('Review handover notes provided by the departing employee and update clearance statuses for each checkpoint.') }}
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="20%">{{ __('Category') }}</th>
                                        <th width="45%">{{ __('Item & Verification Note') }}</th>
                                        <th width="35%">{{ __('Clearance Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($settlement->clearance_data ?? [] as $idx => $item)
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark border font-monospace">{{ $item['category'] ?? 'General' }}</span>
                                            </td>
                                            <td>
                                                <strong class="text-dark d-block mb-1">{{ $item['item'] }}</strong>
                                                <input type="text"
                                                       name="clearance_remarks[{{ $idx }}]"
                                                       class="form-control form-control-sm"
                                                       value="{{ $item['remarks'] ?? '' }}"
                                                       placeholder="{{ __('Verification / Handover note...') }}">
                                                @if(!empty($item['attachment']))
                                                    <div class="mt-1">
                                                        <a href="{{ $settlement->getAttachmentUrl($item['attachment']) }}" target="_blank" class="badge bg-light text-primary border text-decoration-none d-inline-flex align-items-center gap-1" style="font-size: 11px;">
                                                            <i class="ti ti-file-check"></i> {{ \Illuminate\Support\Str::limit($item['attachment_name'] ?? $item['attachment'], 24) }}
                                                        </a>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <select name="clearance_statuses[{{ $idx }}]" class="form-select form-select-sm">
                                                    <option value="Pending" {{ ($item['status'] ?? '') === 'Pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                                    <option value="Returned" {{ ($item['status'] ?? '') === 'Returned' ? 'selected' : '' }}>{{ __('Returned / Cleared') }}</option>
                                                    <option value="Not Applicable" {{ ($item['status'] ?? '') === 'Not Applicable' ? 'selected' : '' }}>{{ __('Not Applicable') }}</option>
                                                </select>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-muted">{{ __('No clearance checkpoints recorded.') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitClearanceUpdate">
                            <i class="ti ti-device-floppy me-1"></i> {{ __('Save Clearance Updates') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- WhatsApp Custom Message Modal --}}
    <div class="modal fade" id="whatsappShareModal" tabindex="-1" aria-labelledby="whatsappShareModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title text-white d-flex align-items-center gap-2" id="whatsappShareModalLabel">
                        <i class="ti ti-brand-whatsapp fs-3"></i> {{ __('Share Settlement via WhatsApp') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small text-uppercase">{{ __('Recipient Employee') }}</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="ti ti-user"></i></span>
                            <input type="text" class="form-control bg-light" value="{{ $settlement->employee_name }} ({{ $settlement->employee_code }})" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small text-uppercase d-flex justify-content-between align-items-center">
                            <span>
                                {{ __('Phone Number') }} 
                                <small class="text-muted fw-normal">({{ __('Optional with Country Code') }})</small>
                            </span>
                            @if(!empty($settlement->employee->phone))
                                <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 text-primary small" id="btnResetWaPhone">
                                    <i class="ti ti-rotate-clockwise"></i> {{ __('Reset to Profile Number') }}
                                </button>
                            @endif
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="ti ti-phone"></i></span>
                            <input type="text" id="waRecipientPhone" class="form-control" 
                                placeholder="{{ __('e.g. 919876543210 (Country code + Number)') }}" 
                                value="{{ preg_replace('/[^0-9]/', '', $settlement->employee->phone ?? '') }}">
                        </div>
                        <small class="text-muted fs-8 d-block mt-1">
                            <i class="ti ti-edit text-primary me-1"></i>
                            {{ __('Pre-filled from employee profile. You can edit this to any different number, or leave blank to select inside WhatsApp.') }}
                        </small>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold text-dark small text-uppercase mb-0">{{ __('WhatsApp Message') }}</label>
                            <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 text-primary small" id="btnResetWaMessage">
                                <i class="ti ti-rotate-clockwise"></i> {{ __('Reset to Default') }}
                            </button>
                        </div>
                        <textarea id="waCustomMessage" class="form-control font-sans" rows="5" placeholder="{{ __('Type your message here...') }}"></textarea>
                        <small class="text-muted fs-8 mt-1 d-block">
                            <i class="ti ti-info-circle"></i> {{ __('You can freely modify this message, add greetings, or customize instructions before sending.') }}
                        </small>
                    </div>

                    <div class="p-3 bg-light rounded border">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold small text-dark"><i class="ti ti-link me-1"></i>{{ __('Clearance Form URL:') }}</span>
                            <button type="button" class="btn btn-xs btn-outline-secondary copy-settlement-link" data-url="{{ $settlement->public_url }}">
                                <i class="ti ti-copy"></i> {{ __('Copy') }}
                            </button>
                        </div>
                        <div class="small text-muted text-break font-monospace">{{ $settlement->public_url }}</div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-success" id="btnSendWaCustom">
                        <i class="ti ti-brand-whatsapp me-1"></i> {{ __('Open in WhatsApp') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Send Email Custom Message Modal --}}
    <div class="modal fade" id="emailShareModal" tabindex="-1" aria-labelledby="emailShareModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content shadow-lg border-0">
                <form action="{{ route('settlement.send.mail', $settlement->id) }}" method="POST" id="emailShareForm">
                    @csrf
                    <div class="modal-header bg-warning text-white py-3">
                        <h5 class="modal-title text-white d-flex align-items-center gap-2" id="emailShareModalLabel">
                            <i class="ti ti-mail fs-3"></i> {{ __('Send Clearance Form via Email') }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-dark small text-uppercase">{{ __('Recipient Employee') }}</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i class="ti ti-user"></i></span>
                                    <input type="text" class="form-control bg-light" value="{{ $settlement->employee_name }} ({{ $settlement->employee_code }})" readonly>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-dark small text-uppercase d-flex justify-content-between align-items-center">
                                    <span>
                                        {{ __('Recipient Email Address') }} <span class="text-danger">*</span>
                                    </span>
                                    @if(!empty($settlement->employee->email))
                                        <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 text-primary small" id="btnResetEmailAddress">
                                            <i class="ti ti-rotate-clockwise"></i> {{ __('Reset to Profile Email') }}
                                        </button>
                                    @endif
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i class="ti ti-mail"></i></span>
                                    <input type="email" id="emailRecipientAddress" name="recipient_email" class="form-control" 
                                        placeholder="{{ __('e.g. employee.personal@gmail.com') }}" 
                                        value="{{ $settlement->employee->email ?? '' }}" required>
                                </div>
                                <small class="text-muted fs-8 d-block mt-1">
                                    <i class="ti ti-edit text-primary me-1"></i>
                                    {{ __('Pre-filled from employee profile. You can edit this to any personal or alternate email address.') }}
                                </small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small text-uppercase">{{ __('Email Subject') }}</label>
                            <input type="text" id="emailSubject" name="email_subject" class="form-control"
                                value="{{ __('Full & Final Settlement & Clearance — ') . $settlement->settlement_number }}">
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold text-dark small text-uppercase mb-0">{{ __('Custom Email Message') }}</label>
                                <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 text-primary small" id="btnResetEmailMessage">
                                    <i class="ti ti-rotate-clockwise"></i> {{ __('Reset to Default') }}
                                </button>
                            </div>
                            <textarea id="emailCustomMessage" name="custom_message" class="form-control font-sans" rows="6" placeholder="{{ __('Type your message here...') }}"></textarea>
                            <small class="text-muted fs-8 mt-1 d-block">
                                <i class="ti ti-info-circle"></i> {{ __('You can freely customize this message, add instructions, or specify personal contact requests.') }}
                            </small>
                        </div>

                        <div class="p-3 bg-light rounded border">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold small text-dark"><i class="ti ti-link me-1"></i>{{ __('Clearance Form URL (Included in Email):') }}</span>
                                <button type="button" class="btn btn-xs btn-outline-secondary copy-settlement-link" data-url="{{ $settlement->public_url }}">
                                    <i class="ti ti-copy"></i> {{ __('Copy') }}
                                </button>
                            </div>
                            <div class="small text-muted text-break font-monospace">{{ $settlement->public_url }}</div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-warning text-white" id="btnSendEmailSubmit">
                            <i class="ti ti-send me-1"></i> {{ __('Send Email Now') }}
                        </button>
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

    // WhatsApp Message & Phone Customizer
    var defaultWaTemplate = "Hello " + {!! json_encode($settlement->employee_name) !!} + ", please review and complete your Full & Final Settlement & Departmental Clearance form: " + {!! json_encode($settlement->public_url) !!};
    var originalEmpPhone = {!! json_encode(preg_replace('/[^0-9]/', '', $settlement->employee->phone ?? '')) !!};

    $('#whatsappShareModal').on('show.bs.modal', function () {
        if (!$('#waCustomMessage').val()) {
            $('#waCustomMessage').val(defaultWaTemplate);
        }
        if (!$('#waRecipientPhone').val() && originalEmpPhone) {
            $('#waRecipientPhone').val(originalEmpPhone);
        }
    });

    $('#btnResetWaPhone').on('click', function () {
        $('#waRecipientPhone').val(originalEmpPhone);
    });

    $('#btnResetWaMessage').on('click', function () {
        $('#waCustomMessage').val(defaultWaTemplate);
    });

    $('#btnSendWaCustom').on('click', function () {
        var msg = $('#waCustomMessage').val().trim();
        if (!msg) {
            alert('{{ __("Please enter a message to send.") }}');
            return;
        }
        var phone = $('#waRecipientPhone').val().trim().replace(/[^0-9]/g, '');
        var url = "https://api.whatsapp.com/send?";
        var params = [];
        if (phone) {
            params.push("phone=" + encodeURIComponent(phone));
        }
        params.push("text=" + encodeURIComponent(msg));
        url += params.join('&');

        var btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> {{ __("Opening WhatsApp...") }}');

        window.open(url, '_blank');

        setTimeout(function () {
            $('#whatsappShareModal').modal('hide');
            btn.prop('disabled', false).html('<i class="ti ti-brand-whatsapp me-1"></i> {{ __("Open in WhatsApp") }}');
        }, 600);
    });

    $('#whatsappShareModal').on('hidden.bs.modal', function () {
        $('#btnSendWaCustom').prop('disabled', false).html('<i class="ti ti-brand-whatsapp me-1"></i> {{ __("Open in WhatsApp") }}');
    });

    // Email Message & Recipient Customizer
    var originalEmpEmail = {!! json_encode($settlement->employee->email ?? '') !!};
    var defaultEmailTemplate = "Dear " + {!! json_encode($settlement->employee_name) !!} + ",\n\n" +
        "Your Full & Final Settlement and Departmental Clearance statement (Ref: " + {!! json_encode($settlement->settlement_number) !!} + ") has been prepared for review.\n\n" +
        "Please review your financial breakdown, departmental clearance checkpoints, and submit your digital sign-off using the link below:\n\n" +
        {!! json_encode($settlement->public_url) !!} + "\n\n" +
        "Regards,\n" + {!! json_encode(\Auth::user()->name ?? 'HR Operations Team') !!};

    $('#emailShareModal').on('show.bs.modal', function () {
        if (!$('#emailCustomMessage').val()) {
            $('#emailCustomMessage').val(defaultEmailTemplate);
        }
        if (!$('#emailRecipientAddress').val() && originalEmpEmail) {
            $('#emailRecipientAddress').val(originalEmpEmail);
        }
    });

    $('#btnResetEmailAddress').on('click', function () {
        $('#emailRecipientAddress').val(originalEmpEmail);
    });

    $('#btnResetEmailMessage').on('click', function () {
        $('#emailCustomMessage').val(defaultEmailTemplate);
    });

    $('#emailShareForm').on('submit', function () {
        var submitBtn = $('#btnSendEmailSubmit');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> {{ __("Sending Email...") }}');
    });

    $('#emailShareModal').on('hidden.bs.modal', function () {
        $('#btnSendEmailSubmit').prop('disabled', false).html('<i class="ti ti-send me-1"></i> {{ __("Send Email Now") }}');
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

    // Signature Mode Switcher ('draw' or 'upload')
    function switchSigMode(prefix, mode) {
        var drawBtn = document.getElementById(prefix + 'TabDrawBtn');
        var uploadBtn = document.getElementById(prefix + 'TabUploadBtn');
        var drawContainer = document.getElementById(prefix === 'manager' ? 'managerSigDrawContainer' : 'authSigDrawContainer');
        var uploadContainer = document.getElementById(prefix === 'manager' ? 'managerSigUploadContainer' : 'authSigUploadContainer');

        if (mode === 'draw') {
            drawBtn.classList.add('active');
            uploadBtn.classList.remove('active');
            drawContainer.style.display = 'block';
            uploadContainer.style.display = 'none';
        } else {
            uploadBtn.classList.add('active');
            drawBtn.classList.remove('active');
            drawContainer.style.display = 'none';
            uploadContainer.style.display = 'block';
        }
    }

    // Handle Uploaded Signature File
    function setupSigUpload(fileInputId, dropzoneId, previewWrapperId, imagePreviewId, hiddenInputId) {
        var fileInput = document.getElementById(fileInputId);
        var dropzone = document.getElementById(dropzoneId);
        var previewWrapper = document.getElementById(previewWrapperId);
        var imagePreview = document.getElementById(imagePreviewId);
        var hiddenInput = document.getElementById(hiddenInputId);

        if (!fileInput) return;

        fileInput.addEventListener('change', function(e) {
            var file = e.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    alert('Signature file must be under 5MB.');
                    return;
                }
                var reader = new FileReader();
                reader.onload = function(evt) {
                    hiddenInput.value = evt.target.result;
                    imagePreview.src = evt.target.result;
                    dropzone.style.display = 'none';
                    previewWrapper.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        if (dropzone) {
            ['dragenter', 'dragover'].forEach(function(ev) {
                dropzone.addEventListener(ev, function(e) {
                    e.preventDefault();
                    dropzone.style.borderColor = '#584ed2';
                    dropzone.style.backgroundColor = '#eef2ff';
                });
            });
            ['dragleave', 'drop'].forEach(function(ev) {
                dropzone.addEventListener(ev, function(e) {
                    e.preventDefault();
                    dropzone.style.borderColor = '#cbd5e1';
                    dropzone.style.backgroundColor = '#f8fafc';
                });
            });
            dropzone.addEventListener('drop', function(e) {
                var dt = e.dataTransfer;
                if (dt && dt.files && dt.files[0]) {
                    fileInput.files = dt.files;
                    var evt = new Event('change');
                    fileInput.dispatchEvent(evt);
                }
            });
        }
    }

    function removeUploadedSig(prefix) {
        var hiddenInputId = prefix === 'manager' ? 'manager_signature_input' : 'authorized_signature_input';
        var fileInputId = prefix === 'manager' ? 'managerSigFileInput' : 'authSigFileInput';
        var dropzoneId = prefix === 'manager' ? 'managerDropzoneArea' : 'authDropzoneArea';
        var previewWrapperId = prefix === 'manager' ? 'managerUploadedPreviewWrapper' : 'authUploadedPreviewWrapper';
        var imagePreviewId = prefix === 'manager' ? 'managerSigImagePreview' : 'authSigImagePreview';

        document.getElementById(hiddenInputId).value = '';
        document.getElementById(fileInputId).value = '';
        document.getElementById(imagePreviewId).src = '';
        document.getElementById(previewWrapperId).style.display = 'none';
        document.getElementById(dropzoneId).style.display = 'block';
    }

    // Initialize upload listeners
    setupSigUpload('managerSigFileInput', 'managerDropzoneArea', 'managerUploadedPreviewWrapper', 'managerSigImagePreview', 'manager_signature_input');
    setupSigUpload('authSigFileInput', 'authDropzoneArea', 'authUploadedPreviewWrapper', 'authSigImagePreview', 'authorized_signature_input');

    $('#managerCountersignForm').on('submit', function (e) {
        if (!document.getElementById('manager_signature_input').value) {
            e.preventDefault();
            alert('{{ __("Please draw or upload your signature before submitting.") }}');
            return false;
        }
        $('#submitManagerSigBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> {{ __("Submitting...") }}');
    });

    $('#managementSignoffForm').on('submit', function (e) {
        if (!document.getElementById('authorized_signature_input').value) {
            e.preventDefault();
            alert('{{ __("Please draw or upload your authorized signature before confirming disbursal.") }}');
            return false;
        }
        $('#submitManagementSigBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> {{ __("Authorizing...") }}');
    });

    // Clearance Quick Status Selector AJAX
    $(document).on('change', '.quick-clearance-status-select', function () {
        var select = $(this);
        var idx = select.data('idx');
        var url = select.data('url');
        var newStatus = select.val();

        select.prop('disabled', true);

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                item_index: idx,
                status: newStatus
            },
            success: function (res) {
                select.prop('disabled', false);
                var badgeEl = $('#clearance_status_badge_' + idx);
                if (newStatus === 'Returned') {
                    badgeEl.html('<span class="badge bg-success"><i class="ti ti-check me-1"></i>{{ __("Returned / Cleared") }}</span>');
                    select.closest('td').find('.quick-mark-cleared-btn').remove();
                } else if (newStatus === 'Not Applicable') {
                    badgeEl.html('<span class="badge bg-secondary">{{ __("Not Applicable") }}</span>');
                } else {
                    badgeEl.html('<span class="badge bg-warning text-dark"><i class="ti ti-clock me-1"></i>{{ __("Pending") }}</span>');
                }

                if (res.cleared_count !== undefined) {
                    $('#clearedCountText').text(res.cleared_count);
                    var summaryBadge = $('#clearanceSummaryBadge');
                    if (res.cleared_count === res.total_count && res.total_count > 0) {
                        summaryBadge.removeClass('bg-primary-subtle text-primary border-primary-subtle').addClass('bg-success text-white');
                    } else {
                        summaryBadge.removeClass('bg-success text-white').addClass('bg-primary-subtle text-primary border-primary-subtle');
                    }
                }
                show_toastr('Success', res.message || '{{ __("Status updated successfully") }}', 'success');
            },
            error: function (xhr) {
                select.prop('disabled', false);
                var errMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : '{{ __("Failed to update status") }}';
                show_toastr('Error', errMsg, 'error');
            }
        });
    });

    // Clearance 1-Click Quick Mark Cleared
    $(document).on('click', '.quick-mark-cleared-btn', function () {
        var btn = $(this);
        var idx = btn.data('idx');
        var url = btn.data('url');

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                item_index: idx,
                status: 'Returned'
            },
            success: function (res) {
                var badgeEl = $('#clearance_status_badge_' + idx);
                badgeEl.html('<span class="badge bg-success"><i class="ti ti-check me-1"></i>{{ __("Returned / Cleared") }}</span>');
                var select = btn.closest('td').find('.quick-clearance-status-select');
                if (select.length) {
                    select.val('Returned');
                }
                if (res.cleared_count !== undefined) {
                    $('#clearedCountText').text(res.cleared_count);
                    var summaryBadge = $('#clearanceSummaryBadge');
                    if (res.cleared_count === res.total_count && res.total_count > 0) {
                        summaryBadge.removeClass('bg-primary-subtle text-primary border-primary-subtle').addClass('bg-success text-white');
                    } else {
                        summaryBadge.removeClass('bg-success text-white').addClass('bg-primary-subtle text-primary border-primary-subtle');
                    }
                }
                btn.remove();
                show_toastr('Success', res.message || '{{ __("Marked as Returned / Cleared") }}', 'success');
            },
            error: function (xhr) {
                btn.prop('disabled', false).html('<i class="ti ti-check"></i>');
                var errMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : '{{ __("Failed to update status") }}';
                show_toastr('Error', errMsg, 'error');
            }
        });
    });

    // Batch Update Clearance Modal Form
    $('#editClearanceChecklistForm').on('submit', function () {
        $('#btnSubmitClearanceUpdate').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> {{ __("Saving...") }}');
    });

    $('#editClearanceModal').on('hidden.bs.modal', function () {
        $('#btnSubmitClearanceUpdate').prop('disabled', false).html('<i class="ti ti-device-floppy me-1"></i> {{ __("Save Clearance Updates") }}');
    });
</script>
@endpush