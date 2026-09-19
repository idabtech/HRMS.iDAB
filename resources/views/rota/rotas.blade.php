@extends('layouts.admin')

@section('page-title')
    {{ __('Rotas') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Rotas') }}</li>
@endsection

@section('title')
    {{ __('Rotas') }}
@endsection

@section('action-button')
    <div class="d-flex gap-2">
        @can('Create Rota')
            <button class="btn btn-primary" id="copyWeekBtn">
                <i class="ti ti-copy"></i> {{ __('Copy Week') }}
            </button>
            <button class="btn btn-primary" id="bulkAddBtn">
                <i class="ti ti-plus"></i> {{ __('Bulk Assign') }}
            </button>
        @endcan
        @can('Delete Rota')
            <button class="btn btn-danger" id="bulkRemoveBtn">
                <i class="ti ti-trash"></i> {{ __('Bulk Remove') }}
            </button>
        @endcan
    </div>
@endsection

@push('css-page')
<style>
.view-tabs .btn { padding: 0.5rem 1rem; }
.shift-regular  { background:#dcfce7; border:1px solid #86efac; color:#166534; }
.shift-overtime { background:#e0e7ff; border:1px solid #a5b4fc; color:#3730a3; }
.bg-indigo      { background-color:#6366f1 !important; color:#fff !important; }
.shift-holiday  { background:#fef9c2; border:1px solid #fde047; color:#854d0e; }
.shift-night    { background:#99a1af; color:white; border:1px solid #6b7280; }
.shift-weekend  { background:#ffedd4; border:1px solid #fdba74; color:#9a3412; }
.shift-emergency{ background:#ffe2e2; border:1px solid #fca5a5; color:#991b1b; }
.shift-training { background:#dbeafe; border:1px solid #93c5fd; color:#1e40af; }
.shift-leave    { background:#f3e8ff; border:1px solid #d8b4fe; color:#6b21a8; }
#dateLabel { min-width:0; width:200px; white-space:normal; word-break:break-word; cursor:pointer; }
#yearPopoverGrid { display:grid; grid-template-columns:repeat(3,1fr); gap:0.35rem; }
#yearPopoverGrid .btn { font-size:0.875rem; padding:0.35rem 0.25rem; border-radius:0.5rem; }
#yearPopoverGrid .btn.active-year { font-weight:600; }
#yearPopoverGrid .btn.today-year  { font-weight:600; }

/* ── Fix dropdown z-index clipping inside table cells / overflow containers ── */
.monthly-calendar td,
.monthly-calendar th,
#weeklyView td,
#weeklyView th,
#dailyView td {
    overflow: visible !important;
}
.monthly-calendar table,
#weeklyView table,
#dailyView table {
    overflow: visible !important;
}
/* rota-dropdown-menu is now handled by custom JS menu appended to body */
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                    <x-rota.view-tabs />
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <button class="btn btn-sm btn-outline-secondary" id="prevBtn"><i class="ti ti-chevron-left"></i></button>
                        <button class="btn btn-sm btn-outline-secondary" id="todayBtn">{{ __('Today') }}</button>
                        <button class="btn btn-sm btn-outline-secondary" id="nextBtn"><i class="ti ti-chevron-right"></i></button>
                        <div class="position-relative" id="dateLabelWrapper" style="cursor:pointer;" onclick="openDatePickerForView()">
                            <h5 class="mb-0 mx-2 fw-semibold text-dark" id="dateLabel"></h5>
                            <input type="date"   id="dayPicker"   style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;pointer-events:none;" tabindex="-1" data-no-flatpickr>
                            <input type="week"   id="weekPicker"  style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;pointer-events:none;" tabindex="-1" data-no-flatpickr>
                            <input type="month"  id="monthPicker" style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;pointer-events:none;" tabindex="-1" data-no-flatpickr>
                            <input type="number" id="yearPicker"  min="2000" max="2100" style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;pointer-events:none;" tabindex="-1">
                        </div>
                        <div id="yearPickerPopover" style="display:none;position:fixed;z-index:9999;background:#fff;border:1px solid #dee2e6;border-radius:0.75rem;box-shadow:0 8px 24px rgba(0,0,0,.14);padding:1rem;min-width:260px;">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <button type="button" class="btn btn-sm btn-light rounded-circle p-1" id="yearPopoverPrev" style="width:30px;height:30px;"><i class="ti ti-chevron-left"></i></button>
                                <span id="yearPopoverRange" class="fw-semibold text-dark" style="font-size:.95rem;"></span>
                                <button type="button" class="btn btn-sm btn-light rounded-circle p-1" id="yearPopoverNext" style="width:30px;height:30px;"><i class="ti ti-chevron-right"></i></button>
                            </div>
                            <div id="yearPopoverGrid" class="d-grid gap-1" style="grid-template-columns:repeat(3,1fr);"></div>
                            <div class="mt-2 pt-2 border-top text-center">
                                <button type="button" class="btn btn-sm btn-link text-muted p-0" id="yearPopoverToday" style="font-size:.8rem;">{{ __('Current Year') }}</button>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <select class="form-select" id="branchFilter" style="width:200px;">
                            <option value="all">{{ __('All Branches') }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <select class="form-select" id="staffFilter" style="width:200px;">
                            <option value="all">{{ __('All Employee') }}</option>
                        </select>
                    </div>
                </div>
                <x-rota.daily-view />
                <x-rota.weekly-view />
                <x-rota.yearly-view />
                <x-rota.monthly-view />
            </div>
        </div>
    </div>
</div>

<x-rota.add-shift-modal />
<x-rota.bulk-add-modal />
<x-rota.bulk-remove-modal />
<x-rota.copy-week-modal />
@endsection

@push('custom-scripts')
<script>
/* =============================================================
   ROTA — all JS in one push block. Web routes + CSRF only.
   ============================================================= */

// ── Permissions ──────────────────────────────────────────
window.canCreateRota = {{ \Auth::user()->can('Create Rota') ? 'true' : 'false' }};
window.canEditRota   = {{ \Auth::user()->can('Edit Rota')   ? 'true' : 'false' }};
window.canDeleteRota = {{ \Auth::user()->can('Delete Rota') ? 'true' : 'false' }};

// ── Rota working days (from company settings) ─────────────
// Array of day numbers that are enabled (0=Sun, 1=Mon … 6=Sat)
@php
    $rotaSettings  = \App\Models\Utility::settings();
    $rotaWorkingDaysArr = array_map('intval', array_filter(array_map('trim', explode(',', $rotaSettings['rota_working_days'] ?? '1,2,3,4,5')), 'strlen'));
@endphp
window.rotaWorkingDays  = {!! json_encode(array_values($rotaWorkingDaysArr)) !!};
window.saturdayPattern  = '{{ $rotaSettings['saturday_pattern'] ?? 'none' }}';

// ── URLs & CSRF ──────────────────────────────────────────
const ROTA_URLS = {
    staff:          '{{ route("rota.staff") }}',
    shifts:         '{{ route("rota.shifts") }}',
    assign:         '{{ route("rota.assign-shift") }}',
    rotaEntry:      '{{ url("rota/rota-entry") }}',   // + /{id} at runtime
    bulkAssign:     '{{ route("rota.bulk-assign") }}',
    bulkRemove:     '{{ route("rota.bulk-remove") }}',
    copyShift:      '{{ route("rota.copy-shift") }}',
    shiftTemplates: '{{ route("rota.shift-templates") }}',
};
const CSRF = '{{ csrf_token() }}';

// ── State ────────────────────────────────────────────────
let currentView       = 'timeGridWeek';
let currentDate       = new Date();
let allStaff          = [];
let allShiftTemplates = [];
let weekStart         = new Date();
weekStart.setDate(weekStart.getDate() - (weekStart.getDay() === 0 ? 6 : weekStart.getDay() - 1));
let currentYear       = new Date().getFullYear();
let currentMonth      = new Date();
let activeBranchId    = null;

// ── Fetch helper ─────────────────────────────────────────
function rotaFetch(url, opts = {}) {
    const headers = Object.assign({
        'Content-Type': 'application/json',
        'Accept':       'application/json',
        'X-CSRF-TOKEN': CSRF,
    }, opts.headers || {});
    return fetch(url, Object.assign({}, opts, { headers }))
        .then(async r => {
            const json = await r.json();
            if (!r.ok || json.success === false) {
                const e = new Error(json.message || 'Something went wrong');
                e.errors = json.errors || null;
                throw e;
            }
            return json;
        });
}

// ── Boot ─────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    updateDateLabel();
    setupViewTabs();
    setupDatePickerListeners();
    setupYearPopoverListeners();
    setupEventListeners();
    loadShiftTemplates();
});

// ── Load shift templates ──────────────────────────────────
function loadShiftTemplates() {
    rotaFetch(ROTA_URLS.shiftTemplates)
        .then(data => {
            allShiftTemplates = Array.isArray(data.data) ? data.data : [];
            populateShiftTemplateSelects();
        })
        .catch(() => {})
        .finally(() => loadStaff(null));
}

function populateShiftTemplateSelects() {
    // Add-shift modal template quick-fill (no name attr — just auto-fills times)
    const addSel = document.getElementById('addShiftTemplateSelect');
    if (addSel) {
        addSel.innerHTML = '<option value="">{{ __("— select to auto-fill times —") }}</option>';
        allShiftTemplates.forEach(t => {
            const templateName = t.raw_name || t.name;
            addSel.innerHTML += `<option value="${t.id}" data-start="${t.company_start_time}" data-end="${t.company_end_time}" data-name="${templateName}">${t.name} (${t.company_start_time} – ${t.company_end_time})</option>`;
        });
        addSel.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            if (!opt.value) return;
            document.getElementById('addShiftStart').value = opt.dataset.start;
            document.getElementById('addShiftEnd').value   = opt.dataset.end;
        });
    }
    // Bulk-add modal uses template_id
    const bulkSel = document.getElementById('bulkShiftSelect');
    if (bulkSel) {
        bulkSel.innerHTML = '<option value="">{{ __("Select Shift Template") }}</option>';
        allShiftTemplates.forEach(t => {
            bulkSel.innerHTML += `<option value="${t.id}" data-start="${t.company_start_time}" data-end="${t.company_end_time}">${t.name} (${t.company_start_time} – ${t.company_end_time})</option>`;
        });
    }
}

// ── Load staff ────────────────────────────────────────────
function loadStaff(branchId) {
    const spinner = '<tr><td colspan="8" class="text-center p-4"><div class="spinner-border" role="status"></div><div class="mt-2">Loading...</div></td></tr>';
    ['weeklyTableBody','dailyTableBody','yearlyTableBody'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.innerHTML = spinner;
    });
    const mc = document.getElementById('monthlyCalendars');
    if (mc) mc.innerHTML = '<div class="text-center p-4"><div class="spinner-border" role="status"></div></div>';

    let url = ROTA_URLS.staff + '?per_page=100';
    if (branchId && branchId !== 'all') url += '&branch_id=' + encodeURIComponent(branchId);

    rotaFetch(url)
        .then(data => {
            allStaff = Array.isArray(data.data) ? data.data : [];
            populateStaffSelects();
            if (!allStaff.length) { showNoStaffMessage(); return; }
            renderCurrentView();
        })
        .catch(() => showStaffLoadError());
}

// ── Populate staff dropdowns & checkboxes ─────────────────
function populateStaffSelects() {
    // staffFilter dropdown (uses user_id = s.id for shift display filtering)
    const filter = document.getElementById('staffFilter');
    if (filter) {
        filter.querySelectorAll('option:not([value="all"])').forEach(o => o.remove());
        allStaff.forEach(s => filter.innerHTML += `<option value="${s.id}">${s.name}</option>`);
    }
    // Add-shift modal employee select (uses employee_id = s.employee_id)
    const addEmpSel = document.getElementById('addShiftEmployeeSelect');
    if (addEmpSel) {
        addEmpSel.innerHTML = '<option value="">{{ __("Select Staff") }}</option>';
        allStaff.forEach(s => addEmpSel.innerHTML += `<option value="${s.employee_id}">${s.name}${s.role ? ' – ' + s.role : ''}</option>`);
    }
    // Bulk-add checkboxes
    const cbAdd = document.getElementById('staffCheckboxes');
    if (cbAdd) {
        cbAdd.innerHTML = '';
        allStaff.forEach(s => cbAdd.innerHTML += `<div class="form-check mb-2">
            <input class="form-check-input bulk-staff-check" type="checkbox" value="${s.employee_id}" id="sadd_${s.employee_id}">
            <label class="form-check-label" for="sadd_${s.employee_id}">${s.name}${s.role ? ' – ' + s.role : ''}</label>
        </div>`);
    }
    // Bulk-remove checkboxes
    const cbRm = document.getElementById('staffRemoveCheckboxes');
    if (cbRm) {
        cbRm.innerHTML = '';
        allStaff.forEach(s => cbRm.innerHTML += `<div class="form-check mb-2">
            <input class="form-check-input bulk-staff-remove-check" type="checkbox" value="${s.employee_id}" id="srm_${s.employee_id}">
            <label class="form-check-label" for="srm_${s.employee_id}">${s.name}${s.role ? ' – ' + s.role : ''}</label>
        </div>`);
    }
}

// ── Select all / clear helpers ────────────────────────────
function selectAllStaff()       { document.querySelectorAll('.bulk-staff-check').forEach(c => c.checked = true); }
function clearAllStaff()        { document.querySelectorAll('.bulk-staff-check').forEach(c => c.checked = false); }
function selectAllStaffRemove() { document.querySelectorAll('.bulk-staff-remove-check').forEach(c => c.checked = true); }
function clearAllStaffRemove()  { document.querySelectorAll('.bulk-staff-remove-check').forEach(c => c.checked = false); }

// Weekday quick-select helpers for bulk-add modal
function selectWeekdays() {
    document.querySelectorAll('.bulk-weekday-check').forEach(c => {
        if (c.disabled) return;
        c.checked = [1, 2, 3, 4, 5].includes(parseInt(c.value));
    });
}
function selectWeekends() {
    document.querySelectorAll('.bulk-weekday-check').forEach(c => {
        if (c.disabled) return;
        c.checked = [0, 6].includes(parseInt(c.value));
    });
}
function selectAllDays() { document.querySelectorAll('.bulk-weekday-check:not(:disabled)').forEach(c => c.checked = true); }
function clearDays()     { document.querySelectorAll('.bulk-weekday-check:not(:disabled)').forEach(c => c.checked = false); }

// ── Empty / error states ──────────────────────────────────
function showNoStaffMessage() {
    const row = `<tr><td colspan="8" class="text-center p-4"><div class="alert alert-warning mb-0"><i class="ti ti-users-off me-2"></i>{{ __('No employee found.') }}</div></td></tr>`;
    ['weeklyTableBody','dailyTableBody','yearlyTableBody'].forEach(id => { const el = document.getElementById(id); if (el) el.innerHTML = row; });
    const mc = document.getElementById('monthlyCalendars');
    if (mc) mc.innerHTML = `<div class="alert alert-warning"><i class="ti ti-users-off me-2"></i>{{ __('No employee found.') }}</div>`;
}
function showStaffLoadError() {
    const row = `<tr><td colspan="8" class="text-center p-4"><div class="alert alert-danger mb-0"><i class="ti ti-alert-circle me-2"></i>{{ __('Failed to load staff. Please refresh.') }}</div></td></tr>`;
    ['weeklyTableBody','dailyTableBody','yearlyTableBody'].forEach(id => { const el = document.getElementById(id); if (el) el.innerHTML = row; });
    const mc = document.getElementById('monthlyCalendars');
    if (mc) mc.innerHTML = `<div class="alert alert-danger"><i class="ti ti-alert-circle me-2"></i>{{ __('Failed to load staff. Please refresh.') }}</div>`;
}

// ── View tabs ─────────────────────────────────────────────
function setupViewTabs() {
    document.querySelectorAll('.view-tabs button').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.view-tabs button').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentView = this.dataset.view;
            ['dailyView','weeklyView','yearlyView','monthlyView'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.display = 'none';
            });
            const map = { timeGridDay:'dailyView', timeGridWeek:'weeklyView', multiMonthYear:'yearlyView', dayGridMonth:'monthlyView' };
            const target = document.getElementById(map[currentView]);
            if (target) target.style.display = 'block';
            updateDateLabel();
            renderCurrentView();
        });
    });
}

