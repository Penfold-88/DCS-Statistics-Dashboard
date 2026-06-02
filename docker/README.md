# Docker Deployment

These files are for Docker Desktop on Windows and macOS, and Docker Engine on Linux.

From the repository root, start the dashboard with:

```bash
docker compose -f docker/docker-compose.yml up -d
```

Open the dashboard at:

```text
http://localhost:8080
```

Useful commands:

```bash
docker compose -f docker/docker-compose.yml logs -f
docker compose -f docker/docker-compose.yml down
docker compose -f docker/docker-compose.yml exec dcs-stats sh
```

To change the public port, create or edit `.env` in the repository root:

```text
WEB_PORT=8090
```
