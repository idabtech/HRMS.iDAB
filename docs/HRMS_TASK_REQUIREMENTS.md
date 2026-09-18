# HRMS SaaS Development Tasks & Feature Specifications

**Product / Architecture:** Multi-Tenant SaaS HRMS Platform  
**Target Industries:** Software Development Companies, IT Services, Salons, Retail, Hospitality, Professional Services, etc.  
**Immediate Priority Client:** Software Development Company (Exit Case: Milan)  
**Author / Dev:** Pratik iDAB  
**Stakeholder / HR Lead:** Jyoti Hirwani  
**Date:** 2026-09-17  
**Status:** In Progress / High Priority  

---

## 🏢 Executive Summary: SaaS & Multi-Tenant Vision

The HRMS platform is designed as a **multi-tenant SaaS application**. Different subscriber companies operate across diverse industries with distinct clearance protocols:
1. **Software / Tech Companies:** Heavy focus on source code handovers, repository access (GitHub/GitLab), cloud infrastructure (AWS/Azure/GCP), API keys, server credentials, database passwords, laptop returns, and remote data deletion.
2. **Salons / Spas:** Clearance involves workstation keys, product kits/inventories, POS till accounts, customer appointments/client registers, uniforms, and cash register handovers.
3. **Retail & Hospitality / General Business:** Focus on physical keys, POS access, store stock, identity cards, petty cash, and uniforms.

To satisfy all subscriber profiles while directly solving today's urgent software company requirement, **Task 1 (Full & Final Settlement Form)** and **Task 2 (Exit Checklist)** are structured as **industry-customizable, template-driven modules**.

---

## 📊 Feature Priority Matrix

| # | Feature / Module | Scope | SaaS Customization Level | Priority | Target Timeline |
|---|---|---|---|---|---|
| **1** | **Full & Final (F&F) Settlement Form** | Employee Offboarding & Financial Settlement | **High** (Dynamic sections + industry-specific clearance items) | 🔴 **CRITICAL (Urgent for Tech Company Client)** | **Tomorrow** |
| **2** | **Employee Exit & Asset Checklist** | Multi-Department Sign-off (HR, IT, Finance, Admin) | **High** (Custom category builder for IT, Operations, Tools) | 🟠 **High** | **Tomorrow** |
| **3** | **Offer Letter & Appointment Letter Module** | Onboarding & Document Generation | **High** (Multi-variant templates, customizable placeholders) | 🟡 **Medium** | **This Sprint** |

---

## 🔴 TASK 1 — SaaS-Ready Full & Final (F&F) Settlement Form

### 1. Architectural Architecture & SaaS Customization
- **Tenant-Level Configuration:** Each company can define custom financial heads, clearance line items, and approval workflows.
- **Industry Preset Packs:**
  - *Software / IT Pack (Default Enabled):* Source code, GitHub, Server/Cloud, Credentials, API Keys, Workstation/Laptop.
  - *Service / Salon Pack:* Uniform, Locker Key, Product Inventory, Till Login, Client Book.
  - *Corporate / General Pack:* ID Card, Access Card, Cabin Keys, Company Car/Fuel Card.
- **Role-Based Security & Form Partitioning:**
  - **HR / Finance Area (LOCKED):** Salary breakdown, deductions, recoveries, settlement amount. Non-editable by the employee.
  - **Company / Management Area (LOCKED):** Departmental sign-offs, payment date, transaction reference number. Non-editable by the employee.
  - **Employee Area (INTERACTIVE / WRITABLE):** Employee acknowledgement, reason confirmation, feedback, and digital signature pad.

---

### 2. Full & Final Form Breakdown

#### Section A — Employee & Company Information (Header)
*Auto-populated from employee profile; verified/locked by HR.*

| Field | Source | Editable By Employee |
|---|---|---|
| Company / Tenant Name | Tenant Profile (e.g., Karma Mark Start Consultancy LLP) | 🔒 Locked |
| Employee Name | Employee Record | 🔒 Locked |
| Employee ID | Employee Record | 🔒 Locked |
| Designation & Department | Employee Record | 🔒 Locked |
| Date of Joining (DOJ) | Employee Record | 🔒 Locked |
| Last Working Day (LWD) | Relieving / Termination Record | 🔒 Locked |
| Reason for Separation | Resignation / End of Contract / Termination | 🔒 Locked |

---

