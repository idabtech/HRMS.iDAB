# Remote Frontend Developer (React.js) - Exit & Clearance Checklist
### (Remote Work & BYOD: Developer Using Own Laptop / Computer)

This document provides a clean, consolidated exit and clearance checklist tailored specifically for **Remote Frontend Developers (React.js / Web)** who work from home/remotely and use **their own personal laptop or computer (Bring Your Own Device - BYOD)**.

> [!TIP]
> **Optimized for HR & Managers**: Every category in this checklist is **unique (1 item per category)**. HR/Managers can quickly copy each row directly into **Section 3 (Departmental & Asset Clearances Checklist)** without managing repetitive or duplicate categories.

---

## 1. Quick Copy-Paste Form Matrix (Section 3 Checklist)

*Each row below represents a distinct department / domain category for clean, one-click entry in the HRMS form:*

| # | Category | Checklist Item Name | Recommended Type | Verification & Handover Requirement (Paste into Instructions / Remarks) |
| :-: | :--- | :--- | :-: | :--- |
| **01** | `Code & Version Control` | **GitHub / GitLab Org Access Revoked & All React Branches / PRs Pushed** | **Mandatory** | IT revokes developer from GitHub/GitLab org, teams, private repos, PATs, and SSH keys. Developer pushes all pending React branches/stashes and shares PR links for handover. |
| **02** | `Security & BYOD Data Purge`| **Local Source Code, `.env` Secrets, Builds & NPM Tokens Purged from Personal Laptop** | **Mandatory** | Developer permanently deletes all cloned company repositories, `.env*` files, local build outputs (`dist/`, `build/`), and removes private `@company` npm tokens from personal laptop; signs BYOD purge declaration. |
| **03** | `Hosting & Cloud Deployments`| **Vercel / Netlify / Cloudflare Dashboard & Server SSH Keys Revoked** | **Mandatory** | Admin removes user seat from frontend deployment platforms (Vercel/Netlify/Firebase) and DevOps removes public SSH keys from server `authorized_keys` / bastions. |
| **04** | `Testing & QA Dashboards` | **Staging Admin Portals, Headless CMS & Monitoring Consoles Revoked** | **Mandatory** | Invalidate accounts on internal staging dashboards, headless CMS (Strapi/Sanity/Contentful), Postman workspaces, and error logging consoles (Sentry, PostHog, GA). |
| **05** | `UI/UX & Design Assets` | **Figma / Adobe XD Workspace Access & Design System Seats Revoked** | **Mandatory** | Downgrade or remove developer seat from company Figma team, client UI mockups, and shared design system tokens. |
| **06** | `Network & Remote Access` | **Company VPN, WireGuard / Tailscale & Zero-Trust Access Terminated** | **Mandatory** | IT deactivates user VPN profile, WireGuard/Tailscale tunnels, remote desktop credentials, and corporate network access. |
| **07** | `Corporate Accounts & Email` | **Google Workspace / M365 & Slack / Teams Terminated Across Personal Devices** | **Mandatory** | Admin triggers "Sign out of all sessions" to disconnect personal machine browsers/apps, changes password, revokes 2FA, and deactivates Slack/Teams accounts. |
| **08** | `Project Management` | **Jira / ClickUp / Linear Tasks Reassigned & Handover Note Documented** | **Mandatory** | Reassign all open sprint tasks, bugs, and PR reviews. Developer submits technical documentation or Loom video handover link. |
| **09** | `Hardware & Assets (BYOD)` | **Confirmation: Personal Laptop Used (Zero Company Hardware Retained)** | **Mandatory** | HR/IT verifies developer used personal laptop (BYOD). Confirms no company hardware, test phones, or tokens are held (or records return if any accessories were provided). |

---

## 2. Detailed Verification Guide for Remote & BYOD Setup

### 1. Code & Version Control
- **GitHub / GitLab / Bitbucket**:
  - Navigate to `Organization Settings` > `Members` > Remove Member.
  - Revoke Personal Access Tokens (PAT) and OAuth authorizations tied to the company organization.
  - Delete developer SSH keys registered in repository deploy keys.
- **Code Handover**:
  - Developer runs `git status` and pushes all local branches:
    ```bash
    git push origin <feature-branch-name>
    ```
  - Confirm no uncommitted code remains in local stashes (`git stash list`).
  - Developer inputs active PR links in their clearance note.

### 2. Security & BYOD Data Purge
Because the developer works on their personal laptop, physical drive wipes by IT are not possible. Enforce the following:
- **Local Directory Deletion**:
  - Developer permanently deletes all cloned project folders, including `node_modules/`, `src/`, and build artifacts (`dist/`, `build/`).
- **Environment Files & Secrets**:
  - Delete all `.env`, `.env.local`, `.env.development`, and `.env.production` files.
  - Invalidate and clear saved tokens from global `~/.npmrc` or `~/.yarnrc` config files.
  - Clear any proprietary API tokens saved in personal Postman / Insomnia collections.
- **Undertaking**:
  - Developer enters their confirmation note and signs the legally binding BYOD purge declaration.

