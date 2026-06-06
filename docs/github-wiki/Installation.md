# 🚀 Installation

This page covers a standard browser-based installation of DCS Statistics Dashboard.

---

## 📋 Requirements

### 🖥️ Web Server

- PHP 7.4 or newer
- PHP cURL extension
- PHP ZIP extension for update and backup features
- Apache, Nginx, IIS, XAMPP, shared hosting, or compatible PHP hosting
- Write access for the dashboard runtime data folders

### 🤖 DCSServerBot

- DCSServerBot with REST API enabled
- Network access from the web server to the DCSServerBot API host and port
- Optional but recommended: DCSServerBot API key

Docker or server-managed installs can also provide the API key with `DCSBOT_API_KEY`. When set, this environment variable is used instead of the saved JSON key.

After installation, the installer attempts to remove `site-config/install.php` automatically. If the web server cannot remove it, the web installer is locked behind the admin session and the Admin Dashboard shows a cleanup warning with a delete button.

---

## ✨ Fresh Install

1. Download the latest dashboard release from GitHub.
2. Upload or copy the `dcs-stats/` folder to your web server.
3. Open the dashboard in your browser.
4. If no configuration exists, the installer should open automatically.
5. Choose the site language.
6. Create the first admin account. This becomes the main **Air Boss** account.
7. Enter your site name and basic settings.
8. Enter your DCSServerBot API host and port.
9. Test the API connection.
10. Save and open the dashboard.

---

## ⚙️ Admin Panel URL

After installation, the admin panel is available at:

```text
/dcs-stats/site-config/
```

If your install is at the web root, use:

```text
/site-config/
```

---

## 🧪 XAMPP Notes

Typical local install path:

```text
C:\xampp\htdocs\dcs-stats
```

Typical local browser URL:

```text
http://localhost/dcs-stats/
```

If the installer does not appear on a fresh install, check that runtime config files have not already been copied into:

```text
dcs-stats/site-config/data/
```

---

## 🐳 Docker Notes

Docker files are now kept in the repository `docker/` folder so the main project root stays cleaner.

For Docker installs, see [Docker Deployment](Docker-Deployment).

---

## ✅ Fresh Install Defaults

On a fresh install, frontend features should be enabled by default.

User choices should then be preserved during updates.

Admin-managed customisations are preserved during normal updates, including uploaded branding under `uploads/`, generated theme files, menu settings, API settings, and local data. Manual edits made directly to core dashboard/template files may be replaced by the updater, so keep custom work in the admin theme tools, `uploads/`, or `custom/` where possible.
