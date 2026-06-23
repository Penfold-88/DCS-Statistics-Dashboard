# ⚙️ Admin Panel

The admin panel controls the dashboard configuration, API connection, themes, feature toggles, CMS tools, updates, backups, users, languages, and privacy settings.

---

## 🔐 Access

Default admin URL:

```text
/dcs-stats/site-config/
```

The first admin created during install becomes the main **Air Boss** account.

---

## 🧭 Main Admin Areas

| Section | Purpose |
| --- | --- |
| Dashboard | Overview cards, update status, quick actions, system information |
| Activity Logs | Admin activity and system logs |
| Export Data | Data export tools |
| Admins | Admin user management |
| LSO Permissions | LSO permission management |
| API Settings | DCSServerBot API configuration |
| Site Features | Enable or disable dashboard sections |
| Menu Manager | Unified public navigation for statistics pages, CMS pages, downloads, integrations, and custom links |
| CMS Settings | Enable/disable CMS and choose an optional CMS landing page |
| CMS Pages | Create and publish custom website pages |
| CMS Galleries | Manage reusable CMS image galleries |
| CMS Downloads | Manage public download cards for externally hosted files |
| CMS Embeds | Copy iframe embed code for approved live widgets |
| Custom Links | Frontend Squadron Links menu |
| Privacy & SEO | Metadata and search engine settings |
| Language | Site language and translation uploads |
| Settings Backup | Whole-site settings import/export |
| Maintenance | Maintenance mode and allowed IPs |
| Update | GitHub update, version, and backup tools |
| Discord Link | Discord navigation/settings |
| Squadron Homepage | Squadron page configuration |
| Themes | Theme colours, images, logos, charts, CSS, and backups |

---

## 🧱 CMS Options

V1.3 adds optional CMS tools for squadrons that want a lightweight website around the dashboard.

CMS Options can include:

- CMS Settings
- Pages
- Embeds
- Galleries
- Downloads

See [Squadron CMS](Squadron-CMS) for setup and feature details.

---

## 🏠 Go To Website

The admin sidebar includes **Go To Website** at the top. This takes the user back to the public dashboard homepage.

---

## 👥 Roles

Roles control access to different admin areas. The main install admin should retain full control.

Use the Admins and Permissions sections to manage access carefully.

LSO users can be granted CMS management access with the **Manage CMS** permission.

---

## 🧪 Demo Mode

When `.demo` exists, sensitive actions are restricted for public showcase installs.

See [Demo Mode](Demo-Mode).