// ── Render dispatcher ─────────────────────────────────────
function renderCurrentView() {
    if      (currentView === 'timeGridDay')    renderDailyView();
    else if (currentView === 'timeGridWeek')   renderWeeklyView();
    else if (currentView === 'dayGridMonth')   renderMonthlyView();
    else if (currentView === 'multiMonthYear') renderYearlyView();
}

// ── Navigation & form event listeners ────────────────────
function setupEventListeners() {
    document.getElementById('prevBtn').addEventListener('click', () => {
        if      (currentView === 'timeGridDay')    currentDate.setDate(currentDate.getDate() - 1);
        else if (currentView === 'timeGridWeek')   weekStart.setDate(weekStart.getDate() - 7);
        else if (currentView === 'multiMonthYear') currentYear--;
        else if (currentView === 'dayGridMonth')   currentMonth.setMonth(currentMonth.getMonth() - 1);
        updateDateLabel(); renderCurrentView();
    });
    document.getElementById('nextBtn').addEventListener('click', () => {
        if      (currentView === 'timeGridDay')    currentDate.setDate(currentDate.getDate() + 1);
        else if (currentView === 'timeGridWeek')   weekStart.setDate(weekStart.getDate() + 7);
        else if (currentView === 'multiMonthYear') currentYear++;
        else if (currentView === 'dayGridMonth')   currentMonth.setMonth(currentMonth.getMonth() + 1);
        updateDateLabel(); renderCurrentView();
    });
    document.getElementById('todayBtn').addEventListener('click', () => {
        currentDate = new Date(); currentYear = new Date().getFullYear(); currentMonth = new Date();
        weekStart = new Date();
        weekStart.setDate(weekStart.getDate() - (weekStart.getDay() === 0 ? 6 : weekStart.getDay() - 1));
        updateDateLabel(); renderCurrentView();
    });
    document.getElementById('branchFilter').addEventListener('change', function () {
        activeBranchId = this.value === 'all' ? null : this.value;
        document.getElementById('staffFilter').value = 'all';
        loadStaff(activeBranchId);
    });
    document.getElementById('staffFilter').addEventListener('change', () => renderCurrentView());

    // Copy Week
    const copyBtn = document.getElementById('copyWeekBtn');
    if (copyBtn) copyBtn.addEventListener('click', handleCopyWeekOpen);
    const copyForm = document.getElementById('copyWeekForm');
    if (copyForm) copyForm.addEventListener('submit', handleCopyWeekSubmit);

    // Bulk Assign
    const bulkAddBtn = document.getElementById('bulkAddBtn');
    if (bulkAddBtn) bulkAddBtn.addEventListener('click', () => { populateStaffSelects(); $('#bulkAddModal').modal('show'); });
    const bulkAddForm = document.getElementById('bulkAddForm');
    if (bulkAddForm) bulkAddForm.addEventListener('submit', handleBulkAddSubmit);

    // Bulk Remove
    const bulkRemoveBtn = document.getElementById('bulkRemoveBtn');
    if (bulkRemoveBtn) bulkRemoveBtn.addEventListener('click', () => { populateStaffSelects(); $('#bulkRemoveModal').modal('show'); });
    const bulkRemoveForm = document.getElementById('bulkRemoveForm');
    if (bulkRemoveForm) bulkRemoveForm.addEventListener('submit', handleBulkRemoveSubmit);

    // Add/Edit shift
    const addShiftForm = document.getElementById('addShiftForm');
    if (addShiftForm) addShiftForm.addEventListener('submit', handleAddShiftSubmit);
}

