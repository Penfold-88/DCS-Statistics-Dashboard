# ✅ Tester Checklist

Use this checklist before a public release or when testing a new install.

---

## 🚀 Fresh Install

- [ ] Download fresh copy from GitHub.
- [ ] Install on XAMPP or local PHP server.
- [ ] Install on hosted/Linux server if available.
- [ ] Confirm installer opens automatically.
- [ ] Choose language during install.
- [ ] Create first admin.
- [ ] Save API settings.
- [ ] Confirm homepage loads.
- [ ] Confirm admin panel loads.

---

## 🔌 API

- [ ] API settings save correctly.
- [ ] Required API key is validated during installation and API configuration.
- [ ] API health page opens.
- [ ] Servers endpoint works.
- [ ] Leaderboard data loads.
- [ ] Pilot statistics search works.
- [ ] Squadrons data loads.
- [ ] Traps/LSO data loads where available.

---

## 🖥️ Frontend

- [ ] Homepage cards display correctly.
- [ ] Attendance cards display correctly.
- [ ] Top 5 pilots selector works.
- [ ] Leaderboard table works.
- [ ] Leaderboard chart works.
- [ ] Pilot search works.
- [ ] Servers page shows expected server cards.
- [ ] Squadrons page shows real squadron data.
- [ ] Custom links menu works.
- [ ] Menu Manager ordering, labels, visibility, and dropdowns appear correctly on desktop.
- [ ] Menu Manager ordering, labels, visibility, and dropdowns appear correctly on mobile.
- [ ] Footer credits modal works.
- [ ] Mobile layout is usable.

---

## ⚙️ Admin

- [ ] Go To Website button works.
- [ ] Site Features toggles work.
- [ ] Detected server toggles work.
- [ ] Theme presets apply.
- [ ] Custom colours apply.
- [ ] Header image upload works.
- [ ] Logo options work.
- [ ] Language switch works.
- [ ] Privacy & SEO saves.
- [ ] Settings backup export/import works.
- [ ] Maintenance mode works.
- [ ] Update page loads.

---

## 🧱 CMS

- [ ] CMS can be enabled and disabled.
- [ ] CMS disabled state leaves the statistics dashboard working normally.
- [ ] CMS page can be created, saved as draft, previewed, published, duplicated, and deleted.
- [ ] CMS landing page can be selected and falls back safely if unavailable.
- [ ] Rich text editor formatting works for headings, lists, quotes, links, and alignment.
- [ ] Media upload accepts valid JPG, PNG, and WebP images.
- [ ] Media upload rejects invalid files and oversized images.
- [ ] Inserted page images stay inside the public page card on mobile.
- [ ] Image galleries can upload multiple images and render with featured image, thumbnails, captions, and lightbox.
- [ ] Downloads can be enabled, created, edited, featured, categorised, filtered, and hidden.
- [ ] Downloads page links open external files in a new tab.
- [ ] CMS widgets render on public pages and remain inside the page card on mobile.
- [ ] Server-scoped widgets show the correct server when selected.
- [ ] Embeds page shows a live preview and copyable iframe code.
- [ ] Menu Manager discovers published CMS pages and CMS Downloads.
- [ ] LSO Manage CMS permission grants/removes CMS admin access as expected.

---

## 🔐 Security

- [ ] Admin pages require login.
- [ ] `/site-config/data/` is not publicly browseable.
- [ ] `/backups/` is not publicly browseable.
- [ ] API key is not visible publicly.
- [ ] `.demo` is not committed.
- [ ] `.dev` is not committed.
- [ ] Runtime JSON files are not committed.
- [ ] CMS uploads and CMS runtime JSON files are not committed.

---

## 🔄 Update Test

- [ ] Install an older version.
- [ ] Configure API settings.
- [ ] Apply custom theme.
- [ ] Add custom links.
- [ ] Add a CMS page, gallery, and download if testing V1.3.
- [ ] Run update.
- [ ] Confirm settings were preserved.
- [ ] Confirm theme was preserved.
- [ ] Confirm users were preserved.
- [ ] Confirm CMS pages, galleries, downloads, menu settings, and uploads were preserved.
- [ ] Confirm installed build/version display is sensible.
