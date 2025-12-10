# Railway Quick Start

## What Changed

✅ **Railway Entrypoint Script** (`railway-entrypoint.sh`)
- Dynamically configures Apache to listen on Railway's `PORT` env var
- Updates both IPv4 and IPv6 listeners

✅ **Database Configuration** (`src/config.php`)
- Supports Railway's MySQL environment variables (`MYSQLHOST`, `MYSQLDATABASE`, etc.)
- Falls back to docker-compose defaults for local development

✅ **Health Check** (`healthcheck.sh`)
- Uses PORT env var for health checks
- Works with Railway's dynamic port assignment

✅ **Dockerfile**
- Entrypoint configured for Railway
- Health check script included

## Deploy to Railway

### 1. Connect Repository
- Go to Railway dashboard
- Click "New Project" → "Deploy from GitHub repo"
- Select your repository

### 2. Add MySQL Service
- Click "+ New" → "Database" → "Add MySQL"
- Railway will auto-generate connection variables

### 3. Configure PHP Service
Railway will auto-detect the Dockerfile. Verify:
- **Build Command**: (auto-detected)
- **Start Command**: (auto-detected, uses entrypoint)
- **Health Check Path**: `/health.php`
- **Port**: Railway sets `PORT` automatically

### 4. Environment Variables (Auto-set by Railway)
Railway automatically provides:
- `PORT` - Port to listen on
- `MYSQLHOST` - MySQL host
- `MYSQLDATABASE` - Database name
- `MYSQLUSER` - Database user
- `MYSQLPASSWORD` - Database password
- `MYSQLPORT` - MySQL port

**No manual configuration needed!** The app reads these automatically.

### 5. Deploy
- Railway will build and deploy automatically
- Watch logs for: "Apache configured -- resuming normal operations"
- Check health: `https://your-app.railway.app/health.php`

## Verify Deployment

```bash
# Health check
curl https://your-app.railway.app/health.php
# Should return: {"status":"ok","timestamp":"..."}

# Main page
curl -I https://your-app.railway.app/
# Should return: HTTP/2 200
```

## Troubleshooting

### 502 Bad Gateway
1. Check Railway logs for Apache startup messages
2. Verify `PORT` is set (Railway sets this automatically)
3. Test health endpoint: `/health.php`

### Database Connection Failed
1. Verify MySQL service is running
2. Check Railway provides `MYSQLHOST`, `MYSQLDATABASE`, etc.
3. Check logs for connection errors

### Health Check Failing
1. Increase startup timeout in Railway settings (60s recommended)
2. Verify `/health.php` is accessible
3. Check Apache error logs in Railway

## Files Changed

- `railway-entrypoint.sh` - NEW: Configures Apache for Railway
- `healthcheck.sh` - NEW: Health check script
- `Dockerfile` - UPDATED: Railway entrypoint and healthcheck
- `src/config.php` - UPDATED: Railway MySQL env vars support
- `src/health.php` - NEW: Health endpoint

## Local Testing

Test Railway config locally:

```bash
# Set PORT (simulates Railway)
export PORT=8080

# Build and run
docker-compose build
docker-compose up

# Test
curl http://localhost:8080/health.php
```

## Next Steps

1. ✅ Push changes to GitHub
2. ✅ Railway will auto-deploy
3. ✅ Verify health endpoint responds
4. ✅ Test application functionality

That's it! Railway handles the rest automatically.