#### Section B — Financial Clearance (Customizable Line Items)
*HR/Finance enters financial numbers. Formulas auto-calculate Gross, Deductions, and Net Settlement.*

| Particulars | Category | SaaS Configurable? | Amount (₹) |
|---|---|---|---|
| Basic & Allowances Payable up to LWD | Earnings | Yes | Auto / Manual |
| Leave Encashment (Earned / Privilege Leaves) | Earnings | Yes | Auto / Manual |
| Incentives / Commission / Overtime | Earnings | Yes | Manual |
| Bonus / Gratuity / Other Payable | Earnings | Yes | Manual |
| **Gross Payable Amount (A)** | **Subtotal** | **Calculated** | **[Auto Sum]** |
| Notice Period Shortfall Recovery | Deductions | Yes | Manual |
| Advance / Personal Loan Balance Recovery | Deductions | Yes | Auto / Manual |
| Asset Damage / Unreturned Equipment Recovery | Deductions | Yes (IT/Admin) | Manual |
| Tax Deductions (TDS) / Professional Tax | Deductions | Yes | Manual |
| Other Authorized Deductions | Deductions | Yes | Manual |
| **Total Deductions (B)** | **Subtotal** | **Calculated** | **[Auto Sum]** |
| **NET FINAL SETTLEMENT AMOUNT (A - B)** | **Final** | **Formula** | **₹ ____________** |

---

#### Section C — Departmental Clearance Matrix (Software / IT Specialized)
*Multi-department checkboxes. Statuses: `[Not Applicable]` `[Returned / Cleared]` `[Pending]`.*

##### 💻 C1. IT & Infrastructure Clearance (Software Company Specific):
1. Company laptop / desktop computer returned with original charger & accessories.
2. Official email ID (Google Workspace / Microsoft 365) suspended & data backed up.
3. Code repositories & version control access revoked (GitHub, GitLab, Bitbucket).
4. Cloud infrastructure, server consoles & database logins revoked (AWS, GCP, Azure, DigitalOcean, cPanel).
5. Central password vaults & API accounts transferred/cleared (1Password, Bitwarden, Postman, Stripe, Hubs).
6. Local development source code deleted from personal/remote systems.
7. VPN, SSH keys, remote desktop & staging environment credentials revoked.

##### 📋 C2. Operational & Admin Clearance:
1. Pending project tasks, deliverables & open tickets handed over to reporting lead.
2. Documentation, client meeting notes & knowledge-transfer (KT) sessions completed.
3. Company access card, biometric registration, office keys returned.
4. Corporate SIM, mobile device, or external storage drives returned.

---

#### Section D — Employee Undertaking & Digital Signature
*The ONLY interactive part open for employee completion.*

> **Pre-set Legal Undertaking (Customizable per Tenant Policy):**  
> "I confirm that I have completed the required handover and returned all company property, software code, credentials, documents, data, and information in my possession. I confirm that, except for the amount stated as payable in this settlement, I have no further financial or employment-related claims against the Company.  
> For software development roles: I explicitly confirm that no source code, database dumps, application designs, API keys, or confidential trade materials have been retained on any personal drives, cloud accounts, or hardware. I undertake not to copy, replicate, or misuse any proprietary material post-separation."

| Field Name | Input Type | Required |
|---|---|---|
| Employee Confirmation Checkbox | Boolean Checkbox | Yes |
| Personal Email & Phone for Future Tax Forms | Text / Email | Yes |
| Digital Signature | Canvas Signature Pad / Upload | Yes |
| Signature Date & Timestamp | Auto-generated UTC/IST | Yes |

---

#### Section E — Management & HR Approvals
*Locked approval steps required to officially close the offboarding ticket.*

1. **HR Executive Review:** Name, Clearance Status (`[Cleared]` / `[Pending]`), Signature, Date.
2. **Finance / Accounts Sign-off:** Payment Mode (`NEFT` / `RTGS` / `Cheque`), Transaction Reference / UTR No., Disbursal Date.
3. **Authorized Company Signatory:** Managing Partner / Director approval & stamp.

---

## 🟠 TASK 2 — SaaS Employee Exit & Asset Checklist

### Purpose & Structure
The Exit Checklist serves as the operational prerequisite before generating the final settlement figures. It is divided into customizable functional silos:

