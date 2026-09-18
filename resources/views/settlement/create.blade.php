@extends('layouts.admin')

@section('page-title')
    {{ __('Create Full & Final Settlement') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('settlement.index') }}">{{ __('Full & Final Settlements') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create') }}</li>
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
<div class="row">
    <div class="col-12">
        <form action="{{ route('settlement.store') }}" method="POST" id="settlementForm">
            @csrf
    
            {{-- SECTION 1: EMPLOYEE & SEPARATION DETAILS --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="ti ti-user me-2 text-primary"></i>{{ __('1. Employee & Separation Details') }}</h5>
                    <span class="badge bg-primary text-white">{{ __('SaaS Multi-Industry Ready') }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('Select Employee') }} <span class="text-danger">*</span></label>
                            <select name="employee_id" id="employee_select" class="form-control select2" required>
                                <option value="">{{ __('-- Choose Employee --') }}</option>
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->id }}"
                                        data-code="{{ $emp->employee_id }}"
                                        data-dept="{{ $emp->department?->name }}"
                                        data-desig="{{ $emp->designation?->name }}"
                                        data-doj="{{ $emp->company_doj }}"
                                        {{ (isset($selectedEmployee) && $selectedEmployee->id == $emp->id) ? 'selected' : '' }}>
                                        {{ $emp->name }} ({{ $emp->employee_id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
    
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('Last Working Day (LWD)') }} <span class="text-danger">*</span></label>
                            <input type="date" name="last_working_day" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
    
                        <div class="col-md-12">
                            <label class="form-label fw-bold">{{ __('Reason for Separation') }} <span class="text-danger">*</span></label>
                            <input type="text" name="reason_for_separation" class="form-control" placeholder="{{ __('e.g., Resignation accepted, Contract ended, Mutual release') }}" required>
                        </div>
                    </div>
    
                    {{-- In-Section Google Form Builder: Section 1 Custom Questions --}}
                    <div class="gform-section-builder">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="fw-bold text-dark"><i class="ti ti-forms text-primary me-1"></i>{{ __('Custom Questions for Separation Details') }}</span>
                                <small class="text-muted d-block">{{ __('Add fields like Notice shortfall waiver, Relocation details, Separation interview notes, etc.') }}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary add-gform-field-btn" data-section="separation">
                                <i class="ti ti-plus"></i> {{ __('Add Question to this Section') }}
                            </button>
                        </div>
                        <div class="gform-fields-container" id="gform_fields_separation">
                            {{-- Injected dynamically --}}
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
                                        <th width="140px">{{ __('Amount (₹)') }}</th>
                                        <th width="40px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="text" name="earnings_name[]" class="form-control form-control-sm" value="Salary Payable up to Last Working Day"></td>
                                        <td><input type="number" step="0.01" name="earnings_amount[]" class="form-control form-control-sm calc-earning" value="0.00"></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td><input type="text" name="earnings_name[]" class="form-control form-control-sm" value="Leave Encashment (Privilege/Earned)"></td>
                                        <td><input type="number" step="0.01" name="earnings_amount[]" class="form-control form-control-sm calc-earning" value="0.00"></td>
                                        <td><button type="button" class="btn btn-xs text-danger remove-row"><i class="ti ti-trash"></i></button></td>
                                    </tr>
                                    <tr>
                                        <td><input type="text" name="earnings_name[]" class="form-control form-control-sm" value="Incentive / Commission / Bonus"></td>
                                        <td><input type="number" step="0.01" name="earnings_amount[]" class="form-control form-control-sm calc-earning" value="0.00"></td>
                                        <td><button type="button" class="btn btn-xs text-danger remove-row"><i class="ti ti-trash"></i></button></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="table-success fw-bold">
                                        <td>{{ __('Total Gross Payable (A)') }}</td>
                                        <td colspan="2" id="display_gross">₹ 0.00</td>
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
                                        <th width="140px">{{ __('Amount (₹)') }}</th>
                                        <th width="40px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="text" name="deductions_name[]" class="form-control form-control-sm" value="Notice Period Recovery / Shortfall"></td>
                                        <td><input type="number" step="0.01" name="deductions_amount[]" class="form-control form-control-sm calc-deduction" value="0.00"></td>
                                        <td><button type="button" class="btn btn-xs text-danger remove-row"><i class="ti ti-trash"></i></button></td>
                                    </tr>
                                    <tr>
                                        <td><input type="text" name="deductions_name[]" class="form-control form-control-sm" value="Loan / Advance Balance Recovery"></td>
                                        <td><input type="number" step="0.01" name="deductions_amount[]" class="form-control form-control-sm calc-deduction" value="0.00"></td>
                                        <td><button type="button" class="btn btn-xs text-danger remove-row"><i class="ti ti-trash"></i></button></td>
                                    </tr>
                                    <tr>
                                        <td><input type="text" name="deductions_name[]" class="form-control form-control-sm" value="Asset / Hardware Damage Recovery"></td>
                                        <td><input type="number" step="0.01" name="deductions_amount[]" class="form-control form-control-sm calc-deduction" value="0.00"></td>
                                        <td><button type="button" class="btn btn-xs text-danger remove-row"><i class="ti ti-trash"></i></button></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="table-danger fw-bold">
                                        <td>{{ __('Total Deductions (B)') }}</td>
                                        <td colspan="2" id="display_deductions">₹ 0.00</td>
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
                        <div class="fs-3 fw-bold text-primary" id="display_net">₹ 0.00</div>
                    </div>
    
                    {{-- In-Section Google Form Builder: Section 2 Custom Questions --}}
                    <div class="gform-section-builder">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="fw-bold text-dark"><i class="ti ti-forms text-primary me-1"></i>{{ __('Custom Questions for Financial Clearance') }}</span>
                                <small class="text-muted d-block">{{ __('Add fields like Bank verification note, Loan clearance NOC status, Gratuity eligibility notes, etc.') }}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary add-gform-field-btn" data-section="financial">
                                <i class="ti ti-plus"></i> {{ __('Add Question to this Section') }}
                            </button>
                        </div>
                        <div class="gform-fields-container" id="gform_fields_financial">
                            {{-- Injected dynamically --}}
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
                                    <th width="20%">{{ __('Category') }}</th>
                                    <th width="45%">{{ __('Checklist Item / Asset') }}</th>
                                    <th width="25%">{{ __('Status') }}</th>
                                    <th width="10%">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody id="clearance_tbody">
                                @foreach ($defaultClearance as $item)
                                    <tr>
                                        <td>
                                            <input type="text" name="clearance_category[]" class="form-control form-control-sm" value="{{ $item['category'] }}" placeholder="{{ __('e.g., IT & Hardware, Admin, HR') }}">
                                        </td>
                                        <td>
                                            <input type="text" name="clearance_item[]" class="form-control form-control-sm" value="{{ $item['item'] }}" placeholder="{{ __('e.g., Company laptop / access card returned') }}">
                                        </td>
                                        <td>
                                            <select name="clearance_status[]" class="form-control form-control-sm">
                                                <option value="Pending" selected>{{ __('Pending') }}</option>
                                                <option value="Returned">{{ __('Returned / Cleared') }}</option>
                                                <option value="Not Applicable">{{ __('Not Applicable (N/A)') }}</option>
                                            </select>
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
                            <button type="button" class="btn btn-sm btn-outline-primary add-gform-field-btn" data-section="assets">
                                <i class="ti ti-plus"></i> {{ __('Add Question to this Section') }}
                            </button>
                        </div>
                        <div class="gform-fields-container" id="gform_fields_assets">
                            {{-- Injected dynamically --}}
                        </div>
                    </div>
                </div>
            </div>
    
            {{-- SECTION 4: EMPLOYEE HANDOVER & UNDERTAKING --}}
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
                        <textarea name="declaration_text" id="declaration_text" class="form-control font-monospace" rows="6" required style="line-height: 1.6; font-size: 13px;">{{ old('declaration_text', $defaultDeclaration ?? App\Models\FullAndFinalSettlement::defaultDeclarationText()) }}</textarea>
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
                                        value="{{ old('policy_rules_text', $defaultPolicyRules ?? App\Models\FullAndFinalSettlement::defaultPolicyRulesText()) }}"
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
                                            value="{{ old('policy_rules_link') }}"
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
                                            value="{{ old('policy_rules_title', 'Company Separation & Exit Policy') }}"
                                            placeholder="{{ __('e.g. View Company Policy & Rules') }}">
                                    </div>
                                    <small class="text-muted fs-8">{{ __('Label displayed on the button the employee clicks to review the document.') }}</small>
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
                            {{-- Injected dynamically --}}
                        </div>
                    </div>
                </div>
            </div>
    
            {{-- Submit Card --}}
            <div class="card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <a href="{{ route('settlement.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="ti ti-device-floppy me-1"></i> {{ __('Save Full & Final Settlement') }}
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

    $(document).on('change', '.select-policy-preset', function () {
        let val = $(this).val();
        let title = $(this).find('option:selected').data('title');
        if (val) {
            $('#policy_rules_link').val(val);
            if (title) {
                $('#policy_rules_title').val(title);
            }
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
        $('#clearance_tbody').append(`<tr>
            <td><input type="text" name="clearance_category[]" class="form-control form-control-sm" value="General" placeholder="{{ __('e.g., IT & Hardware, Admin') }}"></td>
            <td><input type="text" name="clearance_item[]" class="form-control form-control-sm" placeholder="{{ __('e.g., Laptop Charger, Access Key, Official Email Revocation') }}"></td>
            <td>
                <select name="clearance_status[]" class="form-control form-control-sm">
                    <option value="Pending" selected>Pending</option>
                    <option value="Returned">Returned / Cleared</option>
                    <option value="Not Applicable">Not Applicable</option>
                </select>
            </td>
            <td class="text-center"><button type="button" class="btn btn-xs text-danger remove-row"><i class="ti ti-trash"></i></button></td>
        </tr>`);
    });

    $(document).on('click', '.remove-row', function () {
        $(this).closest('tr').remove();
        recalculateTotals();
    });

    // ==================== IN-SECTION GOOGLE FORM BUILDER ====================
    let globalFieldCounter = 0;

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
                            <option value="textarea">Paragraph</option>
                            <option value="select">Dropdown (Options)</option>
                            <option value="date">Date</option>
                            <option value="number">Number</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold mb-1">Who Fills This?</label>
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
                        <label class="form-label small fw-bold mb-1">Value (HR fills now)</label>
                        <input type="text" name="custom_field_value[]" class="form-control form-control-sm" placeholder="Initial value">
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
        } else {
            card.find('.gform-options-row').hide();
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
            $(this).find('[name^="custom_field_required"]').attr('name', `custom_field_required[${idx}]`);
        });
    }

    $('#settlementForm').on('submit', function () {
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