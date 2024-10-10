# Executables (local)
AWK = awk
DOCKER = docker
DOCKER_COMP = docker compose
DOCKER_X = docker buildx
GREP = grep
SED = sed

# Docker containers
CLI_CONT = $(DOCKER_COMP) exec cli

# Executables
PHP      = $(CLI_CONT) php
COMPOSER = $(CLI_CONT) composer
SYMFONY  = $(PHP) bin/console

# Misc
.DEFAULT_GOAL = help
.PHONY        : help build up down config logs bash psql test cov composer vendor sf cc

## —— 💣 ☢️ The Doomsday Machine Makefile ☢️ 💣 ————————————————————————————————
help: ## Outputs this help screen
	@$(GREP) -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | $(AWK) 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | $(SED) -e 's/\[32m##/[33m/'

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images for local development. Pass the parameter "c=" to add options to docker buildx bake; example: make build c="--no-cache --pull"
	@$(eval c ?=)
	@$(DOCKER_X) bake -f docker/bake.hcl -f docker/env.override.hcl $(c)

up: ## Start the docker compose stack. Pass the parameter "c=" to add options to docker compose up; example: make up c="--detach"
	@$(eval c ?=)
	@$(DOCKER_COMP) --env-file .env.local up $(c)

down: ## Stop the docker compose stack. Pass the parameter "c=" to add options to docker compose down; example: make up c="--remove-orphans"
	@$(eval c ?=)
	@$(DOCKER_COMP) --env-file .env.local down $(c)

config: ## Show the docker compose configuration
	@$(DOCKER_COMP) --env-file .env.local config

logs: ## Show live logs
	@$(DOCKER_COMP) logs --tail=0 --follow

bash: ## Execute an interactive bash shell on the cli container
	@$(CLI_CONT) bash

psql: ## Execute an interactive psql client on the cli container. Pass the parameter "c=" to specify the user; example: make psql c="writer"
	@$(eval c ?=)
	@$(CLI_CONT) psql-doom $(c)

test: ## Start tests with phpunit, pass the parameter "c=" to add options to phpunit, example: make test c="--group e2e --stop-on-failure"
	@$(eval c ?=)
	@$(DOCKER_COMP) exec -e APP_ENV=test -e XDEBUG_MODE=coverage cli bin/phpunit $(c)

cov: ## Start tests with phpunit, and generates a coverage report for the entire project
	@$(MAKE) test c='--coverage-text --coverage-html coverage'

## —— Composer 🧙 ——————————————————————————————————————————————————————————————
composer: ## Run composer. Pass the parameter "c=" to run a given command; example: make composer c='req symfony/orm-pack'
	@$(eval c ?=)
	@$(COMPOSER) $(c)

vendor: ## Install vendors according to the current composer.lock file
vendor: c=install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction
vendor: composer

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command; example: make sf c=about
	@$(eval c ?=)
	@$(SYMFONY) $(c)

cc: c=c:c ## Clear the cache
cc: sf
