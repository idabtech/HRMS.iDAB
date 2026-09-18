# HRMS UK NI & HMRC PAYE RTI (MTD) Implementation Review & Production Readiness

## Executive Summary

This document provides a comprehensive technical audit of the **UK National Insurance (NI)**, **HMRC Real Time Information (RTI / Full Payment Submission)**, and **UK Payslip** implementations in the HRMS application.

* **Current State:** ~85% complete. Core HMRC services, NINO format verification, FPS payload building, UK tax-year YTD calculations, and UK payslip templates are fully implemented.
* **Production Status:** Not yet in production. A few critical UI comments, missing bulk payment hooks, and employee onboarding fields need to be completed before go-live.
* **Estimated Time to Production:** **3 to 4 hours** (less than 1 day) for all critical and recommended fixes + sandbox verification.

---

## 1. Feature Audit & Current Status

| Feature / Component | File / Location | Status | Details |
|---|---|---|---|
| **HMRC API Service** | `app/Services/HmrcService.php` | ✅ Completed | OAuth2 Client Credentials grant, token caching (4h), connectivity test (`/hello/application`), NINO format validation. |
| **HMRC RTI Service (FPS)** | `app/Services/HmrcRtiService.php` | ✅ Completed | Full Payment Submission (FPS) JSON payload builder (tax month, tax year, NI categories, taxable gross, net, student loan, pension, YTD values). |
| **Audit Trail Table** | `database/migrations/2026_07_10_create_hmrc_rti_submissions_table.php` | ✅ Ready to Migrate | Stores FPS logs (`submitted`, `rejected`, `failed`, `reference`, `message`, timestamps). |
| **UK YTD Calculation Engine** | `app/Models/Utility.php` (`employeePayslipDetail`) | ✅ Completed | Calculates tax year (April 6 – April 5) accumulations for Gross, PAYE Income Tax, Employee NI, Employer NI, Statutory Pay, and Pensions. |
| **UK Payslip Templates** | `resources/views/payslip/ukpdf.blade.php`<br>`resources/views/payslip/ukPayslipDownload.blade.php` | ✅ Completed | Full UK compliant payslip layout with employee details, NI number, tax code, earnings/deductions, and YTD summary table. |
| **Company PAYE Settings** | `resources/views/setting/company_settings.blade.php` | ✅ Completed | Company-level form for Employer PAYE Ref, Accounts Office Ref, and Test Connection button. |
| **Super Admin HMRC Settings** | `resources/views/setting/system_settings.blade.php` | ⚠️ **Commented Out** | Settings tab & form are commented out in Blade (`{{-- ... --}}`). |
| **Single Payslip RTI Submission** | `app/Http/Controllers/PaySlipController.php` (`paysalary`) | ✅ Completed | Dispatches FPS to HMRC when payslip is marked as paid. |
| **Bulk Payslip RTI Submission** | `app/Http/Controllers/PaySlipController.php` (`bulkpayment`) | ❌ **Missing** | Bulk payment updates payslip status but does not call `HmrcRtiService::submitFps()`. |
| **Employee Form NI Inputs** | `resources/views/employee/create.blade.php`<br>`resources/views/employee/edit.blade.php` | ⚠️ **Missing on Employee form** | Only accessible under `setsalary/edit.blade.php`. Missing during standard employee creation. |

---

## 2. Action Plan: Required Changes for Production

### Phase 1: Critical Fixes (Must Do)

#### 1. Enable Super Admin HMRC Settings UI
* **File:** `resources/views/setting/system_settings.blade.php`
* **Action:** Uncomment lines `409-412` (sidebar tab) and lines `4313-4401` (form container).
* **Rationale:** Allows Super Admin to configure `hmrc_client_id`, `hmrc_client_secret`, `hmrc_server_token`, and toggle `hmrc_enabled`.

#### 2. Connect HMRC FPS to Bulk Payment
* **File:** `app/Http/Controllers/PaySlipController.php`
* **Action:** In `bulkpayment()`, iterate through paid employees and trigger `HmrcRtiService::submitFps()` for UK employees having an NI number.
* **Rationale:** Ensures parity with single payslip payments so bulk-paid employees have their RTI FPS submitted.

#### 3. Run Database Migrations
* **Action:** Execute `php artisan migrate` on production to ensure `hmrc_rti_submissions` table is created.

---

### Phase 2: Recommended Workflow Enhancements

#### 4. Add NI Number & NI Category Letter to Employee Onboarding
* **Files:**
  * `resources/views/employee/create.blade.php`
  * `resources/views/employee/edit.blade.php`
  * `app/Http/Controllers/EmployeeController.php`
* **Action:** Add `ni_number` and `ni_table_letter` fields to the employee create/edit forms and save them in `store()` / `update()`.

#### 5. Include NI Number in Excel Exports
* **Files:**
  * `app/Exports/PayslipExport.php`
  * `app/Exports/PayrollExport.php`
* **Action:** Add `ni_number` and `ni_table_letter` columns to Excel exports.

#### 6. Simplify HMRC Button Condition on Payslip Table
* **File:** `resources/views/payslip/index.blade.php`
* **Action:** Change `@if ((\Auth::user()->type == 'company' || \Auth::user()->type == 'hr') && (\App\Models\Utility::isUkRequest() || request()->boolean('uk_preview')) && \App\Services\HmrcService::isEnabled())` to `@if ((\Auth::user()->type == 'company' || \Auth::user()->type == 'hr') && \App\Services\HmrcService::isEnabled())`.

---

## 3. Timeline & Effort Breakdown

| # | Task | Category | Estimated Time |
|---|---|---|---|
| 1 | Uncomment & test Super Admin HMRC Settings UI | Critical Fix | 15 mins |
| 2 | Implement HMRC FPS submission inside `bulkpayment()` | Critical Fix | 30 mins |
| 3 | Add `ni_number` / `ni_table_letter` to Employee Create & Edit forms & controller | Recommended | 45 mins |
| 4 | Add NI fields to Excel Exports (`PayslipExport`, `PayrollExport`) | Recommended | 20 mins |
| 5 | Clean up UI conditions in `payslip/index.blade.php` | Recommended | 15 mins |
| 6 | Run DB migration & verify sandbox connection / test submission | Testing & Verification | 1–1.5 hours |
| **Total** | **Complete Production Readiness** | | **~3 to 4 Hours** |

---

## 4. Notes on UK Tax & MTD Terminology

* **PAYE RTI vs MTD**: In HMRC terminology, payroll submissions are officially referred to as **PAYE RTI (Real Time Information)** via FPS (Full Payment Submission) and EPS (Employer Payment Summary). **MTD (Making Tax Digital)** applies to VAT and Income Tax Self Assessment (ITSA). The current codebase correctly implements **PAYE RTI (FPS)** for employee payroll.
* **Automatic Tax Band Calculation**: The current system calculates tax and NI deductions from configured saturation deduction rows. If in the future you require a fully automated tax engine (auto-computing 20%/40%/45% tax bands and Class 1 NI thresholds from tax code `1257L` and Category `A`), that can be added as an optional Phase 3 feature (est. 2–3 days).
