<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FullAndFinalSettlement extends Model
{
    protected $table = 'full_and_final_settlements';

    protected $fillable = [
        'created_by',
        'employee_id',
        'settlement_number',
        'industry_type',
        'status',
        'employee_name',
        'employee_code',
        'designation',
        'department',
        'date_of_joining',
        'last_working_day',
        'reason_for_separation',
        'earnings_data',
        'deductions_data',
        'gross_payable',
        'total_deductions',
        'net_amount',
        'clearance_data',
        'custom_fields_schema',
        'custom_fields_data',
        'declaration_text',
        'policy_rules_text',
        'policy_rules_link',
        'policy_rules_title',
        'policy_rules_accepted',
        'employee_declaration_accepted',
        'employee_signature',
        'employee_signed_at',
        'employee_signed_ip',
        'employee_remarks',
        'manager_name',
        'manager_signature',
        'manager_signed_at',
        'manager_remarks',
        'hr_representative_name',
        'hr_signature',
        'hr_cleared_at',
        'final_settlement_status',
        'payment_date',
        'payment_mode',
        'payment_reference_no',
        'authorized_signatory_name',
        'authorized_signature',
        'authorized_date',
        'sharing_token',
        'token_expires_at',
        'link_shared_at',
        'activity_logs',
    ];

    protected $casts = [
        'earnings_data' => 'array',
        'deductions_data' => 'array',
        'clearance_data' => 'array',
        'custom_fields_schema' => 'array',
        'custom_fields_data' => 'array',
        'activity_logs' => 'array',
        'policy_rules_accepted' => 'boolean',
        'employee_declaration_accepted' => 'boolean',
        'date_of_joining' => 'date',
        'last_working_day' => 'date',
        'payment_date' => 'date',
        'authorized_date' => 'date',
        'employee_signed_at' => 'datetime',
        'manager_signed_at' => 'datetime',
        'hr_cleared_at' => 'datetime',
        'token_expires_at' => 'datetime',
        'link_shared_at' => 'datetime',
        'gross_payable' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate or renew cryptographically secure public sharing token
     */
    public function ensureSharingToken(): string
    {
        if (empty($this->sharing_token)) {
            return $this->renewSharingToken(30);
        }
        return $this->sharing_token;
    }

    /**
     * Force renew cryptographically secure public sharing token
     */
    public function renewSharingToken(int $days = 30): string
    {
        $this->sharing_token = Str::random(64);
        $this->token_expires_at = now()->addDays($days);
        $this->save();
        return $this->sharing_token;
    }

    /**
     * Check if the sharing token is expired
     */
    public function isLinkExpired(): bool
    {
        return !empty($this->token_expires_at) && $this->token_expires_at->isPast();
    }

    /**
     * Remaining days until sharing token expires
     */
    public function daysUntilExpiry(): int
    {
        if (empty($this->token_expires_at)) {
            return 0;
        }
        return (int) max(0, now()->diffInDays($this->token_expires_at, false));
    }

    /**
     * Append an entry to the settlement activity log audit trail
     */
    public function logActivity(string $action, string $description, ?string $user = null): void
    {
        $logs = $this->activity_logs ?? [];
        $logs[] = [
            'action' => $action,
            'description' => $description,
            'user' => $user ?: (\Auth::check() ? \Auth::user()->name : 'System'),
            'timestamp' => now()->toIso8601String(),
            'formatted_time' => now()->format('d M Y, h:i A'),
        ];
        $this->activity_logs = $logs;
        $this->save();
    }

    /**
     * Get public shareable URL
     */
    public function getPublicUrlAttribute(): string
    {
        $token = $this->ensureSharingToken();
        return route('settlement.clearance.view', ['token' => $token]);
    }

    /**
     * Dynamically format a date based on company / HRMS date format settings
     */
    public function formatDate($date, bool $includeTime = false): string
    {
        if (empty($date) || $date == '0000-00-00' || $date == '0000-00-00 00:00:00') {
            return '—';
        }

        $creatorId = $this->created_by ?: (\Auth::check() ? \Auth::user()->creatorId() : 1);
        $settings = Utility::getCompanySettings($creatorId);
        $dateFormat = !empty($settings['site_date_format']) ? $settings['site_date_format'] : 'd-m-Y';

        if ($date instanceof \DateTimeInterface) {
            $timestamp = $date->getTimestamp();
        } elseif (is_numeric($date)) {
            $timestamp = (int)$date;
        } else {
            $timestamp = strtotime((string)$date);
        }

        if (!$timestamp) {
            return (string)$date;
        }

        if ($includeTime) {
            $timeFormat = !empty($settings['site_time_format']) ? $settings['site_time_format'] : 'h:i A';
            return date($dateFormat . ', ' . $timeFormat, $timestamp);
        }

        return date($dateFormat, $timestamp);
    }

    /**
     * Dynamically format a currency price based on company currency settings
     */
    public function formatPrice($amount): string
    {
        $creatorId = $this->created_by ?: (\Auth::check() ? \Auth::user()->creatorId() : 1);
        $settings = Utility::getCompanySettings($creatorId);
        $symbol = !empty($settings['site_currency_symbol']) ? $settings['site_currency_symbol'] : '₹';
        $position = !empty($settings['site_currency_symbol_position']) ? $settings['site_currency_symbol_position'] : 'pre';
        $formattedNum = number_format((float)$amount, 2);

        return ($position === 'pre' ? $symbol . ' ' : '') . $formattedNum . ($position === 'post' ? ' ' . $symbol : '');
    }

    /**
     * Dynamically format an employee ID based on company employee prefix settings
     */
    public function formatEmployeeId($number = null): string
    {
        $id = $number ?? ($this->employee->employee_id ?? $this->employee_id);
        if (empty($id)) {
            return $this->employee_code ?: '—';
        }

        $creatorId = $this->created_by ?: (\Auth::check() ? \Auth::user()->creatorId() : 1);
        $settings = Utility::getCompanySettings($creatorId);
        $prefix = !empty($settings['employee_prefix']) ? $settings['employee_prefix'] : '#EMP';

        return $prefix . sprintf("%05d", $id);
    }

    /**
     * Pre-defined industry clearance templates
     */
    public static function getDefaultClearanceChecklist(string $industry = 'software'): array
    {
        if ($industry === 'custom') {
            return [
                ['category' => 'Department / Clearance', 'item' => 'Checklist checkpoint description', 'status' => 'Pending'],
            ];
        }

        if ($industry === 'salon') {
            return [
                ['category' => 'Salon Equipment & Tools', 'item' => 'Personal styling kit & shears/appliances accounted for', 'status' => 'Pending'],
                ['category' => 'Salon Equipment & Tools', 'item' => 'Locker & styling station keys returned', 'status' => 'Pending'],
                ['category' => 'POS & Client Accounts', 'item' => 'Salon POS / billing terminal account de-registered', 'status' => 'Pending'],
                ['category' => 'POS & Client Accounts', 'item' => 'Upcoming appointments & client registers handed over', 'status' => 'Pending'],
                ['category' => 'Salon Property', 'item' => 'Company aprons / uniforms returned in clean condition', 'status' => 'Pending'],
                ['category' => 'Administration', 'item' => 'Petty cash and daily till cash verified with supervisor', 'status' => 'Pending'],
            ];
        }

        if ($industry === 'corporate') {
            return [
                ['category' => 'Assets & Devices', 'item' => 'Company laptop / workstation & accessories returned', 'status' => 'Pending'],
                ['category' => 'Assets & Devices', 'item' => 'Company SIM card / mobile handset returned', 'status' => 'Pending'],
                ['category' => 'Office Security', 'item' => 'Biometric registration removed & RFID identity badge returned', 'status' => 'Pending'],
                ['category' => 'Office Security', 'item' => 'Pedestal, desk & cabin keys handed over to Admin', 'status' => 'Pending'],
                ['category' => 'IT & Software', 'item' => 'Official email & HRMS login suspended', 'status' => 'Pending'],
                ['category' => 'Operations', 'item' => 'Departmental files & physical document binders handed over', 'status' => 'Pending'],
            ];
        }

        // Default: Software / IT company
        return [
            ['category' => 'IT & Hardware', 'item' => 'Company laptop / desktop computer returned with original charger & accessories', 'status' => 'Pending'],
            ['category' => 'Accounts & Email', 'item' => 'Official email (Google Workspace / M365) suspended & mail archive completed', 'status' => 'Pending'],
            ['category' => 'Code Repositories', 'item' => 'Code repository seats & SSH keys revoked (GitHub, GitLab, Bitbucket)', 'status' => 'Pending'],
            ['category' => 'Cloud & Infrastructure', 'item' => 'Cloud consoles, VPS, AWS/Azure/GCP, DigitalOcean & server root logins revoked', 'status' => 'Pending'],
            ['category' => 'Credentials & APIs', 'item' => 'Password vaults (1Password/Bitwarden) & API accounts transferred/revoked', 'status' => 'Pending'],
            ['category' => 'Code & Data Security', 'item' => 'Local development source code & database dumps purged from personal devices', 'status' => 'Pending'],
            ['category' => 'Network & VPN', 'item' => 'VPN, SSH keys, remote desktop & staging environment credentials revoked', 'status' => 'Pending'],
            ['category' => 'Handover & Knowledge', 'item' => 'Pending sprint tasks, tickets & documentation handed over to lead', 'status' => 'Pending'],
        ];
    }

    /**
     * Default legal declaration & undertaking text
     */
    public static function defaultDeclarationText(): string
    {
        return "I confirm that I have completed the required handover and returned all company property, documents, data, and information in my possession. I further confirm that, except for the amount stated as payable in this settlement, I have no further financial or employment-related claims against the Company, subject to applicable law and the terms of my employment agreement.\n\nI agree that, for a period of three (3) months after my last working day, I will remain reasonably available to provide necessary assistance to the Company, if required. I further undertake that I will not copy, reproduce, misuse, replicate, or develop any Company product, software, code, design, confidential information, or proprietary material for personal or third-party use, either directly or indirectly, after resignation from my employment.";
    }

    /**
     * Get the active declaration text or fallback to default
     */
    public function getDeclarationText(): string
    {
        return !empty(trim($this->declaration_text ?? '')) ? $this->declaration_text : self::defaultDeclarationText();
    }

    /**
     * Default policy & rules confirmation statement
     */
    public static function defaultPolicyRulesText(): string
    {
        return "I confirm that I have reviewed the applicable company policy and exit rules, and agree to abide by all post-employment terms.";
    }

    /**
     * Get the active policy rules text or fallback to default
     */
    public function getPolicyRulesText(): string
    {
        return !empty(trim($this->policy_rules_text ?? '')) ? $this->policy_rules_text : self::defaultPolicyRulesText();
    }

    /**
     * Get public URL for uploaded settlement attachment
     */
    public static function getAttachmentUrl(?string $fileName): string
    {
        if (empty($fileName)) {
            return '';
        }
        return Utility::get_file('settlement_attachments/' . $fileName);
    }
}