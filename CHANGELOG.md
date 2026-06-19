# 📋 Changelog

All notable changes to **DCS Statistics Dashboard** are recorded here.

---

## 🚀 V1.3

V1.3 is the **Squadron CMS Expansion** release. Development began by converting the existing dashboard into a cleaner internal framework while preserving the simple installation and update experience expected by Docker, XAMPP, and hosted-server users.

V1.3 remains in development. New implementation entries should include the date they were completed.

---

### ✨ Headline Features

- 🏗️ **Completed the internal framework conversion** across public pages, APIs, administration, installation, updates, themes, settings, backup, export, authentication, and configuration. *(19 June 2026)*
- 🧩 **Introduced 89 focused services** and reduced 39 large mixed-responsibility facades. *(19 June 2026)*
- 🧹 **Removed more than 3,000 lines** from mixed-responsibility classes while retaining existing behaviour. *(19 June 2026)*
- 🔌 **Preserved simple deployment compatibility** for Docker, XAMPP, and hosted web servers without requiring a Laravel-style `/public` directory. *(19 June 2026)*

---

### 🏗️ Framework & File Organisation

- 📁 Moved application code into controllers, services, core helpers, and views under `dcs-stats/app/`. *(11–19 June 2026)*
- 🎛️ Converted public pages, public APIs, admin pages, admin APIs, installation, authentication, updates, themes, settings, backup, export, and configuration workflows to framework classes. *(11–19 June 2026)*
- 🖼️ Separated public rendering, shared layouts, page assets, and reusable views from legacy page files. *(11–14 June 2026)*
- 🧱 Reduced controllers and page facades to request orchestration, service calls, and view rendering. *(13–19 June 2026)*
- 🔗 Improved dependency boundaries and reduced repeated per-request service creation. *(14–19 June 2026)*

---

### 🎨 Theme & Header Services

- 🧩 Centralised theme service composition. *(14 June 2026)*
- 🖼️ Moved header image persistence into a dedicated service. *(18 June 2026)*
- 🔗 Moved theme preview URL construction into a dedicated service. *(18 June 2026)*
- 🎨 Split theme actions into focused preset, import, menu, appearance, upload, restore, logging, and dispatch services. *(19 June 2026)*
- 🧭 Retained `ThemeActionService::handle()` as the stable public facade. *(19 June 2026)*

---

### 🧰 Admin, Core & Installer Services

- 🔐 Extracted admin authentication, session, permission, navigation, URL, and data-initialisation services. *(17–18 June 2026)*
- ⚙️ Extracted admin dashboard, settings, maintenance, metadata, backup, export, and page-state services. *(17–18 June 2026)*
- 🌍 Decomposed cache, localisation, API configuration, feature configuration, and storage responsibilities. *(18 June 2026)*
- 🛠️ Extracted installer environment, input, file-writing, version-initialisation, and completion-rendering services. *(18 June 2026)*
- 📡 Reduced public API controllers to endpoint facades backed by focused API services. *(18 June 2026)*

---

### 🛡️ Security Hardening

- 🔐 Changed admin logout and data export actions to POST-only requests protected by CSRF validation. *(19 June 2026)*
- 🍪 Added Strict SameSite remember-me cookies, automatic Secure cookies on HTTPS, timing-safe token checks, and token rotation after successful restoration. *(19 June 2026)*
- 📤 Prevented spreadsheet formula injection in CSV exports, restricted full exports to explicit field allowlists, and removed upstream API addresses from browser configuration. *(19 June 2026)*
- 📡 Added public API rate limits, leaderboard and search validation, strict proxy method/body handling, and blocked upstream API redirects. *(19 June 2026)*
- 🔑 Redacted API keys from configuration audit events and reinforced private permissions for sensitive configuration, translation, backup, update, and restore files. *(19 June 2026)*
- 🎨 Added validation for uploaded and restored CSS, blocking executable directives and external resource references from imported backups. *(19 June 2026)*
- 📦 Hardened updates with verified TLS, native Windows certificate support, GitHub host and repository validation, immutable commit resolution, download limits, SHA-256 audit output, strict archive structure checks, and protected workspaces. *(19 June 2026)*
- 💾 Added randomized private restore workspaces, archive size and symlink checks, stricter language upload validation, and safer path-boundary checks. *(19 June 2026)*
- 🛠️ Added installer CSRF and rate protection, an optional `DCS_INSTALL_TOKEN`, exclusive installation locking, race-condition checks, and explicit self-delete failure logging. *(19 June 2026)*
- 🔐 Replaced plaintext non-Docker API-key storage with AES-256-GCM encryption, automatic migration, protected local key material, and optional `DCS_CONFIG_ENCRYPTION_KEY` support. *(19 June 2026)*
- 🧾 Replaced silent remember-me session-store failures with explicit validation and server-side error logging. *(19 June 2026)*
- 🐳 Clarified Docker PHP memory headroom, increased multipart POST capacity above the upload ceiling, and retained production OPcache timestamp validation settings. *(19 June 2026)*

