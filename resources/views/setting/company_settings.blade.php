@extends('layouts.admin')
@section('page-title')
{{ __('Settings') }}
@endsection
@php
// $logo = asset(Storage::url('uploads/logo/'));
$logo = \App\Models\Utility::get_file('uploads/logo/');
$company_logo = \App\Models\Utility::getValByName('company_logo');
$company_logo_light = \App\Models\Utility::getValByName('company_logo_light');
$company_favicon = \App\Models\Utility::getValByName('company_favicon');
$color = isset($settings['theme_color']) ? $settings['theme_color'] : 'theme-4';

$settings = App\Models\Utility::settings();

$currantLang = \App\Models\Utility::languages();
$SITE_RTL = \App\Models\Utility::getValByName('SITE_RTL');
$lang = \App\Models\Utility::getValByName('default_language');
@endphp

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
<li class="breadcrumb-item">{{ __('Settings') }}</li>
@endsection

@push('css-page')
<link rel="stylesheet" href="{{ asset('css/summernote/summernote-bs4.css') }}">
@endpush
<style>
    .shift-table {
    min-width: 1300px; /* increase width */
}

.shift-table th,
.shift-table td {
    white-space: nowrap;
    vertical-align: middle;
}

/* Fix input size inside table */
/* .shift-table input,
.shift-table select {
    width: 120px;
    min-width: 100px;
} */
.shift-table select.shift-break-type {
    width: 120px;
    min-width: 100px;
}
/* Optional: better spacing */
.shift-table td {
    padding: 10px;
}

/* ── Salary Slip Settings ─────────────────────────────────────────── */
/* Color picker styling */
.form-control-color {
    cursor: pointer;
    border-radius: 6px;
}
.form-control-color::-webkit-color-swatch-wrapper { padding: 0; }
.form-control-color::-webkit-color-swatch {
    border: none;
    border-radius: 4px;
}

/* Live preview iframe */
#salary-slip-preview-iframe {
    transition: opacity 0.3s ease;
}

/* Stamp preview styling */
#stamp_preview {
    transition: all 0.2s ease;
}

/* Two-column layout for settings */
#salary-slip-settings .card-body .row.g-4 {
    min-height: 520px;
}

/* ── Payslip Color Swatches (brand-settings style) ──────────────── */
.payslip-color-swatches {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    width: 200px;
    margin: 0;
    padding: 0;
}
.payslip-swatch {
    width: 35px;
    height: 25px;
    border-radius: 3px;
    display: inline-block;
    cursor: pointer;
    border: 2px solid transparent;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.28);
    transition: all 0.2s ease;
    overflow: hidden;
}
.payslip-swatch:hover {
    transform: scale(1.1);
    box-shadow: 0 2px 6px rgba(0,0,0,0.35);
}
/* Relies on brand-settings .active_color (border: 2px solid #000 !important) */

/* Custom color input — same visual as brand-settings .color-wrp .color-picker-wrp input[type="color"] */
.payslip-custom-picker {
    background-color: #fff;
    height: 55px;
    cursor: pointer;
    border-radius: 3px;
    margin: 0;
    padding: 0;
    border: 0;
    width: 50px;
    flex-shrink: 0;
}
</style>
@push('script-page')
<script src="{{ asset('css/summernote/summernote-bs4.js') }}"></script>
<script>
    $('.colorPicker').on('click', function(e) {

        $('body').removeClass('custom-color');
        if (/^theme-\d+$/) {
            $('body').removeClassRegex(/^theme-\d+$/);
        }
        $('body').addClass('custom-color');
        $('.themes-color-change').removeClass('active_color');
        $(this).addClass('active_color');
        const input = document.getElementById("color-picker");
        setColor();
        input.addEventListener("input", setColor);

        function setColor() {
            $(':root').css('--color-customColor', input.value);
        }

        $(`input[name='color_flag`).val('true');
    });

    $('.themes-color-change').on('click', function() {

        $(`input[name='color_flag`).val('false');

        var color_val = $(this).data('value');
        $('body').removeClass('custom-color');
        if (/^theme-\d+$/) {
            $('body').removeClassRegex(/^theme-\d+$/);
        }
        $('body').addClass(color_val);
        $('.theme-color').prop('checked', false);
        $('.themes-color-change').removeClass('active_color');
        $('.colorPicker').removeClass('active_color');
        $(this).addClass('active_color');
        $(`input[value=${color_val}]`).prop('checked', true);
    });

    $.fn.removeClassRegex = function(regex) {
        return $(this).removeClass(function(index, classes) {
            return classes.split(/\s+/).filter(function(c) {
                return regex.test(c);
            }).join(' ');
        });
    };
</script>
<script>
    $(document).on('change', '.email-template-checkbox', function() {
        var url = $(this).data('url');
        var chbox = $(this);

        $.ajax({
            url: url,
            type: 'GET',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                status: chbox.val()
            },
            success: function(data) {

            },
        });
    });
</script>
<script>
    $(document).on("click", '.send_email', function(e) {
        e.preventDefault();
        var title = $(this).attr('data-title');

        var size = 'md';
        var url = $(this).attr('data-url');
        if (typeof url != 'undefined') {
            $("#commonModal .modal-title").html(title);
            $("#commonModal .modal-dialog").addClass('modal-' + size);
            $("#commonModal").modal('show');
            $.post(url, {
                _token: '{{ csrf_token() }}',
                mail_driver: $("#mail_driver").val(),
                mail_host: $("#mail_host").val(),
                mail_port: $("#mail_port").val(),
                mail_username: $("#mail_username").val(),
                mail_password: $("#mail_password").val(),
                mail_encryption: $("#mail_encryption").val(),
                mail_from_address: $("#mail_from_address").val(),
                mail_from_name: $("#mail_from_name").val(),
            }, function(data) {
                $('#commonModal .body').html(data);
            });
        }
    });


    $(document).on('submit', '#test_email', function(e) {
        e.preventDefault();
        $("#email_sending").show();
        var post = $(this).serialize();
        var url = $(this).attr('action');
        $.ajax({
            type: "post",
            url: url,
            data: post,
            cache: false,
            beforeSend: function() {
                $('#test_email .btn-create').attr('disabled', 'disabled');
            },
            success: function(data) {
                if (data.is_success) {
                    show_toastr('Success', data.message, 'success');
                } else {
                    show_toastr('Error', data.message, 'error');
                }
                $("#email_sending").hide();
                $('#commonModal').modal('hide');
            },
            complete: function() {
                $('#test_email .btn-create').removeAttr('disabled');
            },
        });
    });
</script>
<script>
    var scrollSpy = new bootstrap.ScrollSpy(document.body, {
        target: '#useradd-sidenav',
        offset: 300
    })

    // $('.themes-color-change').on('click', function() {
    //     var color_val = $(this).data('value');
    //     $('.theme-color').prop('checked', false);
    //     $('.themes-color-change').removeClass('active_color');
    //     $(this).addClass('active_color');
    //     $(`input[value=${color_val}]`).prop('checked', true);

    // });
</script>
<script>
    document.getElementById('company_logo').onchange = function() {
        var src = URL.createObjectURL(this.files[0])
        document.getElementById('image').src = src
    }
</script>
<script>
    document.getElementById('company_logo_light').onchange = function() {
        var src = URL.createObjectURL(this.files[0])
        document.getElementById('image1').src = src
    }
</script>
<script>
    document.getElementById('company_favicon').onchange = function() {
        var src = URL.createObjectURL(this.files[0])
        document.getElementById('image2').src = src
    }
</script>
<script>
    // ── Stamp data URL cache (for live preview via postMessage) ────────
    var _cachedStampDataUrl = '';

    document.getElementById('company_stamp').onchange = function() {
        if (this.files && this.files[0]) {
            var src = URL.createObjectURL(this.files[0]);
            var preview = document.getElementById('stamp_preview');
            var placeholder = document.getElementById('stamp_preview_placeholder');
            preview.src = src;
            preview.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';

            // Read the file as a base64 data URL for the live preview iframe
            var reader = new FileReader();
            reader.onload = function(e) {
                _cachedStampDataUrl = e.target.result;
                updateSalarySlipPreview();
            };
            reader.readAsDataURL(this.files[0]);
        } else {
            // File was cleared — revert to server-stored stamp
            _cachedStampDataUrl = '';
            var preview = document.getElementById('stamp_preview');
            if (preview) preview.style.display = 'none';
            var ph = document.getElementById('stamp_preview_placeholder');
            if (ph) ph.style.display = 'flex';
            updateSalarySlipPreview();
        }
    }
</script>

<script>
    /**
     * Live preview for salary slip settings.
     * Reads the dropdown + color picker and reloads the preview iframe.
     */
    function updateSalarySlipPreview() {
        var templateEl = document.getElementById('payslip_template');
        var colorInput = document.getElementById('payslip_primary_color');
        var hexDisplay = document.getElementById('payslip_color_hex');
        var iframe = document.getElementById('salary-slip-preview-iframe');
        var loading = document.getElementById('preview-loading');

        if (!templateEl || !iframe) return;

        var tpl = templateEl.value;
        var clr = colorInput ? colorInput.value : '';

        // Update hex display
        if (hexDisplay) {
            hexDisplay.value = clr;
        }

        // Show loading spinner
        if (loading) loading.style.display = 'block';

        // Build preview URL (no stamp param — use postMessage to avoid URL length issues)
        var baseUrl = '{{ route("payslip.settings-preview") }}';
        var previewUrl = baseUrl + '?template=' + encodeURIComponent(tpl) + '&color=' + encodeURIComponent(clr);

        // Append section visibility toggles
        var showEmp = document.getElementById('payslip_show_employee_details');
        var showPay = document.getElementById('payslip_show_payment_details');
        var showSig = document.getElementById('payslip_show_signatures');
        var showFtr = document.getElementById('payslip_show_footer');
        if (showEmp) previewUrl += '&show_employee=' + (showEmp.checked ? '1' : '0');
        if (showPay) previewUrl += '&show_payment=' + (showPay.checked ? '1' : '0');
        if (showSig) previewUrl += '&show_signatures=' + (showSig.checked ? '1' : '0');
        if (showFtr) previewUrl += '&show_footer=' + (showFtr.checked ? '1' : '0');

        // Append per-field visibility toggles
        var fieldIds = [
            ['payslip_show_name', 'show_name'],
            ['payslip_show_designation', 'show_designation'],
            ['payslip_show_employee_id', 'show_employee_id'],
            ['payslip_show_department', 'show_department'],
            ['payslip_show_pan_no', 'show_pan_no'],
            ['payslip_show_date_of_joining', 'show_doj'],
            ['payslip_show_bank_name', 'show_bank_name'],
            ['payslip_show_account_no', 'show_account_no'],
            ['payslip_show_bank_code', 'show_bank_code'],
            ['payslip_show_account_holder', 'show_account_holder'],
            ['payslip_show_transaction_mode', 'show_transaction_mode'],
            ['payslip_show_pay_period', 'show_pay_period'],
        ];
        for (var i = 0; i < fieldIds.length; i++) {
            var el = document.getElementById(fieldIds[i][0]);
            if (el) previewUrl += '&' + fieldIds[i][1] + '=' + (el.checked ? '1' : '0');
        }

        // Update iframe and reload
        iframe.src = previewUrl;

        // Hide loading when iframe loads, then send stamp via postMessage
        // Reuse the shared resizer function
        attachPreviewResizer(iframe);
    }

    /**
     * Payslip preset color swatch click handler.
     * When a preset is clicked, highlight it, update the hidden color input, and refresh preview.
     */
    $(document).on('click', '.payslip-swatch', function() {
        var color = $(this).data('color');
        $('.payslip-swatch').removeClass('active_color');
        $(this).addClass('active_color');

        // Update the native color input and hex display
        var colorInput = document.getElementById('payslip_primary_color');
        var hexDisplay = document.getElementById('payslip_color_hex');
        if (colorInput) colorInput.value = color;
        if (hexDisplay) hexDisplay.value = color;

        // Update the preview
        updateSalarySlipPreview();
    });

    /**
     * When the custom color picker changes, deselect all presets and update preview.
     */
    function onPayslipCustomColorChange(color) {
        $('.payslip-swatch').removeClass('active_color');
        var hexDisplay = document.getElementById('payslip_color_hex');
        if (hexDisplay) hexDisplay.value = color;
        updateSalarySlipPreview();
    }

    /**
     * Reset payslip color to the default preset (#584ED2).
     */
    function resetPayslipColor() {
        var defaultColor = '#584ED2';
        var colorInput = document.getElementById('payslip_primary_color');
        var hexDisplay = document.getElementById('payslip_color_hex');
        if (colorInput) colorInput.value = defaultColor;
        if (hexDisplay) hexDisplay.value = defaultColor;

        // Highlight the default swatch
        $('.payslip-swatch').removeClass('active_color');
        $('.payslip-swatch[data-color="' + defaultColor + '"]').addClass('active_color');

        updateSalarySlipPreview();
    }

    /**
     * Attach dynamic height resizer to the preview iframe.
     */
    function attachPreviewResizer(iframe) {
        if (!iframe) return;
        iframe.onload = function() {
            var loading = document.getElementById('preview-loading');
            if (loading) loading.style.display = 'none';

            // Dynamically resize iframe to match content height
            try {
                var doc = iframe.contentDocument || iframe.contentWindow.document;
                if (doc && doc.body) {
                    var h = doc.body.scrollHeight;
                    if (h > 100) {
                        iframe.style.height = h + 'px';
                        iframe.style.minHeight = 'auto';
                    }
                }
            } catch(e) {
                iframe.style.height = 'auto';
            }

            if (window._cachedStampDataUrl) {
                iframe.contentWindow.postMessage({
                    type: 'payslip-stamp',
                    dataUrl: window._cachedStampDataUrl
                }, '*');
            }
        };
    }

    /**
     * Sync the custom color picker and swatch UI on page load.
     */
    $(document).ready(function() {
        // If no preset is selected but a custom color is saved, ensure presets are deselected
        var savedColor = document.getElementById('payslip_primary_color')?.value || '';
        if (savedColor) {
            var matchingSwatch = $('.payslip-swatch[data-color="' + savedColor + '"]');
            if (matchingSwatch.length === 0) {
                $('.payslip-swatch').removeClass('active_color');
            }
        }

        // Attach dynamic height resizer to the initial iframe
        var previewIframe = document.getElementById('salary-slip-preview-iframe');
        attachPreviewResizer(previewIframe);
        // Trigger initial resize once iframe loads (if already loaded, it won't fire)
        if (previewIframe && previewIframe.contentDocument && previewIframe.contentDocument.body) {
            var h = previewIframe.contentDocument.body.scrollHeight;
            if (h > 100) {
                previewIframe.style.height = h + 'px';
                previewIframe.style.minHeight = 'auto';
            }
        }
        // Also try after a small delay for async rendering
        setTimeout(function() {
            try {
                var iframe = document.getElementById('salary-slip-preview-iframe');
                var doc = iframe.contentDocument || iframe.contentWindow.document;
                if (doc && doc.body) {
                    var h = doc.body.scrollHeight;
                    if (h > 100) {
                        iframe.style.height = h + 'px';
                        iframe.style.minHeight = 'auto';
                    }
                }
            } catch(e) {}
        }, 600);
    });

    function toggleTimeFields() {
        for (let i = 0; i < 4; i++) {
            let start = document.querySelector(`[name="company_start_time${i === 0 ? '' : i}"]`);
            let end = document.querySelector(`[name="company_end_time${i === 0 ? '' : i}"]`);
            let nextStart = document.querySelector(`[name="company_start_time${i+1}"]`);
            let nextEnd = document.querySelector(`[name="company_end_time${i+1}"]`);

            if (!start || !end) continue;

            // 1️⃣ Enable/disable next inputs
            if (nextStart && nextEnd) {
                if (start.value && end.value) {
                    nextStart.removeAttribute('disabled');
                    nextEnd.removeAttribute('disabled');
                } else {
                    nextStart.setAttribute('disabled', true);
                    nextEnd.setAttribute('disabled', true);
                    nextStart.value = '';
                    nextEnd.value = '';
                }
            }

            // 2️⃣ Current inputs required if any value exists
            if (start.value || end.value) {
                start.required = true;
                end.required = true;
            } else {
                start.required = false;
                end.required = false;
            }
        }
    }

    // Initialize on page load and on input
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.company-time').forEach(el => {
            el.addEventListener('input', toggleTimeFields);
        });
        toggleTimeFields();
    });
