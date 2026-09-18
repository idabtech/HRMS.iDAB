@php

    $logo = \App\Models\Utility::get_file('uploads/logo/');
    $company_logo = \App\Models\Utility::GetLogo();
    $users = \Auth::user();
    $profile = \App\Models\Utility::get_file('uploads/avatar/');
    $currantLang = $users->currentLanguage();
    $emailTemplate = App\Models\EmailTemplate::getemailTemplate();
    $lang = Auth::user()->lang;
@endphp

@if (isset($setting['cust_theme_bg']) && $setting['cust_theme_bg'] == 'on')
    <nav class="dash-sidebar light-sidebar transprent-bg">
    @else
        <nav class="dash-sidebar light-sidebar">
@endif

{{-- <nav class="dash-sidebar light-sidebar {{ isset($cust_theme_bg) && $cust_theme_bg == 'on' ? 'transprent-bg' : '' }}"> --}}

<div class="navbar-wrapper">
    <div class="m-header main-logo d-flex align-items-center justify-content-between">
        <a href="{{ route('dashboard') }}" class="b-brand">
            <!-- ========   change your logo hear   ============ -->
            <img src="{{ $logo . (isset($company_logo) && !empty($company_logo) ? $company_logo . '?' . time() : 'logo-dark.png' . '?' . time()) }}"
                alt="{{ config('app.name', 'HRMGo') }}" class="logo logo-lg" style="height: 40px;">
        </a>
        <button class="sidebar-collapse-btn" id="collapseBtnNew" aria-label="Collapse sidebar" aria-pressed="false" title="Collapse / expand">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
    </div>
    <div class="navbar-content">
        <ul class="dash-navbar">

            <!-- dashboard-->
            <li class="dash-item {{ Request::segment(1) == '' || Request::segment(1) == 'dashboard' || Request::segment(1) == null ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" class="dash-link">
                    <span class="dash-micon"><i class="ti ti-home"></i></span>
                    <span class="dash-mtext">{{ __('Dashboard') }}</span>
                </a>
            </li>

            <!-- report-->
            @if (\Auth::user()->type == 'company' || Gate::check('Manage Report'))
                <li class="dash-item dash-hasmenu {{ Request::segment(1) == 'report' || Request::segment(1) == 'monthly-attendance' || Request::segment(1) == 'leave' || Request::segment(1) == 'account-statement' || Request::segment(1) == 'payroll' || Request::segment(1) == 'timesheet' ? 'active dash-trigger' : '' }}">
                    <a href="javascript:void(0)" class="dash-link">
                        <span class="dash-micon"><i class="ti ti-file-analytics"></i></span>
                        <span class="dash-mtext">{{ __('Report') }}</span>
                        <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                    </a>
                    <ul class="dash-submenu">
                        @can('Manage Report')
                            <li class="dash-item {{ Request::routeIs('report.income-expense') ? 'active' : '' }}">
                                <a class="dash-link" href="{{ route('report.income-expense') }}">{{ __('Income Vs Expense') }}</a>
                            </li>
                            <li class="dash-item {{ Request::routeIs('report.monthly.attendance') ? 'active' : '' }}">
                                <a class="dash-link" href="{{ route('report.monthly.attendance') }}">{{ __('Monthly Attendance') }}</a>
                            </li>
                            <li class="dash-item {{ Request::routeIs('report.leave') ? 'active' : '' }}">
                                <a class="dash-link" href="{{ route('report.leave') }}">{{ __('Leave') }}</a>
                            </li>
                            <li class="dash-item {{ Request::routeIs('report.account.statement') ? 'active' : '' }}">
                                <a class="dash-link" href="{{ route('report.account.statement') }}">{{ __('Account Statement') }}</a>
                            </li>
                            <li class="dash-item {{ Request::routeIs('report.payroll') ? 'active' : '' }}">
                                <a class="dash-link" href="{{ route('report.payroll') }}">{{ __('Payroll') }}</a>
                            </li>
                            <li class="dash-item {{ Request::routeIs('report.timesheet') ? 'active' : '' }}">
                                <a class="dash-link" href="{{ route('report.timesheet') }}">{{ __('Timesheet') }}</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endif
            <!--dashboard-->

            <!-- user-->
            <!-- user-->
            @if (\Auth::user()->isSuperAdminSideUser())
                @if (\Auth::user()->type == 'super admin' || \Auth::user()->can('Manage User'))
                    <li class="dash-item {{ Request::routeIs('user.index') && Request::segment(1) == 'user' ? 'active' : '' }}">
                        <a href="{{ route('user.index') }}" class="dash-link">
                            <span class="dash-micon"><i class="ti ti-building"></i></span>
                            <span class="dash-mtext">{{ __('Companies') }}</span>
                        </a>
                    </li>
                @endif

                @if (\Auth::user()->type == 'super admin' || \Auth::user()->can('Manage User') || \Auth::user()->can('Manage Role'))
                    <li class="dash-item dash-hasmenu {{ Request::segment(1) == 'superadmin-staff' || Request::segment(1) == 'roles' ? 'active dash-trigger' : '' }}">
                        <a href="javascript:void(0)" class="dash-link">
                            <span class="dash-micon"><i class="ti ti-users"></i></span>
                            <span class="dash-mtext">{{ __('Admin Staff') }}</span>
                            <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="dash-submenu {{ Request::segment(1) == 'superadmin-staff' || Request::segment(1) == 'roles' ? 'active' : '' }}">
                            @if (\Auth::user()->type == 'super admin' || \Auth::user()->can('Manage User'))
                                <li class="dash-item {{ Request::segment(1) == 'superadmin-staff' ? 'active' : '' }}">
                                    <a class="dash-link" href="{{ route('superadmin-staff.index') }}">{{ __('User') }}</a>
                                </li>
                            @endif
                            @if (\Auth::user()->type == 'super admin' || \Auth::user()->can('Manage Role'))
                                <li class="dash-item {{ Request::segment(1) == 'roles' ? 'active' : '' }}">
                                    <a class="dash-link" href="{{ route('roles.index') }}">{{ __('Role') }}</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
            @else
                @if (Gate::check('Manage User') || Gate::check('Manage Role'))
                    <li class="dash-item dash-hasmenu {{ Request::segment(1) == 'user' || Request::segment(1) == 'roles' || Request::segment(1) == 'lastlogin' ? 'active dash-trigger' : '' }}">
                        <a href="javascript:void(0)" class="dash-link">
                            <span class="dash-micon"><i class="ti ti-users"></i></span>
                            <span class="dash-mtext">{{ __('Admin Staff') }}</span>
                            <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="dash-submenu {{ optional(Request::route())->getName() == 'user.index' || optional(Request::route())->getName() == 'users.create' || optional(Request::route())->getName() == 'user.edit' || optional(Request::route())->getName() == 'lastlogin' ? 'active' : '' }}">
                            @can('Manage User')
                                <li class="dash-item {{ Request::segment(1) == 'lastlogin' ? 'active' : '' }}">
                                    <a class="dash-link" href="{{ route('user.index') }}">{{ __('User') }}</a>
                                </li>
                            @endcan
                            @can('Manage Role')
                                <li class="dash-item">
                                    <a class="dash-link" href="{{ route('roles.index') }}">{{ __('Role') }}</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endif
            @endif
            <!-- user-->
            <!-- user-->

            <!-- employee-->
            @if (Gate::check('Manage Employee'))
                <li class="dash-item dash-hasmenu {{ Request::segment(1) == 'employee' ? 'active dash-trigger' : '' }}">
                    <a href="javascript:void(0)" class="dash-link"><span class="dash-micon"><i class="ti ti-user"></i></span><span
                            class="dash-mtext">{{ __('Employee') }}</span><span class="dash-arrow"><i
                                data-feather="chevron-right"></i></span></a>
                    <ul class="dash-submenu">
                        @can('Manage Employee')
                            <li class="dash-item {{ Request::segment(1) == 'employee' ? 'active' : '' }}">
                                <a class="dash-link" href="{{ route('employee.index') }}">{{ __('Employee List') }}</a>
                            </li>
                        @endcan

                        @can('Manage Employee Profile')
                            <li class="dash-item">
                                <a class="dash-link" href="{{ route('employee.profile') }}">{{ __('Employee Profile') }}</a>
                            </li>
                        @endcan

                        @can('Manage Employee Last Login')
                            <li class="dash-item">
                                <a class="dash-link" href="{{ route('lastlogin') }}">{{ __('Last Login') }}</a>
                            </li>
                        @endcan

                        @if (\Auth::user()->type == 'employee')
                            @php
                                $employee = App\Models\Employee::where('user_id', \Auth::user()->id)->first();
                            @endphp
                            @if(!empty($employee))
                                <li class="dash-item">
                                    <a class="dash-link" href="{{ route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}">{{ __('My Profile') }}</a>
                                </li>
                            @endif
                        @endif
                    </ul>
                </li>
            @endif
            <!-- employee-->

            <!-- payroll-->
            @if (\Auth::user()->type != 'employee' && (Gate::check('Manage Set Salary') || Gate::check('Manage Pay Slip') || Gate::check('Manage Travel Expense')))
                <li
                    class="dash-item dash-hasmenu  {{ in_array(Request::segment(1), ['setsalary', 'travel-expenses']) ? 'dash-trigger active' : '' }}">
                    <a href="javascript:void(0)" class="dash-link">
                        <span class="dash-micon">
                            <i class="ti ti-receipt">
                            </i>
                        </span>
                        <span class="dash-mtext">
                            {{ __('Payroll') }}
                        </span>
                        <span class="dash-arrow"><i data-feather="chevron-right">
                            </i>
                        </span>
                    </a>
                    <ul class="dash-submenu ">
                        @can('Manage Set Salary')
                            <li class="dash-item {{ Request::segment(1) == 'setsalary' ? 'active' : '-' }}">
                                <a class="dash-link" href="{{ route('setsalary.index') }}">{{ __('Set Salary') }}</a>
                            </li>
                        @endcan
                        @can('Manage Pay Slip')
                            <li class="dash-item">
                                <a class="dash-link" href="{{ route('payslip.index') }}">{{ __('Payslip') }}</a>
                            </li>
                        @endcan
                        @if (\Auth::user()->type != 'super admin' && !\Auth::user()->isSuperAdminSideUser())
                            @can('Manage Travel Expense')
                                <li class="dash-item {{ Request::segment(1) == 'travel-expenses' ? 'active' : '-' }}">
                                    <a class="dash-link" href="{{ route('travel-expenses.index') }}">{{ __('Travel Expenses & Vouchers') }}</a>
                                </li>
                            @endcan
                        @endif
                    </ul>
                </li>
            @endif
            <!-- payroll-->

            @if (\Auth::user()->type == 'employee')
                <li
                    class="dash-item dash-hasmenu {{ in_array(Request::segment(1), ['setsalary', 'travel-expenses']) ? 'dash-trigger active' : '' }}">
                    <a href="javascript:void(0)" class="dash-link"><span class="dash-micon"><i
                                class="ti ti-receipt"></i></span><span
                            class="dash-mtext">{{ __('Payroll') }}</span><span class="dash-arrow"><i
                                data-feather="chevron-right"></i></span></a>
                    <ul class="dash-submenu">
                        <li class="dash-item {{ Request::segment(1) == 'setsalary' ? 'active' : '-' }}">
                            <a class="dash-link"
                                href="{{ route('setsalary.show', \Illuminate\Support\Facades\Crypt::encrypt(\Auth::user()->id)) }}">{{ __('Salary') }}</a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link" href="{{ route('payslip.index') }}">{{ __('Payslip') }}</a>
                        </li>
                        @can('Manage Travel Expense')
                            <li class="dash-item {{ Request::segment(1) == 'travel-expenses' ? 'active' : '-' }}">
                                <a class="dash-link" href="{{ route('travel-expenses.index') }}">{{ __('Travel Expenses & Vouchers') }}</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endif

            <!-- timesheet-->
            @if (Gate::check('Manage Attendance') || Gate::check('Manage Leave') || Gate::check('Manage TimeSheet'))
                <li
                    class="dash-item dash-hasmenu {{ in_array(Request::segment(1), ['timesheet', 'leave', 'attendanceemployee', 'attendance-requests', 'shiftturner']) ? 'dash-trigger active' : '' }}">
                    <a href="javascript:void(0)" class="dash-link"><span class="dash-micon"><i
                                class="ti ti-clock"></i></span><span
                            class="dash-mtext">{{ __('Timesheet') }}</span><span class="dash-arrow"><i
                                data-feather="chevron-right"></i></span></a>
                    <ul class="dash-submenu">
                        @can('Manage TimeSheet')
                            <li class="dash-item">
                                <a class="dash-link" href="{{ route('timesheet.index') }}">{{ __('Timesheet') }}</a>
                            </li>
                        @endcan
                        @can('Manage Leave')

                            <li class="dash-item {{ Request::segment(1) == 'calender' ? ' active' : '' }}">
                                <a class="dash-link" href="{{ route('leave.index') }}">{{ __('Manage Leave') }}</a>
                            </li>

                        @endcan
                        @if (Gate::check('Manage Attendance') || Gate::check('Manage Attendance Request'))
                            <li class="dash-item dash-hasmenu">
                                <a href="javascript:void(0)" class="dash-link"><span
                                        class="dash-mtext">{{ __('Attendance') }}</span><span class="dash-arrow"><i
                                            data-feather="chevron-right"></i></span></a>
                                <ul class="dash-submenu">
                                    @can('Manage Attendance')
                                        <li class="dash-item">
                                            <a class="dash-link"
                                                href="{{ route('attendanceemployee.index') }}">{{ __('Marked Attendance') }}</a>
                                        </li>
                                    @endcan
                                    @can('Create Attendance')
                                        <li class="dash-item">
                                            <a class="dash-link"
                                                href="{{ route('attendanceemployee.bulkattendance') }}">{{ __('Bulk Attendance') }}</a>
                                        </li>
                                    @endcan

                                    @can('Manage Attendance Request')
                                        <li class="dash-item">
                                            <a class="dash-link"
                                                href="{{ route('attendance-requests.index') }}">{{ __('Attendance Request') }}</a>
                                        </li>
                                    @endcan
                                </ul>
                            </li>
                        @endif

                        @can('Manage Rota')
                            <li class="dash-item {{ Request::segment(1) == 'rota' ? ' active' : '' }}">
                                <a class="dash-link" href="{{ route('rota.index') }}">{{ __('Manage Rota') }}</a>
                            </li>
                        @endcan
                        {{-- @if (\Auth::user()->type == 'company') --}}
                            @if (isset($setting['shift_change']) && $setting['shift_change'] == 'on')
                                <li class="dash-item">
                                    <a class="dash-link"
                                        href="{{ route('shiftturner.index') }}">{{ __('Shift Turner') }}</a>
                                </li>
                            @endif
                        {{-- @endif --}}

                        {{-- remove biometric code --}}
                        {{-- @can('Manage Biometric Attendance')

                            <li class="dash-item">
                                <a class="dash-link" href="{{ route('biometric-attendance.index') }}">{{ __('Biometric Attendance') }}</a>
                            </li>

                        @endcan --}}
                    </ul>
                </li>
            @endif
            <!--timesheet-->

            <!-- performance-->
            @if (Gate::check('Manage Indicator') || Gate::check('Manage Appraisal') || Gate::check('Manage Goal Tracking') || Gate::check('Manage Self Assessment'))
                <li class="dash-item dash-hasmenu {{ in_array(Request::segment(1), ['indicator', 'appraisal', 'goaltracking', 'self-assessments']) ? 'dash-trigger active' : '' }}">
                    <a href="javascript:void(0)" class="dash-link"><span class="dash-micon"><i
                                class="ti ti-3d-cube-sphere"></i></span><span
                            class="dash-mtext">{{ __('Performance') }}</span><span class="dash-arrow"><i
                                data-feather="chevron-right"></i></span></a>
                    <ul class="dash-submenu">
                        @can('Manage Indicator')
                            <li class="dash-item">
                                <a class="dash-link" href="{{ route('indicator.index') }}">{{ __('Indicator') }}</a>
                            </li>
                        @endcan

                        @can('Manage Appraisal')
                            <li class="dash-item">
                                <a class="dash-link" href="{{ route('appraisal.index') }}">{{ __('Appraisal') }}</a>
                            </li>
                        @endcan

                        @can('Manage Goal Tracking')
                            <li class="dash-item">
                                <a class="dash-link"
                                    href="{{ route('goaltracking.index') }}">{{ __('Goal Tracking') }}</a>
                            </li>
                        @endcan

                        @can('Manage Self Assessment')
                            <li class="dash-item {{ Request::segment(1) == 'self-assessments' ? 'active' : '' }}">
                                <a class="dash-link"
                                    href="{{ route('self-assessments.index') }}">{{ __('Self Assessment') }}</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endif
            <!--performance-->

            <!--fianance-->
            @if (Gate::check('Manage Account List') ||
                    Gate::check('Manage Payee') ||
                    Gate::check('Manage Payer') ||
                    Gate::check('Manage Deposit') ||
                    Gate::check('Manage Expense') ||
                    Gate::check('Manage Transfer Balance'))
                <li class="dash-item dash-hasmenu">
                    <a href="javascript:void(0)" class="dash-link"><span class="dash-micon"><i
                                class="ti ti-wallet"></i></span><span
                            class="dash-mtext">{{ __('Finance') }}</span><span class="dash-arrow"><i
                                data-feather="chevron-right"></i></span></a>
                    <ul class="dash-submenu">
                        @can('Manage Account List')
                            <li class="dash-item">
                                <a class="dash-link"
                                    href="{{ route('accountlist.index') }}">{{ __('Account List') }}</a>
                            </li>
                        @endcan
                        @if (\Auth::user()->type == 'company' || Gate::check('View Balance Account List') || Gate::check('Manage Account List'))
                            <li class="dash-item {{ optional(Request::route())->getName() == 'accountbalance' ? 'active' : '' }}">
                                <a class="dash-link"
                                    href="{{ route('accountbalance') }}">{{ __('Account Balance') }}</a>
                            </li>
                        @endif
                        @can('Manage Payee')
                            <li class="dash-item">
                                <a class="dash-link" href="{{ route('payees.index') }}">{{ __('Payees') }}</a>
                            </li>
                        @endcan

                        @can('Manage Payer')
                            <li class="dash-item">
                                <a class="dash-link" href="{{ route('payer.index') }}">{{ __('Payers') }}</a>
                            </li>
                        @endcan

                        @can('Manage Deposit')
                            <li class="dash-item">
                                <a class="dash-link" href="{{ route('deposit.index') }}">{{ __('Deposit') }}</a>
                            </li>
                        @endcan

                        @can('Manage Expense')
                            <li class="dash-item">
                                <a class="dash-link" href="{{ route('expense.index') }}">{{ __('Expense') }}</a>
                            </li>
                        @endcan

                        @can('Manage Transfer Balance')
                            <li class="dash-item">
                                <a class="dash-link"
                                    href="{{ route('transferbalance.index') }}">{{ __('Transfer Balance') }}</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endif
            <!-- fianance-->

            <!--trainning-->
            @if (Gate::check('Manage Trainer') || Gate::check('Manage Training'))
                <li
                    class="dash-item dash-hasmenu {{ Request::segment(1) == 'training' ? 'dash-trigger active' : '' }}">
                    <a href="javascript:void(0)" class="dash-link "><span class="dash-micon"><i
                                class="ti ti-school"></i></span><span
                            class="dash-mtext">{{ __('Training') }}</span><span class="dash-arrow"><i
                                data-feather="chevron-right"></i></span></a>
                    <ul class="dash-submenu">
                        @can('Manage Training')
                            <li class="dash-item {{ Request::segment(1) == 'training' ? ' active' : '' }}">
                                <a class="dash-link"
                                    href="{{ route('training.index') }}">{{ __('Training List') }}</a>
                            </li>
                        @endcan

                        @can('Manage Trainer')
                            <li class="dash-item ">
                                <a class="dash-link" href="{{ route('trainer.index') }}">{{ __('Trainer') }}</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endif

            <!-- tranning-->


            <!-- HR-->
            @if (Gate::check('Manage Award') ||
                    Gate::check('Manage Transfer') ||
                    Gate::check('Manage Resignation') ||
                    Gate::check('Manage Travel') ||
                    Gate::check('Manage Promotion') ||
                    Gate::check('Manage Complaint') ||
                    Gate::check('Manage Warning') ||
                    Gate::check('Manage Termination') ||
                    Gate::check('Manage Announcement') ||
                    Gate::check('Manage Holiday'))
                <li
                    class="dash-item dash-hasmenu {{ Request::segment(1) == 'holiday' ? 'dash-trigger active' : '' }}">
                    <a href="javascript:void(0)" class="dash-link">
                        <span class="dash-micon">
                            <i class="ti ti-user-plus"></i>
                        </span>
                        <span class="dash-mtext">{{ __('HR Admin Setup') }}</span>
                        <span class="dash-arrow">
                            <i data-feather="chevron-right"></i>
                        </span>
                    </a>
                    <ul class="dash-submenu">
                        @can('Manage Award')
                            <li class="dash-item {{ Request::segment(1) == 'award' ? 'active' : '' }}">
                                <a class="dash-link" href="{{ route('award.index') }}">{{ __('Award') }}</a>
                            </li>
                        @endcan
                        @can('Manage Transfer')
                            <li class="dash-item">
                                <a class="dash-link" href="{{ route('transfer.index') }}">{{ __('Transfer') }}</a>
                            </li>
                        @endcan
                        @can('Manage Resignation')
                            <li class="dash-item">
                                <a class="dash-link"
                                    href="{{ route('resignation.index') }}">{{ __('Resignation') }}</a>
                            </li>
                        @endcan
                        @can('Manage Travel')
                            <li class="dash-item">
                                <a class="dash-link" href="{{ route('travel.index') }}">{{ __('Trip') }}</a>
                            </li>
                        @endcan
                        @can('Manage Promotion')
                            <li class="dash-item">
                                <a class="dash-link" href="{{ route('promotion.index') }}">{{ __('Promotion') }}</a>
                            </li>
                        @endcan
                        @can('Manage Complaint')
                            <li class="dash-item">
                                <a class="dash-link" href="{{ route('complaint.index') }}">{{ __('Complaints') }}</a>
                            </li>
                        @endcan
                        @can('Manage Warning')
                            <li class="dash-item">
                                <a class="dash-link" href="{{ route('warning.index') }}">{{ __('Warning') }}</a>
                            </li>
                        @endcan
                        @can('Manage Termination')
                            <li class="dash-item {{ Request::segment(1) == 'termination' ? 'active' : '' }}">
                                <a class="dash-link"
                                    href="{{ route('termination.index') }}">{{ __('Termination') }}</a>
                            </li>
                        @endcan
                        @can('Manage Settlement')
                            <li class="dash-item {{ Request::segment(1) == 'settlement' ? 'active' : '' }}">
                                <a class="dash-link"
                                    href="{{ route('settlement.index') }}">{{ __('Full & Final Settlement') }}</a>
                            </li>
                        @endcan
                        @can('Manage Announcement')
                            <li class="dash-item">
                                <a class="dash-link"
                                    href="{{ route('announcement.index') }}">{{ __('Announcement') }}</a>
                            </li>
                        @endcan
                        @can('Manage Holiday')
                            <li class="dash-item {{ Request::segment(1) == 'holiday' ? ' active' : '' }}">
                                <a class="dash-link" href="{{ route('holiday.index') }}">{{ __('Holidays') }}</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endif
            <!-- HR-->

            <!-- recruitment-->
            @if (Gate::check('Manage Job') ||
                    Gate::check('Manage Job Application') ||
                    Gate::check('Manage Job OnBoard') ||
                    Gate::check('Manage Custom Question') ||
                    Gate::check('Manage Interview Schedule') ||
                    Gate::check('Manage Career'))
                <li
                    class="dash-item dash-hasmenu  {{ Request::segment(1) == 'job' || Request::segment(1) == 'job-application' ? 'dash-trigger active' : '' }} ">
                    <a href="javascript:void(0)" class="dash-link"><span class="dash-micon"><i
                                class="ti ti-license"></i></span><span
                            class="dash-mtext">{{ __('Recruitment') }}</span><span class="dash-arrow"><i
                                data-feather="chevron-right"></i></span></a>
                    <ul class="dash-submenu">
                        @can('Manage Job')
                            <li
                                class="dash-item {{ optional(Request::route())->getName() == 'job.index' ? 'active' : 'dash-hasmenu' }}">
                                <a class="dash-link" href="{{ route('job.index') }}">{{ __('Jobs') }}</a>
                            </li>
                        @endcan
                        @can('Manage Job')
                            <li
                                class="dash-item {{ optional(Request::route())->getName() == 'job.create' ? 'active' : 'dash-hasmenu' }}">
                                <a class="dash-link" href="{{ route('job.create') }}">{{ __('Job Create ') }}</a>
                            </li>
                        @endcan
                        @can('Manage Job Application')
                            <li class="dash-item {{ request()->is('job-application*') ? 'active' : '' }}">
                                <a class="dash-link"
                                    href="{{ route('job-application.index') }}">{{ __('Job Application') }}</a>
                            </li>
                        @endcan
                        @can('Manage Job Application')

                            <li class="dash-item {{ request()->is('candidates-job-applications') ? 'active' : '' }}">
                                <a class="dash-link"
                                    href="{{ route('job.application.candidate') }}">{{ __('Job Candidate') }}</a>
                            </li>
                        @endcan

                        @can('Manage Job OnBoard')
                            <li class="dash-item">
                                <a class="dash-link"
                                    href="{{ route('job.on.board') }}">{{ __('Job On-Boarding') }}</a>
                            </li>
                        @endcan

                        @can('Manage Custom Question')
                            <li class="dash-item">
                                <a class="dash-link"
                                    href="{{ route('custom-question.index') }}">{{ __('Custom Question') }}</a>
                            </li>
                        @endcan

                        @can('Manage Interview Schedule')
                            <li class="dash-item">
                                <a class="dash-link"
                                    href="{{ route('interview-schedule.index') }}">{{ __('Interview Schedule') }}</a>
                            </li>
                        @endcan

                        @can('Manage Career')
                            <li class="dash-item">
                                <a class="dash-link" href="{{ route('career', [\Auth::user()->creatorId(), $lang]) }}"
                                    target="_blank">{{ __('Career') }}</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endif
            <!-- recruitment-->
            <!-- Support-->
            @if (Gate::check('Manage Contract') || Gate::check('Manage Ticket'))
                <li class="dash-item dash-hasmenu {{ Request::segment(1) == 'ticket' || optional(Request::route())->getName() == 'contract.index' || optional(Request::route())->getName() == 'contract.show' ? 'dash-trigger active' : '' }}">
                    <a href="javascript:void(0)" class="dash-link">
                        <span class="dash-micon"><i class="ti ti-headset"></i></span>
                        <span class="dash-mtext">{{ __('Support') }}</span>
                        <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                    </a>
                    <ul class="dash-submenu">
                        @can('Manage Contract')
                            <li class="dash-item {{ optional(Request::route())->getName() == 'contract.index' || optional(Request::route())->getName() == 'contract.show' ? 'active' : '' }}">
                                <a class="dash-link" href="{{ route('contract.index') }}">{{ __('Contracts') }}</a>
                            </li>
                        @endcan
                        @can('Manage Ticket')
                            <li class="dash-item {{ Request::segment(1) == 'ticket' ? 'active' : '' }}">
                                <a class="dash-link" href="{{ route('ticket.index') }}">{{ __('Ticket') }}</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endif
            <!-- Support-->

            <!-- Scheduling & Meetings-->
            @if (Gate::check('Manage Event') || Gate::check('Manage Meeting') || Gate::check('Manage Zoom meeting'))
                <li class="dash-item dash-hasmenu {{ in_array(Request::segment(1), ['event', 'meeting', 'zoommeeting']) ? 'dash-trigger active' : '' }}">
                    <a href="javascript:void(0)" class="dash-link">
                        <span class="dash-micon"><i class="ti ti-calendar"></i></span>
                        <span class="dash-mtext">{{ __('Scheduling') }}</span>
                        <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                    </a>
                    <ul class="dash-submenu">
                        @can('Manage Event')
                            <li class="dash-item {{ Request::segment(1) == 'event' ? 'active' : '' }}">
                                <a class="dash-link" href="{{ route('event.index') }}">{{ __('Event/Task') }}</a>
                            </li>
                        @endcan
                        @can('Manage Meeting')
                            <li class="dash-item {{ Request::segment(1) == 'meeting' || Request::segment(2) == 'meeting' ? 'active' : '' }}">
                                <a class="dash-link" href="{{ route('meeting.index') }}">{{ __('Meeting') }}</a>
                            </li>
                        @endcan
                        @can('Manage Zoom meeting')
                            @if (\Auth::user()->type != 'super admin')
                                <li class="dash-item {{ Request::segment(1) == 'zoommeeting' ? 'active' : '' }}">
                                    <a class="dash-link" href="{{ route('zoom-meeting.index') }}">{{ __('Zoom Meeting') }}</a>
                                </li>
                            @endif
                        @endcan
                    </ul>
                </li>
            @endif
            <!-- Scheduling & Meetings-->

            <!-- Resources-->
            @if (Gate::check('Manage Assets') || Gate::check('Manage Document') || Gate::check('Manage Company Policy') || Gate::check('Manage HR Document Library') || \Auth::user()->type == 'super admin')
                <li class="dash-item dash-hasmenu {{ in_array(Request::segment(1), ['account-assets', 'document-upload', 'company-policy', 'hr-document-library']) ? 'dash-trigger active' : '' }}">
                    <a href="javascript:void(0)" class="dash-link">
                        <span class="dash-micon"><i class="ti ti-folders"></i></span>
                        <span class="dash-mtext">{{ __('Resources') }}</span>
                        <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                    </a>
                    <ul class="dash-submenu">
                        @can('Manage Assets')
                            <li class="dash-item {{ Request::segment(1) == 'account-assets' ? 'active' : '' }}">
                                <a class="dash-link" href="{{ route('account-assets.index') }}">{{ __('Assets') }}</a>
                            </li>
                        @endcan
                        @can('Manage Document')
                            <li class="dash-item {{ Request::segment(1) == 'document-upload' ? 'active' : '' }}">
                                <a class="dash-link" href="{{ route('document-upload.index') }}">{{ __('Document') }}</a>
                            </li>
                        @endcan
                        @can('Manage Company Policy')
                            <li class="dash-item {{ Request::segment(1) == 'company-policy' ? 'active' : '' }}">
                                <a class="dash-link" href="{{ route('company-policy.index') }}">{{ __('Company Policy') }}</a>
                            </li>
                        @endcan
            @if (Gate::check('Manage HR Document Library') || \Auth::user()->isSuperAdminSideUser())
                            <li class="dash-item {{ Request::segment(1) == 'hr-document-library' ? 'active' : '' }}">
                                <a class="dash-link" href="{{ route('hr-document-library.index') }}">{{ __('HR Document Library') }}</a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
            <!-- Resources-->
            <!--chats-->
            @if (!\Auth::user()->isSuperAdminSideUser())
                <li class="dash-item {{ Request::segment(1) == 'chats' ? 'active' : '' }}">
                    <a href="{{ url('chats') }}" class="dash-link"><span class="dash-micon"><i
                                class="ti ti-messages"></i></span><span
                            class="dash-mtext">{{ __('Messenger') }}</span></a>
                </li>
            @endif

            @if (\Auth::user()->type == 'company')
                <li
                    class="dash-item {{ optional(Request::route())->getName() == 'notification-templates.index' || Request::segment(1) == 'notification-templates-lang' ? 'active' : '' }}">
                    <a href="{{ route('notification-templates.index') }}" class="dash-link"><span
                            class="dash-micon"><i class="ti ti-bell"></i></span><span
                            class="dash-mtext">{{ __('Notification Template') }}</span></a>
                </li>
            @endif

            @if (\Auth::user()->isSuperAdminSideUser())
                @if (\Auth::user()->type == 'super admin' || Gate::check('Manage Plan'))
                    <li class="dash-item ">
                        <a href="{{ route('plans.index') }}" class="dash-link"><span
                                class="dash-micon"><i class=" ti ti-trophy"></i></span><span
                                class="dash-mtext">{{ __('Plan') }}</span></a>

                    </li>
                @endif

                @if (\Auth::user()->type == 'super admin' || Gate::check('Manage Plan Request'))
                    <li class="dash-item ">
                        <a href="{{ route('plan_request.index') }}" class="dash-link"><span
                                class="dash-micon"><i class="ti ti-arrow-down-right-circle"></i></span><span
                                class="dash-mtext">{{ __('Plan Request') }}</span></a>
                    </li>
                @endif

                @if (\Auth::user()->type == 'super admin' || Gate::check('Manage Storage Addons') || Gate::check('Manage Storage Addon'))
                    <li class="dash-item {{ Request::routeIs('storage-addons.*') ? 'active' : '' }}">
                        <a href="{{ route('storage-addons.index') }}" class="dash-link">
                            <span class="dash-micon"><i class="ti ti-database"></i></span>
                            <span class="dash-mtext">{{ __('Storage Addons') }}</span>
                        </a>
                    </li>
                @endif

                @if (\Auth::user()->type == 'super admin' || Gate::check('Manage Referral Program'))
                    <li class="dash-item dash-hasmenu  {{ Request::segment(1) == '' ? 'active' : '' }}">
                        <a href="{{ route('referral-program.index') }}" class="dash-link">
                            <span class="dash-micon"><i class="ti ti-discount-2"></i></span><span
                                class="dash-mtext">{{ __('Referral Program') }}</span>
                        </a>
                    </li>
                @endif

                @if (\Auth::user()->type == 'super admin' || Gate::check('manage coupon'))
                    <li
                        class="dash-item dash-hasmenu {{ Request::segment(1) == 'coupons' ? 'active' : '' }}">
                        <a href="{{ route('coupons.index') }}" class="dash-link"><span
                                class="dash-micon"><i class="ti ti-gift"></i></span><span
                                class="dash-mtext">{{ __('Coupon') }}</span></a>

                    </li>
                @endif

                @if (\Auth::user()->type == 'super admin' || Gate::check('Manage Order'))
                    <li class="dash-item ">
                        <a href="{{ route('order.index') }}"
                            class="dash-link {{ request()->is('orders*') ? 'active' : '' }}"><span
                                class="dash-micon"><i class="ti ti-shopping-cart"></i></span><span
                                class="dash-mtext">{{ __('Order') }}</span></a>

                    </li>
                @endif

                @if (\Auth::user()->type == 'super admin' || Gate::check('Manage Email Template'))
                    <li
                        class="dash-item {{ optional(Request::route())->getName() == 'email_template.index' || Request::segment(1) == 'email_template_lang' || optional(Request::route())->getName() == 'manageemail.lang' ? 'active' : '' }}">
                        <a href="{{ route('email_template.index') }}" class="dash-link"><span
                                class="dash-micon"><i class="ti ti-template"></i></span><span
                                class="dash-mtext">{{ __('Email Templates') }}</span></a>

                    </li>
                @endif
            @endif
            <!--report-->
            <!-- @if (Gate::check('Manage Report'))
