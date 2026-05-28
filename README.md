# DCS Statistics Dashboard

Turn your DCSServerBot statistics into a clean, configurable web dashboard for your squadron or public DCS server.

[![DCSServerBot](https://img.shields.io/badge/Requires-DCSServerBot-green?style=for-the-badge)](https://github.com/Special-K-s-Flightsim-Bots/DCSServerBot)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue?style=for-the-badge)](#requirements)
[![Responsive](https://img.shields.io/badge/Responsive-Mobile%20Friendly-purple?style=for-the-badge)](#features)
[![License](https://img.shields.io/badge/License-MIT-lightgrey?style=for-the-badge)](LICENSE)

## Overview

DCS Statistics Dashboard is a PHP web dashboard for communities running [DCSServerBot](https://github.com/Special-K-s-Flightsim-Bots/DCSServerBot). It displays live server information, pilot statistics, leaderboards, squadron data, carrier trap/LSO results, credits, and configurable homepage insights.

The project is designed for normal web hosting, XAMPP/local installs, and Docker deployments. Most setup is handled through the browser-based installer and admin panel.

## What's New in V1.2

- Expanded DCSServerBot REST API support.
- Fully configurable site features from the admin panel.
- Theme presets plus detailed colour controls for squadrons.
- Header image/logo controls.
- Language groundwork with English, German, and Italian translation files.
- Server selector for viewing all servers or individual detected servers.
- Leaderboard chart controls and configurable chart colours.
- API health/debug page.
- File-based API response caching with a clear-cache button.
- Self-hosted Chart.js instead of loading from a CDN.
- Improved security headers, CSRF handling, file permissions, and API proxy validation.
- Admin setting import/export.
- Privacy and SEO settings.
- Maintenance mode page improvements.
- Update/version display improvements.

## Features

- Homepage summary cards for players, playtime, sorties, attendance, and activity.
- Top 5 pilot charts with Kills, K/D, and PvP K/D selector.
- Top 5 insight panels for theatres, missions, and modules.
- Leaderboard table and bar chart.
- Individual pilot search with layered pilot profile data.
- Carrier landings / LSO trap data on pilot profiles.
- Pilot credits page.
- Squadron homepage and squadron detail views.
- Servers page with live mission, weather, slots, extensions, and player information.
- Optional server scope selector across the front end.
- Custom Squadron Links dropdown in the navigation bar.
- Footer credits modal.
- Admin feature toggles for homepage, leaderboard, pilot, credits, squadron, and server sections.
- Theme editor with presets, custom colours, chart colours, header image, and logo options.
- Site metadata editor for keywords, description, and search engine blocking.
- Admin backup/export tools for portable site settings.
- Update tool with stable/dev channel support.

## Requirements

### Dashboard

- PHP 7.4 or newer.
- PHP cURL extension.
- PHP ZIP extension for update/backup features.
- A web server such as Apache, Nginx, IIS, XAMPP, or compatible shared hosting.
- Writable permissions for the dashboard's `site-config/data/` directory.

### DCSServerBot

- DCSServerBot with the REST API enabled.
- Network access from the web server to the DCSServerBot REST API host and port.
- Optional but recommended: configure a DCSServerBot API key and enter it in the dashboard API settings.

## Installation

### Standard Web Hosting

1. Download the latest release from GitHub.
2. Upload the `dcs-stats/` folder to your web server.
3. Open the dashboard in your browser, for example:

   ```text
   https://yourdomain.com/dcs-stats/
   ```

4. The installer should open automatically if the dashboard is not configured.
5. Create the first admin account. This user becomes the Air Boss.
6. Enter your DCSServerBot API host and port.
7. Test the API connection and save.

### XAMPP / Local Testing

1. Copy the `dcs-stats/` folder into your XAMPP `htdocs` folder.
2. Start Apache in XAMPP.
3. Open:

   ```text
   http://localhost/dcs-stats/
   ```

4. Complete the installer.

A full XAMPP guide is available in the project wiki.

### Docker Deployment

The Docker files are included and will be reworked in a future release. The current setup is kept available for users already deploying this way.

```bash
# Clone the repository
git clone https://github.com/Penfold-88/DCS-Statistics-Dashboard.git
cd DCS-Statistics-Dashboard

# Start with Docker
docker compose up -d

# Access at http://localhost:8080
```

Docker will create required folders, set permissions, and start the web container.

Useful Docker commands:

```bash
# View logs
docker compose logs -f

# Stop the container
docker compose down

# Update to latest version
docker compose pull
docker compose up -d

# Access container shell
docker compose exec dcs-stats-web bash
```

To change the public port, edit `.env`:

```bash
WEB_PORT=8090
```

## First-Time Setup

After installation, go to:

```text
/dcs-stats/site-config/
```

The admin panel lets you configure:

- API host, timeout, refresh rate, cache TTL, and optional API key.
- Site feature toggles.
- Theme colours and presets.
- Header background/logo settings.
- Metadata, privacy, and SEO settings.
- Maintenance mode.
- Custom navigation links.
- Language selection.
- Update channel and version checks.
- Settings backup and restore.

## API Configuration

The API host should usually be entered as:

```text
192.168.0.5:9876
```

or:

```text
your-api-domain.com:9876
```

The dashboard will auto-detect HTTP/HTTPS where possible.

If DCSServerBot has an API key configured, add it in:

```text
Admin Panel -> Settings -> API Settings
```

If the API has no key configured, leave the field blank.

## API Caching

The dashboard uses file-based caching for heavier API responses.

Cached endpoints include:

- `/serverstats`
- `/server_attendance`
- `/leaderboard`
- `/squadrons`
- `/squadron_members`
- `/squadron_credits`
- `/stats`
- `/player_info`
- `/traps`
- `/weaponpk`

Live server status endpoints are not cached:

- `/servers`
- `/current_server`

The cache can be cleared from:

```text
Admin Panel -> Settings -> API Settings -> Clear API Cache
```

Cache files are stored locally in:

```text
dcs-stats/site-config/data/api-cache/
```

These files are runtime data and should not be committed to Git.

## Admin Roles

| Role | Purpose |
| --- | --- |
| Air Boss | Full admin access. Can manage settings, updates, users, API, themes, and site configuration. |
| LSO | Limited admin role for landing signal officer permissions and relevant tools. |

The first account created by the installer becomes the main administrator.

## Updates

The update tool is available from:

```text
Admin Panel -> Settings -> Update
```

The updater supports:

- Current installed version display.
- Stable and dev channel handling.
- GitHub branch checks.
- Backup creation before updates.
- Downgrade/version list where available.

Stable is the default channel. A hidden `.dev` file enables the configured development branch.

## Security Notes

The dashboard includes:

- Password hashing.
- CSRF protection for admin actions.
- Session security controls.
- Role checks for admin pages.
- Security headers.
- API endpoint allow-listing in the proxy.
- Optional DCSServerBot API key forwarding.
- Protected runtime data folders.
- Safer file permissions for generated admin data.

Recommended production setup:

- Use HTTPS for the public dashboard.
- Keep `site-config/data/` protected from direct web access.
- Use a DCSServerBot API key where possible.
- Restrict access to the DCSServerBot API port.
- Keep PHP and web server packages updated.
- Back up settings before updates.

## Privacy And SEO

The Privacy & SEO admin section lets you configure:

- Site description.
- Site keywords.
- Search engine indexing preference.

The dashboard also sends minimal install/version check-in data to the project maintainer's central service so support can be aligned with versions in use. The check-in is intended to contain only branch/version style information, not personal user data.

## Troubleshooting

### Installer Does Not Open

The dashboard should redirect to the installer when required configuration files are missing. If it does not:

- Confirm `site-config/data/` is writable.
- Confirm `site-config/data/users.json` does not already exist from another install.
- Clear browser cache.
- Check PHP error logs.

### API Connection Fails

- Check the DCSServerBot REST API is running.
- Confirm the dashboard server can reach the API host and port.
- Check firewall/port forwarding.
- If using Docker, the API host may need to be `host.docker.internal` on Windows/Mac.
- If using an API key, make sure the dashboard key matches the DCSServerBot key.

### Charts Or Styles Look Old After Updating

- Clear browser cache.
- Use the API Settings clear-cache button.
- Restart Apache/PHP if OPcache is enabled and still serving old PHP.

### Update Tool Fails

- Ensure the PHP ZIP extension is enabled.
- Ensure update and backup folders are writable.
- Check that the server can reach GitHub.
- Check the admin update log output.

## Project Structure

```text
DCS-Statistics-Dashboard/
├── dcs-stats/                  Main web application
│   ├── site-config/            Admin panel and setup tools
│   │   ├── api/                Admin API endpoints
│   │   ├── data/               Runtime settings and admin data
│   │   ├── css/                Admin styling
│   │   └── js/                 Admin JavaScript
│   ├── js/                     Front-end JavaScript and vendor scripts
│   ├── lang/                   Translation files
│   ├── index.php               Homepage
│   ├── leaderboard.php         Leaderboard page
│   ├── pilot_statistics.php    Pilot search and profile page
│   ├── pilot_credits.php       Pilot credits page
│   ├── squadrons.php           Squadron pages
│   └── servers.php             Server status page
├── Dockerfile
├── docker-compose.yml
├── CHANGELOG.md
└── README.md
```

## Runtime Files Not To Commit

The following are generated locally and should not be committed:

- `.dev`
- `.php-sessions/`
- `dcs-stats/site-config/data/*.json`
- `dcs-stats/site-config/data/api-cache/`
- `dcs-stats/api_config.json`
- `dcs-stats/site_config.json`
- `dcs-stats/menu_config.json`
- `dcs-stats/custom_theme.css`
- `dcs-stats/header_custom.css`

## Contributing

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
- Update translations where new visible text is added.
- Update documentation when behaviour changes.

## Credits

- [Special K's Flightsim Bots](https://github.com/Special-K-s-Flightsim-Bots) for DCSServerBot.
- VFS-252 Sky Pirates for testing, feedback, and original dashboard work.
- The DCS community for suggestions, bug reports, and real-world testing.
- Eagle Dynamics for DCS World.

## License

This project is licensed under the MIT License. See [LICENSE](LICENSE).

## Support

- Discord Support: [https://discord.gg/uTk8uQ2hxC](https://discord.gg/uTk8uQ2hxC)
- Wiki: [https://github.com/Penfold-88/DCS-Statistics-Dashboard/wiki](https://github.com/Penfold-88/DCS-Statistics-Dashboard/wiki)
- Issues: [https://github.com/Penfold-88/DCS-Statistics-Dashboard/issues](https://github.com/Penfold-88/DCS-Statistics-Dashboard/issues)
- Live Demo: [https://stats.skypirates.uk/](https://stats.skypirates.uk/)
