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

# Misc
.DEFAULT_GOAL = help
.PHONY        : help build print up start down logs bash test composer vendor sf cc own \
                footy-stats-migrations-generate

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
