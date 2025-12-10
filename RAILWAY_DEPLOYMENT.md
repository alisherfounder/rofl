# Railway Deployment Guide

## Railway-Specific Configuration

This application is configured for Railway deployment with dynamic PORT handling.

### Key Features

1. **Dynamic Port Configuration**: Automatically uses Railway's `PORT` environment variable
2. **Health Check Endpoint**: `/health.php` for Railway health checks
3. **MySQL Service**: Configured to work with Railway's MySQL service

## Railway Setup Steps

### 1. Create Services in Railway

#### PHP Application Service
1. Connect your GitHub repository to Railway
2. Create a new service from the repository
3. Railway will auto-detect the Dockerfile

#### MySQL Service
1. Add a new MySQL service in Railway
   - Click "+ New" → "Database" → "Add MySQL"
2. **Connect MySQL to PHP Service** (CRITICAL):
   - Go to your PHP service settings
   - Click "Variables" tab
   - Click "+ New Variable"
   - Select "Reference Variable"
   - Choose your MySQL service
   - Railway will automatically inject: `MYSQLHOST`, `MYSQLDATABASE`, `MYSQLUSER`, `MYSQLPASSWORD`, `MYSQLPORT`

**Important**: 
- Railway automatically sets `PORT` for your PHP service
- Railway automatically provides MySQL connection variables when you reference the MySQL service
- **You don't need to manually set DB_HOST, DB_NAME, etc.** - the app reads Railway's `MYSQL*` variables automatically

### 3. Update Database Configuration

Update `src/config.php` to use Railway's MySQL service:

```php
define('DB_HOST', getenv('DB_HOST') ?: 'mysql');
define('DB_NAME', getenv('DB_NAME') ?: 'mydb');
define('DB_USER', getenv('DB_USER') ?: 'appuser');
define('DB_PASS', getenv('DB_PASS') ?: 'apppassword');
```

Or use Railway's built-in MySQL service variables:
- `MYSQLHOST`
- `MYSQLDATABASE`
- `MYSQLUSER`
- `MYSQLPASSWORD`
- `MYSQLPORT`

### 4. Configure Health Checks

Railway will automatically use `/health.php` for health checks. Ensure:
- Health check path: `/health.php`
- Health check port: Uses `PORT` env var (default 80)
- Startup timeout: 60 seconds (allows MySQL connection)

### 5. Deploy

Railway will:
1. Build the Docker image
2. Run the entrypoint script (`railway-entrypoint.sh`)
3. Configure Apache to listen on `PORT`
4. Start Apache in foreground mode

## How It Works

### Entrypoint Script (`railway-entrypoint.sh`)

The script:
1. Reads `PORT` environment variable (defaults to 80)
2. Updates Apache `ports.conf` to listen on `PORT`
3. Updates VirtualHost configuration to use `PORT`
4. Starts Apache in foreground mode

### Dockerfile

- Installs PHP extensions (pdo, pdo_mysql, mysqli)
- Installs curl for health checks
- Copies Apache configuration
- Copies application source code
- Sets up entrypoint script
- Exposes port 80 (Railway will map to `PORT`)

## Local Testing

Test Railway configuration locally:

```bash
# Set PORT environment variable
export PORT=8080

# Build and run
docker-compose build
docker-compose up

# Test health endpoint
curl http://localhost:8080/health.php
```

## Troubleshooting

### Issue: 502 Bad Gateway

**Check 1**: Verify PORT is set
```bash
# In Railway logs, check:
echo $PORT
```

**Check 2**: Verify Apache is listening
```bash
# Check Railway logs for:
[Wed Dec 10 03:58:00.739166 2025] [mpm_prefork:notice] Apache configured -- resuming normal operations
```

**Check 3**: Test health endpoint
```bash
curl https://your-app.railway.app/health.php
```

### Issue: Database Connection Failed

**Solution**: 
1. **Verify MySQL service is connected to PHP service**:
   - Go to PHP service → Variables tab
   - Ensure MySQL service is listed as a "Reference Variable"
   - If not, add it: "+ New Variable" → "Reference Variable" → Select MySQL service

2. **Check environment variables are set**:
   - Visit: `https://your-app.railway.app/debug_db.php`
   - Verify `MYSQLHOST`, `MYSQLDATABASE`, etc. are shown (not "NOT SET")
   - If variables are missing, reconnect MySQL service to PHP service

3. **Verify MySQL service is running**:
   - Check MySQL service status in Railway dashboard
   - Ensure it shows "Active" status

### Issue: Health Check Failing

**Solution**: 
1. Increase startup timeout in Railway settings
2. Verify `/health.php` is accessible
3. Check Apache error logs in Railway

## Railway-Specific Files

- `railway-entrypoint.sh`: Configures Apache for Railway's PORT
- `Dockerfile`: Railway-compatible Docker configuration
- `src/health.php`: Health check endpoint for Railway

## Environment Variables Reference

| Variable | Description | Default | Railway |
|----------|-------------|---------|---------|
| `PORT` | Port to listen on | 80 | Auto-set by Railway |
| `DB_HOST` | MySQL host | mysql | Railway MySQL service |
| `DB_NAME` | Database name | mydb | Railway MySQL database |
| `DB_USER` | Database user | appuser | Railway MySQL user |
| `DB_PASS` | Database password | apppassword | Railway MySQL password |

## Quick Deploy Checklist

- [ ] Repository connected to Railway
- [ ] MySQL service created
- [ ] PHP service created from Dockerfile
- [ ] Environment variables configured
- [ ] Database config updated (if using Railway MySQL)
- [ ] Health check path set to `/health.php`
- [ ] Deployed and verified health endpoint

## Verification

After deployment:

1. **Health Check**:
   ```bash
   curl https://your-app.railway.app/health.php
   # Should return: {"status":"ok","timestamp":"..."}
   ```

2. **Main Page**:
   ```bash
   curl -I https://your-app.railway.app/
   # Should return: HTTP/2 200
   ```

3. **Check Logs**:
   - Railway dashboard → Service → Logs
   - Look for: "Apache configured -- resuming normal operations"
   - No "connection refused" errors

## Notes

- Railway automatically handles HTTPS/SSL termination
- Railway provides a public URL automatically
- MySQL service is accessible via internal Railway network
- Port binding is handled automatically by Railway

