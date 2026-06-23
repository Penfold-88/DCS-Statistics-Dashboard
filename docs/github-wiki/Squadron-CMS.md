# 🧱 Squadron CMS

The Squadron CMS is an optional V1.3 feature set that lets a squadron build a lightweight public website around the existing DCS Statistics Dashboard.

The dashboard remains the default experience. The CMS adds custom pages, galleries, downloads, reusable live widgets, embeds, and menu control without requiring a separate CMS platform.

---

## ✅ What The CMS Adds

| Feature | Purpose |
| --- | --- |
| CMS Settings | Enable/disable CMS and choose an optional CMS landing page |
| CMS Pages | Create custom public pages with drafts, publishing, previews, duplication, SEO fields, and sharing metadata |
| Media Library | Upload JPG, PNG, and WebP images for page content and galleries |
| Image Galleries | Build reusable gallery blocks with a large image, thumbnail selector, captions, and lightbox viewing |
| Downloads | Create public download cards for externally hosted files such as mission packs, kneeboards, SOPs, briefing documents, mod lists, and templates |
| Widgets | Add live dashboard data blocks to CMS pages |
| Embeds | Copy approved iframe code for live widgets on external websites |
| Menu Manager | Place CMS pages, downloads, dashboard pages, integrations, and custom links into public navigation |

---

## 🧭 Where To Find It

In the admin panel:

```text
Admin Panel -> CMS Options
```

CMS Options contains:

- CMS Settings
- Pages
- Embeds
- Galleries
- Downloads

The public menu is managed separately:

```text
Admin Panel -> Website Options -> Menu Manager
```

---

## 🚦 Enable The CMS

1. Open **CMS Options -> CMS Settings**.
2. Enable CMS.
3. Save settings.
4. Create and publish pages, galleries, downloads, or embeds as needed.

If no CMS landing page is selected, the normal Statistics Dashboard remains the homepage.

If a published CMS page is selected as the landing page:

- `index.php` opens that CMS page.
- The Statistics Dashboard remains available as `index.php?view=statistics`.
- The Menu Manager automatically exposes a Statistics Dashboard menu item.

---

## 📄 CMS Pages

CMS pages support:

- Draft and published states
- Admin-only draft preview
- Page duplication as a draft
- Navigation and landing-page badges
- Browser/sharing title
- Meta description
- Social sharing image
- Rich text formatting
- Links
- Images
- Image alignment
- Text alignment
- Galleries
- Live statistics widgets

Use CMS pages for:

- About Us
- Recruitment
- Server Rules
- Training
- Events or operations information
- Donations
- Server information
- Campaign briefings

---

## 🖼️ Media And Galleries

The media library accepts:

- JPG
- PNG
- WebP

Upload limits:

- 2 MB per image
- 6000 × 6000 maximum dimensions

Gallery features:

- Multi-upload
- Reusable named galleries
- Featured image display
- Horizontal thumbnail selector
- Captions
- Alt text
- Lightbox view
- Usage-aware deletion

Uploaded CMS media is stored under:

```text
dcs-stats/uploads/pages/
```

This is runtime content and should not be committed to Git.

---

## ⬇️ Downloads

Downloads are designed for externally hosted files. The dashboard stores the download card information, not the file itself.

Each download can include:

- Title
- External file URL
- Description
- Category
- Version
- File size
- Button label
- Sort order
- Published/hidden state
- Featured state

Public Downloads page features:

- Featured downloads section
- Category grouping
- Category filter buttons
- Responsive download cards

Good uses:

- Mission packs
- Mod lists
- Kneeboards
- SOP documents
- Briefing PDFs
- Templates
- Training material

---

## 🧩 Widgets

CMS pages can reuse dashboard data widgets.

Available widgets include:

- Server Status
- Summary Statistics
- Player Attendance
- Top Pilots
- Combat Statistics
- Top Squadrons
- Player Activity
- Top Theatres
- Top Missions
- Top Modules

Some widgets support:

- All servers
- Individual detected servers
- Count/limit controls
- Metric choices where relevant

Widgets only load data when present on a page.

---

## 🔌 External Embeds

Use **CMS Options -> Embeds** to copy iframe code for approved live widgets.

Embeds are useful when another website wants to show live dashboard information without copying data manually.

Current embed tools include:

- Server Status
- Dashboard statistics widgets

Embed controls can include:

- Widget type
- Server selection
- Metric/count options
- Preview
- Copyable iframe code
- Transparent/background-friendly output

---

## 🧭 Menu Manager

The Menu Manager automatically discovers:

- Statistics dashboard pages
- Published CMS pages
- CMS Downloads
- Discord link
- Squadron Homepage link
- Custom links

It supports:

- Drag ordering
- Custom labels
- Visibility toggles
- New-tab behaviour where relevant
- One-level parent/child dropdowns

Go to:

```text
Admin Panel -> Website Options -> Menu Manager
```

---

## 🔐 Permissions

CMS administration uses the existing admin permission system.

Air Boss admins always have full access.

LSO access can be controlled from:

```text
Admin Panel -> Global Options -> LSO Permissions
```

Enable **Manage CMS** if LSO users should be able to manage CMS settings, pages, galleries, downloads, media, previews, widgets, and embeds.

---

## 💾 Backups And Updates

Settings backup includes CMS data such as:

- CMS pages
- CMS galleries
- CMS downloads
- Menu configuration
- Site features
- Metadata

Normal updates preserve:

- `site-config/data/`
- `uploads/`
- theme customisations
- generated settings
- menu configuration

Do not commit runtime CMS data files or uploaded media to Git.

---

## ✅ Recommended First CMS Setup

For a basic squadron website:

1. Enable CMS.
2. Create an **About Us** page.
3. Create a **Server Rules** page.
4. Create a **Recruitment** page.
5. Add a gallery for screenshots.
6. Add downloads for mission packs or documents.
7. Add one or two live widgets where useful.
8. Use Menu Manager to order the public navigation.
9. Keep the Statistics Dashboard as the homepage unless you specifically want a custom landing page.

