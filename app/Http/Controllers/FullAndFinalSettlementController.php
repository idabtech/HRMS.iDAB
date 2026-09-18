<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\FullAndFinalSettlement;
use App\Models\Utility;
use App\Models\User;
use App\Models\Notification;
use App\Exports\SettlementExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FullAndFinalSettlementController extends Controller
{
    /**
     * Display a listing of Full & Final settlements.
     */
    public function index(Request $request)
    {
        if (Auth::user()->can('Manage Settlement')) {
            $user = Auth::user();
            $creatorId = $user->creatorId();

            $query = FullAndFinalSettlement::where('created_by', $creatorId)
                ->with(['employee.department', 'employee.designation'])
                ->orderBy('id', 'desc');

            if ($user->type === 'employee') {
                $emp = Employee::where('user_id', $user->id)->first();
                if ($emp) {
                    $query->where('employee_id', $emp->id);
                } else {
                    $query->where('id', 0);
                }
            }

            $employees = Employee::where('created_by', $creatorId)->pluck('name', 'id')->prepend(__('All Employees'), '');

            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('disbursal_status')) {
                $query->where('final_settlement_status', $request->disbursal_status);
            }

            if ($request->filled('start_date')) {
                $query->whereDate('last_working_day', '>=', $request->start_date);
            }

            if ($request->filled('end_date')) {
                $query->whereDate('last_working_day', '<=', $request->end_date);
            }

            if ($request->filled('search')) {
                $term = $request->search;
                $query->where(function ($q) use ($term) {
                    $q->where('settlement_number', 'like', "%{$term}%")
                      ->orWhere('employee_name', 'like', "%{$term}%")
                      ->orWhere('employee_code', 'like', "%{$term}%")
                      ->orWhere('department', 'like', "%{$term}%");
                });
            }

            $settlements = $query->get();

            return view('settlement.index', compact('settlements', 'employees'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Show the form for creating a new settlement.
     */
    public function create(Request $request)
    {
        if (Auth::user()->can('Create Settlement')) {
            $creatorId = Auth::user()->creatorId();
            $employees = Employee::where('created_by', $creatorId)->get();

            $selectedEmployee = null;
            if ($request->filled('employee_id')) {
                $selectedEmployee = Employee::where('created_by', $creatorId)->find($request->employee_id);
            }

            $defaultClearance = FullAndFinalSettlement::getDefaultClearanceChecklist();
            $defaultDeclaration = FullAndFinalSettlement::defaultDeclarationText();
            $defaultPolicyRules = FullAndFinalSettlement::defaultPolicyRulesText();
            $companyPolicies = \App\Models\CompanyPolicy::where('created_by', $creatorId)->get();

            return view('settlement.create', compact('employees', 'selectedEmployee', 'defaultClearance', 'defaultDeclaration', 'defaultPolicyRules', 'companyPolicies'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Store a newly created settlement.
     */
    public function store(Request $request)
    {
        if (Auth::user()->can('Create Settlement')) {
            $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'last_working_day' => 'required|date',
                'reason_for_separation' => 'required|string|max:255',
            ]);

            $creatorId = Auth::user()->creatorId();
            $employee = Employee::where('created_by', $creatorId)->findOrFail($request->employee_id);

            // Generate Settlement Number: FNF-YYYYMM-XXXX
            $count = FullAndFinalSettlement::where('created_by', $creatorId)->count() + 1;
            $settlementNumber = 'FNF-' . date('Ym') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            // Process Earnings
            $earnings = [];
            $gross = 0;
            if ($request->has('earnings_name') && is_array($request->earnings_name)) {
                foreach ($request->earnings_name as $idx => $name) {
                    $amt = floatval($request->earnings_amount[$idx] ?? 0);
                    if (!empty(trim($name))) {
                        $earnings[] = ['name' => trim($name), 'amount' => $amt];
                        $gross += $amt;
                    }
                }
            }

            // Process Deductions
            $deductions = [];
            $totalDeductions = 0;
            if ($request->has('deductions_name') && is_array($request->deductions_name)) {
                foreach ($request->deductions_name as $idx => $name) {
                    $amt = floatval($request->deductions_amount[$idx] ?? 0);
                    if (!empty(trim($name))) {
                        $deductions[] = ['name' => trim($name), 'amount' => $amt];
                        $totalDeductions += $amt;
                    }
                }
            }

            // Process Clearance Matrix
            $clearance = [];
            if ($request->has('clearance_item') && is_array($request->clearance_item)) {
                foreach ($request->clearance_item as $idx => $item) {
                    $cat = $request->clearance_category[$idx] ?? 'General';
                    $status = $request->clearance_status[$idx] ?? 'Pending';
                    $remarks = $request->clearance_remarks[$idx] ?? '';
                    if (!empty(trim($item))) {
                        $clearance[] = [
                            'category' => trim($cat),
                            'item' => trim($item),
                            'status' => $status,
                            'remarks' => trim($remarks),
                        ];
                    }
                }
            }

            // Process In-Section Google Form Custom Questions Schema
            $customFieldsSchema = [];
            $customFieldsData = [];
            if ($request->has('custom_field_label') && is_array($request->custom_field_label)) {
                foreach ($request->custom_field_label as $idx => $lbl) {
                    $label = trim($lbl);
                    if (!empty($label)) {
                        $section = $request->custom_field_section[$idx] ?? 'general';
                        $key = $section . '_' . Str::slug($label, '_') . '_' . $idx;
                        $type = $request->custom_field_type[$idx] ?? 'text';
                        $target = $request->custom_field_target[$idx] ?? 'hr'; // 'hr' or 'employee'
                        $required = !empty($request->custom_field_required[$idx]);
                        $optionsRaw = $request->custom_field_options[$idx] ?? '';
                        $options = array_values(array_filter(array_map('trim', explode(',', $optionsRaw))));
                        $initialVal = $request->custom_field_value[$idx] ?? '';

                        $customFieldsSchema[] = [
                            'key' => $key,
                            'section' => $section,
                            'label' => $label,
                            'type' => $type,
                            'target' => $target,
                            'required' => $required,
                            'options' => $options,
                        ];

                        if ($target === 'hr') {
                            $customFieldsData[$key] = $initialVal;
                        }
                    }
                }
            }

            $netAmount = max(0, $gross - $totalDeductions);

            $settlement = FullAndFinalSettlement::create([
                'created_by' => $creatorId,
                'employee_id' => $employee->id,
                'settlement_number' => $settlementNumber,
                'industry_type' => $request->get('industry_type', 'software'),
                'status' => 'draft',
                'employee_name' => $employee->name,
                'employee_code' => $employee->employee_id,
                'designation' => $employee->designation?->name ?? '',
                'department' => $employee->department?->name ?? '',
                'date_of_joining' => $employee->company_doj,
                'last_working_day' => $request->last_working_day,
                'reason_for_separation' => $request->reason_for_separation,
                'earnings_data' => $earnings,
                'deductions_data' => $deductions,
                'gross_payable' => $gross,
                'total_deductions' => $totalDeductions,
                'net_amount' => $netAmount,
                'clearance_data' => $clearance,
                'custom_fields_schema' => $customFieldsSchema,
                'custom_fields_data' => $customFieldsData,
                'declaration_text' => $request->input('declaration_text', FullAndFinalSettlement::defaultDeclarationText()),
                'policy_rules_text' => $request->input('policy_rules_text', FullAndFinalSettlement::defaultPolicyRulesText()),
                'policy_rules_link' => $request->input('policy_rules_link'),
                'policy_rules_title' => $request->input('policy_rules_title'),
                'sharing_token' => Str::random(64),
                'token_expires_at' => now()->addDays(30),
            ]);

            $settlement->logActivity('Created', 'Settlement record created in Draft status with reference ' . $settlementNumber);

            return redirect()->route('settlement.show', $settlement->id)
                ->with('success', __('Full & Final Settlement created successfully.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Display the specified settlement.
     */
    public function show($id)
    {
        if (Auth::user()->can('Show Settlement') || Auth::user()->can('Manage Settlement')) {
            $creatorId = Auth::user()->creatorId();
            $settlement = FullAndFinalSettlement::where('created_by', $creatorId)
                ->with(['employee.department', 'employee.designation'])
                ->findOrFail($id);

            $settlement->ensureSharingToken();

            return view('settlement.show', compact('settlement'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Show the form for editing the settlement.
     */
    public function edit($id)
    {
        if (Auth::user()->can('Edit Settlement')) {
            $creatorId = Auth::user()->creatorId();
            $settlement = FullAndFinalSettlement::where('created_by', $creatorId)->findOrFail($id);
            $employees = Employee::where('created_by', $creatorId)->get();
            $companyPolicies = \App\Models\CompanyPolicy::where('created_by', $creatorId)->get();

            return view('settlement.edit', compact('settlement', 'employees', 'companyPolicies'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Update the settlement.
     */
    public function update(Request $request, $id)
    {
        if (Auth::user()->can('Edit Settlement')) {
            $creatorId = Auth::user()->creatorId();
            $settlement = FullAndFinalSettlement::where('created_by', $creatorId)->findOrFail($id);

            // Process Earnings
            $earnings = [];
            $gross = 0;
            if ($request->has('earnings_name') && is_array($request->earnings_name)) {
                foreach ($request->earnings_name as $idx => $name) {
                    $amt = floatval($request->earnings_amount[$idx] ?? 0);
                    if (!empty(trim($name))) {
                        $earnings[] = ['name' => trim($name), 'amount' => $amt];
                        $gross += $amt;
                    }
                }
            }

            // Process Deductions
            $deductions = [];
            $totalDeductions = 0;
            if ($request->has('deductions_name') && is_array($request->deductions_name)) {
                foreach ($request->deductions_name as $idx => $name) {
                    $amt = floatval($request->deductions_amount[$idx] ?? 0);
                    if (!empty(trim($name))) {
                        $deductions[] = ['name' => trim($name), 'amount' => $amt];
                        $totalDeductions += $amt;
                    }
                }
            }

            // Process Clearance Matrix
            $clearance = [];
            if ($request->has('clearance_item') && is_array($request->clearance_item)) {
                foreach ($request->clearance_item as $idx => $item) {
                    $cat = $request->clearance_category[$idx] ?? 'General';
                    $status = $request->clearance_status[$idx] ?? 'Pending';
                    $remarks = $request->clearance_remarks[$idx] ?? '';
                    if (!empty(trim($item))) {
                        $clearance[] = [
                            'category' => trim($cat),
                            'item' => trim($item),
                            'status' => $status,
                            'remarks' => trim($remarks),
                        ];
                    }
                }
            }

            // Process In-Section Google Form Custom Questions Schema
            $customFieldsSchema = [];
            $existingData = $settlement->custom_fields_data ?? [];
            if ($request->has('custom_field_label') && is_array($request->custom_field_label)) {
                foreach ($request->custom_field_label as $idx => $lbl) {
                    $label = trim($lbl);
                    if (!empty($label)) {
                        $section = $request->custom_field_section[$idx] ?? 'general';
                        $key = $request->custom_field_key[$idx] ?? ($section . '_' . Str::slug($label, '_') . '_' . $idx);
                        $type = $request->custom_field_type[$idx] ?? 'text';
                        $target = $request->custom_field_target[$idx] ?? 'hr';
                        $required = !empty($request->custom_field_required[$idx]);
                        $optionsRaw = $request->custom_field_options[$idx] ?? '';
                        $options = array_values(array_filter(array_map('trim', explode(',', $optionsRaw))));

                        $customFieldsSchema[] = [
                            'key' => $key,
                            'section' => $section,
                            'label' => $label,
                            'type' => $type,
                            'target' => $target,
                            'required' => $required,
                            'options' => $options,
                        ];

                        if ($target === 'hr' && isset($request->custom_field_value[$idx])) {
                            $existingData[$key] = $request->custom_field_value[$idx];
                        }
                    }
                }
            }

            $netAmount = max(0, $gross - $totalDeductions);

            $settlement->update([
                'last_working_day' => $request->last_working_day ?? $settlement->last_working_day,
                'reason_for_separation' => $request->reason_for_separation ?? $settlement->reason_for_separation,
                'earnings_data' => $earnings,
                'deductions_data' => $deductions,
                'gross_payable' => $gross,
                'total_deductions' => $totalDeductions,
                'net_amount' => $netAmount,
                'clearance_data' => $clearance,
                'custom_fields_schema' => $customFieldsSchema,
                'custom_fields_data' => $existingData,
                'declaration_text' => $request->input('declaration_text', $settlement->getDeclarationText()),
                'policy_rules_text' => $request->input('policy_rules_text', $settlement->getPolicyRulesText()),
                'policy_rules_link' => $request->input('policy_rules_link'),
                'policy_rules_title' => $request->input('policy_rules_title'),
                'final_settlement_status' => $request->final_settlement_status ?? $settlement->final_settlement_status,
                'payment_date' => $request->payment_date,
                'payment_mode' => $request->payment_mode,
                'payment_reference_no' => $request->payment_reference_no,
                'hr_representative_name' => $request->hr_representative_name,
                'authorized_signatory_name' => $request->authorized_signatory_name,
                'authorized_date' => $request->authorized_date,
            ]);

            $settlement->logActivity('Updated', 'Settlement details, earnings/deductions or clearance checkpoints updated by HR');

            return redirect()->route('settlement.show', $settlement->id)
                ->with('success', __('Settlement updated successfully.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Delete the settlement.
     */
    public function destroy($id)
    {
        if (Auth::user()->can('Delete Settlement')) {
            $creatorId = Auth::user()->creatorId();
            $settlement = FullAndFinalSettlement::where('created_by', $creatorId)->findOrFail($id);
            $settlement->delete();

            return redirect()->route('settlement.index')->with('success', __('Settlement deleted successfully.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Send settlement form link to employee via email.
     */
    public function sendMail(Request $request, $id)
    {
        if (Auth::user()->can('Send Settlement Mail') || Auth::user()->can('Manage Settlement')) {
            $creatorId = Auth::user()->creatorId();
            $settlement = FullAndFinalSettlement::where('created_by', $creatorId)->with('employee')->findOrFail($id);

            // Determine recipient email
            if ($request->isMethod('post')) {
                $request->validate([
                    'recipient_email' => 'required|email',
                ]);
                $targetEmail = trim($request->recipient_email);
            } else {
                $targetEmail = $settlement->employee->email ?? '';
            }

            if (empty($targetEmail)) {
                return redirect()->back()->with('error', __('Recipient email address not found. Please provide a valid email address.'));
            }

            $token = $settlement->ensureSharingToken();
            $publicUrl = route('settlement.clearance.view', ['token' => $token]);

            // Construct custom message & subject
            $defaultMessage = "Dear " . $settlement->employee_name . ",\n\n" .
                "Your Full & Final Settlement and Departmental Clearance statement (Ref: " . $settlement->settlement_number . ") has been prepared for review.\n\n" .
                "Please review your financial breakdown, departmental clearance checkpoints, and submit your digital sign-off using the link below:\n\n" .
                $publicUrl . "\n\n" .
                "Regards,\n" . (Auth::user()->name ?? 'HR Operations Team');

            $customMessage = $request->filled('custom_message') ? trim($request->custom_message) : $defaultMessage;
            $emailSubject = $request->filled('email_subject') 
                ? trim($request->email_subject) 
                : __('Full & Final Settlement & Clearance — ') . $settlement->settlement_number;

            // Configure SMTP settings
            $settings = Utility::settings();
            $data = Utility::getSetting();
            $setting = [
                'mail_driver' => '',
                'mail_host' => '',
                'mail_port' => '',
                'mail_encryption' => '',
                'mail_username' => '',
                'mail_password' => '',
                'mail_from_address' => '',
                'mail_from_name' => '',
            ];
            foreach ($data as $row) {
                $setting[$row->name] = $row->value;
            }

            $mailDriver = !empty($settings['mail_driver']) ? $settings['mail_driver'] : (!empty($setting['mail_driver']) ? $setting['mail_driver'] : 'smtp');
            $mailHost = !empty($settings['mail_host']) ? $settings['mail_host'] : (!empty($setting['mail_host']) ? $setting['mail_host'] : '');
            $mailPort = !empty($settings['mail_port']) ? $settings['mail_port'] : (!empty($setting['mail_port']) ? $setting['mail_port'] : '');
            $mailEncryption = !empty($settings['mail_encryption']) ? $settings['mail_encryption'] : (!empty($setting['mail_encryption']) ? $setting['mail_encryption'] : '');
            $mailUsername = !empty($settings['mail_username']) ? $settings['mail_username'] : (!empty($setting['mail_username']) ? $setting['mail_username'] : '');
            $mailPassword = !empty($settings['mail_password']) ? $settings['mail_password'] : (!empty($setting['mail_password']) ? $setting['mail_password'] : '');
            $mailFromAddress = !empty($settings['mail_from_address']) ? $settings['mail_from_address'] : (!empty($setting['mail_from_address']) ? $setting['mail_from_address'] : config('mail.from.address'));
            $mailFromName = !empty($settings['mail_from_name']) ? $settings['mail_from_name'] : (!empty($setting['mail_from_name']) ? $setting['mail_from_name'] : config('app.name', 'HRMS'));

            config([
                'mail.default' => $mailDriver,
                'mail.mailers.smtp.transport' => $mailDriver,
                'mail.mailers.smtp.host' => $mailHost,
                'mail.mailers.smtp.port' => $mailPort,
                'mail.mailers.smtp.encryption' => $mailEncryption,
                'mail.mailers.smtp.username' => $mailUsername,
                'mail.mailers.smtp.password' => $mailPassword,
                'mail.from.address' => $mailFromAddress,
                'mail.from.name' => $mailFromName,
            ]);

            $emailSent = false;
            try {
                Mail::send('email.settlement_clearance', [
                    'settlement' => $settlement,
                    'custom_message' => $customMessage,
                    'publicUrl' => $publicUrl,
                ], function ($message) use ($targetEmail, $emailSubject, $mailFromAddress, $mailFromName) {
                    $message->to($targetEmail)
                            ->from($mailFromAddress, $mailFromName)
                            ->subject($emailSubject);
                });
                $emailSent = true;
            } catch (\Throwable $e) {
                Log::error('Settlement clearance custom email sending failed: ' . $e->getMessage());
                // Fallback attempt via Utility
                try {
                    $uArr = [
                        'employee_name' => $settlement->employee_name,
                        'settlement_number' => $settlement->settlement_number,
                        'settlement_url' => $publicUrl,
                        'net_amount' => $settlement->net_amount,
                    ];
                    Utility::sendEmailTemplate('employee_resignation', [$targetEmail], $uArr);
                    $emailSent = true;
                } catch (\Throwable $ex) {
                    Log::error('Settlement clearance email template fallback failed: ' . $ex->getMessage());
                }
            }

            $settlement->update([
                'status' => $settlement->status === 'draft' ? 'sent' : $settlement->status,
                'link_shared_at' => now(),
            ]);

            $settlement->logActivity('Link Sent via Email', 'Clearance form link sent to: ' . $targetEmail);

            if ($emailSent) {
                return redirect()->back()->with('success', __('Settlement clearance email successfully sent to: ') . $targetEmail);
            } else {
                return redirect()->back()->with('success', __('Settlement status updated. Note: Delivery to ') . $targetEmail . __(' may depend on active SMTP settings.'));
            }
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Standalone public view accessible via secure token.
     */
    public function publicView($token)
    {
        $settlement = FullAndFinalSettlement::where('sharing_token', $token)
            ->with(['employee', 'creator'])
            ->firstOrFail();

        if ($settlement->token_expires_at && $settlement->token_expires_at->isPast()) {
            return view('settlement.expired', compact('settlement'));
        }

        $company = User::find($settlement->created_by);

        return view('settlement.public_view', compact('settlement', 'company'));
    }

    /**
     * Submit employee digital signature, declaration, and employee-targeted custom fields via public view.
     */
    public function publicSubmitSignature(Request $request, $token)
    {
        $settlement = FullAndFinalSettlement::where('sharing_token', $token)->firstOrFail();

        // Lock form if already signed or cleared
        if ($settlement->status === 'signed' || $settlement->status === 'cleared' || $settlement->employee_declaration_accepted) {
            return response()->json([
                'success' => false,
                'message' => __('This settlement has already been signed and submitted. No further modifications are permitted.'),
            ], 422);
        }

        $validationRules = [
            'employee_declaration' => 'required',
            'employee_signature' => 'required|string',
        ];
        if (!empty($settlement->policy_rules_text) || !empty($settlement->policy_rules_link)) {
            $validationRules['employee_policy_rules'] = 'required';
        }
        $request->validate($validationRules);

        // Update clearance checklist items with optional employee handover remarks
        $clearanceData = $settlement->clearance_data ?? [];
        if ($request->has('clearance_items') && is_array($request->clearance_items)) {
            foreach ($request->clearance_items as $itemData) {
                $idx = $itemData['idx'] ?? null;
                if ($idx !== null && isset($clearanceData[$idx])) {
                    if (isset($itemData['remarks']) && trim($itemData['remarks']) !== '') {
                        $clearanceData[$idx]['remarks'] = trim($itemData['remarks']);
                    }
                }
            }
        }

        // Merge employee responses for custom fields assigned to 'employee'
        $customData = $settlement->custom_fields_data ?? [];
        if ($request->has('custom_fields') && is_array($request->custom_fields)) {
            foreach ($request->custom_fields as $key => $val) {
                $customData[$key] = is_array($val) ? implode(', ', $val) : $val;
            }
        }

        $settlement->update([
            'employee_declaration_accepted' => true,
            'policy_rules_accepted' => (bool) $request->input('employee_policy_rules', true),
            'employee_signature' => $request->employee_signature,
            'employee_signed_at' => now(),
            'employee_signed_ip' => $request->ip(),
            'employee_remarks' => $request->employee_remarks,
            'clearance_data' => $clearanceData,
            'custom_fields_data' => $customData,
            'status' => 'signed',
        ]);

        $settlement->logActivity(
            'Signed by Employee',
            'Employee digitally signed clearance declaration and verified checklist items from IP ' . $request->ip(),
            $settlement->employee_name
        );

        // Notify HR Admin / Creator via In-App Dashboard Notification (matches Leave/Event system)
        try {
            if (!empty($settlement->created_by)) {
                Notification::create([
                    'user_id'     => $settlement->created_by,
                    'type'        => 'settlement',
                    'title'       => 'Settlement Signed: ' . $settlement->employee_name,
                    'message'     => 'Full & Final Settlement (' . $settlement->settlement_number . ') has been digitally signed and submitted by ' . $settlement->employee_name . '.',
                    'icon'        => 'ti ti-file-certificate',
                    'badge_text'  => 'SETTLEMENT SIGNED',
                    'badge_color' => 'success',
                    'action_url'  => route('settlement.show', $settlement->id),
                    'extra_data'  => [
                        'description'       => 'Full & Final Settlement (' . $settlement->settlement_number . ') digitally signed and submitted.',
                        'settlement_number' => $settlement->settlement_number,
                        'employee_name'     => $settlement->employee_name,
                        'department'        => $settlement->department,
                        'net_amount'        => $settlement->net_amount,
                        'signed_at'         => now()->format('d M Y, h:i A'),
                        'ip'                => $request->ip(),
                    ],
                    'is_read'     => 0,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Settlement dashboard notification creation failed: ' . $e->getMessage());
        }

        // Notify HR Admin / Creator via Email
        try {
            $creator = $settlement->creator;
            if ($creator && !empty($creator->email)) {
                $subject = "Full & Final Settlement Signed: {$settlement->employee_name} ({$settlement->settlement_number})";
                $viewUrl = route('settlement.show', $settlement->id);
                $body = "Dear {$creator->name},\n\n"
                    . "The separating employee {$settlement->employee_name} ({$settlement->employee_code}) has completed, digitally signed, and submitted their Full & Final Settlement and clearance form.\n\n"
                    . "Settlement Number: {$settlement->settlement_number}\n"
                    . "Department: {$settlement->department}\n"
                    . "Net Payable Amount: " . number_format((float)$settlement->net_amount, 2) . "\n"
                    . "Signed Timestamp: " . now()->format('d M Y, h:i A') . "\n"
                    . "Submission IP: " . $request->ip() . "\n\n"
                    . "You can review their submitted checklist notes, answers, and digital signature in the HRMS dashboard:\n"
                    . "{$viewUrl}\n\n"
                    . "Regards,\n" . config('app.name');

                Mail::raw($body, function ($msg) use ($creator, $subject) {
                    $msg->to($creator->email)->subject($subject);
                });
            }
        } catch (\Throwable $e) {
            Log::warning('Settlement notification email to HR failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => __('Thank you! Your Full & Final Settlement has been successfully signed and submitted.'),
        ]);
    }

    /**
     * Export settlements to Excel spreadsheet.
     */
    public function export(Request $request)
    {
        if (Auth::user()->can('Manage Settlement')) {
            $creatorId = Auth::user()->creatorId();
            $filters = $request->all();
            $fileName = 'settlements_' . date('Ymd_His') . '.xlsx';
            return Excel::download(new SettlementExport($creatorId, $filters), $fileName);
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Regenerate / extend sharing token for a settlement.
     */
    public function regenerateLink($id)
    {
        if (Auth::user()->can('Manage Settlement') || Auth::user()->can('Edit Settlement')) {
            $creatorId = Auth::user()->creatorId();
            $settlement = FullAndFinalSettlement::where('created_by', $creatorId)->findOrFail($id);

            $settlement->renewSharingToken(30);
            $settlement->logActivity('Link Renewed', 'HR renewed the secure clearance link (extended for 30 days)');

            return redirect()->back()->with('success', __('Clearance form link renewed successfully. Valid for the next 30 days.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Recall settlement to Draft status for revisions before signature.
     */
    public function recallToDraft($id)
    {
        if (Auth::user()->can('Manage Settlement') || Auth::user()->can('Edit Settlement')) {
            $creatorId = Auth::user()->creatorId();
            $settlement = FullAndFinalSettlement::where('created_by', $creatorId)->findOrFail($id);

            if ($settlement->status === 'signed' || $settlement->status === 'cleared') {
                return redirect()->back()->with('error', __('Cannot recall a settlement that has already been signed or cleared.'));
            }

            $settlement->update(['status' => 'draft']);
            $settlement->logActivity('Recalled to Draft', 'HR recalled the settlement back to Draft status for editing and revisions');

            return redirect()->route('settlement.edit', $settlement->id)
                ->with('success', __('Settlement recalled to Draft. You may now make and save your revisions.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Manager / HOD countersign verification.
     */
    public function managerCountersign(Request $request, $id)
    {
        if (Auth::user()->can('Manage Settlement') || Auth::user()->can('Edit Settlement')) {
            $request->validate([
                'manager_name' => 'required|string|max:255',
                'manager_signature' => 'required|string',
            ]);

            $creatorId = Auth::user()->creatorId();
            $settlement = FullAndFinalSettlement::where('created_by', $creatorId)->findOrFail($id);

            $settlement->update([
                'manager_name' => $request->manager_name,
                'manager_signature' => $request->manager_signature,
                'manager_signed_at' => now(),
                'manager_remarks' => $request->manager_remarks,
            ]);

            $settlement->logActivity('Manager Countersign', 'Department Manager/HOD (' . $request->manager_name . ') countersigned and verified handover');

            return redirect()->back()->with('success', __('Manager / HOD clearance countersign recorded successfully.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Management final clearance and payment sign-off.
     */
    public function managementSignoff(Request $request, $id)
    {
        if (Auth::user()->can('Manage Settlement') || Auth::user()->can('Edit Settlement')) {
            $request->validate([
                'authorized_signatory_name' => 'required|string|max:255',
                'authorized_signature' => 'required|string',
                'payment_mode' => 'required|string',
                'payment_date' => 'required|date',
                'payment_reference_no' => 'required|string',
            ]);

            $creatorId = Auth::user()->creatorId();
            $settlement = FullAndFinalSettlement::where('created_by', $creatorId)->findOrFail($id);

            $settlement->update([
                'authorized_signatory_name' => $request->authorized_signatory_name,
                'authorized_signature' => $request->authorized_signature,
                'authorized_date' => $request->payment_date,
                'hr_representative_name' => $request->hr_representative_name ?: Auth::user()->name,
                'hr_cleared_at' => now(),
                'payment_mode' => $request->payment_mode,
                'payment_date' => $request->payment_date,
                'payment_reference_no' => $request->payment_reference_no,
                'final_settlement_status' => 'Cleared',
                'status' => 'cleared',
            ]);

            $settlement->logActivity('Final Clearance & Disbursed', 'Authorized Signatory (' . $request->authorized_signatory_name . ') completed final sign-off. Payment recorded via ' . $request->payment_mode . ' (Ref: ' . $request->payment_reference_no . ')');

            return redirect()->back()->with('success', __('Final management sign-off and payment details recorded. Settlement is marked Cleared & Paid.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Download clean PDF export of the settlement.
     */
    public function downloadPdf($id)
    {
        $creatorId = Auth::user() ? Auth::user()->creatorId() : null;
        $query = FullAndFinalSettlement::query();
        if ($creatorId) {
            $query->where('created_by', $creatorId);
        }
        $settlement = $query->with(['employee', 'creator'])->findOrFail($id);
        $company = User::find($settlement->created_by);

        return view('settlement.pdf', compact('settlement', 'company'));
    }

    /**
     * Public download of settlement PDF for separating employee using sharing token.
     */
    public function publicDownloadPdf($token)
    {
        $settlement = FullAndFinalSettlement::where('sharing_token', $token)->with(['employee', 'creator'])->firstOrFail();
        if ($settlement->isLinkExpired()) {
            return view('settlement.expired', compact('settlement'));
        }
        $company = User::find($settlement->created_by);

        return view('settlement.pdf', compact('settlement', 'company'));
    }

    /**
     * Update clearance checklist status and verification notes from admin show page.
     */
    public function updateClearanceChecklist(Request $request, $id)
    {
        if (Gate::check('Edit Settlement') || \Auth::user()->can('Manage Settlement')) {
            $settlement = FullAndFinalSettlement::findOrFail($id);
            $clearanceData = $settlement->clearance_data ?? [];

            // Single item quick update (AJAX or form)
            if ($request->has('item_index')) {
                $idx = (int) $request->input('item_index');
                if (isset($clearanceData[$idx])) {
                    $newStatus = $request->input('status', 'Returned');
                    $clearanceData[$idx]['status'] = $newStatus;
                    if ($request->has('remarks') && $request->input('remarks') !== null) {
                        $clearanceData[$idx]['remarks'] = trim($request->input('remarks'));
                    }
                    $settlement->update(['clearance_data' => $clearanceData]);
                    $settlement->logActivity(
                        'Clearance Checkpoint Updated',
                        'Updated checkpoint "' . ($clearanceData[$idx]['item'] ?? 'Item') . '" status to ' . $newStatus,
                        \Auth::user()->name
                    );

                    if ($request->ajax()) {
                        return response()->json([
                            'success' => true,
                            'message' => __('Clearance checkpoint updated successfully.'),
                            'status' => $newStatus,
                        ]);
                    }

                    return redirect()->back()->with('success', __('Clearance checkpoint updated successfully.'));
                }
            }

            // Batch update all checkpoints
            if ($request->has('clearance_statuses') && is_array($request->clearance_statuses)) {
                foreach ($request->clearance_statuses as $idx => $status) {
                    if (isset($clearanceData[$idx])) {
                        $clearanceData[$idx]['status'] = $status;
                        if (isset($request->clearance_remarks[$idx])) {
                            $clearanceData[$idx]['remarks'] = trim($request->clearance_remarks[$idx]);
                        }
                    }
                }
                $settlement->update(['clearance_data' => $clearanceData]);
                $settlement->logActivity(
                    'Clearance Checklist Updated',
                    'Departmental clearance checkpoints updated by ' . \Auth::user()->name,
                    \Auth::user()->name
                );

                return redirect()->back()->with('success', __('Departmental clearance checklist updated successfully.'));
            }

            // Quick Mark All Cleared
            if ($request->has('mark_all_cleared')) {
                foreach ($clearanceData as &$item) {
                    if (($item['status'] ?? '') !== 'Not Applicable') {
                        $item['status'] = 'Returned';
                    }
                }
                unset($item);
                $settlement->update(['clearance_data' => $clearanceData]);
                $settlement->logActivity(
                    'All Clearances Marked Cleared',
                    'All departmental checkpoints marked as Returned / Cleared by ' . \Auth::user()->name,
                    \Auth::user()->name
                );

                return redirect()->back()->with('success', __('All clearance items have been verified and marked as Cleared!'));
            }

            return redirect()->back()->with('error', __('Invalid clearance update request.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }
}