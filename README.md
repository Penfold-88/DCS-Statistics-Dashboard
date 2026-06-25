# DCS Statistics Dashboard

Turn your DCSServerBot statistics into a polished, configurable, squadron-ready website.

[![Version](https://img.shields.io/badge/Version-V1.3-orange?style=for-the-badge)](#v13-squadron-cms-expansion)
[![DCSServerBot](https://img.shields.io/badge/Requires-DCSServerBot-green?style=for-the-badge)](https://github.com/Special-K-s-Flightsim-Bots/DCSServerBot)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue?style=for-the-badge)](#requirements)
[![Mobile](https://img.shields.io/badge/Mobile-Friendly-purple?style=for-the-badge)](#frontend-features)
[![Languages](https://img.shields.io/badge/Languages-EN%20%7C%20DE%20%7C%20IT%20%7C%20ES-orange?style=for-the-badge)](#language-support)
[![License](https://img.shields.io/badge/License-MIT-lightgrey?style=for-the-badge)](LICENSE)
[![Live Demo](https://img.shields.io/badge/Live%20Demo-Online-brightgreen?style=for-the-badge&logo=googlechrome&logoColor=white)](https://demo.dcsstatisticsdashboard.app/)

---

## ✨ What's New In V1.3

### 🧱 Squadron CMS

- 📄 Custom pages with drafts, publishing, navigation badges, duplication, previews, SEO/sharing metadata, and optional landing-page selection.
- ✍️ Self-hosted safe editor for rich text, links, images, galleries, and live dashboard widgets.
- 🖼️ Media library and image galleries for squadron screenshots, recruitment images, training references, and server showcase pages.
- ⬇️ Downloads page for externally hosted files such as mission packs, kneeboards, mod lists, SOPs, and briefing documents.
- 🧩 Reusable live statistics widgets and iframe embeds for use inside CMS pages or external websites.
- 🧭 Unified Menu Manager for dashboard links, CMS pages, downloads, integrations, custom links, labels, ordering, visibility, and dropdowns.
- 🔐 CMS administration protected by existing admin roles and LSO permission controls.

### 🏗️ Framework And Security

- 🧱 Public pages, APIs, admin tools, installer, updater, backups, themes, authentication, localisation, and exports now run through focused controllers and services under `dcs-stats/app/`.
- 🔐 Saved DCSServerBot API keys are encrypted with AES-256-GCM and migrated from older plaintext storage automatically.
- 🛡️ Stronger validation for API proxy requests, uploads, sessions, exports, backups, updates, and imported custom files.
- 📦 Runtime data remains under protected data/upload folders and is kept out of Git.

---

## ✨ What's New In V1.2

### 🖥️ Dashboard Experience

- 📊 New configurable homepage cards and insight panels.
- 🛰️ Server selector for **All Servers** or individual detected servers.
- 🏆 Top 5 pilot chart selector for **Kills**, **K/D**, and **PvP K/D**.
- 📈 Leaderboard table and bar chart improvements.
- 🛬 Carrier landing / LSO trap data added to pilot profiles.
- 📱 Better mobile styling across navigation, cards, tables, and panels.
- 🔗 Custom **Squadron Links** dropdown in the front-end navigation.
- 🙌 Footer credits modal with project and contributor credits.

### ⚙️ Admin Control

- ✅ Site Features panel can enable or disable major dashboard sections.
- 🎛️ Fine-grained toggles for homepage, attendance, Top 5 insights, leaderboard, pilot, credits, squadron, and server sections.
- 🖥️ Dynamic detected server card controls.
- 📋 Admin dashboard overview cards.
- 🩺 API health/debug page.
- ⏱️ Configurable API refresh rate.
- 🔄 Update/version display improvements.
- 💾 Full settings import/export for portable site configuration.

### 🎨 Theme System

- 🎨 Theme presets.
- 🧪 Custom theme preset creation.
- 🌈 Detailed colour controls for headings, text, panels, cards, tables, buttons, footer, charts, and gradients.
- 📸 Header background image upload.
- 🪪 Logo support with display options for logo only, text only, or logo plus text.
- 📊 Homepage and leaderboard chart colour controls.
- 💾 Theme backup and restore tools.

### 🌍 Language Support

- 🗣️ Language groundwork added across the dashboard.
- 🇬🇧 English, 🇩🇪 German, 🇮🇹 Italian, and 🇪🇸 Spanish translation files included.
- 🧾 Translation template file for future community translations.
- 🛠️ Installer language selection.
- ⚙️ Admin language setting.

### 🚀 Performance

- ⚡ File-based API response caching for heavier endpoints.
- 🧹 Clear API cache button in the admin panel.
- 📴 Avoids unnecessary heavy API calls when related features are disabled.
- 📦 Self-hosted Chart.js instead of relying on a CDN.
- 🔁 Cache-busted assets for easier updates.

### 🔐 Security

- 🛡️ Stronger CSRF protection on destructive admin actions.
- 🔑 Session ID regeneration after login.
- 📁 Safer generated file permissions.
- 🚦 Improved API proxy allow-listing and validation.
- 🔐 Optional DCSServerBot API key support.
- 🙈 Sensitive API settings kept server-side.
- 🧪 Demo mode restrictions for public demo installs.
- 🗄️ Protected runtime data folders.

---

## 🧭 Frontend Features

| Area | Features |
| --- | --- |
| 🏠 Home | Player totals, playtime, sorties, attendance cards, Top 5 pilots, combat chart, Top 5 insights |
| 🏆 Leaderboard | Sortable pilot table, configurable columns, bar chart, mobile card view |
| 👨‍✈️ Pilot Statistics | Pilot search, profile layers, charts, LSO trap history, weapon/stat data |
| 🎖️ Pilot Credits | Credit views with translated labels and themed styling |
| 🛡️ Squadrons | Squadron overview, members, logos, leaderboard, live data panels |
| 🛰️ Servers | Live server cards, mission, theatre, weather, slots, extensions, SRS, active players |
| 🧭 Navigation | Unified menu manager, automatic feature/CMS links, labels, visibility, ordering, parent/child dropdowns, server scope selector |
| 📄 CMS Pages | Optional squadron website pages, landing pages, galleries, downloads, live widgets, embeds, and public navigation integration |
| 🙌 Credits | Footer credits modal with relevant project acknowledgements |

---

## 🛠️ Admin Features

| Section | What You Can Manage |
| --- | --- |
| 🔌 API Settings | DCSServerBot host, port, optional API key, timeout, cache TTL, refresh interval, API health |
| ✅ Site Features | Enable/disable dashboard sections and individual feature blocks |
| 🎨 Theme Management | Presets, colours, gradients, charts, header image, logo, CSS upload, backup/restore |
| 🧭 Menu Manager | Order and rename public links, hide items, choose new-tab behaviour, and build one-level dropdowns |
| ⚙️ CMS Settings | Enable/disable CMS features and choose an optional CMS landing page |
| 📄 CMS Pages | Dedicated page list/editor, draft previews, duplication, safe rich text, media library, publishing, landing-page selection and per-page SEO/social fields |
| 🖼️ CMS Galleries | Reusable image galleries with local media upload, captions, thumbnail selector, and lightbox viewing |
| ⬇️ CMS Downloads | Public download cards for externally hosted files, with categories, featured downloads, filtering, metadata, and batch entry tools |
| 🔌 CMS Embeds | Copy iframe code for approved live dashboard widgets on external websites |
| 🔗 Custom Links | Add third-party squadron links to the front-end navigation |
| 🔎 Privacy & SEO | Keywords, description, search engine crawling preference |
| 🌍 Language | Select site language and upload translation files |
| 🧰 Maintenance | Maintenance page and allowed IP management |
| 🔄 Updates | Stable/dev update channel, version display, backups, update checks |
| 💾 Settings Backup | Export and restore dashboard settings |
| 👥 Admins | Admin users, roles, LSO permissions, demo restrictions |

---

## 📋 Requirements

### 🖥️ Dashboard

- PHP 7.4 or newer.
- PHP cURL extension.
- PHP ZIP extension for update and backup features.
- Apache, Nginx, IIS, XAMPP, shared hosting, or a compatible PHP web server.
- Writable access for the dashboard runtime data folder.

### 🤖 DCSServerBot

- DCSServerBot with REST API enabled.
- Network access from the dashboard web server to the DCSServerBot REST API host and port.
- Optional but recommended: DCSServerBot API key.

---

## 🧱 Squadron CMS Quick Start

The CMS is optional. A fresh install can continue using the statistics dashboard exactly as before.

To enable the CMS:

```text
Admin Panel -> CMS Options -> CMS Settings
```

Common first steps:

1. Enable CMS.
2. Create a page from **CMS Pages**.
3. Add text, images, galleries, downloads, or live widgets.
4. Publish the page.
5. Use **Website Options -> Menu Manager** to place the page in navigation.
6. Optionally select a published CMS page as the homepage from **CMS Settings**.

Useful CMS tools:

| Tool | Use |
| --- | --- |
| CMS Pages | Create and publish custom website pages |
| CMS Galleries | Build reusable screenshot/media galleries |
| CMS Downloads | Link to mission packs, briefings, kneeboards, SOPs, mod lists, and other externally hosted files |
| CMS Embeds | Copy approved iframe embeds for live widgets on external websites |
| Menu Manager | Control where CMS pages, downloads, dashboard pages, and custom links appear |

The CMS stores user content under protected runtime data files and upload folders. These files are preserved by updates and should not be committed to Git.

---

## 🚦 First-Time Setup

1. Upload or copy the `dcs-stats/` folder to your web server.
2. Open the dashboard in your browser.
3. The installer should open automatically when no configuration exists.
4. Create the first admin account. This becomes the main **Air Boss** account.
5. Choose the site language.
6. Enter your DCSServerBot API host and port.
7. Test the API connection.
8. Save and start customising.

After installation, the admin panel is available at:

```text
/dcs-stats/site-config/
```

Once the dashboard is installed, the installer attempts to remove `site-config/install.php` automatically. If the web server cannot remove it, the web installer is locked behind the admin session and the Admin Dashboard shows a cleanup warning with a delete button.

---

## 🔌 API Configuration

The API host is normally entered as:

```text
192.168.0.0:9876
```

or:

```text
your-api-domain.com:9876
```

The dashboard will try to handle HTTP/HTTPS safely depending on the install environment.

HTTP remains the default for newly entered DCSServerBot hosts because many bot installations expose their REST API only over HTTP on a private network. HTTPS is preserved whenever it is configured or detected. Browser pages use the dashboard's server-side, allowlisted PHP proxy rather than connecting directly to an HTTP API.

If DCSServerBot has an API key configured, add it in:

```text
Admin Panel -> Settings -> API Settings
```

If your DCSServerBot REST API has no key configured, leave the field blank.

Keys saved through the installer or API Settings page are encrypted with AES-256-GCM. Existing plaintext keys are migrated automatically. The generated encryption key is kept in the protected `site-config/data/` directory.

For Docker or server-managed installs, you can keep the API key out of `api_config.json` by setting:

```env
DCSBOT_API_KEY=your_key_here
```

When `DCSBOT_API_KEY` is present, the dashboard uses it for API requests and ignores the saved API key field.

Advanced server-managed installs can provide a stable `DCS_CONFIG_ENCRYPTION_KEY` instead of using the generated local encryption-key file. Keep this value unchanged after first use.

---

## ⚡ API Caching

The dashboard includes file-based caching for heavier API responses.

Cached endpoints include:

- `/credits`
- `/getuser`
- `/highscore`
- `/serverstats`
- `/server_attendance`
- `/leaderboard`
- `/modulestats`
- `/squadrons`
- `/squadron_members`
- `/squadron_credits`
- `/stats`
- `/player_info`
- `/player_squadrons`
- `/topkills`
- `/topkdr`
- `/traps`
- `/trueskill`
- `/weaponpk`

Live status endpoints are not cached:

- `/servers`
- `/current_server`

Clear the cache from:

```text
Admin Panel -> Settings -> API Settings -> Clear API Cache
```

Cache files are stored in:

```text
dcs-stats/site-config/data/api-cache/
```

These are runtime files and should not be committed to Git.

`/server_attendance` has a minimum 15-minute cache time because it performs broader attendance aggregation. Page feature settings prevent the request when no attendance or insight widgets need it.

---

## 🌍 Language Support

Included languages:

- English
- German
- Italian
- Spanish

Translation files live in:

```text
dcs-stats/lang/
```

A translation template is included for new community translations:

```text
dcs-stats/lang/translation-template.json
```

When adding new visible text, translations should be kept up to date.

---

## 🔄 Updates

The update tool is available from:

```text
Admin Panel -> Settings -> Update
```

The updater supports:

- Installed version display.
- Stable and dev channel handling.
- GitHub branch checks.
- Backup creation before updates.
- Downgrade/version list where available.

Stable is the default update channel. A hidden `.dev` file enables the configured development branch.

Manual source installations are recorded without a commit SHA because the installer cannot verify which Git commit produced the copied files. They can still install the latest build from the selected stable or development channel, after which the updater records the verified commit.

Runtime settings and admin-managed customisations are preserved during normal updates, including uploaded branding under `uploads/`, generated theme files, menu settings, API settings, and local data. Manual edits made directly to core dashboard/template files may be replaced by the updater, so keep custom work in the admin theme tools, `uploads/`, or `custom/` where possible.

---

## 🔐 Security Notes

The dashboard includes:

- Password hashing.
- Session security controls.
- Session ID regeneration after login.
- CSRF protection for admin actions.
- Role checks for admin pages.
- Security headers.
- API proxy endpoint, method, query, and request-body allow-listing.
- Public API request validation and rate limits.
- Optional DCSServerBot API key forwarding.
- AES-256-GCM encryption for saved API keys with automatic plaintext migration.
- Sensitive API values kept server-side and redacted from audit events.
- Protected runtime data folders.
- Safer generated file permissions.
- CSV formula-injection protection and privacy-reduced standard exports.
- Strict validation for imported CSS, translations, backups, and update archives.
- Verified update downloads with archive structure and path-safety checks.

Recommended production setup:

- Use HTTPS for the public dashboard where possible.
- Protect `site-config/data/` from direct web access.
- Use a DCSServerBot API key where possible.
- Restrict access to the DCSServerBot API port.
- Keep PHP and web server packages updated.
- Back up settings before updates.

---

## 🔎 Privacy And SEO

The Privacy & SEO admin section lets you configure:

- Site description.
- Site keywords.
- Search engine indexing preference.

The dashboard sends very minimal install/version check-in data to the project maintainer's central service. This is intended to contain branch/version style information only, so support can be aligned with versions in use.

---

## 🧯 Troubleshooting

### 🛠️ Installer Does Not Open

- Confirm `site-config/data/` is writable.
- Confirm required config files do not already exist from another install.
- Clear browser cache.
- Check PHP error logs.

### 🔌 API Connection Fails

- Check that the DCSServerBot REST API is running.
- Confirm the dashboard server can reach the API host and port.
- Check firewall and port forwarding.
- If using Docker, the API host may need to be `host.docker.internal` on Windows/Mac.
- If using an API key, make sure the dashboard key matches the DCSServerBot key.

### 🎨 Charts Or Styles Look Old After Updating

- Clear browser cache.
- Use the API Settings clear-cache button.
- Restart Apache/PHP if OPcache is enabled and still serving old PHP.

### 🔄 Update Tool Fails

- Ensure the PHP ZIP extension is enabled.
- Ensure update and backup folders are writable.
- Check that the server can reach GitHub.
- Check the admin update log output.

---

## 🗂️ Project Structure

```text
DCS-Statistics-Dashboard/
├── dcs-stats/                  Main web application
│   ├── app/                    Internal V1.3 framework
│   │   ├── Controllers/        Public, API, admin, and installer controllers
│   │   ├── Core/               Framework, security, storage, and configuration helpers
│   │   ├── Services/           Focused application and integration services
│   │   ├── Views/              Public and admin view templates
│   │   └── bootstrap.php       Framework bootstrap
│   ├── css/widgets/            Reusable CMS/dashboard widget styles
│   ├── site-config/            Admin panel and setup tools
│   │   ├── api/                Admin API endpoints
│   │   ├── data/               Runtime settings and admin data
│   │   ├── css/                Admin styling
│   │   └── js/                 Admin JavaScript
│   ├── js/                     Front-end JavaScript and vendor scripts
│   ├── lang/                   Translation files
│   ├── uploads/pages/          CMS page and gallery media uploads
│   ├── index.php               Thin homepage entry point
│   ├── page.php                Thin CMS page entry point
│   ├── page_preview.php        Protected CMS draft preview entry point
│   ├── downloads.php           Thin CMS downloads entry point
│   ├── server_status_embed.php Thin server status iframe embed entry point
│   ├── dashboard_widget_embed.php Thin dashboard widget iframe embed entry point
│   ├── leaderboard.php         Thin leaderboard entry point
│   ├── pilot_statistics.php    Thin pilot profile entry point
│   ├── pilot_credits.php       Thin pilot credits entry point
│   ├── squadrons.php           Thin squadron entry point
│   └── servers.php             Thin server-status entry point
├── docker/                      Docker image and compose files
│   ├── Dockerfile
│   ├── Dockerfile.dockerignore
│   └── docker-compose.yml
├── CHANGELOG.md
└── README.md
```

---

## 🤝 Contributing

Contributions are welcome.

Recommended workflow:

```bash
git clone https://github.com/Penfold-88/DCS-Statistics-Dashboard.git
cd DCS-Statistics-Dashboard
git checkout -b feature/your-feature-name
```

Before opening a pull request:

- Test on desktop and mobile widths.
- Check PHP syntax for changed files.
- Avoid committing runtime data from your own install.
- Keep business logic in `app/` services and controllers; preserve the thin physical PHP entry points.
- Do not introduce a mandatory `/public` document root or rewrite-rule dependency.
- Update translations where new visible text is added.
- Update documentation when behaviour changes.

---

## 🙌 Credits

- [Special K's Flightsim Bots](https://github.com/Special-K-s-Flightsim-Bots) for DCSServerBot.
- VFS-252 Sky Pirates for testing, feedback, and original dashboard work.
- The DCS community for suggestions, bug reports, and real-world testing.
- Eagle Dynamics for DCS World.

---

## 💬 Support

- Discord Support: [https://discord.gg/uTk8uQ2hxC](https://discord.gg/uTk8uQ2hxC)
- Wiki: [https://github.com/Penfold-88/DCS-Statistics-Dashboard/wiki](https://github.com/Penfold-88/DCS-Statistics-Dashboard/wiki)
- Issues: [https://github.com/Penfold-88/DCS-Statistics-Dashboard/issues](https://github.com/Penfold-88/DCS-Statistics-Dashboard/issues)
- Live Demo: [https://demo.dcsstatisticsdashboard.app/](https://demo.dcsstatisticsdashboard.app/)
---

## 📄 License

This project is licensed under the MIT License. See [LICENSE](LICENSE).

---

# 📦 Installation Guides

The sections below are kept separate for users who need local testing or Docker deployment.

---

## 🌐 Standard Web Hosting Install

1. Download the latest release from GitHub.
2. Upload the `dcs-stats/` folder to your web server.
3. Open the dashboard in your browser, for example:

   ```text
   https://yourdomain.com/dcs-stats/
   ```

4. The installer should open automatically if the dashboard is not configured.
5. Create the first admin account.
6. Enter your DCSServerBot API host and port.
7. Test the API connection and save.

---

## 🧪 XAMPP / Local Testing Install

1. Copy the `dcs-stats/` folder into your XAMPP `htdocs` folder.
2. Start Apache in XAMPP.
3. Open:

   ```text
   http://localhost/dcs-stats/
   ```

4. Complete the installer.

Useful XAMPP checks:

- Make sure PHP cURL is enabled.
- Make sure PHP ZIP is enabled if you want update/backup features.
- Make sure Apache can write to `dcs-stats/site-config/data/`.

---

## 🐳 Docker Deployment

The Docker setup is designed to work on Windows, macOS, and Linux using Docker Desktop or Docker Engine.

Copy and run:

```bash
git clone https://github.com/Penfold-88/DCS-Statistics-Dashboard.git
cd DCS-Statistics-Dashboard
docker compose -f docker/docker-compose.yml up -d --build
```

Then open `http://localhost:8080`.

The Docker image includes PHP cURL, PHP ZIP / ZipArchive, cURL, zip/unzip, HTTPS certificate support, git tooling, and production OPcache defaults so the dashboard can talk to GitHub and process dashboard updates.

An `.env` file is optional. Docker uses safe defaults if you do not create one. Copy `.env.example` to `.env` only if you want to change runtime settings such as the public port, PHP limits, OPcache behaviour, logs, health checks, resource limits, or the optional `DCSBOT_API_KEY` secure API key override. Required PHP extensions and command-line tools are built into the Docker image.

Useful Docker commands:

```bash
# View logs
docker compose -f docker/docker-compose.yml logs -f

# Stop the container
docker compose -f docker/docker-compose.yml down

# Update to latest version
docker compose -f docker/docker-compose.yml pull
docker compose -f docker/docker-compose.yml up -d --build

# Restart after using the dashboard updater
docker compose -f docker/docker-compose.yml restart

# Access container shell
docker compose -f docker/docker-compose.yml exec dcs-stats sh
```

To change the public port, copy the example file, then edit `.env` in the repository root:

```bash
cp .env.example .env
```

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

```bash
WEB_PORT=8090
```

For local Docker development only, you can allow PHP to detect file edits by adding this to `.env`:

```bash
OPCACHE_VALIDATE_TIMESTAMPS=1
OPCACHE_REVALIDATE_FREQ=2
```
