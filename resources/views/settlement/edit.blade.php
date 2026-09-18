@extends('layouts.admin')

@section('page-title')
    {{ __('Edit Settlement: ') . $settlement->settlement_number }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('settlement.index') }}">{{ __('Full & Final Settlements') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit') }}</li>
@endsection

@section('content')
    <form action="{{ route('settlement.update', $settlement->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Header Info --}}
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0 text-white"><i class="ti ti-file-text me-2"></i>{{ __('1. Employee & Separation Details') }}</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">{{ __('Employee') }}</label>
                        <input type="text" class="form-control" value="{{ $settlement->employee_name }} ({{ $settlement->employee_code }})" readonly disabled>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">{{ __('Designation / Department') }}</label>
                        <input type="text" class="form-control" value="{{ $settlement->designation }} / {{ $settlement->department }}" readonly disabled>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">{{ __('Last Working Day') }}</label>
                        <input type="date" name="last_working_day" class="form-control" value="{{ $settlement->last_working_day ? $settlement->last_working_day->format('Y-m-d') : '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">{{ __('Reason for Separation') }}</label>
                        <input type="text" name="reason_for_separation" class="form-control" value="{{ $settlement->reason_for_separation }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Financial Clearance --}}
        <div class="card mb-4">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-white"><i class="ti ti-calculator me-2"></i>{{ __('2. Financial Clearance') }}</h5>
                <span class="badge bg-warning text-dark">{{ __('Locked for Employee') }}</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 border-end">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-success mb-0">{{ __('Earnings & Additions (A)') }}</h6>
                            <button type="button" class="btn btn-xs btn-outline-success" id="add_earning_btn"><i class="ti ti-plus"></i> {{ __('Add') }}</button>
                        </div>
                        <table class="table table-sm" id="earnings_table">
                            <thead><tr><th>{{ __('Particulars') }}</th><th width="140px">{{ __('Amount') }}</th><th width="40px"></th></tr></thead>
                            <tbody>
                                @foreach ($settlement->earnings_data ?? [] as $earn)
                                    <tr>
                                        <td><input type="text" name="earnings_name[]" class="form-control form-control-sm" value="{{ $earn['name'] }}"></td>
                                        <td><input type="number" step="0.01" name="earnings_amount[]" class="form-control form-control-sm calc-earning" value="{{ $earn['amount'] }}"></td>
                                        <td><button type="button" class="btn btn-xs text-danger remove-row"><i class="ti ti-trash"></i></button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-success fw-bold"><td>{{ __('Gross (A)') }}</td><td colspan="2" id="display_gross">₹ 0.00</td></tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-danger mb-0">{{ __('Deductions & Recoveries (B)') }}</h6>
                            <button type="button" class="btn btn-xs btn-outline-danger" id="add_deduction_btn"><i class="ti ti-plus"></i> {{ __('Add') }}</button>
                        </div>
                        <table class="table table-sm" id="deductions_table">
                            <thead><tr><th>{{ __('Particulars') }}</th><th width="140px">{{ __('Amount') }}</th><th width="40px"></th></tr></thead>
                            <tbody>
                                @foreach ($settlement->deductions_data ?? [] as $ded)
                                    <tr>
                                        <td><input type="text" name="deductions_name[]" class="form-control form-control-sm" value="{{ $ded['name'] }}"></td>
                                        <td><input type="number" step="0.01" name="deductions_amount[]" class="form-control form-control-sm calc-deduction" value="{{ $ded['amount'] }}"></td>
                                        <td><button type="button" class="btn btn-xs text-danger remove-row"><i class="ti ti-trash"></i></button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-danger fw-bold"><td>{{ __('Deductions (B)') }}</td><td colspan="2" id="display_deductions">₹ 0.00</td></tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="alert alert-primary mt-3 d-flex justify-content-between align-items-center mb-0">
                    <h5 class="mb-0 fw-bold">{{ __('NET FINAL SETTLEMENT AMOUNT (A - B):') }}</h5>
                    <div class="fs-3 fw-bold text-primary" id="display_net">₹ 0.00</div>
                </div>
            </div>
        </div>

        {{-- Clearance Checklist --}}
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-white"><i class="ti ti-checklist me-2"></i>{{ __('3. Departmental Clearances Checklist') }}</h5>
                <button type="button" class="btn btn-xs btn-light text-dark" id="add_clearance_btn"><i class="ti ti-plus"></i> {{ __('Add Checkpoint') }}</button>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-sm" id="clearance_table">
                    <thead class="table-light">
                        <tr><th width="20%">{{ __('Category') }}</th><th width="50%">{{ __('Item') }}</th><th width="25%">{{ __('Status') }}</th><th width="5%"></th></tr>
                    </thead>
                    <tbody id="clearance_tbody">
                        @foreach ($settlement->clearance_data ?? [] as $chk)
                            <tr>
                                <td><input type="text" name="clearance_category[]" class="form-control form-control-sm" value="{{ $chk['category'] }}" placeholder="{{ __('e.g., IT & Hardware, Admin, HR') }}"></td>
                                <td><input type="text" name="clearance_item[]" class="form-control form-control-sm" value="{{ $chk['item'] }}" placeholder="{{ __('e.g., Company laptop / access card returned') }}"></td>
                                <td>
                                    <select name="clearance_status[]" class="form-control form-control-sm">
                                        <option value="Pending" {{ ($chk['status'] ?? '') == 'Pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                        <option value="Returned" {{ ($chk['status'] ?? '') == 'Returned' ? 'selected' : '' }}>{{ __('Returned / Cleared') }}</option>
                                        <option value="Not Applicable" {{ ($chk['status'] ?? '') == 'Not Applicable' ? 'selected' : '' }}>{{ __('Not Applicable (N/A)') }}</option>
                                    </select>
                                </td>
                                <td class="text-center"><button type="button" class="btn btn-xs text-danger remove-row"><i class="ti ti-trash"></i></button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Employee Declaration & Legal Undertaking --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="ti ti-file-certificate me-2 text-primary"></i>{{ __('Employee Legal Declaration & Undertaking Terms') }}</h5>
                <button type="button" class="btn btn-xs btn-outline-secondary" id="reset_declaration_btn">
                    <i class="ti ti-rotate-clockwise me-1"></i> {{ __('Reset to Default Terms') }}
                </button>
            </div>
            <div class="card-body">
                <small class="text-muted d-block mb-2">
                    {{ __('This declaration and undertaking is presented to the employee above their digital signature on the public form. You can add more paragraphs, modify clauses, or customize it to your company requirements.') }}
                </small>
                <textarea name="declaration_text" id="declaration_text" class="form-control font-monospace" rows="6" required style="line-height: 1.6; font-size: 13px;">{{ old('declaration_text', $settlement->getDeclarationText()) }}</textarea>
            </div>
        </div>

        {{-- Management & Payment --}}
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0 text-white"><i class="ti ti-cash me-2"></i>{{ __('4. Management Sign-off & Payment Information') }}</h5>
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
                            <option value="">-- Choose Mode --</option>
                            <option value="NEFT" {{ $settlement->payment_mode == 'NEFT' ? 'selected' : '' }}>NEFT</option>
                            <option value="RTGS" {{ $settlement->payment_mode == 'RTGS' ? 'selected' : '' }}>RTGS</option>
                            <option value="Bank Transfer" {{ $settlement->payment_mode == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="Cheque" {{ $settlement->payment_mode == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">{{ __('Transaction Ref (UTR No.)') }}</label>
                        <input type="text" name="payment_reference_no" class="form-control" value="{{ $settlement->payment_reference_no }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('HR Representative Name') }}</label>
                        <input type="text" name="hr_representative_name" class="form-control" value="{{ $settlement->hr_representative_name }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Authorized Signatory Name') }}</label>
                        <input type="text" name="authorized_signatory_name" class="form-control" value="{{ $settlement->authorized_signatory_name }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body d-flex justify-content-between">
                <a href="{{ route('settlement.show', $settlement->id) }}" class="btn btn-light">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary px-4"><i class="ti ti-device-floppy me-1"></i> {{ __('Update Settlement') }}</button>
            </div>
        </div>
    </form>
@endsection

@push('script-page')
<script>
    const defaultDeclarationText = @json(App\Models\FullAndFinalSettlement::defaultDeclarationText());
    $('#reset_declaration_btn').on('click', function () {
        if (confirm('{{ __("Reset declaration text to default legal terms?") }}')) {
            $('#declaration_text').val(defaultDeclarationText);
        }
    });

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
            <td><input type="text" name="earnings_name[]" class="form-control form-control-sm" placeholder="Earning Item"></td>
            <td><input type="number" step="0.01" name="earnings_amount[]" class="form-control form-control-sm calc-earning" value="0.00"></td>
            <td><button type="button" class="btn btn-xs text-danger remove-row"><i class="ti ti-trash"></i></button></td>
        </tr>`);
    });

    $('#add_deduction_btn').on('click', function () {
        $('#deductions_table tbody').append(`<tr>
            <td><input type="text" name="deductions_name[]" class="form-control form-control-sm" placeholder="Deduction Item"></td>
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

    recalculateTotals();
</script>
@endpush