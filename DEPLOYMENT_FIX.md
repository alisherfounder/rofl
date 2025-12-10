# Deployment Fix Guide - 502 Bad Gateway

## Problem Analysis

The 502 error with "connection refused" indicates the reverse proxy cannot connect to your backend container. Root causes:

1. **Container not running** - Service crashed or failed to start
2. **Port mismatch** - Container listening on wrong port
3. **Health check failures** - Platform marked service unhealthy
4. **Apache not started** - Web server failed to initialize
5. **Network issues** - Container networking misconfigured

## Fixes Applied

### 1. Health Check Endpoint
- Added `/health.php` endpoint for platform health checks
- Returns JSON status with timestamp

### 2. Dockerfile Improvements
- Installed `curl` for health checks
- Added `EXPOSE 80` directive
- Added container healthcheck configuration

### 3. Docker Compose Updates
- Added MySQL health check (waits for DB to be ready)
- Added PHP service health check
- PHP service now waits for MySQL to be healthy before starting
- Proper service dependencies configured

### 4. Apache Configuration
- Ensured rewrite module is properly configured
- VirtualHost listens on all interfaces (*:80)

## Deployment Steps

### For Local Testing

```bash
# Rebuild containers
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# Check logs
docker-compose logs php
docker-compose logs mysql

# Verify health
curl http://localhost:8080/health.php
```

### For Production Deployment

#### Option 1: Cloud Run / Similar Platforms

1. **Ensure PORT environment variable is respected** (if platform requires it):
   - Most platforms inject `PORT` env var
   - Apache listens on port 80 by default
   - If platform expects different port, update Apache config

2. **Check platform-specific requirements**:
   ```bash
   # Verify container starts
   docker run -p 8080:80 your-image-name
   
   # Test health endpoint
   curl http://localhost:8080/health.php
   ```

3. **Platform Configuration**:
   - Set health check path: `/health.php`
   - Set health check port: `80` (or platform's expected port)
   - Set startup timeout: `60s` (allows MySQL connection time)

#### Option 2: Railway / Render / Fly.io

These platforms typically:
- Use `PORT` environment variable
- Expect health checks on `/health` or `/health.php`
- Need proper `EXPOSE` directive in Dockerfile

**If platform uses PORT env var**, create startup script:

```dockerfile
# Add to Dockerfile
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
ENTRYPOINT ["docker-entrypoint.sh"]
```

Create `docker-entrypoint.sh`:
```bash
#!/bin/bash
if [ -n "$PORT" ]; then
    sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
    sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/" /etc/apache2/sites-available/000-default.conf
fi
exec apache2-foreground
```

## Troubleshooting Checklist

### 1. Verify Container Status
```bash
docker ps -a
# Should show php_app and mysql_db as "Up"
```

### 2. Check Apache Logs
```bash
docker-compose logs php | grep -i error
docker exec php_app tail -f /var/log/apache2/error.log
```

### 3. Test Database Connection
```bash
docker exec php_app php -r "
try {
    \$pdo = new PDO('mysql:host=mysql;dbname=mydb', 'appuser', 'apppassword');
    echo 'DB connection OK';
} catch (Exception \$e) {
    echo 'DB error: ' . \$e->getMessage();
}
"
```

### 4. Test HTTP Endpoint
```bash
# From inside container
docker exec php_app curl http://localhost/health.php

# From host
curl http://localhost:8080/health.php
```

### 5. Verify Port Binding
```bash
docker port php_app
# Should show: 80/tcp -> 0.0.0.0:8080
```

### 6. Check Health Check Status
```bash
docker inspect php_app | grep -A 10 Health
```

## Common Issues & Solutions

### Issue: "Connection refused" persists

**Solution 1**: Apache not starting
```bash
docker exec php_app apache2ctl status
docker exec php_app apache2ctl start
```

**Solution 2**: Port conflict
- Check if port 8080 is already in use
- Change port mapping in docker-compose.yml

**Solution 3**: Platform-specific port requirements
- Some platforms require listening on `$PORT` env var
- Update Apache config to use environment variable

### Issue: Health check failing

**Solution**: Increase startup time
- Platform may be checking before MySQL is ready
- Increase `start_period` in healthcheck config
- Ensure MySQL healthcheck passes first

### Issue: Database connection errors

**Solution**: Verify MySQL is healthy
```bash
docker-compose exec mysql mysqladmin ping -h localhost -u root -prootpassword
```

## Platform-Specific Notes

### Cloudflare / Edge Network
- Ensure backend is accessible from edge locations
- Check firewall rules allow connections
- Verify DNS points to correct backend

### Kubernetes / ECS
- Ensure service selector matches pod labels
- Check service port configuration
- Verify ingress/load balancer configuration

### Docker Swarm
- Ensure service is deployed to correct network
- Check service discovery is working
- Verify port publishing configuration

## Verification

After deployment, verify:

1. ✅ Health endpoint responds: `curl https://betterly.selflabsai.com/health.php`
2. ✅ Main page loads: `curl https://betterly.selflabsai.com/`
3. ✅ No 502 errors in platform logs
4. ✅ Container health status is "healthy"
5. ✅ Database connections succeed

## Next Steps

1. Rebuild and redeploy containers
2. Monitor logs for 30 seconds after deployment
3. Test health endpoint from platform's edge location
4. Verify database connectivity
5. Test full application flow

