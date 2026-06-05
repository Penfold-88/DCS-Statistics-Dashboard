# 🧰 Troubleshooting

Common issues and first checks.

---

## 🚪 Installer Does Not Open

Check:

- You are opening the correct install URL.
- Old config files are not already present.
- `site-config/data/` does not contain copied runtime files from another install.
- File permissions allow PHP to create required settings.

---

## 🎨 Admin Page Looks Unstyled

Check:

- CSS files are loading.
- The install path is correct.
- Browser cache is cleared.
- The web server is serving static assets correctly.

---

## 🩺 API Health Shows 422

HTTP 422 usually means the endpoint exists but needs request parameters.

For example, some POST endpoints require a request body.

---

## 🐢 Dashboard Loads Slowly

Check:

- API cache is enabled.
- Heavy sections are disabled if not needed.
- `/server_attendance` is not being called unnecessarily.
- The DCSServerBot API server is responding quickly.
- The web server can reach the API over the network.

---

## 🎨 Theme Resets After Update

Check:

- Theme files are not being overwritten manually.
- Runtime theme/config files are not committed to Git.
- Update preservation rules are working.
- Create a theme backup before large updates.

---

## 📦 ZIP / Update Errors

Check:

- PHP ZIP extension is enabled.
- Backup/update folders are writable.
- Web host allows ZIP operations.
- The update log output for the exact error.

---

## 📱 Mobile Cannot Access Local Preview

Check:

- The preview server is bound to the computer's LAN IP, not only `127.0.0.1`.
- Windows Firewall allows the port.
- Phone and PC are on the same network.
- Use a URL like:

```text
http://192.168.x.x:8094/
```

---

## 🧹 Cache Issues

Use:

```text
Admin Panel -> Settings -> API Settings -> Clear API Cache
```

Also clear browser cache after CSS or JavaScript changes.
