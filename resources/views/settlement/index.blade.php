@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Full & Final Settlement') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Full & Final Settlement') }}</li>
@endsection

@section('action-button')
    @can('Manage Settlement')
        <a href="{{ route('settlement.export', request()->query()) }}" class="btn btn-sm btn-success me-2" data-bs-toggle="tooltip" title="{{ __('Export to Excel') }}">
            <i class="ti ti-file-spreadsheet"></i> {{ __('Export') }}
        </a>
    @endcan
    @can('Create Settlement')
        <a href="{{ route('settlement.create') }}" data-bs-toggle="tooltip" title=""
            class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endcan
@endsection

@section('content')
    <div class="row">
        {{-- Filters Section --}}
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body p-3">
                    {{ Form::open(['route' => ['settlement.index'], 'method' => 'GET', 'id' => 'settlement_filter']) }}
                    <div class="row align-items-end g-2">
                        {{-- Search Input --}}
                        <div class="col-xl-3 col-lg-3 col-md-6 col-12">
                            <label class="form-label small fw-bold">{{ __('Search') }}</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="ti ti-search"></i></span>
                                {{ Form::text('search', request('search'), ['class' => 'form-control', 'placeholder' => __('Settlement #, Name, Code...')]) }}
                            </div>
                        </div>

                        {{-- Employee Filter --}}
                        @if (\Auth::user()->type !== 'employee')
                            <div class="col-xl-2 col-lg-3 col-md-6 col-12">
                                <label class="form-label small fw-bold">{{ __('Employee') }}</label>
                                {{ Form::select('employee_id', $employees ?? ['' => __('All Employees')], request('employee_id'), ['class' => 'form-select form-select-sm select']) }}
                            </div>
                        @endif

                        {{-- Settlement Status Filter --}}
                        <div class="col-xl-2 col-lg-2 col-md-6 col-12">
                            <label class="form-label small fw-bold">{{ __('Settlement Status') }}</label>
                            {{ Form::select('status', [
                                '' => __('All Statuses'),
                                'draft' => __('Draft'),
                                'sent' => __('Sent to Employee'),
                                'signed' => __('Signed by Employee'),
                                'cleared' => __('Cleared & Paid'),
                            ], request('status'), ['class' => 'form-select form-select-sm select']) }}
                        </div>

                        {{-- Disbursal / Payment Status Filter --}}
                        <div class="col-xl-2 col-lg-2 col-md-6 col-12">
                            <label class="form-label small fw-bold">{{ __('Payment Status') }}</label>
                            {{ Form::select('disbursal_status', [
                                '' => __('All Disbursal Statuses'),
                                'Pending' => __('Pending'),
                                'Cleared' => __('Cleared & Paid'),
                            ], request('disbursal_status'), ['class' => 'form-select form-select-sm select']) }}
                        </div>

                        {{-- Start Date --}}
                        <div class="col-xl-1 col-lg-2 col-md-3 col-6">
                            <label class="form-label small fw-bold">{{ __('From Date') }}</label>
                            {{ Form::date('start_date', request('start_date'), ['class' => 'form-control form-control-sm']) }}
                        </div>

                        {{-- End Date --}}
                        <div class="col-xl-1 col-lg-2 col-md-3 col-6">
                            <label class="form-label small fw-bold">{{ __('To Date') }}</label>
                            {{ Form::date('end_date', request('end_date'), ['class' => 'form-control form-control-sm']) }}
                        </div>

                        {{-- Filter Action Buttons --}}
                        <div class="col-xl-1 col-lg-auto col-md-auto col-12 d-flex gap-1 justify-content-end">
                            <button type="submit" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Apply Filter') }}">
                                <i class="ti ti-filter"></i>
                            </button>
                            <a href="{{ route('settlement.index') }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="{{ __('Reset Filters') }}">
                                <i class="ti ti-refresh"></i>
                            </a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>

        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>{{ __('Settlement #') }}</th>
                                    <th>{{ __('Employee') }}</th>
                                    <th>{{ __('Department') }}</th>
                                    <th>{{ __('Designation') }}</th>
                                    <th>{{ __('Last Working Day') }}</th>
                                    <th>{{ __('Net Payable') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @if (Gate::check('Edit Settlement') || Gate::check('Delete Settlement') || Gate::check('Show Settlement') || Gate::check('Send Settlement Mail') || \Auth::user()->can('Manage Settlement'))
                                        <th width="220px">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($settlements as $settlement)
                                    <tr>
                                        <td>
                                            <a href="{{ route('settlement.show', $settlement->id) }}" class="btn btn-outline-primary">
                                                {{ $settlement->settlement_number }}
                                            </a>
                                            @if(!empty($settlement->industry_type))
                                                <small class="text-muted d-block text-uppercase mt-1">{{ $settlement->industry_type }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="font-style">{{ $settlement->employee_name }}</div>
                                            <small class="text-muted">{{ $settlement->employee_code }}</small>
                                        </td>
                                        <td>{{ $settlement->department ?: '-' }}</td>
                                        <td>{{ $settlement->designation ?: '-' }}</td>
                                        <td>{{ $settlement->formatDate($settlement->last_working_day) }}</td>
                                        <td class="text-success font-weight-bold">
                                            {{ $settlement->formatPrice($settlement->net_amount) }}
                                        </td>
                                        <td>
                                            @if ($settlement->status === 'draft')
                                                <span class="status_badge badge bg-secondary p-2 px-3 rounded">{{ __('Draft') }}</span>
                                            @elseif ($settlement->status === 'sent')
                                                <span class="status_badge badge bg-info p-2 px-3 rounded">{{ __('Sent to Employee') }}</span>
                                            @elseif ($settlement->status === 'signed')
                                                <span class="status_badge badge bg-primary p-2 px-3 rounded">{{ __('Signed by Employee') }}</span>
                                            @elseif ($settlement->status === 'cleared')
                                                <span class="status_badge badge bg-success p-2 px-3 rounded">{{ __('Cleared & Paid') }}</span>
                                            @else
                                                <span class="status_badge badge bg-warning p-2 px-3 rounded">{{ ucfirst($settlement->status) }}</span>
                                            @endif

                                            @if($settlement->isLinkExpired())
                                                <small class="badge bg-danger text-white d-block mt-1" style="font-size: 10px;">
                                                    <i class="ti ti-alert-triangle"></i> {{ __('Link Expired') }}
                                                </small>
                                            @elseif($settlement->status !== 'signed' && $settlement->status !== 'cleared')
                                                <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                                    <i class="ti ti-clock"></i> {{ $settlement->daysUntilExpiry() . __(' days left') }}
                                                </small>
                                            @endif
                                        </td>
                                        @if (Gate::check('Edit Settlement') || Gate::check('Delete Settlement') || Gate::check('Show Settlement') || Gate::check('Send Settlement Mail') || \Auth::user()->can('Manage Settlement'))
                                            <td class="Action">
                                                <span>
                                                    {{-- Copy Public Link --}}
                                                    <div class="action-btn bg-secondary me-2">
                                                        <a href="javascript:void(0)"
                                                            class="mx-3 btn btn-sm align-items-center copy-settlement-link"
                                                            data-url="{{ $settlement->public_url }}"
                                                            data-bs-toggle="tooltip" title=""
                                                            data-bs-original-title="{{ __('Copy Secure Form Link') }}">
                                                            <span class="text-white"><i class="ti ti-link"></i></span>
                                                        </a>
                                                    </div>

                                                    {{-- WhatsApp Share --}}
                                                    <div class="action-btn bg-success me-2">
                                                        <a href="javascript:void(0)"
                                                            class="mx-3 btn btn-sm align-items-center open-wa-modal"
                                                            data-name="{{ $settlement->employee_name }}"
                                                            data-code="{{ $settlement->employee_code }}"
                                                            data-phone="{{ preg_replace('/[^0-9]/', '', $settlement->employee->phone ?? '') }}"
                                                            data-url="{{ $settlement->public_url }}"
                                                            data-bs-toggle="tooltip" title=""
                                                            data-bs-original-title="{{ __('Share via WhatsApp') }}">
                                                            <span class="text-white"><i class="ti ti-brand-whatsapp"></i></span>
                                                        </a>
                                                    </div>

                                                    {{-- Send Mail --}}
                                                    @can('Send Settlement Mail')
                                                        <div class="action-btn bg-warning me-2">
                                                            <a href="{{ route('settlement.send.mail', $settlement->id) }}"
                                                                class="mx-3 btn btn-sm align-items-center"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="{{ __('Send Form via Email') }}">
                                                                <span class="text-white"><i class="ti ti-mail"></i></span>
                                                            </a>
                                                        </div>
                                                    @endcan

                                                    {{-- Regenerate / Extend Link --}}
                                                    @if(Gate::check('Edit Settlement') || \Auth::user()->can('Manage Settlement'))
                                                        <div class="action-btn bg-dark me-2">
                                                            <a href="{{ route('settlement.regenerate.link', $settlement->id) }}"
                                                                class="mx-3 btn btn-sm align-items-center"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="{{ __('Regenerate Link (30 Days)') }}">
                                                                <span class="text-white"><i class="ti ti-refresh"></i></span>
                                                            </a>
                                                        </div>
                                                    @endif

                                                    {{-- View Details --}}
                                                    @can('Show Settlement')
                                                        <div class="action-btn bg-primary me-2">
                                                            <a href="{{ route('settlement.show', $settlement->id) }}"
                                                                class="mx-3 btn btn-sm align-items-center"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="{{ __('View Details') }}">
                                                                <span class="text-white"><i class="ti ti-eye"></i></span>
                                                            </a>
                                                        </div>
                                                    @endcan

                                                    {{-- Download PDF --}}
                                                    <div class="action-btn bg-success me-2">
                                                        <a href="{{ route('settlement.download.pdf', $settlement->id) }}" target="_blank"
                                                            class="mx-3 btn btn-sm align-items-center"
                                                            data-bs-toggle="tooltip" title=""
                                                            data-bs-original-title="{{ __('Print / PDF') }}">
                                                            <span class="text-white"><i class="ti ti-download"></i></span>
                                                        </a>
                                                    </div>

                                                    {{-- Edit --}}
                                                    @can('Edit Settlement')
                                                        <div class="action-btn bg-info me-2">
                                                            <a href="{{ route('settlement.edit', $settlement->id) }}"
                                                                class="mx-3 btn btn-sm align-items-center"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="{{ __('Edit Settlement') }}">
                                                                <span class="text-white"><i class="ti ti-pencil"></i></span>
                                                            </a>
                                                        </div>
                                                    @endcan

                                                    {{-- Delete --}}
                                                    @can('Delete Settlement')
                                                        <div class="action-btn bg-danger">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['settlement.destroy', $settlement->id], 'id' => 'delete-form-' . $settlement->id]) !!}
                                                            <a href="javascript:void(0)"
                                                                class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="{{ __('Delete') }}"
                                                                aria-label="{{ __('Delete') }}">
                                                                <span class="text-white"><i class="ti ti-trash"></i></span>
                                                            </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endcan
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- WhatsApp Custom Message Modal --}}
    <div class="modal fade" id="indexWhatsappModal" tabindex="-1" aria-labelledby="indexWhatsappModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title text-white d-flex align-items-center gap-2" id="indexWhatsappModalLabel">
                        <i class="ti ti-brand-whatsapp fs-3"></i> {{ __('Share Settlement via WhatsApp') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small text-uppercase">{{ __('Recipient Employee') }}</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="ti ti-user"></i></span>
                            <input type="text" id="modalWaRecipientName" class="form-control bg-light" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small text-uppercase d-flex justify-content-between align-items-center">
                            <span>
                                {{ __('Phone Number') }} 
                                <small class="text-muted fw-normal">({{ __('Optional with Country Code') }})</small>
                            </span>
                            <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 text-primary small" id="btnIndexResetPhone" style="display: none;">
                                <i class="ti ti-rotate-clockwise"></i> {{ __('Reset to Profile Number') }}
                            </button>
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="ti ti-phone"></i></span>
                            <input type="text" id="modalWaRecipientPhone" class="form-control" 
                                placeholder="{{ __('e.g. 919876543210 (Country code + Number)') }}">
                        </div>
                        <small class="text-muted fs-8 d-block mt-1">
                            <i class="ti ti-edit text-primary me-1"></i>
                            {{ __('Pre-filled from employee profile. You can edit this to any different number, or leave blank to select inside WhatsApp.') }}
                        </small>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold text-dark small text-uppercase mb-0">{{ __('WhatsApp Message') }}</label>
                            <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 text-primary small" id="btnIndexResetWa">
                                <i class="ti ti-rotate-clockwise"></i> {{ __('Reset to Default') }}
                            </button>
                        </div>
                        <textarea id="modalWaMessage" class="form-control font-sans" rows="5" placeholder="{{ __('Type your message here...') }}"></textarea>
                        <small class="text-muted fs-8 mt-1 d-block">
                            <i class="ti ti-info-circle"></i> {{ __('You can freely modify this message, add greetings, or customize instructions before sending.') }}
                        </small>
                    </div>

                    <div class="p-3 bg-light rounded border">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold small text-dark"><i class="ti ti-link me-1"></i>{{ __('Clearance Form URL:') }}</span>
                            <button type="button" class="btn btn-xs btn-outline-secondary copy-settlement-link" id="modalWaCopyBtn" data-url="">
                                <i class="ti ti-copy"></i> {{ __('Copy') }}
                            </button>
                        </div>
                        <div id="modalWaUrlDisplay" class="small text-muted text-break font-monospace"></div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-success" id="btnIndexSendWa">
                        <i class="ti ti-brand-whatsapp me-1"></i> {{ __('Open in WhatsApp') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
<script>
    $(document).on('click', '.copy-settlement-link', function () {
        var url = $(this).attr('data-url');
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(url).then(function () {
                show_toastr('Success', '{{ __("Secure settlement form link copied to clipboard!") }}', 'success');
            }).catch(function () {
                copyFallback(url);
            });
        } else {
            copyFallback(url);
        }
    });

    function copyFallback(url) {
        var dummy = document.createElement("input");
        document.body.appendChild(dummy);
        dummy.value = url;
        dummy.select();
        document.execCommand("copy");
        document.body.removeChild(dummy);
        show_toastr('Success', '{{ __("Secure settlement form link copied to clipboard!") }}', 'success');
    }

    // Index WhatsApp Modal Handler
    var currentSelectedEmpName = '';
    var currentSelectedUrl = '';
    var currentSelectedPhone = '';

    $(document).on('click', '.open-wa-modal', function () {
        var name = $(this).data('name') || '';
        var code = $(this).data('code') || '';
        var phone = $(this).data('phone') || '';
        var url = $(this).data('url') || '';

        currentSelectedEmpName = name;
        currentSelectedUrl = url;
        currentSelectedPhone = phone;

        $('#modalWaRecipientName').val(name + (code ? ' (' + code + ')' : ''));
        $('#modalWaRecipientPhone').val(phone);
        if (phone) {
            $('#btnIndexResetPhone').show();
        } else {
            $('#btnIndexResetPhone').hide();
        }
        $('#modalWaCopyBtn').attr('data-url', url);
        $('#modalWaUrlDisplay').text(url);

        var defaultMsg = "Hello " + name + ", please review and complete your Full & Final Settlement & Departmental Clearance form: " + url;
        $('#modalWaMessage').val(defaultMsg);

        $('#indexWhatsappModal').modal('show');
    });

    $('#btnIndexResetPhone').on('click', function () {
        $('#modalWaRecipientPhone').val(currentSelectedPhone);
    });

    $('#btnIndexResetWa').on('click', function () {
        var defaultMsg = "Hello " + currentSelectedEmpName + ", please review and complete your Full & Final Settlement & Departmental Clearance form: " + currentSelectedUrl;
        $('#modalWaMessage').val(defaultMsg);
    });

    $('#btnIndexSendWa').on('click', function () {
        var msg = $('#modalWaMessage').val().trim();
        if (!msg) {
            alert('{{ __("Please enter a message to send.") }}');
            return;
        }
        var phone = $('#modalWaRecipientPhone').val().trim().replace(/[^0-9]/g, '');
        var waUrl = "https://api.whatsapp.com/send?";
        var params = [];
        if (phone) {
            params.push("phone=" + encodeURIComponent(phone));
        }
        params.push("text=" + encodeURIComponent(msg));
        waUrl += params.join('&');

        window.open(waUrl, '_blank');
        $('#indexWhatsappModal').modal('hide');
    });
</script>
@endpush