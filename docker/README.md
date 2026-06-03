# Docker Deployment

These files are for Docker Desktop on Windows and macOS, and Docker Engine on Linux.

## Quick Start

Copy and run these commands:

```bash
git clone https://github.com/Penfold-88/DCS-Statistics-Dashboard.git
cd DCS-Statistics-Dashboard
cp .env.example .env
docker compose -f docker/docker-compose.yml up -d --build
```

Open the dashboard at:

```text
http://localhost:8080
```

The image includes the dashboard requirements needed for GitHub updates and ZIP installs:

- PHP cURL extension
- PHP ZIP / ZipArchive extension
- cURL command-line tool
- unzip / zip tools
- GitHub HTTPS certificate support
- git command-line tool for Git status checks
- production OPcache defaults for public internet-facing installs

Useful commands:

```bash
docker compose -f docker/docker-compose.yml logs -f
docker compose -f docker/docker-compose.yml down
docker compose -f docker/docker-compose.yml up -d --build
docker compose -f docker/docker-compose.yml exec dcs-stats sh
```

To change the public port, edit `.env` in the repository root:

```text
WEB_PORT=8090
```

## OPcache and Updates

Docker uses production OPcache defaults:

```text
OPCACHE_VALIDATE_TIMESTAMPS=0
OPCACHE_REVALIDATE_FREQ=0
```

After running a dashboard update, restart the container so PHP loads the updated files:

```bash
docker compose -f docker/docker-compose.yml restart
```

For local Docker development only, you can allow PHP to detect file edits by adding this to `.env`:

```text
OPCACHE_VALIDATE_TIMESTAMPS=1
OPCACHE_REVALIDATE_FREQ=2
```
