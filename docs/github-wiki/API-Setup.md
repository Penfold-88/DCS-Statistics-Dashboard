# 🔌 API Setup

The dashboard pulls live and historical data from the DCSServerBot REST API.

---

## ⚙️ Where To Configure

Go to:

```text
Admin Panel -> Settings -> API Settings
```

---

## 🌐 API Host Format

Typical local network example:

```text
192.168.0.5:9876
```

Domain example:

```text
api.yourdomain.com:9876
```

The dashboard will build the API base URL from the host value.

---

## 🔑 API Key

DCSServerBot can optionally use an API key.

If your DCSServerBot REST API has an API key configured, enter it in:

```text
DCSServerBot API Key
```

If your bot has no API key configured, leave the field blank.

For Docker or server-managed installs, you can keep the API key out of the saved JSON config by setting an environment variable:

```env
DCSBOT_API_KEY=your_key_here
```

When `DCSBOT_API_KEY` is present, the dashboard uses it for API requests and ignores the saved API key field. The API Settings page will show a notice when this override is active.

---

## 🔒 HTTP And HTTPS

Some DCSServerBot installs run over HTTP only.

The dashboard is designed to remain backwards compatible with HTTP API hosts while still supporting HTTPS where available.

If the website is HTTPS and the API is HTTP, browser-side calls can fail due to mixed-content rules. The dashboard avoids this by routing API requests through the server-side PHP proxy.

---

## 🩺 API Health

Use the API health/debug page from the API Settings screen to check endpoint availability.

Some endpoints may require request parameters. A 422 response can mean the endpoint exists but requires a valid request body.

---

## ⚡ Cached Endpoints

The dashboard caches heavier API responses to improve load times.

Examples include:

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

Live status endpoints are not cached:

- `/servers`
- `/current_server`

---

## 🧹 Clear API Cache

Go to:

```text
Admin Panel -> Settings -> API Settings -> Clear API Cache
```

Cache files are runtime files and should not be committed to Git.
