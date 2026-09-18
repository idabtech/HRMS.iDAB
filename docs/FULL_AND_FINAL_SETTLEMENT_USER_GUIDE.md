# Full & Final Settlement & Digital Clearance Guide

A comprehensive, non-technical guide explaining how the Full & Final Settlement (FnF) and Departmental Clearance system works for HR Administrators and Departing Employees/Customers.

---

## 1. Overview & Purpose

The **Full & Final Settlement & Clearance** module provides a modern, paperless, and legally compliant way to manage employee offboarding. 

It handles four essential requirements in one seamless workflow:
1. **Financial Settlement Calculation**: Accurately accounts for gross earnings, notice adjustments, and deductions to calculate the net final payable amount.
2. **Departmental & Asset Handover**: Tracks equipment returns, credentials revocation, and clearance status across IT, Admin, Accounts, and Operations.
3. **Interactive Employee Clearance**: Enables the employee to verify items, write handover notes or serial numbers, and check off completed responsibilities online.
4. **Digital Sign-Off**: Captures legally valid digital signatures (drawn or uploaded) along with date, time, and IP verification.

---

## 2. Complete End-to-End Workflow

```
[ HR / Admin ]
      │
      ├─► 1. Selects Employee & Enters Separation Details
      ├─► 2. Sets Up Earnings & Deductions (Auto-calculates Net Payable)
      ├─► 3. Customizes Departmental & Asset Checklist Checkpoints
      ├─► 4. Adds Any Custom Questions (Targeted to HR or Employee)
      └─► 5. Saves Settlement Record
            │
            ▼
[ Share Link Generated ]
      │
      ├─► Email Notification sent automatically to Employee
      └─► Clean, secure link available to copy and share directly
            │
            ▼
[ Employee / Customer ]
      │
      ├─► 1. Opens Secure Link (No login required)
      ├─► 2. Reviews Personal & Financial Statement
      ├─► 3. Checks Off Handed-Over Items & Adds Notes / Serial Numbers
      ├─► 4. Real-time Progress Bar & Departmental Badges update live
      ├─► 5. Fills Any Additional Information Questions
      ├─► 6. Accepts Legal Declaration & Undertaking
      ├─► 7. Draws Digital Signature OR Uploads Signature Image
      └─► 8. Submits the Clearance Form
            │
            ▼
[ Settlement Confirmed & Locked ]
      │
      ├─► Employee immediately sees verified Certificate confirmation
      ├─► HR Admin reviews submitted notes, status, and signature
      └─► Downloadable PDF generated for company archives and accounting
```

---

## 3. How HR / Admin Creates the Settlement Form

### Step 1: Employee & Separation Details
- Navigate to **Full & Final Settlement** in the admin dashboard and click **Create Settlement**.
- Select the employee from the dropdown. Their Employee ID, Designation, Department, and Date of Joining load automatically.
- Enter the **Last Working Day (LWD)** and the **Reason for Separation** (e.g., *Resignation Accepted*, *Contract Completed*, *Mutual Release*).

### Step 2: Financial Clearance Breakdown
- **Earnings & Payables (A)**:
  - Default rows include basic salary payable up to the last working day, earned leave encashment, gratuity, and performance bonuses.
  - Add custom earning rows anytime by clicking **Add Earning**.
- **Deductions & Recoveries (B)**:
  - Deductions include notice period shortfall recovery, loan/advance balances, and asset damage recovery.
  - Add custom deduction rows anytime by clicking **Add Deduction**.
- **Net Final Payable Amount (A - B)**:
  - Calculates automatically in real time as you adjust numbers.

### Step 3: Departmental & Asset Clearances Checklist
- Build and customize checklist checkpoints for your organization:
  - **Category**: Name your departments freely (e.g., *IT & Hardware*, *Cloud & Code Repositories*, *Finance & Accounts*, *Admin & Facility*, *Operations*, etc.).
  - **Checklist Item**: Write the specific item or responsibility (e.g., *Company laptop and charger returned*, *Official email suspended*, *Locker keys returned*).
  - **Add Checkpoints**: Click **Add Checkpoint** to add as many custom items as needed.
  - **Remove Checkpoints**: Delete unnecessary items using the trash button.

