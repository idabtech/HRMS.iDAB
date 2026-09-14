<style>
    .addBtn {
        float: right;
    }

    /* Uniform Document Gallery Styles */
    .doc-gallery-grid {
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
    .doc-card-remove-btn {
        position: absolute;
        top: -8px;
        right: -8px;
        width: 22px;
        height: 22px;
        background: #ef4444;
        color: #ffffff;
        border: 2px solid #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: bold;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        z-index: 10;
        line-height: 1;
        padding: 0;
        transition: transform 0.15s ease, background-color 0.15s ease;
    }
    .doc-card-remove-btn:hover {
        background: #dc2626;
        transform: scale(1.15);
    }
    .doc-card-file-wrapper {
        width: 100%;
        height: 100%;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 6px;
        text-align: center;
        background: #f1f5f9;
    }
    .doc-card-file-icon {
        font-size: 1.6rem;
        margin-bottom: 2px;
    }
    .doc-card-file-name {
        font-size: 0.68rem;
        color: #334155;
        font-weight: 500;
        max-width: 100px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
@extends('layouts.admin')

@php
    $company_settings = \App\Models\Utility::settings();
@endphp
@section('page-title')
{{ __('Edit Employee') }}
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
<li class="breadcrumb-item"><a href="{{ url('employee') }}">{{ __('Employee') }}</a></li>
<li class="breadcrumb-item">{{ __('Edit Employee') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="">
        <div class="">

            {{ Form::model($employee, ['route' => ['employee.update', $employee->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data', 'class' => 'employee-edit-form']) }}
            <div class="row">
                <div class="col-md-6 ">
                    <div class="card em-card">
                        <div class="card-header">
                            <h5>{{ __('Personal Detail') }}</h5>
                        </div>
                        <div class="card-body">

                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('name', __('First Name'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::text('name', old('name', $employee->name), ['class' => 'form-control', 'required' => 'required']) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('last_name', __('Last Name'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::text('last_name', old('last_name', $employee->last_name), ['class' => 'form-control', 'required' => 'required']) !!}
                                </div>
                                <x-mobile divClass="col-md-6" name="phone" label="{{ __('Phone') }}"
                                    placeholder="{{ __('Enter employee phone') }}" id="phone" required="true">
                                </x-mobile>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('dob', __('Date of Birth'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::date('dob',
                                        old('dob', !empty($employee->dob)
                                        ? \Carbon\Carbon::parse($employee->dob)->format('Y-m-d')
                                        : null),
                                        [
                                        'class' => 'form-control w-100',
                                        'required',
                                        'autocomplete' => 'off',
                                        'placeholder' => 'Select Date of Birth'

                                        ])
                                        !!}
                                    </div>
                                </div>
                                <div class="col-md-6 ">
                                    <div class="form-group ">
                                        {!! Form::label('gender', __('Gender'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        <div class="d-flex radio-check">
                                            <div class="custom-control custom-radio custom-control-inline">
                                                <input type="radio" id="g_male" value="Male" name="gender"
                                                    class="form-check-input" required
                                                    {{ old('gender', $employee->gender) == 'Male' ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="g_male">{{ __('Male') }}</label>
                                            </div>
                                            <div class="custom-control custom-radio ms-1 custom-control-inline">
                                                <input type="radio" id="g_female" value="Female" name="gender"
                                                    class="form-check-input"
                                                    {{ old('gender', $employee->gender) == 'Female' ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="g_female">{{ __('Female') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group ">
                                        {!! Form::label('email', __('Email'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::email('email', old('email', $employee->email), [
                                        'class' => 'form-control',
                                        'required' => 'required',
                                        'placeholder' => 'Enter employee email',
                                        ]) !!}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('passcode', __('Passcode'), ['class' => 'form-label']) !!}
                                        {!! Form::text('passcode', old('passcode', $employee->user->passcode ?? ''), [
                                        'class' => 'form-control',
                                        'placeholder' => __('Enter employee passcode'),
                                        ]) !!}
                                    </div>
                                </div>
                                <div class="form-group col-md-12">
                                    {{ Form::label('address', __('Address'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                                    {{ Form::textarea('address', old('address', $employee->address), ['class' => 'form-control' ,'placeholder'=>__('Enter address'),'rows'=>'3']) }}
                                </div>
                                 <!-- <div class="col-md-6">
                                    <div class="form-group ">
                                        {!! Form::label('address', __('Address'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::textarea('address', null, ['class' => 'form-control', 'required' => 'required', 'rows' => 2]) !!}
                                    </div>
                                </div> -->

                            </div>

                            @if (\Auth::user()->type == 'employee')
                            {{-- {!! Form::submit('Update', ['class' => 'btn  btn-primary float-right']) !!} --}}
                            @endif
                        </div>
                    </div>
                </div>
                @if (\Auth::user()->type != 'employee')
                <div class="col-md-6 ">
                        <div class="card em-card">
                            <div class="card-header">
                                <h5>{{ __('Company Detail') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        {!! Form::label('employee_id', __('Employee ID'), ['class' => 'form-label']) !!}
                                        {!! Form::text('employee_id', $employeesId, ['class' => 'form-control', 'disabled' => 'disabled']) !!}
                                    </div>
                                    <div class=" form-group col-md-6">
                                        {!! Form::label('company_doj', __('Date Of Joining'), ['class'=> 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {{ Form::date('company_doj', old('company_doj', !empty($employee->company_doj) ? \Carbon\Carbon::parse($employee->company_doj)->format('Y-m-d') : null), [
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
                                        {{ Form::select('branch_id', $branches, old('branch_id', $employee->branch_id), ['class' => 'form-control branch_id', 'required' => 'required', 'placeholder' => 'Select Branch']) }}
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
                                        {{ Form::select('department_id', $departments, old('department_id', $employee->department_id), ['class' => 'form-control department_id', 'id' => 'department_id', 'required' => 'required']) }}
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
                                        {{ Form::select('subdepartment_id', $subdepartments, old('subdepartment_id', $employee->subdepartment_id), ['class' => 'form-control subdepartment_id', 'id' => 'subdepartment_id', 'required' => 'required', 'placeholder' => 'Select Sub Department']) }}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {{ Form::label('designation_id', __('Select Designation'), ['class' => 'form-label']) }}
                                        <a href="javascript:void(0)" data-title="{{ __('Create New Designation') }}"
                                            onclick="modalShow([{'department': '{{ $departments }}'}, {'name' : ''}], 'create-designation','Create Designation','designation')"
                                            data-bs-toggle="tooltip" title="{{ __('Create New Designation') }}"
                                            class="btn btn-sm btn-primary addBtn"
                                            data-bs-original-title="{{ __('Create') }}">
                                            <i class="ti ti-plus"></i>
                                        </a>


                                        <div class="form-icon-user">
                                            <div class="designation_div">
                                                <select class="form-control designation_id" name="designation_id"
                                                    id="choices-multiple" placeholder="Select Designation">
                                                </select>
                                            </div>
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
                                                {{ Form::select('employment_type_id', $employmentTypes ?? [], old('employment_type_id', $employee->employment_type_id ?? null), ['class' => 'form-control employment_type_id', 'placeholder' => 'Select Employment Type']) }}
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
                                            <div class="shift_div">
                                                {{ Form::select('shift_id', $shift, old('shift_id', $employee->shift_id), ['class' => 'form-control shift_id', 'required' => 'required', 'placeholder' => 'Select Shift']) }}
                                            </div>
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
                                                if ($selectedShiftIndex === null &&  is_array($company_shifts) && !empty($employee->company_start_time) && !empty($employee->company_end_time)) {
                                                    foreach ($company_shifts as $index => $shift) {
                                                        if ($shift['start'] === $employee->company_start_time &&
                                                            $shift['end'] === $employee->company_end_time) {
                                                            $selectedShiftIndex = $index;
                                                            break;
                                                        }
                                                    }
                                                }
                                            @endphp

                                            @if(!empty($shiftOptions))
                                                {{ Form::select('company_shift_time', $shiftOptions, $selectedShiftIndex, [
                                                    'class' => 'form-control',
                                                    'id' => 'company_shift_time',
                                                    'placeholder' => __('Select Shift Time'),
                                                    'onchange' => 'loadShiftBreakTimes()'
                                                ]) }}
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
                                        {{ Form::select('refresh_type', ['fixed' => 'Fixed Time', 'flexible' => 'Flexible Time'], old('refresh_type', $employee->refresh_type ?? 'fixed'), ['class' => 'form-control', 'id' => 'refresh_break_type', 'onchange' => 'toggleShiftBreakType()', 'required' => 'required']) }}
                                    </div>

                                    <!-- Refresh Break Fixed Time Fields -->
                                    <!-- <div class="form-group col-md-6" id="refreshStartDiv" style="display:none;">
                                        {{ Form::label('refresh_start', __('Refresh Start Time'), ['class' => 'form-label']) }}
                                        {{ Form::time('refresh_start', old('refresh_start', $employee->refresh_start), ['class' => 'form-control', 'id' => 'refresh_start']) }}
                                    </div> -->

                                    <!-- <div class="form-group col-md-6" id="refreshEndDiv" style="display:none;">
                                        {{ Form::label('refresh_end', __('Refresh End Time'), ['class' => 'form-label']) }}
                                        {{ Form::time('refresh_end', old('refresh_end', $employee->refresh_end), ['class' => 'form-control', 'id' => 'refresh_end']) }}
                                    </div> -->

                                    <!-- Refresh Break Flexible Time Fields -->
                                    <!-- <div class="form-group col-md-6" id="refreshMinDiv" style="display:none;">
                                        {{ Form::label('refresh_minutes', __('Refresh Minutes'), ['class' => 'form-label']) }}
                                        {{ Form::number('refresh_minutes', old('refresh_minutes', $employee->refresh_minutes ?? 0), ['class' => 'form-control', 'id' => 'refresh_minutes', 'min' => 0]) }}
                                    </div> -->
                                    @php
                                        $initialBreakType = old('refresh_type', $employee->refresh_type ?? 'fixed');
                                    @endphp

                                    <!-- Lunch Time Fields -->
                                    <div class="form-group col-md-6" id="lunchTimeDiv" style="{{ $initialBreakType === 'fixed' ? 'display:block;' : 'display:none;' }}">
                                        {{ Form::label('lunch_start', __('Lunch Start Time'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                                        {{ Form::time('lunch_start', old('lunch_start', $employee->lunch_start), ['class' => 'form-control', 'id' => 'lunch_start']) }}
                                    </div>

                                    <div class="form-group col-md-6" id="lunchEndDiv" style="{{ $initialBreakType === 'fixed' ? 'display:block;' : 'display:none;' }}">
                                        {{ Form::label('lunch_end', __('Lunch End Time'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                                        {{ Form::time('lunch_end', old('lunch_end', $employee->lunch_end), ['class' => 'form-control', 'id' => 'lunch_end']) }}
                                    </div>

                                    <div class="form-group col-md-6" id="lunchMinDiv" style="{{ $initialBreakType === 'flexible' ? 'display:block;' : 'display:none;' }}">
                                        {{ Form::label('lunch_minutes', __('Lunch Minutes'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                                        {{ Form::number('lunch_minutes', old('lunch_minutes', $employee->lunch_minutes ?? 0), ['class' => 'form-control', 'id' => 'lunch_minutes', 'min' => 0]) }}
                                    </div>

                                    <!-- Tea Time Fields -->
                                    <div class="form-group col-md-6" id="teaTimeDiv" style="{{ $initialBreakType === 'fixed' ? 'display:block;' : 'display:none;' }}">
                                        {{ Form::label('tea_start', __('Tea Start Time'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                                        {{ Form::time('tea_start', old('tea_start', $employee->tea_start), ['class' => 'form-control', 'id' => 'tea_start']) }}
                                    </div>

                                    <div class="form-group col-md-6" id="teaEndDiv" style="{{ $initialBreakType === 'fixed' ? 'display:block;' : 'display:none;' }}">
                                        {{ Form::label('tea_end', __('Tea End Time'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                                        {{ Form::time('tea_end', old('tea_end', $employee->tea_end), ['class' => 'form-control', 'id' => 'tea_end']) }}
                                    </div>

                                    <div class="form-group col-md-6" id="teaMinDiv" style="{{ $initialBreakType === 'flexible' ? 'display:block;' : 'display:none;' }}">
                                        {{ Form::label('tea_minutes', __('Tea Minutes'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                                        {{ Form::number('tea_minutes', old('tea_minutes', $employee->tea_minutes ?? 0), ['class' => 'form-control', 'id' => 'tea_minutes', 'min' => 0]) }}
                                    </div>

                                    <!-- Hidden fields to store shift times -->
                                    {{ Form::hidden('company_start_time', '', ['id' => 'company_start_time']) }}
                                    {{ Form::hidden('company_end_time', '', ['id' => 'company_end_time']) }}

                                    <div class="form-group col-md-12">
                                        <div class="row">

                                            <!-- @php
                                                $refreshType = $company_settings['refreshtime'] ?? 'fixed';
                                            @endphp
                                            @if($refreshType === 'fixed')
                                                <div class="col-md-3 p-2">
                                                    <div class="form-group">
                                                        {{ Form::label('refresh_start', __('Refresh Start'), ['class' => 'form-label']) }}
                                                        {{ Form::time('refresh_start', old('refresh_start', $employee->refresh_start), ['class' => 'form-control', 'required']) }}
                                                    </div>
                                                </div>
                                                <div class="col-md-3 p-2">
                                                    <div class="form-group">
                                                        {{ Form::label('refresh_end', __('Refresh End'), ['class' => 'form-label']) }}
                                                        {{ Form::time('refresh_end', old('refresh_end', $employee->refresh_end), ['class' => 'form-control', 'required']) }}
                                                    </div>
                                                </div>
                                            @else
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        {{ Form::label('refresh_minutes', __('Refresh Minutes'), ['class' => 'form-label']) }}
                                                        {{ Form::number('refresh_minutes', old('refresh_minutes', $employee->refresh_minutes ?? 0), ['class' => 'form-control', 'required', 'min' => 0]) }}
                                                    </div>
                                                </div>
                                            @endif -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="col-md-6 ">
                        <div class="employee-detail-wrap ">
                            <div class="card em-card">
                                <div class="card-header">
                                    <h5>{{ __('Company Detail') }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info">
                                                <strong>{{ __('Branch') }}</strong>
                                                <span>{{ !empty($employee->branch) ? $employee->branch->name : '' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info font-style">
                                                <strong>{{ __('Department') }}</strong>
                                                <span>{{ !empty($employee->department) ? $employee->department->name : '' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info font-style">
                                                <strong>{{ __('Designation') }}</strong>
                                                <span>{{ !empty($employee->designation) ? $employee->designation->name : '' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info font-style">
                                                <strong>{{ __('shift') }}</strong>
                                                <span>{{ !empty($employee->shift) ? $employee->shift->name : '' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info">
                                                <strong>{{ __('Date Of Joining') }}</strong>
                                                <span>{{ \Auth::user()->dateFormat($employee->company_doj) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
                @if (\Auth::user()->type != 'employee')
                <div class="row">

                <div class="col-md-6 ">
                    <div class="card em-card" id="document-card">
                        <div class="card-header">
                            <h5>{{ __('Document') }}</h5>
                        </div>
                        <div class="card-body">
                            @php
                            $employeedoc = $employee->documents()->pluck('document_value', 'document_id')->toArray();
                            @endphp

                            @foreach ($documents as $key => $document)
                            @php
                                $logo        = \App\Models\Utility::get_file('uploads/document');
                                $docType     = $document->document_type ?? 'file';
                                $docRecord   = $employeeDocuments[$document->id] ?? null;
                                $isRequested = $docRecord ? (int)$docRecord->is_requested : 0;
                                $parsed      = $docRecord ? $docRecord->getParsedValue($docType) : ['text' => null, 'file' => null, 'files' => []];
                                $textValue   = $parsed['text'];
                                $filesList   = $parsed['files'];
                                $previewId   = 'prev_' . $document->id . '_' . $key;
                            @endphp
                            <div class="row mb-3 pb-2 border-bottom">
                                <div class="form-group col-12">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label mb-0 fw-bold">
                                            {{ $document->name }}
                                            @if ($document->is_required == 1)
                                                <span class="text-danger">*</span>
                                            @endif
                                            <span class="badge bg-warning text-dark ms-2 req-doc-badge req-doc-badge-{{ $document->id }} {{ $isRequested ? '' : 'd-none' }}">
                                                <i class="ti ti-alert-circle me-1"></i>please upload your document
                                            </span>
                                        </label>
                                        <button type="button" 
                                                class="btn btn-sm {{ $isRequested ? 'btn-warning' : 'btn-primary' }} request-doc-btn" 
                                                data-doc-id="{{ $document->id }}" 
                                                data-emp-id="{{ $employee->id }}" 
                                                data-is-requested="{{ $isRequested }}">
                                            <i class="ti ti-send me-1"></i><span class="btn-text">{{ $isRequested ? __('Requested') : __('Request Document') }}</span>
                                        </button>
                                    </div>
                                    <input type="hidden" name="emp_doc_id[{{ $document->id }}]" value="{{ $document->id }}">

                                    {{-- TEXT field --}}
                                    @if ($docType === 'text' || $docType === 'both')
                                        <input type="text"
                                            class="form-control mb-2"
                                            name="document_text[{{ $document->id }}]"
                                            id="document_text_{{ $document->id }}"
                                            value="{{ $textValue ?? '' }}"
                                            placeholder="{{ __('Enter') }} {{ $document->name }}">
                                    @endif

                                    {{-- FILE field (multiple uploads allowed) --}}
                                    @if ($docType === 'file' || $docType === 'both')
                                        <div class="choose-files mb-2">
                                            <label for="document_{{ $document->id }}" class="mb-0">
                                                <div class="bg-primary document btn btn-primary btn-sm py-1 px-3">
                                                    <i class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                </div>
                                                <input type="file"
                                                    class="form-control file d-none"
                                                    name="document[{{ $document->id }}][]"
                                                    id="document_{{ $document->id }}"
                                                    data-preview-id="{{ $previewId }}"
                                                    multiple
                                                    onchange="handleDocPreview(this)">
                                            </label>
                                        </div>

                                        {{-- Gallery Preview Area --}}
                                        <div id="{{ $previewId }}" class="doc-gallery-grid">
                                            @if (!empty($filesList))
                                                @foreach ($filesList as $fIdx => $fName)
                                                    @php
                                                        $fExt = strtolower(pathinfo($fName, PATHINFO_EXTENSION));
                                                        $fIsImg = in_array($fExt, ['jpg','jpeg','png','gif','webp','bmp','svg']);
                                                        $fUrl = $logo . '/' . rawurlencode($fName);
                                                    @endphp
                                                    <div class="doc-card-item">
                                                        <button type="button" class="doc-card-remove-btn" title="{{ __('Remove file') }}" onclick="deleteDocFile({{ $employee->id }}, {{ $document->id }}, '{{ $fName }}', this)">
                                                            &times;
                                                        </button>
                                                        @if ($fIsImg)
                                                            <a href="{{ $fUrl }}" target="_blank" class="doc-card-img-wrapper" title="{{ $fName }}">
                                                                <img src="{{ $fUrl }}" alt="{{ $document->name }}">
                                                            </a>
                                                        @else
                                                            <a href="{{ $fUrl }}" target="_blank" class="doc-card-file-wrapper text-decoration-none" title="{{ $fName }}">
                                                                <i class="ti ti-file-description doc-card-file-icon text-primary"></i>
                                                                <span class="doc-card-file-name">{{ $fName }}</span>
                                                            </a>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                    <div class="col-md-6">
                        <div class="card em-card">
                            <div class="card-header">
                                <h5>{{ __('Bank Account Detail') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        {!! Form::label('account_holder_name', __('Account Holder Name'), ['class' => 'form-label']) !!}
                                        {!! Form::text('account_holder_name', old('account_holder_name', !empty($employee->account_holder_name)?$employee->account_holder_name:''), ['class' => 'form-control',
                                    'placeholder' => 'Enter account holder name']) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('account_number', __('Account Number'), ['class' => 'form-label']) !!}
                                        {!! Form::number('account_number', old('account_number', !empty($employee->account_number)?$employee->account_number:''), ['class' => 'form-control',
                                    'placeholder' => 'Enter account number']) !!}

                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('bank_name', __('Bank Name'), ['class' => 'form-label']) !!}
                                        {!! Form::text('bank_name', old('bank_name', !empty($employee->bank_name)?$employee->bank_name:''), ['class' => 'form-control',
                                    'placeholder' => 'Enter bank name']) !!}

                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('bank_identifier_code', __(\App\Models\Utility::bankCodeLabel()), ['class' => 'form-label']) !!}
                                        {!! Form::text('bank_identifier_code', old('bank_identifier_code', !empty($employee->bank_identifier_code)?$employee->bank_identifier_code:''), ['class' => 'form-control',
                                    'placeholder' => 'Enter bank identifier code']) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('branch_location', __('Branch Location'), ['class' => 'form-label']) !!}
                                        {!! Form::text('branch_location', old('branch_location', !empty($employee->branch_location)?$employee->branch_location:''), ['class' => 'form-control',
                                    'placeholder' => 'Enter branch location']) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('tax_payer_id', __('Tax Payer Id'), ['class' => 'form-label']) !!}
                                        {!! Form::text('tax_payer_id', old('tax_payer_id', !empty($employee->tax_payer_id)?$employee->tax_payer_id:''), ['class' => 'form-control',
                                    'placeholder' => 'Enter tax payer id']) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="row">
                    <div class="col-md-6 ">
                        <div class="employee-detail-wrap">
                            <div class="card em-card" id="document-card">
                                <div class="card-header">
                                    <h5>{{ __('Document Detail') }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @php
                                         $logo = \App\Models\Utility::get_file('uploads/document/');
                                         $hasAnyRequested = false;
                                        @endphp
                                        @foreach ($documents as $key => $document)
                                        @php
                                            $docType     = $document->document_type ?? 'file';
                                            $docRecord   = $employeeDocuments[$document->id] ?? null;
                                            $isRequested = $docRecord ? (int)$docRecord->is_requested : 0;
                                            if ($isRequested) { $hasAnyRequested = true; }
                                            $parsed      = $docRecord ? $docRecord->getParsedValue($docType) : ['text' => null, 'file' => null, 'files' => []];
                                            $textValue   = $parsed['text'];
                                            $filesList   = $parsed['files'];
                                            $previewId   = 'emp_prev_' . $document->id . '_' . $key;
                                        @endphp
                                        <div class="col-md-12 mb-3 pb-2 border-bottom">
                                            <div class="form-group">
                                                <label class="form-label font-weight-bold">
                                                    {{ $document->name }}
                                                    @if ($document->is_required == 1)
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                    @if ($isRequested)
                                                        <span class="badge bg-warning text-dark ms-2">
                                                            <i class="ti ti-alert-circle me-1"></i>please upload your document
                                                        </span>
                                                    @endif
                                                </label>
                                                <input type="hidden" name="emp_doc_id[{{ $document->id }}]" value="{{ $document->id }}">

                                                @if ($isRequested)
                                                    {{-- Requested document: Show upload input options --}}
                                                    @if ($docType === 'text' || $docType === 'both')
                                                        <input type="text"
                                                            class="form-control mb-2"
                                                            name="document_text[{{ $document->id }}]"
                                                            id="document_text_{{ $document->id }}"
                                                            value="{{ $textValue ?? '' }}"
                                                            placeholder="{{ __('Enter') }} {{ $document->name }}">
                                                    @endif

                                                    @if ($docType === 'file' || $docType === 'both')
                                                        <div class="choose-files mb-2">
                                                            <label for="document_{{ $document->id }}" class="mb-0">
                                                                <div class="bg-primary document btn btn-primary btn-sm py-1 px-3">
                                                                    <i class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                                </div>
                                                                <input type="file"
                                                                    class="form-control file d-none"
                                                                    name="document[{{ $document->id }}][]"
                                                                    id="document_{{ $document->id }}"
                                                                    data-preview-id="{{ $previewId }}"
                                                                    multiple
                                                                    onchange="handleDocPreview(this)">
                                                            </label>
                                                        </div>

                                                        <div id="{{ $previewId }}" class="doc-gallery-grid">
                                                            @if (!empty($filesList))
                                                                @foreach ($filesList as $fIdx => $fName)
                                                                    @php
                                                                        $fExt = strtolower(pathinfo($fName, PATHINFO_EXTENSION));
                                                                        $fIsImg = in_array($fExt, ['jpg','jpeg','png','gif','webp','bmp','svg']);
                                                                        $fUrl = $logo . '/' . rawurlencode($fName);
                                                                    @endphp
                                                                    <div class="doc-card-item">
                                                                        <button type="button" class="doc-card-remove-btn" title="{{ __('Remove file') }}" onclick="deleteDocFile({{ $employee->id }}, {{ $document->id }}, '{{ $fName }}', this)">
                                                                            &times;
                                                                        </button>
                                                                        @if ($fIsImg)
                                                                            <a href="{{ $fUrl }}" target="_blank" class="doc-card-img-wrapper" title="{{ $fName }}">
                                                                                <img src="{{ $fUrl }}" alt="{{ $document->name }}">
                                                                            </a>
                                                                        @else
                                                                            <a href="{{ $fUrl }}" target="_blank" class="doc-card-file-wrapper text-decoration-none" title="{{ $fName }}">
                                                                                <i class="ti ti-file-description doc-card-file-icon text-primary"></i>
                                                                                <span class="doc-card-file-name">{{ $fName }}</span>
                                                                            </a>
                                                                        @endif
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    @endif
                                                @else
                                                    {{-- Not requested: Show read-only display gallery --}}
                                                    <div class="info mt-1 doc-gallery-grid">
                                                        @if ($docType === 'text')
                                                            <span>{{ !empty($textValue) ? $textValue : '-' }}</span>
                                                        @elseif ($docType === 'both')
                                                            @if ($textValue) <div class="w-100 mb-1"><span>{{ $textValue }}</span></div> @endif
                                                            @if (!empty($filesList))
                                                                @foreach ($filesList as $fName)
                                                                    @php
                                                                        $fExt = strtolower(pathinfo($fName, PATHINFO_EXTENSION));
                                                                        $fIsImg = in_array($fExt, ['jpg','jpeg','png','gif','webp','bmp','svg']);
                                                                        $fUrl = $logo . '/' . rawurlencode($fName);
                                                                    @endphp
                                                                    <div class="doc-card-item">
                                                                        @if ($fIsImg)
                                                                            <a href="{{ $fUrl }}" target="_blank" class="doc-card-img-wrapper" title="{{ $fName }}">
                                                                                <img src="{{ $fUrl }}" alt="{{ $document->name }}">
                                                                            </a>
                                                                        @else
                                                                            <a href="{{ $fUrl }}" target="_blank" class="doc-card-file-wrapper text-decoration-none" title="{{ $fName }}">
                                                                                <i class="ti ti-file-description doc-card-file-icon text-primary"></i>
                                                                                <span class="doc-card-file-name">{{ $fName }}</span>
                                                                            </a>
                                                                        @endif
                                                                    </div>
                                                                @endforeach
                                                            @elseif (!$textValue)
                                                                <span>-</span>
                                                            @endif
                                                        @else
                                                            @if (!empty($filesList))
                                                                @foreach ($filesList as $fName)
                                                                    @php
                                                                        $fExt = strtolower(pathinfo($fName, PATHINFO_EXTENSION));
                                                                        $fIsImg = in_array($fExt, ['jpg','jpeg','png','gif','webp','bmp','svg']);
                                                                        $fUrl = $logo . '/' . rawurlencode($fName);
                                                                    @endphp
                                                                    <div class="doc-card-item">
                                                                        @if ($fIsImg)
                                                                            <a href="{{ $fUrl }}" target="_blank" class="doc-card-img-wrapper" title="{{ $fName }}">
                                                                                <img src="{{ $fUrl }}" alt="{{ $document->name }}">
                                                                            </a>
                                                                        @else
                                                                            <a href="{{ $fUrl }}" target="_blank" class="doc-card-file-wrapper text-decoration-none" title="{{ $fName }}">
                                                                                <i class="ti ti-file-description doc-card-file-icon text-primary"></i>
                                                                                <span class="doc-card-file-name">{{ $fName }}</span>
                                                                            </a>
                                                                        @endif
                                                                    </div>
                                                                @endforeach
                                                            @else
                                                                <span>-</span>
                                                            @endif
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                        @if ($hasAnyRequested)
                                            <div class="col-md-12 text-end mt-2">
                                                <input type="submit" value="{{ __('Save Documents') }}" class="btn btn-primary">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 ">
                        <div class="employee-detail-wrap">
                            <div class="card em-card">
                                <div class="card-header">
                                    <h5>{{ __('Bank Account Detail') }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info">
                                                <strong>{{ __('Account Holder Name') }}</strong>
                                                <span>{{ $employee->account_holder_name }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info font-style">
                                                <strong>{{ __('Account Number') }}</strong>
                                                <span>{{ $employee->account_number }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info font-style">
                                                <strong>{{ __('Bank Name') }}</strong>
                                                <span>{{ $employee->bank_name }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info">
                                                <strong>{{ __(\App\Models\Utility::bankCodeLabel()) }}</strong>
                                                <span>{{ $employee->bank_identifier_code }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info">
                                                <strong>{{ __('Branch Location') }}</strong>
                                                <span>{{ $employee->branch_location }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info">
                                                <strong>{{ __('Tax Payer Id') }}</strong>
                                                <span>{{ $employee->tax_payer_id }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info">
                                                <strong>{{ __('PF/UAN Number') }}</strong>
                                                <span>{{ $employee->pf_id }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info">
                                                <strong>{{ __('ESIC') }}</strong>
                                                <span>{{ $employee->esic_id }}</span>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                {{-- ── Leave Type Assignment ─────────────────────────────────────────── --}}
                @if (\Auth::user()->type != 'employee')
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
                                                    $oldDays = old('leave_types.' . $leaveType->id . '.days');
                                                    $oldIsPaid = old('leave_types.' . $leaveType->id . '.is_paid');

                                                    $isAssigned = $oldChecked !== null ? (bool)$oldChecked : array_key_exists($leaveType->id, $assignedLeaveTypes ?? []);
                                                    $assignedDays = $oldDays !== null ? $oldDays : ($isAssigned ? ($assignedLeaveTypes[$leaveType->id]['total_days'] ?? $leaveType->days) : $leaveType->days);
                                                    $isPaid = $oldIsPaid !== null ? (bool)$oldIsPaid : ($isAssigned ? ($assignedLeaveTypes[$leaveType->id]['is_paid'] ?? $leaveType->is_paid) : $leaveType->is_paid);
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input leave-type-check" type="checkbox"
                                                                name="leave_types[{{ $leaveType->id }}][checked]"
                                                                value="1"
                                                                id="leave_type_{{ $leaveType->id }}"
                                                                {{ $isAssigned ? 'checked' : '' }}>
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
                                                                {{ $isPaid ? 'checked' : '' }}>
                                                            <label class="form-check-label ms-2 small" for="is_paid_{{ $leaveType->id }}" id="is_paid_label_{{ $leaveType->id }}">
                                                                {{ $isPaid ? __('Paid') : __('Unpaid') }}
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.5" min="0"
                                                            class="form-control text-center"
                                                            name="leave_types[{{ $leaveType->id }}][days]"
                                                            value="{{ $assignedDays }}"
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
                @endif

                @if (\Auth::user()->type != 'employee')
                <div class="col-12 text-end my-3 clearfix">
                    <button type="submit" class="btn btn-primary px-4">{{ 'Update' }}</button>
                </div>
                @endif
                {!! Form::close() !!}

                {{-- ── Employee Documents (HR Files Management - Standalone Section at Bottom) ── --}}
                @if (\Auth::user()->can('Manage Employee File Document') || \Auth::user()->can('View Employee File Document') || \Auth::user()->can('Create Employee File Document') || \Auth::user()->can('Edit Employee File Document') || \Auth::user()->can('Delete Employee File Document') || \Auth::user()->can('Download Employee File Document') || \Auth::user()->type == 'company')
                @php
                    $empIds = array_values(array_unique(array_filter([$employee->id, $employee->employee_id])));
                    $employeeFileDocs = \App\Models\EmployeeFileDocument::whereIn('employee_id', $empIds)->with('uploader')->orderBy('id', 'desc')->get();
                @endphp
                <div class="row mt-4 mb-4">
                    <div class="col-md-12">
                        <div class="card em-card" id="employee-hr-documents-card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col-6">
                                        <h5 class="mb-0">{{ __('Employee Documents') }}</h5>
                                    </div>
                                    <div class="col-6 text-end">
                                        @if (\Auth::user()->can('Create Employee File Document') || \Auth::user()->type == 'company')
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#uploadEmployeeDocModal" class="btn btn-sm btn-primary">
                                            <i class="ti ti-plus me-1"></i>{{ __('Upload Document') }}
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-border-style">
                                <div class="table-responsive">
                                    <table class="table" id="employee-file-docs-table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('DOCUMENT ID') }}</th>
                                                <th>{{ __('DOCUMENT NAME') }}</th>
                                                <th>{{ __('DOCUMENT TYPE') }}</th>
                                                <th>{{ __('FILE TYPE') }}</th>
                                                <th>{{ __('UPLOADED DATE') }}</th>
                                                <th>{{ __('UPLOADED BY') }}</th>
                                                <th width="200px">{{ __('ACTION') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="employee-file-docs-list">
                                            @forelse ($employeeFileDocs as $idx => $fDoc)
                                                <tr id="emp-doc-row-{{ $fDoc->id }}">
                                                    <td>
                                                        <a class="btn btn-outline-primary" href="{{ route('employee-file-documents.preview', $fDoc->id) }}" target="_blank">
                                                            #DOC{{ sprintf('%05d', $fDoc->id) }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $fDoc->document_name }}</td>
                                                    <td>
                                                        <span class="badge rounded-pill px-3 py-1 fw-bold" style="background-color: {{ $fDoc->badge_style['bg'] }}; color: {{ $fDoc->badge_style['color'] }}; font-size: 0.76rem;">
                                                            {{ $fDoc->document_type }}
                                                        </span>
                                                    </td>
                                                    <td>{{ strtoupper($fDoc->file_extension) }}</td>
                                                    <td>{{ $fDoc->created_at ? $fDoc->created_at->format('d M, Y') : '-' }}</td>
                                                    <td>{{ $fDoc->uploader ? $fDoc->uploader->name : __('Admin') }}</td>
                                                    <td class="Action">
                                                        @if (\Auth::user()->can('View Employee File Document') || \Auth::user()->can('Manage Employee File Document') || \Auth::user()->type == 'company')
                                                        <div class="action-btn bg-info me-2">
                                                            <a href="{{ route('employee-file-documents.preview', $fDoc->id) }}" target="_blank"
                                                               class="mx-3 btn btn-sm align-items-center"
                                                               data-bs-toggle="tooltip" title="{{ __('View') }}">
                                                                <span class="text-white"><i class="ti ti-eye"></i></span>
                                                            </a>
                                                        </div>
                                                        @endif
                                                        @if (\Auth::user()->can('Download Employee File Document') || \Auth::user()->type == 'company')
                                                        <div class="action-btn bg-primary me-2">
                                                            <a href="{{ route('employee-file-documents.download', $fDoc->id) }}"
                                                               class="mx-3 btn btn-sm align-items-center"
                                                               data-bs-toggle="tooltip" title="{{ __('Download') }}">
                                                                <span class="text-white"><i class="ti ti-download"></i></span>
                                                            </a>
                                                        </div>
                                                        @endif
                                                        @if (\Auth::user()->can('Delete Employee File Document') || \Auth::user()->type == 'company')
                                                        <div class="action-btn bg-danger me-2">
                                                            <a href="#" onclick="deleteEmpFileDoc({{ $fDoc->id }}, '{{ addslashes($fDoc->document_name) }}')"
                                                               class="mx-3 btn btn-sm align-items-center"
                                                               data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                                <span class="text-white"><i class="ti ti-trash"></i></span>
                                                            </a>
                                                        </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr id="no-emp-docs-row">
                                                    <td colspan="7" class="text-center py-4 text-muted">
                                                        {{ __('No employee documents uploaded yet.') }}
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upload Employee Document Modal (Standard App Modal Design) -->
                <div class="modal fade" id="uploadEmployeeDocModal" tabindex="-1" aria-labelledby="uploadEmployeeDocModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="uploadEmployeeDocModalLabel">{{ __('Upload Employee Document') }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form id="uploadEmployeeDocForm" action="{{ route('employee-file-documents.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-12 form-group mb-3">
                                            <label for="modal_document_type" class="col-form-label pt-0">{{ __('Document Type') }} <span class="text-danger">*</span></label>
                                            <select name="document_type" id="modal_document_type" class="form-select form-control" required>
                                                <option value="Appointment Letter">{{ __('Appointment Letter') }}</option>
                                                <option value="Offer Letter">{{ __('Offer Letter') }}</option>
                                                <option value="Warning Letter">{{ __('Warning Letter') }}</option>
                                                <option value="Office Letter">{{ __('Office Letter') }}</option>
                                                <option value="Experience Letter">{{ __('Experience Letter') }}</option>
                                                <option value="Increment Letter">{{ __('Salary / Increment Letter') }}</option>
                                                <option value="Certificate">{{ __('Certificate') }}</option>
                                                <option value="Other" selected>{{ __('Other') }}</option>
                                            </select>
                                        </div>

                                        <div class="col-12 form-group mb-3">
                                            <label for="modal_document_name" class="col-form-label">{{ __('Document Title / Name') }} <small class="text-muted">({{ __('Optional') }})</small></label>
                                            <input type="text" name="document_name" id="modal_document_name" class="form-control" placeholder="{{ __('e.g. Appointment Letter 2025') }}">
                                        </div>

                                        <div class="col-12 form-group mb-3">
                                            <label for="modal_documents_files" class="col-form-label">{{ __('Select File(s)') }} <span class="text-danger">*</span></label>
                                            <input type="file" name="documents[]" id="modal_documents_files" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png" required>
                                            <small class="text-muted d-block mt-1">{{ __('Supported formats: PDF, JPG, JPEG, PNG (Max 5MB per file).') }}</small>
                                        </div>

                                        {{-- Upload Progress Bar --}}
                                        <div class="col-12 progress d-none mb-2" id="upload-doc-progress-bar" style="height: 6px;">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%;"></div>
                                        </div>
                                        <small class="text-primary d-none fw-semibold" id="upload-doc-status-text">{{ __('Uploading files, please wait...') }}</small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
                                    <input type="submit" value="{{ __('Upload Document') }}" class="btn btn-primary" id="upload-doc-submit-btn">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
            </div>
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
    <script type="text/javascript">
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                const modal = document.querySelector('.modal.show');

                if (modal) {
                    submitForm();
                } else {
                    const mainForm = document.querySelector('.employee-edit-form');
                    if (mainForm) {
                        mainForm.querySelector('button[type="submit"]')?.click();
                    }
                }
            }
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
                    var emp_selct = ` <select class="form-control  designation_id select2" name="designation_id" id="choices-multiple"
                                            placeholder="Select Designation" >
                                            </select>`;
                    $('.designation_div').html(emp_selct);
                    $('.designation_id').append('<option value="">Select any Designation</option>');
                    var selectedDesignation = '{{ old('designation_id', $employee->designation_id) }}';
                    $.each(data, function(key, value) {
                        var select = '';
                        if (key == selectedDesignation) {
                            select = 'selected';
                        }

                        $('.designation_id').append('<option value="' + key + '"  ' + select + '>' +
                            value + '</option>');
                    });
                    new Choices('#choices-multiple', {
                        removeItemButton: true,
                    });
                }
            });
        }

        $(document).ready(function() {
            var d_id = $('#department_id').val();
            var designation_id = '{{ $employee->designation_id }}';

            console.log(d_id);
            getDesignation(d_id);

            var s_id = $('#shift_id').val();
            var shift_id = '{{ $employee->shift_id }}';
        });

        $(document).on('change', 'select[name=department_id]', function() {
            var department_id = $(this).val();
            getDesignation(department_id);
        });
    </script>
    <script>
        // $(document).ready(function()){

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
                    console.log(response);
                    $('#subdepartment_id').html(response)
                }
            })
        })
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
                                                                                <label class="form-label">' + key1
                        .toUpperCase() +
                        '</label>';
                    // console.log(value1);

                    if (value1 != '') {
                        html += '<div class="form-icon-user">\
                                                                                <select class="form-control" name="' +
                            key1 +
                            '">';
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
                                                                                    <input name="' + key1 + '" id="' +
                            key1 +
                            '" placeholder="' +
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

            // Set hidden fields with shift times
            const startTimeEl = document.getElementById('company_start_time');
            const endTimeEl = document.getElementById('company_end_time');
            if (startTimeEl) startTimeEl.value = shift.start || '';
            if (endTimeEl) endTimeEl.value = shift.end || '';

            // Populate times if available
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
            
            // Trigger UI update based on refresh_break_type
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

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadShiftBreakTimes();
            toggleShiftBreakType();
        });

        $(document).on('change', '#refresh_break_type', function() {
            toggleShiftBreakType();
        });

        // Document preview handler — shows uniform card previews for multiple selected files
        function handleDocPreview(input) {
            var previewId = input.getAttribute('data-preview-id');
            var previewArea = document.getElementById(previewId);
            if (!input.files || !input.files.length) return;

            var imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];

            for (var i = 0; i < input.files.length; i++) {
                (function(file) {
                    var fileName = file.name;
                    var ext = fileName.split('.').pop().toLowerCase();

                    var container = document.createElement('div');
                    container.className = 'doc-card-item new-preview-item';

                    if (imageExts.indexOf(ext) !== -1) {
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            container.innerHTML = '<div class="doc-card-img-wrapper"><img src="' + e.target.result + '" alt="' + fileName + '" title="' + fileName + '"></div>';
                            previewArea.appendChild(container);
                        };
                        reader.readAsDataURL(file);
                    } else {
                        container.innerHTML = '<div class="doc-card-file-wrapper"><i class="ti ti-file-description doc-card-file-icon text-primary"></i><span class="doc-card-file-name">' + fileName + '</span></div>';
                        previewArea.appendChild(container);
                    }
                })(input.files[i]);
            }
        }

        // AJAX file deletion handler
        function deleteDocFile(employeeId, documentId, fileName, btn) {
            if (!confirm('{{ __("Are you sure you want to remove this file?") }}')) return;

            var $item = $(btn).closest('.doc-card-item');
            $.ajax({
                url: "{{ route('employee.delete-document-file') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    employee_id: employeeId,
                    document_id: documentId,
                    file_name: fileName
                },
                success: function(res) {
                    if (res.success) {
                        $item.fadeOut(300, function() { $(this).remove(); });
                        show_toastr('Success', res.message, 'success');
                    } else {
                        show_toastr('Error', res.message, 'error');
                    }
                },
                error: function() {
                    show_toastr('Error', '{{ __("Failed to remove file.") }}', 'error');
                }
            });
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
            var leaveTypeId = id.replace('is_paid_', '');
            var $label = $('#is_paid_label_' + leaveTypeId);
            $label.text($(this).prop('checked') ? '{{ __("Paid") }}' : '{{ __("Unpaid") }}');
        });

        // AJAX Request Document toggle
        $(document).on('click', '.request-doc-btn', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var docId = $btn.data('doc-id');
            var empId = $btn.data('emp-id');
            var currentStatus = parseInt($btn.data('is-requested')) || 0;
            var newStatus = currentStatus === 1 ? 0 : 1;

            $btn.prop('disabled', true);

            $.ajax({
                url: "{{ route('employee.request-document') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    employee_id: empId,
                    document_id: docId,
                    is_requested: newStatus
                },
                success: function(response) {
                    $btn.prop('disabled', false);
                    if (response.success) {
                        $btn.data('is-requested', newStatus);
                        var $badge = $('.req-doc-badge-' + docId);
                        if (newStatus === 1) {
                            $badge.removeClass('d-none');
                            $btn.removeClass('btn-primary').addClass('btn-warning');
                            $btn.find('.btn-text').text("{{ __('Requested') }}");
                            show_toastr('Success', response.message, 'success');
                        } else {
                            $badge.addClass('d-none');
                            $btn.removeClass('btn-warning').addClass('btn-primary');
                            $btn.find('.btn-text').text("{{ __('Request Document') }}");
                            show_toastr('Success', response.message, 'success');
                        }
                    } else {
                        show_toastr('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false);
                    show_toastr('Error', '{{ __("Something went wrong. Please try again.") }}', 'error');
                }
            });
        });

        // ── Employee HR Documents Drag & Drop, AJAX Upload, Preview & Delete ──
        var $dragDropZone = $('#emp-doc-drag-drop-zone');
        var $dragDropInput = $('#drag-drop-file-input');

        $dragDropZone.on('click', function() {
            $dragDropInput.trigger('click');
        });

        $dragDropZone.on('dragover dragenter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).css('border-color', 'var(--bs-primary, #6c5ce7)').css('background', '#f1f5f9');
        });

        $dragDropZone.on('dragleave drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).css('border-color', '#cbd5e1').css('background', '#f8fafc');
        });

        $dragDropZone.on('drop', function(e) {
            var files = e.originalEvent.dataTransfer.files;
            if (files && files.length > 0) {
                var modalEl = document.getElementById('uploadEmployeeDocModal');
                var fileInput = document.getElementById('modal_documents_files');
                fileInput.files = files;
                var bsModal = new bootstrap.Modal(modalEl);
                bsModal.show();
            }
        });

        $dragDropInput.on('change', function() {
            if (this.files && this.files.length > 0) {
                var modalEl = document.getElementById('uploadEmployeeDocModal');
                var fileInput = document.getElementById('modal_documents_files');
                fileInput.files = this.files;
                var bsModal = new bootstrap.Modal(modalEl);
                bsModal.show();
            }
        });

        $('#uploadEmployeeDocForm').on('submit', function(e) {
            e.preventDefault();
            var form = this;
            var formData = new FormData(form);

            var $btn = $('#upload-doc-submit-btn');
            var $progress = $('#upload-doc-progress-bar');
            var $progressBar = $progress.find('.progress-bar');
            var $status = $('#upload-doc-status-text');

            $btn.prop('disabled', true);
            $progress.removeClass('d-none');
            $status.removeClass('d-none');
            $progressBar.css('width', '10%');

            $.ajax({
                url: "{{ route('employee-file-documents.store') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = Math.round((evt.loaded / evt.total) * 100);
                            $progressBar.css('width', percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    $btn.prop('disabled', false);
                    $progress.addClass('d-none');
                    $status.addClass('d-none');

                    if (response.success) {
                        show_toastr('Success', response.message, 'success');
                        var modalEl = document.getElementById('uploadEmployeeDocModal');
                        var bsModal = bootstrap.Modal.getInstance(modalEl);
                        if (bsModal) bsModal.hide();

                        setTimeout(function() {
                            location.reload();
                        }, 500);
                    } else {
                        show_toastr('Error', response.message || '{{ __("Upload failed.") }}', 'error');
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false);
                    $progress.addClass('d-none');
                    $status.addClass('d-none');
                    var msg = '{{ __("Upload failed. Please check file format and size.") }}';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    show_toastr('Error', msg, 'error');
                }
            });
        });

        window.deleteEmpFileDoc = function(docId, docName) {
            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false
            });

            swalWithBootstrapButtons.fire({
                title: '{{ __("Are you sure?") }}',
                text: '{{ __("This action can not be undone. Do you want to continue?") }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '{{ __("Yes") }}',
                cancelButtonText: '{{ __("No") }}',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('employee-file-documents') }}/" + docId,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: 'DELETE'
                        },
                        success: function(response) {
                            if (response.success) {
                                show_toastr('Success', response.message, 'success');
                                $('#emp-doc-row-' + docId).fadeOut(300, function() {
                                    $(this).remove();
                                    if ($('#employee-file-docs-list tr').length === 0) {
                                        $('#employee-file-docs-list').html('<tr id="no-emp-docs-row"><td colspan="7" class="text-center py-4 text-muted"><i class="ti ti-files fs-1 text-secondary opacity-50 mb-2 d-block"></i>{{ __("No employee documents uploaded yet.") }}</td></tr>');
                                    }
                                });
                            } else {
                                show_toastr('Error', response.message, 'error');
                            }
                        },
                        error: function() {
                            show_toastr('Error', '{{ __("Unable to delete document. Please try again.") }}', 'error');
                        }
                    });
                }
            });
        };
    </script>
    @endpush