---

### 🧹 Compatibility & Cleanup

- 🗑️ Removed 23 obsolete internal compatibility wrappers after replacing filename-based bootstrap dependencies with framework support loaders. *(19 June 2026)*
- 🔌 Removed nine duplicate `*_api.php` aliases and retained one canonical URL for each public API endpoint. *(19 June 2026)*
- 🧭 Kept thin root and `site-config/` PHP entry files as stable deployment adapters. *(19 June 2026)*
- 🌐 Retained `dcs-stats/` as the web document root with no mandatory rewrite rules, custom `php.ini` settings, or `/public` directory migration. *(19 June 2026)*
- ✅ Verified all 498 remaining PHP files lint cleanly after compatibility cleanup. *(19 June 2026)*
- 🧪 Confirmed public pages, canonical APIs, login, and protected admin routes continued to respond correctly. *(19 June 2026)*

---

## 🚀 V1.2

V1.2 is a major dashboard, admin, theme, update, language, security, and performance release. It focuses on making the project easier to install, easier to customise, safer to run, and much better suited to different squadrons and server setups.

---

### ✨ Headline Features

- 🎨 **Full theme management system** with custom colours, gradients, chart colours, header images, logos, presets, backups, and restores.
- 🌍 **Language support groundwork** with English, German, and Italian translation files plus a translation template for future languages.
- 📅 Added a public date format setting so squadrons can choose regional date display.
- ⚙️ **Expanded admin panel** with feature toggles, API settings, privacy/SEO tools, update controls, backups, logs, custom links, and dashboard overview cards.
- 📡 **Expanded DCSServerBot API support** including newer endpoint data, server attendance, server details, traps/LSO data, and server-scoped views.
- 🧭 **Server filter selector** across the frontend, allowing users to view all servers or a specific detected server.
- 📊 **New charts and visual controls** for leaderboard, homepage, pilot statistics, and theme-customisable chart colours.
- 🛡️ **Large security hardening pass** covering CSRF, session handling, file permissions, backup safety, API config exposure, uploads, and demo mode.
- 🔒 Hardened updater ZIP extraction validation to reject unsafe archive paths before unpacking updates.
- ⚡ **Performance improvements** with API caching, reduced unnecessary calls, lighter admin homepage loading, and smarter chart loading.
- 🧪 **Demo mode support** for public showcase installs with protected settings and restricted sensitive actions.

---

### 🎨 Theme & Visual Customisation

- 🎛️ Added a full **Theme Management** area in the admin panel.
- 🖌️ Added editable colours for:
  - Page background
  - Header title and subtitle
  - Navigation
  - Cards and panels
  - Card headings
  - Muted card text
  - Buttons
  - Tables
  - Table header text
  - Table body text
  - Player name links
  - Footer
  - Charts
- 🌈 Added controlled soft gradient options.
- 🌌 Added page background gradient options.
- 🖼️ Added optional full-page background image upload with focal-position controls.
- 🧩 Added theme presets while still allowing fully custom themes.
- 💾 Added theme backup and restore tools.
- 🖼️ Added custom header image upload with default fallback.
- 🪪 Added header logo support with controls to show:
  - Text only
  - Logo only
  - Logo and text
