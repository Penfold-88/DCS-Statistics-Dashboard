# 📚 Publishing The Wiki

GitHub Wikis are stored in a separate repository from the main project.

For this project, the wiki repository will normally be:

```text
https://github.com/Penfold-88/DCS-Statistics-Dashboard.wiki.git
```

---

## 🚀 First-Time Publish

From a working folder outside the main dashboard repository:

```powershell
git clone https://github.com/Penfold-88/DCS-Statistics-Dashboard.wiki.git
cd DCS-Statistics-Dashboard.wiki
```

Copy the files from:

```text
docs/github-wiki/
```

into the cloned wiki folder.

Then publish:

```powershell
git status
git add .
git commit -m "Add project wiki"
git push
```

---

## 🔄 Updating The Wiki Later

When dashboard features change:

1. Update the matching page in `docs/github-wiki/`.
2. Copy the changed page into the `.wiki.git` clone.
3. Commit and push the wiki repository.

---

## ⚠️ Important Note

The main repository and the wiki repository have separate Git histories.

Committing wiki source files to the main repository does not automatically update the public GitHub Wiki. The files still need to be copied or pushed to the `.wiki.git` repository.
