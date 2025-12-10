#!/bin/bash

echo "=== Deployment Verification ==="
echo ""

echo "1. Testing health endpoint..."
HEALTH=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/health.php)
if [ "$HEALTH" = "200" ]; then
    echo "✅ Health check: OK (HTTP $HEALTH)"
    curl -s http://localhost:8080/health.php | jq . 2>/dev/null || curl -s http://localhost:8080/health.php
else
    echo "❌ Health check: FAILED (HTTP $HEALTH)"
fi
echo ""

echo "2. Testing main page..."
MAIN=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/)
if [ "$MAIN" = "200" ]; then
    echo "✅ Main page: OK (HTTP $MAIN)"
else
    echo "❌ Main page: FAILED (HTTP $MAIN)"
fi
echo ""

echo "3. Checking container status..."
docker ps --filter "name=php_app" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
echo ""

echo "4. Checking MySQL connection from PHP container..."
docker exec php_app php -r "
try {
    \$pdo = new PDO('mysql:host=mysql;dbname=mydb', 'appuser', 'apppassword');
    echo '✅ Database connection: OK\n';
} catch (Exception \$e) {
    echo '❌ Database connection: FAILED - ' . \$e->getMessage() . '\n';
}
"
echo ""

echo "5. Recent Apache error logs..."
docker exec php_app tail -5 /var/log/apache2/error.log 2>/dev/null || echo "No errors found"
echo ""

echo "=== Verification Complete ==="

