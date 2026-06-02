# Docker Deployment

These files are for Docker Desktop on Windows and macOS, and Docker Engine on Linux.

## Quick Start

Copy and run these commands:

```bash
git clone https://github.com/Penfold-88/DCS-Statistics-Dashboard.git
cd DCS-Statistics-Dashboard
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

Useful commands:

```bash
docker compose -f docker/docker-compose.yml logs -f
docker compose -f docker/docker-compose.yml down
docker compose -f docker/docker-compose.yml up -d --build
docker compose -f docker/docker-compose.yml exec dcs-stats sh
```

To change the public port, create or edit `.env` in the repository root:

```text
WEB_PORT=8090
```
