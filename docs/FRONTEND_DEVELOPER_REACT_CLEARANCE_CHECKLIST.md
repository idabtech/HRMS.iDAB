# Remote Frontend Developer (React.js) - Exit & Clearance Checklist
### (Remote Work & BYOD: Developer Using Own Laptop / Computer)

This document provides an exit and clearance checklist tailored specifically for **Remote Frontend Developers (React.js / Web)** who work from home or remotely and use **their own personal laptop or computer (Bring Your Own Device - BYOD)**.

Because no physical company laptop needs to be returned, the clearance focus shifts decisively to **remote access de-provisioning, source code & `.env` secrets sanitization from personal storage, VPN/Zero-Trust disconnection, and digital handover**.

You can copy and paste these checkpoints directly into **Section 3 (Departmental & Asset Clearances Checklist)** and **Section 4 (Custom Undertakings & Questions)** of your HRMS Full & Final Settlement form.

---

## 1. Quick Copy-Paste Form Matrix (Section 3 Checklist)

| # | Category | Checklist Item Name | Recommended Type | Verification & Handover Requirement |
| :-: | :--- | :--- | :-: | :--- |
| **01** | `Code & Repositories` | **GitHub / GitLab / Bitbucket Org Access & SSH Keys Revoked** | **Mandatory** | Remove from company GitHub/GitLab org, teams, and private repositories. Revoke personal access tokens (PAT), deploy keys, and public SSH keys registered from personal machine. |
| **02** | `Code & Repositories` | **All Local React Branches, Stashes & Handover PRs Pushed** | **Mandatory** | Developer pushes all pending code branches, creates PRs for in-progress features, and shares PR links and stashes with Tech Lead. |
| **03** | `Security & BYOD Purge` | **Local Source Code, `.env` Secrets & Builds Purged from Personal Laptop** | **Mandatory** | Developer permanently deletes all cloned company repositories, `.env*` files, local build folders (`build/`, `dist/`), and database dumps from their personal computer; signs legal undertaking. |
| **04** | `Security & BYOD Purge` | **Private NPM Registry Tokens & Developer Credentials Removed** | **Mandatory** | Revoke developer's NPM authToken for scoped company packages (`@company/*`), FontAwesome Pro tokens, and invalidate `.npmrc` / `.yarnrc` tokens on developer's machine. |
| **05** | `Hosting & Deployments` | **Vercel / Netlify / Cloudflare / Firebase Team Access Revoked** | **Mandatory** | Remove user seat from Vercel/Netlify/Cloudflare teams, preview deployment access, and revoke CLI login tokens (`vercel login` / `netlify login`). |
| **06** | `Hosting & Deployments` | **Server SSH / VPS Access Keys Removed (`~/.ssh/authorized_keys`)** | **Mandatory** | SysAdmin/DevOps deletes personal machine's public SSH key from server `authorized_keys`, and revokes VPN/bastion access. |
| **07** | `Testing & Production` | **Staging / QA / Production Admin Dashboards & Postman Revoked** | **Mandatory** | Invalidate internal admin accounts, test user personas, Postman team workspace seats, and headless CMS logins (Strapi, Sanity, Contentful). |
| **08** | `Testing & Production` | **Frontend Monitoring & Analytics Consoles Removed (Sentry, PostHog, GA)** | Optional | Remove user access from Sentry, LogRocket, PostHog, Google Analytics, or Datadog consoles. |
| **09** | `UI/UX & Design Assets` | **Figma / Adobe XD / InVision Workspace Access Revoked** | **Mandatory** | Downgrade/remove developer seat from company Figma team and shared client UI design systems. |
| **10** | `Network & Remote Access`| **Company VPN / Tailscale / Cloudflare Zero-Trust / Remote Access Terminated** | **Mandatory** | IT deactivates user VPN profile, WireGuard/OpenVPN credentials, Zero-Trust network tunnels, and remote desktop credentials. |
| **11** | `Accounts & Email` | **Company Google Workspace / M365 Account Suspended & Remote Sessions Terminated** | **Mandatory** | IT admin resets password, executes "Sign out of all sessions" to disconnect personal browser/desktop sessions, revokes 2FA keys, and suspends account. |
| **12** | `Accounts & Email` | **Slack / Microsoft Teams / Discord Dev Channels Deactivated** | **Mandatory** | Deactivate user account, terminate active desktop/mobile app sessions, and remove from private client and internal channels. |
| **13** | `Project Management` | **Jira / ClickUp / Linear Tasks Reassigned & Handover Note Documented** | **Mandatory** | Reassign all open sprint tickets, backlog items, and review duties. Developer submits Loom recording or written handover document. |
| **14** | `Hardware (BYOD Status)`| **Confirmation: Personal Laptop Used (Zero Company Hardware Retained)** | **Mandatory** | HR/IT verifies employee used personal laptop (BYOD). Confirms no company hardware, test phones, or tokens are held (or records return if any accessories were provided). |