// ── Copy Week ─────────────────────────────────────────────
function handleCopyWeekOpen() {
    const uid = document.getElementById('staffFilter').value;
    if (!uid || uid === 'all') {
        show_toastr('Warning', '{{ __("Please select a specific staff member first") }}', 'warning');
        return;
    }

    // Determine source date range from current view
    let srcStart, srcEnd;
    if (currentView === 'timeGridDay') {
        srcStart = srcEnd = currentDate.toISOString().split('T')[0];
    } else if (currentView === 'timeGridWeek') {
        const we = new Date(weekStart);
        we.setDate(we.getDate() + 6);
        srcStart = weekStart.toISOString().split('T')[0];
        srcEnd   = we.toISOString().split('T')[0];
    } else if (currentView === 'dayGridMonth') {
        const y = currentMonth.getFullYear(), m = currentMonth.getMonth();
        srcStart = new Date(y, m, 1).toISOString().split('T')[0];
        srcEnd   = new Date(y, m + 1, 0).toISOString().split('T')[0];
    } else if (currentView === 'multiMonthYear') {
        srcStart = currentYear + '-01-01';
        srcEnd   = currentYear + '-12-31';
    } else {
        return;
    }

    // Find staff name for display
    const staffMember = allStaff.find(s => String(s.id) === String(uid));
    const staffName   = staffMember ? staffMember.name : uid;

    // Store on form for submit handler
    const form = document.getElementById('copyWeekForm');
    form.dataset.sourceStart = srcStart;
    form.dataset.sourceEnd   = srcEnd;
    form.dataset.userId      = uid;

    // Update read-only labels
    document.getElementById('copySourceLabel').textContent = srcStart + ' → ' + srcEnd;
    document.getElementById('copyStaffLabel').textContent  = staffName;

    // Clear target date fields via Flatpickr
    ['targetStartDate', 'targetEndDate'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el && el._flatpickr) el._flatpickr.clear();
        else if (el) el.value = '';
    });

    const inst = bootstrap.Modal.getInstance(document.getElementById('copyWeekModal'));
    if (inst) inst.dispose();
    new bootstrap.Modal(document.getElementById('copyWeekModal')).show();
}

