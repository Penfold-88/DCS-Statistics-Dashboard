# 🐳 Docker Deployment

Docker support is provided for users who prefer to run the dashboard in a container instead of installing directly into Apache, Nginx, XAMPP, or shared hosting.

The same Docker setup is intended to work on:

- 🪟 Windows with Docker Desktop
- 🍎 macOS with Docker Desktop
- 🐧 Linux with Docker Engine and Docker Compose

---

## 📁 Docker File Location

Docker files are stored in:

```text
docker/
```

The main files are:

```text
docker/Dockerfile
docker/docker-compose.yml
docker/Dockerfile.dockerignore
docker/README.md
```

---

## 🚀 Quick Start

Copy and run:

```bash
git clone https://github.com/Penfold-88/DCS-Statistics-Dashboard.git
cd DCS-Statistics-Dashboard
docker compose -f docker/docker-compose.yml up -d --build
```

Then open:

```text
http://localhost:8080
```

---

## 📦 Included Update Requirements

The Docker image includes the tools and PHP extensions needed for the dashboard updater and GitHub checks:

- PHP cURL extension
- PHP ZIP / ZipArchive extension
- cURL command-line tool
- zip and unzip tools
- GitHub HTTPS certificate support
- git command-line tool for Git status checks
- production OPcache defaults for public internet-facing installs

An `.env` file is optional. Docker uses safe defaults if you do not create one. Copy `.env.example` to `.env` only if you want to change runtime settings such as the public port, PHP limits, OPcache behaviour, logs, health checks, resource limits, or the optional `DCSBOT_API_KEY` secure API key override. Required PHP extensions and command-line tools are built into the Docker image.

To keep the DCSServerBot API key out of `site-config/data/api_config.json`, set this in `.env`:

```env
DCSBOT_API_KEY=your_key_here
```

When this variable is set, it overrides the saved API key field in the dashboard admin panel.

---

## ▶️ Start From An Existing Checkout

From the repository root, run:

```bash
docker compose -f docker/docker-compose.yml up -d
```

Then open:

```text
http://localhost:8080
```

---

## 🔧 Change The Public Port

Copy the example environment file:

```bash
cp .env.example .env
```

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Then edit `.env` in the repository root:

```text
WEB_PORT=8090
```

Then restart:

```bash
docker compose -f docker/docker-compose.yml down
docker compose -f docker/docker-compose.yml up -d --build
```

---

## 🧰 Useful Commands

View logs:

```bash
docker compose -f docker/docker-compose.yml logs -f
```

Stop the dashboard:

```bash
docker compose -f docker/docker-compose.yml down
```

Open a shell inside the container:

```bash
docker compose -f docker/docker-compose.yml exec dcs-stats sh
```

---

## ⚡ OPcache and Updates

Docker uses production OPcache defaults:

```text
OPCACHE_VALIDATE_TIMESTAMPS=0
OPCACHE_REVALIDATE_FREQ=0
```

This reduces repeated file checks on public installs.

After using the dashboard updater, restart the container so PHP loads the updated files:

```bash
docker compose -f docker/docker-compose.yml restart
```

For local Docker development only, add this to `.env` if you want PHP to detect edits without a restart:

```text
OPCACHE_VALIDATE_TIMESTAMPS=1
OPCACHE_REVALIDATE_FREQ=2
```

---

## 🔄 Updates

If you update using the dashboard updater, Docker files in both the old root location and the new `docker/` folder are treated as user-customisable files.

That means:

- Existing Docker users are less likely to lose local Docker edits.
- Non-Docker users are not affected.
- Docker is not required for normal web installs.

---

## 🛡️ Security Notes

The Docker Nginx config blocks direct access to sensitive runtime data, including:

- `data/`
- `backups/`
- `site-config/data/`
- `site-config/theme_backups/`
- `.git/`
- direct `.json`, `.log`, and `.sql` files unless explicitly allowed

This matters because `.htaccess` rules do not protect files when running under Nginx.

---

## ✅ First Install

On a fresh Docker install, open:

```text
http://localhost:8080/site-config/install.php
```

Create the first admin user, configure the API, then open the main dashboard.
