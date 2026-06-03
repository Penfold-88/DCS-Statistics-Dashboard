# Security Policy

Thank you for helping keep DCS Statistics Dashboard safe for the community.

Please report security issues privately first so they can be reviewed and fixed before details are shared publicly.

## Supported Versions

Security fixes are currently handled for the latest public release and the active development branch.

| Version / Branch | Supported |
| --- | --- |
| Latest stable release | Yes |
| Active dev branch | Yes |
| Older releases | Best effort |

If you are unsure which version you are running, check the installed build information in the admin update page.

## Reporting A Vulnerability

Please do not open a public GitHub issue for suspected security vulnerabilities.

Use one of these private routes instead:

- GitHub private security advisory, if available on the repository.
- Discord support: https://discord.gg/uTk8uQ2hxC
- Contact the project maintainer directly through the project support channels.

When reporting, please include:

- The dashboard version and branch.
- Whether the install is XAMPP, Apache, nginx, Docker, or another host.
- A clear description of the issue.
- Steps to reproduce, if safe to share.
- Screenshots or logs with secrets removed.
- Whether the issue affects public visitors, logged-in admins, demo mode, updates, backups, API settings, or exported data.

Please remove or mask API keys, passwords, session cookies, IP addresses, and private server URLs before sending logs or screenshots.

## What To Expect

Security reports will be reviewed as quickly as possible.

For confirmed issues, the project will aim to:

- Confirm the affected area.
- Prepare a fix on the active development branch.
- Release or merge the fix when tested.
- Credit reporters where appropriate and where they want credit.

Response times may vary because this is a community project, but high-risk reports will be prioritised.

## Security Scope

Reports are especially useful for:

- Authentication and session handling.
- Admin permissions and role bypasses.
- CSRF, upload, backup, restore, update, or export issues.
- Exposure of API configuration, API keys, user data, or local settings.
- Docker, Apache, nginx, or `.htaccess` protections that could expose private files.
- Unsafe update handling or archive extraction.
- Cross-site scripting or unsafe custom theme handling.

## Deployment Responsibilities

DCS Statistics Dashboard includes security hardening, but server owners are still responsible for their own hosting environment.

Recommended deployment practices:

- Use HTTPS for public deployments where possible.
- Keep PHP, Apache/nginx, Docker, and the host operating system updated.
- Use strong admin passwords.
- Configure the optional DCSServerBot API key where available.
- Do not publish private data files, backups, logs, `.dev`, `.demo`, or local config files.
- Keep `site-config/data`, cache folders, backups, and runtime files blocked from direct web access.
- Restrict the admin panel to trusted users only.

## Privacy And Install Check-In

The dashboard may send minimal install/version check-in information to the project maintainer's central service to help understand which versions are in use and plan support. This is intended to be limited to branch/version style information and not personal dashboard data.

Do not include personal data, private API details, passwords, or user-identifying information in security reports unless it is essential to the vulnerability and has been masked where possible.

## Public Disclosure

Please allow time for a fix before publishing vulnerability details. Coordinated disclosure helps protect users who may not update immediately.
