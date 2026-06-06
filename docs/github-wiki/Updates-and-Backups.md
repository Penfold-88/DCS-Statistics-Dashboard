# 🔄 Updates And Backups

The dashboard includes an admin updater and backup tools.

---

## 🧭 Update Page

Go to:

```text
Admin Panel -> Settings -> Update
```

The page shows:

- Dashboard version
- Installed build
- Update channel
- Current branch
- GitHub source
- Installed commit
- Installed date
- Last updated time
- Update status

---

## 📦 Manual Downloads

Fresh manual downloads show:

```text
V1.2 (Manual Download)
```

After the updater runs successfully, the installed build can include commit/date information from GitHub.

---

## 🛤️ Stable And Dev Channels

Stable is used by default.

If a `.dev` file exists, the updater can use the configured development branch.

Do not commit `.dev` to Git.

Admin-managed customisations are preserved during normal updates, including uploaded branding under `uploads/`, generated theme files, menu settings, API settings, and local data. Manual edits made directly to core dashboard/template files may be replaced by the updater, so keep custom work in the admin theme tools, `uploads/`, or `custom/` where possible.

---

## 💾 Backups

The updater creates backups before updates.

Manual backup tools are also available from the Update page.

Backup restore now includes safer rollback handling if a restore fails part-way through.

---

## 🚫 Runtime Files To Keep Out Of Git

Do not commit install-specific runtime files such as:

- API config
- Users
- Logs
- Sessions
- Install check-in state
- Cache files
- `.dev`
- `.demo`

---

## ✅ Before Updating

Recommended update process:

1. Create a manual backup.
2. Confirm API settings are known.
3. Run the update.
4. Check admin dashboard.
5. Check frontend homepage.
6. Check theme and logo.
7. Check feature toggles.
8. Check API health.
