# 🔐 Security And Privacy

This page summarises the main security and privacy controls.

---

## 🛡️ Admin Security

The dashboard includes:

- Login-protected admin pages
- Role/permission checks
- CSRF protection on sensitive actions
- Session ID regeneration after login
- Remember-me hardening
- Demo mode restrictions
- Safer backup restore handling

---

## 🔌 API Security

API settings are managed server-side.

The public frontend config endpoint only returns harmless browser settings needed by the dashboard.

The DCSServerBot API key, API host details, and sensitive settings stay server-side.

Optional DCSServerBot API key support is available and recommended where possible.

For Docker or server-managed installs, the API key can be supplied through `DCSBOT_API_KEY`. When this environment variable is set, it is used at runtime instead of the saved JSON key, which keeps the key out of `site-config/data/api_config.json`.

---

## 📁 Protected Files And Folders

Sensitive runtime data should not be directly downloadable.

Protected areas include:

- `site-config/data/`
- `data/`
- `backups/`
- `site-config/theme_backups/`
- `.dev`
- `.demo`
- Runtime JSON files

On Apache, this is handled by `.htaccess`.

On Nginx/Docker, protection must be present in the Nginx config.

---

## 🔎 Privacy And SEO

Go to:

```text
Admin Panel -> Settings -> Privacy & SEO
```

Admins can configure:

- Website keywords
- Website description
- Search engine crawling preference

---

## 📡 Install Check-In

The dashboard sends minimal version/check-in information to help understand which versions are in use.

Intended data:

- Project
- Version
- Branch
- Channel
- UTC day

It should not send player data, usernames, site names, install IDs, or personal information.

---

## ✅ Recommended Public Install Checks

Before public release or production use:

1. Confirm admin pages require login.
2. Confirm runtime data folders are blocked.
3. Confirm backups are blocked from direct download.
4. Confirm API key is not visible in page source or public endpoints.
5. Confirm update and backup actions require admin permission.
6. Confirm demo mode is disabled unless intentionally used.