function handleCopyWeekSubmit(e) {
    e.preventDefault();

    const form     = document.getElementById('copyWeekForm');
    const srcStart = form.dataset.sourceStart;
    const srcEnd   = form.dataset.sourceEnd;
    const userId   = form.dataset.userId;

    const tgtStart = document.getElementById('targetStartDate').value;
    const tgtEnd   = document.getElementById('targetEndDate').value;

    if (!tgtStart || !tgtEnd) {
        show_toastr('Warning', '{{ __("Please select target start and end dates") }}', 'warning');
        return;
    }
    if (tgtEnd < tgtStart) {
        show_toastr('Warning', '{{ __("Target end date must be after start date") }}', 'warning');
        return;
    }

    const btn = form.querySelector('[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>{{ __("Copying...") }}';

    rotaFetch(ROTA_URLS.copyShift, {
        method: 'POST',
        body: JSON.stringify({
            user_id:   parseInt(userId),
            src_start: srcStart,
            src_end:   srcEnd,
            tgt_start: tgtStart,
            tgt_end:   tgtEnd,
        }),
    })
        .then(r => {
            show_toastr('Success', r.message || '{{ __("Shifts copied successfully") }}', 'success');
            bootstrap.Modal.getInstance(document.getElementById('copyWeekModal')).hide();
            renderCurrentView();
        })
        .catch(err => show_toastr('Error', err.message || '{{ __("Failed to copy shifts") }}', 'error'))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-copy me-1"></i>{{ __("Copy Shifts") }}';
        });
}

