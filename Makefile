.PHONY: build install start start-worker start-classic restart stop status logs audit

build:
	docker compose build

install: build
	docker compose run --rm --no-deps php-classic composer install --no-progress

start: install
	docker compose up -d
	@echo "Leaky Blog is running:"
	@echo "  WORKER mode:  http://localhost:8080  (one PHP worker serves every request)"
	@echo "  CLASSIC mode: http://localhost:8081  (Symfony boots on every request)"

start-worker: install
	docker compose up -d php-worker
	@echo "Leaky Blog in WORKER mode: http://localhost:8080"

start-classic: install
	docker compose up -d php-classic
	@echo "Leaky Blog in CLASSIC mode: http://localhost:8081"

# Restart the workers, for example after igor:doctor patched src/
restart:
	docker compose restart

stop:
	docker compose down

status:
	docker compose ps

logs:
	docker compose logs -f

audit:
	vendor/bin/igor-php .
