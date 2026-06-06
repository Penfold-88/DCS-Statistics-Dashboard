# Contributing To DCS Statistics Dashboard

Thanks for wanting to help improve DCS Statistics Dashboard.

This project is built for DCS squadrons, server owners, and communities who want a clean way to view DCSServerBot statistics. Contributions are welcome, whether that is a bug fix, translation, documentation improvement, Docker improvement, theme polish, or a new dashboard feature.

## Before You Start

Please check the existing issues first:

https://github.com/Penfold-88/DCS-Statistics-Dashboard/issues

If you are planning a larger change, please open an issue or discussion first so the approach can be agreed before you spend a lot of time on it.

## Security Issues

Please do not report security vulnerabilities in a public issue.

Use the project security policy instead:

https://github.com/Penfold-88/DCS-Statistics-Dashboard/security/policy

Security reports should be handled privately first so users can be protected before details are shared publicly.

## Branches

The project generally uses:

- `master` for stable public releases.
- `Dev-20-05-26` for active development and testing.

Most pull requests should target the active development branch unless the maintainer asks otherwise.

## Local Development

The dashboard can be run with a normal PHP web server, XAMPP, Apache/nginx, or Docker.

For a simple local PHP/XAMPP style setup:

1. Clone or download the repository.
2. Place the `dcs-stats` folder in your web root.
3. Open the dashboard in your browser.
4. Run the installer if the site has not been configured yet.

For Docker:

```bash
git clone https://github.com/Penfold-88/DCS-Statistics-Dashboard.git
cd DCS-Statistics-Dashboard
docker compose -f docker/docker-compose.yml up -d --build
```

Then open:

```text
http://localhost:8080
```

An `.env` file is optional for Docker. The defaults should work for most users. Copy `.env.example` to `.env` only if you need to change things such as the public port or PHP limits.

## What Not To Commit

Please do not commit local runtime data, private settings, or generated files.

Avoid committing:

- `site-config/data/*.json`
- API keys or private API hosts.
- `.dev`
- `.demo`
- `.php-sessions`
- cache files.
- backup ZIPs.
- preview server logs.
- local uploaded logos or private images.
- generated PDF review files.

If a change needs a new default setting, add it through the installer/defaults instead of committing your own live config file.

## Code Style

Please keep changes consistent with the existing project style.

General guidance:

- Keep changes focused.
- Prefer clear PHP over clever PHP.
- Avoid unnecessary rewrites of unrelated files.
- Keep user-facing text translatable where possible.
- Escape output that is shown in HTML.
- Use CSRF checks for state-changing admin actions.
- Avoid exposing private config, logs, backups, or runtime data.
- Keep public endpoints limited to harmless values.
- Do not add third-party CDNs unless there is a strong reason.

## Translations

The dashboard supports multiple languages.

When adding or changing user-facing text:

- Add the English text to the language system.
- Update existing translations where practical.
- Keep the translation template updated for future translators.

If you cannot translate a new string yourself, add a clear English fallback and mention it in the pull request.

## Themes And UI Changes

The dashboard has a configurable theme system, so please avoid hardcoded colours unless they are truly required.

When changing frontend UI:

- Use existing theme variables where possible.
- Check desktop and mobile layouts.
- Make sure long names, translated text, and large server data still fit cleanly.
- Keep admin pages readable in dark themes.

## API And Performance Changes

Some DCSServerBot endpoints can return large responses, especially on busy servers.

When adding API usage:

- Avoid unnecessary calls.
- Respect existing feature toggles.
- Use cache helpers where appropriate.
- Do not call heavy endpoints if the related cards or sections are disabled.
- Consider server filtering where it makes sense.
- Keep the API health/debug pages safe and admin-only.

## Testing Checklist

Before opening a pull request, please test the parts you changed.

Useful checks:

- Fresh install opens the installer when config files are missing.
- Existing installs keep their settings after update.
- Admin login/logout still works.
- Site feature toggles still hide the right sections.
- Theme changes still apply across frontend and admin pages.
- Language selection still works.
- Docker still starts if Docker files were changed.
- No local data files are included in the commit.

For PHP changes, run syntax checks on edited PHP files where possible:

```bash
php -l path/to/file.php
```

## Pull Request Guidelines

Please include:

- A short summary of what changed.
- Why the change was made.
- Any testing you performed.
- Screenshots for UI changes, if helpful.
- Notes about translations, Docker, installer changes, or update behaviour.

Small, focused pull requests are easier to review and merge.

## Documentation

If you add or change a feature, please consider updating:

- `README.md`
- `CHANGELOG.md`
- Relevant wiki documentation.
- Installer or admin help text, if the feature affects setup.

## Questions And Support

For general help and community support:

https://discord.gg/uTk8uQ2hxC

Thanks again for helping make the dashboard better.