// ── Bulk Assign ───────────────────────────────────────────
function handleBulkAddSubmit(e) {
    e.preventDefault();
    const fd         = new FormData(this);
    const templateId = fd.get('template_id');
    if (!templateId) { show_toastr('Warning', '{{ __("Please select a shift template") }}', 'warning'); return; }

    const ids = [...document.querySelectorAll('.bulk-staff-check:checked')].map(c => parseInt(c.value));
    if (!ids.length) { show_toastr('Warning', '{{ __("Please select at least one employee") }}', 'warning'); return; }

    const weekdays = [...document.querySelectorAll('.bulk-weekday-check:checked')].map(c => parseInt(c.value));
    if (!weekdays.length) { show_toastr('Warning', '{{ __("Please select at least one day") }}', 'warning'); return; }

    const startDate = fd.get('start_date');
    const endDate   = fd.get('end_date');
    if (!startDate || !endDate) { show_toastr('Warning', '{{ __("Please select a date range") }}', 'warning'); return; }

    const overwrite = fd.get('overwrite') === '1';

    rotaFetch(ROTA_URLS.bulkAssign, {
        method: 'POST',
        body: JSON.stringify({
            employee_ids: ids,
            template_id:  parseInt(templateId),
            start_date:   startDate,
            end_date:     endDate,
            weekdays,
            overwrite,
        }),
    })
        .then(r => {
            show_toastr('Success', r.message || '{{ __("Shifts assigned successfully") }}', 'success');
            bootstrap.Modal.getInstance(document.getElementById('bulkAddModal')).hide();
            this.reset();
            renderCurrentView();
        })
        .catch(err => {
            if (err.errors) Object.values(err.errors).flat().forEach(m => show_toastr('Error', m, 'error'));
            else show_toastr('Error', err.message || '{{ __("Failed to assign shifts") }}', 'error');
        });
}

// ── Bulk Remove ───────────────────────────────────────────
function handleBulkRemoveSubmit(e) {
    e.preventDefault();

    const ids = [...document.querySelectorAll('.bulk-staff-remove-check:checked')].map(c => parseInt(c.value));
    if (!ids.length) { show_toastr('Warning', '{{ __("Please select at least one employee") }}', 'warning'); return; }

    const startDate = document.getElementById('removeStartDate').value;
    const endDate   = document.getElementById('removeEndDate').value;
    if (!startDate || !endDate) { show_toastr('Warning', '{{ __("Please select a date range") }}', 'warning'); return; }

    const weekdays = [...document.querySelectorAll('.bulk-remove-weekday-check:checked')].map(c => parseInt(c.value));
    if (!weekdays.length) { show_toastr('Warning', '{{ __("Please select at least one day") }}', 'warning'); return; }

    rotaFetch(ROTA_URLS.bulkRemove, {
        method: 'POST',
        body: JSON.stringify({
            employee_ids: ids,
            start_date:   startDate,
            end_date:     endDate,
            weekdays,
        }),
    })
        .then(r => {
            show_toastr('Success', r.message || '{{ __("Shifts removed successfully") }}', 'success');
            bootstrap.Modal.getInstance(document.getElementById('bulkRemoveModal')).hide();
            renderCurrentView();
        })
        .catch(err => show_toastr('Error', err.message || '{{ __("Failed to remove shifts") }}', 'error'));
}

// Weekday helpers for bulk-remove modal
function selectWeekdaysRemove() { document.querySelectorAll('.bulk-remove-weekday-check').forEach(c => { if (c.disabled) return; c.checked = [1,2,3,4,5].includes(parseInt(c.value)); }); }
function selectWeekendsRemove() { document.querySelectorAll('.bulk-remove-weekday-check').forEach(c => { if (c.disabled) return; c.checked = [0,6].includes(parseInt(c.value)); }); }
function selectAllDaysRemove()  { document.querySelectorAll('.bulk-remove-weekday-check:not(:disabled)').forEach(c => c.checked = true); }
function clearDaysRemove()      { document.querySelectorAll('.bulk-remove-weekday-check:not(:disabled)').forEach(c => c.checked = false); }

