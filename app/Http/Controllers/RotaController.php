<?php

namespace App\Http\Controllers;

use App\Services\Idab\IdabApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\Leave;
use App\Models\AttendanceEmployee;
use App\Models\Branch;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class RotaController extends Controller
{
    private IdabApiService $idab;

    public function __construct(IdabApiService $idab)
    {
        $this->idab = $idab;
    }

    // ── Page ─────────────────────────────────────────────────────────────────

    public function index()
    {
        if (!\Auth::user()->can('Manage Rota')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $branches = Branch::where('created_by', Auth::user()->creatorId())->get();
        return view('rota.rotas', compact('branches'));
    }

    // ── GET /rota/staff ───────────────────────────────────────────────────────

    public function apiStaff(Request $request)
    {
        if (!\Auth::user()->can('Manage Rota')) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        $user = Auth::user();

        // Employees can only see themselves in the rota
        if ($user->type === 'employee') {
            $employee = Employee::with(['branch', 'department', 'designation'])
                ->where('user_id', $user->id)
                ->first();

            if (!$employee) {
                return response()->json(['success' => true, 'data' => [], 'meta' => ['total' => 0, 'per_page' => 1, 'current_page' => 1, 'last_page' => 1]]);
            }

            $data = collect([[
                'id'          => $employee->user_id,
                'employee_id' => $employee->id,
                'name'        => trim($employee->name . ' ' . $employee->last_name),
                'role'        => optional($employee->designation)->name
                              ?? optional($employee->department)->name
                              ?? 'Employee',
                'branch_id'   => $employee->branch_id,
                'branch_name' => optional($employee->branch)->name ?? '',
            ]]);

            return response()->json([
                'success' => true,
                'data'    => $data,
                'meta'    => ['total' => 1, 'per_page' => 1, 'current_page' => 1, 'last_page' => 1],
            ]);
        }

        $query = Employee::with(['branch', 'department', 'designation'])
            ->where('employees.created_by', $user->creatorId())
            ->where('employees.is_active', 1);

        if ($request->filled('branch_id') && $request->branch_id !== 'all') {
            $query->where('employees.branch_id', $request->branch_id);
        }

        $employees = $query->paginate((int) $request->get('per_page', 100));

        $data = $employees->map(fn($emp) => [
            'id'          => $emp->user_id,
            'employee_id' => $emp->id,
            'name'        => trim($emp->name . ' ' . $emp->last_name),
            'role'        => optional($emp->designation)->name
                          ?? optional($emp->department)->name
                          ?? 'Employee',
            'branch_id'   => $emp->branch_id,
            'branch_name' => optional($emp->branch)->name ?? '',
        ]);

        return response()->json([
            'success' => true,
            'data'    => $data,
            'meta'    => [
                'total'        => $employees->total(),
                'per_page'     => $employees->perPage(),
                'current_page' => $employees->currentPage(),
                'last_page'    => $employees->lastPage(),
            ],
        ]);
    }

    // ── GET /rota/shifts ──────────────────────────────────────────────────────
    // Priority per employee per day:
    //   1. Date override (is_deleted=false)  → show override shift
    //   2. Date override (is_deleted=true)   → show nothing (deleted for this day)
    //   3. Employee default (date=null)      → show default shift
    //   4. Nothing                           → skip

    public function apiShifts(Request $request)
    {
        if (!\Auth::user()->can('Manage Rota')) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $user      = Auth::user();
        $creatorId = $user->creatorId();
        $startDate = $request->start_date;
        $endDate   = $request->end_date;

        $settings          = \App\Models\Utility::settings();
        $companyShiftsJson = $settings['company_shifts'] ?? '';
        $companyShiftsArr  = !empty($companyShiftsJson) ? (json_decode($companyShiftsJson, true) ?? []) : [];
        $hiddenFromRota    = collect($companyShiftsArr)
            ->filter(fn($s) => ($s['show_in_rota'] ?? 1) == 0)
            ->map(fn($s) => ($s['start'] ?? '') . '|' . ($s['end'] ?? ''))
            ->flip()   // use as a hash-set for O(1) lookup
            ->all();

        // Working days: comma-separated day numbers (0=Sun … 6=Sat). Default Mon–Fri.
        $workingDaysRaw  = $settings['rota_working_days'] ?? '1,2,3,4,5';
        $workingDays     = array_map('intval', array_filter(array_map('trim', explode(',', $workingDaysRaw)), 'strlen'));
        $holidays = \App\Models\Holiday::where('created_by', $creatorId)
            ->where('start_date', '<=', $endDate)
            ->where('end_date',   '>=', $startDate)
            ->get();

        $holidayDates = [];
        foreach ($holidays as $h) {
            $hPeriod = \Carbon\CarbonPeriod::create($h->start_date, $h->end_date);
            foreach ($hPeriod as $hDay) {
                $holidayDates[$hDay->format('Y-m-d')] = true;
            }
        }

        // Scope employees — eager-load the company-assigned shift (employees.shift_id).
        $empQuery = Employee::with('shift')->where('created_by', $creatorId)->where('is_active', 1);

        if ($user->type === 'employee') {
            $empQuery->where('user_id', $user->id);
        } else {
            if ($request->filled('branch_id') && $request->branch_id !== 'all') {
                $empQuery->where('branch_id', $request->branch_id);
            }
            if ($request->filled('user_id')) {
                $empQuery->where('user_id', $request->user_id);
            }
        }

        $employees   = $empQuery->get();
        $employeeIds = $employees->pluck('id')->toArray();

        // All date-specific overrides (active + deleted markers) for the period
        $rotaEntries = Shift::rotaEntries()
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('created_by', $creatorId)
            ->get();

        // Employee-level default shifts (date = null)
        $defaultShifts = Shift::employeeDefaults()
            ->whereIn('employee_id', $employeeIds)
            ->where('created_by', $creatorId)
            ->get();

        // Approved leaves
        $leaves = Leave::whereIn('employee_id', $employeeIds)
            ->where('status', 'approved')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(fn($q2) => $q2
                      ->where('start_date', '<=', $startDate)
                      ->where('end_date', '>=', $endDate));
            })
            ->get();

        // Attendance
        $attendances = AttendanceEmployee::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        // Build daily summary
        $period       = CarbonPeriod::create($startDate, $endDate);
        $dailySummary = [];

        foreach ($period as $day) {
            $dateStr   = $day->format('Y-m-d');
            $dayShifts = [];

            foreach ($employees as $emp) {
                // Check for a date-specific override (active or deleted marker)
                $override = $rotaEntries->first(fn($r) =>
                    $r->employee_id === $emp->id &&
                    $r->date->format('Y-m-d') === $dateStr
                );

                // Leave check (needed for both shift and leave-only entries)
                $onLeave = $leaves->first(fn($l) =>
                    $l->employee_id === $emp->id &&
                    $dateStr >= $l->start_date &&
                    $dateStr <= $l->end_date
                );

                // Attendance check
                $attendance = $attendances->first(fn($a) =>
                    $a->employee_id === $emp->id && $a->date === $dateStr
                );

                // If there's an approved leave on this day, always show it —
                // even if no shift is assigned.
                if ($onLeave && !$override) {
                    // Show leave entry without a shift backing it
                    $dayShifts[] = [
                        'id'         => null,
                        'shiftId'    => null,
                        'userId'     => 'user_' . $emp->user_id,
                        'employeeId' => $emp->id,
                        'type'       => 'leave',
                        'startTime'  => null,
                        'endTime'    => null,
                        'shiftName'  => optional($onLeave->leaveType)->title ?? 'Leave',
                        'notes'      => $onLeave->leave_reason,
                        'isDefault'  => false,
                        'isLeaveOnly' => true,
                        'leave'      => [
                            'id'         => $onLeave->id,
                            'type'       => optional($onLeave->leaveType)->title ?? 'Leave',
                            'start_date' => $onLeave->start_date,
                            'end_date'   => $onLeave->end_date,
                            'reason'     => $onLeave->leave_reason,
                            'days'       => $onLeave->total_leave_days,
                        ],
                        'attendance' => null,
                    ];
                    continue;
                }

                // Deleted marker → suppress default, show nothing (unless on leave)
                if ($override && $override->is_deleted) {
                    // Still show leave even if shift was deleted for this day
                    if ($onLeave) {
                        $dayShifts[] = [
                            'id'         => null,
                            'shiftId'    => null,
                            'userId'     => 'user_' . $emp->user_id,
                            'employeeId' => $emp->id,
                            'type'       => 'leave',
                            'startTime'  => null,
                            'endTime'    => null,
                            'shiftName'  => optional($onLeave->leaveType)->title ?? 'Leave',
                            'notes'      => $onLeave->leave_reason,
                            'isDefault'  => false,
                            'isLeaveOnly' => true,
                            'leave'      => [
                                'id'         => $onLeave->id,
                                'type'       => optional($onLeave->leaveType)->title ?? 'Leave',
                                'start_date' => $onLeave->start_date,
                                'end_date'   => $onLeave->end_date,
                                'reason'     => $onLeave->leave_reason,
                                'days'       => $onLeave->total_leave_days,
                            ],
                            'attendance' => null,
                        ];
                    }
                    continue;
                }

                // Determine which shift record to display
                $isDefault = false;
                if ($override) {
                    $shift = $override;
                } else {
                    // 1st fallback: employee-level default (shifts table, date=null, employee_id set)
                    $shift = $defaultShifts->first(fn($d) => $d->employee_id === $emp->id);

                    // 2nd fallback: company-assigned shift from employees.shift_id
                    if (!$shift) {
                        $shift = $emp->shift;
                    }

                    if (!$shift) continue;

                    if ($shift && $shift->id === optional($emp->shift)->id) {
                        $shiftKey = ($shift->company_start_time ?? '') . '|' . ($shift->company_end_time ?? '');
                        if (array_key_exists($shiftKey, $hiddenFromRota)) {
                            continue;
                        }
                    }

                    $isDefault = true;
                    if (!empty($workingDays) && !in_array((int) $day->dayOfWeek, $workingDays)) {
                        continue;
                    }
                    if (!empty($holidayDates) && isset($holidayDates[$dateStr])) {
                        continue;
                    }
                }

                // Resolve type — leave overrides shift type
                $type = $shift->type ?? 'regular';
                if ($onLeave) {
                    $type = 'leave';
                }

                $hasExtraTime = ($attendance && !empty($attendance->overtime) && $attendance->overtime !== '00:00:00');

                $dayShifts[] = [
                    'id'          => $shift->id,
                    'shiftId'     => $shift->id,
                    'userId'      => 'user_' . $emp->user_id,
                    'employeeId'  => $emp->id,
                    'type'        => $type,
                    'startTime'   => $shift->company_start_time,
                    'endTime'     => $shift->company_end_time,
                    'shiftName'   => $shift->name ?? '',
                    'notes'       => $shift->notes,
                    'isDefault'   => $isDefault,
                    'isLeaveOnly' => false,
                    'leave'       => $onLeave ? [
                        'id'         => $onLeave->id,
                        'type'       => optional($onLeave->leaveType)->title ?? 'Leave',
                        'start_date' => $onLeave->start_date,
                        'end_date'   => $onLeave->end_date,
                        'reason'     => $onLeave->leave_reason,
                        'days'       => $onLeave->total_leave_days,
                    ] : null,
                    'attendance'  => $attendance ? [
                        'clock_in'         => $attendance->clock_in,
                        'clock_out'        => $attendance->clock_out,
                        'total_break'      => $attendance->total_break,
                        'total_lunch_time' => $attendance->total_lunch_time,
                        'total_tea_time'   => $attendance->total_tea_time,
                        'status'           => $attendance->status,
                        'overtime'         => (!empty($attendance->overtime) && $attendance->overtime !== '00:00:00') ? $attendance->overtime : null,
                        'extra_time'       => $hasExtraTime ? $attendance->overtime : null,
                    ] : null,
                ];
            }

            if (!empty($dayShifts)) {
                $dailySummary[] = ['date' => $dateStr, 'shifts' => $dayShifts];
            }
        }

        return response()->json([
            'success' => true,
            'data'    => ['dailySummary' => $dailySummary],
        ]);
    }

    // ── GET /rota/shift-templates ─────────────────────────────────────────────

    public function apiShiftTemplates()
    {
        if (!\Auth::user()->can('Manage Rota')) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        $templates = [];

        // 1. Load company shifts configured in Company Settings
        $settings = \App\Models\Utility::settings();
        $companyShiftsJson = $settings['company_shifts'] ?? '';
        $companyShiftsArr = !empty($companyShiftsJson) ? (json_decode($companyShiftsJson, true) ?? []) : [];

        if (is_array($companyShiftsArr)) {
            foreach ($companyShiftsArr as $idx => $cShift) {
                if (($cShift['show_in_rota'] ?? 1) == 1) {
                    $start = !empty($cShift['start']) ? $cShift['start'] : '09:00';
                    $end   = !empty($cShift['end']) ? $cShift['end'] : '17:00';

                    if (strlen($start) == 5) $start .= ':00';
                    if (strlen($end) == 5)   $end .= ':00';

                    $rawName = !empty($cShift['name']) ? $cShift['name'] : __('Shift') . ' ' . ($idx + 1);

                    $colorDot = '🟣 ';
                    if (!empty($cShift['color'])) {
                        $c = strtolower(ltrim($cShift['color'], '#'));
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

                    $name = $colorDot . $rawName;

                    $templates[] = [
                        'id'                 => 'company_shift_' . $idx,
                        'name'               => $name,
                        'raw_name'           => $rawName,
                        'company_start_time' => $start,
                        'company_end_time'   => $end,
                    ];
                }
            }
        }

        // 2. Load DB shift templates (excluding 00:00:00 empty templates)
        $dbShifts = Shift::templates()
            ->where('created_by', Auth::user()->creatorId())
            ->get();

        foreach ($dbShifts as $s) {
            $sStart = $s->company_start_time;
            $sEnd   = $s->company_end_time;

            if (($sStart == '00:00:00' || empty($sStart)) && ($sEnd == '00:00:00' || empty($sEnd))) {
                continue;
            }

            $templates[] = [
                'id'                 => $s->id,
                'name'               => $s->name,
                'company_start_time' => $sStart ?: '00:00:00',
                'company_end_time'   => $sEnd ?: '00:00:00',
            ];
        }

        return response()->json(['success' => true, 'data' => $templates]);
    }

    // ── GET /rota/branches ────────────────────────────────────────────────────

    public function apiBranches()
    {
        if (!\Auth::user()->can('Manage Rota')) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        $branches = Branch::where('created_by', Auth::user()->creatorId())->get();
        return response()->json(['success' => true, 'data' => $branches]);
    }

    // ── POST /rota/assign-shift ───────────────────────────────────────────────
    // Handles two cases:
    //   is_default=true  → upsert employee default shift (date=null)
    //   is_default=false → upsert date override; if a deleted marker exists for
    //                      that date, reactivate it with the new times.

    public function apiAssignShift(Request $request)
    {
        if (!\Auth::user()->can('Create Rota') && !\Auth::user()->can('Edit Rota')) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        $request->validate([
            'rota_id'     => 'nullable|integer',
            'employee_id' => 'required|integer',
            'date'        => 'nullable|date_format:Y-m-d',
            'is_default'  => 'nullable|boolean',
            'start_time' => [ 'required', function ($attribute, $value, $fail) { if ( !preg_match('/^\d{2}:\d{2}$/', $value) && !preg_match('/^\d{2}:\d{2}:\d{2}$/', $value) ) { $fail("The $attribute must be in HH:MM or HH:MM:SS format."); } }, ],
            'end_time' => [ 'required', function ($attribute, $value, $fail) { if ( !preg_match('/^\d{2}:\d{2}$/', $value) && !preg_match('/^\d{2}:\d{2}:\d{2}$/', $value) ) { $fail("The $attribute must be in HH:MM or HH:MM:SS format."); } }, ],
            'type'        => 'nullable|in:regular,overtime,holiday,night,weekend,emergency,training,leave',
            'name'        => 'nullable|string|max:100',
            'notes'       => 'nullable|string|max:255',
        ]);

        $user     = Auth::user();
        $employee = Employee::where('id', $request->employee_id)
            ->where('created_by', $user->creatorId())
            ->firstOrFail();

        $isDefault = $request->boolean('is_default', false);

        $startTime = strlen($request->start_time) == 5 ? $request->start_time . ':00' : $request->start_time;
        $endTime   = strlen($request->end_time) == 5 ? $request->end_time . ':00' : $request->end_time;
        $shiftType = $request->type ?? 'regular';

        $payload = [
            'name'               => $request->name ?? null,
            'company_start_time' => $startTime,
            'company_end_time'   => $endTime,
            'type'               => $shiftType,
            'notes'              => $request->notes,
            'is_deleted'         => false,
            'created_by'         => $user->creatorId(),
        ];

        $targetShift = null;
        if ($request->filled('rota_id')) {
            $targetShift = Shift::where('id', $request->rota_id)
                ->where('created_by', $user->creatorId())
                ->first();
        }

        if ($targetShift) {
            if ($isDefault) {
                $payload['date'] = null;
            } elseif ($request->filled('date')) {
                $payload['date'] = $request->date;
            }
            $targetShift->fill($payload)->save();
            $rota = $targetShift;

            if ($request->filled('date')) {
                Shift::where('employee_id', $employee->id)
                    ->where('date', $request->date)
                    ->update([
                        'type'               => $shiftType,
                        'company_start_time' => $startTime,
                        'company_end_time'   => $endTime,
                        'is_deleted'         => false,
                    ]);
            }
        } else {
            if ($isDefault) {
                $rota = Shift::updateOrCreate(
                    ['employee_id' => $employee->id, 'date' => null],
                    $payload
                );
            } else {
                if (!$request->filled('date')) {
                    return response()->json(['success' => false, 'message' => 'Date is required for a date-specific shift.'], 422);
                }

                $rota = Shift::updateOrCreate(
                    ['employee_id' => $employee->id, 'date' => $request->date],
                    $payload
                );
            }
        }

        // Fire-and-forget: failures are logged but never break the HRMS response.
        if (!$isDefault && $rota->date) {
            try {
                $shiftPayload = [
                    'date'       => $rota->date->format('Y-m-d'),
                    'start_time' => $rota->company_start_time,
                    'end_time'   => $rota->company_end_time,
                    'type'       => $rota->type ?? 'regular',
                    'notes'      => $rota->notes,
                ];

                $this->idab->updateShift($employee->id, $shiftPayload);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('RotaController: idabcard sync failed on assign.', [
                    'employee_id' => $employee->id,
                    'error'       => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Shift saved successfully.',
            'data'    => [
                'id'          => $rota->id,
                'employee_id' => $rota->employee_id,
                'date'        => $rota->date ? $rota->date->format('Y-m-d') : null,
                'start_time'  => $rota->company_start_time,
                'end_time'    => $rota->company_end_time,
                'type'        => $rota->type,
                'name'        => $rota->name,
                'is_default'  => is_null($rota->date),
            ],
        ]);
    }

    // ── GET /rota/rota-entry/{id} ─────────────────────────────────────────────
    // Works for both default shifts (date=null) and date overrides.

    public function apiGetRotaEntry(string $id)
    {
        if (!\Auth::user()->can('Edit Rota')) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        $id = (int) $id;
        if ($id <= 0) {
            return response()->json(['success' => false, 'message' => 'Invalid shift ID.'], 422);
        }
        $user = Auth::user();

        $rota = Shift::where('id', $id)
            ->where('created_by', $user->creatorId())
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data'    => [
                'id'          => $rota->id,
                'employee_id' => $rota->employee_id,
                'date'        => $rota->date ? $rota->date->format('Y-m-d') : null,
                'start_time'  => $rota->company_start_time,
                'end_time'    => $rota->company_end_time,
                'type'        => $rota->type,
                'name'        => $rota->name,
                'notes'       => $rota->notes,
                'is_default'  => is_null($rota->date),
            ],
        ]);
    }

    public function apiDeleteRotaEntry(Request $request, string $id)
    {
        if (!\Auth::user()->can('Delete Rota')) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        $id = (int) $id;
        if ($id <= 0) {
            return response()->json(['success' => false, 'message' => 'Invalid shift ID.'], 422);
        }
        $user = Auth::user();

        $rota = Shift::where('id', $id)
            ->where('created_by', $user->creatorId())
            ->firstOrFail();

        $isDefaultShift = is_null($rota->date);

        if (!$isDefaultShift) {
            try {
                $this->idab->deleteShift($rota->employee_id, $rota->date->format('Y-m-d'));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('RotaController: idabcard deleteShift failed.', [
                    'shift_id' => $rota->id,
                    'error'    => $e->getMessage(),
                ]);
            }
            $rota->delete();
            return response()->json(['success' => true, 'message' => 'Shift override removed. Default shift will now show for this day.']);
        }

        // It's a default shift — we need a specific date to suppress it for
        $request->validate(['date' => 'required|date_format:Y-m-d']);
        $date = $request->date;

        // Check if there's already a date override for this employee+date
        $existing = Shift::rotaEntries()
            ->where('employee_id', $rota->employee_id)
            ->where('date', $date)
            ->where('created_by', $user->creatorId())
            ->first();

        if ($existing) {
            try {
                $this->idab->deleteShift($existing->employee_id, $date);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('RotaController: idabcard deleteShift (override) failed.', [
                    'shift_id' => $existing->id,
                    'error'    => $e->getMessage(),
                ]);
            }
            $existing->update(['is_deleted' => true]);
        } else {
            // Insert a deletion marker to suppress the default for this date
            Shift::create([
                'employee_id'        => $rota->employee_id,
                'date'               => $date,
                'name'               => null,
                'company_start_time' => $rota->company_start_time,
                'company_end_time'   => $rota->company_end_time,
                'type'               => 'regular',
                'is_deleted'         => true,
                'created_by'         => $user->creatorId(),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Shift hidden for this date.']);
    }

    public function apiBulkAssign(Request $request)
    {
        if (!\Auth::user()->can('Create Rota')) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        $request->validate([
            'employee_ids'   => 'required|array|min:1',
            'employee_ids.*' => 'integer',
            'template_id'    => 'required|integer',
            'start_date'     => 'required|date_format:Y-m-d',
            'end_date'       => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'weekdays'       => 'nullable|array',
            'weekdays.*'     => 'integer|between:0,6',
            'overwrite'      => 'nullable|boolean',
        ]);

        $user = Auth::user();
        if (str_starts_with((string)$request->template_id, 'company_shift_')) {
            $idx = (int) str_replace('company_shift_', '', $request->template_id);
            $settings = \App\Models\Utility::settings();
            $companyShiftsJson = $settings['company_shifts'] ?? '';
            $companyShiftsArr = !empty($companyShiftsJson) ? (json_decode($companyShiftsJson, true) ?? []) : [];
            $cShift = $companyShiftsArr[$idx] ?? null;
            if (!$cShift) {
                return response()->json(['success' => false, 'message' => 'Shift template not found.'], 422);
            }
            $templateStart = !empty($cShift['start']) ? $cShift['start'] : '09:00';
            $templateEnd   = !empty($cShift['end']) ? $cShift['end'] : '17:00';
            if (strlen($templateStart) == 5) $templateStart .= ':00';
            if (strlen($templateEnd) == 5)   $templateEnd .= ':00';
            $templateName  = !empty($cShift['name']) ? $cShift['name'] : __('Shift') . ' ' . ($idx + 1);
        } else {
            $templateObj = Shift::templates()
                ->where('id', $request->template_id)
                ->where('created_by', $user->creatorId())
                ->firstOrFail();
            $templateStart = $templateObj->company_start_time;
            $templateEnd   = $templateObj->company_end_time;
            $templateName  = $templateObj->name;
        }

        $employees = Employee::whereIn('id', $request->employee_ids)
            ->where('created_by', $user->creatorId())
            ->get();

        if ($employees->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No valid employees found.'], 422);
        }

        $weekdays  = $request->weekdays ?? [0, 1, 2, 3, 4, 5, 6];
        $overwrite = $request->boolean('overwrite', false);
        $period    = CarbonPeriod::create($request->start_date, $request->end_date);
        $created   = 0;
        $skipped   = 0;

        // Load saturday pattern from company settings
        $settings   = \App\Models\Utility::settings();
        $satPattern = $settings['saturday_pattern'] ?? 'none';

        foreach ($period as $day) {
            if (!in_array($day->dayOfWeek, $weekdays)) continue;

            // Apply saturday pattern if Saturday is in the requested weekdays
            if ($day->dayOfWeek === 6) {
                $occurrence = (int) ceil($day->day / 7); // which Saturday of the month (1st, 2nd…)
                $isSatWorking = match ($satPattern) {
                    'odd'  => $occurrence % 2 === 1,
                    'even' => $occurrence % 2 === 0,
                    'none' => false,
                    default => true, // 'all'
                };
                if (!$isSatWorking) {
                    $skipped++;
                    continue;
                }
            }
            $dateStr = $day->format('Y-m-d');

            foreach ($employees as $emp) {
                $existing = Shift::rotaEntries()
                    ->where('employee_id', $emp->id)
                    ->where('date', $dateStr)
                    ->first();

                // Active override exists and overwrite is off → skip
                if ($existing && !$existing->is_deleted && !$overwrite) {
                    $skipped++;
                    continue;
                }

                // Upsert: creates new, reactivates deleted marker, or overwrites existing
                Shift::updateOrCreate(
                    ['employee_id' => $emp->id, 'date' => $dateStr],
                    [
                        'name'               => $templateName,
                        'company_start_time' => $templateStart,
                        'company_end_time'   => $templateEnd,
                        'type'               => 'regular',
                        'is_deleted'         => false,
                        'created_by'         => $user->creatorId(),
                    ]
                );
                $created++;
            }
        }

        // idabcard sync
        if ($created > 0) {
            try {
                $idabOptions = [
                    'type'           => 'regular',
                    'status'         => 'scheduled',
                    'skipExisting'   => !$overwrite,
                    'updateExisting' => $overwrite,
                ];

                // Pass customDays only when a specific subset of weekdays was requested
                if (count($weekdays) < 7) {
                    $idabOptions['customDays'] = $weekdays;
                }

                $this->idab->bulkCreateShifts(
                    $employees->pluck('id')->toArray(),
                    $request->start_date,
                    $request->end_date,
                    substr($templateStart, 0, 5),
                    substr($templateEnd, 0, 5),
                    $idabOptions
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('RotaController: idabcard bulkCreateShifts failed.', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$created} shifts assigned" . ($skipped ? ", {$skipped} skipped (already assigned)" : '') . '.',
        ]);
    }

    // ── POST /rota/bulk-remove ────────────────────────────────────────────────
    // Smart delete logic per employee per date in range:
    //   - Active date override exists → mark is_deleted=true
    //   - No date override but employee has a default shift → create deletion marker
    //   - No shift at all → skip

    public function apiBulkRemove(Request $request)
    {
        if (!\Auth::user()->can('Delete Rota')) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        $request->validate([
            'employee_ids'   => 'required|array|min:1',
            'employee_ids.*' => 'integer',
            'start_date'     => 'required|date_format:Y-m-d',
            'end_date'       => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'weekdays'       => 'nullable|array',
            'weekdays.*'     => 'integer|between:0,6',
        ]);

        $user      = Auth::user();
        $creatorId = $user->creatorId();

        $employees = Employee::with('shift')
            ->whereIn('id', $request->employee_ids)
            ->where('created_by', $creatorId)
            ->get();

        if ($employees->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No valid employees found.'], 422);
        }

        $employeeIds = $employees->pluck('id')->toArray();
        $weekdays    = $request->weekdays ?? [0, 1, 2, 3, 4, 5, 6];
        $period      = CarbonPeriod::create($request->start_date, $request->end_date);

        // Load saturday pattern from company settings
        $settings   = \App\Models\Utility::settings();
        $satPattern = $settings['saturday_pattern'] ?? 'none';

        // Load all employee-level default shifts (date=null rows)
        $defaultShifts = Shift::employeeDefaults()
            ->whereIn('employee_id', $employeeIds)
            ->where('created_by', $creatorId)
            ->get()
            ->keyBy('employee_id');

        // Load all existing date overrides in the range (active + deleted markers)
        $existingOverrides = Shift::rotaEntries()
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->where('created_by', $creatorId)
            ->get();

        $removed = 0;
        $skipped = 0;

        foreach ($period as $day) {
            if (!in_array($day->dayOfWeek, $weekdays)) continue;

            // Apply saturday pattern if Saturday is in the requested weekdays
            if ($day->dayOfWeek === 6) {
                $occurrence = (int) ceil($day->day / 7); // which Saturday of the month (1st, 2nd…)
                $isSatWorking = match ($satPattern) {
                    'odd'  => $occurrence % 2 === 1,
                    'even' => $occurrence % 2 === 0,
                    'none' => false,
                    default => true, // 'all'
                };
                if (!$isSatWorking) {
                    $skipped++;
                    continue;
                }
            }

            $dateStr = $day->format('Y-m-d');

            foreach ($employees as $emp) {
                // Find existing date override for this employee+date
                $override = $existingOverrides->first(fn($r) =>
                    $r->employee_id === $emp->id &&
                    $r->date->format('Y-m-d') === $dateStr
                );

                if ($override) {
                    if ($override->is_deleted) {
                        // Already deleted — skip
                        $skipped++;
                        continue;
                    }
                    // Active override → mark as deleted
                    $override->update(['is_deleted' => true]);
                    $removed++;
                    continue;
                }

                // No date override — check if employee has any default shift
                // (employee-level default OR company-assigned shift)
                $hasDefault = isset($defaultShifts[$emp->id]) || $emp->shift;

                if (!$hasDefault) {
                    // No shift at all for this employee — nothing to remove
                    $skipped++;
                    continue;
                }

                // Has a default shift → create a deletion marker to suppress it for this date
                $defaultShift = $defaultShifts[$emp->id] ?? $emp->shift;

                Shift::create([
                    'employee_id'        => $emp->id,
                    'date'               => $dateStr,
                    'name'               => null,
                    'company_start_time' => $defaultShift->company_start_time,
                    'company_end_time'   => $defaultShift->company_end_time,
                    'type'               => 'regular',
                    'is_deleted'         => true,
                    'created_by'         => $creatorId,
                ]);
                $removed++;
            }
        }

        if ($removed > 0) {
            try {
                $options = [];
                if (count($weekdays) < 7) {
                    $options['customDays'] = $weekdays;
                }
                $this->idab->bulkDeleteShifts(
                    $employees->pluck('id')->toArray(),
                    $request->start_date,
                    $request->end_date,
                    $options
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('RotaController: idabcard bulkDeleteShifts failed.', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$removed} shifts removed" . ($skipped ? ", {$skipped} skipped" : '') . '.',
        ]);
    }

    // ── POST /rota/copy-shift ─────────────────────────────────────────────────
    // Copies rota entries from a source period to a target period for one employee.
    // If the source period has no explicit overrides, falls back to the employee's
    // default shift and creates date-specific overrides in the target period.

    public function apiCopyShift(Request $request)
    {
        if (!\Auth::user()->can('Create Rota')) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        $request->validate([
            'user_id'   => 'required|integer',
            'src_start' => 'required|date_format:Y-m-d',
            'src_end'   => 'required|date_format:Y-m-d',
            'tgt_start' => 'required|date_format:Y-m-d',
            'tgt_end'   => 'required|date_format:Y-m-d',
        ]);

        $user     = Auth::user();
        $employee = Employee::with('shift')
            ->where('user_id', $request->user_id)
            ->where('created_by', $user->creatorId())
            ->firstOrFail();

        // Try explicit rota overrides first
        $sourceEntries = Shift::activeRotaEntries()
            ->where('employee_id', $employee->id)
            ->whereBetween('date', [$request->src_start, $request->src_end])
            ->get();

        // If no explicit overrides, fall back to employee-level default or company shift
        $defaultShift = null;
        if ($sourceEntries->isEmpty()) {
            $defaultShift = Shift::employeeDefaults()
                ->where('employee_id', $employee->id)
                ->where('created_by', $user->creatorId())
                ->first()
                ?? $employee->shift; // company-assigned shift

            if (!$defaultShift) {
                return response()->json([
                    'success' => false,
                    'message' => 'No shifts found in source period.',
                ], 422);
            }
        }

        $offset  = Carbon::parse($request->src_start)->diffInDays(Carbon::parse($request->tgt_start), false);
        $tgtEnd  = Carbon::parse($request->tgt_end);
        $srcStart = Carbon::parse($request->src_start);
        $srcEnd   = Carbon::parse($request->src_end);
        $created = 0;
        $skipped = 0;

        if ($defaultShift) {
            // Copy the default shift to every day in the target period
            $period = CarbonPeriod::create($request->tgt_start, $request->tgt_end);
            foreach ($period as $day) {
                $dateStr = $day->format('Y-m-d');

                $exists = Shift::activeRotaEntries()
                    ->where('employee_id', $employee->id)
                    ->where('date', $dateStr)
                    ->exists();

                if ($exists) { $skipped++; continue; }

                Shift::updateOrCreate(
                    ['employee_id' => $employee->id, 'date' => $dateStr],
                    [
                        'name'               => $defaultShift->name,
                        'company_start_time' => $defaultShift->company_start_time,
                        'company_end_time'   => $defaultShift->company_end_time,
                        'type'               => $defaultShift->type ?? 'regular',
                        'notes'              => $defaultShift->notes,
                        'is_deleted'         => false,
                        'created_by'         => $user->creatorId(),
                    ]
                );
                $created++;
            }
        } else {
            // Copy explicit overrides day-by-day with offset
            foreach ($sourceEntries as $src) {
                $newDate = Carbon::parse($src->date->format('Y-m-d'))->addDays($offset);
                if ($newDate->gt($tgtEnd)) continue;

                $exists = Shift::activeRotaEntries()
                    ->where('employee_id', $employee->id)
                    ->where('date', $newDate->format('Y-m-d'))
                    ->exists();

                if ($exists) { $skipped++; continue; }

                Shift::create([
                    'employee_id'        => $employee->id,
                    'date'               => $newDate->format('Y-m-d'),
                    'name'               => $src->name,
                    'company_start_time' => $src->company_start_time,
                    'company_end_time'   => $src->company_end_time,
                    'type'               => $src->type,
                    'notes'              => $src->notes,
                    'is_deleted'         => false,
                    'created_by'         => $user->creatorId(),
                ]);
                $created++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$created} shifts copied" . ($skipped ? ", {$skipped} skipped (already exist)" : '') . '.',
        ]);
    }
}
