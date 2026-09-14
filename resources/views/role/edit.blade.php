{{ Form::model($role, ['route' => ['roles.update', $role->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group">
            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}<x-required></x-required>
            @if ($role->name == 'employee')
                <p class="form-control">{{ $role->name }}</p>
            @else
                <div class="form-icon-user">
                    {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Role Name')]) }}
                </div>
            @endif
            @error('name')
                <span class="invalid-name" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group">
            @if (!empty($permissions))
                <div class="card bg-light border-0 shadow-none mb-3 p-3 rounded-3">
                    <div class="row align-items-center g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold mb-1.5 text-dark" style="font-size: 0.85rem;">{{ __('Exclude Modules from this Role:') }}</label>
                            <div class="position-relative">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0"><i class="ti ti-search text-muted"></i></span>
                                    <input type="text" id="roleModuleSearchInput" class="form-control form-control-sm border-start-0 ps-0" placeholder="{{ __('Search...') }}" autocomplete="off">
                                    <button class="btn btn-outline-secondary" type="button" id="clearRoleModuleSearch" title="{{ __('Clear Search') }}">
                                        <i class="ti ti-x"></i>
                                    </button>
                                </div>
                                <!-- Module Select Dropdown List -->
                                <div id="moduleDropdownList" class="dropdown-menu module-dropdown-container w-100 border-0" style="display: none;">
                                    <div class="d-flex flex-column" id="moduleDropdownItems">
                                        <!-- Dynamically populated -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <h6 class="my-3">{{ __('Assign Permission to Roles') }} </h6>
                <table class="table  mb-0" id="dataTable-1">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" class="align-middle checkbox_middle form-check-input"
                                    name="checkall" id="checkall">
                            </th>
                            <th>{{ __('Module') }} </th>
                            <th>{{ __('Permissions') }} </th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            if (Auth::user()->isSuperAdminSideUser() && !in_array($role->name, ['company', 'employee'])) {
                                $modules = [
                                    'User',
                                    'Role',
                                    'HR Document Library',
                                    'Plan',
                                    'Plan Request',
                                    'Storage Addons',
                                    'Referral Program',
                                    'Coupon',
                                    'Order',
                                    'Email Template',
                                    'Landing Page',
                                    'System Setup',
                                    'Language',
                                ];
                            } else {
                                $modules = [
                                    'Report',
                                    'User',
                                    'Role',
                                    'Employee Profile',
                                    'Employee Last Login',
                                    'Attendance Request',
                                    'Employee',
                                    'Employee File Document',
                                    'Set Salary',
                                    'Pay Slip',
                                    'Travel Expense',
                                    'TimeSheet',
                                    'Leave',
                                    'Leave Document',
                                    'Attendance',
                                    'Indicator',
                                    'Appraisal',
                                    'Self Assessment',
                                    'Goal Tracking',
                                    'Account List',
                                    'Balance Account List',
                                    'Payee',
                                    'Payer',
                                    'Deposit',
                                    'Expense',
                                    'Transfer Balance',
                                    'Training',
                                    'Trainer',
                                    'Award',
                                    'Transfer',
                                    'Resignation',
                                    'Travel',
                                    'Promotion',
                                    'Complaint',
                                    'Warning',
                                    'Termination',
                                    'Announcement',
                                    'Holiday',
                                    'Job',
                                    'Job Application',
                                    'Job OnBoard',
                                    'Interview Schedule',
                                    'Custom Question',
                                    'Career',
                                    'Contract',
                                    'Ticket',
                                    'Event',
                                    'Meeting',
                                    'Assets',
                                    'Document',
                                    'HR Document Library',
                                    'Company Policy',
                                    'Branch',
                                    'Department',
                                    'Subdepartment',
                                    'Designation',
                                    'Wages',
                                    'Shift',
                                    'Leave Type',
                                    'Document Type',
                                    'Payslip Type',
                                    'Allowance',
                                    'Allowance Option',
                                    'Loan',
                                    'Loan Option',
                                    'Training Type',
                                    'Award Type',
                                    'Termination Type',
                                    'Job Category',
                                    'Job Stage',
                                    'Performance Type',
                                    'Competencies',
                                    'Expense Type',
                                    'Income Type',
                                    'Payment Type',
                                    'Contract Type',
                                    'Employment Type',
                                    'Plan',
                                    'Deduction Option',
                                    'Commission',
                                    'Saturation Deduction',
                                    'Other Payment',
                                    'Bonous',
                                    'Peark',
                                    'Pension',
                                    'Overtime',
                                    'Goal Type',
                                    'Job Application Note',
                                    'Job Application Skill',
                                    'Zoom meeting',
                                    'Rota',
                                    'Salary Revision'
                                ];
                            }

                            $permissionTypes = [
                                'Manage',
                                'Create',
                                'Edit',
                                'Reset Password',
                                'Login Enable Disable',
                                'Delete',
                                'Show',
                                'Move',
                                'client permission',
                                'invite user',
                                'buy',
                                'Add',
                                'Approve',
                                'Decline',
                                'Request',
                                'View',
                                'Download',
                                'Review',
                                'Bulk Generate',
                            ];
                            $rolePermissions = $role->permissions()->pluck('id')->toArray();
                        @endphp
                        @foreach ($modules as $module)
                            @php
                                $hasPermission = false;
                                foreach ($permissionTypes as $type) {
                                    if ((!Auth::user()->isSuperAdminSideUser() || in_array($role->name, ['company', 'employee'])) && $module == 'HR Document Library' && in_array($type, ['Create', 'Edit', 'Delete'])) {
                                        continue;
                                    }
                                    $permName = $type . ' ' . $module;
                                    if (in_array($permName, (array) $permissions) || in_array(strtolower($permName), (array) $permissions)) {
                                        $hasPermission = true;
                                        break;
                                    }
                                }
                            @endphp

                            @if ($hasPermission)
                                @php
                                    $superAdminOnlyModules = ['Plan', 'Plan Request', 'Storage Addons', 'Referral Program', 'Coupon', 'Order', 'Email Template', 'Landing Page', 'System Setup', 'Language'];
                                    $modCategory = in_array($module, $superAdminOnlyModules) ? 'super_admin' : 'company';
                                @endphp
                                <tr class="role-module-row" data-module="{{ strtolower($module) }}" data-category="{{ $modCategory }}">
                                    <td>
                                        <input type="checkbox" class="align-middle ischeck form-check-input"
                                            name="checkall" data-id="{{ str_replace(' ', '', $module) }}">
                                    </td>
                                    <td>
                                        <label class="ischeck" data-id="{{ str_replace(' ', '', $module) }}">
                                            {{ ucfirst($module) }}
                                        </label>
                                    </td>
                                    <td>
                                        <div class="row">
                                            @foreach ($permissionTypes as $type)
                                                @php
                                                    if ((!Auth::user()->isSuperAdminSideUser() || in_array($role->name, ['company', 'employee'])) && $module == 'HR Document Library' && in_array($type, ['Create', 'Edit', 'Delete'])) {
                                                        continue;
                                                    }
                                                    $permissionKey = array_search(
                                                        $type . ' ' . $module,
                                                        (array) $permissions,
                                                    );
                                                    if ($permissionKey === false) {
                                                        $permissionKey = array_search(
                                                            strtolower($type . ' ' . $module),
                                                            (array) $permissions,
                                                        );
                                                    }
                                                @endphp
                                                @if ($permissionKey !== false)
                                                    <div class="col-md-3 custom-control custom-checkbox">
                                                        {{ Form::checkbox('permissions[]', $permissionKey, in_array($permissionKey, $rolePermissions), ['class' => 'form-check-input isscheck isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $permissionKey]) }}
                                                        {{ Form::label('permission' . $permissionKey, ucfirst($type), ['class' => 'form-label font-weight-500', 'style' => 'white-space: normal;']) }}
                                                        <br>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <input type="submit" value="{{ __('Update') }}" class="btn  btn-primary">
</div>
{{ Form::close() }}


<style>
    .module-dropdown-container {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 10px !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.08) !important;
        padding: 6px !important;
        margin-top: 4px !important;
        max-height: 230px !important;
        overflow-y: auto !important;
        z-index: 1060 !important;
    }
    .module-dropdown-container::-webkit-scrollbar {
        width: 5px;
    }
    .module-dropdown-container::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 10px;
    }
    .module-dropdown-item {
        padding: 8px 12px !important;
        border-radius: 6px !important;
        font-size: 0.85rem !important;
        font-weight: 500 !important;
        color: #334155 !important;
        display: flex !important;
        align-items: center !important;
        margin-bottom: 2px !important;
        transition: all 0.15s ease-in-out !important;
        cursor: pointer !important;
        text-decoration: none !important;
        border: none !important;
        background: transparent;
    }
    .module-dropdown-item.d-none-item {
        display: none !important;
    }
    .module-dropdown-item:hover:not(.active) {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
    }
    .module-dropdown-item.active {
        background-color: #5c59e8 !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        box-shadow: 0 2px 6px rgba(92, 89, 232, 0.25) !important;
    }
    .module-dropdown-item.active i {
        color: #ffffff !important;
    }
</style>

<script>
    (function() {
        function populateModuleDropdownIfNeeded() {
            var itemsHtml = '<a href="javascript:void(0)" class="dropdown-item module-dropdown-item active" data-module="all"><i class="ti ti-list-check me-2 fs-6 text-primary"></i>' + "{{ __('All Modules') }}" + '</a>';
            
            $('.role-module-row').each(function() {
                var moduleName = $(this).find('td:nth-child(2)').text().trim();
                if (moduleName) {
                    itemsHtml += '<a href="javascript:void(0)" class="dropdown-item module-dropdown-item" data-module="' + moduleName.toLowerCase() + '"><i class="ti ti-layers-subtract me-2 fs-6 text-muted"></i>' + moduleName + '</a>';
                }
            });
            
            $('#moduleDropdownItems').html(itemsHtml);
        }

        // Immediately populate on script execute inside modal
        setTimeout(populateModuleDropdownIfNeeded, 100);

        $(document).on('click', "#checkall", function() {
            $('.role-module-row:visible input:checkbox').not(this).prop('checked', this.checked);
        });
        $(document).on('click', ".ischeck", function() {
            var ischeck = $(this).data('id');
            $('.isscheck_' + ischeck).prop('checked', this.checked);
        });

        // Dynamic Module Search & Dropdown List Interactions
        $(document).on('focus click', '#roleModuleSearchInput', function() {
            populateModuleDropdownIfNeeded();
            $('#moduleDropdownList').show();
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('.position-relative').length) {
                $('#moduleDropdownList').hide();
            }
        });

        $(document).on('keyup input', '#roleModuleSearchInput', function() {
            populateModuleDropdownIfNeeded();
            var val = $(this).val().toLowerCase().trim();
            $('#moduleDropdownList').show();
            
            $('#moduleDropdownItems .module-dropdown-item').each(function() {
                var modText = $(this).text().toLowerCase().trim();
                var modAttr = $(this).attr('data-module') || '';
                if (modAttr === 'all' || val === '' || modText.indexOf(val) > -1) {
                    $(this).removeClass('d-none-item');
                } else {
                    $(this).addClass('d-none-item');
                }
            });

            filterRoleModules();
        });

        $(document).on('click', '.module-dropdown-item', function(e) {
            e.preventDefault();
            var mod = $(this).data('module');
            var text = $(this).text().trim();
            
            $('.module-dropdown-item').removeClass('active');
            $(this).addClass('active');

            if (mod === 'all') {
                $('#roleModuleSearchInput').val('');
            } else {
                $('#roleModuleSearchInput').val(text);
            }
            
            $('#moduleDropdownList').hide();
            filterRoleModules();
        });

        $(document).on('click', '#clearRoleModuleSearch', function() {
            $('#roleModuleSearchInput').val('');
            $('.module-dropdown-item').removeClass('active');
            $('.module-dropdown-item[data-module="all"]').addClass('active');
            $('#moduleDropdownList').hide();
            filterRoleModules();
        });

        function filterRoleModules() {
            var searchValue = $('#roleModuleSearchInput').val().toLowerCase().trim();

            $('.role-module-row').each(function() {
                var row = $(this);
                var moduleName = row.find('td:nth-child(2)').text().toLowerCase();

                if (searchValue === '' || moduleName.indexOf(searchValue) > -1) {
                    row.show();
                } else {
                    row.hide();
                }
            });
        }
    })();
</script>
