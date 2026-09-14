<?php

namespace Database\Seeders;

use App\Models\SystemModule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class SystemModuleSeeder extends Seeder
{
    public function run()
    {
        $permissions = Permission::all();
        $actions = [
            'Manage', 'Create', 'Edit', 'Delete', 'Show', 'View', 'Move', 'Add', 
            'Download', 'Approve', 'Decline', 'Request', 'buy', 'client permission', 
            'invite user', 'Reset Password', 'Login Enable Disable'
        ];

        $modulesFound = [];

        foreach ($permissions as $p) {
            $name = trim($p->name);
            $moduleName = $name;
            foreach ($actions as $action) {
                if (str_starts_with(strtolower($name), strtolower($action) . ' ')) {
                    $moduleName = trim(substr($name, strlen($action)));
                    break;
                }
            }
            if ($moduleName && !in_array($moduleName, $modulesFound)) {
                $modulesFound[] = $moduleName;
            }
        }

        // Icon mapping for known modules
        $iconMap = [
            'Plan' => 'ti ti-trophy',
            'Plan Request' => 'ti ti-git-pull-request',
            'Storage Addons' => 'ti ti-database',
            'Storage Addon' => 'ti ti-database',
            'Referral Program' => 'ti ti-discount-2',
            'Coupon' => 'ti ti-gift',
            'coupon' => 'ti ti-gift',
            'Order' => 'ti ti-shopping-cart',
            'Email Template' => 'ti ti-mail',
            'Landing Page' => 'ti ti-layout-landing-page',
            'System Setup' => 'ti ti-settings',
            'Language' => 'ti ti-world',
            'User' => 'ti ti-users',
            'Role' => 'ti ti-lock',
            'Employee Profile' => 'ti ti-id',
            'Employee' => 'ti ti-user-check',
            'Employee File Document' => 'ti ti-file-text',
            'Employee Last Login' => 'ti ti-clock',
            'Attendance' => 'ti ti-calendar-check',
            'Attendance Request' => 'ti ti-calendar-event',
            'TimeSheet' => 'ti ti-clock-hour-4',
            'Set Salary' => 'ti ti-cash',
            'Pay Slip' => 'ti ti-file-text',
            'Travel Expense' => 'ti ti-plane-departure',
            'Leave' => 'ti ti-calendar-minus',
            'Leave Document' => 'ti ti-file-certificate',
            'Report' => 'ti ti-chart-bar',
            'Indicator' => 'ti ti-adjustments',
            'Appraisal' => 'ti ti-medal',
            'Goal Tracking' => 'ti ti-target',
            'Account List' => 'ti ti-credit-card',
            'Payee' => 'ti ti-user-minus',
            'Payer' => 'ti ti-user-plus',
            'Deposit' => 'ti ti-wallet',
            'Expense' => 'ti ti-receipt',
            'Transfer Balance' => 'ti ti-arrows-exchange',
            'Training' => 'ti ti-school',
            'Trainer' => 'ti ti-user-voice',
            'Award' => 'ti ti-award',
            'Transfer' => 'ti ti-arrows-left-right',
            'Resignation' => 'ti ti-logout',
            'Travel' => 'ti ti-plane',
            'Promotion' => 'ti ti-trending-up',
            'Complaint' => 'ti ti-message-report',
            'Warning' => 'ti ti-alert-triangle',
            'Termination' => 'ti ti-user-off',
            'Announcement' => 'ti ti-speakerphone',
            'Holiday' => 'ti ti-sun',
            'Job' => 'ti ti-briefcase',
            'Job Application' => 'ti ti-file-description',
            'Job OnBoard' => 'ti ti-user-check',
            'Interview Schedule' => 'ti ti-calendar',
            'Custom Question' => 'ti ti-help',
            'Career' => 'ti ti-building',
            'Contract' => 'ti ti-file-contract',
            'Ticket' => 'ti ti-ticket',
            'Event' => 'ti ti-calendar-event',
            'Meeting' => 'ti ti-device-laptop',
            'Assets' => 'ti ti-box',
            'Document' => 'ti ti-files',
            'HR Document Library' => 'ti ti-folder',
            'Company Policy' => 'ti ti-clipboard-list',
            'Branch' => 'ti ti-git-branch',
            'Department' => 'ti ti-subtask',
            'Subdepartment' => 'ti ti-git-fork',
            'Designation' => 'ti ti-user-circle',
            'Wages' => 'ti ti-coin',
            'Shift' => 'ti ti-clock',
            'Leave Type' => 'ti ti-list-details',
            'Document Type' => 'ti ti-file-code',
            'Payslip Type' => 'ti ti-report-money',
            'Allowance' => 'ti ti-plus',
            'Allowance Option' => 'ti ti-adjustments-horizontal',
            'Loan' => 'ti ti-businessplan',
            'Loan Option' => 'ti ti-adjustments-alt',
            'Training Type' => 'ti ti-book',
            'Award Type' => 'ti ti-badge-ad',
            'Termination Type' => 'ti ti-user-x',
            'Job Category' => 'ti ti-category',
            'Job Stage' => 'ti ti-stack-2',
            'Performance Type' => 'ti ti-star',
            'Competencies' => 'ti ti-checkbox',
            'Expense Type' => 'ti ti-tags',
            'Income Type' => 'ti ti-receipt-tax',
            'Payment Type' => 'ti ti-currency',
            'Contract Type' => 'ti ti-news',
            'Employment Type' => 'ti ti-id-badge',
            'Rota' => 'ti ti-calendar-time',
            'Salary Revision' => 'ti ti-trending-up',
        ];

        $superAdminModules = ['Plan', 'Plan Request', 'Storage Addons', 'Storage Addon', 'Referral Program', 'Coupon', 'coupon', 'Order', 'Email Template', 'Landing Page', 'System Setup', 'Language', 'Company Settings', 'System Settings'];

        $existingSlugs = [];
        $order = 1;

        foreach ($modulesFound as $modName) {
            $slug = Str::slug($modName);
            if (empty($slug)) continue;

            $existingSlugs[] = $slug;

            $isSuperAdmin = in_array($modName, $superAdminModules);
            $icon = $iconMap[$modName] ?? 'ti ti-box';

            SystemModule::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => ucfirst($modName),
                    'icon' => $icon,
                    'description' => "{$modName} system module",
                    'module_type' => $isSuperAdmin ? 'super_admin' : 'company',
                    'available_for' => $isSuperAdmin ? 'both' : 'business',
                    'parent_id' => null,
                    'sort_order' => $order++,
                    'status' => true,
                    'is_always_included' => in_array($modName, ['System Setup', 'Landing Page', 'Language']),
                    'is_auto_included' => false,
                ]
            );
        }

        // Delete any module from system_modules table that does not exist in actual system permissions
        SystemModule::whereNotIn('slug', $existingSlugs)->delete();
    }
}