### 1. Work & Project Handover (Operational Lead / Reporting Manager)
- [ ] Assigned sprint tasks, backlog items, or client tickets updated.
- [ ] Active code branches pushed, pull requests reviewed, and staging builds handed over.
- [ ] Architecture documentation, user guides, and system designs stored in company wiki/Notion.
- [ ] Client contact details and relationship history handed over to successor.
- [ ] Formal Knowledge Transfer (KT) sessions conducted with designated team members.

### 2. Company Data, Code & IP Protection (IT / Compliance)
- [ ] Company-owned source code verified in official repositories.
- [ ] Confirmation that no code repositories exist on unmonitored personal devices.
- [ ] No company data remains on personal cloud drives (Google Drive, Dropbox, OneDrive).
- [ ] NDA, non-compete, and intellectual property assignment covenants reaffirmed.

### 3. IT Access, Credentials & Account Revocation (IT Administrator)
- [ ] Single Sign-On (SSO) / Google Workspace / Microsoft 365 disabled.
- [ ] Git repository seats (GitHub/GitLab) removed.
- [ ] Server SSH keys, server root access, and Bastion access revoked.
- [ ] CI/CD pipeline access (Jenkins, GitHub Actions) and deployment privileges deleted.
- [ ] Third-party SaaS tools revoked (Slack, Jira, Trello, Asana, Notion, Figma).
- [ ] Production databases, staging databases, and Redis/cache access disconnected.
- [ ] Domain registrars, Cloudflare, payment gateways, and API hub access transferred.

### 4. Company Physical Property & Assets (Admin / IT Asset Manager)
- [ ] Company laptop (Make/Model/Serial No.) inspected for physical damage and returned.
- [ ] Laptop charger, mouse, bag, and hardware adapters returned.
- [ ] Mobile testing devices / tablets returned.
- [ ] Physical office access badge / keys handed back.
- [ ] *Remote Work Confirmation (If BYOD/No Asset Issued):* Verified that no physical assets were provided.

---

## 🟡 TASK 3 — Offer Letter & Appointment Letter Module

### Core Requirements
1. **Dynamic Template Builder:** Rich text editor supporting template tags (`{{candidate_name}}`, `{{designation}}`, `{{ctc}}`, `{{joining_date}}`, `{{company_name}}`, `{{signatory_name}}`).
2. **Template Library / Variants:** Allows companies to maintain multiple presets:
   - Senior Developer / Tech Lead Offer Template (includes IP & Invention Assignment clauses).
   - Junior Developer / Intern Offer Template (includes training bond/stipend terms).
   - Executive / Sales Offer Template (includes commission structure).
   - Salon Stylist / Technician Template (service commission terms).
3. **In-System Customization:** HR can preview and make one-off edits to an individual candidate's offer letter before dispatch.
4. **Automated Dispatch & Digital Acceptance:** Direct email dispatch with an online review link. The candidate can electronically accept/sign.
5. **Secure Cloud Archive:** The signed offer letter is automatically filed under the candidate-turned-employee's permanent document vault.

---

## 🚀 SaaS Technical Implementation Plan (For Pratik)

### Database Architecture
1. **`tenant_exit_templates`**: Stores customizable section configs and checklist items per company.
2. **`exit_settlements`**: Stores master settlement record (employee_id, status, financial_breakdown_json, clearance_status_json, payment_info).
3. **`exit_signatures`**: Stores digital signature image paths, signer IP address, timestamp, and audit trail.
4. **`offer_letter_templates`**: Stores reusable letter layouts, headers, footers, and tokens.

### Security & Compliance Controls
- **HMAC / Tokenized Public Links:** When sending forms to separating employees or incoming candidates, links use secure single-use signed URLs with expiration.
- **Form Immutability:** Once the employee signs Section D, the entire document freezes into a finalized snapshot. No further line item modifications can occur without voiding the signature.
- **PDF Generation:** Export to crisp, branded PDF using company branding, logo, and authorized stamp.

---

## 📅 Immediate Action Items

| Owner | Action | Target Date |
|---|---|---|
| **Jyoti (HR)** | Review financial heads and approve standard IT clearance points for Milan | Today |
| **Pratik (Dev)** | Implement Section A, B, C, D in HRMS with locked/unlocked zones & digital signature | Tomorrow Morning |
| **Pratik (Dev)** | Test PDF generation and digital signature capture flow | Tomorrow Afternoon |
| **Jyoti / Pratik** | Final dry run with Milan's settlement details | Tomorrow EOD |
