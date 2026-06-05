# 🧪 Demo Mode

Demo mode is for public showcase installs where visitors can view the admin panel without being able to change sensitive settings.

---

## 🟨 Enable Demo Mode

Create a hidden file named:

```text
.demo
```

in the main dashboard directory.

The protected demo admin user should be configured inside `.demo`.

Do not commit `.demo` to Git.

---

## 🔐 What Demo Mode Does

Demo mode can:

- Show a frontend demo banner
- Show an admin demo banner
- Restrict sensitive admin actions
- Mask API host/key values
- Lock update tools
- Lock export and backup tools
- Lock metadata edits
- Lock API settings edits
- Protect the configured demo admin from deletion/deactivation

---

## 📣 Demo Banner

The frontend demo banner explains that demo data is provided for showcase purposes.

The admin demo banner explains that sensitive data is restricted and demo data may reset.

---

## ✅ Recommended Demo Setup

For a public demo:

1. Create a dedicated demo install.
2. Use demo-safe API/data only.
3. Add `.demo`.
4. Configure the protected demo admin user in `.demo`.
5. Confirm edits are blocked.
6. Confirm sensitive data is masked.
7. Reset demo data on a schedule if required.
