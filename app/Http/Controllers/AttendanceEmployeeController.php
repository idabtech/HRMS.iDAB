<?php

namespace App\Http\Controllers;

use App\Imports\AttendanceImport;
use App\Models\AttendanceEmployee;
use App\Models\AttendanceRequest;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\IpRestrict;
use App\Models\User;
use App\Models\Utility;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceEmployeeController extends Controller
{
    public function index(Request $request)
    {
        if (\Auth::user()->can('Manage Attendance')) {
            $branch = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            // $branch->prepend('All', '');

            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend('All', '');

            if (\Auth::user()->type == 'employee') {
                $emp = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;

                $attendanceEmployee = AttendanceEmployee::where('employee_id', $emp);

                if ($request->type == 'monthly' && !empty($request->month)) {
                    $month = date('m', strtotime($request->month));
                    $year  = date('Y', strtotime($request->month));


                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

                    // old date
                    // $end_date   = date($year . '-' . $month . '-t');

                    $attendanceEmployee->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                } elseif ($request->type == 'daily' && !empty($request->date)) {
                    $attendanceEmployee->where('date', $request->date);
                } else {
                    $month      = date('m');
                    $year       = date('Y');
                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

                    // old date
                    // $end_date   = date($year . '-' . $month . '-t');

                    $attendanceEmployee->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                }

                $attendanceEmployee = $attendanceEmployee->latest()->get();
                $employees = [];
            } else {

                $monthInput = $request->input('month');
                $attendanceDate = date('Y-m-d', strtotime($monthInput . '-01'));
                $employee = Employee::select('id')->where('created_by', \Auth::user()->creatorId());
                if (!empty($request->branch)) {
                    $employee->where('branch_id', $request->branch);
                }

                if (!empty($request->department)) {
                    $employee->where('department_id', $request->department);
                }

                if (!empty($request->employee)) {
                    $employee->where('id', $request->employee);
                }
                $employee = $employee->get()->pluck('id');

                $attendanceEmployee = AttendanceEmployee::whereIn('employee_id', $employee);
                if ($request->type == 'monthly' && !empty($request->month)) {

                    $month = date('m', strtotime($request->month));
                    $year  = date('Y', strtotime($request->month));
                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

                    // old date
                    // $end_date   = date($year . '-' . $month . '-t');

                    $attendanceEmployee->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                } elseif ($request->type == 'daily' && !empty($request->date)) {
                    $attendanceEmployee->where('date', $request->date);
                } else {

                    $month      = date('m');
                    $year       = date('Y');
                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));
                    // old date
                    // $end_date   = date($year . '-' . $month . '-t');

                    $attendanceEmployee->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                }

                $attendanceEmployee = $attendanceEmployee->latest()->get();
                $employees = Employee::where('created_by', Auth::user()->creatorId());

                if (!empty($request->branch)) {
                    $employees->where('branch_id', $request->branch);
                }

                if (!empty($request->department)) {
                    $employees->where('department_id', $request->department);
                }

                $employees = $employees->pluck('name', 'id');
            }
            return view('attendance.index', compact('attendanceEmployee', 'branch', 'department', 'employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create Attendance')) {
            $employees = User::where('created_by', '=', Auth::user()->creatorId())->where('type', '=', "employee")->get()->pluck('name', 'id');

            return view('attendance.create', compact('employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Attendance')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required',
                    'date' => 'required',
                    'clock_in' => 'required',
                    'clock_out' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $employee = Employee::find($request->employee_id);

            if ($employee && $employee->company_start_time && $employee->company_end_time) {
                $startTime = $employee->company_start_time;
                $endTime   = $employee->company_end_time;
            } else {
                $startTime  = Utility::getValByName('company_start_time');
                $endTime    = Utility::getValByName('company_end_time');
            }
            $attendance = AttendanceEmployee::where('employee_id', '=', $request->employee_id)->where('date', '=', $request->date)->where('clock_out', '=', '00:00:00')->get()->toArray();
            if ($attendance) {
                return redirect()->route('attendanceemployee.index')->with('error', __('Employee Attendance Already Created.'));
            } else {
                $date = date("Y-m-d");

                $totalLateSeconds = strtotime($request->clock_in) - strtotime($date . $startTime);

                $hours = floor($totalLateSeconds / 3600);
                $mins  = floor($totalLateSeconds / 60 % 60);
                $secs  = floor($totalLateSeconds % 60);
                $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);


                //early Leaving
                $totalEarlyLeavingSeconds = strtotime($date . $endTime) - strtotime($request->clock_out);
                $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                $secs                     = floor($totalEarlyLeavingSeconds % 60);
                $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);


                if (strtotime($request->clock_out) > strtotime($date . $endTime)) {
                    //Overtime
                    $totalOvertimeSeconds = strtotime($request->clock_out) - strtotime($date . $endTime);
                    $hours                = floor($totalOvertimeSeconds / 3600);
                    $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                    $secs                 = floor($totalOvertimeSeconds % 60);
                    $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                } else {
                    $overtime = '00:00:00';
                }

                $employeeAttendance                = new AttendanceEmployee();
                $employeeAttendance->employee_id   = $request->employee_id;
                $employeeAttendance->date          = $date;
                $employeeAttendance->status        = 'Present';
                $employeeAttendance->clock_in      = $request->clock_in . ':00';
                $employeeAttendance->clock_out     = $request->clock_out . ':00';
                $employeeAttendance->late          = $late;
                $employeeAttendance->early_leaving = $earlyLeaving;
                $employeeAttendance->overtime      = $overtime;
                $employeeAttendance->total_rest    = '00:00:00';
                $employeeAttendance->created_by    = \Auth::user()->creatorId();
                $employeeAttendance->save();

                return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully created.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function show(Request $request)
    {
        // return redirect()->back();
        return redirect()->route('attendanceemployee.index');
    }
    public function edit($id)
    {
        if (\Auth::user()->can('Edit Attendance')) {
            $attendanceEmployee = AttendanceEmployee::where('id', $id)->first();
            $employees          = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            return view('attendance.edit', compact('attendanceEmployee', 'employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, $id)
    {

        if ($request->has('employee_id')) {
            // Admin/HR editing attendance from management list
            $employeeId = AttendanceEmployee::where('employee_id', $request->employee_id)->first();
            $check = AttendanceEmployee::where('id', '=', $id)->where('employee_id', '=', $request->employee_id)->where('date', $request->date)->first();

            if (!empty($employeeId) || !empty($check)) {
                $employee = Employee::find($request->employee_id);

                if ($employee && $employee->company_start_time && $employee->company_end_time) {
                    $startTime = $employee->company_start_time;
                    $endTime   = $employee->company_end_time;
                } else {
                    $startTime  = Utility::getValByName('company_start_time');
                    $endTime    = Utility::getValByName('company_end_time');
                }

                $clockIn = $request->clock_in;
                $clockOut = $request->clock_out;

                if ($clockIn) {
                    $status = "present";
                } else {
                    $status = "leave";
                }

                $totalLateSeconds = strtotime($clockIn) - strtotime($startTime);

                $hours = floor($totalLateSeconds / 3600);
                $mins  = floor($totalLateSeconds / 60 % 60);
                $secs  = floor($totalLateSeconds % 60);
                $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                $totalEarlyLeavingSeconds = strtotime($endTime) - strtotime($clockOut);
                $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                $secs                     = floor($totalEarlyLeavingSeconds % 60);
                $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                if (strtotime($clockOut) > strtotime($endTime)) {
                    //Overtime
                    $totalOvertimeSeconds = strtotime($clockOut) - strtotime($endTime);
                    $hours                = floor($totalOvertimeSeconds / 3600);
                    $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                    $secs                 = floor($totalOvertimeSeconds % 60);
                    $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                } else {
                    $overtime = '00:00:00';
                }
                if ($check && $check->date == date('Y-m-d')) {
                    $check->update([
                        'late' => $late,
                        'early_leaving' => ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00',
                        'overtime' => $overtime,
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut
                    ]);

                    return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully updated.'));
                } else {
                    return redirect()->route('attendanceemployee.index')->with('error', __('You can only update current day attendance.'));
                }
            } else {
                return redirect()->back()->with('error', __('Employee not avaliable'));
            }
        }

        $employeeId      = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
        if ($employeeId == 0) {
            $empUser = Employee::where('user_id', Auth::user()->id)->first();
            $employeeId = $empUser ? $empUser->id : 0;
        }
        $todayAttendance = AttendanceEmployee::where('employee_id', '=', $employeeId)->where('date', date('Y-m-d'))->first();

        $employee = Employee::find($employeeId);

        if ($employee && $employee->company_start_time && $employee->company_end_time) {
            $startTime = $employee->company_start_time;
            $endTime   = $employee->company_end_time;
        } else {
            $startTime  = Utility::getValByName('company_start_time');
            $endTime    = Utility::getValByName('company_end_time');
        }

        if (Auth::user()->type == 'employee') {

            $date = date("Y-m-d");
            $time = date("H:i:s");

            //early Leaving
            $totalEarlyLeavingSeconds = strtotime($date . $endTime) - time();
            $hours                    = floor($totalEarlyLeavingSeconds / 3600);
            $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
            $secs                     = floor($totalEarlyLeavingSeconds % 60);
            $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

            if (time() > strtotime($date . $endTime)) {
                //Overtime
                $totalOvertimeSeconds = time() - strtotime($date . $endTime);
                $hours                = floor($totalOvertimeSeconds / 3600);
                $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                $secs                 = floor($totalOvertimeSeconds % 60);
                $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } else {
                $overtime = '00:00:00';
            }

            $attendanceEmployee['clock_out']     = $time;
            $attendanceEmployee['early_leaving'] = $earlyLeaving;
            $attendanceEmployee['overtime']      = $overtime;

            if (!empty($date)) {
                $attendanceEmployee['date']       =  $date;
            }

            AttendanceEmployee::where('id', $id)->update($attendanceEmployee);

            return redirect()->route('dashboard')->with('success', __('Employee successfully clock Out.'));
        } else {
            $date = date("Y-m-d");
            $clockout_time = date("H:i:s");
            //late
            $totalLateSeconds = strtotime($clockout_time) - strtotime($date . $startTime);

            $hours            = abs(floor($totalLateSeconds / 3600));
            $mins             = abs(floor($totalLateSeconds / 60 % 60));
            $secs             = abs(floor($totalLateSeconds % 60));

            $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

            //early Leaving
            $totalEarlyLeavingSeconds = strtotime($date . $endTime) - strtotime($clockout_time);
            $hours                    = floor($totalEarlyLeavingSeconds / 3600);
            $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
            $secs                     = floor($totalEarlyLeavingSeconds % 60);
            $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);


            if (strtotime($clockout_time) > strtotime($date . $endTime)) {
                //Overtime
                $totalOvertimeSeconds = strtotime($clockout_time) - strtotime($date . $endTime);
                $hours                = floor($totalOvertimeSeconds / 3600);
                $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                $secs                 = floor($totalOvertimeSeconds % 60);
                $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } else {
                $overtime = '00:00:00';
            }

            $attendanceEmployee                = AttendanceEmployee::find($id);
            $attendanceEmployee->clock_out     = $clockout_time;
            $attendanceEmployee->late          = $late;
            $attendanceEmployee->early_leaving = $earlyLeaving;
            $attendanceEmployee->overtime      = $overtime;
            $attendanceEmployee->total_rest    = '00:00:00';

            $attendanceEmployee->save();

            return redirect()->back()->with('success', __('Employee attendance successfully updated.'));
        }
    }

    public function destroy($id)
    {
        if (\Auth::user()->can('Delete Attendance')) {
            $attendance = AttendanceEmployee::where('id', $id)->first();

            $attendance->delete();

            return redirect()->route('attendanceemployee.index')->with('success', __('Attendance successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function attendance(Request $request)
    {
        $settings = Utility::settings();

        if (!empty($settings['ip_restrict']) && $settings['ip_restrict'] == 'on') {
            $userIp = $request->input('client_ip') ?? $request->header('X-Forwarded-For') ?? $request->header('X-Real-IP') ?? request()->ip();
            if (str_contains($userIp, ',')) {
                $userIp = trim(explode(',', $userIp)[0]);
            }
            $ip     = IpRestrict::where('created_by', Auth::user()->creatorId())->whereIn('ip', [$userIp])->first();
            if (empty($ip)) {
                if ($request->wantsJson() || $request->is('api/*')) {
                    return response()->json(['success' => false, 'message' => __('This IP address (:ip) is not allowed to clock in & clock out.', ['ip' => $userIp])], 403);
                }
                return redirect()->back()->with('error', __('This IP address (:ip) is not allowed to clock in & clock out.', ['ip' => $userIp]));
            }
        }

        $employeeId = !empty(Auth::user()->employee) ? Auth::user()->employee->id : 0;
        $date       = date("Y-m-d");
        $time       = date("H:i:s");

        $employee = Employee::find($employeeId);

        if ($employee && $employee->company_start_time && $employee->company_end_time) {
            $startTime = $employee->company_start_time;
        } else {
            $startTime  = Utility::getValByName('company_start_time');
        }

        // === Handle Break Button ===
        if ($request->has('break')) {
            $attendance = AttendanceRequest::where('employee_id', $employeeId)
                ->whereDate('requested_at', $date)
                ->where('type', 'clock_in')
                ->where('status', 'pending')
                ->latest()
                ->first();

            if (!$attendance) {
                $attendance = AttendanceEmployee::where('employee_id', $employeeId)
                    ->where('date', $date)
                    ->where('clock_out', '00:00:00')
                    ->latest()
                    ->first();
            }
            if ($attendance) {
                $breakType = strtolower($request->input('break_type', 'lunch','tea'));
                $validBreakTypes = ['lunch','tea'];

                if (!in_array($breakType, $validBreakTypes)) {
                    if ($request->wantsJson() || $request->is('api/*')) {
                        return response()->json(['success' => false, 'message' => __('Invalid break type.')], 400);
                    }
                    return redirect()->back()->with('error', __('Invalid break type.'));
                }

                $breaks = $attendance->breaks ? json_decode($attendance->breaks, true) : [];
                $lastBreak = !empty($breaks) ? end($breaks) : null;

                if (empty($breaks) || ($lastBreak['end'] ?? null) !== null) {
                    if (!$this->isBreakStartAllowed($employee, $time, $breakType)) {
                        if ($request->wantsJson() || $request->is('api/*')) {
                            return response()->json(['success' => false, 'message' => __(':type break can only be started within the configured time window.', ['type' => ucfirst($breakType)])], 400);
                        }
                        return redirect()->back()->with('error', __(':type break can only be started within the configured time window.', ['type' => ucfirst($breakType)]));
                    }

                    // Start a new break
                    $breaks[] = [
                        'type' => $breakType,
                        'start' => $time,
                        'end' => null,
                        'duration' => '00:00:00',
                        'counted_duration' => '00:00:00',
                        'over_break' => '00:00:00',
                        'reason' => null,
                    ];

                    $attendance->breaks = json_encode($breaks);
                    $attendance->save();

                    return redirect()->back()->with('success', __('Break started successfully.'));
                }

                // End the current break
                $lastIndex = count($breaks) - 1;
                $currentBreak = $breaks[$lastIndex];
                $currentType = strtolower($currentBreak['type'] ?? 'lunch');
                $breaks[$lastIndex]['end'] = $time;

                $startDateTime = strtotime($date . ' ' . $currentBreak['start']);
                $endDateTime = strtotime($date . ' ' . $time);
                $actualSeconds = max(0, $endDateTime - $startDateTime);

                $allowedBreakSeconds = $this->getAllowedBreakSeconds($employee, $currentType);

                if ($actualSeconds > $allowedBreakSeconds && empty(trim($request->input('break_reason', '')))) {
                    if ($request->wantsJson() || $request->is('api/*')) {
                        return response()->json(['success' => false, 'message' => __(ucfirst($currentType) . ' break exceeded allowed time. Please provide a reason.')], 400);
                    }
                    return redirect()->back()->with('error', __(ucfirst($currentType) . ' break exceeded allowed time. Please provide a reason.'));
                }

                $countedSeconds = min($actualSeconds, $allowedBreakSeconds);
                $overflowSeconds = max(0, $actualSeconds - $allowedBreakSeconds);

                $breaks[$lastIndex]['duration'] = gmdate('H:i:s', $actualSeconds);
                $breaks[$lastIndex]['counted_duration'] = gmdate('H:i:s', $countedSeconds);
                $breaks[$lastIndex]['over_break'] = gmdate('H:i:s', $overflowSeconds);
                $breaks[$lastIndex]['reason'] = $request->input('break_reason') ? trim($request->input('break_reason')) : ($breaks[$lastIndex]['reason'] ?? null);

                $totalLunchSeconds = 0;
                $totalTeaSeconds = 0;
                $totalActualBreakSeconds = 0;
                $totalOverBreakSeconds = 0;

                foreach ($breaks as $b) {
                    if (!empty($b['start']) && !empty($b['end'])) {
                        $breakSeconds = strtotime($date . ' ' . $b['end']) - strtotime($date . ' ' . $b['start']);
                        $totalActualBreakSeconds += $breakSeconds;

                        $breakType = strtolower($b['type'] ?? 'lunch');
                        if ($breakType === 'lunch') {
                            $totalLunchSeconds += $breakSeconds;
                        } elseif ($breakType === 'tea') {
                            $totalTeaSeconds += $breakSeconds;
                        }
                    }
                }

                $allowedLunchSeconds = $this->getAllowedBreakSeconds($employee, 'lunch');
                $allowedTeaSeconds = $this->getAllowedBreakSeconds($employee, 'tea');
                $lunchOverbreakSeconds = max(0, $totalLunchSeconds - $allowedLunchSeconds);
                $teaOverbreakSeconds = max(0, $totalTeaSeconds - $allowedTeaSeconds);
                $totalOverBreakSeconds += $lunchOverbreakSeconds + $teaOverbreakSeconds;

                // Additional enforcement: require reason when cumulative break time exceeds allowed
                // or (in fixed mode) when break end time goes beyond configured end window.
                $refreshSettings = Utility::settings();
                $breakMode = $employee->refresh_type ?: $employee->shift_break_type ?: 'fixed';

                $providedReason = trim($request->input('break_reason', ''));

                if ($breakMode === 'fixed') {
                    if ($currentType === 'lunch') {
                        $lunchEndTime = $employee->lunch_end ?? $refreshSettings['lunch_end'] ?? '13:00:00';
                        $lunchEndDateTime = strtotime($date . ' ' . $lunchEndTime);

                        if ($endDateTime > $lunchEndDateTime && empty($providedReason)) {
                            if ($request->wantsJson() || $request->is('api/*')) {
                                return response()->json(['success' => false, 'message' => __('Lunch break went past configured end time. Please provide a reason.')], 400);
                            }
                            return redirect()->back()->with('error', __('Lunch break went past configured end time. Please provide a reason.'));
                        }

                        if ($totalLunchSeconds > $allowedLunchSeconds && empty($providedReason)) {
                            if ($request->wantsJson() || $request->is('api/*')) {
                                return response()->json(['success' => false, 'message' => __('Total lunch time exceeded allowed minutes. Please provide a reason.')], 400);
                            }
                            return redirect()->back()->with('error', __('Total lunch time exceeded allowed minutes. Please provide a reason.'));
                        }
                    } else {
                        $teaEndTime = $employee->tea_end ?? $refreshSettings['tea_end'] ?? '16:00:00';
                        $teaEndDateTime = strtotime($date . ' ' . $teaEndTime);

                        if ($endDateTime > $teaEndDateTime && empty($providedReason)) {
                            if ($request->wantsJson() || $request->is('api/*')) {
                                return response()->json(['success' => false, 'message' => __('Tea break went past configured end time. Please provide a reason.')], 400);
                            }
                            return redirect()->back()->with('error', __('Tea break went past configured end time. Please provide a reason.'));
                        }

                        if ($totalTeaSeconds > $allowedTeaSeconds && empty($providedReason)) {
                            if ($request->wantsJson() || $request->is('api/*')) {
                                return response()->json(['success' => false, 'message' => __('Total tea time exceeded allowed minutes. Please provide a reason.')], 400);
                            }
                            return redirect()->back()->with('error', __('Total tea time exceeded allowed minutes. Please provide a reason.'));
                        }
                    }
                } else {
                    // flexible: check cumulative totals only
                    if ($currentType === 'lunch' && $totalLunchSeconds > $allowedLunchSeconds && empty($providedReason)) {
                        if ($request->wantsJson() || $request->is('api/*')) {
                            return response()->json(['success' => false, 'message' => __('Total lunch time exceeded allowed minutes. Please provide a reason.')], 400);
                        }
                        return redirect()->back()->with('error', __('Total lunch time exceeded allowed minutes. Please provide a reason.'));
                    }

                    if ($currentType === 'tea' && $totalTeaSeconds > $allowedTeaSeconds && empty($providedReason)) {
                        if ($request->wantsJson() || $request->is('api/*')) {
                            return response()->json(['success' => false, 'message' => __('Total tea time exceeded allowed minutes. Please provide a reason.')], 400);
                        }
                        return redirect()->back()->with('error', __('Total tea time exceeded allowed minutes. Please provide a reason.'));
                    }
                }

                $attendance->breaks = json_encode($breaks);
                $attendance->total_break = gmdate('H:i:s', $totalActualBreakSeconds);
                $attendance->total_lunch_time = gmdate('H:i:s', $totalLunchSeconds);
                $attendance->total_tea_time = gmdate('H:i:s', $totalTeaSeconds);
                $attendance->total_rest = gmdate('H:i:s', $totalOverBreakSeconds);
                $attendance->save();

                if ($request->wantsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => true,
                        'message' => __('Break ended successfully.'),
                    ], 200);
                }
                return redirect()->back()->with('success', __('Break ended successfully.'));
            }

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => __('No active clock-in found to start/end break.')], 400);
            }
            return redirect()->back()->with('error', __('No active clock-in found to start/end break.'));
        }

        // === Handle Clock In ===
        $expectedStartTime = $date . ' ' . $startTime;
        $actualClockInTime = $date . ' ' . $time;

        $totalLateSeconds = strtotime($actualClockInTime) - strtotime($expectedStartTime);
        $totalLateSeconds = max($totalLateSeconds, 0);

        $hours = abs(floor($totalLateSeconds / 3600));
        $mins  = abs(floor($totalLateSeconds / 60 % 60));
        $secs  = abs(floor($totalLateSeconds % 60));
        $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

        $checkDb = AttendanceEmployee::where('employee_id', '=', $employeeId)
            ->where('date', $date)
            ->where('clock_out', '00:00:00')
            ->first();

        if (empty($checkDb)) {
            // First clock-in for today
            $employeeAttendance                = new AttendanceEmployee();
            $employeeAttendance->employee_id   = $employeeId;
            $employeeAttendance->date          = $date;
            $employeeAttendance->status        = 'Present';
            $employeeAttendance->clock_in      = $time;
            $employeeAttendance->clock_out     = '00:00:00';
            $employeeAttendance->late          = $late;
            $employeeAttendance->early_leaving = '00:00:00';
            $employeeAttendance->overtime      = '00:00:00';
            $employeeAttendance->breaks        = json_encode([]);
            $employeeAttendance->total_break   = '00:00:00';
            $employeeAttendance->total_lunch_time = '00:00:00';
            $employeeAttendance->total_tea_time = '00:00:00';
            $employeeAttendance->total_rest    = '00:00:00';
            $employeeAttendance->created_by    = \Auth::user()->id;

            $employeeAttendance->save();

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => __('Employee Successfully Clock In.'),
                    'data' => $employeeAttendance,
                ], 201);
            }
            return redirect()->back()->with('success', __('Employee Successfully Clock In.'));
        }

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => __('Already clocked in today without clocking out.'),
            ], 409);
        }
        return redirect()->back()->with('error', __('Already clocked in today without clocking out.'));
    }

    protected function getAllowedBreakSeconds(Employee $employee, string $breakType)
    {
        $refreshSettings = Utility::settings();
        // Use employee's refresh_type for lunch/tea breaks, fallback to shift_break_type for compatibility
        $breakType_Mode = $employee->refresh_type ?: $employee->shift_break_type ?: 'fixed';

        if ($breakType_Mode === 'flexible') {
            if ($breakType === 'tea') {
                $teaMinutes = $employee->tea_minutes ?? $refreshSettings['tea_minutes'] ?? 15;
                return max(0, intval($teaMinutes)) * 60;
            }
            $lunchMinutes = $employee->lunch_minutes ?? $refreshSettings['lunch_minutes'] ?? 30;
            return max(0, intval($lunchMinutes)) * 60;
        }

        // Fixed mode - use employee's configured times
        if ($breakType === 'tea') {
            $teaStart = $employee->tea_start ?? $refreshSettings['tea_start'] ?? '15:30:00';
            $teaEnd = $employee->tea_end ?? $refreshSettings['tea_end'] ?? '16:00:00';
            return max(0, intval(strtotime($teaEnd) - strtotime($teaStart)));
        }

        $lunchStart = $employee->lunch_start ?? $refreshSettings['lunch_start'] ?? '12:00:00';
        $lunchEnd = $employee->lunch_end ?? $refreshSettings['lunch_end'] ?? '13:00:00';
        return max(0, intval(strtotime($lunchEnd) - strtotime($lunchStart)));
    }

    protected function isBreakStartAllowed(Employee $employee, $time, string $breakType)
    {
        $refreshSettings = Utility::settings();
        // Use employee's refresh_type for lunch/tea breaks, fallback to shift_break_type for compatibility
        $breakType_Mode = $employee->refresh_type ?: $employee->shift_break_type ?: 'fixed';

        if ($breakType_Mode !== 'fixed') {
            return true;
        }

        $startTime = $breakType === 'tea'
            ? ($employee->tea_start ?? $refreshSettings['tea_start'] ?? null)
            : ($employee->lunch_start ?? $refreshSettings['lunch_start'] ?? null);
        $endTime = $breakType === 'tea'
            ? ($employee->tea_end ?? $refreshSettings['tea_end'] ?? null)
            : ($employee->lunch_end ?? $refreshSettings['lunch_end'] ?? null);

        if (!$startTime || !$endTime) {
            return false;
        }

        $reference = '1970-01-01 ';
        $currentSeconds = strtotime($reference . $time);
        $startSeconds = strtotime($reference . $startTime);
        $endSeconds = strtotime($reference . $endTime);

        if ($endSeconds < $startSeconds) {
            $endSeconds += 86400;
            if ($currentSeconds < $startSeconds) {
                $currentSeconds += 86400;
            }
        }

        return $currentSeconds >= $startSeconds && $currentSeconds <= $endSeconds;
    }

    public function bulkAttendance(Request $request)
    {
        if (\Auth::user()->can('Create Attendance')) {

            $branch = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branch->prepend('Select Branch', '');

            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend('Select Department', '');

            $selfEmpId = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;

            $employees = [];
            if (!empty($request->branch) && !empty($request->department) && $request->department >= 0) {
                $employees = Employee::where('created_by', \Auth::user()->creatorId())
                    ->where('branch_id', $request->branch)
                    ->where('department_id', $request->department)
                    ->where('company_doj', '<=', $request->date)
                    ->when($selfEmpId, fn($q) => $q->where('id', '!=', $selfEmpId))
                    ->get();
            } elseif (!empty($request->branch) && empty($request->department) && $request->department == '0') {
                $employees = Employee::where('created_by', \Auth::user()->creatorId())
                    ->where('branch_id', $request->branch)
                    ->where('company_doj', '<=', $request->date)
                    ->when($selfEmpId, fn($q) => $q->where('id', '!=', $selfEmpId))
                    ->get();
            }
            return view('attendance.bulk', compact('employees', 'branch', 'department'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function bulkAttendanceData(Request $request)
    {
        if (\Auth::user()->can('Create Attendance')) {
            if (!empty($request->branch) && $request->has('department')) {

                $employees = $request->employee_id;
                $selfEmpId = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
                $atte      = [];
                foreach ($employees as $empId) {

                    // Prevent marking own attendance via bulk
                    if ($selfEmpId && $empId == $selfEmpId) {
                        continue;
                    }

                    $employee = Employee::find($empId);
                    if (!$employee) {
                        continue;
                    }

                    if ($employee->company_start_time && $employee->company_end_time) {
                        $startTime = $employee->company_start_time;
                        $endTime   = $employee->company_end_time;
                    } else {
                        $startTime  = Utility::getValByName('company_start_time');
                        $endTime    = Utility::getValByName('company_end_time');
                    }

                    $present = 'present-' . $empId;
                    $in      = 'in-' . $empId;
                    $out     = 'out-' . $empId;
                    $atte[]  = $present;
                    if ($request->input($present) == 'on') {

                        $in  = date("H:i:s", strtotime($request->input($in)));
                        $out = date("H:i:s", strtotime($request->input($out)));

                        $totalLateSeconds = strtotime($in) - strtotime($startTime);

                        $hours = floor($totalLateSeconds / 3600);
                        $mins  = floor($totalLateSeconds / 60 % 60);
                        $secs  = floor($totalLateSeconds % 60);
                        $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                        //early Leaving
                        $totalEarlyLeavingSeconds = strtotime($endTime) - strtotime($out);
                        $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                        $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                        $secs                     = floor($totalEarlyLeavingSeconds % 60);
                        $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);


                        if (strtotime($out) > strtotime($endTime)) {
                            //Overtime
                            $totalOvertimeSeconds = strtotime($out) - strtotime($endTime);
                            $hours                = floor($totalOvertimeSeconds / 3600);
                            $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                            $secs                 = floor($totalOvertimeSeconds % 60);
                            $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                        } else {
                            $overtime = '00:00:00';
                        }

                        $attendance = AttendanceEmployee::where('employee_id', '=', $empId)->where('date', '=', $request->date)->first();

                        if (!empty($attendance)) {
                            $employeeAttendance = $attendance;
                        } else {
                            $employeeAttendance              = new AttendanceEmployee();
                            $employeeAttendance->employee_id = $empId;
                            $employeeAttendance->created_by  = \Auth::user()->creatorId();
                        }

                        $employeeAttendance->date          = Carbon::parse($request->date)->format('Y-m-d');
                        $employeeAttendance->status        = 'Present';
                        $employeeAttendance->clock_in      = $in;
                        $employeeAttendance->clock_out     = $out;
                        $employeeAttendance->late          = $late;
                        $employeeAttendance->early_leaving = ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00';
                        $employeeAttendance->overtime      = $overtime;
                        $employeeAttendance->total_rest    = '00:00:00';
                        $employeeAttendance->save();
                    } else {
                        $attendance = AttendanceEmployee::where('employee_id', '=', $empId)->where('date', '=', $request->date)->first();

                        if (!empty($attendance)) {
                            $employeeAttendance = $attendance;
                        } else {
                            $employeeAttendance              = new AttendanceEmployee();
                            $employeeAttendance->employee_id = $empId;
                            $employeeAttendance->created_by  = \Auth::user()->creatorId();
                        }

                        $employeeAttendance->status        = 'Leave';
                        $employeeAttendance->date          = $request->date;
                        $employeeAttendance->clock_in      = '00:00:00';
                        $employeeAttendance->clock_out     = '00:00:00';
                        $employeeAttendance->late          = '00:00:00';
                        $employeeAttendance->early_leaving = '00:00:00';
                        $employeeAttendance->overtime      = '00:00:00';
                        $employeeAttendance->total_rest    = '00:00:00';
                        $employeeAttendance->save();
                    }
                }

                return redirect()->back()->with('success', __('Employee attendance successfully created.'));
            } else {
                return redirect()->back()->with('error', __('Branch & department field required.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function importFile()
    {
        return view('attendance.import');
    }

    // public function import(Request $request)
    // {
    //     $rules = [
    //         'file' => 'required|mimes:csv,txt,xlsx',
    //     ];
    //     $validator = \Validator::make($request->all(), $rules);

    //     if ($validator->fails()) {
    //         $messages = $validator->getMessageBag();

    //         return redirect()->back()->with('error', $messages->first());
    //     }

    //     $attendance = (new AttendanceImport())->toArray(request()->file('file'))[0];

    //     $email_data = [];
    //     foreach ($attendance as $key => $employee) {
    //         if ($key != 0) {
    //             echo "<pre>";
    //             if ($employee != null && Employee::where('email', $employee[0])->where('created_by', \Auth::user()->creatorId())->exists()) {
    //                 $email = $employee[0];
    //             } else {
    //                 $email_data[] = $employee[0];
    //             }
    //         }
    //     }
    //     $totalattendance = count($attendance) - 1;
    //     $errorArray    = [];

    //     $startTime = Utility::getValByName('company_start_time');
    //     $endTime   = Utility::getValByName('company_end_time');

    //     if (!empty($attendanceData)) {
    //         $errorArray[] = $attendanceData;
    //     } else {
    //         foreach ($attendance as $key => $value) {
    //             if ($key != 0) {
    //                 $employeeData = Employee::where('email', $value[0])->where('created_by', \Auth::user()->creatorId())->first();
    //                 // $employeeId = 0;
    //                 if (!empty($employeeData)) {
    //                     $employeeId = $employeeData->id;


    //                     $clockIn = $value[2];
    //                     $clockOut = $value[3];

    //                     if ($clockIn) {
    //                         $status = "present";
    //                     } else {
    //                         $status = "leave";
    //                     }

    //                     $totalLateSeconds = strtotime($clockIn) - strtotime($startTime);

    //                     $hours = floor($totalLateSeconds / 3600);
    //                     $mins  = floor($totalLateSeconds / 60 % 60);
    //                     $secs  = floor($totalLateSeconds % 60);
    //                     $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

    //                     $totalEarlyLeavingSeconds = strtotime($endTime) - strtotime($clockOut);
    //                     $hours                    = floor($totalEarlyLeavingSeconds / 3600);
    //                     $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
    //                     $secs                     = floor($totalEarlyLeavingSeconds % 60);
    //                     $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

    //                     if (strtotime($clockOut) > strtotime($endTime)) {
    //                         //Overtime
    //                         $totalOvertimeSeconds = strtotime($clockOut) - strtotime($endTime);
    //                         $hours                = floor($totalOvertimeSeconds / 3600);
    //                         $mins                 = floor($totalOvertimeSeconds / 60 % 60);
    //                         $secs                 = floor($totalOvertimeSeconds % 60);
    //                         $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
    //                     } else {
    //                         $overtime = '00:00:00';
    //                     }

    //                     $check = AttendanceEmployee::where('employee_id', $employeeId)->where('date', $value[1])->first();
    //                     if ($check) {
    //                         $check->update([
    //                             'late' => $late,
    //                             'early_leaving' => ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00',
    //                             'overtime' => $overtime,
    //                             'clock_in' => $value[2],
    //                             'clock_out' => $value[3]
    //                         ]);
    //                     } else {
    //                         $time_sheet = AttendanceEmployee::create([
    //                             'employee_id' => $employeeId,
    //                             'date' => $value[1],
    //                             'status' => $status,
    //                             'late' => $late,
    //                             'early_leaving' => ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00',
    //                             'overtime' => $overtime,
    //                             'clock_in' => $value[2],
    //                             'clock_out' => $value[3],
    //                             'created_by' => \Auth::user()->id,
    //                         ]);
    //                     }
    //                 }
    //             } else {
    //                 $email_data = implode(' And ', $email_data);
    //             }
    //         }
    //         if (!empty($email_data)) {
    //             return redirect()->back()->with('status', 'this record is not import. ' . '</br>' . $email_data);
    //         } else {
    //             if (empty($errorArray)) {
    //                 $data['status'] = 'success';
    //                 $data['msg']    = __('Record successfully imported');
    //             } else {

    //                 $data['status'] = 'error';
    //                 $data['msg']    = count($errorArray) . ' ' . __('Record imported fail out of' . ' ' . $totalattendance . ' ' . 'record');


    //                 foreach ($errorArray as $errorData) {
    //                     $errorRecord[] = implode(',', $errorData->toArray());
    //                 }

    //                 \Session::put('errorArray', $errorRecord);
    //             }

    //             return redirect()->back()->with($data['status'], $data['msg']);
    //         }
    //     }
    // }

    public function attendanceImportdata(Request $request)
    {
        session_start();
        $html = '<h3 class="text-danger text-center">Below data is not inserted</h3></br>';
        $flag = 0;
        $html .= '<table class="table table-bordered"><tr>';
        try {
            $request = $request->data;
            $file_data = $_SESSION['file_data'];

            unset($_SESSION['file_data']);
        } catch (\Throwable $th) {
            $html = '<h3 class="text-danger text-center">Something went wrong, Please try again</h3></br>';
            return response()->json([
                'html' => true,
                'response' => $html,
            ]);
        }
        $user = Auth::user();

        foreach ($file_data as $key => $row) {
            $employeeData = Employee::Where('email', 'like', $row[$request['employee_email']])->where('created_by', \Auth::user()->creatorId())->first();

            if ($employeeData && $employeeData->company_start_time && $employeeData->company_end_time) {
                $startTime = $employeeData->company_start_time;
                $endTime   = $employeeData->company_end_time;
            } else {
                $startTime  = Utility::getValByName('company_start_time');
                $endTime    = Utility::getValByName('company_end_time');
            }

            if (!empty($employeeData)) {
                try {

                    $employeeId = $employeeData->id;

                    $clockIn = $row[$request['clock_in']];
                    $clockOut = $row[$request['clock_out']];

                    if ($clockIn) {
                        $status = "Present";
                    } else {
                        $status = "Leave";
                    }

                    $totalLateSeconds = strtotime($clockIn) - strtotime($startTime);

                    $hours = floor($totalLateSeconds / 3600);
                    $mins = floor($totalLateSeconds / 60 % 60);
                    $secs = floor($totalLateSeconds % 60);
                    $late = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                    $totalEarlyLeavingSeconds = strtotime($endTime) - strtotime($clockOut);
                    $hours = floor($totalEarlyLeavingSeconds / 3600);
                    $mins = floor($totalEarlyLeavingSeconds / 60 % 60);
                    $secs = floor($totalEarlyLeavingSeconds % 60);
                    $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                    if (strtotime($clockOut) > strtotime($endTime)) {
                        //Overtime
                        $totalOvertimeSeconds = strtotime($clockOut) - strtotime($endTime);
                        $hours = floor($totalOvertimeSeconds / 3600);
                        $mins = floor($totalOvertimeSeconds / 60 % 60);
                        $secs = floor($totalOvertimeSeconds % 60);
                        $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                    } else {
                        $overtime = '00:00:00';
                    }

                    $check = AttendanceEmployee::where('employee_id', $employeeId)->where('date', $row[$request['date']])->first();

                    if ($check) {
                        $check->update([
                            'late' => $late,
                            'early_leaving' => ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00',
                            'overtime' => $overtime,
                            'clock_in' => $row[$request['clock_in']],
                            'clock_out' => $row[$request['clock_out']],
                        ]);
                    } else {
                        $time_sheet = AttendanceEmployee::create([
                            'employee_id' => $employeeId,
                            'date' => $row[$request['date']],
                            'status' => $status,
                            'late' => $late,
                            'early_leaving' => ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00',
                            'overtime' => $overtime,
                            'clock_in' => $row[$request['clock_in']],
                            'clock_out' => $row[$request['clock_out']],
                            'created_by' => \Auth::user()->id,
                        ]);
                    }
                } catch (\Exception $e) {
                    $flag = 1;
                    $html .= '<tr>';

                    $html .= '<td>' . (isset($row[$request['employee_email']]) ? $row[$request['employee_email']] : '-') . '</td>';
                    $html .= '<td>' . (isset($row[$request['date']]) ? $row[$request['date']] : '-') . '</td>';
                    $html .= '<td>' . (isset($row[$request['clock_in']]) ? $row[$request['clock_in']] : '-') . '</td>';
                    $html .= '<td>' . (isset($row[$request['clock_out']]) ? $row[$request['clock_out']] : '-') . '</td>';

                    $html .= '</tr>';
                }
            } else {
                $flag = 1;
                $html .= '<tr>';

                $html .= '<td>' . (isset($row[$request['employee_email']]) ? $row[$request['employee_email']] : '-') . '</td>';
                $html .= '<td>' . (isset($row[$request['date']]) ? $row[$request['date']] : '-') . '</td>';
                $html .= '<td>' . (isset($row[$request['clock_in']]) ? $row[$request['clock_in']] : '-') . '</td>';
                $html .= '<td>' . (isset($row[$request['clock_out']]) ? $row[$request['clock_out']] : '-') . '</td>';

                $html .= '</tr>';
            }
        }

        $html .= '
                        </table>
                        <br />
                        ';
        if ($flag == 1) {

            return response()->json([
                'html' => true,
                'response' => $html,
            ]);
        } else {
            return response()->json([
                'html' => false,
                'response' => 'Data Imported Successfully',
            ]);
        }
    }


    // Add this method to your API Controller (e.g., AttendanceController or HRMSController)

    /**
     * Handle employee break (start/end)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleBreak(Request $request)
    {
        try {
            // Get authenticated user from token
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => __('Unauthorized access.'),
                ], 401);
            }

            $employeeId = !empty($user->employee) ? $user->employee->id : 0;

            if (!$employeeId) {
                return response()->json([
                    'success' => false,
                    'message' => __('Employee not found.'),
                ], 404);
            }

            $settings = Utility::settings();
            if (!empty($settings['ip_restrict']) && $settings['ip_restrict'] == 'on') {
                $userIp = $request->input('client_ip') ?? $request->header('X-Forwarded-For') ?? $request->header('X-Real-IP') ?? $request->ip();
                if (str_contains($userIp, ',')) {
                    $userIp = trim(explode(',', $userIp)[0]);
                }
                $ip = IpRestrict::where('created_by', $user->creatorId())->where('ip', $userIp)->first();
                if (empty($ip)) {
                    return response()->json([
                        'success' => false,
                        'message' => __('This IP address (:ip) is not allowed to clock in & clock out.', ['ip' => $userIp]),
                    ], 403);
                }
            }

            $date = date("Y-m-d");
            $time = date("H:i:s");

            // Find today's active attendance (where clock_out is 00:00:00)
            $attendance = AttendanceEmployee::where('employee_id', $employeeId)
                ->where('date', $date)
                ->where('clock_out', '00:00:00')
                ->latest()
                ->first();

            if (!$attendance) {
                $attendance = AttendanceRequest::where('employee_id', $employeeId)
                    ->whereDate('requested_at', $date)
                    ->latest()
                    ->first();
                if (!$attendance) {
                    return response()->json([
                        'success' => false,
                        'message' => __('No active clock-in found. Please clock in first.'),
                        'break_status' => null,
                    ], 400);
                }
            }

            // Decode existing breaks
            $breaks = $attendance->breaks ? json_decode($attendance->breaks, true) : [];
            $breakType = strtolower($request->input('break_type', 'lunch'));
            $validBreakTypes = ['lunch', 'tea'];

            if (!in_array($breakType, $validBreakTypes)) {
                return response()->json([
                    'success' => false,
                    'message' => __('Invalid break type.'),
                ], 400);
            }

            // Check if we need to start or end break
            $lastBreak = !empty($breaks) ? end($breaks) : null;
            $isBreakActive = $lastBreak && ($lastBreak['end'] ?? null) === null;

            if (!$isBreakActive) {
                if (!$this->isBreakStartAllowed($user->employee, $time, $breakType)) {
                    return response()->json([
                        'success' => false,
                        'message' => __(':type break can only be started within the configured time window.', ['type' => ucfirst($breakType)]),
                    ], 400);
                }

                // Start a new break
                $breaks[] = [
                    'type' => $breakType,
                    'start' => $time,
                    'end'   => null,
                    'duration' => '00:00:00',
                    'counted_duration' => '00:00:00',
                    'over_break' => '00:00:00',
                    'reason' => null,
                ];
                $attendance->breaks = json_encode($breaks);
                $attendance->save();

                return response()->json([
                    'success' => true,
                    'message' => __('Break started successfully.'),
                    'break_status' => 'active', // Break is now active
                    'break_start_time' => $time,
                    'data' => [
                        'attendance_id' => $attendance->id,
                        'breaks' => $breaks,
                        'total_break' => $attendance->total_break,
                    ],
                ], 200);
            } else {
                // End the current break
                $lastIndex = count($breaks) - 1;
                $currentBreak = $breaks[$lastIndex];
                $currentType = strtolower($currentBreak['type'] ?? 'lunch');
                $breaks[$lastIndex]['end'] = $time;

                $startDateTime = strtotime($date . ' ' . $currentBreak['start']);
                $endDateTime = strtotime($date . ' ' . $time);
                $actualSeconds = max(0, $endDateTime - $startDateTime);

                $allowedBreakSeconds = $this->getAllowedBreakSeconds($user->employee, $currentType);
                if ($actualSeconds > $allowedBreakSeconds && empty(trim($request->input('break_reason', '')))) {
                    return response()->json([
                        'success' => false,
                        'message' => __(ucfirst($currentType) . ' break exceeded allowed time. Please provide a reason.'),
                    ], 400);
                }

                $countedSeconds = min($actualSeconds, $allowedBreakSeconds);
                $overflowSeconds = max(0, $actualSeconds - $allowedBreakSeconds);

                $breaks[$lastIndex]['duration'] = gmdate('H:i:s', $actualSeconds);
                $breaks[$lastIndex]['counted_duration'] = gmdate('H:i:s', $countedSeconds);
                $breaks[$lastIndex]['over_break'] = gmdate('H:i:s', $overflowSeconds);
                $breaks[$lastIndex]['reason'] = $request->input('break_reason') ? trim($request->input('break_reason')) : ($breaks[$lastIndex]['reason'] ?? null);

                $totalLunchSeconds = 0;
                $totalTeaSeconds = 0;
                $totalActualBreakSeconds = 0;
                $totalOverBreakSeconds = 0;

                foreach ($breaks as $b) {
                    if (!empty($b['start']) && !empty($b['end'])) {
                        $breakSeconds = strtotime($date . ' ' . $b['end']) - strtotime($date . ' ' . $b['start']);
                        $totalActualBreakSeconds += $breakSeconds;

                        $breakType = strtolower($b['type'] ?? 'lunch');
                        if ($breakType === 'lunch') {
                            $totalLunchSeconds += $breakSeconds;
                        } elseif ($breakType === 'tea') {
                            $totalTeaSeconds += $breakSeconds;
                        }
                    }
                }

                $allowedLunchSeconds = $this->getAllowedBreakSeconds($user->employee, 'lunch');
                $allowedTeaSeconds = $this->getAllowedBreakSeconds($user->employee, 'tea');
                $lunchOverbreakSeconds = max(0, $totalLunchSeconds - $allowedLunchSeconds);
                $teaOverbreakSeconds = max(0, $totalTeaSeconds - $allowedTeaSeconds);
                $totalOverBreakSeconds += $lunchOverbreakSeconds + $teaOverbreakSeconds;

                // Additional enforcement: require reason when cumulative break time exceeds allowed
                // or (in fixed mode) when break end time goes beyond configured end window.
                $refreshSettings = Utility::settings();
                $breakMode = $user->employee->refresh_type ?: $user->employee->shift_break_type ?: 'fixed';

                $providedReason = trim($request->input('break_reason', ''));

                if ($breakMode === 'fixed') {
                    if ($currentType === 'lunch') {
                        $lunchEndTime = $user->employee->lunch_end ?? $refreshSettings['lunch_end'] ?? '13:00:00';
                        $lunchEndDateTime = strtotime($date . ' ' . $lunchEndTime);

                        if ($endDateTime > $lunchEndDateTime && empty($providedReason)) {
                            return response()->json([
                                'success' => false,
                                'message' => __('Lunch break went past configured end time. Please provide a reason.'),
                            ], 400);
                        }

                        if ($totalLunchSeconds > $allowedLunchSeconds && empty($providedReason)) {
                            return response()->json([
                                'success' => false,
                                'message' => __('Total lunch time exceeded allowed minutes. Please provide a reason.'),
                            ], 400);
                        }
                    } else {
                        $teaEndTime = $user->employee->tea_end ?? $refreshSettings['tea_end'] ?? '16:00:00';
                        $teaEndDateTime = strtotime($date . ' ' . $teaEndTime);

                        if ($endDateTime > $teaEndDateTime && empty($providedReason)) {
                            return response()->json([
                                'success' => false,
                                'message' => __('Tea break went past configured end time. Please provide a reason.'),
                            ], 400);
                        }

                        if ($totalTeaSeconds > $allowedTeaSeconds && empty($providedReason)) {
                            return response()->json([
                                'success' => false,
                                'message' => __('Total tea time exceeded allowed minutes. Please provide a reason.'),
                            ], 400);
                        }
                    }
                } else {
                    // flexible: check cumulative totals only
                    if ($currentType === 'lunch' && $totalLunchSeconds > $allowedLunchSeconds && empty($providedReason)) {
                        return response()->json([
                            'success' => false,
                            'message' => __('Total lunch time exceeded allowed minutes. Please provide a reason.'),
                        ], 400);
                    }

                    if ($currentType === 'tea' && $totalTeaSeconds > $allowedTeaSeconds && empty($providedReason)) {
                        return response()->json([
                            'success' => false,
                            'message' => __('Total tea time exceeded allowed minutes. Please provide a reason.'),
                        ], 400);
                    }
                }

                $attendance->breaks = json_encode($breaks);
                $attendance->total_break = gmdate('H:i:s', $totalActualBreakSeconds);
                $attendance->total_lunch_time = gmdate('H:i:s', $totalLunchSeconds);
                $attendance->total_tea_time = gmdate('H:i:s', $totalTeaSeconds);
                $attendance->total_rest = gmdate('H:i:s', $totalOverBreakSeconds);
                $attendance->save();

                $workingSeconds = 0;
                if (!empty($attendance->clock_in)) {
                    $clockInDate = strtotime($date . ' ' . $attendance->clock_in);
                    $nowDate = strtotime($date . ' ' . $time);
                    if ($nowDate < $clockInDate) {
                        $nowDate = $clockInDate;
                    }
                    $workingSeconds = max(0, $nowDate - $clockInDate - $totalActualBreakSeconds);
                }
                $hours = floor($workingSeconds / 3600);
                $mins = floor(($workingSeconds % 3600) / 60);
                $secs = $workingSeconds % 60;
                $workingHours = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                return response()->json([
                    'success' => true,
                    'message' => __('Break ended successfully.'),
                    'break_status' => 'ended', // Break has ended
                    'break_end_time' => $time,
                    'total_break_duration' => gmdate('H:i:s', $totalActualBreakSeconds),
                    'working_hours' => $workingHours,
                    'data' => [
                        'attendance_id' => $attendance->id,
                        'breaks' => $breaks,
                        'total_break' => $attendance->total_break,
                    ],
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('An error occurred while processing break.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current break status for employee
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBreakStatus()
    {
        try {
            if (request()->get('employee_id') && !empty(request()->get('employee_id'))) {
                $employeeId = request()->get('employee_id');
                $user = User::find($employeeId);
            } else {
                $user = Auth::user();
            }

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => __('Unauthorized access.'),
                ], 401);
            }

            $employeeId = !empty($user->employee) ? $user->employee->id : 0;

            if (!$employeeId) {
                return response()->json([
                    'success' => false,
                    'message' => __('Employee not found.'),
                ], 404);
            }

            $date = date("Y-m-d");

            // Find today's active attendance
            $attendance = AttendanceEmployee::where('employee_id', $employeeId)
                ->where('date', $date)
                ->where('clock_out', '00:00:00')
                ->latest()
                ->first();

            if (!$attendance) {
                $attendance = AttendanceRequest::where('employee_id', $employeeId)
                    ->whereDate('requested_at', $date)
                    ->latest()
                    ->first();
                if (!$attendance) {
                    return response()->json([
                        'success' => true,
                        'is_clocked_in' => false,
                        'break_status' => null,
                        'message' => __('Not clocked in today.'),
                    ], 200);
                }
            }

            // Check break status
            $breaks = $attendance->breaks ? json_decode($attendance->breaks, true) : [];
            $lastBreak = !empty($breaks) ? end($breaks) : null;
            $isBreakActive = $lastBreak && ($lastBreak['end'] ?? null) === null;

            return response()->json([
                'success' => true,
                'is_clocked_in' => true,
                'break_status' => $isBreakActive ? 'active' : 'ended',
                'message' => $isBreakActive ? __('Break is active.') : __('No active break.'),
                'data' => [
                    'attendance_id' => $attendance->id,
                    'clock_in' => $attendance->clock_in,
                    'breaks' => $breaks,
                    'total_break' => $attendance->total_break,
                    'current_break_start' => $isBreakActive ? $lastBreak['start'] : null,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('An error occurred while fetching break status.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

   public function passcodeVerify(Request $request)
    {
        try {
            $passcode = trim((string)$request->passcode);
            $user = Auth::user();
            $companyId = ($user->type == 'company') ? $user->id : $user->creatorId();

            $companyPasscodes = \App\Models\User::where(function($q) use ($companyId) {
                $q->where('id', $companyId)
                  ->orWhere('created_by', $companyId);
            })
            ->whereNotNull('passcode')
            ->where('passcode', '!=', '')
            ->pluck('passcode')
            ->toArray();

            $validPasscodes = array_values(array_unique(array_map('strval', array_filter($companyPasscodes))));

            if (in_array($passcode, $validPasscodes)) {
                return response()->json([
                    'success' => true,
                    'message' => "passcode verify succesfully"
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "passcode verification failed"
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('An error occurred while fetching break status.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function generatePasscode()
    {
        try {
            $users = User::all();

            foreach ($users as $user) {
                $passcode = random_int(000000, 999999);
                User::where('id', $user->id)->update([
                    'passcode' => $passcode,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('An error occurred while fetching break status.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