- 📏 Added recommended logo and header image sizing guidance.
- 📊 Split homepage chart colour controls into their matching widgets for finer control.
- 🧼 Fixed multiple old hardcoded green colours so presets and custom colours apply properly.
- 📱 Improved mobile nav and card colour consistency.
- 🧱 Improved card styling consistency across homepage, servers, leaderboard, squadrons, and pilot statistics.

---

### 🧭 Navigation & Custom Links

- 🔗 Added configurable **Squadron Links** menu.
- ✏️ Added admin controls to rename the custom links menu item.
- ➕ Added support for user-created third-party links.
- 📂 Moved admin-side link management into its own cleaner container under settings.
- 👁️ Frontend navigation now respects feature toggles for:
  - Leaderboard
  - Pilot statistics
  - Servers
  - Squadrons
  - Credits
  - Discord
  - Custom links

---

### 🏠 Homepage

- 📊 Added and refined homepage chart widgets.
- 🏆 Added Top 5 pilot card selector for:
  - Kills
  - K/D
  - PvP K/D
- 🎚️ Added site feature toggles for homepage sections.
- 👥 Added attendance card feature toggle.
- 🧠 Fixed **Top 5 Insights** toggle so it controls theatres, missions, and modules together.
- 🧮 Top insights now use **Top 5** instead of Top 10.
- 🧼 Removed old explanatory text from the homepage.
- 🎨 Matched new cards to the rest of the dashboard styling.
- 👥 Added player icons to attendance cards.
- 📱 Improved long translated card labels so they wrap cleanly on smaller screens.
- 📱 Disabled homepage chart hover tooltips on touch/mobile screens to prevent floating overlays.
- 📱 Fixed the Top 5 Pilots metric selector overflowing its chart card on mobile.
- 📅 Player Activity Overview dates now follow the configured public date format.
- ⚡ Reduced unnecessary homepage API calls and made slower chart data load in the background where possible.

---

### 🏆 Leaderboard

- 📊 Added a leaderboard bar chart.
- 🎚️ Added an admin toggle to enable or disable the leaderboard chart.
- 🔢 Added configurable leaderboard columns.
- 🧹 Removed duplicate/non-working flight hours option.
- 🚫 Marked unsupported sortie-related per-pilot fields as not implemented where needed.
- 📱 Improved mobile leaderboard cards and fixed old hardcoded colours.
- 📐 Expanded table layout to reduce awkward horizontal scrolling.
- 📊 Fixed leaderboard chart overflow when the screen is narrowed.
- 🌍 Added translation coverage for leaderboard text.

---

### 👨‍✈️ Pilot Statistics

- 🔍 Improved pilot search result styling.
- 🎨 Fixed pilot search highlights and selected pilot views to follow theme colours.
- 📊 Updated pilot statistics charts to use configured theme colours.
- 🛬 Added **Carrier Landings / LSO** data from the `/traps` API.
- 📋 Added a dedicated carrier landing card under the existing pilot data.
- 🧾 Carrier landing table includes grade, points, wire, aircraft, location, and time.
- 🕒 Separated carrier landing date and time more clearly without increasing row height.
- 📅 Removed leading zero padding from carrier landing dates.
- 📐 Widened the pilot statistics layout so charts and LSO tables have more room on desktop.
- 🌍 Added translation coverage for pilot search, result selection, layered pilot views, and chart labels.
- 📅 Carrier landing dates now follow the configured public date format.

---

### 🛬 Carrier / LSO Features

- 🛬 Added support for DCSServerBot `/traps` endpoint.
- 🎯 Added carrier landing summary data to pilot statistics.
- 📋 Added readable trap history table.
- 🔐 Added LSO permissions controls in admin.
- 🧪 Demo mode now greys out LSO permissions where editing is restricted.

---

### 🖥️ Servers Page

- 🧹 Removed the old original server table.
- 🧱 Rebuilt server display into cleaner cards matching the rest of the site.
- 🌦️ Added controls for server card sections:
  - Status badge
  - Description
  - Mission and theatre
  - Slot usage
  - Restart time
  - Weather
  - Extensions/SRS
  - Active players
