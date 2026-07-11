# 🎚️ Site Features

The Site Features page lets admins control which dashboard sections are visible. CMS-specific availability is managed from CMS Options, while the dashboard feature toggles remain focused on statistics pages and API-backed dashboard sections.

---

## ⚙️ Where To Configure

Go to:

```text
Admin Panel -> Settings -> Site Features
```

---

## 🧩 Major Feature Areas

Feature controls include:

- Navigation items
- Homepage sections
- Attendance cards
- Top 5 insights
- Leaderboard columns
- Leaderboard chart
- Pilot features
- Credits system
- Squadron system
- Server features
- Server filter selector
- Detected server cards

CMS features are managed from:

```text
Admin Panel -> CMS Options -> CMS Settings
Admin Panel -> CMS Options -> Galleries
Admin Panel -> CMS Options -> Downloads
```

---

## 🏠 Homepage Controls

The homepage can show or hide:

- Main stat cards
- Attendance cards
- Top 5 pilots chart
- Combat statistics chart
- Top 5 insights
- Top theatres
- Top missions
- Top modules

If a related section is disabled, the dashboard should avoid unnecessary API calls for that data.

---

## 🧱 CMS Features

The CMS is optional. When enabled, it can add:

- Custom pages
- Image galleries
- Downloads
- Live widgets
- Embeds
- Optional CMS landing page
- Menu Manager discovery for published CMS content

See [Squadron CMS](Squadron-CMS).

---

## 🛰️ Server Filter

The frontend server selector lets users view:

- All Servers
- Individual detected servers

If the server filter is disabled, the dashboard uses all enabled server data.

Detected server visibility is controlled under the server feature settings.

---

## 🔄 Update Behaviour

Fresh installs should enable features by default.

Updates should preserve the user's existing feature choices.
