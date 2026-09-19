@extends('layouts.admin')

@section('page-title')
    {{ __('Edit Settlement: ') . $settlement->settlement_number }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('settlement.index') }}">{{ __('Full & Final Settlements') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit: ') . $settlement->settlement_number }}</li>
@endsection

@push('css-page')
<style>
    /* Google Form Builder In-Section Stylings matching Tabler / HRMGo admin theme */
    .gform-section-builder {
        background-color: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
        padding: 15px;
        margin-top: 15px;
    }
    .gform-builder-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-left: 4px solid var(--bs-primary, #584ed2);
        border-radius: 6px;
        padding: 14px;
        margin-bottom: 12px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
        transition: all 0.2s ease;
    }
    .gform-builder-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }
    .gform-card-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--bs-primary, #584ed2);
    }
</style>
@endpush

@section('content')
    @php
        $creatorId = \Auth::user()->creatorId();
        $companySettings = \App\Models\Utility::getCompanySettings($creatorId);
        $currencySymbol = !empty($companySettings['site_currency_symbol']) ? $companySettings['site_currency_symbol'] : '₹';
        $fieldsSchema = $settlement->custom_fields_schema ?? [];
        $customValues = $settlement->custom_fields_data ?? [];
        $globalCounter = 0;
    @endphp