- 🧩 Added dynamic detected server toggles so individual server cards can be shown or hidden.
- 📐 Improved long mission name wrapping and scaling.
- 🧾 Improved long server description wrapping so large descriptions stay inside the card.
- 🔐 Added a server feature toggle to mask extension/SRS password-style values on the public servers page.
- 📅 Server restart times now follow the configured public date format.
- 👥 Improved active player display and server player count handling.
- 🎨 Fixed server page colours so weather, extensions, active players, titles, and boxes follow theme settings.
- 🌍 Added translation coverage for servers page.

---

### 🎚️ Site Features

- ✅ Fresh installs now enable frontend features by default.
- 💾 Updates preserve existing user feature choices.
- 🏠 Added homepage section controls.
- 👥 Added attendance card controls.
- 🏆 Added leaderboard column controls.
- 📊 Added chart controls.
- 🖥️ Added server section controls.
- 🧭 Added frontend server filter toggle.
- 🧩 Server filter respects detected server visibility settings.
- 🔁 If the server filter is disabled, the frontend uses all enabled server data.

---

### 🌍 Languages & Translation

- 🌐 Added language system groundwork.
- 🇬🇧 Added English language file.
- 🇩🇪 Added German language file.
- 🇮🇹 Added Italian language file.
- 🧾 Added translation template JSON for future community translations.
- ⬆️ Added upload support for additional translation files.
- 🛠️ Added admin language controls.
- 🧰 Added installer language selection support.
- 🧭 Translated major frontend pages:
  - Homepage
  - Leaderboard
  - Pilot statistics
  - Pilot credits
  - Squadrons
  - Servers
  - Credits modal
  - Maintenance page
- 🛡️ Translated major admin pages:
  - Dashboard
  - Admins
  - LSO permissions
  - API settings
  - API health
  - Site features
  - Custom links
  - Privacy & SEO
  - Settings backup
  - Maintenance
  - Discord settings
  - Squadron settings
  - Update
  - Themes
- 🧼 Cleaned the language admin page to show currently translated languages clearly.

---

### 🔐 Privacy, SEO & Metadata

- 🔎 Added **Privacy & SEO** admin section.
- 🏷️ Added editable website metadata:
  - Keywords
  - Description
  - Search engine crawling preference
- 🚫 Added option to block search engines from crawling.
- ℹ️ Added minimal usage data disclaimer explaining version/branch check-in behaviour.

---

### 📡 Install Check-In & Version Reporting

- 📈 Added anonymous install/version check-in support.
- 🔒 Check-in sends minimal aggregate data only:
  - Project
  - Version
  - Branch
  - Channel
  - UTC day
- 🚫 No install IDs, IP addresses, usernames, site names, or player data are stored.
- ⏱️ Reduced check-in timeout so a slow check-in service does not noticeably slow the dashboard.
- 🔁 Check-in now reports again after an update, even if the site already checked in earlier that day.

---

### 🔄 Updater & Versioning

- 🧭 Added update channel handling:
  - Stable uses `master`
  - Dev uses configured dev branch when `.dev` exists
- 🧾 Improved installed build/version display.
- 🧭 Manual downloads now show as `V1.2 (Manual Download)` until the updater records an installed commit.
- 🛟 Added support info copy button for easier issue reporting.
- 💾 Added automatic backup before updates.
- 🧱 Improved update preservation rules for:
  - API config
  - Site config
  - User data
  - Theme files
  - Header files
  - Menu config
  - Uploads
  - Docker files
  - `.dev`
  - `.demo`
