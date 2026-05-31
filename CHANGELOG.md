# 📋 Changelog

All notable changes to **DCS Statistics Dashboard** are recorded here.

---

## 🚀 V1.2 - 2026-05-31

V1.2 is a major dashboard, admin, theme, update, language, security, and performance release. It focuses on making the project easier to install, easier to customise, safer to run, and much better suited to different squadrons and server setups.

---

### ✨ Headline Features

- 🎨 **Full theme management system** with custom colours, gradients, chart colours, header images, logos, presets, backups, and restores.
- 🌍 **Language support groundwork** with English, German, and Italian translation files plus a translation template for future languages.
- ⚙️ **Expanded admin panel** with feature toggles, API settings, privacy/SEO tools, update controls, backups, logs, custom links, and dashboard overview cards.
- 📡 **Expanded DCSServerBot API support** including newer endpoint data, server attendance, server details, traps/LSO data, and server-scoped views.
- 🧭 **Server filter selector** across the frontend, allowing users to view all servers or a specific detected server.
- 📊 **New charts and visual controls** for leaderboard, homepage, pilot statistics, and theme-customisable chart colours.
- 🛡️ **Large security hardening pass** covering CSRF, session handling, file permissions, backup safety, API config exposure, uploads, and demo mode.
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
- 🌍 Added translation coverage for leaderboard text.

---

### 👨‍✈️ Pilot Statistics

- 🔍 Improved pilot search result styling.
- 🎨 Fixed pilot search highlights and selected pilot views to follow theme colours.
- 📊 Updated pilot statistics charts to use configured theme colours.
- 🛬 Added **Carrier Landings / LSO** data from the `/traps` API.
- 📋 Added a dedicated carrier landing card under the existing pilot data.
- 🧾 Carrier landing table includes grade, points, wire, aircraft, location, and time.
- 🌍 Added translation coverage for pilot search, result selection, layered pilot views, and chart labels.

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