<div class="row">
    <div class="col-12">
        @if($settlement->status === 'signed' || $settlement->status === 'cleared')
            <div class="alert alert-warning d-flex align-items-center mb-4 shadow-sm" role="alert">
                <i class="ti ti-alert-triangle fs-2 me-3 text-warning"></i>
                <div>
                    <h6 class="alert-heading fw-bold mb-1">{{ __('Important Notice:') }} {{ __('This settlement is already :status.', ['status' => $settlement->status === 'signed' ? __('Signed by Employee') : __('Cleared & Disbursed')]) }}</h6>
                    <p class="small mb-0">{{ __('Modifying financial payables, recoveries, clearance checklists, or legal clauses now will alter the certified record already approved or signed by the employee.') }}</p>
                </div>
            </div>
        @endif

        <form action="{{ route('settlement.update', $settlement->id) }}" method="POST" id="settlementEditForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')
    
            {{-- SECTION 1: EMPLOYEE & SEPARATION DETAILS --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="ti ti-user me-2 text-primary"></i>{{ __('1. Employee & Separation Details') }}</h5>
                    <span class="badge bg-primary text-white">{{ $settlement->settlement_number }}</span>
                </div>
                <div class="card-body">
                    {{-- Employee Information Overview --}}
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-4">
                                <label class="form-label small text-muted mb-0">{{ __('Employee') }}</label>
                                <div class="fw-bold text-dark fs-6">{{ $settlement->employee_name }}</div>
                                <span class="badge bg-white text-secondary border font-monospace">{{ \Auth::user()->employeeIdFormat($settlement->employee->employee_id ?? $settlement->employee_id) }}</span>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted mb-0">{{ __('Designation / Department') }}</label>
                                <div class="fw-semibold text-dark">{{ $settlement->designation ?? __('N/A') }}</div>
                                <small class="text-muted">{{ $settlement->department ?? __('N/A') }}</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted mb-0">{{ __('Date of Joining (DOJ)') }}</label>
                                <div class="fw-semibold text-dark">
                                    {{ $settlement->date_of_joining ? \Auth::user()->dateFormat($settlement->date_of_joining) : __('N/A') }}
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('Last Working Day (LWD)') }} <span class="text-danger">*</span></label>
                            <input type="date" name="last_working_day" class="form-control" value="{{ $settlement->last_working_day ? $settlement->last_working_day->format('Y-m-d') : '' }}" required>
                        </div>
    
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('Reason for Separation') }} <span class="text-danger">*</span></label>
                            <input type="text" name="reason_for_separation" class="form-control" value="{{ $settlement->reason_for_separation }}" placeholder="{{ __('e.g., Resignation accepted, Contract ended, Mutual release') }}" required>
                        </div>
                    </div>
    
                    {{-- In-Section Google Form Builder: Section 1 Custom Questions --}}
                    <div class="gform-section-builder">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="fw-bold text-dark"><i class="ti ti-forms text-primary me-1"></i>{{ __('Custom Questions for Separation Details') }}</span>
                                <small class="text-muted d-block">{{ __('Add fields like Notice shortfall waiver, Relocation details, Separation interview notes, etc.') }}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary add-gform-field-btn" data-section="separation" data-default-target="hr">
                                <i class="ti ti-plus"></i> {{ __('Add Question to this Section') }}
                            </button>
                        </div>
                        <div class="gform-fields-container" id="gform_fields_separation">
                            @foreach ($fieldsSchema as $fld)
                                @if (($fld['section'] ?? '') === 'separation')
                                    @php
                                        $globalCounter++;
                                        $cardId = 'gfield_' . $globalCounter;
                                        $fKey = $fld['key'] ?? ('separation_' . $globalCounter);
                                        $fType = $fld['type'] ?? 'text';
                                        $fTarget = $fld['target'] ?? 'hr';
                                        $fVal = $customValues[$fKey] ?? '';
                                        $fOptions = is_array($fld['options'] ?? null) ? implode(', ', $fld['options']) : ($fld['options'] ?? '');
                                    @endphp
                                    <div class="gform-builder-card" id="{{ $cardId }}">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="gform-card-title"><i class="ti ti-help-circle me-1"></i>{{ __('Custom Field #') . $globalCounter }}</span>
                                            <button type="button" class="btn btn-xs text-danger remove-gfield-card" data-target="#{{ $cardId }}">
                                                <i class="ti ti-trash"></i> {{ __('Delete') }}
                                            </button>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-md-5">
                                                <label class="form-label small fw-bold mb-1">{{ __('Question / Field Title') }} <span class="text-danger">*</span></label>
                                                <input type="text" name="custom_field_label[]" class="form-control form-control-sm" value="{{ $fld['label'] ?? '' }}" placeholder="{{ __('e.g., Forwarding Email, Laptop Serial #') }}" required>
                                                <input type="hidden" name="custom_field_section[]" value="separation">
                                                <input type="hidden" name="custom_field_key[]" value="{{ $fKey }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-bold mb-1">{{ __('Input Type') }}</label>
                                                <select name="custom_field_type[]" class="form-control form-control-sm gform-type-select">
                                                    <option value="text" {{ $fType === 'text' ? 'selected' : '' }}>{{ __('Short Text') }}</option>
                                                    <option value="textarea" {{ $fType === 'textarea' ? 'selected' : '' }}>{{ __('Paragraph (Textarea)') }}</option>
                                                    <option value="file" {{ $fType === 'file' ? 'selected' : '' }}>{{ __('File / Attachment Upload') }}</option>
                                                    <option value="select" {{ $fType === 'select' ? 'selected' : '' }}>{{ __('Dropdown (Options)') }}</option>
                                                    <option value="date" {{ $fType === 'date' ? 'selected' : '' }}>{{ __('Date') }}</option>
                                                    <option value="number" {{ $fType === 'number' ? 'selected' : '' }}>{{ __('Number') }}</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small fw-bold mb-1">{{ __('Who Fills / Uploads This?') }}</label>
                                                <select name="custom_field_target[]" class="form-control form-control-sm gform-target-select">
                                                    <option value="employee" {{ $fTarget === 'employee' ? 'selected' : '' }}>{{ __('Employee (Online Form)') }}</option>
                                                    <option value="hr" {{ $fTarget === 'hr' ? 'selected' : '' }}>{{ __('HR / Company (Locked for Employee)') }}</option>
                                                </select>
                                            </div>
                                            <div class="col-md-8 gform-options-row" style="{{ $fType === 'select' ? '' : 'display:none;' }}">
                                                <label class="form-label small fw-bold mb-1">{{ __('Dropdown Choices (comma-separated)') }}</label>
                                                <input type="text" name="custom_field_options[]" class="form-control form-control-sm" value="{{ $fOptions }}" placeholder="Choice 1, Choice 2, Choice 3">
                                            </div>
                                            <div class="col-md-4 gform-hr-value-row" style="{{ $fTarget === 'hr' ? '' : 'display:none;' }}">
                                                <label class="form-label small fw-bold mb-1 gform-hr-val-label">{{ $fType === 'file' ? __('Attach Document / File') : __('Value (HR fills now)') }}</label>
                                                <input type="text" name="custom_field_value[]" class="form-control form-control-sm gform-hr-val-input" value="{{ $fVal }}" placeholder="Current value" style="{{ $fType === 'file' ? 'display:none;' : '' }}">
                                                <input type="file" name="custom_field_file_{{ $globalCounter - 1 }}" class="form-control form-control-sm gform-hr-file-input" style="{{ $fType === 'file' ? '' : 'display:none;' }}">
                                                @if ($fType === 'file' && !empty($fVal))
                                                    <div class="mt-1">
                                                        <a href="{{ $settlement->getAttachmentUrl($fVal) }}" target="_blank" class="badge bg-light text-primary border text-decoration-none d-inline-flex align-items-center gap-1">
                                                            <i class="ti ti-paperclip"></i> {{ __('View Existing File') }}
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="col-12 mt-1">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="custom_field_required[]" value="1" id="chk_req_{{ $globalCounter }}" {{ !empty($fld['required']) ? 'checked' : '' }}>
                                                    <label class="form-check-label small" for="chk_req_{{ $globalCounter }}">{{ __('Required') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
    
            {{-- SECTION 2: FINANCIAL CLEARANCE & BREAKDOWN --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="ti ti-receipt-2 me-2 text-primary"></i>{{ __('2. Financial Clearance Breakdown') }}</h5>
                    <span class="badge bg-warning text-dark"><i class="ti ti-lock me-1"></i>{{ __('Certified by HR / Locked for Employee') }}</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- Earnings Table --}}
                        <div class="col-md-6 border-end">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-success mb-0"><i class="ti ti-plus-circle me-1"></i>{{ __('Earnings & Payables (A)') }}</h6>
                                <button type="button" class="btn btn-xs btn-outline-success" id="add_earning_btn">
                                    <i class="ti ti-plus"></i> {{ __('Add Earning') }}
                                </button>
                            </div>
                            <table class="table table-sm" id="earnings_table">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Particulars') }}</th>
                                        <th width="140px">{{ __('Amount') }} ({{ $currencySymbol }})</th>
                                        <th width="40px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($settlement->earnings_data ?? [] as $earn)
                                        <tr>
                                            <td><input type="text" name="earnings_name[]" class="form-control form-control-sm" value="{{ $earn['name'] }}"></td>
                                            <td><input type="number" step="0.01" name="earnings_amount[]" class="form-control form-control-sm calc-earning" value="{{ $earn['amount'] }}"></td>
                                            <td><button type="button" class="btn btn-xs text-danger remove-row"><i class="ti ti-trash"></i></button></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td><input type="text" name="earnings_name[]" class="form-control form-control-sm" value="Salary Payable up to Last Working Day"></td>
                                            <td><input type="number" step="0.01" name="earnings_amount[]" class="form-control form-control-sm calc-earning" value="0.00"></td>
                                            <td></td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="table-success fw-bold">
                                        <td>{{ __('Total Gross Payable (A)') }}</td>
                                        <td colspan="2" id="display_gross">{{ $currencySymbol }} 0.00</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
    
                        {{-- Deductions Table --}}
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-danger mb-0"><i class="ti ti-minus-circle me-1"></i>{{ __('Deductions & Recoveries (B)') }}</h6>
                                <button type="button" class="btn btn-xs btn-outline-danger" id="add_deduction_btn">
                                    <i class="ti ti-plus"></i> {{ __('Add Deduction') }}
                                </button>
                            </div>
                            <table class="table table-sm" id="deductions_table">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Particulars') }}</th>
                                        <th width="140px">{{ __('Amount') }} ({{ $currencySymbol }})</th>
                                        <th width="40px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($settlement->deductions_data ?? [] as $ded)
                                        <tr>
                                            <td><input type="text" name="deductions_name[]" class="form-control form-control-sm" value="{{ $ded['name'] }}"></td>
                                            <td><input type="number" step="0.01" name="deductions_amount[]" class="form-control form-control-sm calc-deduction" value="{{ $ded['amount'] }}"></td>
                                            <td><button type="button" class="btn btn-xs text-danger remove-row"><i class="ti ti-trash"></i></button></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td><input type="text" name="deductions_name[]" class="form-control form-control-sm" value="Notice Period Recovery / Shortfall"></td>
                                            <td><input type="number" step="0.01" name="deductions_amount[]" class="form-control form-control-sm calc-deduction" value="0.00"></td>
                                            <td><button type="button" class="btn btn-xs text-danger remove-row"><i class="ti ti-trash"></i></button></td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="table-danger fw-bold">
                                        <td>{{ __('Total Deductions (B)') }}</td>
                                        <td colspan="2" id="display_deductions">{{ $currencySymbol }} 0.00</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
    
                    {{-- Net Final Settlement Box --}}
                    <div class="alert alert-primary mt-3 d-flex justify-content-between align-items-center mb-0">
                        <div>
                            <h5 class="mb-0 fw-bold">{{ __('NET FINAL SETTLEMENT AMOUNT (A - B):') }}</h5>
                            <small class="text-muted">{{ __('Calculated net balance to disburse to employee.') }}</small>
                        </div>
                        <div class="fs-3 fw-bold text-primary" id="display_net">{{ $currencySymbol }} 0.00</div>
                    </div>
    
                    {{-- In-Section Google Form Builder: Section 2 Custom Questions --}}
                    <div class="gform-section-builder">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="fw-bold text-dark"><i class="ti ti-forms text-primary me-1"></i>{{ __('Custom Questions for Financial Clearance') }}</span>
                                <small class="text-muted d-block">{{ __('Add fields like Bank verification note, Loan clearance NOC status, Gratuity eligibility notes, etc.') }}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary add-gform-field-btn" data-section="financial" data-default-target="hr">
                                <i class="ti ti-plus"></i> {{ __('Add Question to this Section') }}
                            </button>
                        </div>
                        <div class="gform-fields-container" id="gform_fields_financial">
                            @foreach ($fieldsSchema as $fld)
                                @if (($fld['section'] ?? '') === 'financial')
                                    @php
                                        $globalCounter++;
                                        $cardId = 'gfield_' . $globalCounter;
                                        $fKey = $fld['key'] ?? ('financial_' . $globalCounter);
                                        $fType = $fld['type'] ?? 'text';
                                        $fTarget = $fld['target'] ?? 'hr';
                                        $fVal = $customValues[$fKey] ?? '';
                                        $fOptions = is_array($fld['options'] ?? null) ? implode(', ', $fld['options']) : ($fld['options'] ?? '');
                                    @endphp
                                    <div class="gform-builder-card" id="{{ $cardId }}">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="gform-card-title"><i class="ti ti-help-circle me-1"></i>{{ __('Custom Field #') . $globalCounter }}</span>
                                            <button type="button" class="btn btn-xs text-danger remove-gfield-card" data-target="#{{ $cardId }}">
                                                <i class="ti ti-trash"></i> {{ __('Delete') }}
                                            </button>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-md-5">
                                                <label class="form-label small fw-bold mb-1">{{ __('Question / Field Title') }} <span class="text-danger">*</span></label>
                                                <input type="text" name="custom_field_label[]" class="form-control form-control-sm" value="{{ $fld['label'] ?? '' }}" placeholder="{{ __('e.g., Gratuity NOC Status') }}" required>
                                                <input type="hidden" name="custom_field_section[]" value="financial">
                                                <input type="hidden" name="custom_field_key[]" value="{{ $fKey }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-bold mb-1">{{ __('Input Type') }}</label>
                                                <select name="custom_field_type[]" class="form-control form-control-sm gform-type-select">
                                                    <option value="text" {{ $fType === 'text' ? 'selected' : '' }}>{{ __('Short Text') }}</option>
                                                    <option value="textarea" {{ $fType === 'textarea' ? 'selected' : '' }}>{{ __('Paragraph (Textarea)') }}</option>
                                                    <option value="file" {{ $fType === 'file' ? 'selected' : '' }}>{{ __('File / Attachment Upload') }}</option>
                                                    <option value="select" {{ $fType === 'select' ? 'selected' : '' }}>{{ __('Dropdown (Options)') }}</option>
                                                    <option value="date" {{ $fType === 'date' ? 'selected' : '' }}>{{ __('Date') }}</option>
                                                    <option value="number" {{ $fType === 'number' ? 'selected' : '' }}>{{ __('Number') }}</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small fw-bold mb-1">{{ __('Who Fills / Uploads This?') }}</label>
                                                <select name="custom_field_target[]" class="form-control form-control-sm gform-target-select">
                                                    <option value="employee" {{ $fTarget === 'employee' ? 'selected' : '' }}>{{ __('Employee (Online Form)') }}</option>
                                                    <option value="hr" {{ $fTarget === 'hr' ? 'selected' : '' }}>{{ __('HR / Company (Locked for Employee)') }}</option>
                                                </select>
                                            </div>
                                            <div class="col-md-8 gform-options-row" style="{{ $fType === 'select' ? '' : 'display:none;' }}">
                                                <label class="form-label small fw-bold mb-1">{{ __('Dropdown Choices (comma-separated)') }}</label>
                                                <input type="text" name="custom_field_options[]" class="form-control form-control-sm" value="{{ $fOptions }}" placeholder="Choice 1, Choice 2, Choice 3">
                                            </div>
                                            <div class="col-md-4 gform-hr-value-row" style="{{ $fTarget === 'hr' ? '' : 'display:none;' }}">
                                                <label class="form-label small fw-bold mb-1 gform-hr-val-label">{{ $fType === 'file' ? __('Attach Document / File') : __('Value (HR fills now)') }}</label>
                                                <input type="text" name="custom_field_value[]" class="form-control form-control-sm gform-hr-val-input" value="{{ $fVal }}" placeholder="Current value" style="{{ $fType === 'file' ? 'display:none;' : '' }}">
                                                <input type="file" name="custom_field_file_{{ $globalCounter - 1 }}" class="form-control form-control-sm gform-hr-file-input" style="{{ $fType === 'file' ? '' : 'display:none;' }}">
                                                @if ($fType === 'file' && !empty($fVal))
                                                    <div class="mt-1">
                                                        <a href="{{ $settlement->getAttachmentUrl($fVal) }}" target="_blank" class="badge bg-light text-primary border text-decoration-none d-inline-flex align-items-center gap-1">
                                                            <i class="ti ti-paperclip"></i> {{ __('View Existing File') }}
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="col-12 mt-1">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="custom_field_required[]" value="1" id="chk_req_{{ $globalCounter }}" {{ !empty($fld['required']) ? 'checked' : '' }}>
                                                    <label class="form-check-label small" for="chk_req_{{ $globalCounter }}">{{ __('Required') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
    
            {{-- SECTION 3: DEPARTMENTAL & IT ASSET CLEARANCES --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="ti ti-checklist me-2 text-primary"></i>{{ __('3. Departmental & Asset Clearances Checklist') }}</h5>
                    <button type="button" class="btn btn-xs btn-light text-primary border" id="add_clearance_btn">
                        <i class="ti ti-plus"></i> {{ __('Add Checkpoint') }}
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle" id="clearance_table">
                            <thead class="table-light">
                                <tr>
                                    <th width="18%">{{ __('Category') }}</th>
                                    <th width="40%">{{ __('Checklist Item & Handover Note') }}</th>
                                    <th width="35%">{{ __('Status & Document / Proof Upload') }}</th>
                                    <th width="7%" class="text-center">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody id="clearance_tbody">
                                @foreach ($settlement->clearance_data ?? [] as $chk)
                                    <tr>
                                        <td>
                                            <input type="text" name="clearance_category[]" class="form-control form-control-sm" value="{{ $chk['category'] ?? '' }}" placeholder="{{ __('e.g., IT & Hardware, Admin, HR') }}">
                                        </td>
                                        <td>
                                            <input type="text" name="clearance_item[]" class="form-control form-control-sm mb-1 fw-semibold" value="{{ $chk['item'] ?? '' }}" placeholder="{{ __('Checklist item title...') }}">
                                            <textarea name="clearance_remarks[]" class="form-control form-control-xs mb-1" rows="1" placeholder="{{ __('Optional note/remarks or handover instruction...') }}" style="font-size: 11.5px;">{{ $chk['remarks'] ?? '' }}</textarea>
                                            <div class="form-check form-switch p-0 d-flex align-items-center gap-2">
                                                <input type="checkbox" name="clearance_required[{{ $loop->index }}]" value="1" class="form-check-input ms-0 clearance-req-check" id="clr_req_{{ $loop->index }}" {{ !empty($chk['required']) ? 'checked' : '' }}>
                                                <label class="form-check-label text-danger small fw-semibold" for="clr_req_{{ $loop->index }}" style="font-size: 11px;">
                                                    <i class="ti ti-asterisk me-1"></i>{{ __('Mandatory for Employee to complete') }}
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1 align-items-center">
                                                <select name="clearance_status[]" class="form-control form-control-sm">
                                                    <option value="Pending" {{ ($chk['status'] ?? '') == 'Pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                                    <option value="Returned" {{ ($chk['status'] ?? '') == 'Returned' ? 'selected' : '' }}>{{ __('Returned / Cleared') }}</option>
                                                    <option value="Not Applicable" {{ ($chk['status'] ?? '') == 'Not Applicable' ? 'selected' : '' }}>{{ __('Not Applicable (N/A)') }}</option>
                                                </select>
                                                <input type="file" name="clearance_file_{{ $loop->index }}" class="form-control form-control-xs" style="font-size: 11px;" title="{{ __('Attach document / handover proof') }}">
                                            </div>
                                            @if (!empty($chk['attachment']))
                                                <div class="mt-1">
                                                    <a href="{{ $settlement->getAttachmentUrl($chk['attachment']) }}" target="_blank" class="badge bg-light text-primary border text-decoration-none d-inline-flex align-items-center gap-1" style="font-size: 11px;">
                                                        <i class="ti ti-file-check"></i> {{ \Illuminate\Support\Str::limit($chk['attachment_name'] ?? $chk['attachment'], 22) }}
                                                    </a>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-xs text-danger remove-row"><i class="ti ti-trash"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
    
                    {{-- In-Section Google Form Builder: Section 3 Custom Questions --}}
                    <div class="gform-section-builder">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="fw-bold text-dark"><i class="ti ti-forms text-primary me-1"></i>{{ __('Custom Questions for IT & Asset Clearance') }}</span>
                                <small class="text-muted d-block">{{ __('Add fields like Laptop Serial No., GitHub username wiped, BYOD Remote Wipe declaration, etc.') }}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary add-gform-field-btn" data-section="assets" data-default-target="hr">
                                <i class="ti ti-plus"></i> {{ __('Add Question to this Section') }}
                            </button>
                        </div>
                        <div class="gform-fields-container" id="gform_fields_assets">
                            @foreach ($fieldsSchema as $fld)
                                @if (($fld['section'] ?? '') === 'assets')
                                    @php
                                        $globalCounter++;
                                        $cardId = 'gfield_' . $globalCounter;
                                        $fKey = $fld['key'] ?? ('assets_' . $globalCounter);
                                        $fType = $fld['type'] ?? 'text';
                                        $fTarget = $fld['target'] ?? 'hr';
                                        $fVal = $customValues[$fKey] ?? '';
                                        $fOptions = is_array($fld['options'] ?? null) ? implode(', ', $fld['options']) : ($fld['options'] ?? '');
                                    @endphp
                                    <div class="gform-builder-card" id="{{ $cardId }}">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="gform-card-title"><i class="ti ti-help-circle me-1"></i>{{ __('Custom Field #') . $globalCounter }}</span>
                                            <button type="button" class="btn btn-xs text-danger remove-gfield-card" data-target="#{{ $cardId }}">
                                                <i class="ti ti-trash"></i> {{ __('Delete') }}
                                            </button>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-md-5">
                                                <label class="form-label small fw-bold mb-1">{{ __('Question / Field Title') }} <span class="text-danger">*</span></label>
                                                <input type="text" name="custom_field_label[]" class="form-control form-control-sm" value="{{ $fld['label'] ?? '' }}" placeholder="{{ __('e.g., Asset Serial #') }}" required>
                                                <input type="hidden" name="custom_field_section[]" value="assets">
                                                <input type="hidden" name="custom_field_key[]" value="{{ $fKey }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-bold mb-1">{{ __('Input Type') }}</label>
                                                <select name="custom_field_type[]" class="form-control form-control-sm gform-type-select">
                                                    <option value="text" {{ $fType === 'text' ? 'selected' : '' }}>{{ __('Short Text') }}</option>
                                                    <option value="textarea" {{ $fType === 'textarea' ? 'selected' : '' }}>{{ __('Paragraph (Textarea)') }}</option>
                                                    <option value="file" {{ $fType === 'file' ? 'selected' : '' }}>{{ __('File / Attachment Upload') }}</option>
                                                    <option value="select" {{ $fType === 'select' ? 'selected' : '' }}>{{ __('Dropdown (Options)') }}</option>
                                                    <option value="date" {{ $fType === 'date' ? 'selected' : '' }}>{{ __('Date') }}</option>
                                                    <option value="number" {{ $fType === 'number' ? 'selected' : '' }}>{{ __('Number') }}</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small fw-bold mb-1">{{ __('Who Fills / Uploads This?') }}</label>
                                                <select name="custom_field_target[]" class="form-control form-control-sm gform-target-select">
                                                    <option value="employee" {{ $fTarget === 'employee' ? 'selected' : '' }}>{{ __('Employee (Online Form)') }}</option>
                                                    <option value="hr" {{ $fTarget === 'hr' ? 'selected' : '' }}>{{ __('HR / Company (Locked for Employee)') }}</option>
                                                </select>
                                            </div>
                                            <div class="col-md-8 gform-options-row" style="{{ $fType === 'select' ? '' : 'display:none;' }}">
                                                <label class="form-label small fw-bold mb-1">{{ __('Dropdown Choices (comma-separated)') }}</label>
                                                <input type="text" name="custom_field_options[]" class="form-control form-control-sm" value="{{ $fOptions }}" placeholder="Choice 1, Choice 2, Choice 3">
                                            </div>
                                            <div class="col-md-4 gform-hr-value-row" style="{{ $fTarget === 'hr' ? '' : 'display:none;' }}">
                                                <label class="form-label small fw-bold mb-1 gform-hr-val-label">{{ $fType === 'file' ? __('Attach Document / File') : __('Value (HR fills now)') }}</label>
                                                <input type="text" name="custom_field_value[]" class="form-control form-control-sm gform-hr-val-input" value="{{ $fVal }}" placeholder="Current value" style="{{ $fType === 'file' ? 'display:none;' : '' }}">
                                                <input type="file" name="custom_field_file_{{ $globalCounter - 1 }}" class="form-control form-control-sm gform-hr-file-input" style="{{ $fType === 'file' ? '' : 'display:none;' }}">
                                                @if ($fType === 'file' && !empty($fVal))
                                                    <div class="mt-1">
                                                        <a href="{{ $settlement->getAttachmentUrl($fVal) }}" target="_blank" class="badge bg-light text-primary border text-decoration-none d-inline-flex align-items-center gap-1">
                                                            <i class="ti ti-paperclip"></i> {{ __('View Existing File') }}
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="col-12 mt-1">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="custom_field_required[]" value="1" id="chk_req_{{ $globalCounter }}" {{ !empty($fld['required']) ? 'checked' : '' }}>
                                                    <label class="form-check-label small" for="chk_req_{{ $globalCounter }}">{{ __('Required') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
    
            {{-- SECTION 4: EMPLOYEE HANDOVER & LEGAL UNDERTAKING --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="ti ti-writing me-2 text-primary"></i>{{ __('4. Employee Handover & Legal Undertaking') }}</h5>
                    <span class="badge bg-info text-white">{{ __('Interactive for Employee Online') }}</span>
                </div>
                <div class="card-body">
                    {{-- Custom Editable Declaration & Undertaking Terms --}}
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold text-dark mb-0">
                                <i class="ti ti-file-certificate text-primary me-1"></i> {{ __('Employee Legal Declaration & Undertaking Terms') }} <span class="text-danger">*</span>
                            </label>
                            <button type="button" class="btn btn-xs btn-outline-secondary" id="reset_declaration_btn">
                                <i class="ti ti-rotate-clockwise me-1"></i> {{ __('Reset to Default Terms') }}
                            </button>
                        </div>
                        <small class="text-muted d-block mb-2">
                            {{ __('This declaration and undertaking will appear directly above the employee digital signature on the public form. You can add more paragraphs, modify clauses, or customize it to your company requirements.') }}
                        </small>
                        <textarea name="declaration_text" id="declaration_text" class="form-control font-monospace" rows="6" required style="line-height: 1.6; font-size: 13px;">{{ old('declaration_text', $settlement->getDeclarationText()) }}</textarea>
                    </div>
    
                    {{-- Policy & Exit Rules Link Attachment --}}
                    <div class="card border mb-3 bg-light-subtle">
                        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark small text-uppercase">
                                <i class="ti ti-link text-info me-1"></i> {{ __('Policy & Rules Attachment (Shown Above Sign-off Checkbox)') }}
                            </span>
                            <span class="badge bg-info-subtle text-info small">{{ __('Interactive Policy Link') }}</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-2">
                                <div class="col-md-12 mb-2">
                                    <label class="form-label small fw-bold text-dark">{{ __('Policy Agreement Statement Line') }}</label>
                                    <input type="text" name="policy_rules_text" id="policy_rules_text" class="form-control form-control-sm"
                                        value="{{ old('policy_rules_text', $settlement->getPolicyRulesText()) }}"
                                        placeholder="{{ __('e.g. I confirm that I have reviewed the company policy and separation rules...') }}">
                                    <small class="text-muted fs-8">{{ __('This line is highlighted directly above the declaration confirmation checkbox for the employee.') }}</small>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold text-dark d-flex justify-content-between">
                                        <span>{{ __('Attach Policy Document Link / URL') }}</span>
                                        @if(isset($companyPolicies) && count($companyPolicies) > 0)
                                            <span class="text-primary small fw-normal">{{ __('Or pick from policies below') }}</span>
                                        @endif
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light"><i class="ti ti-link"></i></span>
                                        <input type="url" name="policy_rules_link" id="policy_rules_link" class="form-control"
                                            value="{{ old('policy_rules_link', $settlement->policy_rules_link) }}"
                                            placeholder="{{ __('https://... or link to document') }}">
                                    </div>
                                    @if(isset($companyPolicies) && count($companyPolicies) > 0)
                                        <div class="mt-1">
                                            <select class="form-select form-select-xs select-policy-preset" style="font-size: 11px; padding: 2px 6px;">
                                                <option value="">{{ __('-- Select an existing Company Policy --') }}</option>
                                                @foreach($companyPolicies as $policy)
                                                    @php
                                                        $policyFile = !empty($policy->attachment) ? \App\Models\Utility::get_file('uploads/companyPolicy') . '/' . $policy->attachment : '';
                                                    @endphp
                                                    <option value="{{ $policyFile }}" data-title="{{ $policy->title }}">{{ $policy->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold text-dark">{{ __('Policy Link Title / Button Label') }}</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light"><i class="ti ti-file-text"></i></span>
                                        <input type="text" name="policy_rules_title" id="policy_rules_title" class="form-control"
                                            value="{{ old('policy_rules_title', $settlement->policy_rules_title ?? 'Company Separation & Exit Policy') }}"
                                            placeholder="{{ __('e.g. Employee Handbook / Exit Rules Policy') }}">
                                    </div>
                                    <small class="text-muted fs-8">{{ __('Label displayed on the clickable link pill on public clearance page & PDF.') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded small text-muted border mb-3">
                        <i class="ti ti-info-circle text-primary me-1"></i>
                        {{ __('The digital signature canvas and confirmation checkbox will be presented to the employee automatically. You can also add specific custom questions for the employee to answer below.') }}
                    </div>
    
                    {{-- In-Section Google Form Builder: Section 4 Custom Questions --}}
                    <div class="gform-section-builder">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="fw-bold text-dark"><i class="ti ti-forms text-primary me-1"></i>{{ __('Custom Questions for Departing Employee') }}</span>
                                <small class="text-muted d-block">{{ __('Add questions that the employee must answer online before signing (e.g. Forwarding email, Feedback, Handover links).') }}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary add-gform-field-btn" data-section="employee" data-default-target="employee">
                                <i class="ti ti-plus"></i> {{ __('Add Question to this Section') }}
                            </button>
                        </div>
                        <div class="gform-fields-container" id="gform_fields_employee">
                            @foreach ($fieldsSchema as $fld)
                                @if (($fld['section'] ?? '') === 'employee')
                                    @php
                                        $globalCounter++;
                                        $cardId = 'gfield_' . $globalCounter;
                                        $fKey = $fld['key'] ?? ('employee_' . $globalCounter);
                                        $fType = $fld['type'] ?? 'text';
                                        $fTarget = $fld['target'] ?? 'employee';
                                        $fVal = $customValues[$fKey] ?? '';
                                        $fOptions = is_array($fld['options'] ?? null) ? implode(', ', $fld['options']) : ($fld['options'] ?? '');
                                    @endphp
                                    <div class="gform-builder-card" id="{{ $cardId }}">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="gform-card-title"><i class="ti ti-help-circle me-1"></i>{{ __('Custom Field #') . $globalCounter }}</span>
                                            <button type="button" class="btn btn-xs text-danger remove-gfield-card" data-target="#{{ $cardId }}">
                                                <i class="ti ti-trash"></i> {{ __('Delete') }}
                                            </button>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-md-5">
                                                <label class="form-label small fw-bold mb-1">{{ __('Question / Field Title') }} <span class="text-danger">*</span></label>
                                                <input type="text" name="custom_field_label[]" class="form-control form-control-sm" value="{{ $fld['label'] ?? '' }}" placeholder="{{ __('e.g., Forwarding Email') }}" required>
                                                <input type="hidden" name="custom_field_section[]" value="employee">
                                                <input type="hidden" name="custom_field_key[]" value="{{ $fKey }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-bold mb-1">{{ __('Input Type') }}</label>
                                                <select name="custom_field_type[]" class="form-control form-control-sm gform-type-select">
                                                    <option value="text" {{ $fType === 'text' ? 'selected' : '' }}>{{ __('Short Text') }}</option>
                                                    <option value="textarea" {{ $fType === 'textarea' ? 'selected' : '' }}>{{ __('Paragraph (Textarea)') }}</option>
                                                    <option value="file" {{ $fType === 'file' ? 'selected' : '' }}>{{ __('File / Attachment Upload') }}</option>
                                                    <option value="select" {{ $fType === 'select' ? 'selected' : '' }}>{{ __('Dropdown (Options)') }}</option>
                                                    <option value="date" {{ $fType === 'date' ? 'selected' : '' }}>{{ __('Date') }}</option>
                                                    <option value="number" {{ $fType === 'number' ? 'selected' : '' }}>{{ __('Number') }}</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small fw-bold mb-1">{{ __('Who Fills / Uploads This?') }}</label>
                                                <select name="custom_field_target[]" class="form-control form-control-sm gform-target-select">
                                                    <option value="employee" {{ $fTarget === 'employee' ? 'selected' : '' }}>{{ __('Employee (Online Form)') }}</option>
                                                    <option value="hr" {{ $fTarget === 'hr' ? 'selected' : '' }}>{{ __('HR / Company (Locked for Employee)') }}</option>
                                                </select>
                                            </div>
                                            <div class="col-md-8 gform-options-row" style="{{ $fType === 'select' ? '' : 'display:none;' }}">
                                                <label class="form-label small fw-bold mb-1">{{ __('Dropdown Choices (comma-separated)') }}</label>
                                                <input type="text" name="custom_field_options[]" class="form-control form-control-sm" value="{{ $fOptions }}" placeholder="Choice 1, Choice 2, Choice 3">
                                            </div>
                                            <div class="col-md-4 gform-hr-value-row" style="{{ $fTarget === 'hr' ? '' : 'display:none;' }}">
                                                <label class="form-label small fw-bold mb-1 gform-hr-val-label">{{ $fType === 'file' ? __('Attach Document / File') : __('Value (HR fills now)') }}</label>
                                                <input type="text" name="custom_field_value[]" class="form-control form-control-sm gform-hr-val-input" value="{{ $fVal }}" placeholder="Current value" style="{{ $fType === 'file' ? 'display:none;' : '' }}">
                                                <input type="file" name="custom_field_file_{{ $globalCounter - 1 }}" class="form-control form-control-sm gform-hr-file-input" style="{{ $fType === 'file' ? '' : 'display:none;' }}">
                                                @if ($fType === 'file' && !empty($fVal))
                                                    <div class="mt-1">
                                                        <a href="{{ $settlement->getAttachmentUrl($fVal) }}" target="_blank" class="badge bg-light text-primary border text-decoration-none d-inline-flex align-items-center gap-1">
                                                            <i class="ti ti-paperclip"></i> {{ __('View Existing File') }}
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="col-12 mt-1">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="custom_field_required[]" value="1" id="chk_req_{{ $globalCounter }}" {{ !empty($fld['required']) ? 'checked' : '' }}>
                                                    <label class="form-check-label small" for="chk_req_{{ $globalCounter }}">{{ __('Required') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
    
            {{-- SECTION 5: MANAGEMENT SIGN-OFF & PAYMENT INFORMATION --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="ti ti-cash me-2 text-primary"></i>{{ __('5. Management Sign-off & Payment Information') }}</h5>
                    <span class="badge bg-dark text-white">{{ __('Disbursal Records') }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">{{ __('Settlement Clearance Status') }}</label>
                            <select name="final_settlement_status" class="form-control">
                                <option value="Pending" {{ $settlement->final_settlement_status == 'Pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                <option value="Cleared" {{ $settlement->final_settlement_status == 'Cleared' ? 'selected' : '' }}>{{ __('Cleared') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">{{ __('Payment Date') }}</label>
                            <input type="date" name="payment_date" class="form-control" value="{{ $settlement->payment_date ? $settlement->payment_date->format('Y-m-d') : '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">{{ __('Payment Mode') }}</label>
                            <select name="payment_mode" class="form-control">
                                <option value="">{{ __('-- Choose Mode --') }}</option>
                                <option value="NEFT" {{ $settlement->payment_mode == 'NEFT' ? 'selected' : '' }}>NEFT</option>
                                <option value="RTGS" {{ $settlement->payment_mode == 'RTGS' ? 'selected' : '' }}>RTGS</option>
                                <option value="Bank Transfer" {{ $settlement->payment_mode == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="Cheque" {{ $settlement->payment_mode == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                                <option value="UPI" {{ $settlement->payment_mode == 'UPI' ? 'selected' : '' }}>UPI</option>
                                <option value="Cash" {{ $settlement->payment_mode == 'Cash' ? 'selected' : '' }}>Cash</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">{{ __('Transaction Ref (UTR No.)') }}</label>
                            <input type="text" name="payment_reference_no" class="form-control" value="{{ $settlement->payment_reference_no }}" placeholder="{{ __('e.g., UTR123456789') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('HR Representative Name') }}</label>
                            <input type="text" name="hr_representative_name" class="form-control" value="{{ $settlement->hr_representative_name }}" placeholder="{{ __('HR Manager Name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('Authorized Signatory Name') }}</label>
                            <input type="text" name="authorized_signatory_name" class="form-control" value="{{ $settlement->authorized_signatory_name }}" placeholder="{{ __('Director / Finance Head') }}">
                        </div>
                    </div>
                </div>
            </div>
    
            {{-- Submit Card --}}
            <div class="card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <a href="{{ route('settlement.show', $settlement->id) }}" class="btn btn-light">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="ti ti-device-floppy me-1"></i> {{ __('Update Full & Final Settlement') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script-page')
<script>
    const defaultDeclarationText = @json(App\Models\FullAndFinalSettlement::defaultDeclarationText());
    $('#reset_declaration_btn').on('click', function () {
        if (confirm('{{ __("Reset declaration text to default legal terms?") }}')) {
            $('#declaration_text').val(defaultDeclarationText);
        }
    });

    // Dynamic Financial Calculations
    function recalculateTotals() {
        let gross = 0;
        $('.calc-earning').each(function () { gross += parseFloat($(this).val()) || 0; });
        let deductions = 0;
        $('.calc-deduction').each(function () { deductions += parseFloat($(this).val()) || 0; });
        let net = Math.max(0, gross - deductions);

        $('#display_gross').text('₹ ' + gross.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
        $('#display_deductions').text('₹ ' + deductions.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
        $('#display_net').text('₹ ' + net.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
    }

    $(document).on('input', '.calc-earning, .calc-deduction', recalculateTotals);

    $('#add_earning_btn').on('click', function () {
        $('#earnings_table tbody').append(`<tr>
            <td><input type="text" name="earnings_name[]" class="form-control form-control-sm" placeholder="e.g. Gratuity / Arrears"></td>
            <td><input type="number" step="0.01" name="earnings_amount[]" class="form-control form-control-sm calc-earning" value="0.00"></td>
            <td><button type="button" class="btn btn-xs text-danger remove-row"><i class="ti ti-trash"></i></button></td>
        </tr>`);
    });

    $('#add_deduction_btn').on('click', function () {
        $('#deductions_table tbody').append(`<tr>
            <td><input type="text" name="deductions_name[]" class="form-control form-control-sm" placeholder="e.g. TDS / Shortfall"></td>
            <td><input type="number" step="0.01" name="deductions_amount[]" class="form-control form-control-sm calc-deduction" value="0.00"></td>
            <td><button type="button" class="btn btn-xs text-danger remove-row"><i class="ti ti-trash"></i></button></td>
        </tr>`);
    });

    $('#add_clearance_btn').on('click', function () {
        const rowIdx = $('#clearance_tbody tr').length;
        $('#clearance_tbody').append(`<tr>
            <td><input type="text" name="clearance_category[]" class="form-control form-control-sm" value="General" placeholder="{{ __('e.g., IT & Hardware, Admin') }}"></td>
            <td>
                <input type="text" name="clearance_item[]" class="form-control form-control-sm mb-1 fw-semibold" placeholder="{{ __('e.g., Laptop Charger, Access Key') }}">
                <textarea name="clearance_remarks[]" class="form-control form-control-xs mb-1" rows="1" placeholder="{{ __('Optional note/remarks or handover instruction...') }}" style="font-size: 11.5px;"></textarea>
                <div class="form-check form-switch p-0 d-flex align-items-center gap-2">
                    <input type="checkbox" name="clearance_required[${rowIdx}]" value="1" class="form-check-input ms-0 clearance-req-check" id="clr_req_${rowIdx}">
                    <label class="form-check-label text-danger small fw-semibold" for="clr_req_${rowIdx}" style="font-size: 11px;">
                        <i class="ti ti-asterisk me-1"></i>{{ __('Mandatory for Employee to complete') }}
                    </label>
                </div>
            </td>
            <td>
                <div class="d-flex gap-1 align-items-center">
                    <select name="clearance_status[]" class="form-control form-control-sm">
                        <option value="Pending" selected>Pending</option>
                        <option value="Returned">Returned / Cleared</option>
                        <option value="Not Applicable">Not Applicable</option>
                    </select>
                    <input type="file" name="clearance_file_${rowIdx}" class="form-control form-control-xs" style="font-size: 11px;" title="{{ __('Attach document / handover proof') }}">
                </div>
            </td>
            <td class="text-center"><button type="button" class="btn btn-xs text-danger remove-row"><i class="ti ti-trash"></i></button></td>
        </tr>`);
        reindexClearanceFiles();
    });

    $(document).on('click', '.remove-row', function () {
        $(this).closest('tr').remove();
        reindexClearanceFiles();
        recalculateTotals();
    });

    function reindexClearanceFiles() {
        $('#clearance_tbody tr').each(function(idx) {
            $(this).find('input[type="file"][name^="clearance_file_"]').attr('name', `clearance_file_${idx}`);
            $(this).find('input[type="checkbox"][name^="clearance_required"]').attr('name', `clearance_required[${idx}]`).attr('id', `clr_req_${idx}`);
            $(this).find('label[for^="clr_req_"]').attr('for', `clr_req_${idx}`);
        });
    }

    // ==================== IN-SECTION GOOGLE FORM BUILDER ====================
    let globalFieldCounter = {{ $globalCounter }};

    $('.add-gform-field-btn').on('click', function () {
        const section = $(this).attr('data-section');
        const defaultTarget = $(this).attr('data-default-target') || 'hr';
        const container = $('#gform_fields_' + section);

        globalFieldCounter++;
        const cardId = 'gfield_' + globalFieldCounter;
        const isEmployeeSection = (defaultTarget === 'employee');

        const card = `
            <div class="gform-builder-card" id="${cardId}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="gform-card-title"><i class="ti ti-help-circle me-1"></i>Custom Field #${globalFieldCounter}</span>
                    <button type="button" class="btn btn-xs text-danger remove-gfield-card" data-target="#${cardId}">
                        <i class="ti ti-trash"></i> Delete
                    </button>
                </div>
                <div class="row g-2">
                    <div class="col-md-5">
                        <label class="form-label small fw-bold mb-1">Question / Field Title <span class="text-danger">*</span></label>
                        <input type="text" name="custom_field_label[]" class="form-control form-control-sm" placeholder="e.g., Forwarding Email, Laptop Serial #" required>
                        <input type="hidden" name="custom_field_section[]" value="${section}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold mb-1">Input Type</label>
                        <select name="custom_field_type[]" class="form-control form-control-sm gform-type-select">
                            <option value="text">Short Text</option>
                            <option value="textarea">Paragraph (Textarea)</option>
                            <option value="file">File / Attachment Upload</option>
                            <option value="select">Dropdown (Options)</option>
                            <option value="date">Date</option>
                            <option value="number">Number</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold mb-1">Who Fills / Uploads This?</label>
                        <select name="custom_field_target[]" class="form-control form-control-sm gform-target-select">
                            <option value="employee" ${isEmployeeSection ? 'selected' : ''}>Employee (Online Form)</option>
                            <option value="hr" ${!isEmployeeSection ? 'selected' : ''}>HR / Company (Locked for Employee)</option>
                        </select>
                    </div>
                    <div class="col-md-8 gform-options-row" style="display:none;">
                        <label class="form-label small fw-bold mb-1">Dropdown Choices (comma-separated)</label>
                        <input type="text" name="custom_field_options[]" class="form-control form-control-sm" placeholder="Choice 1, Choice 2, Choice 3">
                    </div>
                    <div class="col-md-4 gform-hr-value-row" style="${isEmployeeSection ? 'display:none;' : ''}">
                        <label class="form-label small fw-bold mb-1 gform-hr-val-label">Value (HR fills now)</label>
                        <input type="text" name="custom_field_value[]" class="form-control form-control-sm gform-hr-val-input" placeholder="Current value">
                        <input type="file" name="custom_field_file_${globalFieldCounter - 1}" class="form-control form-control-sm gform-hr-file-input" style="display:none;">
                    </div>
                    <div class="col-12 mt-1">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="custom_field_required[${globalFieldCounter - 1}]" value="1" id="chk_req_${globalFieldCounter}">
                            <label class="form-check-label small" for="chk_req_${globalFieldCounter}">Required</label>
                        </div>
                    </div>
                </div>
            </div>
        `;

        container.append(card);
    });

    $(document).on('change', '.gform-type-select', function () {
        const type = $(this).val();
        const card = $(this).closest('.gform-builder-card');
        if (type === 'select') {
            card.find('.gform-options-row').show();
            card.find('.gform-hr-val-input').show();
            card.find('.gform-hr-file-input').hide();
        } else if (type === 'file') {
            card.find('.gform-options-row').hide();
            card.find('.gform-hr-val-input').hide();
            card.find('.gform-hr-file-input').show();
            card.find('.gform-hr-val-label').text('Attach Document / File');
        } else {
            card.find('.gform-options-row').hide();
            card.find('.gform-hr-val-input').show();
            card.find('.gform-hr-file-input').hide();
            card.find('.gform-hr-val-label').text('Value (HR fills now)');
        }
    });

    $(document).on('change', '.gform-target-select', function () {
        const target = $(this).val();
        const card = $(this).closest('.gform-builder-card');
        if (target === 'hr') {
            card.find('.gform-hr-value-row').show();
        } else {
            card.find('.gform-hr-value-row').hide();
        }
    });

    $(document).on('click', '.remove-gfield-card', function () {
        const target = $(this).attr('data-target');
        $(target).remove();
    });

    // Reindex custom fields on submit to ensure array indexes match controller expectations
    function reindexCustomFields() {
        $('.gform-builder-card').each(function(idx) {
            $(this).find('[name^="custom_field_label"]').attr('name', `custom_field_label[${idx}]`);
            $(this).find('[name^="custom_field_section"]').attr('name', `custom_field_section[${idx}]`);
            $(this).find('[name^="custom_field_key"]').attr('name', `custom_field_key[${idx}]`);
            $(this).find('[name^="custom_field_type"]').attr('name', `custom_field_type[${idx}]`);
            $(this).find('[name^="custom_field_target"]').attr('name', `custom_field_target[${idx}]`);
            $(this).find('[name^="custom_field_options"]').attr('name', `custom_field_options[${idx}]`);
            $(this).find('[name^="custom_field_value"]').attr('name', `custom_field_value[${idx}]`);
            $(this).find('.gform-hr-file-input').attr('name', `custom_field_file_${idx}`);
            $(this).find('[name^="custom_field_required"]').attr('name', `custom_field_required[${idx}]`);
        });
    }

    $('#settlementEditForm').on('submit', function () {
        reindexClearanceFiles();
        reindexCustomFields();
    });

    $(document).on('change', '.select-policy-preset', function () {
        const url = $(this).val();
        const title = $(this).find('option:selected').data('title');
        if (url) {
            $('#policy_rules_link').val(url);
            if (title) {
                $('#policy_rules_title').val(title);
            }
        }
    });

    recalculateTotals();
</script>
@endpush