// ── Shared shift badge builder ────────────────────────────
function buildShiftBadge(shift, date, viewType) {
    const shiftId    = shift.id   || null;
    const shiftType  = shift.type || 'regular';
    const startTime  = shift.startTime || '';
    const endTime    = shift.endTime   || '';
    const shiftName  = shift.shiftName || '';
    const isDefault  = shift.isDefault  ?? false;
    const isLeaveOnly = shift.isLeaveOnly ?? false;
    const leaveInfo  = shift.leave || null;

    const canEdit         = !isLeaveOnly && (window.canEditRota   ?? false);
    const canDelete       = !isLeaveOnly && (window.canDeleteRota ?? false);
    const canShowDropdown = canEdit || canDelete;

    const isMonthly = viewType === 'monthly';
    const fontSize  = isMonthly ? '0.65rem' : '0.7rem';
    const iconSize  = isMonthly ? '12px' : '14px';
    const btnSize   = isMonthly ? '14px' : '16px';
    const padding   = canShowDropdown
        ? (isMonthly ? '4px 20px 4px 6px' : '6px 24px 6px 8px')
        : (isMonthly ? '4px 6px'           : '6px 8px');

    // ── Leave-only entry (no shift assigned, just an approved leave) ──────────
    if (isLeaveOnly) {
        const leaveTypeName = leaveInfo ? leaveInfo.type  : 'Leave';
        const leaveReason   = leaveInfo ? leaveInfo.reason : '';
        const leaveDays     = leaveInfo ? leaveInfo.days   : '';

        return '<div class="shift-leave mb-1"'
            + ' style="border-radius:0.25rem;font-size:' + fontSize + ';padding:' + padding + ';word-break:break-word;">'
            + '<div style="font-weight:600;"><i class="ti ti-beach me-1" style="font-size:0.7rem;"></i>' + leaveTypeName + '</div>'
            + (leaveDays ? '<div style="font-size:0.6rem;opacity:.8;">' + leaveDays + ' day(s)</div>' : '')
            + (leaveReason ? '<div style="font-size:0.6rem;opacity:.7;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="' + leaveReason + '">' + leaveReason + '</div>' : '')
            + '</div>';
    }

    // ── Normal shift entry (with optional leave overlay) ─────────────────────
    const timeStr = typeof formatTime12hours === 'function'
        ? formatTime12hours(startTime + '-' + endTime)
        : startTime + ' – ' + endTime;

    const defaultStyle = isDefault ? 'border-style:dashed;opacity:0.85;' : '';
    const defaultLabel = isDefault ? ' <span style="font-size:0.6rem;opacity:.7;">(default)</span>' : '';
    const typeLabel    = shiftType.charAt(0).toUpperCase() + shiftType.slice(1);

    // Leave overlay badge on top of shift
    let leaveOverlay = '';
    if (leaveInfo && shiftType === 'leave') {
        leaveOverlay = '<div style="font-size:0.6rem;background:rgba(107,33,168,.15);border-radius:3px;padding:1px 4px;margin-top:2px;">'
            + '<i class="ti ti-beach me-1"></i>' + leaveInfo.type
            + '</div>';
    }

    // Overtime pill badge below shift hours
    let overtimeBadge = '';
    const overtimeVal = (shift.attendance && shift.attendance.overtime) ? shift.attendance.overtime : ((shift.attendance && shift.attendance.extra_time) ? shift.attendance.extra_time : null);
    if (overtimeVal && overtimeVal !== '00:00:00') {
        overtimeBadge = '<div class="badge bg-indigo text-white mt-1" style="font-size:0.6rem;line-height:1.2;padding:2px 5px;display:inline-flex;align-items:center;">'
            + '<i class="ti ti-clock me-1" style="font-size:0.65rem;"></i>OT: ' + overtimeVal
            + '</div>';
    }

    const editLabel  = isDefault ? '{{ __("Override for this date") }}' : '{{ __("Edit") }}';
    const editOnclick   = isDefault
        ? 'editDefaultShiftForDate(' + shiftId + ', \'' + date + '\')'
        : 'editShift(' + shiftId + ')';
    const deleteOnclick = 'deleteShift(' + shiftId + ', \'' + date + '\', ' + isDefault + ')';

    let dotsBtnHtml = '';
    if (canShowDropdown) {
        dotsBtnHtml = '<button class="btn btn-link p-0 rota-action-btn" type="button"'
            + ' data-shift-id="'    + (shiftId || '')   + '"'
            + ' data-date="'        + date               + '"'
            + ' data-is-default="'  + (isDefault ? '1' : '0') + '"'
            + ' data-can-edit="'    + (canEdit   ? '1' : '0') + '"'
            + ' data-can-delete="'  + (canDelete  ? '1' : '0') + '"'
            + ' data-edit-label="'  + editLabel          + '"'
            + ' data-employee-id="' + (shift.employeeId || '') + '"'
            + ' data-start-time="'  + startTime          + '"'
            + ' data-end-time="'    + endTime            + '"'
            + ' data-shift-type="'  + shiftType          + '"'
            + ' data-shift-name="'  + shiftName          + '"'
            + ' style="position:absolute;top:2px;right:2px;width:' + btnSize + ';height:' + btnSize + ';line-height:1;font-size:' + iconSize + ';color:inherit;"'
            + ' onclick="event.stopPropagation();openRotaActionMenu(this);">'
            + '<i class="ti ti-dots-vertical"></i>'
            + '</button>';
    }

    return '<div class="shift-' + shiftType + ' mb-1"'
        + ' style="border-radius:0.25rem;font-size:' + fontSize + ';position:relative;padding:' + padding + ';word-break:break-word;' + defaultStyle + '"'
        + ' onclick="event.stopPropagation();">'
        + '<div style="font-weight:600;">' + typeLabel + defaultLabel + '</div>'
        + (shiftName ? '<div style="font-size:0.6rem;opacity:.8;">' + shiftName + '</div>' : '')
        + (startTime ? '<div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + timeStr + '</div>' : '')
        + overtimeBadge
        + leaveOverlay
        + dotsBtnHtml
        + '</div>';
}

// ── Custom rota action menu (replaces Bootstrap dropdown) ─────────────────────
// Appended directly to <body> so it is never clipped by any ancestor.

var _rotaMenuEl = null;

function openRotaActionMenu(btn) {
    closeRotaActionMenu(); // close any existing one first

    var shiftId    = btn.getAttribute('data-shift-id')    || null;
    var date       = btn.getAttribute('data-date')         || '';
    var isDefault  = btn.getAttribute('data-is-default')  === '1';
    var canEdit    = btn.getAttribute('data-can-edit')     === '1';
    var canDelete  = btn.getAttribute('data-can-delete')   === '1';
    var editLabel  = btn.getAttribute('data-edit-label')   || '{{ __("Edit") }}';
    var employeeId = btn.getAttribute('data-employee-id') || '';
    var startTime  = btn.getAttribute('data-start-time')  || '';
    var endTime    = btn.getAttribute('data-end-time')    || '';
    var shiftType  = btn.getAttribute('data-shift-type')  || 'regular';
    var shiftName  = btn.getAttribute('data-shift-name')  || '';

    // Build menu styled like a Bootstrap dropdown-menu
    var menu = document.createElement('div');
    menu.id  = 'rotaActionMenu';
    menu.className = 'dropdown-menu show';
    menu.style.cssText = 'position:fixed;z-index:99999;display:block;min-width:180px;';

    if (canEdit) {
        var editItem = document.createElement('a');
        editItem.href      = '#';
        editItem.className = 'dropdown-item';
        editItem.innerHTML = '<i class="ti ti-edit me-2"></i>' + editLabel;
        editItem.addEventListener('click', function (e) {
            e.preventDefault();
            closeRotaActionMenu();
            if (isDefault) {
                // For default shifts: pre-fill modal directly from badge data
                // (no API call needed — avoids the null/template-id issue)
                _openOverrideModalFromData(employeeId, date, startTime, endTime, shiftType, shiftName);
            } else {
                // For overrides: load from DB to get latest data
                editShift(shiftId);
            }
        });
        menu.appendChild(editItem);
    }

    if (canDelete) {
        var divider = document.createElement('div');
        divider.className = 'dropdown-divider';
        menu.appendChild(divider);

        var delItem = document.createElement('a');
        delItem.href      = '#';
        delItem.className = 'dropdown-item text-danger';
        delItem.innerHTML = '<i class="ti ti-trash me-2"></i>{{ __("Delete") }}';
        delItem.addEventListener('click', function (e) {
            e.preventDefault();
            closeRotaActionMenu();
            deleteShift(shiftId, date, isDefault);
        });
        menu.appendChild(delItem);
    }

    document.body.appendChild(menu);
    _rotaMenuEl = menu;

    // Position relative to the button
    var rect  = btn.getBoundingClientRect();
    var menuW = menu.offsetWidth  || 180;
    var menuH = menu.offsetHeight || 80;
    var vpW   = window.innerWidth;
    var vpH   = window.innerHeight;

    var top  = rect.bottom + 2;
    var left = rect.right  - menuW;

    if (top + menuH > vpH - 8)  top  = rect.top - menuH - 2;
    if (left < 4)                left = 4;
    if (left + menuW > vpW - 4)  left = vpW - menuW - 4;

    menu.style.top  = top  + 'px';
    menu.style.left = left + 'px';

    // Close on outside click
    setTimeout(function () {
        document.addEventListener('click', closeRotaActionMenu, { once: true });
    }, 0);
}

function closeRotaActionMenu() {
    if (_rotaMenuEl) {
        _rotaMenuEl.remove();
        _rotaMenuEl = null;
    }
}

// Pre-fill the override modal directly from badge data (no API call).
// Used when editing a company-default shift (employees.shift_id) which
// has no employee_id on the shifts row so apiGetRotaEntry can't find it.
function _openOverrideModalFromData(employeeId, date, startTime, endTime, shiftType, shiftName) {
    document.getElementById('rotaId').value                    = '';
    document.getElementById('addShiftEmployeeSelect').value    = employeeId;
    document.getElementById('addShiftType').value              = shiftType || 'regular';
    document.getElementById('addShiftStart').value             = startTime;
    document.getElementById('addShiftEnd').value               = endTime;
    document.getElementById('addShiftNotes').value             = '';
    document.getElementById('addShiftTemplateSelect').value    = '';
    document.getElementById('isDefaultToggle').checked         = false;
    document.getElementById('isDefaultHidden').value           = '0';
    document.getElementById('dateFieldWrapper').style.display  = '';
    document.getElementById('addShiftDate').required           = true;
    _setDateValue('addShiftDate', date);
    document.getElementById('overrideBanner').classList.remove('d-none');
    document.getElementById('addShiftModalTitle').textContent  = '{{ __("Override Shift for Date") }}';

    _pendingModalData = {
        date:        date || '',
        employee_id: employeeId,
        type:        shiftType || 'regular',
        start_time:  startTime,
        end_time:    endTime,
        notes:       '',
    };

    _showAddShiftModal();
}

// Close menu on ESC or scroll
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeRotaActionMenu();
});
window.addEventListener('scroll', closeRotaActionMenu, true);