### Step 4: Adding Custom Questions (Optional)
- If you need extra records, you can click **Add Question to this Section** on any section:
  - Set the question title (e.g., *Forwarding Email Address*, *Laptop Serial Number*, *Notice Shortfall Waiver Notes*).
  - Select input type (Short Text, Paragraph, Dropdown, Date, Number).
  - Choose who fills it:
    - **HR Certified**: Filled out internally by HR during creation.
    - **Employee to Fill Online**: Presented directly to the employee when they open their clearance link.

### Step 5: Save Record
- Click **Save Full & Final Settlement**. The record is saved as a draft with a unique reference number (e.g., `FNF-202609-0001`).

---

## 4. How the Form is Shared with the Employee

- **Clean & Professional URL**:
  - Each settlement generates a secure, private token link without exposing internal IDs or words like "public".
- **Email Sharing**:
  - Click **Send to Employee** on the settlement details page.
  - The system dispatches an official email containing the employee's name, settlement reference number, calculated net amount, and a direct button to access their clearance form.
- **Direct Link Copying**:
  - HR can also copy the secure link directly from the dashboard and send it via Slack, WhatsApp, or official correspondence.

---

## 5. How the Employee / Customer Fills and Signs the Form

When the employee opens the secure link on a desktop, tablet, or smartphone:

### Step 1: Document Review
- The employee reviews the executive clearance document header with company details and official reference numbers.
- They inspect the itemized financial statement showing earnings, deductions, and the net payable amount.

### Step 2: Interactive Departmental Checklist
- **Check Completed Items**:
  - For each checkpoint (e.g., *Company laptop returned with original charger*), the employee checks the box when complete.
  - As soon as an item is checked, its status badge immediately flips from **Pending Clearance** (orange) to **Completed / Handed Over** (green), and the row turns soft green.
- **Add Comments / Serial Numbers**:
  - Under each checkpoint, an input field allows the employee to type specific details (e.g., *Serial #C02G1234, returned to IT desk on 15th Sep*).
- **Live Progress Feedback**:
  - Each department category counter (e.g., `1 / 1 Cleared`) updates in real time.
  - When all items in a department are checked, the folder icon changes to a green checkmark.
  - The top progress bar dynamically animates from `0%` toward `100%`.

### Step 3: Additional Employee Information Questions
- If HR configured custom employee questions, they appear in a neat information card.
- The employee enters the requested answers (such as personal email address, forwarding contact number, or handover repository links).

### Step 4: Formal Declaration & Undertaking
- The employee reads the clear legal undertaking confirming that all equipment, software access, and proprietary property have been returned, and confidentiality terms are acknowledged.
- They check the mandatory confirmation box: *"I have read, understood, and agree to the declaration and undertaking terms above."*

### Step 5: General Comments / Correspondence Notes (Optional)
- The employee can write overall handover comments or future contact information in the remarks field.

### Step 6: Digital Signature (Dual Mode)
The employee has two flexible ways to provide their signature:
1. **Draw Signature**:
   - Draw directly on the screen using a finger on touch devices, a trackpad, or a mouse.
   - Click *Clear & Redraw* anytime if a correction is needed.
2. **Upload Signature Image**:
   - Switch to the *Upload Signature Image* tab.
   - Drag and drop or click to upload a photo/scan of their signature (PNG, JPG, or JPEG).
   - A preview displays immediately, with an option to remove and choose another if desired.

### Step 7: Submit
- The employee clicks **Sign & Submit Settlement**.
- The checklist answers, handover comments, custom field responses, and digital signature are encrypted and securely submitted.

---

## 6. What Happens After Submission

1. **Instant Confirmation for the Employee**:
   - The webpage refreshes into a verified **Settlement Signed & Confirmed** certificate view.
   - It displays their signature, recorded handover notes, submission date and time, and verified IP address.
   - The form is now locked to prevent any unauthorized tampering.

2. **HR Admin Dashboard Review**:
   - The status updates from `Sent` to `Signed`.
   - HR can open the settlement record to inspect every checkmark, employee comment, and digital signature.

3. **Official PDF Export**:
   - HR or management can click **Download PDF** at any time.
   - Generates an executive, print-ready document containing the complete breakdown, departmental checklist with employee notes, and digital sign-off suitable for audits and payroll records.
