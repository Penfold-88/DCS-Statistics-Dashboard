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

## 🔐 File Permissions

If you are not using XAMPP or Docker, your web server must be able to write to the dashboard runtime folders. This allows the installer, admin settings, themes, backups, cache, and uploaded branding to work.

The most important writable locations are:

```text
dcs-stats/site-config/data/
dcs-stats/uploads/
dcs-stats/custom/
dcs-stats/backups/
```

These generated files may also need write access:

```text
dcs-stats/site_config.json
dcs-stats/menu_config.json
dcs-stats/custom_theme.css
dcs-stats/header_custom.css
dcs-stats/.version_meta.json
```

On Debian or Ubuntu, the web server user is often `www-data`. From the folder above `dcs-stats`, you can usually grant safe ownership with:

```bash
sudo chown -R www-data:www-data dcs-stats/site-config/data dcs-stats/uploads dcs-stats/custom dcs-stats/backups
sudo chmod -R 750 dcs-stats/site-config/data dcs-stats/uploads dcs-stats/custom dcs-stats/backups
```

If the generated root-level files already exist, you can also run:

```bash
sudo chown www-data:www-data dcs-stats/site_config.json dcs-stats/menu_config.json dcs-stats/custom_theme.css dcs-stats/header_custom.css dcs-stats/.version_meta.json
sudo chmod 640 dcs-stats/site_config.json dcs-stats/menu_config.json dcs-stats/custom_theme.css dcs-stats/header_custom.css dcs-stats/.version_meta.json
```

Do not use `chmod 777` unless you fully understand the risk. It makes files world-writable and is not recommended for public servers.

On shared hosting, use the hosting file manager or control panel to make the folders writable by the web server account. If unsure, ask your host which user PHP runs as.

If the admin login says `Security token invalid` immediately after install, PHP is usually not saving sessions correctly. Check that `dcs-stats/site-config/data/` is writable by the web server; the dashboard will try to create a protected `php-sessions/` folder inside it.

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