- 🧰 Fixed updater support for systems without `ZipArchive` by showing a clearer message.
- 🪟 Fixed Windows path preservation issues in updater.
- 🧼 Fixed update flow that could incorrectly return users to install.php.
- 📡 Update now triggers a version check-in after successful update.
- 🐳 Fixed Docker non-root startup by making nginx and php-fpm logs writable by the `www` user.
- 🐳 Moved Docker deployment files into a dedicated `docker/` folder with updated cross-platform compose commands.
- 🐳 Added Docker image dependencies for GitHub update support, including PHP cURL, ZIP, certificates, and git tooling.
- 🐳 Hardened Docker OPcache defaults for internet-facing installs while allowing local development overrides.
- 🐳 Made the Docker startup script a real copied file to avoid startup failures on some Windows Docker builds.
- 🐳 Fixed Docker PHP-FPM logging so non-root containers do not fail with `/proc/self/fd/2` permission errors.

---

### 🛡️ Security Hardening

- 🔐 Added session ID regeneration after successful login.
- 🧷 Tightened remember-me comparison handling.
- 🛡️ Added CSRF protection to sensitive update/backup/version actions.
- 🔒 Added authentication protection around API configuration exposure.
- 🙈 Public frontend API config now returns only minimal browser-safe values.
- 🧼 Removed SVG logo upload support to avoid active-content risk.
- 🧱 Reduced unsafe file permissions and removed world-writable fallbacks.
- 🗃️ Improved backup and restore safety.
- ♻️ Added rollback protection if a backup restore fails part-way through.
- 🚫 Blocked direct web access to generated backup archives.
- 🧾 Added validation for export date inputs.
- 🚫 Added stronger direct access protection for sensitive data directories.
- 🧪 Added demo mode restrictions for public showcase installs.
- 🙈 Masked sensitive API settings in demo mode.
- 🚪 Locked update, metadata, export, backup, API settings, and edit actions where demo restrictions apply.
- 👤 Demo protected admin is now read from `.demo` instead of being hardcoded.
- 🧯 Admin forms no longer prefill demo/test credentials.
- 🪵 Improved admin log pruning to avoid huge log files slowing the panel.
- 📦 Self-hosted Chart.js instead of relying on the CDN.
- 🧰 Added cache busting helpers for assets.

---

### ⚡ Performance & Caching

- 🗄️ Added file-based API response caching.
- ⏱️ Added configurable API cache TTL options including 5, 10, 15, 30, and 60 minutes.
- 🧹 Added API cache clear button in API settings.
- 🧠 Avoids calling heavy endpoints when related cards/features are disabled.
- 🐢 Added special handling around heavy `/server_attendance` usage.
- 🚀 Reduced homepage initial blocking calls.
- 📊 Top squadron chart data now loads in the background.
- 🧭 Server stats, attendance, and top pilots are fetched more efficiently.
- 🧰 Simplified admin dashboard stats to avoid unnecessary data reads.
- 🪵 Admin logs now prune to a maximum retained count.
- 🧹 API cache now automatically prunes expired files and caps total cache file growth.

---

### 🧪 Demo Mode

- 🧾 Added `.demo` driven demo mode.
- 🟨 Added frontend demo banner.
- 🟨 Added admin demo banner.
- 🔐 Sensitive admin actions are restricted in demo mode.
- 👤 Protected demo admin user is configured through `.demo`.
- 🧪 Users can browse admin/theme options without being able to save restricted edits.
- 🧹 Demo messaging warns that sensitive data is restricted and data may reset.

---

### 🧰 Admin Panel

- 🧭 Renamed **CAG Bridge** to **Admin Panel**.
- 🃏 Added admin dashboard overview cards.
- 🔄 Admin dashboard update card now highlights when a GitHub update is available.
- 🏠 Added a quick **Go To Website** shortcut at the top of the admin navigation.
- 📉 Made admin homepage panels cleaner and smaller.
- 🪵 Improved bridge/admin log layout.
- ⚡ Reduced admin homepage load overhead.
- 🔧 Added API health/debug page.
- 🔘 API health is accessible from API settings rather than cluttering the dashboard.
- 💾 Added whole-site settings import/export.
- 🧰 Added clearer backup and restore controls.
- 🧑‍💼 Improved admin user protection and demo restrictions.

---

### 🧱 Installer & Fresh Installs

