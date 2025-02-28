.PHONY: up db down cron

up:
	docker compose up -d --build
	docker compose exec app sh -c "composer install"
	npm --prefix frontend install
	npm --prefix frontend run dev &
db:
	docker compose exec db sh -c "mysql -u root -proot postcodes < docker-entrypoint-initdb.d/init.sql"

down:
	docker compose down

import:
	docker compose exec app php src/scripts/import.php

cron:
	docker compose exec app sh -c "cat /var/log/cron.log"