---

## 2. Detailed Verification Guide for Remote & BYOD Setup

### A. Source Code & Version Control
1. **GitHub / GitLab / Bitbucket**:
   - Navigate to `Organization Settings` > `Members` > Remove Member.
   - Inspect individual repository permissions to ensure the developer was not added directly as an outside collaborator.
   - Revoke Personal Access Tokens (PAT) generated for repository access.
   - Delete any deployment keys or developer SSH keys registered in repository settings.
2. **React Code Handover**:
   - Ensure the developer runs:
     ```bash
     git status
     git push origin <feature-branch-name>
     ```
   - Check that no work remains stranded in local git stashes (`git stash list`).
   - Require links to all active Pull Requests (PRs) assigned to the developer.

### B. BYOD Storage Sanitization & IP Security
Because the developer is working remotely on their personal computer, physical disk wipes by company IT are not possible. Enforce the following:
1. **Source Code Deletion**:
   - Developer must permanently delete all local repositories, clone folders, and build outputs (`dist/`, `build/`, `out/`).
2. **Environment & Secrets Sanitization**:
   - Developer must delete all `.env`, `.env.local`, `.env.development`, and `.env.production` files.
   - Remove saved API secrets from personal tools (e.g., Postman collections, Insomnia, Paw).
3. **Registry & Package Credentials**:
   - Developer must remove company tokens from their global `~/.npmrc` or `~/.yarnrc`:
     ```bash
     # Inspect and remove company npm tokens:
     cat ~/.npmrc
     ```
   - Rotate any client-side API keys that the developer had access to (Stripe publishable keys, Firebase configs, Google Maps API keys).
4. **Legal BYOD Undertaking**:
   - Require the developer to sign a binding declaration confirming the total removal of company code and proprietary data from their personal computer and external drives.

### C. Remote Infrastructure & Hosting
1. **Vercel / Netlify / Cloudflare Pages**:
   - Remove user from the team dashboard under `Team Settings` > `Members`.
   - Invalidate any local CLI credentials generated on their personal machine (`vercel logout`, `netlify logout`).
2. **Server & Bastion SSH Keys**:
   - Remove the developer's public key from `/home/<deploy_user>/.ssh/authorized_keys` on staging and production VPS servers.
3. **VPN & Zero-Trust Network Access**:
   - Immediately revoke company VPN certificates (OpenVPN, WireGuard, Cisco AnyConnect).
   - Invalidate corporate Zero-Trust profiles (Cloudflare Access, Tailscale, AWS Client VPN).

### D. Testing, CMS & Analytics Consoles
1. **Staging / QA Dashboards**:
   - Revoke developer and test user accounts on internal staging dashboards.
2. **Headless CMS & Content Platforms**:
   - Deactivate user seats on Strapi, Sanity, Contentful, WordPress, or Supabase consoles.
3. **Error Monitoring & Analytics**:
   - Remove developer seat from Sentry, LogRocket, Datadog, Hotjar, and Google Analytics.

### E. Corporate Communication & Remote Sessions
1. **Company Google Workspace / Microsoft 365**:
   - Sign in to Google Admin (`admin.google.com`) or M365 Admin Center.
   - Click **"Sign out of all sessions"** (forces remote browser and personal device disconnect).
   - Transfer Google Drive files and docs to Tech Lead or Manager.
   - Change password, revoke 2FA / recovery phone numbers, and suspend the account.
2. **Slack / Microsoft Teams**:
   - Deactivate member account.
   - Force session termination across all remote desktop and mobile clients.

### F. Hardware Verification (BYOD / Remote Confirmation)
1. **Personal Laptop Confirmation**:
   - Formally document in HR records that the employee provided their own hardware (BYOD).
   - Confirm company did not purchase or finance the laptop (or that any BYOD equipment stipend conditions have concluded).
2. **Peripheral & Accessory Check (If applicable)**:
   - Check asset register for any secondary equipment dispatched to the remote developer (e.g., test smartphones for responsive QA, company YubiKey, drawing tablets, headsets).
   - If zero company assets were issued, mark status as **"N/A - BYOD Only"**. If accessories were provided, require courier tracking receipt.

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
   - Add/verify the 14 checkpoints from the matrix above.
   - Ensure the BYOD Local Code Purge item (#03) and BYOD Hardware Confirmation item (#14) are marked **Mandatory**.
4. Under **4. Custom Undertakings & Questions**:
   - Add the questions from Section 3 above using the in-section question builder.
   - Mark the **BYOD Code Purge Declaration** and **Hardware Status** questions as **Required**.
5. Save the settlement and click **"Copy Clearance Link"** or **"Send Clearance Link"** to email it to the remote developer.
6. The remote developer completes the checklist, inputs their PR handover links, signs the BYOD data purge undertaking, and submits their clearance digitally.