// ── Add/Edit shift ────────────────────────────────────────
function handleAddShiftSubmit(e) {
    e.preventDefault();
    const fd   = new FormData(this);
    const data = Object.fromEntries(fd);

    // Build the payload — rota_id maps to the hidden id field for updates
    const payload = {
        employee_id: data.employee_id,
        start_time:  data.start_time,
        end_time:    data.end_time,
        type:        data.type,
        notes:       data.notes || null,
        is_default:  data.is_default === '1' ? true : false,
    };

    // Include date only for date-specific shifts
    if (payload.is_default === false && data.date) {
        payload.date = data.date;
    }

    // If editing an existing row, pass its id so the backend can updateOrCreate correctly
    if (data.rota_id) {
        payload.rota_id = data.rota_id;
    }

    rotaFetch(ROTA_URLS.assign, { method: 'POST', body: JSON.stringify(payload) })
        .then(r => {
            show_toastr('Success', r.message || '{{ __("Shift saved successfully") }}', 'success');
            bootstrap.Modal.getInstance(document.getElementById('addShiftModal')).hide();
            this.reset();
            document.getElementById('rotaId').value          = '';
            document.getElementById('isDefaultHidden').value = '0';
            document.getElementById('isDefaultToggle').checked = false;
            document.getElementById('dateFieldWrapper').style.display = '';
            document.getElementById('addShiftModalTitle').textContent = '{{ __("Add Shift") }}';
            renderCurrentView();
        })
        .catch(err => show_toastr('Error', err.message || '{{ __("Failed to save shift") }}', 'error'));
}

// ── Edit shift — load rota entry by real DB id ────────────
// This is now handled in add-shift-modal.blade.php (editShift function)
// kept here as a passthrough for backward compatibility with view onclick calls.

// ── Open add-shift modal for a specific staff + date ──────
function openAddShiftForStaff(staffId) {
    openAddShiftForStaffAndDate(staffId, currentDate.toISOString().split('T')[0]);
}

// openAddShiftForStaffAndDate is defined in add-shift-modal.blade.php

// ── Delete shift ──────────────────────────────────────────
// rotaId  = the DB id of the shift row being deleted
// date    = the calendar date being deleted (required when deleting a default shift
//           so we can create a suppression marker for that specific day)
// isDefault = true when the displayed shift is a default (date=null) row
function deleteShift(rotaId, date, isDefault) {
    if (!rotaId) return;

    const msg = isDefault
        ? '{{ __("This will hide the default shift for this date only. The default shift will still apply to other days.") }}'
        : '{{ __("This removes the shift override for this date. The default shift will show again if one exists.") }}';

    Swal.fire({
        title: '{{ __("Delete this shift?") }}', icon: 'warning',
        text:  msg,
        showCancelButton: true, confirmButtonColor: '#d33',
        confirmButtonText: '{{ __("Yes, delete it!") }}',
    }).then(r => {
        if (!r.isConfirmed) return;

        const body = isDefault && date ? { date } : {};

        rotaFetch(`${ROTA_URLS.rotaEntry}/${rotaId}`, {
            method: 'DELETE',
            body:   JSON.stringify(body),
        })
            .then(res => { show_toastr('Success', res.message || '{{ __("Shift deleted") }}', 'success'); renderCurrentView(); })
            .catch(err => show_toastr('Error', err.message || '{{ __("Failed") }}', 'error'));
    });
}