<li class="dash-item dash-hasmenu">
<a href="javascript:void(0)" class="dash-link"><span class="dash-micon"><i
class="ti ti-list"></i></span><span
class="dash-mtext">{{ __('Report') }}</span><span
class="dash-arrow"><i data-feather="chevron-right"></i></span></a>
<ul class="dash-submenu">
@can('Manage Report')
<li class="dash-item">
<a class="dash-link"
href="{{ route('report.income-expense') }}">{{ __('Income Vs Expense') }}</a>
</li>

<li class="dash-item">
<a class="dash-link"
href="{{ route('report.monthly.attendance') }}">{{ __('Monthly Attendance') }}</a>
</li>

<li class="dash-item">
<a class="dash-link"
href="{{ route('report.leave') }}">{{ __('Leave') }}</a>
</li>


<li class="dash-item">
<a class="dash-link"
href="{{ route('report.account.statement') }}">{{ __('Account Statement') }}</a>
</li>



<li class="dash-item">
<a class="dash-link"
href="{{ route('report.timesheet') }}">{{ __('Timesheet') }}</a>
</li>
@endcan


</ul>
</li>
@endif -->


            <!--constant-->
            @if (Gate::check('Manage Department') ||
                    Gate::check('Manage Designation') ||
                    Gate::check('Manage Document Type') ||
                    Gate::check('Manage Branch') ||
                    Gate::check('Manage Award Type') ||
                    Gate::check('Manage Termination Types') ||
                    Gate::check('Manage Payslip Type') ||
                    Gate::check('Manage Allowance Option') ||
                    Gate::check('Manage Loan Options') ||
                    Gate::check('Manage Deduction Options') ||
                    Gate::check('Manage Expense Type') ||
                    Gate::check('Manage Income Type') ||
                    Gate::check('Manage Payment Type') ||
                    Gate::check('Manage Leave Type') ||
                    Gate::check('Manage Training Type') ||
                    Gate::check('Manage Job Category') ||
                    Gate::check('Manage Job Stage'))
                <li
                    class="dash-item dash-hasmenu {{ optional(Request::route())->getName() == 'branch.index' ||optional(Request::route())->getName() == 'department.index' ||optional(Request::route())->getName() == 'designation.index' ||optional(Request::route())->getName() == 'leavetype.index' ||optional(Request::route())->getName() == 'document.index' ||optional(Request::route())->getName() == 'paysliptype.index' ||optional(Request::route())->getName() == 'allowanceoption.index' ||optional(Request::route())->getName() == 'loanoption.index' ||optional(Request::route())->getName() == 'deductionoption.index' ||optional(Request::route())->getName() == 'goaltype.index' ||optional(Request::route())->getName() == 'trainingtype.index' ||optional(Request::route())->getName() == 'awardtype.index' ||optional(Request::route())->getName() == 'terminationtype.index' ||optional(Request::route())->getName() == 'job-category.index' ||optional(Request::route())->getName() == 'job-stage.index' ||optional(Request::route())->getName() == 'performanceType.index' ||optional(Request::route())->getName() == 'competencies.index' ||optional(Request::route())->getName() == 'expensetype.index' ||optional(Request::route())->getName() == 'incometype.index' ||optional(Request::route())->getName() == 'paymenttype.index' ||optional(Request::route())->getName() == 'contract_type.index'? ' active': '' }}">
                    <a href="{{ route('branch.index') }}" class="dash-link"><span class="dash-micon"><i
                                class="ti ti-table"></i></span><span
                            class="dash-mtext">{{ __('HRM System Setup') }}</span></a>
                </li>
                {{-- <ul class="dash-submenu">
@can('Manage Branch')
<li class="dash-item {{ request()->is('branch*') ? 'active' : '' }}">
<a class="dash-link"
href="{{ route('branch.index') }}">{{ __('Branch') }}</a>
</li>
@endcan
@can('Manage Department')
<li class="dash-item {{ request()->is('department*') ? 'active' : '' }}">
<a class="dash-link"
href="{{ route('department.index') }}">{{ __('Department') }}</a>
</li>
@endcan
@can('Manage Designation')
<li class="dash-item {{ request()->is('designation*') ? 'active' : '' }}">
<a class="dash-link"
href="{{ route('designation.index') }}">{{ __('Designation') }}</a>
</li>
@endcan
@can('Manage Document Type')
<li class="dash-item {{ request()->is('document*') ? 'active' : '' }}">
<a class="dash-link"
href="{{ route('document.index') }}">{{ __('Document Type') }}</a>
</li>
@endcan

@can('Manage Award Type')
<li class="dash-item {{ request()->is('awardtype*') ? 'active' : '' }}">
<a class="dash-link"
href="{{ route('awardtype.index') }}">{{ __('Award Type') }}</a>
</li>
@endcan
@can('Manage Termination Types')
<li
class="dash-item {{ request()->is('terminationtype*') ? 'active' : '' }}">
<a class="dash-link"
href="{{ route('terminationtype.index') }}">{{ __('Termination Type') }}</a>
</li>
@endcan
@can('Manage Payslip Type')
<li class="dash-item {{ request()->is('paysliptype*') ? 'active' : '' }}">
<a class="dash-link"
href="{{ route('paysliptype.index') }}">{{ __('Payslip Type') }}</a>
</li>
@endcan
@can('Manage Allowance Option')
<li
class="dash-item {{ request()->is('allowanceoption*') ? 'active' : '' }}">
<a class="dash-link"
href="{{ route('allowanceoption.index') }}">{{ __('Allowance Option') }}</a>
</li>
@endcan
@can('Manage Loan Option')
<li class="dash-item {{ request()->is('loanoption*') ? 'active' : '' }}">
<a class="dash-link"
href="{{ route('loanoption.index') }}">{{ __('Loan Option') }}</a>
</li>
@endcan
@can('Manage Deduction Option')
<li
class="dash-item {{ request()->is('deductionoption*') ? 'active' : '' }}">
<a class="dash-link"
href="{{ route('deductionoption.index') }}">{{ __('Deduction Option') }}</a>
</li>
@endcan
@can('Manage Expense Type')
<li class="dash-item {{ request()->is('expensetype*') ? 'active' : '' }}">
<a class="dash-link"
href="{{ route('expensetype.index') }}">{{ __('Expense Type') }}</a>
</li>
@endcan
@can('Manage Income Type')
<li class="dash-item {{ request()->is('incometype*') ? 'active' : '' }}">
<a class="dash-link"
href="{{ route('incometype.index') }}">{{ __('Income Type') }}</a>
</li>
@endcan
@can('Manage Payment Type')
<li class="dash-item {{ request()->is('paymenttype*') ? 'active' : '' }}">
<a class="dash-link"
href="{{ route('paymenttype.index') }}">{{ __('Payment Type') }}</a>
</li>
@endcan
@can('Manage Leave Type')
<li class="dash-item {{ request()->is('leavetype*') ? 'active' : '' }}">
<a class="dash-link"
href="{{ route('leavetype.index') }}">{{ __('Leave Type') }}</a>
</li>
@endcan
@can('Manage Termination Type')
<li
class="dash-item {{ request()->is('terminationtype*') ? 'active' : '' }}">
<a class="dash-link"
href="{{ route('terminationtype.index') }}">{{ __('Termination Type') }}</a>
</li>
@endcan
@can('Manage Goal Type')
<li class="dash-item {{ request()->is('goaltype*') ? 'active' : '' }}">
<a class="dash-link"
href="{{ route('goaltype.index') }}">{{ __('Goal Type') }}</a>
</li>
@endcan
@can('Manage Training Type')
<li class="dash-item {{ request()->is('trainingtype*') ? 'active' : '' }}">
<a class="dash-link"
href="{{ route('trainingtype.index') }}">{{ __('Training Type') }}</a>
</li>
@endcan

@if (\Auth::user()->type !== 'hr')
@can('Manage Job Category')
<li
class="dash-item {{ request()->is('job-category*') ? 'active' : '' }}">
<a class="dash-link"
href="{{ route('job-category.index') }}">{{ __('Job Category') }}</a>
</li>
@endcan
@endif

@if (\Auth::user()->type !== 'hr')
@can('Manage Job Stage')
<li
class="dash-item {{ request()->is('job-stage*') ? 'active' : '' }}">
<a class="dash-link"
href="{{ route('job-stage.index') }}">{{ __('Job Stage') }}</a>
</li>
@endcan
@endif

<li
class="dash-item {{ request()->is('performanceType*') ? 'active' : '' }}">
<a class="dash-link"
href="{{ route('performanceType.index') }}">{{ __('Performance Type') }}</a>
</li>

@can('Manage Competencies')
<li class="dash-item {{ request()->is('competencies*') ? 'active' : '' }}">

<a class="dash-link"
href="{{ route('competencies.index') }}">{{ __('Competencies') }}</a>
</li>
@endcan
</ul> --}}
            @endif
            <!--constant-->

            @if (\Auth::user()->isSuperAdminSideUser())
                {{-- 
                <li class="dash-item {{ optional(Request::route())->getName() == 'system-modules.index' ? 'active' : '' }}">
                    <a href="{{ route('system-modules.index') }}" class="dash-link">
                        <span class="dash-micon"><i class="ti ti-layout-grid"></i></span>
                        <span class="dash-mtext">{{ __('System Modules') }}</span>
                    </a>
                </li>
                --}}

                @if (\Auth::user()->type == 'super admin' || Gate::check('Manage Landing Page'))
                    @include('landingpage::menu.landingpage')
                @endif

                @if (\Auth::user()->type == 'super admin' || Gate::check('Manage System Setup') || Gate::check('Manage System Settings'))
                    <li class="dash-item ">
                        <a href="{{ route('settings.index') }}" class="dash-link"><span
                                class="dash-micon"><i class="ti ti-settings"></i></span><span
                                class="dash-mtext">{{ __('Settings') }}</span></a>
                    </li>
                @endif
            @endif
            <!--------------------- Start System Setup ----------------------------------->

            @if (!\Auth::user()->isSuperAdminSideUser())

                @if (\Auth::user()->type == 'company' || Gate::check('Manage Plan') || Gate::check('Manage Order') || Gate::check('Manage Company Settings') || Gate::check('Manage System Settings'))
                    <li class="dash-item dash-hasmenu">
                        <a href="javascript:void(0)" class="dash-link ">
                            <span class="dash-micon"><i class="ti ti-settings"></i></span><span
                                class="dash-mtext">{{ __('System Setup') }}</span><span
                                class="dash-arrow">
                                <i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="dash-submenu">
                            @if (\Auth::user()->type == 'company' || Gate::check('Manage Company Settings') || Gate::check('Manage System Settings'))
                                <li
                                    class="dash-item {{ Request::segment(1) == 'company-setting' || optional(Request::route())->getName() == 'settings.index' ? ' active' : '' }}">
                                    <a href="{{ route('settings.index') }}"
                                        class="dash-link">{{ __('System Settings') }}</a>
                                </li>
                            @endif
                            @if (Gate::check('Manage Plan'))
                                <li
                                    class="dash-item{{ optional(Request::route())->getName() == 'plans.index' || optional(Request::route())->getName() == 'stripe' ? ' active' : '' }}">
                                    <a href="{{ route('plans.index') }}"
                                        class="dash-link">{{ __('Setup Subscription Plan') }}</a>
                                </li>
                            @endif
                            <li
                                class="dash-item{{ optional(Request::route())->getName() == 'referral-program.company' ? ' active' : '' }}">
                                <a href="{{ route('referral-program.company') }}"
                                    class="dash-link">{{ __('Referral Program') }}</a>
                            </li>
                            @if (\Auth::user()->type == 'super admin' || \Auth::user()->type == 'company')
                                <li
                                    class="dash-item {{ Request::segment(1) == 'order' ? 'active' : '' }}">
                                    <a href="{{ route('order.index') }}"
                                        class="dash-link">{{ __('Order') }}</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
            @endif

            <!--------------------- End System Setup ----------------------------------->
</ul>

</div>
</div>
</nav>
