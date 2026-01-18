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
                footy-stats-data-diff footy-stats-match-list footy-stats-match-chance-list footy-stats-match-xg-list \
                footy-stats-migrations-generate footy-stats-team-standing-list footy-stats-team-standing-predict \
                footy-stats-team-strength-list

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

test: ## Start tests with phpunit, pass the parameter "c=" to add options to phpunit
	@$(eval c ?=)
	@$(CD_DOCKER_COMP) run --rm -e APP_ENV=test php bin/phpunit $(c)

cov: ## Start tests with phpunit and generate coverage report for the project
	@$(CD_DOCKER_COMP) run --rm -e APP_ENV=test -e XDEBUG_MODE=coverage php bin/phpunit --testdox --coverage-text --coverage-html coverage

## —— Composer 🧙  ——————————————————————————————————————————————————————————————
composer: ## Run composer, pass the parameter "c=" to run a given command
	@$(eval c ?=)
	@$(COMPOSER) $(c)

vendor: ## Install vendors according to the current composer.lock file
vendor: c=install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction
vendor: composer

## —— Symfony 🎵  ———————————————————————————————————————————————————————————————
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command
	@$(eval c ?=)
	@$(SYMFONY) $(c)

cc: c=c:c ## Clear the cache
cc: sf

## —— Troubleshooting 🔎  ———————————————————————————————————————————————————————
own: ## On Linux host, set current user as owner of the project files that were created by the docker container
	@$(CD_DOCKER_COMP) run --rm php chown -R $$(id -u):$$(id -g) .

## —— Footy Stats ⚽ ————————————————————————————————————————————————————————————
footy-stats-data-diff: ## Insert or update Footy Stats table data, pass the parameter "c=" to add options or arguments
	@$(eval c ?=)
	@$(SYMFONY) app:footy-stats:data:diff $(c)

footy-stats-match-list: ## List matches, pass the parameter "c=" to add options or arguments
	@$(eval c ?=)
	@$(SYMFONY) app:footy-stats:match:list $(c)

footy-stats-match-chance-list: ## List (pending) match chances, pass the parameter "c=" to add options or arguments
	@$(eval c ?=)
	@$(SYMFONY) app:footy-stats:match:chance:list $(c)

footy-stats-match-xg-list: ## List (pending) match expected goals, pass the parameter "c=" to add options or arguments
	@$(eval c ?=)
	@$(SYMFONY) app:footy-stats:match:xg:list $(c)

footy-stats-migrations-generate: ## Generate a Footy Stats migration class, pass the parameter "c=" to add options or arguments
	@$(eval c ?=)
	@$(SYMFONY) app:footy-stats:migrations:generate $(c)

footy-stats-team-standing-list: ## List team standings, pass the parameter "c=" to add options or arguments
	@$(eval c ?=)
	@$(SYMFONY) app:footy-stats:team-standing:list $(c)

footy-stats-team-standing-predict: ## Predict team standings, pass the parameter "c=" to add options or arguments
	@$(eval c ?=)
	@$(SYMFONY) app:footy-stats:team-standing:predict $(c)

footy-stats-team-strength-list: ## List team strengths, pass the parameter "c=" to add options or arguments
	@$(eval c ?=)
	@$(SYMFONY) app:footy-stats:team-strength:list $(c)

