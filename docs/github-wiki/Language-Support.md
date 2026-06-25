# 🌍 Language Support

The dashboard includes translation groundwork for frontend and admin areas.

---

## 🗣️ Included Languages

Current included languages:

- English
- German
- Italian
- Spanish

---

## ⚙️ Where To Configure

Go to:

```text
Admin Panel -> Settings -> Language
```

The installer also includes language selection.

---

## 📄 Translation Files

Translation files live in:

```text
dcs-stats/lang/
```

Current files include:

```text
en.php
de.php
it.php
es.php
translation-template.json
```

---

## ➕ Adding A New Language

1. Copy `translation-template.json`.
2. Translate the values.
3. Upload through the Language admin page if supported.
4. Test frontend and admin pages.
5. Check mobile layouts, especially longer translated headings.

---

## 🧹 Translation Maintenance

When new visible text is added to the dashboard, translation files should be updated at the same time.

This helps avoid mixed English text on non-English installs.
