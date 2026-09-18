<style>
    .addBtn {
        float: right;
    }

    /* Document Preview Card Styles */
    .doc-preview-area {
        min-height: 30px;
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        align-items: center;
        margin-top: 10px;
    }
    .doc-card-item {
        position: relative;
        width: 120px;
        height: 85px;
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        background: #ffffff;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        overflow: visible !important;
        transition: all 0.2s ease-in-out;
    }
    .doc-card-item:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        border-color: #cbd5e1;
    }
    .doc-card-img-wrapper {
        width: 100%;
        height: 100%;
        border-radius: 8px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f8fafc;
    }
    .doc-card-img-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.25s ease;
    }
    .doc-card-item:hover .doc-card-img-wrapper img {
        transform: scale(1.06);
    }
    .doc-card-file-wrapper {
        width: 100%;
        height: 100%;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 5px 6px;
        text-align: center;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        text-decoration: none !important;
        position: relative;
        transition: all 0.2s ease-in-out;
    }
    .doc-card-file-wrapper:hover {
        background: #f1f5f9;
        text-decoration: none !important;
    }
    .doc-card-file-icon {
        font-size: 1.65rem;
        line-height: 1;
        margin-bottom: 3px;
        transition: transform 0.2s ease;
    }
    .doc-card-item:hover .doc-card-file-icon {
        transform: scale(1.1);
    }
    .doc-card-file-badge {
        font-size: 0.55rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        padding: 1px 5px;
        border-radius: 3px;
        text-transform: uppercase;
        line-height: 1.2;
    }
    .doc-card-file-name {
        font-size: 0.65rem;
        color: #334155;
        font-weight: 600;
        max-width: 105px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
        margin-top: 3px;
    }

    /* Extension Specific Color Badges & Icons */
    .doc-type-pdf .doc-card-file-icon { color: #dc2626; }
    .doc-type-pdf .doc-card-file-badge { background: #fee2e2; color: #b91c1c; }

    .doc-type-word .doc-card-file-icon { color: #2563eb; }
    .doc-type-word .doc-card-file-badge { background: #dbeafe; color: #1d4ed8; }

    .doc-type-excel .doc-card-file-icon { color: #16a34a; }
    .doc-type-excel .doc-card-file-badge { background: #dcfce7; color: #15803d; }

    .doc-type-csv .doc-card-file-icon { color: #0d9488; }
    .doc-type-csv .doc-card-file-badge { background: #ccfbf1; color: #0f766e; }

    .doc-type-ppt .doc-card-file-icon { color: #ea580c; }
    .doc-type-ppt .doc-card-file-badge { background: #ffedd5; color: #c2410c; }

    .doc-type-archive .doc-card-file-icon { color: #9333ea; }
    .doc-type-archive .doc-card-file-badge { background: #f3e8ff; color: #7e22ce; }

    .doc-type-text .doc-card-file-icon { color: #64748b; }
    .doc-type-text .doc-card-file-badge { background: #f1f5f9; color: #475569; }

    .doc-type-code .doc-card-file-icon { color: #0284c7; }
    .doc-type-code .doc-card-file-badge { background: #e0f2fe; color: #0369a1; }

    .doc-type-default .doc-card-file-icon { color: #64748b; }
    .doc-type-default .doc-card-file-badge { background: #f1f5f9; color: #475569; }
</style>
@extends('layouts.admin')
@section('page-title')
{{ __('Create Employee') }}
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
<li class="breadcrumb-item"><a href="{{ url('employee') }}">{{ __('Employee') }}</a></li>
<li class="breadcrumb-item">{{ __('Create Employee') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="">
        <div class="">
            <div class="row">

            </div>
            {{ Form::open(['route' => ['employee.store'], 'method' => 'post', 'enctype' => 'multipart/form-data', 'class' => 'employee-create-form']) }}
            <div class="row">
                <div class="col-md-6">
                    <div class="card em-card">
                        <div class="card-header">
                            <h5>{{ __('Personal Detail') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('name', __('First Name'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::text('fname', old('name'), [
                                    'class' => 'form-control',
                                    'required' => 'required',
                                    'placeholder' => 'Enter Employee First Name',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('name', __('Last Name'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::text('lname', old('name'), [
                                    'class' => 'form-control',
                                    'required' => 'required',
                                    'placeholder' => 'Enter Employee Last Name',
                                    ]) !!}
                                </div>
                                <x-mobile divClass="col-md-6" name="phone" label="{{ __('Phone') }}"
                                    placeholder="{{ __('Enter employee phone') }}" id="phone" required="true">
                                </x-mobile>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('dob', __('Date of Birth'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>

                                        {{ Form::date('dob', old('dob'),
                                            [
                                                'class' => 'form-control w-100',
                                                'required',
                                                'autocomplete' => 'off',
                                                'placeholder' => 'Select Date of Birth',
                                            ])
                                        }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('gender', __('Gender'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        <div class="d-flex radio-check">
                                            <div class="custom-control custom-radio custom-control-inline">
                                                <input type="radio" id="g_male" value="Male" name="gender"
                                                    class="form-check-input" required {{ old('gender') == 'Male' ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="g_male">{{ __('Male') }}</label>
                                            </div>
                                            <div class="custom-control custom-radio ms-1 custom-control-inline">
                                                <input type="radio" id="g_female" value="Female" name="gender"
                                                    class="form-check-input" {{ old('gender') == 'Female' ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="g_female">{{ __('Female') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('email', __('Email'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::email('email', old('email'), [
                                    'class' => 'form-control',
                                    'required' => 'required',
                                    'placeholder' => 'Enter employee email',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('password', __('Password'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::password('password', [
                                    'class' => 'form-control',
                                    'required' => 'required',
                                    'placeholder' => 'Enter employee new password',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-12">
                                    {!! Form::label('address', __('Address'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::textarea('address', old('address'), [
                                    'class' => 'form-control',
                                    'rows' => 2,
                                    'required' => 'required',
                                    'placeholder' => 'Enter employee address',
                                    ]) !!}
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card em-card">
                        <div class="card-header">
                            <h5>{{ __('Company Detail') }}</h5>
                        </div>
                        <div class="card-body employee-detail-create-body">
                            <div class="row">
                                @csrf
                                <div class="form-group col-md-6">
                                    {!! Form::label('employee_id', __('Employee ID'), ['class' => 'form-label']) !!}
                                    {!! Form::text('employee_id', $employeesId, ['class' => 'form-control', 'disabled' => 'disabled']) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('company_doj', __('Date Of Joining'), ['class'=> 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {{ Form::date('company_doj', old('company_doj'), [
                                        'class' => 'form-control w-100',
                                        'required',
                                        'autocomplete' => 'off',
                                        'placeholder' => 'Select company date of joining',
                                    ]) }}
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('branch_id', __('Select Branch'), ['class' => 'form-label']) }}<span
                                        class="text-danger pl-1">*</span>
                                    <a href="javascript:void(0)" data-title="{{ __('Create New Branch') }}"
                                        onclick="modalShow([{'name' : ''}], 'create-branch', 'Create Branch','branch')"
                                        data-bs-toggle="tooltip" title="{{ __('Create New Branch') }}"
                                        class="btn btn-sm btn-primary addBtn"
                                        data-bs-original-title="{{ __('Create') }}">
                                        <i class="ti ti-plus"></i>
                                    </a>
                                    <div class="form-icon-user">
                                        {{ Form::select('branch_id', $branches, old('branch_id'), ['class' => 'form-control branch_id', 'required' => 'required', 'placeholder' => 'Select Branch']) }}
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    {{ Form::label('department_id', __('Select Department'), ['class' => 'form-label']) }}<span
                                        class="text-danger pl-1">*</span>
                                    <a href="javascript:void(0)" data-title="{{ __('Create New Department') }}"
                                        onclick="modalShow([{'branch': '{{ $branches }}'}, {'name' : ''}, {'slug' : ''}], 'create-department','Create Department','department')"
                                        data-bs-toggle="tooltip" title="{{ __('Create New Department') }}"
                                        class="btn btn-sm btn-primary addBtn "
                                        data-bs-original-title="{{ __('Create') }}">
                                        <i class="ti ti-plus"></i>
                                    </a>
                                    <div class="form-icon-user">
                                        {{ Form::select('department_id', $departments, old('department_id'), ['class' => 'form-control department_id', 'id' => 'department_id', 'required' => 'required', 'placeholder' => 'Select Department']) }}
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    {{ Form::label('subdepartment_id', __('Select Sub Department'), ['class' => 'form-label']) }}<span
                                        class="text-danger pl-1">*</span>
                                    <a href="javascript:void(0)" data-title="{{ __('Create New Sub Department') }}"
                                        onclick="modalShow([{'department': '{{ $departments }}'}, {'name' : ''}], 'create-subDepartment','Create Sub Department','subdepartment')"
                                        data-bs-toggle="tooltip" title="{{ __('Create New Sub Department') }}"
                                        class="btn btn-sm btn-primary addBtn "
                                        data-bs-original-title="{{ __('Create') }}">
                                        <i class="ti ti-plus"></i>
                                    </a>
                                    <div class="form-icon-user">
                                        {{ Form::select('subdepartment_id', $subdepartments, old('subdepartment_id'), ['class' => 'form-control subdepartment_id', 'id' => 'subdepartment_id', 'required' => 'required', 'placeholder' => 'Select Sub Department']) }}
                                    </div>
                                </div>

                                <div class="form-group col-md-6 ">
                                    {{ Form::label('designation_id', __('Select Designation'), ['class' => 'form-label']) }}
                                    <a href="javascript:void(0)" data-title="{{ __('Create New Designation') }}"
                                        onclick="modalShow([{'department': '{{ $departments }}'}, {'name' : ''}], 'create-designation','Create Designation','designation')"
                                        data-bs-toggle="tooltip" title="{{ __('Create New Designation') }}"
                                        class="btn btn-sm btn-primary addBtn"
                                        data-bs-original-title="{{ __('Create') }}">
                                        <i class="ti ti-plus"></i>
                                    </a>
                                    <div class="form-icon-user">
                                        {{ Form::select('designation_id', $designations, old('designation_id'), ['class' => 'form-control designation_id', 'id' => 'designation_id', 'required' => 'required', 'placeholder' => 'Select Designation']) }}

                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('employment_type_id', __('Employment Type'), ['class' => 'form-label']) }}
                                    <a href="javascript:void(0)" data-title="{{ __('Create New Employment Type') }}"
                                        onclick="modalShow([{'name' : ''}], 'create-employmenttype', 'Create Employment Type','employmenttype')"
                                        data-bs-toggle="tooltip" title="{{ __('Create New Employment Type') }}"
                                        class="btn btn-sm btn-primary addBtn"
                                        data-bs-original-title="{{ __('Create') }}">
                                        <i class="ti ti-plus"></i>
                                    </a>
                                    <div class="form-icon-user">
                                        <div class="employmenttype_div">
                                            {{ Form::select('employment_type_id', $employmentTypes ?? [], old('employment_type_id'), ['class' => 'form-control employment_type_id', 'placeholder' => 'Select Employment Type']) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('shift_id', __('Select Shift'), ['class' => 'form-label']) }}<span
                                        class="text-danger pl-1">*</span>
                                    <a href="javascript:void(0)" data-title="{{ __('Create New  Shift') }}"
                                        onclick="modalShow([{'name' : ''}], 'create-shift', 'Create Shift','shift')"
                                        data-bs-toggle="tooltip" title="{{ __('Create New  Shift') }}"
                                        class="btn btn-sm btn-primary addBtn"
                                        data-bs-original-title="{{ __('Create') }}">
                                        <i class="ti ti-plus"></i>
                                    </a>

                                    <div class="form-icon-user">
                                        {{ Form::select('shift_id', $shift, old('shift_id'), ['class' => 'form-control shift_id', 'id' => 'shift_id', 'required' => 'required', 'placeholder' => 'Select Shift']) }}

                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    {{ Form::label('company_shift_time', __('Select Shift Time'), ['class' => 'form-label']) }}
                                    <span class="text-danger pl-1">*</span>

                                    <div class="form-icon-user">
                                        @php
                                            $shiftOptions = [];

                                            if (!empty($company_shifts) && is_array($company_shifts)) {
                                                foreach ($company_shifts as $index => $shift) {
                                                    $name = !empty($shift['name']) ? $shift['name'] : (!empty($shift['title']) ? $shift['title'] : 'Shift ' . ($index + 1));
                                                    $start = $shift['start'] ?? '';
                                                    $end = $shift['end'] ?? '';
                                                    $timeStr = ($start && $end) ? " ({$start} - {$end})" : "";

                                                    $colorDot = '🟣 ';
                                                    if (!empty($shift['color'])) {
                                                        $c = strtolower(ltrim($shift['color'], '#'));
                                                        if (strlen($c) === 6) {
                                                            $r = hexdec(substr($c, 0, 2));
                                                            $g = hexdec(substr($c, 2, 2));
                                                            $b = hexdec(substr($c, 4, 2));
                                                            if ($r > 180 && $g < 100 && $b < 100) { $colorDot = '🔴 '; }
                                                            elseif ($g > 140 && $r < 140 && $b < 140) { $colorDot = '🟢 '; }
                                                            elseif ($b > 140 && $r < 140) { $colorDot = '🔵 '; }
                                                            elseif ($r > 200 && $g > 160 && $b < 100) { $colorDot = '🟡 '; }
                                                            elseif ($r > 200 && $g > 100 && $b < 80) { $colorDot = '🟠 '; }
                                                            elseif ($r > 100 && $b > 100 && $g < 120) { $colorDot = '🟣 '; }
                                                            elseif ($r < 80 && $g < 80 && $b < 80) { $colorDot = '⚫ '; }
                                                        }
                                                    }

                                                    $label = $colorDot . $name . $timeStr;
                                                    $shiftOptions[$index] = $label;
                                                }
                                            }

                                            $selectedShiftIndex = old('company_shift_time', null);
                                        @endphp

                                        @if(!empty($shiftOptions))
                                            {{ Form::select(
                                                'company_shift_time',
                                                $shiftOptions,
                                                $selectedShiftIndex,
                                                [
                                                    'class' => 'form-control',
                                                    'id' => 'company_shift_time',
                                                    'placeholder' => __('Select Shift Time'),
                                                    'onchange' => 'loadShiftBreakTimes()',
                                                    'required' => 'required'
                                                ]
                                            ) }}
                                        @else
                                            <div class="alert alert-info mt-2">
                                                {{ __('Please set company shifts in Company Settings first.') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>


                                <!--- Refresh Break Time --->
                                <div class="form-group col-md-6">
                                    {{ Form::label('refresh_type', __('Refresh Break Time'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                                    {{ Form::select('refresh_type', ['fixed' => 'Fixed Time', 'flexible' => 'Flexible Time'], old('refresh_type', 'fixed'), ['class' => 'form-control', 'id' => 'refresh_break_type', 'onchange' => 'toggleShiftBreakType()', 'required' => 'required']) }}
                                </div>

                                <!-- Refresh Break Fixed Time Fields (Legacy) -->
                                <!-- <div class="form-group col-md-6" id="refreshStartDiv" style="display:none;">
                                    {{ Form::label('refresh_start', __('Refresh Start Time'), ['class' => 'form-label']) }}
                                    {{ Form::time('refresh_start', old('refresh_start', null), ['class' => 'form-control', 'id' => 'refresh_start']) }}
                                </div>

                                <div class="form-group col-md-6" id="refreshEndDiv" style="display:none;">
                                    {{ Form::label('refresh_end', __('Refresh End Time'), ['class' => 'form-label']) }}
                                    {{ Form::time('refresh_end', old('refresh_end', null), ['class' => 'form-control', 'id' => 'refresh_end']) }}
                                </div> -->

                                <!-- Lunch Time Fields -->
                                <div class="form-group col-md-6" id="lunchTimeDiv" style="display:none;">
                                    {{ Form::label('lunch_start', __('Lunch Start Time'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                                    {{ Form::time('lunch_start', old('lunch_start',null), ['class' => 'form-control', 'id' => 'lunch_start']) }}
                                </div>

                                <div class="form-group col-md-6" id="lunchEndDiv" style="display:none;">
                                    {{ Form::label('lunch_end', __('Lunch End Time'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                                    {{ Form::time('lunch_end', old('lunch_end', null), ['class' => 'form-control', 'id' => 'lunch_end']) }}
                                </div>

                                <div class="form-group col-md-6" id="lunchMinDiv" style="display:none;">
                                    {{ Form::label('lunch_minutes', __('Lunch Minutes'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                                    {{ Form::number('lunch_minutes', null, ['class' => 'form-control', 'id' => 'lunch_minutes', 'min' => 0]) }}
                                </div>

                                <!-- Tea Time Fields -->
                                <div class="form-group col-md-6" id="teaTimeDiv" style="display:none;">
                                    {{ Form::label('tea_start', __('Tea Start Time'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                                    {{ Form::time('tea_start', old('tea_start', null), ['class' => 'form-control', 'id' => 'tea_start']) }}
                                </div>

                                <div class="form-group col-md-6" id="teaEndDiv" style="display:none;">
                                    {{ Form::label('tea_end', __('Tea End Time'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                                    {{ Form::time('tea_end', old('tea_end', null), ['class' => 'form-control', 'id' => 'tea_end']) }}
                                </div>

                                <div class="form-group col-md-6" id="teaMinDiv" style="display:none;">
                                    {{ Form::label('tea_minutes', __('Tea Minutes'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                                    {{ Form::number('tea_minutes', null, ['class' => 'form-control', 'id' => 'tea_minutes', 'min' => 0]) }}
                                </div>


                                <div class="form-group col-md-12">
                                    <div class="row">

                                        <!-- @php
                                            $refreshType = $company_settings['refreshtime'] ?? 'fixed';
                                        @endphp
                                        @if($refreshType === 'fixed')
                                            <div class="col-md-3 p-2">
                                                <div class="form-group">
                                                    {{ Form::label('refresh_start', __('Refresh Start'), ['class' => 'form-label']) }}
                                                    {{ Form::time('refresh_start', old('refresh_start'), ['class' => 'form-control', 'required']) }}
                                                </div>
                                            </div>
                                            <div class="col-md-3 p-2">
                                                <div class="form-group">
                                                    {{ Form::label('refresh_end', __('Refresh End'), ['class' => 'form-label']) }}
                                                    {{ Form::time('refresh_end', old('refresh_end'), ['class' => 'form-control', 'required']) }}
                                                </div>
                                            </div>
                                        @else
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('refresh_minutes', __('Refresh Minutes'), ['class' => 'form-label']) }}
                                                    {{ Form::number('refresh_minutes', old('refresh_minutes', 0), ['class' => 'form-control', 'required', 'min' => 0]) }}
                                                </div>
                                            </div>
                                        @endif -->
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 ">
                    <div class="card em-card">
                        <div class="card-header">
                            <h5>{{ __('Document') }}</h6>
                        </div>
                        <div class="card-body employee-detail-create-body">
                            @foreach ($documents as $key => $document)
                            @php
                                $previewId = 'prev_' . $document->id . '_' . $key;
                                $docType   = $document->document_type ?? 'file';
                            @endphp
                            <div class="row mb-2">
                                <div class="form-group col-12">
                                    <label class="form-label">
                                        {{ $document->name }}
                                        @if ($document->is_required == 1)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>
                                    <input type="hidden" name="emp_doc_id[{{ $document->id }}]" value="{{ $document->id }}">

                                    {{-- TEXT field --}}
                                    @if ($docType === 'text' || $docType === 'both')
                                        <input type="text"
                                            class="form-control mb-2"
                                            name="document_text[{{ $document->id }}]"
                                            id="document_text_{{ $document->id }}"
                                            placeholder="{{ __('Enter') }} {{ $document->name }}"
                                            @if ($document->is_required == 1 && $docType === 'text') required @endif>
                                    @endif

                                    {{-- FILE field --}}
                                    @if ($docType === 'file' || $docType === 'both')
                                        <div class="choose-files">
                                            <label for="document[{{ $document->id }}]">
                                                <div class="bg-primary document">
                                                    <i class="ti ti-upload"></i>{{ __('Choose file here') }}
                                                </div>
                                                <input type="file"
                                                    class="form-control file d-none @error('document') is-invalid @enderror"
                                                    name="document[{{ $document->id }}]"
                                                    id="document[{{ $document->id }}]"
                                                    data-filename="{{ $document->id . '_filename' }}"
                                                    data-preview-id="{{ $previewId }}"
                                                    onchange="handleDocPreview(this)">
                                            </label>
                                            <div id="{{ $previewId }}" class="doc-preview-area mt-2"></div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-md-6 ">
                    <div class="card em-card">
                        <div class="card-header">
                            <h5>{{ __('Bank Account Detail') }}</h5>
                        </div>
                        <div class="card-body employee-detail-create-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('account_holder_name', __('Account Holder Name'), ['class' => 'form-label']) !!}
                                    {!! Form::text('account_holder_name', old('account_holder_name'), [
                                    'class' => 'form-control',
                                    'placeholder' => 'Enter account holder name',
                                    ]) !!}

                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('account_number', __('Account Number'), ['class' => 'form-label']) !!}
                                    {!! Form::number('account_number', old('account_number'), [
                                    'class' => 'form-control',
                                    'placeholder' => 'Enter account number',
                                    ]) !!}

                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('bank_name', __('Bank Name'), ['class' => 'form-label']) !!}
                                    {!! Form::text('bank_name', old('bank_name'), ['class' => 'form-control', 'placeholder' => 'Enter bank name']) !!}

                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('bank_identifier_code', __(\App\Models\Utility::bankCodeLabel()), ['class' => 'form-label']) !!}
                                    {!! Form::text('bank_identifier_code', old('bank_identifier_code'), [
                                    'class' => 'form-control',
                                    'placeholder' => 'Enter ' . \App\Models\Utility::bankCodeLabel(),
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('branch_location', __('Branch Location'), ['class' => 'form-label']) !!}
                                    {!! Form::text('branch_location', old('branch_location'), [
                                    'class' => 'form-control',
                                    'placeholder' => 'Enter branch location',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('tax_payer_id', __('Tax Payer/PAN Id'), ['class' => 'form-label']) !!}
                                    {!! Form::text('tax_payer_id', old('tax_payer_id'), [
                                    'class' => 'form-control',
                                    'placeholder' => 'Enter tax payer id',
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Leave Type Assignment ─────────────────────────────────────────── --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="card em-card">
                        <div class="card-header">
                            <h5>{{ __('Assign Leave Types') }}</h5>
                        </div>
                        <div class="card-body table-border-style">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width:50px;">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="select_all_leave_types">
                                                </div>
                                            </th>
                                            <th>{{ __('Leave Type') }}</th>
                                            <th class="text-center" style="width:120px;">{{ __('Paid/Unpaid') }}</th>
                                            <th class="text-center" style="width:150px;">{{ __('Total Days') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($leaveTypes as $leaveType)
                                            @php
                                                $oldChecked = old('leave_types.' . $leaveType->id . '.checked');
                                                $oldDays = old('leave_types.' . $leaveType->id . '.days', $leaveType->days);
                                                $oldIsPaid = old('leave_types.' . $leaveType->id . '.is_paid', $leaveType->is_paid ? '1' : null);
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="form-check">
                                                        <input class="form-check-input leave-type-check" type="checkbox"
                                                            name="leave_types[{{ $leaveType->id }}][checked]"
                                                            value="1"
                                                            id="leave_type_{{ $leaveType->id }}"
                                                            {{ $oldChecked ? 'checked' : '' }}>
                                                    </div>
                                                </td>
                                                <td>
                                                    <label for="leave_type_{{ $leaveType->id }}" class="mb-0">
                                                        {{ $leaveType->title }}
                                                    </label>
                                                </td>
                                                <td class="text-center">
                                                    <div class="form-check form-switch d-flex justify-content-center mb-0">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="leave_types[{{ $leaveType->id }}][is_paid]"
                                                            value="1"
                                                            id="is_paid_{{ $leaveType->id }}"
                                                            {{ $oldIsPaid ? 'checked' : '' }}>
                                                        <label class="form-check-label ms-2 small" for="is_paid_{{ $leaveType->id }}" id="is_paid_label_{{ $leaveType->id }}">
                                                            {{ $oldIsPaid ? __('Paid') : __('Unpaid') }}
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.5" min="0"
                                                        class="form-control text-center"
                                                        name="leave_types[{{ $leaveType->id }}][days]"
                                                        value="{{ $oldDays }}"
                                                        placeholder="{{ $leaveType->days }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="float-end">
                <button type="submit" class="btn  btn-primary">{{ 'Create' }}</button>
            </div>
        </div>

        </form>
    </div>
</div>
<div class="modal fade" id="staticBackdrop" data-backdrop="static" data-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel"></h5>
                {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button> --}}
            </div>
            <form id="myForm">
                <div class="modal-body" id="newModalBody">

                </div>
            </form>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Close</button>
                <button type="button" class="btn btn-primary" onclick="submitForm()">Submit</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('script-page')
@include('layouts.dateformat')
<script>
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            const modal = document.querySelector('.modal.show');

            if (modal) {
                submitForm();
            } else {
                const mainForm = document.querySelector('.employee-create-form');
                if (mainForm) {
                    mainForm.querySelector('button[type="submit"]')?.click();
                }
            }
        }
    });

    $('input[type="file"]').change(function(e) {
        var file = e.target.files[0].name;
        var file_name = $(this).attr('data-filename');
        $('.' + file_name).append(file);
    });
</script>
<script>
    $(document).ready(function() {
        var d_id = $('.department_id').val();
        getDesignation(d_id);
    });

    $(document).on('change', 'select[name=department_id]', function() {
        var department_id = $(this).val();
        getDesignation(department_id);
    });

    function getDesignation(did) {

        $.ajax({
            url: '{{ route('employee.json') }}',
            type: 'POST',
            data: {
                "department_id": did,
                "_token": "{{ csrf_token() }}",
            },
            success: function(data) {

                $('.designation_id').empty();
                var emp_selct = ` <select class="form-control  designation_id" name="designation_id" id="choices-multiple"
                                            placeholder="Select Designation" >
                                            </select>`;
                $('.designation_div').html(emp_selct);

                $('.designation_id').append('<option value="0"> {{ __('All') }} </option>');
                $.each(data, function(key, value) {
                    $('.designation_id').append('<option value="' + key + '">' + value +
                        '</option>');
                });
                new Choices('#choices-multiple', {
                    removeItemButton: true,
                });


            }
        });
    }
</script>
<script>
    $('#department_id').change(function() {
        var department_id = $(this).val();
        $.ajax({
            url: '{{ route('employee.sub') }}',
            type: "post",
            data: {
                '_token': '{{ csrf_token() }}',
                'department_id': department_id
            },
            success: function(response) {
                $('#subdepartment_id').html(response)
            }
        })
    })
      // Load shift break times based on selected shift
        const companyShifts = @json($company_shifts ?? []);

        function loadShiftBreakTimes() {
            const selectElement = document.getElementById('company_shift_time');
            if (!selectElement) return;
            const selectedIndex = selectElement.value;

            if (selectedIndex === '' || !companyShifts[selectedIndex]) {
                return;
            }

            const shift = companyShifts[selectedIndex];

            if (shift.type) {
                const refreshBreakType = document.getElementById('refresh_break_type');
                if (refreshBreakType) {
                    refreshBreakType.value = shift.type;
                }
            }

            if (shift.lunch_start && document.getElementById('lunch_start')) {
                document.getElementById('lunch_start').value = shift.lunch_start;
            }
            if (shift.lunch_end && document.getElementById('lunch_end')) {
                document.getElementById('lunch_end').value = shift.lunch_end;
            }
            if (shift.tea_start && document.getElementById('tea_start')) {
                document.getElementById('tea_start').value = shift.tea_start;
            }
            if (shift.tea_end && document.getElementById('tea_end')) {
                document.getElementById('tea_end').value = shift.tea_end;
            }
            if (shift.lunch_minutes && document.getElementById('lunch_minutes')) {
                document.getElementById('lunch_minutes').value = shift.lunch_minutes;
            }
            if (shift.tea_minutes && document.getElementById('tea_minutes')) {
                document.getElementById('tea_minutes').value = shift.tea_minutes;
            }

            toggleShiftBreakType();
        }

        // Toggle shift break type fields (lunch and tea)
        function toggleShiftBreakType() {
            const breakTypeEl = document.getElementById('refresh_break_type');
            if (!breakTypeEl) return;
            let breakType = breakTypeEl.value;
            if (!breakType) {
                breakType = 'fixed';
                breakTypeEl.value = 'fixed';
            }

            const lunchStart = document.getElementById('lunchTimeDiv');
            const lunchEnd = document.getElementById('lunchEndDiv');
            const lunchMin = document.getElementById('lunchMinDiv');
            const teaStart = document.getElementById('teaTimeDiv');
            const teaEnd = document.getElementById('teaEndDiv');
            const teaMin = document.getElementById('teaMinDiv');

            if (breakType === 'fixed') {
                // Show time fields, hide minute fields
                if (lunchStart) lunchStart.style.display = 'block';
                if (lunchEnd) lunchEnd.style.display = 'block';
                if (lunchMin) lunchMin.style.display = 'none';
                if (teaStart) teaStart.style.display = 'block';
                if (teaEnd) teaEnd.style.display = 'block';
                if (teaMin) teaMin.style.display = 'none';
            } else if (breakType === 'flexible') {
                // Show minute fields, hide time fields
                if (lunchStart) lunchStart.style.display = 'none';
                if (lunchEnd) lunchEnd.style.display = 'none';
                if (lunchMin) lunchMin.style.display = 'block';
                if (teaStart) teaStart.style.display = 'none';
                if (teaEnd) teaEnd.style.display = 'none';
                if (teaMin) teaMin.style.display = 'block';
            } else {
                // Hide all if not set
                if (lunchStart) lunchStart.style.display = 'none';
                if (lunchEnd) lunchEnd.style.display = 'none';
                if (lunchMin) lunchMin.style.display = 'none';
                if (teaStart) teaStart.style.display = 'none';
                if (teaEnd) teaEnd.style.display = 'none';
                if (teaMin) teaMin.style.display = 'none';
            }
        }

        // Toggle refresh break type fields
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadShiftBreakTimes();
            toggleShiftBreakType();
        });

        $(document).on('change', '#refresh_break_type', function() {
            toggleShiftBreakType();
        });
    // }
</script>
<script>
    var globalRoute = '';
    var html = "";
    var globalBranch = '';
    var globalDepartment = '';
    var globalSubDepartment = '';
    var globalDesignation = '';
    var globalShift = '';
    var newData = "";

    function modalShow(data, route, title, section) {
        html = "";
        globalRoute = route;
        newData = "";
        switch (section) {
            case 'department':
                getData('get-branch');
                break;
            case 'subdepartment':
                getData('get-department');
                break;
            case 'designation':
                getData('get-department');
                break;
            default:
                break;
        }
        $('#staticBackdropLabel').text(title);

        html += '<div class="row">';
        $.each(data, function(key, value) {
            $.each(value, function(key1, value1) {
                html += '<div class="col-lg-12 col-md-12 col-sm-12">\
                                                    <div class="form-group">\
                                                        <label class="form-label">' + key1.toUpperCase() + '</label>';

                if (value1 != '') {
                    html += '<div class="form-icon-user">\
                                                        <select class="form-control" name="' + key1 + '">';
                    value1 = JSON.parse(value1);
                    if (newData != '') {
                        value1 = newData;
                    }
                    $.each(value1, function(key2, value2) {
                        html += '<option value="' + key2 + '">' + value2 + '</option>';
                    })
                    html += '</select>\
                                                    </div>';
                } else {
                    html += '<div class="form-icon-user">\
                                                            <input name="' + key1 + '" id="' + key1 + '" placeholder="' +
                        key1
                        .toUpperCase() + '" class="form-control">\
                                                        </div>';
                }

                html += '   </div>\
                                                    </div>';
            })
        });
        html += '</div>';
        $('#newModalBody').html(html);
        $('#staticBackdrop').modal('show');
    }

    function submitForm() {
        var input = [];
        $("#myForm :input").each(function() {
            var value = $(this).val();
            var name = $(this).attr('name');
            input.push({
                'name': name,
                'value': value
            });
        });
        var url = "<?= url('') ?>/" + globalRoute;

        $.ajax({
            type: "post",
            url: url,
            data: input,
            success: function(response) {
                closeModal();
                response = JSON.parse(response);
                switch (response.section) {
                    case 'branch':
                        $('.branch_id').html(response.output);
                        break;
                    case 'department':
                        $('.department_id').html(response.output);
                        break;
                    case 'subdepartment':
                        $('.subdepartment_id').html(response.output);
                        break;
                    case 'designation':
                        $('.designation_id').html(response.output);
                        break;
                    case 'shift':
                        $('.shift_id').html(response.output);
                        break;
                    case 'employmenttype':
                        $('.employment_type_id').html(response.output);
                        break;
                    default:
                        break;
                }
            }
        });
    }

    function closeModal() {
        $('#staticBackdrop').modal('hide');
        html = "";
    }

    function getData(url) {
        var url = "<?= url('') ?>/" + url;
        $.ajax({
            type: "get",
            url: url,
            async: false,
            success: function(response) {
                newData = JSON.parse(response);
            }
        });
    }

    // Helper to resolve file type details (icon, badge, CSS class)
    function getDocFileInfo(fileName) {
        var ext = (fileName || '').split('.').pop().toLowerCase();
        var map = {
            'pdf':  { typeClass: 'doc-type-pdf',     icon: 'fas fa-file-pdf',        label: 'PDF' },
            'doc':  { typeClass: 'doc-type-word',    icon: 'fas fa-file-word',       label: 'DOC' },
            'docx': { typeClass: 'doc-type-word',    icon: 'fas fa-file-word',       label: 'DOCX' },
            'xls':  { typeClass: 'doc-type-excel',   icon: 'fas fa-file-excel',      label: 'XLS' },
            'xlsx': { typeClass: 'doc-type-excel',   icon: 'fas fa-file-excel',      label: 'XLSX' },
            'csv':  { typeClass: 'doc-type-csv',     icon: 'fas fa-file-csv',        label: 'CSV' },
            'ppt':  { typeClass: 'doc-type-ppt',     icon: 'fas fa-file-powerpoint', label: 'PPT' },
            'pptx': { typeClass: 'doc-type-ppt',     icon: 'fas fa-file-powerpoint', label: 'PPTX' },
            'txt':  { typeClass: 'doc-type-text',    icon: 'fas fa-file-alt',        label: 'TXT' },
            'rtf':  { typeClass: 'doc-type-text',    icon: 'fas fa-file-alt',        label: 'RTF' },
            'zip':  { typeClass: 'doc-type-archive', icon: 'fas fa-file-archive',    label: 'ZIP' },
            'rar':  { typeClass: 'doc-type-archive', icon: 'fas fa-file-archive',    label: 'RAR' },
            '7z':   { typeClass: 'doc-type-archive', icon: 'fas fa-file-archive',    label: '7Z' },
            'tar':  { typeClass: 'doc-type-archive', icon: 'fas fa-file-archive',    label: 'TAR' },
            'gz':   { typeClass: 'doc-type-archive', icon: 'fas fa-file-archive',    label: 'GZ' },
        };
        return map[ext] || { typeClass: 'doc-type-default', icon: 'fas fa-file', label: (ext ? ext.toUpperCase().substring(0, 4) : 'FILE') };
    }

    // Document preview handler — shows image preview or file-type card preview on file selection
    function handleDocPreview(input) {
        var previewId = input.getAttribute('data-preview-id');
        var previewArea = document.getElementById(previewId);
        if (!input.files || !input.files.length) return;

        previewArea.innerHTML = '';
        var imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];

        for (var i = 0; i < input.files.length; i++) {
            (function(file) {
                var fileName = file.name;
                var ext = fileName.split('.').pop().toLowerCase();

                var card = document.createElement('div');
                card.className = 'doc-card-item';

                if (imageExts.indexOf(ext) !== -1) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        card.innerHTML = '<div class="doc-card-img-wrapper">' +
                            '<img src="' + e.target.result + '" alt="' + fileName + '" title="' + fileName + '">' +
                            '</div>';
                        previewArea.appendChild(card);
                    };
                    reader.readAsDataURL(file);
                } else {
                    var info = getDocFileInfo(fileName);
                    card.innerHTML = '<div class="doc-card-file-wrapper ' + info.typeClass + '">' +
                        '<i class="' + info.icon + ' doc-card-file-icon"></i>' +
                        '<span class="doc-card-file-badge">' + info.label + '</span>' +
                        '<span class="doc-card-file-name" title="' + fileName + '">' + fileName + '</span>' +
                        '</div>';
                    previewArea.appendChild(card);
                }
            })(input.files[i]);
        }
    }

    // Select All leave types checkbox
    $(document).on('change', '#select_all_leave_types', function() {
        $('.leave-type-check').prop('checked', $(this).prop('checked'));
    });
    $(document).on('change', '.leave-type-check', function() {
        var total = $('.leave-type-check').length;
        var checked = $('.leave-type-check:checked').length;
        $('#select_all_leave_types').prop('checked', total === checked);
    });

    // Toggle Paid/Unpaid label text
    $(document).on('change', '[id^="is_paid_"]', function() {
        var id = $(this).attr('id');
        var labelId = id + '_label';
        // label id format: is_paid_label_X but our id is is_paid_X
        var leaveTypeId = id.replace('is_paid_', '');
        var $label = $('#is_paid_label_' + leaveTypeId);
        $label.text($(this).prop('checked') ? '{{ __("Paid") }}' : '{{ __("Unpaid") }}');
    });
</script>
@endpush
