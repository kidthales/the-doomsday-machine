# Executables (local)
CD_DOCKER_TOUCH = cd docker && touch
CD_DOCKER_BAKE := $(CD_DOCKER_TOUCH) -a .env && docker buildx bake --allow=fs.read=.. -f .env -f bake.hcl
CD_DOCKER_COMP  = cd docker && docker compose
CD_DOCKER_DOCK  = cd docker && docker

# Docker containers
PHP_CONT := $(CD_DOCKER_COMP) exec php

# Executables
PHP      := $(PHP_CONT) php
COMPOSER := $(PHP_CONT) composer
SYMFONY  := $(PHP) bin/console

MIGRATIONS := $(SYMFONY) doctrine:migrations:
FOOTY_STATS_MIGRATION_CONFIG = --configuration config/migrations/doctrine_migrations_footy_stats.yaml

# Misc
.DEFAULT_GOAL = help
.PHONY        : help build print up start down logs bash test composer vendor sf cc own \
                footy-stats-migrations-current \
                footy-stats-migrations-dump-schema \
                footy-stats-migrations-execute \
                footy-stats-migrations-generate \
                footy-stats-migrations-latest \
                footy-stats-migrations-list \
                footy-stats-migrations-migrate \
                footy-stats-migrations-rollup \
                footy-stats-migrations-status \
                footy-stats-migrations-up-to-date \
                footy-stats-migrations-version

## —— ☢️  The Doomsday Machine Makefile ☢️  ————————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker 🐳  ————————————————————————————————————————————————————————————————
build: ## Builds the development container images
	@$(CD_DOCKER_BAKE) --pull --no-cache

print: ## Prints the bake options used to build the development container images
	@$(CD_DOCKER_BAKE) --print

up: ## Start the development containers in detached mode (no logs)
	@$(CD_DOCKER_COMP) up --detach

start: build up ## Build and start the development containers

down: ## Stop the development containers
	@$(CD_DOCKER_COMP) down --remove-orphans

logs: ## Show live logs
	@$(CD_DOCKER_COMP) logs --tail=0 --follow

bash: ## Connect to the php service via bash
	@$(PHP_CONT) bash

test: ## Start tests with phpunit, pass the parameter "c=" to add options to phpunit, example: make test c="--group e2e --stop-on-failure"
	@$(eval c ?=)
	@$(CD_DOCKER_COMP) run --rm -e APP_ENV=test php bin/phpunit $(c)

cov: ## Start tests with phpunit and generate coverage report for the project
	@$(CD_DOCKER_COMP) run --rm -e APP_ENV=test -e XDEBUG_MODE=coverage php bin/phpunit --testdox --coverage-text --coverage-html coverage

## —— Composer 🧙  ——————————————————————————————————————————————————————————————
composer: ## Run composer, pass the parameter "c=" to run a given command, example: make composer c='req symfony/orm-pack'
	@$(eval c ?=)
	@$(COMPOSER) $(c)

vendor: ## Install vendors according to the current composer.lock file
vendor: c=install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction
vendor: composer

## —— Symfony 🎵  ———————————————————————————————————————————————————————————————
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command, example: make sf c=about
	@$(eval c ?=)
	@$(SYMFONY) $(c)

cc: c=c:c ## Clear the cache
cc: sf

## —— Troubleshooting 🔎  ———————————————————————————————————————————————————————
own: ## On Linux host, set current user as owner of the project files that were created by the docker container
	@$(CD_DOCKER_COMP) run --rm php chown -R $$(id -u):$$(id -g) .

## —— Footy Stats ⚽ ————————————————————————————————————————————————————————————
footy-stats-migrations-current: ## Outputs the current Footy Stats migration version, pass the parameter "c=" to include options, example: make footy-stats-migrations-current c=--help
	@$(eval c ?=)
	@$(MIGRATIONS)current $(FOOTY_STATS_MIGRATION_CONFIG) $(c)

footy-stats-migrations-dump-schema: ## Dump the Footy Stats database schema to a migration, pass the parameter "c=" to include options, example: make footy-stats-migrations-dump-schema c=--help
	@$(eval c ?=)
	@$(MIGRATIONS)dump-schema $(FOOTY_STATS_MIGRATION_CONFIG) $(c)

footy-stats-migrations-execute: ## Execute one or more Footy Stats migration versions up or down manually, pass the parameter "c=" to include options and arguments, example: make footy-stats-migrations-execute c='FQCN --down'
	@$(eval c ?=)
	@$(MIGRATIONS)execute $(FOOTY_STATS_MIGRATION_CONFIG) $(c)

footy-stats-migrations-generate: ## Generate a Footy Stats migration class, pass the parameter "c=" to include options, example: make footy-stats-migrations-generate c=--help
	@$(eval c ?=)
	@$(SYMFONY) app:footy-stats:migrations:generate $(c)

footy-stats-migrations-latest: ## Outputs the latest Footy Stats migration version, pass the parameter "c=" to include options, example: make footy-stats-migrations-latest c=--help
	@$(eval c ?=)
	@$(MIGRATIONS)latest $(FOOTY_STATS_MIGRATION_CONFIG) $(c)

footy-stats-migrations-list: ## Display a list of all available Footy Stats migrations and their status, pass the parameter "c=" to include options, example: make footy-stats-migrations-list c=--help
	@$(eval c ?=)
	@$(MIGRATIONS)list $(FOOTY_STATS_MIGRATION_CONFIG) $(c)

footy-stats-migrations-migrate: ## Execute a Footy Stats migration to a specified version or the latest available version, pass the parameter "c=" to include options and arguments, example: make footy-stats-migrations-migrate c='FQCN --all-or-nothing'
	@$(eval c ?=)
	@$(MIGRATIONS)migrate $(FOOTY_STATS_MIGRATION_CONFIG) $(c)

footy-stats-migrations-rollup: ## Rollup Footy Stats migrations by deleting all tracked versions and insert the one version that exists, pass the parameter "c=" to include options, example: make footy-stats-migrations-rollup c=--help
	@$(eval c ?=)
	@$(MIGRATIONS)rollup $(FOOTY_STATS_MIGRATION_CONFIG) $(c)

footy-stats-migrations-status: ## View the status of a set of Footy Stats migrations, pass the parameter "c=" to include options, example: make footy-stats-migrations-status c=--help
	@$(eval c ?=)
	@$(MIGRATIONS)status $(FOOTY_STATS_MIGRATION_CONFIG) $(c)

footy-stats-migrations-up-to-date: ## Check if Footy Stats schema is up-to-date, pass the parameter "c=" to include options, example: make footy-stats-migrations-up-to-date c=--help
	@$(eval c ?=)
	@$(MIGRATIONS)up-to-date $(FOOTY_STATS_MIGRATION_CONFIG) $(c)

footy-stats-migrations-version: ## Manually add and delete Footy Stats migration versions from the version table, pass the parameter "c=" to include options and arguments, example: make footy-stats-migrations-version c='MIGRATION-FQCN --add'
	@$(eval c ?=)
	@$(MIGRATIONS)version $(FOOTY_STATS_MIGRATION_CONFIG) $(c)
