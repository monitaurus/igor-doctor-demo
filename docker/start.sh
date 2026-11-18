#!/bin/sh
# Builds what lives in var/ (one volume per service, so the host's var/ is never shared with Linux),
# then starts FrankenPHP. FRANKENPHP_CONFIG decides between worker and classic mode.
set -e
cd /app
bin/console sass:build --no-interaction
bin/console cache:warmup --no-interaction
exec frankenphp run --config /etc/frankenphp/Caddyfile
