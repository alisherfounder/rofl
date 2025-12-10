#!/bin/bash
PORT=${PORT:-80}
curl -f http://localhost:$PORT/health.php || exit 1