- 🌍 Added language selection groundwork for installer.
- ✅ Fresh installs now create required settings cleanly.
- ✅ Fresh installs enable feature toggles by default.
- 🔐 Installer now supports optional DCSServerBot API keys when testing protected `/servers` endpoints.
- ⚠️ Admin dashboard now warns if `site-config/install.php` is still present after installation.
- 🧹 Admin dashboard can now delete `site-config/install.php` directly when file permissions allow it.
- 🕒 The existing “Show Last Update Time” setting now displays the latest data refresh timestamp in the public footer.
- 🔒 Hardened installer cleanup, backup deletion, installer API key validation, and theme image path handling from the v4 security review.
- 🔐 Added optional `DCSBOT_API_KEY` environment override so Docker/server installs can keep the DCSServerBot API key out of saved JSON config.
- 🔐 Locked the web installer behind admin authentication once the dashboard is already installed.
- 🧹 Installer now attempts to remove `site-config/install.php` automatically after a successful install.
- 🧭 Fresh installs now use the selected update channel branch and record the latest GitHub commit metadata when available.
- 🧭 Stable update channel now defaults to `main` instead of the old `master` branch name.
- 🧭 Missing configuration now redirects to installer properly.
- 🧹 Removed fragile `goto` flow from installer logic.
- 🧰 Installer-generated local data stays out of Git distribution.

---

### 📄 Footer & Credits

- ©️ Footer now reads **© 2025 DCS Statistics Dashboard**.
- 🧾 Footer now opens a credits modal instead of listing all credits inline.
- ✈️ Updated Sky Pirates credit to **VFS-252 Sky Pirates**.
- 🤝 Added Special K credit with GitHub link.
- 🌍 Added translation coverage for credits and modal content.

---

### 🧹 Repository & Runtime Cleanup

- 🚫 Ignored local/runtime files:
  - `.php-sessions/`
  - `.dev`
  - `.demo`
  - Generated data JSON files
  - Preview server logs
  - Local PDF review files
- 🧼 Removed accidentally tracked local session/log/runtime files from Git tracking.
- 🧾 Kept Docker files in place for now to avoid breaking existing Docker users.

---

### 🐛 Fixes

- 🧯 Fixed site-config layout breaking when CSS/assets failed to apply.
- 🧭 Fixed admin navigation links incorrectly pointing to `/login.php`.
- 📊 Fixed leaderboard columns still showing after being disabled.
- 📱 Fixed mobile nav using default colours.
- 📱 Fixed mobile leaderboard stat colours.
- 🎨 Fixed hardcoded green headings, glows, search borders, and badges.
- 🖼️ Fixed header gradient affecting uploaded logos.
- 👥 Fixed squadrons page `sq.members.forEach is not a function`.
- 👥 Fixed squadron data fallback issues.
- 🖥️ Fixed servers active player display using stale/incorrect data where possible.
- 🧾 Fixed bridge log pagination fatal error.
- 🧾 Fixed export page undefined role constant fatal error.
- 🗃️ Fixed backup restore filename mismatch.
- 🔐 Fixed several security review findings from external audit reports.

---

## 🧭 Earlier History

### V1.1 - 2025

- 🔍 Added pilot search improvements.
- 📊 Added early Chart.js visualisations.
- 🛬 Added initial carrier trap tracking from JSON data.
- 🏆 Added leaderboard and pilot statistics improvements.
- 🛡️ Added early security headers, input validation, and file access protection.
- 📚 Improved README and installation documentation.

### Pre-V1.1

- 📡 Added initial DCSServerBot REST API integration.
- 🧾 Added API/JSON routing support.
- 🧮 Added server statistics aggregation.
- 🎨 Added early frontend styling improvements.
- 🧰 Added first admin/configuration tooling.

---

## 🤝 Credits

- ✈️ **Penfold-88** - Project lead and ongoing feature direction.
- 🛠️ **Socialoutcast** - Original development and security contributions.
- 🏴‍☠️ **VFS-252 Sky Pirates** - Demo data, testing, and project support.
- 🤝 **Special K** - Flightsim bot ecosystem support.

---

## 📝 Notes

- Docker files are still kept at the repository root for compatibility.
- Runtime data and local configuration should not be committed to Git.
- Translation files should be kept up to date as new features are added.