### 3. Hosting & Cloud Deployments
- **Vercel / Netlify / Cloudflare Pages**:
  - Remove user from team dashboards under `Team Settings` > `Members`.
  - Invalidate local CLI sessions on their personal machine (`vercel logout`, `netlify logout`).
- **Server SSH Keys**:
  - Remove public SSH key from `/home/<user>/.ssh/authorized_keys` across staging and production VPS servers.

### 4. Testing & QA Dashboards
- **Staging / QA Dashboards**:
  - Revoke staff login credentials to staging portals and API testing consoles.
- **Headless CMS & Content Platforms**:
  - Invalidate user accounts on Strapi, Sanity, Contentful, WordPress, or Supabase.
- **Monitoring & Analytics Consoles**:
  - Remove developer seat from Sentry, LogRocket, Datadog, Hotjar, and Google Analytics.

### 5. UI/UX & Design Assets
- **Figma / Adobe XD**:
  - Remove editor seat from Figma workspace to prevent ongoing access to unreleased mockups, wireframes, and design system tokens.

### 6. Network & Remote Access
- **Company VPN & Zero-Trust**:
  - Immediately deactivate user VPN profile (OpenVPN, WireGuard, Cisco AnyConnect).
  - Invalidate corporate Zero-Trust profiles (Cloudflare Access, Tailscale, AWS Client VPN).

### 7. Corporate Accounts & Email
- **Google Workspace / Microsoft 365**:
  - In Google Admin or M365 Admin, trigger **"Sign out of all sessions"** (disconnects personal browsers and desktop mail clients).
  - Transfer Google Drive files and documents to the Tech Lead or Manager.
  - Change password, revoke 2FA recovery methods, and suspend account.
- **Slack / Microsoft Teams**:
  - Deactivate user seat; terminate active desktop/mobile app sessions.

### 8. Project Management & Handover
- **Jira / ClickUp / Linear**:
  - Reassign open sprint tickets, backlog items, and code review duties to peer developers.
  - Developer shares links to Confluence/Notion documentation or Loom walkthrough videos.

### 9. Hardware & Assets (BYOD Status)
- **Personal Device Confirmation**:
  - Formally document in HR records that the employee used personal hardware (BYOD).
  - Confirm no company laptop was issued, financed, or retained.
- **Secondary Peripherals / Accessories Check**:
  - Verify if any peripheral (test phone for responsive testing, hardware token, headset) was ever shipped.
  - If zero assets were issued, mark as **"N/A - BYOD Only"**. If accessories were provided, require tracking slip of return courier.

---

## 3. Recommended Section 4 Custom Questions (In-Section Form Builder)

When configuring the settlement in the HRMS, click **"Add Question to this Section"** under Section 4 to include these interactive fields tailored for a **Remote & BYOD Frontend Developer**:

| Field Title | Input Type | Who Fills This? | Required? | Purpose |
| :--- | :---: | :---: | :---: | :--- |
| **Personal Email & Phone for F&F Documents** | Short Text | Employee | **Yes** | Contact information for sending final settlement payslip, Form 16 / tax forms, and service certificate. |
| **Active PR Links & Code Handover Details** | Textarea | Employee | **Yes** | Developer lists open GitHub/GitLab PR links, handover documentation URLs, and Loom recording links. |
| **Declaration of Code & .env Purge from Personal Laptop (BYOD)** | Dropdown (`Confirmed: All company code, repos, .env files and local builds permanently deleted from my personal computer`, `Pending Deletion`) | Employee | **Yes** | Mandatory legal undertaking confirming no source code or confidential secrets remain on personal hardware. |
| **Company Accounts & Remote Tools Disconnected** | Dropdown (`Confirmed Disconnected: Signed out of all company portals, VPNs, and Slack/Teams`, `In Progress`) | Employee | **Yes** | Confirmation that developer logged out and removed local profiles/tokens from personal machine. |
| **Company Hardware Status (BYOD Confirmation)** | Dropdown (`Confirmed: I used my own personal laptop and possess zero company-owned hardware or accessories`, `I have company accessories to courier`) | Employee | **Yes** | Explicit confirmation that no company physical assets are held or need to be recovered. |
| **Accessory Courier Return Receipt (If Company Accessories Issued)** | File Upload | Employee | Optional | Upload tracking slip if any test device, monitor, or hardware accessory was couriered back. |

---

## 4. How to Apply This in the HRMS

1. Go to **Full & Final Settlement** > **Create Settlement**.
2. Select the remote frontend developer and set their **Last Working Day** date.
3. Under **3. Departmental & Asset Clearances Checklist**:
   - Add the **9 distinct category rows** from the table in Section 1.
   - For each row, enter the Category name, Item title, and paste the Requirement into the Remarks/Instructions field.
   - Turn on the **"Mandatory for Employee to complete"** toggle for items requiring employee confirmation.
4. Under **4. Custom Undertakings & Questions**:
   - Add the questions from Section 3 above using the in-section question builder.
   - Mark the **BYOD Code Purge Declaration** and **Hardware Status** questions as **Required**.
5. Save the settlement and click **"Copy Clearance Link"** or **"Send Clearance Link"** to email it to the remote developer.
6. The remote developer completes each mandatory checkpoint with their handover notes (attachment optional), answers the custom questions, signs digitally, and submits.