// ── Date label ────────────────────────────────────────────
function updateDateLabel() {
    const pad = n => String(n).padStart(2, '0');
    if (currentView === 'timeGridDay') {
        document.getElementById('dateLabel').textContent = currentDate.toLocaleDateString('en-US', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
        document.getElementById('dayPicker').value = `${currentDate.getFullYear()}-${pad(currentDate.getMonth()+1)}-${pad(currentDate.getDate())}`;
    } else if (currentView === 'timeGridWeek') {
        const we = new Date(weekStart); we.setDate(we.getDate() + 6);
        document.getElementById('dateLabel').textContent =
            weekStart.toLocaleDateString('en-US', { month:'short', day:'numeric' }) + ' – ' +
            we.toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' });
        document.getElementById('weekPicker').value = `${weekStart.getFullYear()}-W${pad(getISOWeekNumber(weekStart))}`;
    } else if (currentView === 'multiMonthYear') {
        document.getElementById('dateLabel').textContent = currentYear;
        document.getElementById('yearPicker').value = currentYear;
    } else if (currentView === 'dayGridMonth') {
        document.getElementById('dateLabel').textContent = currentMonth.toLocaleDateString('en-US', { month:'long', year:'numeric' });
        document.getElementById('monthPicker').value = `${currentMonth.getFullYear()}-${pad(currentMonth.getMonth()+1)}`;
    }
}

function getISOWeekNumber(date) {
    const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
    d.setUTCDate(d.getUTCDate() + 4 - (d.getUTCDay() || 7));
    const y = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
    return Math.ceil((((d - y) / 86400000) + 1) / 7);
}

// ── Date pickers ──────────────────────────────────────────
function openDatePickerForView() {
    if (currentView === 'multiMonthYear') { openYearPickerPopover(); return; }
    ['dayPicker','weekPicker','monthPicker','yearPicker'].forEach(id => document.getElementById(id).style.pointerEvents = 'none');
    const map = { timeGridDay:'dayPicker', timeGridWeek:'weekPicker', dayGridMonth:'monthPicker' };
    const pid = map[currentView];
    if (!pid) return;
    const p = document.getElementById(pid);
    p.style.pointerEvents = 'auto';
    try { p.showPicker(); } catch(ex) { p.focus(); }
}

function setupDatePickerListeners() {
    document.getElementById('dayPicker').addEventListener('change', function () {
        if (!this.value) return;
        currentDate = new Date(this.value + 'T00:00:00');
        updateDateLabel(); renderCurrentView();
    });
    document.getElementById('weekPicker').addEventListener('change', function () {
        if (!this.value) return;
        const [yr, wk] = this.value.split('-W').map(Number);
        const jan4 = new Date(yr, 0, 4);
        const mon  = new Date(jan4);
        mon.setDate(jan4.getDate() - ((jan4.getDay() || 7) - 1) + (wk - 1) * 7);
        weekStart = mon; updateDateLabel(); renderWeeklyView();
    });
    document.getElementById('monthPicker').addEventListener('change', function () {
        if (!this.value) return;
        const [y, m] = this.value.split('-').map(Number);
        currentMonth = new Date(y, m - 1, 1);
        updateDateLabel(); renderMonthlyView();
    });
    document.getElementById('yearPicker').addEventListener('change', function () {
        const v = parseInt(this.value, 10);
        if (!v || v < 2000 || v > 2100) return;
        currentYear = v; updateDateLabel(); renderYearlyView();
    });
}

// ── Year popover ──────────────────────────────────────────
let _yearPage = 0;
function openYearPickerPopover() {
    const pop = document.getElementById('yearPickerPopover');
    if (pop.style.display !== 'none') { closeYearPickerPopover(); return; }
    const rect = document.getElementById('dateLabelWrapper').getBoundingClientRect();
    pop.style.top = (rect.bottom + 6) + 'px';
    pop.style.left = rect.left + 'px';
    _yearPage = Math.floor(currentYear / 12) * 12;
    renderYearPopoverGrid();
    pop.style.display = 'block';
}
function closeYearPickerPopover() { document.getElementById('yearPickerPopover').style.display = 'none'; }
function renderYearPopoverGrid() {
    const todayY = new Date().getFullYear();
    document.getElementById('yearPopoverRange').textContent = `${_yearPage} – ${_yearPage + 11}`;
    const grid = document.getElementById('yearPopoverGrid');
    grid.innerHTML = '';
    for (let y = _yearPage; y <= _yearPage + 11; y++) {
        const btn = document.createElement('button');
        btn.type = 'button'; btn.textContent = y;
        btn.className = 'btn btn-sm btn-outline-secondary';
        if (y === currentYear) btn.classList.add('active-year');
        else if (y === todayY) btn.classList.add('today-year');
        btn.addEventListener('click', () => { currentYear = y; updateDateLabel(); renderYearlyView(); closeYearPickerPopover(); });
        grid.appendChild(btn);
    }
}
function setupYearPopoverListeners() {
    document.getElementById('yearPopoverPrev').addEventListener('click', e => { e.stopPropagation(); _yearPage -= 12; renderYearPopoverGrid(); });
    document.getElementById('yearPopoverNext').addEventListener('click', e => { e.stopPropagation(); _yearPage += 12; renderYearPopoverGrid(); });
    document.getElementById('yearPopoverToday').addEventListener('click', e => { e.stopPropagation(); currentYear = new Date().getFullYear(); updateDateLabel(); renderYearlyView(); closeYearPickerPopover(); });
    document.addEventListener('click', e => {
        const pop = document.getElementById('yearPickerPopover');
        if (pop.style.display === 'none') return;
        if (!pop.contains(e.target) && !document.getElementById('dateLabelWrapper').contains(e.target)) closeYearPickerPopover();
    });
}
</script>
@endpush
