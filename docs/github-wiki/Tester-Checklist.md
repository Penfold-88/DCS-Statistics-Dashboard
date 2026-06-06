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
- [ ] Optional API key field works when used.
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

## 🔐 Security

- [ ] Admin pages require login.
- [ ] `/site-config/data/` is not publicly browseable.
- [ ] `/backups/` is not publicly browseable.
- [ ] API key is not visible publicly.
- [ ] `.demo` is not committed.
- [ ] `.dev` is not committed.
- [ ] Runtime JSON files are not committed.

---

## 🔄 Update Test

- [ ] Install an older version.
- [ ] Configure API settings.
- [ ] Apply custom theme.
- [ ] Add custom links.
- [ ] Run update.
- [ ] Confirm settings were preserved.
- [ ] Confirm theme was preserved.
- [ ] Confirm users were preserved.
- [ ] Confirm installed build/version display is sensible.