</script>
@endpush
@section('content')
<div class="row">

    <div class="col-sm-12">
        <div class="row">
            <div class="col-xl-3">
                <div class="card sticky-top">
                    <div class="list-group list-group-flush" id="useradd-sidenav">

                        <a href="#brand-settings" id="brand-setting-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Brand Settings') }}
                            <div
                                class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>

                        <a href="#system-settings" id="system-setting-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('System Settings') }}
                            <div
                                class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>

                        <a href="#email-settings" id="email-setting-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Email Settings') }}
                            <div
                                class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>

                        <a href="#company-settings" id="company-setting-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Company Settings') }}
                            <div
                                class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>

                        <a id="email-notification-tab" data-toggle="tab" href="#email-notification-settings"
                            role="tab" aria-controls="" aria-selected="false"
                            class="list-group-item list-group-item-action border-0">{{ __('Email Notification Settings') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>

                        <a href="#ip-restriction-settings" id="ip-restrict-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('IP Access Control') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>

                        @if (Auth::user()->type == 'company')
                        <a href="#zoom-meeting-settings" id="zoom-meeting-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Zoom Meeting Settings') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>

                        <a href="#slack-settings" id="slack-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Slack Settings') }}
                            <div
                                class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>

                        <a href="#telegram-settings" id="telegram-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Telegram Settings') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>

                        <a href="#twilio-settings" id="twilio-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Twilio Settings') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>
                        @endif
                        <a href="#offer-letter-settings" id="offer-letter-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Offer Letter Settings') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>

                        <a href="#joining-letter-settings" id="joining-letter-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Joining Letter Settings') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>

                        <a href="#experience-certificate-settings" id="experience-certificate-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Certificate of Experience Settings') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>

                        <a href="#noc-settings" id="noc-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('No Objection Certificate Settings') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>

                        <a href="#google-calender" id="google-calendar-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Google Calendar Settings') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>

                        <a href="#webhook-settings" id="webhook-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Webhook Settings') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>

                        <a href="#salary-slip-settings" id="salary-slip-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('Salary Slip Settings') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>

                        {{-- remove biometric code --}}
                        {{-- <a href="#biometric-attendance" id="biometric-attendance-tab"
                                class="list-group-item list-group-item-action border-0">{{ __('Biometric Attendance Settings') }}
                        <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a> --}}

                        @if(\App\Services\HmrcService::isEnabled())
                        <a href="#hmrc-paye-settings" id="hmrc-paye-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('HMRC PAYE Settings') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>
                        @endif

                        <a href="#idab-employee-sync-settings" id="idab-employee-sync-tab"
                            class="list-group-item list-group-item-action border-0">{{ __('HRMS Employee Sync') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>

                    </div>

                </div>
            </div>

            <div class="col-xl-9">
                <div class="" id="brand-settings">
                    {{ Form::model($settings, ['route' => 'business.setting', 'method' => 'POST', 'enctype' => 'multipart/form-data']) }}
                    <div class="row">
                        <div class="col-lg-12 col-sm-12 col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>{{ __('Brand Settings') }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-4 col-sm-6 col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>{{ __('Logo dark') }}</h5>
                                                </div>
                                                <div class="card-body pt-0">
                                                    <div class=" setting-card">
                                                        <div class="logo-content mt-4 ">
                                                            <a href="{{ $logo . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png') }}"
                                                                target="_blank">
                                                                <img id="image" alt="your image"
                                                                    src="{{ $logo . (isset($company_logo) && !empty($company_logo) ? $company_logo . '?' . time() : 'logo-dark.png' . '?' . time()) }}"
                                                                    width="150px" height="55px" class="big-logo">
                                                            </a>
                                                        </div>
                                                        <div class="choose-files mt-4">
                                                            <label for="company_logo">
                                                                <div class=" bg-primary "> <i
                                                                        class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                                </div>
                                                                <input type="file" class="form-control file d-none"
                                                                    name="company_logo" id="company_logo"
                                                                    data-filename="company_logo">
                                                            </label>
                                                        </div>
                                                        @error('company_logo')
                                                        <div class="row">
                                                            <span class="invalid-company_logo" role="alert">
                                                                <strong
                                                                    class="text-danger">{{ $message }}</strong>
                                                            </span>
                                                        </div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-6 col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>{{ __('Logo Light') }}</h5>
                                                </div>

                                                <div class="card-body pt-0">
                                                    <div class=" setting-card">
                                                        <div class="logo-content mt-4">
                                                            {{-- <img id="image1" src="{{ $logo . '/' . (isset($company_logo_light) && !empty($company_logo_light) ? $company_logo_light : 'logo-light.png') }}"
                                                            class="logo logo-sm img_setting"
                                                            style="filter: drop-shadow(2px 3px 7px #011c4b);"> --}}
                                                            <a href="{{ $logo . (isset($company_logo_light) && !empty($company_logo_light) ? $company_logo_light : 'logo-light.png') }}"
                                                                target="_blank">
                                                                <img id="image1" alt="your image"
                                                                    src="{{ $logo . (isset($company_logo_light) && !empty($company_logo_light) ? $company_logo_light . '?' . time() : 'logo-light.png' . '?' . time()) }}"
                                                                    width="150px" height="55px"
                                                                    class="big-logo" style="filter: drop-shadow(2px 3px 7px #011c4b);">
                                                            </a>

                                                        </div>
                                                        <div class="choose-files mt-4">
                                                            <label for="company_logo_light">
                                                                <div class=" bg-primary dark_logo_update"> <i
                                                                        class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                                </div>
                                                                <input type="file" class="form-control file d-none"
                                                                    name="company_logo_light" id="company_logo_light"
                                                                    data-filename="dark_logo_update">
                                                            </label>
                                                        </div>
                                                        @error('company_logo_light')
                                                        <div class="row">
                                                            <span class="invalid-company_logo_light" role="alert">
                                                                <strong
                                                                    class="text-danger">{{ $message }}</strong>
                                                            </span>
                                                        </div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-6 col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>{{ __('Favicon') }}</h5>
                                                </div>
                                                <div class="card-body pt-0">
                                                    <div class=" setting-card">
                                                        <div class="logo-content mt-4 setting-logo">
                                                            {{-- <img src="{{ $logo . '/' . (isset($company_favicon) && !empty($company_favicon) ? $company_favicon : 'favicon.png') }}"
                                                            width="50px" class="logo logo-sm img_setting"> --}}
                                                            <a href="{{ $logo . (isset($company_favicon) && !empty($company_favicon) ? $company_favicon : 'favicon.png') }}"
                                                                target="_blank">
                                                                <img id="image2" alt="your image"
                                                                    src="{{ $logo . (isset($company_favicon) && !empty($company_favicon) ? $company_favicon . '?' . time() : 'favicon.png' . '?' . time()) }}"
                                                                    width="50px" height="50px"
                                                                    class="big-logo" style="filter: drop-shadow(2px 3px 7px #011c4b);">
                                                            </a>
                                                        </div>
                                                        <div class="choose-files mt-3">

                                                            <label for="company_favicon">
                                                                <div class=" bg-primary company_favicon"> <i
                                                                        class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                                </div>
                                                                <input type="file" class="form-control file d-none"
                                                                    name="company_favicon" id="company_favicon"
                                                                    data-filename="company_favicon">
                                                            </label>
                                                        </div>
                                                        @error('company_favicon')
                                                        <div class="row">
                                                            <span class="invalid-logo" role="alert">
                                                                <strong
                                                                    class="text-danger">{{ $message }}</strong>
                                                            </span>
                                                        </div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group col-md-4">

                                            {{ Form::label('title_text', __('Title Text'), ['class' => 'col-form-label']) }}
                                            {{ Form::text('title_text', null, ['class' => 'form-control', 'placeholder' => __('Enter Title Text')]) }}

                                            @error('title_text')
                                            <span class="invalid-title_text" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>

                                        <div class="form-group col-md-4">
                                            {{ Form::label('default_language', __('Default Language'), ['class' => 'col-form-label']) }}
                                            <select name="default_language" id="default_language"
                                                class="form-control">
                                                {{-- @foreach (\App\Models\Utility::languages() as $language)
                                                        <option @if ($lang == $language) selected @endif
                                                            value="{{ $language }}">{{ Str::upper($language) }}
                                                </option>
                                                @endforeach --}}
                                                @foreach (App\Models\Utility::languages() as $code => $language)
                                                <option @if ($lang==$code) selected @endif
                                                    value="{{ $code }}">{{ Str::ucfirst($language) }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-3 ">
                                            <div class="col switch-width">
                                                <div class="form-group ml-2 mr-3">
                                                    {{ Form::label('SITE_RTL', __('Enable RTL'), ['class' => 'col-form-label']) }}
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" data-toggle="switchbutton"
                                                            data-onstyle="primary" class="" name="SITE_RTL"
                                                            id="SITE_RTL"
                                                            {{ $SITE_RTL == 'on' ? 'checked="checked"' : '' }}>
                                                        <label class="custom-control-label mb-1"
                                                            for="SITE_RTL"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <h5 class="mt-3 mb-3">{{ __('Theme Customizer') }}</h5>
                                        <div class="col-12">
                                            <div class="pct-body">
                                                <div class="row">
                                                    <div class="col-4">
                                                        <h6 class="">
                                                            <i data-feather="credit-card"
                                                                class="me-2"></i>{{ __('Primary color Settings') }}
                                                        </h6>
                                                        <hr class="my-2" />
                                                        <div class="color-wrp">
                                                            <div class="theme-color themes-color">
                                                                <a href="javascript:void(0)"
                                                                    class="themes-color-change {{ $color == 'theme-1' ? 'active_color' : '' }}"
                                                                    data-value="theme-1"></a>
                                                                <input type="radio" class="theme_color d-none"
                                                                    name="theme_color" value="theme-1"
                                                                    {{ $color == 'theme-1' ? 'checked' : '' }}>
                                                                <a href="javascript:void(0)"
                                                                    class="themes-color-change {{ $color == 'theme-2' ? 'active_color' : '' }}"
                                                                    data-value="theme-2"></a>
                                                                <input type="radio" class="theme_color d-none"
                                                                    name="theme_color" value="theme-2"
                                                                    {{ $color == 'theme-2' ? 'checked' : '' }}>
                                                                <a href="javascript:void(0)"
                                                                    class="themes-color-change {{ $color == 'theme-3' ? 'active_color' : '' }}"
                                                                    data-value="theme-3"></a>
                                                                <input type="radio" class="theme_color d-none"
                                                                    name="theme_color" value="theme-3"
                                                                    {{ $color == 'theme-3' ? 'checked' : '' }}>
                                                                <a href="javascript:void(0)"
                                                                    class="themes-color-change {{ $color == 'theme-4' ? 'active_color' : '' }}"
                                                                    data-value="theme-4"></a>
                                                                <input type="radio" class="theme_color d-none"
                                                                    name="theme_color" value="theme-4"
                                                                    {{ $color == 'theme-4' ? 'checked' : '' }}>
                                                                <a href="javascript:void(0)"
                                                                    class="themes-color-change {{ $color == 'theme-5' ? 'active_color' : '' }}"
                                                                    data-value="theme-5"></a>
                                                                <input type="radio" class="theme_color d-none"
                                                                    name="theme_color" value="theme-5"
                                                                    {{ $color == 'theme-5' ? 'checked' : '' }}>
                                                                <br>
                                                                <a href="javascript:void(0)"
                                                                    class="themes-color-change {{ $color == 'theme-6' ? 'active_color' : '' }}"
                                                                    data-value="theme-6"></a>
                                                                <input type="radio" class="theme_color d-none"
                                                                    name="theme_color" value="theme-6"
                                                                    {{ $color == 'theme-6' ? 'checked' : '' }}>
                                                                <a href="javascript:void(0)"
                                                                    class="themes-color-change {{ $color == 'theme-7' ? 'active_color' : '' }}"
                                                                    data-value="theme-7"></a>
                                                                <input type="radio" class="theme_color d-none"
                                                                    name="theme_color" value="theme-7"
                                                                    {{ $color == 'theme-7' ? 'checked' : '' }}>
                                                                <a href="javascript:void(0)"
                                                                    class="themes-color-change {{ $color == 'theme-8' ? 'active_color' : '' }}"
                                                                    data-value="theme-8"></a>
                                                                <input type="radio" class="theme_color d-none"
                                                                    name="theme_color" value="theme-8"
                                                                    {{ $color == 'theme-8' ? 'checked' : '' }}>
                                                                <a href="javascript:void(0)"
                                                                    class="themes-color-change {{ $color == 'theme-9' ? 'active_color' : '' }}"
                                                                    data-value="theme-9"></a>
                                                                <input type="radio" class="theme_color d-none"
                                                                    name="theme_color" value="theme-9"
                                                                    {{ $color == 'theme-9' ? 'checked' : '' }}>
                                                                <a href="javascript:void(0)"
                                                                    class="themes-color-change {{ $color == 'theme-10' ? 'active_color' : '' }}"
                                                                    data-value="theme-10"></a>
                                                                <input type="radio" class="theme_color d-none"
                                                                    name="theme_color" value="theme-10"
                                                                    {{ $color == 'theme-10' ? 'checked' : '' }}>
                                                            </div>
                                                            <div class="color-picker-wrp">
                                                                <input type="color"
                                                                    value="{{ $color ? $color : '' }}"
                                                                    class="colorPicker {{ isset($settings['color_flag']) && $settings['color_flag'] == 'true' ? 'active_color' : '' }} image-input"
                                                                    name="custom_color" data-bs-toggle="tooltip"
                                                                    data-bs-placement="top" id="color-picker">
                                                                <input type="hidden" name="custom-color"
                                                                    id="colorCode">
                                                                <input type='hidden' name="color_flag"
                                                                    value={{ isset($settings['color_flag']) && $settings['color_flag'] == 'true' ? 'true' : 'false' }}>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <h6 class=" ">
                                                            <i data-feather="layout"
                                                                class="me-2"></i>{{ __('Sidebar Settings') }}
                                                        </h6>
                                                        <hr class="my-2 " />
                                                        <div class="form-check form-switch ">
                                                            <input type="checkbox" class="form-check-input"
                                                                id="cust_theme_bg" name="cust_theme_bg"
                                                                {{ $settings['cust_theme_bg'] == 'on' ? 'checked' : '' }} />

                                                            <label class="form-check-label f-w-600 pl-1"
                                                                for="cust_theme_bg">{{ __('Transparent layout') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <h6 class=" ">
                                                            <i data-feather="sun"
                                                                class=""></i>{{ __('Layout Settings') }}
                                                        </h6>

                                                        <hr class=" my-2  " />
                                                        <div class="form-check form-switch mt-2 ">
                                                            <input type="hidden" name="cust_darklayout"
                                                                value="off">
                                                            <input type="checkbox" class="form-check-input"
                                                                id="cust_darklayout" name="cust_darklayout"
                                                                {{ $settings['cust_darklayout'] == 'on' ? 'checked' : '' }} />

                                                            <label class="form-check-label f-w-600 pl-1"
                                                                for="cust_darklayout">{{ __('Dark Layout') }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <button class="btn-submit btn btn-primary" type="submit">
                                        {{ __('Save Changes') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>

                <div class="" id="system-settings">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ __('System Settings') }}</h5>
                        </div>
                        {{ Form::model($settings, ['route' => 'system.settings', 'method' => 'post']) }}
                        <div class="card-body">
                            <div class="row company-setting">
                                <div class="form-group col-md-4">
                                    {{ Form::label('site_currency', __('Currency'), ['class' => 'col-form-label']) }}<x-required></x-required>
                                    {{ Form::text('site_currency', null, ['class' => 'form-control', 'placeholder' => __('Enter Currency')]) }}
                                    <small class="text-xs">
                                        {{ __('Note: Add currency code as per three-letter ISO code') }}.
                                        <a href="https://stripe.com/docs/currencies"
                                            target="_blank">{{ __('You can find out how to do that here.') }}</a>
                                    </small>
                                    @error('site_currency')
                                    <br>
                                    <span class="text-xs text-danger invalid-site_currency"
                                        role="alert">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    {{ Form::label('site_currency_symbol', __('Currency Symbol'), ['class' => 'col-form-label']) }}<x-required></x-required>
                                    {{ Form::text('site_currency_symbol', null, ['class' => 'form-control', 'placeholder' => __('Enter Currency Symbol')]) }}
                                    @error('site_currency_symbol')
                                    <span class="text-xs text-danger invalid-site_currency_symbol"
                                        role="alert">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="col-form-label">{{ __('Currency Symbol Position') }}</label>
                                    <div class="form-check form-check">
                                        <input class="form-check-input" type="radio" id="pre" value="pre"
                                            name="site_currency_symbol_position"
                                            @if ($settings['site_currency_symbol_position']=='pre' ) checked @endif>
                                        <label class="form-check-label" for="pre">
                                            {{ __('Pre') }}
                                        </label>
                                    </div>
                                    <div class="form-check form-check">
                                        <input class="form-check-input" type="radio" id="post" value="post"
                                            name="site_currency_symbol_position"
                                            @if ($settings['site_currency_symbol_position']=='post' ) checked @endif>
                                        <label class="form-check-label" for="post">
                                            {{ __('Post') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="site_date_format"
                                        class="col-form-label">{{ __('Date Format') }}</label>
                                    <select type="text" name="site_date_format" class="form-control"
                                        id="site_date_format">

                                        <option value="M j, Y" {{ @$settings['site_date_format'] == 'M j, Y' ? 'selected' : '' }}>
                                            Jan 1, 2015
                                        </option>

                                        <option value="d M, Y" {{ @$settings['site_date_format'] == 'd M, Y' ? 'selected' : '' }}>
                                            1 Jan, 2015
                                        </option>

                                        <option value="d-m-Y" {{ @$settings['site_date_format'] == 'd-m-Y' ? 'selected' : '' }}>
                                            15-12-2015
                                        </option>

                                        <option value="d/m/Y" {{ @$settings['site_date_format'] == 'd/m/Y' ? 'selected' : '' }}>
                                            15/12/2015
                                        </option>

                                        <option value="m/d/Y" {{ @$settings['site_date_format'] == 'm/d/Y' ? 'selected' : '' }}>
                                            12/15/2015
                                        </option>

                                        <option value="Y-m-d" {{ @$settings['site_date_format'] == 'Y-m-d' ? 'selected' : '' }}>
                                            2015-01-01
                                        </option>

                                        <option value="d.m.Y" {{ @$settings['site_date_format'] == 'd.m.Y' ? 'selected' : '' }}>
                                            01.01.2015
                                        </option>

                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="site_time_format"
                                        class="col-form-label">{{ __('Time Format') }}</label>
                                    <select type="text" name="site_time_format" class="form-control"
                                        id="site_time_format">
                                        <option value="g:i A"
                                            @if (@$settings['site_time_format']=='g:i A' ) selected="selected" @endif>
                                            10:30 PM</option>
                                        {{-- <option value="g:i a"
                                                @if (@$settings['site_time_format'] == 'g:i a') selected="selected" @endif>
                                                10:30 pm</option> --}}
                                        <option value="H:i"
                                            @if (@$settings['site_time_format']=='H:i' ) selected="selected" @endif>
                                            22:30</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-4">
                                    {{-- {{Form::label('bug_prefix',__('Bug Prefix'),['class'=>'col-form-label']) }}
                                    {{Form::text('bug_prefix',null,array('class'=>'form-control'))}}
                                    @error('bug_prefix')
                                    <span class="text-xs text-danger invalid-bug_prefix" role="alert">{{ $message }}</span>
                                    @enderror --}}


                                    {{ Form::label('employee_prefix', __('Employee Prefix'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('employee_prefix', null, ['class' => 'form-control', 'placeholder' => __('Enter Employee Prefix')]) }}
                                    @error('employee_prefix')
                                    <span class="text-xs text-danger invalid-employee_prefix" role="alert">
                                        <small class="text-danger">{{ $message }}</small>
                                    </span>
                                    @enderror

                                </div>

                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button class="btn-submit btn btn-primary" type="submit">
                                {{ __('Save Changes') }}
                            </button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>

                <div class="" id="email-settings">
                    {{ Form::open(['route' => 'email.settings', 'method' => 'post']) }}
                    <div class="row">
                        <div class="col-lg-12 col-sm-12 col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>{{ __('Email Settings') }}</h5>
                                    <small
                                        class="text-muted">{{ __('This SMTP will be used for sending your company-level email. If this field is empty, then SuperAdmin SMTP will be used for sending emails.') }}</small>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                            {{ Form::label('mail_driver', __('Mail Driver'), ['class' => 'col-form-label mail_driver']) }}
                                            {{ Form::text('mail_driver', isset($settings['mail_driver']) ? $settings['mail_driver'] : '', ['class' => 'form-control', 'id' => 'mail_driver', 'placeholder' => __('Enter Mail Driver')]) }}
                                            @error('mail_driver')
                                            <span class="text-xs text-danger invalid-mail_driver"
                                                role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                            {{ Form::label('mail_host', __('Mail Host'), ['class' => 'col-form-label']) }}
                                            {{ Form::text('mail_host', isset($settings['mail_host']) ? $settings['mail_host'] : '', ['class' => 'form-control', 'id' => 'mail_host', 'placeholder' => __('Enter Mail Host')]) }}
                                            @error('mail_host')
                                            <span class="text-xs text-danger invalid-mail_host"
                                                role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                            {{ Form::label('mail_port', __('Mail Port'), ['class' => 'col-form-label']) }}
                                            {{ Form::text('mail_port', isset($settings['mail_port']) ? $settings['mail_port'] : '', ['class' => 'form-control', 'id' => 'mail_port', 'placeholder' => __('Enter Mail Port')]) }}
                                            @error('mail_port')
                                            <span class="text-xs text-danger invalid-mail_port"
                                                role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                            {{ Form::label('mail_username', __('Mail Username'), ['class' => 'col-form-label']) }}
                                            {{ Form::text('mail_username', isset($settings['mail_username']) ? $settings['mail_username'] : '', ['class' => 'form-control', 'id' => 'mail_username', 'placeholder' => __('Enter Mail Username')]) }}
                                            @error('mail_username')
                                            <span class="text-xs text-danger invalid-mail_username"
                                                role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                            {{ Form::label('mail_password', __('Mail Password'), ['class' => 'col-form-label']) }}
                                            <div class="position-relative">
                                                {{ Form::input('password', 'mail_password', isset($settings['mail_password']) ? $settings['mail_password'] : '', ['class' => 'form-control', 'id' => 'mail_password', 'placeholder' => __('Enter Mail Password')]) }}
                                                <span class="toggle-password-btn">
                                                    <i class="ti ti-eye"></i>
                                                </span>
                                            </div>
                                            @error('mail_password')
                                            <span class="text-xs text-danger invalid-mail_password"
                                                role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                            {{ Form::label('mail_encryption', __('Mail Encryption'), ['class' => 'col-form-label']) }}
                                            {{ Form::text('mail_encryption', isset($settings['mail_encryption']) ? $settings['mail_encryption'] : '', ['class' => 'form-control', 'id' => 'mail_encryption', 'placeholder' => __('Enter Mail Encryption')]) }}
                                            @error('mail_encryption')
                                            <span class="text-xs text-danger invalid-mail_encryption"
                                                role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                            {{ Form::label('mail_from_address', __('Mail From Address'), ['class' => 'col-form-label']) }}
                                            {{ Form::text('mail_from_address', isset($settings['mail_from_address']) ? $settings['mail_from_address'] : '', ['class' => 'form-control', 'id' => 'mail_from_address', 'placeholder' => __('Enter Mail From Address')]) }}
                                            @error('mail_from_address')
                                            <span class="text-xs text-danger invalid-mail_from_address"
                                                role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                            {{ Form::label('mail_from_name', __('Mail From Name'), ['class' => 'col-form-label']) }}
                                            {{ Form::text('mail_from_name', isset($settings['mail_from_name']) ? $settings['mail_from_name'] : '', ['class' => 'form-control', 'id' => 'mail_from_name', 'placeholder' => __('Enter Mail From Name')]) }}
                                            @error('mail_from_name')
                                            <span class="text-xs text-danger invalid-mail_from_name"
                                                role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <a href="javascript:void(0)"
                                                class="btn btn-print-invoice  btn-primary m-r-10 send_email"
                                                data-title="{{ __('Send Test Mail') }}"
                                                data-url="{{ route('test.mail') }}">
                                                {{ __('Send Test Mail') }}
                                            </a>

                                        </div>
                                        <div class="text-end col-md-6">
                                            {{ Form::submit(__('Save Changes'), ['class' => 'btn btn-xs btn-primary']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>

                <div class="" id="company-settings">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ __('Company Settings') }}</h5>
                        </div>
                        {{ Form::model($settings, ['route' => 'company.settings', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                        <div class="card-body">

                            <div class="row">
                                <div class="form-group col-md-4">
                                    {{ Form::label('company_name', __('Company Name'), ['class' => 'col-form-label']) }}<x-required></x-required>
                                    {{ Form::text('company_name', null, ['class' => 'form-control ', 'placeholder' => __('Enter Company Name'), 'required' => 'required']) }}

                                    @error('company_name')
                                    <span class="invalid-company_name" role="alert">
                                        <strong class="text-danger">{{ $message }}</strong>
                                    </span>
                                    @enderror

                                </div>
                                <div class="form-group col-md-4">
                                    {{ Form::label('company_address', __('Address'), ['class' => 'col-form-label']) }}<x-required></x-required>
                                    {{ Form::text('company_address', null, ['class' => 'form-control ', 'placeholder' => __('Enter Address'), 'required' => 'required']) }}
                                    @error('company_address')
                                    <span class="invalid-company_address" role="alert">
                                        <strong class="text-danger">{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    {{ Form::label('company_city', __('City'), ['class' => 'col-form-label']) }}<x-required></x-required>
                                    {{ Form::text('company_city', null, ['class' => 'form-control ', 'placeholder' => __('Enter City'), 'required' => 'required']) }}
                                    @error('company_city')
                                    <span class="invalid-company_city" role="alert">
                                        <strong class="text-danger">{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    {{ Form::label('company_state', __('State'), ['class' => 'col-form-label']) }}<x-required></x-required>
                                    {{ Form::text('company_state', null, ['class' => 'form-control ', 'placeholder' => __('Enter State'), 'required' => 'required']) }}
                                    @error('company_state')
                                    <span class="invalid-company_state" role="alert">
                                        <strong class="text-danger">{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    {{ Form::label('company_zipcode', __('Zip/Post Code'), ['class' => 'col-form-label']) }}<x-required></x-required>
                                    {{ Form::text('company_zipcode', null, ['class' => 'form-control', 'placeholder' => __('Enter Zip/Post Code'), 'required' => 'required']) }}
                                    @error('company_zipcode')
                                    <span class="invalid-company_zipcode" role="alert">
                                        <strong class="text-danger">{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    {{ Form::label('company_country', __('Country'), ['class' => 'col-form-label']) }}<x-required></x-required>
                                    {{ Form::text('company_country', null, ['class' => 'form-control', 'placeholder' => __('Enter Country'), 'required' => 'required']) }}
                                    @error('company_country')
                                    <span class="invalid-company_country" role="alert"><strong
                                            class="text-danger">{{ $message }}</strong></span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    {{ Form::label('company_telephone', __('Telephone'), ['class' => 'col-form-label']) }}<x-required></x-required>
                                    {{ Form::text('company_telephone', null, ['class' => 'form-control', 'placeholder' => __('Enter Telephone'), 'required' => 'required']) }}
                                    @error('company_telephone')
                                    <span class="invalid-company_telephone" role="alert"><strong
                                            class="text-danger">{{ $message }}</strong></span>
                                    @enderror
                                </div>
                                {{-- <div class="form-group col-md-4">
                                        {{ Form::label('company_email', __('System Email'), ['class' => 'col-form-label']) }}
                                        {{ Form::text('company_email', null, ['class' => 'form-control', 'placeholder' => 'Enter System Email']) }}
                                        @error('company_email')
                                        <span class="invalid-company_email" role="alert"><strong
                                                class="text-danger">{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        {{ Form::label('company_email_from_name', __('Email (From Name)'), ['class' => 'col-form-label']) }}
                                        {{ Form::text('company_email_from_name', null, ['class' => 'form-control ', 'placeholder' => 'Enter Email']) }}
                                        @error('company_email_from_name')
                                        <span class="invalid-company_email_from_name" role="alert"><strong
                                                class="text-danger">{{ $message }}</strong></span>
                                        @enderror
                                    </div> --}}


                                <div class="form-group col-md-4">
                                    {{ Form::label('defult_country', __('Based Country Setting'), ['class' => 'col-form-label']) }}
                                    <select name="defult_country" id="defult_country" class="form-control select2">
                                        @php
                                            $currentCountry = strtolower($settings['defult_country'] ?? 'india');
                                            $countryList    = \App\Models\Utility::getCountryList();
                                        @endphp
                                        @foreach($countryList as $cKey => $cName)
                                            <option value="{{ $cKey }}" {{ ($currentCountry == $cKey || $currentCountry == strtolower($cName)) ? 'selected' : '' }}>
                                                {{ __($cName) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('defult_country')
                                    <span class="invalid-defult_country" role="alert">
                                        <small class="text-danger">{{ $message }}</small>
                                    </span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-4">
                                    {{ Form::label('timezone', __('Timezone'), ['class' => 'col-form-label']) }}<x-required></x-required>
                                    <select type="text" name="timezone" class="form-control select2"
                                        id="timezone" required>
                                        <option value="">{{ __('Select Timezone') }}</option>
                                        @if (!empty($timezones))
                                        @foreach ($timezones as $k => $timezone)
                                        <option value="{{ $k }}"
                                            {{ $settings['timezone'] == $k ? 'selected' : '' }}>
                                            {{ $timezone }}
                                        </option>
                                        @endforeach
                                        @endif
                                    </select>
                                    @error('timezone')
                                    <span class="invalid-timezone" role="alert">
                                        <small class="text-danger">{{ $message }}</small>
                                    </span>
                                    @enderror

                                </div>

                                <div class="form-group col-md-4">
                                    <div class="form-check form-switch" style="margin:35px 0 7px 0;">
                                        <input class="form-check-input" type="checkbox" id="shift_change"
                                            name="shift_change"
                                            {{ $settings['shift_change'] == 'on' ? 'checked="checked"' : '' }}>
                                        <label class="form-check-label" for="shift_change">Enable Auto Shift Turner?</label>
                                    </div>
                                    <div class="form-group col-md-6" id="shift_turner" style="margin-left: 34px;"
                                        @if ($settings['shift_change']=='off' ) style="display:none" @endif>
                                        {{-- {{ Form::label('shift_turner', __('Shift Turner'), ['class' => 'col-form-label']) }} --}}
                                        {{ Form::select('shift_turner', $shift_turner, $settings['shift_turner'], ['class' => 'form-control select2']) }}
                                    </div>
                                </div>

                                <div class="form-group col-md-4">
                                    {{ Form::label('working_hours_per_day', __('Working Hours Per Day'), ['class' => 'col-form-label']) }}
                                    {{ Form::number('working_hours_per_day', old('working_hours_per_day', $settings['working_hours_per_day'] ?? 8), ['class' => 'form-control', 'min' => '1', 'max' => '24', 'step' => '0.5', 'placeholder' => '8']) }}
                                    <small class="text-muted">
                                        {{ __('Used to calculate per-hour salary rate. If set, this overrides the shift start/end time calculation.') }}
                                    </small>
                                    @error('working_hours_per_day')
                                        <span class="text-danger d-block mt-1"><small>{{ $message }}</small></span>
                                    @enderror
                                </div>
                            </div>
                            {{-- <div class="col-md-12">
                                <div class="row">
                                    @for ($i = 0; $i < 4; $i++)
                                        <div class="form-group col-md-3">
                                            <label class="col-form-label" for="{{ $i == 0 ? 'company_start_time' : "company_start_time$i" }}">
                                                {{ __('Shift Start Time') }}{{ $i == 0 ? '' : ' '.($i+1) }}
                                                <x-required></x-required>
                                            </label>
                                            {{ Form::time(
                                                            $i == 0 ? "company_start_time" : "company_start_time$i",
                                                            old($i == 0 ? "company_start_time" : "company_start_time$i", $settings[$i == 0 ? "company_start_time" : "company_start_time$i"] ?? ''),
                                                            [
                                                                'class' => 'form-control timepicker_format company-time',
                                                                'data-index' => $i,
                                                                'disabled' => $i > 0 ? 'disabled' : null,
                                                            ]
                                                        ) }}
                                            @error($i == 0 ? "company_start_time" : "company_start_time$i")
                                            <span class="invalid-company_start_time" role="alert">
                                                <small class="text-danger">{{ $message }}</small>
                                            </span>
                                            @enderror
                                </div>

                                <div class="form-group col-md-3">
                                    <label class="col-form-label" for="{{ $i == 0 ? 'company_end_time' : "company_end_time$i" }}">
                                        {{ __('Shift End Time') }}{{ $i == 0 ? '' : ' '.($i+1) }}
                                        <x-required></x-required>
                                    </label>
                                    {{ Form::time(
                                                        $i == 0 ? "company_end_time" : "company_end_time$i",
                                                        old($i == 0 ? "company_end_time" : "company_end_time$i", $settings[$i == 0 ? "company_end_time" : "company_end_time$i"] ?? ''),
                                                        [
                                                            'class' => 'form-control timepicker_format company-time',
                                                            'data-index' => $i,
                                                            'disabled' => $i > 0 ? 'disabled' : null,
                                                        ]
                                                    ) }}
                                    @error($i == 0 ? "company_end_time" : "company_end_time$i")
                                    <span class="invalid-company_end_time" role="alert">
                                        <small class="text-danger">{{ $message }}</small>
                                    </span>
                                    @enderror
                                </div>
                                @endfor
                            </div> --}}
                            <div class="container mt-4">

                                <!-- Toggle + Add -->
                                <div class="d-flex justify-content-between mb-5 align-items-center">
                                    <div class="form-check form-switch">
                                        <label class="form-check-label" for="toggleShift">Create Shift</label>
                                        <input class="form-check-input" type="checkbox" id="toggleShift" onchange="toggleTable()" {{ !empty($company_shifts) ? 'checked' : '' }}>
                                    </div>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="addRow()">+ Add Shift</button>
                                </div>

                                <!-- Table -->
                                <div id="shiftTableWrapper" style="display:none;">
                                    <div class="table-responsive">
                                        <table class="table table-bordered text-center align-middle shift-table">
                                            <thead class="table-light">
                                                <tr>
                                                    <th colspan="3">Shift</th>
                                                    <th colspan="2">Time</th>
                                                    <th rowspan="2">Break Type</th>
                                                    <th colspan="2">Lunch</th>
                                                    <th colspan="2">Tea</th>
                                                    <th rowspan="2">Show in Rota</th>
                                                    <th rowspan="2">Action</th>
                                                </tr>
                                                <tr>
                                                    <th>Name *</th>
                                                    <th>Code</th>
                                                    <th>Color</th>
                                                    <th>Start</th>
                                                    <th>End</th>
                                                    <th>Start</th>
                                                    <th>End</th>
                                                    <th>Start</th>
                                                    <th>End</th>
                                                </tr>
                                            </thead>
                                           <tbody id="shiftBody">

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- ── Working Days ─────────────────────────────── --}}
                            <div class="row col-12 mt-3">
                                <div class="d-flex align-items-center mb-2 gap-2">
                                    <label class="col-form-label fw-semibold mb-0">{{ __('Rota Working Days') }}</label>
                                    <small class="text-muted">{{ __('Toggle off days that are non-working (e.g. Sunday). Shifts will not appear in the rota on disabled days.') }}</small>
                                </div>
                                @php
                                    $workingDays = array_map('trim', explode(',', $settings['rota_working_days'] ?? '1,2,3,4,5'));
                                    $dayLabels   = [0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];
                                @endphp
                                <div class="d-flex flex-wrap gap-4 mt-2">
                                    @foreach ($dayLabels as $dayNum => $dayName)
                                    <div class="form-check form-switch">
                                        <input class="form-check-input working-day-check"
                                               type="checkbox"
                                               name="rota_working_days[]"
                                               value="{{ $dayNum }}"
                                               id="wd_{{ $dayNum }}"
                                               style="cursor:pointer;"
                                               {{ in_array((string)$dayNum, $workingDays) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="wd_{{ $dayNum }}" style="cursor:pointer;">
                                            {{ __($dayName) }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                                {{-- Hidden sentinel so an empty selection (all off) still submits --}}
                                <input type="hidden" name="rota_working_days_submitted" value="1">

                                {{-- ── Saturday Pattern ────────────────────────────────── --}}
                                <div class="mt-3" id="saturday_pattern_row" style="{{ in_array('6', $workingDays) ? '' : 'display:none;' }}">
                                    <label class="col-form-label fw-semibold mb-1">{{ __('Saturday  Pattern') }}</label>
                                    <small class="text-muted d-block mb-2">{{ __('Choose which Saturdays are working days when Saturday is enabled above.') }}</small>
                                    @php $satPattern = $settings['saturday_pattern'] ?? 'none'; @endphp
                                    <select name="saturday_pattern" class="form-control" style="max-width:300px;">
                                        <option value="all"  {{ $satPattern === 'all'  ? 'selected' : '' }}>{{ __('Every Saturday (all working)') }}</option>
                                        <option value="odd"  {{ $satPattern === 'odd'  ? 'selected' : '' }}>{{ __('Odd Saturdays only (1st, 3rd, 5th)') }}</option>
                                        <option value="even" {{ $satPattern === 'even' ? 'selected' : '' }}>{{ __('Even Saturdays only (2nd, 4th)') }}</option>
                                    </select>
                                </div>

                                {{-- ── Salary Calculation Basis ─────────────────────────── --}}
                                <div class="mt-3" id="salary_day_calculation_row">
                                    <label class="col-form-label fw-semibold mb-1">{{ __('Salary Calculation Basis') }}</label>
                                    <small class="text-muted d-block mb-2">{{ __('Choose whether per-day salary rate and total monthly calculation is based on Working Days or Month Wise (31 calendar days).') }}</small>
                                    @php $salaryDayCalc = $settings['salary_day_calculation'] ?? 'working_days'; @endphp
                                    <select name="salary_day_calculation" class="form-control" style="max-width:350px;">
                                        <option value="working_days" {{ in_array($salaryDayCalc, ['working_days', 'working_day_wise']) ? 'selected' : '' }}>{{ __('Working Day Wise') }}</option>
                                        <option value="month_wise"    {{ in_array($salaryDayCalc, ['month_wise', 'calendar_month']) ? 'selected' : '' }}>{{ __('Month Wise') }}</option>
                                    </select>
                                </div>

                                {{-- Also handle Sunday pattern if Sunday is a working day ── --}}
                                {{-- (extensible: same concept applies to any "alternate" day) --}}
                            </div>

                            @push('script-page')
                            <script>
                                // Show/hide Saturday pattern row based on Sat checkbox & Auto Clock Out row
                                document.addEventListener('DOMContentLoaded', function () {
                                    var satCheck = document.getElementById('wd_6');
                                    var patRow   = document.getElementById('saturday_pattern_row');
                                    if (satCheck && patRow) {
                                        satCheck.addEventListener('change', function () {
                                            patRow.style.display = this.checked ? '' : 'none';
                                        });
                                    }

                                    var autoClockOutCheck = document.getElementById('auto_clock_out');
                                    var autoClockOutRow   = document.getElementById('auto_clock_out_minutes_row');
                                    if (autoClockOutCheck && autoClockOutRow) {
                                        autoClockOutCheck.addEventListener('change', function () {
                                            autoClockOutRow.style.display = this.checked ? '' : 'none';
                                        });
                                    }
                                });
                            </script>
                            @endpush

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <b>{{ Form::label('login_early_time', __('Login Early Time (min)'), ['class' => 'col-form-label']) }}</b>
                                            {{ Form::number('login_early_time', old('login_early_time', $settings['login_early_time'] ?? 0), ['class' => 'form-control', 'min' => '0']) }}
                                            @error('login_early_time')
                                            <span class="invalid-login_early_time" role="alert">
                                                <small class="text-danger">{{ $message }}</small>
                                            </span>
                                            @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <b>{{ Form::label('login_deley_min', __('Login Delay Time (min)'), ['class' => 'col-form-label']) }}</b>
                                            {{ Form::number('login_deley_min', old('login_deley_min', $settings['login_deley_min'] ?? 0), ['class' => 'form-control', 'min' => '0']) }}
                                            @error('login_deley_min')
                                            <span class="invalid-login_deley_min" role="alert">
                                                <small class="text-danger">{{ $message }}</small>
                                            </span>
                                            @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <b>{{ Form::label('logout_lead_time', __('Logout Lead Time (Min)'), ['class' => 'col-form-label']) }}</b>
                                            {{ Form::number('logout_lead_time', old('logout_lead_time', $settings['logout_lead_time'] ?? 0), ['class' => 'form-control', 'min' => '0']) }}
                                            @error('logout_lead_time')
                                            <span class="invalid-logout_lead_time" role="alert">
                                                <small class="text-danger">{{ $message }}</small>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ── Auto Clock Out Settings ────────────────────────────────────── --}}
                            <div class="col-12 mt-4 pt-3 border-top">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div>
                                        <label class="col-form-label fw-semibold mb-0">{{ __('Auto Clock Out') }}</label>
                                        <small class="text-muted d-block">{{ __('Enable to automatically clock out employees after shift end time.') }}</small>
                                    </div>
                                    <div class="form-check form-switch custom-switch-v1">
                                        <input type="hidden" name="auto_clock_out" value="off">
                                        <input class="form-check-input input-primary"
                                               type="checkbox"
                                               name="auto_clock_out"
                                               id="auto_clock_out"
                                               value="on"
                                               style="cursor:pointer; width: 44px; height: 22px;"
                                               {{ ($settings['auto_clock_out'] ?? 'off') == 'on' ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="mt-3 p-3 bg-light rounded border" id="auto_clock_out_minutes_row" style="{{ ($settings['auto_clock_out'] ?? 'off') == 'on' ? '' : 'display:none;' }}">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <div>
                                            <label class="col-form-label fw-semibold mb-0">{{ __('Auto Clock Out After (Minutes)') }}</label>
                                            <small class="text-muted d-block">{{ __('Automatically clock out employee after this many minutes from shift end time if not clocked out.') }}</small>
                                        </div>
                                        <div style="width: 150px;">
                                            <div class="input-group">
                                                <input type="number"
                                                       name="auto_clock_out_time"
                                                       id="auto_clock_out_time"
                                                       class="form-control text-center"
                                                       value="{{ old('auto_clock_out_time', $settings['auto_clock_out_time'] ?? 30) }}"
                                                       min="0"
                                                       step="1"
                                                       placeholder="30">
                                                <span class="input-group-text bg-white text-muted">min</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- Stamp upload moved to Salary Slip Settings section --}}
                        </div>

                        <div class="card-footer text-end">
                            <button class="btn-submit btn btn-primary" id="addSig" type="submit">
                                {{ __('Save Changes') }}
                            </button>
                        </div>
                        {{ Form::close() }}



                    </div>
                </div>

            {{-- </div>
        </div> --}}

        {{-- <div id="email-notification-settings" class="card">
                        @foreach ($EmailTemplates as $EmailTemplate)
                            {{ Form::model($settings, ['route' => ['company.email.setting', $EmailTemplate->id], 'method' => 'get']) }}
        @csrf
        @endforeach
        <div class="col-md-12">
            <div class="card-header">
                <div class="row">
                    <div class="col-lg-8 col-md-8 col-sm-8">
                        <h5>{{ __('Email Notification Settings') }}</h5>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    @foreach ($EmailTemplates as $EmailTemplate)
                    <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                        <div class="list-group">
                            <div class="list-group-item form-switch form-switch-right">
                                <label class="form-label"
                                    style="margin-left:5%;">{{ $EmailTemplate->name }}</label>

                                <input class="form-check-input" name='{{ $EmailTemplate->id }}'
                                    id="email_tempalte_{{ $EmailTemplate->template->id }}"
                                    type="checkbox"
                                    @if ($EmailTemplate->template->is_active == 1) checked="checked" @endif
                                type="checkbox" value="1"
                                data-url="{{ route('company.email.setting', [$EmailTemplate->template->id]) }}" />
                                <label class="form-check-label"
                                    for="email_tempalte_{{ $EmailTemplate->template->id }}"></label>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="card-footer p-0">
                    <div class="col-sm-12 mt-3 px-2">
                        <div class="text-end">
                            <input class="btn btn-print-invoice  btn-primary " type="submit"
                                value="{{ __('Save Changes') }}">
                        </div>
                    </div>

                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div> --}}

    <!--Email Notification Setting-->
    <div id="email-notification-settings" class="card">

        {{ Form::model($settings, ['route' => ['company.email.setting'], 'method' => 'post']) }}
        @csrf
        <div class="col-md-12">
            <div class="card-header">
                <div class="row">
                    <div class="col-lg-8 col-md-8 col-sm-8">
                        <h5>{{ __('Email Notification Settings') }}</h5>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <!-- <div class=""> -->
                    @foreach ($EmailTemplates as $EmailTemplate)
                    <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                        <div class="list-group">
                            <div class="list-group-item form-switch form-switch-right">
                                <label class="form-label"
                                    style="margin-left:5%;">{{ $EmailTemplate->name }}</label>

                                <input class="form-check-input" name='{{ $EmailTemplate->id }}'
                                    id="email_tempalte_{{ $EmailTemplate->template->id }}"
                                    type="checkbox"
                                    @if ($EmailTemplate->template->is_active == 1) checked="checked" @endif
                                type="checkbox" value="1"
                                data-url="{{ route('company.email.setting', [$EmailTemplate->template->id]) }}" />
                                <label class="form-check-label"
                                    for="email_tempalte_{{ $EmailTemplate->template->id }}"></label>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    <!-- </div> -->
                </div>
            </div>
            <div class="card-footer text-end">
                <button class="btn-submit btn btn-primary" type="submit">
                    {{ __('Save Changes') }}
                </button>
            </div>
        </div>
        {{ Form::close() }}
    </div>

    <div class="" id="ip-restriction-settings">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 me-3">{{ __('IP Access Control') }}</h5>
                <div class="d-flex  align-items-center">
                    <div>
                    {{-- Form wraps only the switch --}}
                    {{ Form::model($settings, ['route' => 'company.settings', 'method' => 'post', 'id' => 'ipRestrictForm']) }}
                    <div class="form-check form-switch" style="min-height: 0;">
                        <input type="hidden" name="ip_restrict_only" value="1">
                        <input type="checkbox" class="form-check-input" name="ip_restrict" id="ip_restrict"
                            {{ $settings['ip_restrict'] == 'on' ? 'checked' : '' }}
                            onchange="document.getElementById('ipRestrictForm').submit();">
                        <label class="form-check-label" for="ip_restrict"></label>
                    </div>
                    {{ Form::close() }}
                    </div>
                    {{-- Create new IP button --}}
                    <a data-url="{{ route('create.ip') }}" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"
                        data-bs-placement="top" data-size="md" data-ajax-popup="true"
                        data-title="{{ __('Add New IP') }}">
                        <i class="ti ti-plus"></i>
                    </a>
                </div>


            </div>
            <div class="card-body table-border-style ">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th class="w-75"> {{ __('Allowed IP Address') }}</th>
                                <th width="200px"> {{ 'Action' }}</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($ips as $ip)
                            <tr class="Action">
                                <td class="sorting_1">{{ $ip->ip }}</td>
                                <td class="">
                                    @can('Manage Company Settings')
                                    <div class="action-btn me-2">
                                        <a class="mx-3 btn btn-sm bg-info align-items-center"
                                            data-url="{{ route('edit.ip', $ip->id) }}"
                                            data-size="md" data-ajax-popup="true"
                                            data-title="{{ __('Edit IP') }}"
                                            data-bs-toggle="tooltip"
                                            data-bs-original-title="{{ __('Edit') }}"
                                            data-bs-placement="top" class="edit-icon"
                                            data-original-title="{{ __('Edit') }}"><span
                                                class="text-white"><i
                                                    class="ti ti-pencil"></i></span></a>
                                    </div>
                                    @endcan
                                    @can('Manage Company Settings')
                                    <div class="action-btn">
                                        {!! Form::open(['method' => 'DELETE', 'route' => ['destroy.ip', $ip->id], 'id' => 'delete-form-' . $ip->id]) !!}
                                        <a href="javascript:void(0)" data-bs-toggle="tooltip"
                                            data-bs-original-title="{{ __('Delete') }}"
                                            data-bs-placement="top"
                                            class="mx-3 btn btn-sm bg-danger align-items-center bs-pass-para"
                                            data-bs-toggle="tooltip" data-bs-placement="bottom"
                                            title="{{ __('Delete') }}">
                                            <span class="text-white"><i
                                                    class="ti ti-trash"></i></span></a>
                                        {!! Form::close() !!}
                                    </div>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>


            </div>
        </div>
    </div>


    @if (Auth::user()->type == 'company')
    <div class="" id="zoom-meeting-settings">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Zoom Meeting Settings') }}</h5>
            </div>
            {{ Form::open(['route' => 'zoom.settings', 'method' => 'post']) }}
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                        {{ Form::label('zoom_account_id', __('Zoom Account ID'), ['class' => 'col-form-label']) }}
                        {{ Form::text('zoom_account_id', isset($settings['zoom_account_id']) ? $settings['zoom_account_id'] : '', ['class' => 'form-control ', 'placeholder' => __('Enter Zoom Account ID')]) }}
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                        {{ Form::label('zoom_client_id', __('Zoom Client ID'), ['class' => 'col-form-label']) }}
                        {{ Form::text('zoom_client_id', isset($settings['zoom_client_id']) ? $settings['zoom_client_id'] : '', ['class' => 'form-control ', 'placeholder' => __('Enter Zoom Client ID')]) }}
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                        {{ Form::label('zoom_client_secret', __('Zoom Client Secret Key'), ['class' => 'col-form-label']) }}
                        {{ Form::text('zoom_client_secret', isset($settings['zoom_client_secret']) ? $settings['zoom_client_secret'] : '', ['class' => 'form-control ', 'placeholder' => __('Enter Zoom Client Secret Key')]) }}
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                <button class="btn-submit btn btn-primary" type="submit">
                    {{ __('Save Changes') }}
                </button>
            </div>
            {{ Form::close() }}
        </div>
    </div>

    <div class="" id="slack-settings">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Slack Settings') }}</h5>
                <small
                    class="text-secondary font-weight-bold">{{ __('Slack Notification Settings') }}</small>
            </div>
            {{ Form::open(['route' => 'slack.setting', 'id' => 'slack-setting', 'method' => 'post', 'class' => 'd-contents']) }}
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                        {{ Form::label('Slack Webhook URL', __('Slack Webhook URL'), ['class' => 'col-form-label']) }}<x-required></x-required>
                        {{ Form::text('slack_webhook', isset($settings['slack_webhook']) ? $settings['slack_webhook'] : '', ['class' => 'form-control w-100', 'placeholder' => __('Enter Slack Webhook URL'), 'required' => 'required']) }}
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 form-group mb-3">
                        {{-- {{ Form::label('Module Setting', __('Module Setting'), ['class' => 'col-form-label']) }} --}}
                    </div>
                    <div class="col-md-4">
                        <ul class="list-group">
                            <li
                                class="list-group-item d-flex align-items-center justify-content-between">
                                {{ Form::label('Monthly payslip create', __('New Monthly Payslip'), ['class' => 'col-form-label']) }}
                                <div class="form-check form-switch d-inline-block float-right">
                                    {{ Form::checkbox('monthly_payslip_notification', '1', isset($settings['monthly_payslip_notification']) && $settings['monthly_payslip_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'monthly_payslip_notification']) }}
                                    <label class="col-form-label"
                                        for="monthly_payslip_notification"></label>
                                </div>
                            </li>

                            <li
                                class="list-group-item d-flex align-items-center justify-content-between">
                                {{ Form::label('Award create', __('New Award'), ['class' => 'col-form-label']) }}
                                <div class="form-check form-switch d-inline-block float-right">
                                    {{ Form::checkbox('award_notification', '1', isset($settings['award_notification']) && $settings['award_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'award_notification']) }}
                                    <label class="col-form-label" for="award_notification"></label>
                                </div>
                            </li>

                            <li
                                class="list-group-item d-flex align-items-center justify-content-between">
                                {{ Form::label('Ticket create', __('New Ticket'), ['class' => 'col-form-label']) }}
                                <div class="form-check form-switch d-inline-block float-right">
                                    {{ Form::checkbox('ticket_notification', '1', isset($settings['ticket_notification']) && $settings['ticket_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'ticket_notification']) }}
                                    <label class="col-form-label" for="ticket_notification"></label>
                                </div>
                            </li>


                        </ul>
                    </div>

                    <div class="col-md-4">
                        <ul class="list-group">
                            <li
                                class="list-group-item d-flex align-items-center justify-content-between">
                                {{ Form::label('Announcement create', __('New Announcement'), ['class' => 'col-form-label']) }}
                                <div class="form-check form-switch d-inline-block float-right">

                                    {{ Form::checkbox('Announcement_notification', '1', isset($settings['Announcement_notification']) && $settings['Announcement_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'Announcement_notification']) }}
                                    <label class="col-form-label"
                                        for="Announcement_notification"></label>
                                </div>
                            </li>

                            <li
                                class="list-group-item d-flex align-items-center justify-content-between">
                                {{ Form::label('Holidays create', __('New Holidays'), ['class' => 'col-form-label']) }}
                                <div class="form-check form-switch d-inline-block float-right">
                                    {{ Form::checkbox('Holiday_notification', '1', isset($settings['Holiday_notification']) && $settings['Holiday_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'Holiday_notification']) }}
                                    <label class="col-form-label" for="Holiday_notification"></label>
                                </div>
                            </li>

                            <li
                                class="list-group-item d-flex align-items-center justify-content-between">
                                {{ Form::label('Event create', __('New Event'), ['class' => 'col-form-label']) }}
                                <div class="form-check form-switch d-inline-block float-right">
                                    {{ Form::checkbox('event_notification', '1', isset($settings['event_notification']) && $settings['event_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'event_notification']) }}
                                    <label class="col-form-label" for="event_notification"></label>
                                </div>
                            </li>


                        </ul>
                    </div>

                    <div class="col-md-4">
                        <ul class="list-group">
                            <li
                                class="list-group-item d-flex align-items-center justify-content-between">
                                {{ Form::label('Meeting create', __('New Meeting'), ['class' => 'col-form-label']) }}
                                <div class="form-check form-switch d-inline-block float-right">
                                    {{ Form::checkbox('meeting_notification', '1', isset($settings['meeting_notification']) && $settings['meeting_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'meeting_notification']) }}
                                    <label class="col-form-label" for="meeting_notification"></label>
                                </div>
                            </li>

                            <li
                                class="list-group-item d-flex align-items-center justify-content-between">
                                {{ Form::label('Company policy create', __('New Company Policy'), ['class' => 'col-form-label']) }}
                                <div class="form-check form-switch d-inline-block float-right">
                                    {{ Form::checkbox('company_policy_notification', '1', isset($settings['company_policy_notification']) && $settings['company_policy_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'company_policy_notification']) }}
                                    <label class="col-form-label"
                                        for="company_policy_notification"></label>
                                </div>
                            </li>

                            <li
                                class="list-group-item d-flex align-items-center justify-content-between">
                                {{ Form::label('Contract create', __('New Contract'), ['class' => 'col-form-label']) }}
                                <div class="form-check form-switch d-inline-block float-right">
                                    {{ Form::checkbox('contract_notification', '1', isset($settings['contract_notification']) && $settings['contract_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'contract_notification']) }}
                                    <label class="col-form-label" for="contract_notification"></label>
                                </div>
                            </li>

                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                <button class="btn-submit btn btn-primary" type="submit">
                    {{ __('Save Changes') }}
                </button>
            </div>
            {{ Form::close() }}
        </div>
    </div>



    <div class="" id="telegram-settings">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Telegram Settings') }}</h5>
                <small
                    class="text-secondary font-weight-bold">{{ __('Telegram Notification Settings') }}</small>
            </div>
            {{ Form::open(['route' => 'telegram.setting', 'id' => 'telegram-setting', 'method' => 'post', 'class' => 'd-contents']) }}
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 form-group">
                        {{ Form::label('Telegram Access Token', __('Telegram Access Token'), ['class' => 'col-form-label']) }}<x-required></x-required>
                        {{ Form::text('telegram_accestoken', isset($settings['telegram_accestoken']) ? $settings['telegram_accestoken'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Telegram AccessToken'), 'required' => 'required']) }}
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 form-group">
                        {{ Form::label('Telegram ChatID', __('Telegram ChatID'), ['class' => 'col-form-label']) }}<x-required></x-required>
                        {{ Form::text('telegram_chatid', isset($settings['telegram_chatid']) ? $settings['telegram_chatid'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Telegram ChatID'), 'required' => 'required']) }}
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 form-group mb-3">
                        {{-- {{ Form::label('Module Setting', __('Module Setting'), ['class' => 'col-form-label']) }} --}}
                    </div>


                    <div class="col-md-4">
                        <ul class="list-group">
                            <li
                                class="list-group-item d-flex align-items-center justify-content-between">
                                {{ Form::label('Monthly payslip create', __('New Monthly Payslip'), ['class' => 'col-form-label']) }}
                                <div class="form-check form-switch d-inline-block float-right">
                                    {{ Form::checkbox('telegram_monthly_payslip_notification', '1', isset($settings['telegram_monthly_payslip_notification']) && $settings['telegram_monthly_payslip_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_monthly_payslip_notification']) }}
                                    <label class="col-form-label"
                                        for="telegram_monthly_payslip_notification"></label>
                                </div>
                            </li>

                            <li
                                class="list-group-item d-flex align-items-center justify-content-between">
                                {{ Form::label('Award create', __('New Award'), ['class' => 'col-form-label']) }}
                                <div class="form-check form-switch d-inline-block float-right">
                                    {{ Form::checkbox('telegram_award_notification', '1', isset($settings['telegram_award_notification']) && $settings['telegram_award_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_award_notification']) }}
                                    <label class="col-form-label"
                                        for="telegram_award_notification"></label>
                                </div>
                            </li>

                            <li
                                class="list-group-item d-flex align-items-center justify-content-between">
                                {{ Form::label('Ticket create', __('New Ticket '), ['class' => 'col-form-label']) }}
                                <div class="form-check form-switch d-inline-block float-right">
                                    {{ Form::checkbox('telegram_ticket_notification', '1', isset($settings['telegram_ticket_notification']) && $settings['telegram_ticket_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_ticket_notification']) }}
                                    <label class="col-form-label"
                                        for="telegram_ticket_notification"></label>
                                </div>
                            </li>


                        </ul>
                    </div>

                    <div class="col-md-4">
                        <ul class="list-group">
                            <li
                                class="list-group-item d-flex align-items-center justify-content-between">
                                {{ Form::label('Announcement create', __('New Announcement'), ['class' => 'col-form-label']) }}
                                <div class="form-check form-switch d-inline-block float-right">
                                    {{ Form::checkbox('telegram_Announcement_notification', '1', isset($settings['telegram_Announcement_notification']) && $settings['telegram_Announcement_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_Announcement_notification']) }}
                                    <label class="col-form-label"
                                        for="telegram_Announcement_notification"></label>
                                </div>
                            </li>

                            <li
                                class="list-group-item d-flex align-items-center justify-content-between">
                                {{ Form::label('Holidays create', __('New Holidays '), ['class' => 'col-form-label']) }}
                                <div class="form-check form-switch d-inline-block float-right">
                                    {{ Form::checkbox('telegram_Holiday_notification', '1', isset($settings['telegram_Holiday_notification']) && $settings['telegram_Holiday_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_Holiday_notification']) }}
                                    <label class="col-form-label"
                                        for="telegram_Holiday_notification"></label>
                                </div>
                            </li>

                            <li
                                class="list-group-item d-flex align-items-center justify-content-between">
                                {{ Form::label('Event create', __('New Event'), ['class' => 'col-form-label']) }}
                                <div class="form-check form-switch d-inline-block float-right">
                                    {{ Form::checkbox('telegram_event_notification', '1', isset($settings['telegram_event_notification']) && $settings['telegram_event_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_event_notification']) }}
                                    <label class="col-form-label"
                                        for="telegram_event_notification"></label>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="col-md-4">
                        <ul class="list-group">
                            <li
                                class="list-group-item d-flex align-items-center justify-content-between">
                                {{ Form::label('Meeting create', __('New Meeting'), ['class' => 'col-form-label']) }}
                                <div class="form-check form-switch d-inline-block float-right">
                                    {{ Form::checkbox('telegram_meeting_notification', '1', isset($settings['telegram_meeting_notification']) && $settings['telegram_meeting_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_meeting_notification']) }}
                                    <label class="col-form-label"
                                        for="telegram_meeting_notification"></label>
                                </div>
                            </li>

                            <li
                                class="list-group-item d-flex align-items-center justify-content-between">
                                {{ Form::label('Company policy create', __('New Company Policy '), ['class' => 'col-form-label']) }}
                                <div class="form-check form-switch d-inline-block float-right">
                                    {{ Form::checkbox('telegram_company_policy_notification', '1', isset($settings['telegram_company_policy_notification']) && $settings['telegram_company_policy_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_company_policy_notification']) }}
                                    <label class="col-form-label"
                                        for="telegram_company_policy_notification"></label>
                                </div>
                            </li>

                            <li
                                class="list-group-item d-flex align-items-center justify-content-between">
                                {{ Form::label('Contract create', __('New Contract'), ['class' => 'col-form-label']) }}
                                <div class="form-check form-switch d-inline-block float-right">
                                    {{ Form::checkbox('telegram_contract_notification', '1', isset($settings['telegram_contract_notification']) && $settings['telegram_contract_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_contract_notification']) }}
                                    <label class="col-form-label"
                                        for="telegram_contract_notification"></label>
                                </div>
                            </li>

                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                <button class="btn-submit btn btn-primary" type="submit">
                    {{ __('Save Changes') }}
                </button>
            </div>
            {{ Form::close() }}
        </div>
    </div>

    <div class="" id="twilio-settings">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Twilio Settings') }}</h5>
                <small
                    class="text-secondary font-weight-bold">{{ __('Twilio Notification Settings') }}</small>
            </div>
            {{ Form::open(['route' => 'twilio.setting', 'id' => 'twilio-setting', 'method' => 'post', 'class' => 'd-contents']) }}
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 form-group">
                        {{ Form::label('Twilio SID', __('Twilio SID'), ['class' => 'col-form-label']) }}<x-required></x-required>
                        {{ Form::text('twilio_sid', isset($settings['twilio_sid']) ? $settings['twilio_sid'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Twilio Sid'), 'required' => 'required']) }}
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 form-group">
                        {{ Form::label('Twilio Token', __('Twilio Token'), ['class' => 'col-form-label']) }}<x-required></x-required>
                        {{ Form::text('twilio_token', isset($settings['twilio_token']) ? $settings['twilio_token'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Twilio Token'), 'required' => 'required']) }}
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 form-group">
                        {{ Form::label('Twilio From', __('Twilio From'), ['class' => 'col-form-label']) }}<x-required></x-required>
                        {{ Form::text('twilio_from', isset($settings['twilio_from']) ? $settings['twilio_from'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Twilio From'), 'required' => 'required']) }}
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 form-group mb-3">
                        {{-- {{ Form::label('Module Setting', __('Module Setting'), ['class' => 'col-form-label']) }} --}}
                    </div>


                    <div class="col-md-4">
                        <ul class="list-group">
                            <li
                                class="list-group-item d-flex align-items-center justify-content-between">
                                {{ Form::label('Payslip create', __('New Monthly Payslip'), ['class' => 'col-form-label']) }}
                                <div class="form-check form-switch d-inline-block float-right">
                                    {{ Form::checkbox('twilio_monthly_payslip_notification', '1', isset($settings['twilio_monthly_payslip_notification']) && $settings['twilio_monthly_payslip_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'twilio_monthly_payslip_notification']) }}
                                    <label class="col-form-label"
                                        for="twilio_monthly_payslip_notification"></label>
                                </div>
                            </li>

                            <li
                                class="list-group-item d-flex align-items-center justify-content-between">
                                {{ Form::label('Leave Approve/Reject', __('Leave Approve/Reject'), ['class' => 'col-form-label']) }}
                                <div class="form-check form-switch d-inline-block float-right">
                                    {{ Form::checkbox('twilio_leave_approve_notification', '1', isset($settings['twilio_leave_approve_notification']) && $settings['twilio_leave_approve_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'twilio_leave_approve_notification']) }}
                                    <label class="col-form-label"
                                        for="twilio_leave_approve_notification"></label>
                                </div>
                            </li>

                            <li
                                class="list-group-item d-flex align-items-center justify-content-between">
                                {{ Form::label('Ticket create', __('New Ticket '), ['class' => 'col-form-label']) }}
                                <div class="form-check form-switch d-inline-block float-right">
                                    {{ Form::checkbox('twilio_ticket_notification', '1', isset($settings['twilio_ticket_notification']) && $settings['twilio_ticket_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'twilio_ticket_notification']) }}
                                    <label class="col-form-label"
                                        for="twilio_ticket_notification"></label>
                                </div>
                            </li>


                        </ul>
                    </div>

                    <div class="col-md-4">
                        <ul class="list-group">
                            <li
                                class="list-group-item d-flex align-items-center justify-content-between">
                                {{ Form::label('Award create', __('New Award'), ['class' => 'col-form-label']) }}
                                <div class="form-check form-switch d-inline-block float-right">
                                    {{ Form::checkbox('twilio_award_notification', '1', isset($settings['twilio_award_notification']) && $settings['twilio_award_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'twilio_award_notification']) }}
                                    <label class="col-form-label"
                                        for="twilio_award_notification"></label>
                                </div>
                            </li>

                            <li
                                class="list-group-item d-flex align-items-center justify-content-between">
                                {{ Form::label('Trip create', __('New Trip '), ['class' => 'col-form-label']) }}
                                <div class="form-check form-switch d-inline-block float-right">
                                    {{ Form::checkbox('twilio_trip_notification', '1', isset($settings['twilio_trip_notification']) && $settings['twilio_trip_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'twilio_trip_notification']) }}
                                    <label class="col-form-label"
                                        for="twilio_trip_notification"></label>
                                </div>
                            </li>

                        </ul>
                    </div>

                    <div class="col-md-4">
                        <ul class="list-group">
                            <li
                                class="list-group-item d-flex align-items-center justify-content-between">
                                {{ Form::label('Event create', __('New Event'), ['class' => 'col-form-label']) }}
                                <div class="form-check form-switch d-inline-block float-right">
                                    {{ Form::checkbox('twilio_event_notification', '1', isset($settings['twilio_event_notification']) && $settings['twilio_event_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'twilio_event_notification']) }}
                                    <label class="col-form-label"
                                        for="twilio_event_notification"></label>
                                </div>
                            </li>

                            <li
                                class="list-group-item d-flex align-items-center justify-content-between">
                                {{ Form::label('Announcement create', __('New Announcement '), ['class' => 'col-form-label']) }}
                                <div class="form-check form-switch d-inline-block float-right">
                                    {{ Form::checkbox('twilio_announcement_notification', '1', isset($settings['twilio_announcement_notification']) && $settings['twilio_announcement_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'twilio_announcement_notification']) }}
                                    <label class="col-form-label"
                                        for="twilio_announcement_notification"></label>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                <button class="btn-submit btn btn-primary" type="submit">
                    {{ __('Save Changes') }}
                </button>
            </div>
            {{ Form::close() }}
        </div>
    </div>
    @endif
    <div class="" id="offer-letter-settings">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5>{{ __('Offer Letter Settings') }}</h5>
                <div class="d-flex justify-content-end drp-languages">
                    <ul class="list-unstyled mb-0 m-2">
                        <li class="dropdown dash-h-item drp-language" style="margin-top: -19px;">
                            <a class="dash-head-link dropdown-toggle arrow-none me-0"
                                data-bs-toggle="dropdown" href="javascript:void(0)" role="button"
                                aria-haspopup="false" aria-expanded="false" id="dropdownLanguage">
                                <span class="drp-text hide-mob text-primary">
                                    {{ Str::ucfirst($offerlangName->fullName) }}
                                </span>
                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                            </a>
                            <div class="dropdown-menu dash-h-dropdown dropdown-menu-end"
                                aria-labelledby="dropdownLanguage">
                                {{-- @foreach ($currantLang as $offerlangs) --}}
                                {{-- <a href="{{ route('get.offerlatter.language', ['noclangs' => $noclang, 'explangs' => $explang, 'offerlangs' => $offerlangs, 'joininglangs' => $joininglang]) }}"
                                class="dropdown-item ms-1 {{ $offerlangs == $offerlang ? 'text-primary' : '' }}">{{ Str::upper($offerlangs) }}</a>
                                @endforeach --}}
                                @foreach (App\Models\Utility::languages() as $code => $offerlangs)
                                <a href="{{ route('get.offerlatter.language', ['noclangs' => $noclang, 'explangs' => $explang, 'offerlangs' => $code, 'joininglangs' => $joininglang]) }}"
                                    class="dropdown-item ms-1 {{ $offerlang == $code ? 'text-primary' : '' }}">
                                    <span>{{ ucFirst($offerlangs) }}</span>
                                </a>
                                @endforeach
                            </div>
                        </li>
                    </ul>

                </div>
            </div>
            <div class="card-body ">
                <h5 class="font-weight-bold pb-3">
                    {{ __('Placeholders') }}
                </h5>

                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="card">
                        <div class="card-header card-body">
                            <div class="row text-xs">
                                <div class="row">
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{applicant_name}', 'offer_letter_content')" style="cursor: pointer;">
                                        {{ __('Applicant Name') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{applicant_name}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{app_name}', 'offer_letter_content')" style="cursor: pointer;">
                                        {{ __('Company Name') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{app_name}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{job_title}', 'offer_letter_content')" style="cursor: pointer;">
                                        {{ __('Job title') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{job_title}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{job_type}', 'offer_letter_content')" style="cursor: pointer;">
                                        {{ __('Job type') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{job_type}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{start_date}', 'offer_letter_content')" style="cursor: pointer;">
                                        {{ __('Proposed Start Date') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{start_date}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{workplace_location}', 'offer_letter_content')" style="cursor: pointer;">
                                        {{ __('Working Location') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{workplace_location}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{days_of_week}', 'offer_letter_content')" style="cursor: pointer;">
                                        {{ __('Days Of Week') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{days_of_week}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{salary}', 'offer_letter_content')" style="cursor: pointer;">
                                        {{ __('Salary') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{salary}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{salary_type}', 'offer_letter_content')" style="cursor: pointer;">
                                        {{ __('Salary Type') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{salary_type}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{salary_duration}', 'offer_letter_content')" style="cursor: pointer;">
                                        {{ __('Salary Duration') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{salary_duration}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{offer_expiration_date}', 'offer_letter_content')" style="cursor: pointer;">
                                        {{ __('Offer Expiration Date') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{offer_expiration_date}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body table-border-style ">

                {{ Form::open(['route' => ['offerlatter.update', $offerlang], 'method' => 'post']) }}
                <div class="form-group col-12">
                    {{ Form::label('content', __(' Format'), ['class' => 'form-label text-dark']) }}
                    <textarea name="content" class="form-control summernote-simple" id="offer_letter_content" rows="15">
                    {{ old('content', isset($currOfferletterLang->content) ? $currOfferletterLang->content : '') }}</textarea>
                </div>
            </div>
            <div class="card-footer text-end">

                {{ Form::submit(__('Save Changes'), ['class' => 'btn  btn-primary']) }}
            </div>

            {{ Form::close() }}
        </div>
    </div>

    <div class="" id="joining-letter-settings">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5>{{ __('Joining Letter Settings') }}</h5>
                <div class="d-flex justify-content-end drp-languages">
                    <ul class="list-unstyled mb-0 m-2">
                        <li class="dropdown dash-h-item drp-language" style="margin-top: -19px;">
                            <a class="dash-head-link dropdown-toggle arrow-none me-0"
                                data-bs-toggle="dropdown" href="javascript:void(0)" role="button"
                                aria-haspopup="false" aria-expanded="false" id="dropdownLanguage1">
                                <span class="drp-text hide-mob text-primary">

                                    {{ Str::ucfirst($joininglangName->fullName) }}
                                </span>
                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                            </a>
                            <div class="dropdown-menu dash-h-dropdown dropdown-menu-end"
                                aria-labelledby="dropdownLanguage1">
                                {{-- @foreach ($currantLang as $joininglangs)
                                                    <a href="{{ route('get.joiningletter.language', ['noclangs' => $noclang, 'explangs' => $explang, 'offerlangs' => $offerlang, 'joininglangs' => $joininglangs]) }}"
                                class="dropdown-item {{ $joininglangs == $joininglang ? 'text-primary' : '' }}">{{ Str::upper($joininglangs) }}</a>
                                @endforeach --}}
                                @foreach (App\Models\Utility::languages() as $code => $joininglangs)
                                <a href="{{ route('get.joiningletter.language', ['noclangs' => $noclang, 'explangs' => $explang, 'offerlangs' => $offerlang, 'joininglangs' => $code]) }}"
                                    class="dropdown-item ms-1 {{ $joininglang == $code ? 'text-primary' : '' }}">
                                    <span>{{ ucFirst($joininglangs) }}</span>
                                </a>
                                @endforeach
                            </div>
                        </li>

                    </ul>
                </div>

            </div>
            <div class="card-body ">
                <h5 class="font-weight-bold pb-3">
                    {{ __('Placeholders') }}
                </h5>

                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="card">
                        <div class="card-header card-body">
                            <div class="row text-xs">
                                <div class="row">
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{date}', 'joining_letter_content')" style="cursor: pointer;">
                                        {{ __('Date') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{date}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{app_name}', 'joining_letter_content')" style="cursor: pointer;">
                                        {{ __('Company Name') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{app_name}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{employee_name}', 'joining_letter_content')" style="cursor: pointer;">
                                        {{ __('Employee Name') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{employee_name}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{address}', 'joining_letter_content')" style="cursor: pointer;">
                                        {{ __('Address') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{address}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{designation}', 'joining_letter_content')" style="cursor: pointer;">
                                        {{ __('Designation') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{designation}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{start_date}', 'joining_letter_content')" style="cursor: pointer;">
                                        {{ __('Start Date') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{start_date}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{branch}', 'joining_letter_content')" style="cursor: pointer;">
                                        {{ __('Branch') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{branch}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{start_time}', 'joining_letter_content')" style="cursor: pointer;">
                                        {{ __('Start Time') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{start_time}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{end_time}', 'joining_letter_content')" style="cursor: pointer;">
                                        {{ __('End Time') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{end_time}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{total_hours}', 'joining_letter_content')" style="cursor: pointer;">
                                        {{ __('Number of Hours') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{total_hours}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body table-border-style ">

                {{ Form::open(['route' => ['joiningletter.update', $joininglang], 'method' => 'post']) }}
                <div class="form-group col-12">
                    {{ Form::label('content', __(' Format'), ['class' => 'form-label text-dark']) }}
                    <textarea name="content" class="form-control summernote-simple" id="joining_letter_content" rows="15">
                    {{ old('content', isset($currjoiningletterLang->content) ? $currjoiningletterLang->content : '') }}</textarea>
                </div>

            </div>
            <div class="card-footer text-end">

                {{ Form::submit(__('Save Changes'), ['class' => 'btn  btn-primary']) }}
            </div>

            {{ Form::close() }}



        </div>
    </div>

    <div class="" id="experience-certificate-settings">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5>{{ __('Certificate of Experience Settings') }}
                </h5>
                <div class="d-flex justify-content-end drp-languages">
                    <ul class="list-unstyled mb-0 m-2">
                        <li class="dropdown dash-h-item drp-language" style="margin-top: -19px;">
                            <a class="dash-head-link dropdown-toggle arrow-none me-0"
                                data-bs-toggle="dropdown" href="javascript:void(0)" role="button"
                                aria-haspopup="false" aria-expanded="false" id="dropdownLanguage1">
                                <span class="drp-text hide-mob text-primary">

                                    {{ Str::ucfirst($explangName->fullName) }}
                                </span>
                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                            </a>
                            <div class="dropdown-menu dash-h-dropdown dropdown-menu-end"
                                aria-labelledby="dropdownLanguage1">
                                {{-- @foreach ($currantLang as $explangs)
                                                    <a href="{{ route('get.experiencecertificate.language', ['noclangs' => $noclang, 'explangs' => $explangs, 'offerlangs' => $offerlang, 'joininglangs' => $joininglang]) }}"
                                class="dropdown-item {{ $explangs == $explang ? 'text-primary' : '' }}">{{ Str::upper($explangs) }}</a>
                                @endforeach --}}
                                @foreach (App\Models\Utility::languages() as $code => $explangs)
                                <a href="{{ route('get.experiencecertificate.language', ['noclangs' => $noclang, 'explangs' => $code, 'offerlangs' => $offerlang, 'joininglangs' => $joininglang]) }}"
                                    class="dropdown-item ms-1 {{ $explang == $code ? 'text-primary' : '' }}">
                                    <span>{{ ucFirst($explangs) }}</span>
                                </a>
                                @endforeach
                            </div>
                        </li>

                    </ul>
                </div>

            </div>
            <div class="card-body ">
                <h5 class="font-weight-bold pb-3">
                    {{ __('Placeholders') }}
                </h5>

                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="card">
                        <div class="card-header card-body">
                            <div class="row text-xs">
                                <div class="row">
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{app_name}', 'experience_certificate_content')" style="cursor: pointer;">
                                        {{ __('Company Name') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{app_name}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{employee_name}', 'experience_certificate_content')" style="cursor: pointer;">
                                        {{ __('Employee Name') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{employee_name}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{date}', 'experience_certificate_content')" style="cursor: pointer;">
                                        {{ __('Date of Issuance') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{date}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{designation}', 'experience_certificate_content')" style="cursor: pointer;">
                                        {{ __('Designation') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{designation}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{start_date}', 'experience_certificate_content')" style="cursor: pointer;">
                                        {{ __('Start Date') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{start_date}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{branch}', 'experience_certificate_content')" style="cursor: pointer;">
                                        {{ __('Branch') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{branch}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{start_time}', 'experience_certificate_content')" style="cursor: pointer;">
                                        {{ __('Start Time') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{start_time}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{end_time}', 'experience_certificate_content')" style="cursor: pointer;">
                                        {{ __('End Time') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{end_time}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{total_hours}', 'experience_certificate_content')" style="cursor: pointer;">
                                        {{ __('Number of Hours') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{total_hours}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body table-border-style ">

                {{ Form::open(['route' => ['experiencecertificate.update', $explang], 'method' => 'post']) }}
                <div class="form-group col-12">
                    {{ Form::label('content', __(' Format'), ['class' => 'form-label text-dark']) }}
                    <textarea name="content" class="form-control summernote-simple" id="experience_certificate_content" rows="15">
                    {{ old('content', isset($currexperiencecertificateLang->content) ? $currexperiencecertificateLang->content : '') }}</textarea>
                </div>

            </div>
            <div class="card-footer text-end">

                {{ Form::submit(__('Save Changes'), ['class' => 'btn  btn-primary']) }}
            </div>

            {{ Form::close() }}
        </div>
    </div>

    <div class="" id="noc-settings">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5>{{ __('No Objection Certificate Settings') }}</h5>
                <div class="d-flex justify-content-end drp-languages">
                    <ul class="list-unstyled mb-0 m-2">
                        <li class="dropdown dash-h-item drp-language" style="margin-top: -19px;">
                            <a class="dash-head-link dropdown-toggle arrow-none me-0"
                                data-bs-toggle="dropdown" href="javascript:void(0)" role="button"
                                aria-haspopup="false" aria-expanded="false" id="dropdownLanguage1">
                                <span class="drp-text hide-mob text-primary">

                                    {{ Str::ucfirst($noclangName->fullName) }}
                                </span>
                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                            </a>
                            <div class="dropdown-menu dash-h-dropdown dropdown-menu-end"
                                aria-labelledby="dropdownLanguage1">
                                {{-- @foreach ($currantLang as $noclangs)
                                                    <a href="{{ route('get.noc.language', ['noclangs' => $noclangs, 'explangs' => $explang, 'offerlangs' => $offerlang, 'joininglangs' => $joininglang]) }}"
                                class="dropdown-item {{ $noclangs == $noclang ? 'text-primary' : '' }}">{{ Str::upper($noclangs) }}</a>
                                @endforeach --}}
                                @foreach (App\Models\Utility::languages() as $code => $noclangs)
                                <a href="{{ route('get.noc.language', ['noclangs' => $code, 'explangs' => $explang, 'offerlangs' => $offerlang, 'joininglangs' => $joininglang]) }}"
                                    class="dropdown-item ms-1 {{ $noclang == $code ? 'text-primary' : '' }}">
                                    <span>{{ ucFirst($noclangs) }}</span>
                                </a>
                                @endforeach
                            </div>
                        </li>

                    </ul>
                </div>

            </div>
            <div class="card-body ">
                <h5 class="font-weight-bold pb-3">
                    {{ __('Placeholders') }}
                </h5>

                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="card">
                        <div class="card-header card-body">
                            <div class="row text-xs">
                                <div class="row">
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{date}', 'noc_content')" style="cursor: pointer;">
                                        {{ __('Date') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{date}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{app_name}', 'noc_content')" style="cursor: pointer;">
                                        {{ __('Company Name') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{app_name}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{employee_name}', 'noc_content')" style="cursor: pointer;">
                                        {{ __('Employee Name') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{employee_name}</span>
                                    </p>
                                    <p class="col-4 mb-2" onclick="insertPlaceholderTag('{designation}', 'noc_content')" style="cursor: pointer;">
                                        {{ __('Designation') }} : <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{designation}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body table-border-style">
                {{ Form::open(['route' => ['noc.update', $noclang], 'method' => 'post']) }}
                <div class="form-group col-12">
                    {{ Form::label('content', __(' Format'), ['class' => 'form-label text-dark']) }}

                    <textarea name="content" class="form-control summernote-simple" id="noc_content" rows="15">
                    {{ old('content', isset($currnocLang->content) ? $currnocLang->content : '') }}</textarea>
                </div>

            </div>
            <div class="card-footer text-end">

                {{ Form::submit(__('Save Changes'), ['class' => 'btn  btn-primary']) }}
            </div>

            {{ Form::close() }}
        </div>
    </div>

    {{-- Google calendar --}}
    <div class="card" id="google-calender">
        <div class="col-md-12">
            {{ Form::open(['url' => route('google.calender.settings'), 'enctype' => 'multipart/form-data']) }}
            <div class="card-header">
                <div class="row">
                    <div class="col-lg-8 col-md-8 col-sm-8">
                        <h5 class="">
                            {{ __('Google Calendar') }}
                        </h5>
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-4 text-end">
                        <div class="col switch-width">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="form-check-input" name="is_enabled"
                                    data-toggle="switchbutton" data-onstyle="primary" id="is_enabled"
                                    {{ isset($settings['is_enabled']) && $settings['is_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                <label class="custom-control-label form-label" for="is_enabled"></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                        {{ Form::label('Google calendar id', __('Google Calendar Id'), ['class' => 'col-form-label']) }}<x-required></x-required>
                        {{ Form::text('google_clender_id', !empty($settings['google_clender_id']) ? $settings['google_clender_id'] : '', ['class' => 'form-control ', 'placeholder' => __('Google Calendar Id'), 'required' => 'required']) }}
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                        {{ Form::label('Google calendar json file', __('Google Calendar JSON File'), ['class' => 'col-form-label']) }}
                        <input type="file" class="form-control" name="google_calender_json_file"
                            id="file">
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                <button class="btn-submit btn btn-primary" type="submit">
                    {{ __('Save Changes') }}
                </button>
            </div>
            {{ Form::close() }}
        </div>
    </div>

    {{-- Webhook Settings --}}
    <div class="" id="webhook-settings">
        <div class="card">
            <div class="card-header d-flex justify-content-between">

                <h5>{{ __('Webhook Settings') }}</h5>
                @can('Create Webhook')
                <a data-url="{{ route('create.webhook') }}" class="btn btn-sm btn-primary"
                    data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"
                    data-bs-placement="top" data-size="md" data-ajax-popup="true"
                    data-title="{{ __('Create New Webhook') }}">
                    <i class="ti ti-plus"></i>
                </a>
                @endcan

            </div>
            <div class="card-body table-border-style ">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th class="w-25">
                                    {{ __('Module') }}
                                </th>
                                <th class="w-20">
                                    {{ __('URL') }}
                                </th>
                                <th class="w-30">
                                    {{ __('Method') }}
                                </th>
                                <th width="150px">
                                    {{ 'Action' }}
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($webhooks as $webhook)
                            <tr class="Action">
                                <td class="sorting_1">
                                    {{ $webhook->module }}
                                </td>
                                <td class="sorting_3">
                                    {{ $webhook->url }}
                                </td>
                                <td class="sorting_2">
                                    {{ $webhook->method }}
                                </td>
                                <td class="">
                                    @can('Edit Webhook')
                                    <div class="action-btn me-2">
                                        <a class="mx-3 btn btn-sm bg-info align-items-center"
                                            data-url="{{ route('edit.webhook', $webhook->id) }}"
                                            data-size="md" data-ajax-popup="true"
                                            data-title="{{ __('Edit Webhook Settings') }}"
                                            data-bs-toggle="tooltip"
                                            data-bs-original-title="{{ __('Edit') }}"
                                            data-bs-placement="top" class="edit-icon"
                                            data-original-title="{{ __('Edit') }}"><span
                                                class="text-white"><i
                                                    class="ti ti-pencil"></i></span></a>
                                    </div>
                                    @endcan
                                    @can('Delete Webhook')
                                    <div class="action-btn">
                                        {!! Form::open([
                                        'method' => 'DELETE',
                                        'route' => ['destroy.webhook', $webhook->id],
                                        'id' => 'delete-form-' . $webhook->id,
                                        ]) !!}
                                        <a href="javascript:void(0)" data-bs-toggle="tooltip"
                                            data-bs-original-title="{{ __('Delete') }}"
                                            data-bs-placement="top"
                                            class="mx-3 btn btn-sm bg-danger align-items-center bs-pass-para"
                                            data-bs-toggle="tooltip" data-bs-placement="bottom"
                                            title="{{ __('Delete') }}">
                                            <span class="text-white"><i
                                                    class="ti ti-trash"></i></span></a>
                                        {!! Form::close() !!}
                                    </div>
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            <tr class="text-center">
                                <td colspan="4">{{ __('No entries found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Salary Slip Settings (Payslip Template & Color) ─────────────────────── --}}
    <div class="" id="salary-slip-settings">
        <div class="card">
            <div class="card-header">
                <h5><i class="ti ti-receipt me-1"></i>{{ __('Salary Slip Settings') }}</h5>
                <small class="text-secondary font-weight-bold">
                    {{ __('Configure the payslip template, color, and stamp for all salary slips.') }}
                </small>
            </div>

            {{ Form::model($settings, ['route' => 'company.settings', 'method' => 'post', 'enctype' => 'multipart/form-data', 'id' => 'salary-slip-form']) }}
            @csrf
            @php
                $_selectedTemplate = $settings['payslip_template'] ?? 'standard';
                $_selectedColor    = $settings['payslip_primary_color'] ?? '';
                $_stampFile        = \App\Models\Utility::getValByName('company_stamp');
                $_stampUrl         = $_stampFile
                    ? \App\Models\Utility::get_file('uploads/logo/') . '/' . $_stampFile . '?' . time()
                    : null;
            @endphp

            <div class="card-body">
                <div class="row g-4">
                    {{-- ═══ LEFT COLUMN: Form Fields ═══ --}}
                    <div class="col-lg-5 col-xl-4">
                        <div class="row gy-3">

                            {{-- Template Format (Dropdown) --}}
                            <div class="col-12 form-group">
                                {{ Form::label('payslip_template', __('Payslip Template'), ['class' => 'col-form-label fw-semibold']) }}
                                {{ Form::select('payslip_template', [
                                    'standard'       => __('Standard — India / US / International'),
                                    'uk'             => __('UK — Tax Code, NI, YTD'),
                                    'compact'        => __('Compact — Minimalist Style'),
                                    'professional'   => __('Professional — Corporate Formal'),
                                    'modern'         => __('Modern — Card Based Design'),
                                    'classic'        => __('Classic — Elegant Serif Style'),
                                    'minimal'        => __('Minimal — Clean Monochrome'),
                                    'executive'      => __('Executive — Premium Dark Theme'),
                                    'bold'           => __('Bold — Striking High Contrast'),
                                    'elegant'        => __('Elegant — Refined Sophisticated'),
                                    'contemporary'   => __('Contemporary — Trendy Asymmetric'),
                                ], $_selectedTemplate, [
                                    'class' => 'form-control select2',
                                    'id' => 'payslip_template',
                                    'onchange' => 'updateSalarySlipPreview()',
                                ]) }}
                                <small class="text-muted d-block mt-1">
                                    <i class="ti ti-info-circle me-1"></i>
                                    {{ __('Determines the payslip layout for PDF downloads & previews.') }}
                                </small>
                            </div>                                {{-- Primary Color --}}
                            <div class="col-12 form-group">
                                {{ Form::label('payslip_primary_color', __('Payslip Color'), ['class' => 'col-form-label fw-semibold']) }}
                                @php
                                    $payslipPresets = [
                                        '#584ED2' => __('Default Purple'),
                                        '#4F46E5' => __('Indigo'),
                                        '#2563EB' => __('Blue'),
                                        '#0891B2' => __('Teal'),
                                        '#059669' => __('Green'),
                                        '#D97706' => __('Amber'),
                                        '#DC2626' => __('Red'),
                                        '#9333EA' => __('Purple'),
                                        '#BE185D' => __('Pink'),
                                        '#1E293B' => __('Slate'),
                                    ];
                                    $_defaultHex = '#584ED2';
                                @endphp                                    {{-- Row 1 & 2: 10 Preset color swatches (5 per row, auto-wraps) --}}
                                    <div class="payslip-color-swatches">
                                        @foreach($payslipPresets as $hex => $label)
                                            <a href="javascript:void(0)"
                                                class="payslip-swatch {{ ($_selectedColor === $hex) || (empty($_selectedColor) && $hex === $_defaultHex) ? 'active_color' : '' }}"
                                                data-color="{{ $hex }}"
                                                data-bs-toggle="tooltip"
                                                title="{{ $label }}"
                                                style="background: {{ $hex }};"></a>
                                        @endforeach
                                    </div>
                                    {{-- Row 3: Custom color picker + hex display + reset --}}
                                    <div class="d-flex align-items-center gap-2 mt-2">
                                        <div class="color-picker-wrp">
                                            <input type="color" name="payslip_primary_color"
                                                id="payslip_primary_color"
                                                value="{{ $_selectedColor ?: $_defaultHex }}"
                                                oninput="onPayslipCustomColorChange(this.value)"
                                                class="payslip-custom-picker">
                                        </div>
                                        <input type="text" readonly
                                            id="payslip_color_hex"
                                            value="{{ $_selectedColor ?: $_defaultHex }}"
                                            class="form-control" style="width:85px; font-size:12px;">
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                            onclick="resetPayslipColor()"
                                            data-bs-toggle="tooltip" title="{{ __('Reset to default') }}">
                                            <i class="ti ti-refresh"></i>
                                        </button>
                                    </div>
                                <small class="text-muted d-block mt-1">
                                    <i class="ti ti-info-circle me-1"></i>
                                    {{ __('Choose a preset color or pick a custom one.') }}
                                </small>
                            </div>

                            {{-- Stamp Upload --}}
                            <div class="col-12 form-group">
                                {{ Form::label('company_stamp', __('Authorized Stamp'), ['class' => 'col-form-label fw-semibold']) }}
                                <div class="d-flex align-items-start gap-3">
                                    <div class="text-center" style="flex-shrink:0;">
                                        @if($_stampUrl)
                                            <img id="stamp_preview" src="{{ $_stampUrl }}" alt="Stamp"
                                                style="width:80px;height:80px;border-radius:50%;object-fit:contain;border:3px solid #e0e0e0;">
                                        @else
                                            <div id="stamp_preview_placeholder"
                                                style="width:80px;height:80px;border-radius:50%;border:3px dashed #ccc;display:flex;align-items:center;justify-content:center;color:#aaa;font-size:10px;text-align:center;padding:8px;">
                                                {{ __('No stamp') }}
                                            </div>
                                            <img id="stamp_preview" src="" alt="Stamp"
                                                style="width:80px;height:80px;border-radius:50%;object-fit:contain;border:3px solid #e0e0e0;display:none;">
                                        @endif
                                    </div>
                                    <div style="flex:1;">
                                        <input type="file" class="form-control" name="company_stamp" id="company_stamp"
                                            accept="image/png,image/jpeg,image/jpg"
                                            onchange="if(this.files&&this.files[0]){var src=URL.createObjectURL(this.files[0]);var p=document.getElementById('stamp_preview');var ph=document.getElementById('stamp_preview_placeholder');p.src=src;p.style.display='block';if(ph)ph.style.display='none';updateSalarySlipPreview();}">
                                        <small class="text-muted d-block mt-1">
                                            <i class="ti ti-info-circle me-1"></i>
                                            {{ __('PNG with transparent background recommended.') }}
                                        </small>
                                        @error('company_stamp')
                                            <span class="text-danger d-block mt-1"><small>{{ $message }}</small></span>
                                        @enderror
                                    </div>
                                </div>
                            </div>                            {{-- Section Visibility Toggles --}}
                            <div class="col-12" style="border-top:1px solid #eee; padding-top:18px; margin-top:12px;">
                                <label class="col-form-label fw-semibold mb-2" style="font-size:13px;">
                                    <i class="ti ti-eye-off me-1"></i>{{ __('Payslip Section Visibility') }}
                                </label>
                                <small class="text-muted d-block mb-2" style="font-size:11px; line-height:1.4;">
                                    {{ __('Toggle sections & individual fields on the payslip. Unchecked = hidden.') }}
                                </small>
                                <div class="d-flex flex-column gap-1">
                                    {{-- ═══ Employee Details ═══ --}}
                                    <div class="form-check form-switch d-flex align-items-center" style="padding-left:2.5rem;">
                                        <input type="hidden" name="payslip_show_employee_details" value="off">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                            name="payslip_show_employee_details" id="payslip_show_employee_details"
                                            value="on"
                                            {{ ($settings['payslip_show_employee_details'] ?? 'on') === 'on' ? 'checked' : '' }}
                                            onchange="updateSalarySlipPreview()">
                                        <label class="form-check-label ms-2 fw-semibold" for="payslip_show_employee_details" style="font-size:12.5px; cursor:pointer;">
                                            <i class="ti ti-user me-1 text-muted"></i>{{ __('Employee Details') }}
                                        </label>
                                    </div>
                                    {{-- Sub-fields for Employee Details --}}
                                    <div class="d-flex flex-wrap gap-x-3 gap-y-1 ps-5 ms-1" style="border-left:2px solid #e5e7eb;">
                                        <div class="form-check form-switch d-flex align-items-center" style="padding-left:2.5rem; min-width:130px;">
                                            <input type="hidden" name="payslip_show_name" value="off">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                name="payslip_show_name" id="payslip_show_name" value="on"
                                                {{ ($settings['payslip_show_name'] ?? 'on') === 'on' ? 'checked' : '' }}
                                                onchange="updateSalarySlipPreview()">
                                            <label class="form-check-label ms-2" for="payslip_show_name" style="font-size:11.5px; cursor:pointer;">{{ __('Name') }}</label>
                                        </div>
                                        <div class="form-check form-switch d-flex align-items-center" style="padding-left:2.5rem; min-width:130px;">
                                            <input type="hidden" name="payslip_show_designation" value="off">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                name="payslip_show_designation" id="payslip_show_designation" value="on"
                                                {{ ($settings['payslip_show_designation'] ?? 'on') === 'on' ? 'checked' : '' }}
                                                onchange="updateSalarySlipPreview()">
                                            <label class="form-check-label ms-2" for="payslip_show_designation" style="font-size:11.5px; cursor:pointer;">{{ __('Designation') }}</label>
                                        </div>
                                        <div class="form-check form-switch d-flex align-items-center" style="padding-left:2.5rem; min-width:130px;">
                                            <input type="hidden" name="payslip_show_employee_id" value="off">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                name="payslip_show_employee_id" id="payslip_show_employee_id" value="on"
                                                {{ ($settings['payslip_show_employee_id'] ?? 'on') === 'on' ? 'checked' : '' }}
                                                onchange="updateSalarySlipPreview()">
                                            <label class="form-check-label ms-2" for="payslip_show_employee_id" style="font-size:11.5px; cursor:pointer;">{{ __('Employee ID') }}</label>
                                        </div>
                                        <div class="form-check form-switch d-flex align-items-center" style="padding-left:2.5rem; min-width:130px;">
                                            <input type="hidden" name="payslip_show_department" value="off">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                name="payslip_show_department" id="payslip_show_department" value="on"
                                                {{ ($settings['payslip_show_department'] ?? 'on') === 'on' ? 'checked' : '' }}
                                                onchange="updateSalarySlipPreview()">
                                            <label class="form-check-label ms-2" for="payslip_show_department" style="font-size:11.5px; cursor:pointer;">{{ __('Department') }}</label>
                                        </div>
                                        <div class="form-check form-switch d-flex align-items-center" style="padding-left:2.5rem; min-width:130px;">
                                            <input type="hidden" name="payslip_show_pan_no" value="off">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                name="payslip_show_pan_no" id="payslip_show_pan_no" value="on"
                                                {{ ($settings['payslip_show_pan_no'] ?? 'on') === 'on' ? 'checked' : '' }}
                                                onchange="updateSalarySlipPreview()">
                                            <label class="form-check-label ms-2" for="payslip_show_pan_no" style="font-size:11.5px; cursor:pointer;">{{ __('PAN No.') }}</label>
                                        </div>
                                        <div class="form-check form-switch d-flex align-items-center" style="padding-left:2.5rem; min-width:130px;">
                                            <input type="hidden" name="payslip_show_date_of_joining" value="off">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                name="payslip_show_date_of_joining" id="payslip_show_date_of_joining" value="on"
                                                {{ ($settings['payslip_show_date_of_joining'] ?? 'on') === 'on' ? 'checked' : '' }}
                                                onchange="updateSalarySlipPreview()">
                                            <label class="form-check-label ms-2" for="payslip_show_date_of_joining" style="font-size:11.5px; cursor:pointer;">{{ __('Date of Joining') }}</label>
                                        </div>
                                    </div>

                                    {{-- ═══ Payment Details ═══ --}}
                                    <div class="form-check form-switch d-flex align-items-center" style="padding-left:2.5rem;" class="mt-2">
                                        <input type="hidden" name="payslip_show_payment_details" value="off">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                            name="payslip_show_payment_details" id="payslip_show_payment_details"
                                            value="on"
                                            {{ ($settings['payslip_show_payment_details'] ?? 'on') === 'on' ? 'checked' : '' }}
                                            onchange="updateSalarySlipPreview()">
                                        <label class="form-check-label ms-2 fw-semibold" for="payslip_show_payment_details" style="font-size:12.5px; cursor:pointer;">
                                            <i class="ti ti-credit-card me-1 text-muted"></i>{{ __('Payment Details') }}
                                        </label>
                                    </div>
                                    {{-- Sub-fields for Payment Details --}}
                                    <div class="d-flex flex-wrap gap-x-3 gap-y-1 ps-5 ms-1" style="border-left:2px solid #e5e7eb;">
                                        <div class="form-check form-switch d-flex align-items-center" style="padding-left:2.5rem; min-width:130px;">
                                            <input type="hidden" name="payslip_show_bank_name" value="off">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                name="payslip_show_bank_name" id="payslip_show_bank_name" value="on"
                                                {{ ($settings['payslip_show_bank_name'] ?? 'on') === 'on' ? 'checked' : '' }}
                                                onchange="updateSalarySlipPreview()">
                                            <label class="form-check-label ms-2" for="payslip_show_bank_name" style="font-size:11.5px; cursor:pointer;">{{ __('Bank Name') }}</label>
                                        </div>
                                        <div class="form-check form-switch d-flex align-items-center" style="padding-left:2.5rem; min-width:130px;">
                                            <input type="hidden" name="payslip_show_account_no" value="off">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                name="payslip_show_account_no" id="payslip_show_account_no" value="on"
                                                {{ ($settings['payslip_show_account_no'] ?? 'on') === 'on' ? 'checked' : '' }}
                                                onchange="updateSalarySlipPreview()">
                                            <label class="form-check-label ms-2" for="payslip_show_account_no" style="font-size:11.5px; cursor:pointer;">{{ __('Account No.') }}</label>
                                        </div>
                                        <div class="form-check form-switch d-flex align-items-center" style="padding-left:2.5rem; min-width:130px;">
                                            <input type="hidden" name="payslip_show_bank_code" value="off">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                name="payslip_show_bank_code" id="payslip_show_bank_code" value="on"
                                                {{ ($settings['payslip_show_bank_code'] ?? 'on') === 'on' ? 'checked' : '' }}
                                                onchange="updateSalarySlipPreview()">
                                            <label class="form-check-label ms-2" for="payslip_show_bank_code" style="font-size:11.5px; cursor:pointer;">{{ __('Bank Code') }}</label>
                                        </div>
                                        <div class="form-check form-switch d-flex align-items-center" style="padding-left:2.5rem; min-width:130px;">
                                            <input type="hidden" name="payslip_show_account_holder" value="off">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                name="payslip_show_account_holder" id="payslip_show_account_holder" value="on"
                                                {{ ($settings['payslip_show_account_holder'] ?? 'on') === 'on' ? 'checked' : '' }}
                                                onchange="updateSalarySlipPreview()">
                                            <label class="form-check-label ms-2" for="payslip_show_account_holder" style="font-size:11.5px; cursor:pointer;">{{ __('Account Holder') }}</label>
                                        </div>
                                        <div class="form-check form-switch d-flex align-items-center" style="padding-left:2.5rem; min-width:130px;">
                                            <input type="hidden" name="payslip_show_transaction_mode" value="off">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                name="payslip_show_transaction_mode" id="payslip_show_transaction_mode" value="on"
                                                {{ ($settings['payslip_show_transaction_mode'] ?? 'on') === 'on' ? 'checked' : '' }}
                                                onchange="updateSalarySlipPreview()">
                                            <label class="form-check-label ms-2" for="payslip_show_transaction_mode" style="font-size:11.5px; cursor:pointer;">{{ __('Transaction Mode') }}</label>
                                        </div>
                                        <div class="form-check form-switch d-flex align-items-center" style="padding-left:2.5rem; min-width:130px;">
                                            <input type="hidden" name="payslip_show_pay_period" value="off">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                name="payslip_show_pay_period" id="payslip_show_pay_period" value="on"
                                                {{ ($settings['payslip_show_pay_period'] ?? 'on') === 'on' ? 'checked' : '' }}
                                                onchange="updateSalarySlipPreview()">
                                            <label class="form-check-label ms-2" for="payslip_show_pay_period" style="font-size:11.5px; cursor:pointer;">{{ __('Pay Period') }}</label>
                                        </div>
                                    </div>

                                    {{-- ═══ Signatures & Footer ═══ --}}
                                    <div class="form-check form-switch d-flex align-items-center" style="padding-left:2.5rem;">
                                        <input type="hidden" name="payslip_show_signatures" value="off">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                            name="payslip_show_signatures" id="payslip_show_signatures"
                                            value="on"
                                            {{ ($settings['payslip_show_signatures'] ?? 'on') === 'on' ? 'checked' : '' }}
                                            onchange="updateSalarySlipPreview()">
                                        <label class="form-check-label ms-2 fw-semibold" for="payslip_show_signatures" style="font-size:12.5px; cursor:pointer;">
                                            <i class="ti ti-signature me-1 text-muted"></i>{{ __('Signatures') }}
                                        </label>
                                    </div>
                                    <div class="form-check form-switch d-flex align-items-center" style="padding-left:2.5rem;">
                                        <input type="hidden" name="payslip_show_footer" value="off">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                            name="payslip_show_footer" id="payslip_show_footer"
                                            value="on"
                                            {{ ($settings['payslip_show_footer'] ?? 'on') === 'on' ? 'checked' : '' }}
                                            onchange="updateSalarySlipPreview()">
                                        <label class="form-check-label ms-2 fw-semibold" for="payslip_show_footer" style="font-size:12.5px; cursor:pointer;">
                                            <i class="ti ti-article me-1 text-muted"></i>{{ __('Footer') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div>{{-- /row gy-3 --}}

                        <div class="mt-4">
                            <button class="btn btn-primary" type="submit">
                                <i class="ti ti-device-floppy me-1"></i>{{ __('Save Changes') }}
                            </button>
                        </div>
                    </div>{{-- /LEFT COL --}}

                    {{-- ═══ RIGHT COLUMN: Live Preview ═══ --}}
                    <div class="col-lg-7 col-xl-8">
                        <div class="card border shadow-sm h-100">
                            <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center bg-light">
                                <h6 class="mb-0" style="font-size:13px;">
                                    <i class="ti ti-eye text-primary me-1"></i>{{ __('Live Preview') }}
                                </h6>
                                <div>
                                    <span class="badge bg-success me-2" style="font-size:9px;">{{ __('LIVE') }}</span>
                                    <small class="text-muted" style="font-size:11px;">{{ __('Changes reflect instantly') }}</small>
                                </div>
                            </div>
                            <div class="card-body p-0" style="position:relative; min-height:400px; background:#f5f5f5;">
                                <iframe id="salary-slip-preview-iframe"
                                    src="{{ route('payslip.settings-preview', ['template' => $_selectedTemplate, 'color' => $_selectedColor ?: '']) }}"
                                    style="width:100%; height:400px; border:none; display:block;"
                                    title="{{ __('Salary Slip Preview') }}" sandbox="allow-scripts allow-same-origin"></iframe>
                                <div id="preview-loading"
                                    style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); display:none;">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">{{ __('Loading...') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>{{-- /RIGHT COL --}}

                </div>{{-- /row g-4 --}}
            </div>

            {{-- BOTTOM bar (for small screens / fallback) --}}
            <div class="card-footer text-end d-lg-none">
                <button class="btn btn-primary" type="submit">
                    <i class="ti ti-device-floppy me-1"></i>{{ __('Save Changes') }}
                </button>
            </div>
            {{ Form::close() }}
        </div>
    </div>

    {{-- HMRC PAYE Settings (Company Level) --}}
    @if(\App\Services\HmrcService::isEnabled())
    <div class="" id="hmrc-paye-settings">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('HMRC PAYE Settings') }}</h5>
                <small class="text-secondary font-weight-bold">
                    {{ __('Enter your employer PAYE details for HMRC RTI submissions (Full Payment Submission). These are specific to your company.') }}
                </small>
            </div>

            {{ Form::open(['route' => 'hmrc.paye.settings.store', 'method' => 'post']) }}
            @csrf
            <div class="card-body">
                <div class="row">
                    {{-- Employer PAYE Ref --}}
                    <div class="col-md-6 form-group">
                        {{ Form::label('hmrc_employer_paye_ref', __('Employer PAYE Reference'), ['class' => 'col-form-label']) }}
                        {{ Form::text('hmrc_employer_paye_ref', isset($settings['hmrc_employer_paye_ref']) ? $settings['hmrc_employer_paye_ref'] : '', ['class' => 'form-control', 'placeholder' => __('e.g. 123/AB12345')]) }}
                        <small class="text-muted">{{ __('Format: 3-digit HMRC office number / employer reference. Found on the letter HMRC sent when you registered as an employer.') }}</small>
                    </div>

                    {{-- Accounts Office Ref --}}
                    <div class="col-md-6 form-group">
                        {{ Form::label('hmrc_accounts_office_ref', __('Accounts Office Reference'), ['class' => 'col-form-label']) }}
                        {{ Form::text('hmrc_accounts_office_ref', isset($settings['hmrc_accounts_office_ref']) ? $settings['hmrc_accounts_office_ref'] : '', ['class' => 'form-control', 'placeholder' => __('e.g. 123PA00012345')]) }}
                        <small class="text-muted">{{ __('13-character reference from HMRC. Available online if you pay electronically.') }}</small>
                    </div>

                    {{-- Tax Year --}}
                    <div class="col-md-6 form-group">
                        {{ Form::label('hmrc_tax_year', __('Current Tax Year'), ['class' => 'col-form-label']) }}
                        @php
                            $currentMonth = (int) date('n');
                            $currentYear  = (int) date('Y');
                            $taxYearStart = $currentMonth >= 4 ? $currentYear : $currentYear - 1;
                            $taxYearLabel = $taxYearStart . '-' . ($taxYearStart + 1);
                        @endphp
                        {{ Form::text('hmrc_tax_year', $taxYearLabel, ['class' => 'form-control', 'readonly']) }}
                        <small class="text-muted">{{ __('UK tax year runs 6 April to 5 April. Auto-calculated.') }}</small>
                    </div>

                    {{-- Corporation Tax Ref (optional) --}}
                    <div class="col-md-6 form-group">
                        {{ Form::label('hmrc_cotax_ref', __('Corporation Tax Reference (Optional)'), ['class' => 'col-form-label']) }}
                        {{ Form::text('hmrc_cotax_ref', isset($settings['hmrc_cotax_ref']) ? $settings['hmrc_cotax_ref'] : '', ['class' => 'form-control', 'placeholder' => __('Only for limited companies')]) }}
                        <small class="text-muted">{{ __('Your COTAX reference if you are a limited company.') }}</small>
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                <button type="button" class="btn btn-outline-info me-2" id="btn-hmrc-test-company">
                    <i class="ti ti-plug"></i> {{ __('Test HMRC Connection') }}
                </button>
                <input type="submit" value="{{ __('Save Changes') }}" class="btn-submit btn btn-primary">
                <div id="hmrc-company-result" class="mt-2 text-start" style="display:none;"></div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
    @endif

    {{-- ═══ HRMS Employee Sync ═══ --}}
    <div class="" id="idab-employee-sync-settings">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-1">{{ __('HRMS Employee Sync') }}</h5>
                    <small class="text-muted">{{ __('Import employees from your iDAB system into HRMS') }}</small>
                </div>
                <div>
                    <button type="button" class="btn btn-primary btn-sm btn-sync-idab-employees" id="btn-sync-idab-employees" onclick="syncIdabEmployeesNow(this)">
                        <i class="ti ti-refresh me-1"></i> {{ __('Sync Employees Now') }}
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info border-0 shadow-sm" style="background-color: #e0f2fe; color: #0369a1; border-radius: 8px;">
                    <h6 class="fw-bold mb-2" style="color: #0284c7;">
                        <i class="ti ti-info-circle me-1"></i> {{ __('How it works') }}
                    </h6>
                    <ul class="mb-0 ps-3 small">
                        <li class="mb-1">{{ __('Clicking "Sync Employees Now" connects via SSO and fetches all staff from the iDAB system.') }}</li>
                        <li class="mb-1">{{ __('Staff with working status (is_working_staff: true) are assigned the "Employee" role and placed under Employees.') }}</li>
                        <li class="mb-1">{{ __('Staff with non-working status (is_working_staff: false) are moved to Admin Staff with no role assigned.') }}</li>
                        <li><strong class="text-warning">{{ __('Action required:') }}</strong> {{ __('After sync, review your Employee and Admin Staff lists to verify assigned records.') }}</li>
                    </ul>
                </div>

                <div id="idab-sync-result-box" class="mt-3 d-none">
                    <div class="alert alert-success border-0 shadow-sm mb-2" id="idab-sync-result-msg"></div>
                    <div class="alert alert-warning border-0 shadow-sm mb-0 d-none" id="idab-sync-warning-msg"></div>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
</div>
</div>

@endsection

@push('script-page')
<script>
let index = 0;

// Existing shifts data from PHP
const existingShifts = @json($company_shifts ?? []);

// Toggle table
function toggleTable() {
    let checked = document.getElementById('toggleShift').checked;
    let wrapper = document.getElementById('shiftTableWrapper');
    let body = document.getElementById('shiftBody');

    wrapper.style.display = checked ? 'block' : 'none';

    if (checked && body.children.length === 0) {
        loadExistingShifts();
    }
}

// Load existing shifts on page load
function loadExistingShifts() {
    if (existingShifts.length > 0) {
        existingShifts.forEach((shift, idx) => {
            addRowWithData(shift, idx);
        });
    } else {
        addRow();
    }
}

// Add row with existing data
function addRowWithData(shiftData, existingIndex) {
    let row = `
    <tr id="row_${index}" class="align-middle">
        <td><input type="text" name="shifts[${index}][name]" class="form-control" placeholder="Shift Name *" value="${shiftData.name || shiftData.title || 'Shift ' + (index + 1)}" required></td>
        <td><input type="text" name="shifts[${index}][code]" class="form-control" placeholder="Code" value="${shiftData.code || ''}"></td>
        <td><input type="color" name="shifts[${index}][color]" class="form-control form-control-color" value="${shiftData.color || '#51459d'}" title="Choose Color" style="width: 40px; height: 36px; padding: 2px;"></td>

        <td><input type="time" name="shifts[${index}][start]" class="form-control" value="${shiftData.start || ''}"></td>
        <td><input type="time" name="shifts[${index}][end]" class="form-control" value="${shiftData.end || ''}"></td>

        <td>
            <select name="shifts[${index}][type]" class="form-control shift-break-type"
                onchange="toggleType(this, ${index})">
                <option value="">Select Break Type</option>
                <option value="fixed" ${shiftData.type === 'fixed' ? 'selected' : ''}>Fixed</option>
                <option value="flexible" ${shiftData.type === 'flexible' ? 'selected' : ''}>Flexible</option>
            </select>
        </td>

        <!-- Lunch -->
        <td>
            <input type="time" id="lunch_start_${index}" name="shifts[${index}][lunch_start]" class="form-control" value="${shiftData.lunch_start || ''}">
            <span id="lunch_min_label_${index}" class="text-secondary fw-semibold" style="display:none;">Minutes</span>
        </td>

        <td>
            <input type="time" id="lunch_end_${index}" name="shifts[${index}][lunch_end]" class="form-control" value="${shiftData.lunch_end || ''}">
            <input type="number" id="lunch_min_${index}" name="shifts[${index}][lunch_minutes]" class="form-control" placeholder="0" min="0" value="${shiftData.lunch_minutes || ''}" style="display:none;">
        </td>

        <!-- Tea -->
        <td>
            <input type="time" id="tea_start_${index}" name="shifts[${index}][tea_start]" class="form-control" value="${shiftData.tea_start || ''}">
            <span id="tea_min_label_${index}" class="text-secondary fw-semibold" style="display:none;">Minutes</span>
        </td>

        <td>
            <input type="time" id="tea_end_${index}" name="shifts[${index}][tea_end]" class="form-control" value="${shiftData.tea_end || ''}">
            <input type="number" id="tea_min_${index}" name="shifts[${index}][tea_minutes]" class="form-control" placeholder="0" min="0" value="${shiftData.tea_minutes || ''}" style="display:none;">
        </td>

        <td class="text-center">
            <div class="form-check form-switch d-flex justify-content-center" style="transform: scale(0.75); transform-origin: center;">
                <input class="form-check-input" type="checkbox"
                    name="shifts[${index}][show_in_rota]"
                    id="show_in_rota_${index}"
                    value="1"
                    ${shiftData.show_in_rota == 1 ? 'checked' : ''}
                    title="Show in Rota">
            </div>
        </td>

        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(${index})">X</button>
        </td>
    </tr>
    `;

    document.getElementById('shiftBody').insertAdjacentHTML('beforeend', row);
    toggleType(document.querySelector(`#row_${index} select[name="shifts[${index}][type]"]`), index);
    index++;
}

// Add row
function addRow() {

    let row = `
    <tr id="row_${index}" class="align-middle">
        <td><input type="text" name="shifts[${index}][name]" class="form-control" placeholder="Shift Name *" value="Shift ${index + 1}" required></td>
        <td><input type="text" name="shifts[${index}][code]" class="form-control" placeholder="Code"></td>
        <td><input type="color" name="shifts[${index}][color]" class="form-control form-control-color" value="#51459d" title="Choose Color" style="width: 40px; height: 36px; padding: 2px;"></td>

        <td><input type="time" name="shifts[${index}][start]" class="form-control"></td>
        <td><input type="time" name="shifts[${index}][end]" class="form-control"></td>

        <td>
            <select name="shifts[${index}][type]" class="form-control"
                onchange="toggleType(this, ${index})">
                <option value="">Select Break Type</option>
                <option value="fixed">Fixed</option>
                <option value="flexible">Flexible</option>
            </select>
        </td>

        <!-- Lunch -->
        <td>
            <input type="time" id="lunch_start_${index}" name="shifts[${index}][lunch_start]" class="form-control">
            <span id="lunch_min_label_${index}" class="text-secondary fw-semibold" style="display:none;">Minutes</span>
        </td>

        <td>
            <input type="time" id="lunch_end_${index}" name="shifts[${index}][lunch_end]" class="form-control">
            <input type="number" id="lunch_min_${index}" name="shifts[${index}][lunch_minutes]" class="form-control" placeholder="0" min="0" style="display:none;">
        </td>

        <!-- Tea -->
        <td>
            <input type="time" id="tea_start_${index}" name="shifts[${index}][tea_start]" class="form-control">
            <span id="tea_min_label_${index}" class="text-secondary fw-semibold" style="display:none;">Minutes</span>
        </td>

        <td>
            <input type="time" id="tea_end_${index}" name="shifts[${index}][tea_end]" class="form-control">
            <input type="number" id="tea_min_${index}" name="shifts[${index}][tea_minutes]" class="form-control" placeholder="0" min="0" style="display:none;">
        </td>

        <td class="text-center">
            <div class="form-check form-switch" style="transform-origin: center;">
                <input class="form-check-input" type="checkbox"
                    name="shifts[${index}][show_in_rota]"
                    id="show_in_rota_${index}"
                    value="1"
                    title="Show in Rota">
            </div>
        </td>

        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(${index})">X</button>
        </td>
    </tr>
    `;

    document.getElementById('shiftBody').insertAdjacentHTML('beforeend', row);
    toggleType(document.querySelector(`#row_${index} select[name="shifts[${index}][type]"]`), index);
    index++;
}

// Toggle Fixed / Flexible
function toggleType(select, i) {
    if (!select) return;
    let type = select.value;

    let lunchStart = document.getElementById('lunch_start_' + i);
    let lunchEnd = document.getElementById('lunch_end_' + i);
    let lunchMinLabel = document.getElementById('lunch_min_label_' + i);
    let lunchMin = document.getElementById('lunch_min_' + i);

    let teaStart = document.getElementById('tea_start_' + i);
    let teaEnd = document.getElementById('tea_end_' + i);
    let teaMinLabel = document.getElementById('tea_min_label_' + i);
    let teaMin = document.getElementById('tea_min_' + i);

    if (type === 'fixed') {
        if (lunchStart) lunchStart.style.display = 'block';
        if (lunchEnd) lunchEnd.style.display = 'block';
        if (lunchMinLabel) lunchMinLabel.style.display = 'none';
        if (lunchMin) lunchMin.style.display = 'none';

        if (teaStart) teaStart.style.display = 'block';
        if (teaEnd) teaEnd.style.display = 'block';
        if (teaMinLabel) teaMinLabel.style.display = 'none';
        if (teaMin) teaMin.style.display = 'none';
    } else if (type === 'flexible') {
        if (lunchStart) lunchStart.style.display = 'none';
        if (lunchEnd) lunchEnd.style.display = 'none';
        if (lunchMinLabel) lunchMinLabel.style.display = 'inline-block';
        if (lunchMin) lunchMin.style.display = 'block';

        if (teaStart) teaStart.style.display = 'none';
        if (teaEnd) teaEnd.style.display = 'none';
        if (teaMinLabel) teaMinLabel.style.display = 'inline-block';
        if (teaMin) teaMin.style.display = 'block';
    } else {
        if (lunchStart) lunchStart.style.display = 'none';
        if (lunchEnd) lunchEnd.style.display = 'none';
        if (lunchMinLabel) lunchMinLabel.style.display = 'none';
        if (lunchMin) lunchMin.style.display = 'none';

        if (teaStart) teaStart.style.display = 'none';
        if (teaEnd) teaEnd.style.display = 'none';
        if (teaMinLabel) teaMinLabel.style.display = 'none';
        if (teaMin) teaMin.style.display = 'none';
    }
}

// Remove row
function removeRow(i) {
    document.getElementById('row_' + i).remove();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    if (existingShifts.length > 0) {
        document.getElementById('toggleShift').checked = true;
        document.getElementById('shiftTableWrapper').style.display = 'block';
        loadExistingShifts();
    }
});

// ── HMRC Test Connection (Company Settings) ──────────────────────────
$(document).on('click', '#btn-hmrc-test-company', function() {
    var $btn = $(this);
    var $result = $('#hmrc-company-result');

    $btn.prop('disabled', true).html('<i class="ti ti-loader ti-spin me-1"></i> {{ __("Testing...") }}');
    $result.hide();

    $.ajax({
        url: '{{ route("hmrc.test.connection") }}',
        type: 'GET',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function(res) {
            if (res.success) {
                if (typeof show_toastr === 'function') {
                    show_toastr('Success', res.message, 'success');
                }
                $result.html('<span class="text-success fw-bold me-2"><i class="ti ti-circle-check-filled text-success me-1"></i> ' + res.message + '</span>').show();
            } else {
                if (typeof show_toastr === 'function') {
                    show_toastr('Error', res.message, 'error');
                }
                $result.html('<span class="text-danger fw-bold me-2"><i class="ti ti-circle-x-filled text-danger me-1"></i> ' + res.message + '</span>').show();
            }
        },
        error: function(xhr) {
            var msg = xhr.responseJSON ? xhr.responseJSON.message : '{{ __("Connection test failed.") }}';
            if (typeof show_toastr === 'function') {
                show_toastr('Error', msg, 'error');
            }
            $result.html('<span class="text-danger fw-bold me-2"><i class="ti ti-circle-x-filled text-danger me-1"></i> ' + msg + '</span>').show();
        },
        complete: function() {
            $btn.prop('disabled', false).html('<i class="ti ti-plug me-1"></i> {{ __("Test HMRC Connection") }}');
        }
    });
});

// ── iDAB Employee Sync Handler ────────────────────────────────────────
function syncIdabEmployeesNow(btn) {
    var $btn = $(btn);
    var $box = $('#idab-sync-result-box');
    var $msg = $('#idab-sync-result-msg');

    $btn.prop('disabled', true).html('<i class="ti ti-loader ti-spin me-1"></i> {{ __("Syncing...") }}');
    $box.addClass('d-none');

    $.ajax({
        url: '{{ route("idab.sync.employees") }}',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function(res) {
            if (res.success) {
                if (typeof show_toastr === 'function') {
                    show_toastr('Success', res.message, 'success');
                }
                $msg.removeClass('alert-danger').addClass('alert-success').html(res.message);

                if (res.warning) {
                    $('#idab-sync-warning-msg').removeClass('d-none').html('<i class="ti ti-alert-triangle me-1"></i> ' + res.warning);
                    if (typeof show_toastr === 'function') {
                        setTimeout(function() {
                            show_toastr('Warning', res.warning, 'warning');
                        }, 500);
                    }
                } else {
                    $('#idab-sync-warning-msg').addClass('d-none');
                }

                $box.removeClass('d-none');
            } else {
                if (typeof show_toastr === 'function') {
                    show_toastr('Error', res.message, 'error');
                }
                $msg.removeClass('alert-success').addClass('alert-danger').html(res.message);
                $('#idab-sync-warning-msg').addClass('d-none');
                $box.removeClass('d-none');
            }
        },
        error: function(xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : '{{ __("Sync failed. Please try again.") }}';
            if (typeof show_toastr === 'function') {
                show_toastr('Error', msg, 'error');
            }
            $msg.removeClass('alert-success').addClass('alert-danger').html(msg);
            $box.removeClass('d-none');
        },
        complete: function() {
            $btn.prop('disabled', false).html('<i class="ti ti-refresh me-1"></i> {{ __("Sync Employees Now") }}');
        }
    });
}
$(document).on('click', '#btn-sync-idab-employees', function() {
    syncIdabEmployeesNow(this);
});

// ── Save Textarea Cursor Position ─────────────────────────────────────
$(document).on('keyup click focus input select', '#offer-letter-settings textarea, #joining-letter-settings textarea, #experience-certificate-settings textarea, #noc-settings textarea, textarea', function() {
    if (typeof this.selectionStart === 'number') {
        this.lastPos = this.selectionStart;
    }
});

// ── Global Function: Insert Placeholder Tag at Caret Position ──────────
function insertPlaceholderTag(tagText, targetEditorId) {
    if (!tagText) return;

    var el = null;

    // 1. Target exact textarea by specific ID if passed
    if (targetEditorId) {
        el = document.getElementById(targetEditorId);
    }

    // 2. Fallback to visible textarea
    if (!el) {
        var $visible = $('textarea[name="content"]:visible, textarea:visible').first();
        if ($visible.length) el = $visible[0];
    }

    // 3. Ultimate Fallback
    if (!el) {
        var $any = $('textarea[name="content"], textarea').first();
        if ($any.length) el = $any[0];
    }

    if (!el) return;

    var $textarea = $(el);

    // Check if Summernote is active
    var $note = $textarea.siblings('.note-editor');
    if (!$note.length) $note = $textarea.parent().find('.note-editor');

    if ($note.length > 0 && typeof $.fn.summernote !== 'undefined') {
        try {
            $textarea.summernote('focus');
            $textarea.summernote('insertText', tagText);
        } catch(err) {
            var curCode = $textarea.summernote('code') || '';
            $textarea.summernote('code', curCode + ' ' + tagText);
        }
    } else {
        // Standard Textarea Caret Insertion
        var val = el.value || '';
        var start = (typeof el.selectionStart === 'number' && el.selectionStart >= 0) ? el.selectionStart : (typeof el.lastPos === 'number' ? el.lastPos : val.length);
        var end = (typeof el.selectionEnd === 'number' && el.selectionEnd >= 0) ? el.selectionEnd : start;

        var newVal = val.substring(0, start) + tagText + val.substring(end);
        el.value = newVal;

        var newPos = start + tagText.length;
        if (typeof el.setSelectionRange === 'function') {
            el.setSelectionRange(newPos, newPos);
        } else {
            el.selectionStart = newPos;
            el.selectionEnd = newPos;
        }
        el.lastPos = newPos;

        el.focus();
        $(el).trigger('input').trigger('change');
    }
}

// ── Universal Click Handler for Placeholder Tags ───────────────────────
$(document).on('click', '.placeholder-tag, #offer-letter-settings p, #joining-letter-settings p, #experience-certificate-settings p, #noc-settings p, #offer-letter-settings span, #joining-letter-settings span, #experience-certificate-settings span, #noc-settings span', function(e) {
    var rawText = $(this).attr('data-placeholder') || $(this).text() || '';
    var match = rawText.match(/\{[a-zA-Z0-9_]+\}/);
    if (!match) return;
    e.preventDefault();
    insertPlaceholderTag(match[0], this);
});

// ── Smooth Scroll & Sidebar Tab Activation Handler for URL Hash Navigation ──────
function handleSettingsHashScroll() {
    var hash = window.location.hash;
    if (hash) {
        var $tabLink = $('#useradd-sidenav a[href="' + hash + '"]');
        if ($tabLink.length) {
            $('#useradd-sidenav a').removeClass('active');
            $tabLink.addClass('active');
        }
        var $targetSec = $(hash);
        if ($targetSec.length) {
            setTimeout(function() {
                $('html, body').animate({
                    scrollTop: $targetSec.offset().top - 75
                }, 400);
            }, 150);
        }
    }
}

$(document).ready(function() {
    handleSettingsHashScroll();
});
$(window).on('hashchange', function() {
    handleSettingsHashScroll();
});
</script>
@endpush
