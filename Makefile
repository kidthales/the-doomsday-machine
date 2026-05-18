# Executables (local)
CD_DOCKER_TOUCH = cd docker && touch
CD_DOCKER_BAKE := $(CD_DOCKER_TOUCH) -a .env && docker buildx bake --allow=fs.read=.. -f .env -f bake.hcl
CD_DOCKER_COMP  = cd docker && docker compose
CD_DOCKER_DOCK  = cd docker && docker

# Docker containers
PHP_CONT    := $(CD_DOCKER_COMP) exec php
OLLAMA_CONT := $(CD_DOCKER_COMP) exec -it ollama

# Executables
PHP      := $(PHP_CONT) php
COMPOSER := $(PHP_CONT) composer
SYMFONY  := $(PHP) bin/console
OLLAMA   := $(OLLAMA_CONT) ollama

# Misc
JABRONIBETZ_MIGRATION_CONFIG_PATH := config/doctrine_migrations/jabronibetz.yaml

.DEFAULT_GOAL = help
.PHONY        : help \
                build print up start down logs bash \
                test cov \
                composer vendor \
                sf cc \
                own \
                ollama llm \
                footy-stats-database-diff footy-stats-database-sync \
                footy-stats-deduction-list footy-stats-deduction-update \
                footy-stats-match-chance-list footy-stats-match-list footy-stats-match-xg-list \
                footy-stats-team-standing-list footy-stats-team-standing-predict \
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

## —— Testing 🧪  ———————————————————————————————————————————————————————————————
test: ## Start tests with phpunit, pass the parameter "c=" to add options to phpunit
	@$(eval c ?=)
	@$(CD_DOCKER_COMP) run --rm -e APP_ENV=test php bin/phpunit $(c)

cov: ## Start tests with phpunit and generate coverage report for the project
	@$(CD_DOCKER_COMP) run --rm -e APP_ENV=test -e XDEBUG_MODE=coverage php bin/phpunit --testdox --display-all-issues --coverage-text --show-uncovered-for-coverage-text --coverage-html coverage

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

## —— Doctrine 🛢️  ——————————————————————————————————————————————————————————————
app-migrations-list: ## Display a list of all available app migrations and their status
app-migrations-list: c=doctrine\:migrations\:list
app-migrations-list: sf

app-migrations-status: ## View the status of a set of app migrations
app-migrations-status: c=doctrine\:migrations\:status
app-migrations-status: sf

app-migrations-diff: ## Generate a migration by comparing the current app database to the app mapping information
app-migrations-diff: c=doctrine\:migrations\:diff --formatted
app-migrations-diff: sf

app-migrations-migrate: ## Execute an app migration to the latest available version
app-migrations-migrate: c=doctrine\:migrations\:migrate
app-migrations-migrate: sf

jabronibetz-migrations-list: ## Display a list of all available jabronibetz migrations and their status
jabronibetz-migrations-list: c=doctrine\:migrations\:list --configuration=$(JABRONIBETZ_MIGRATION_CONFIG_PATH)
jabronibetz-migrations-list: sf

jabronibetz-migrations-status: ## View the status of a set of jabronibetz migrations
jabronibetz-migrations-status: c=doctrine\:migrations\:status --configuration=$(JABRONIBETZ_MIGRATION_CONFIG_PATH)
jabronibetz-migrations-status: sf

jabronibetz-migrations-diff: ## Generate a migration by comparing the current jabronibetz database to the jabronibetz mapping information
jabronibetz-migrations-diff: c=doctrine\:migrations\:diff --formatted --configuration=$(JABRONIBETZ_MIGRATION_CONFIG_PATH)
jabronibetz-migrations-diff: sf

jabronibetz-migrations-migrate: ## Execute an jabronibetz migration to the latest available version
jabronibetz-migrations-migrate: c=doctrine\:migrations\:migrate --configuration=$(JABRONIBETZ_MIGRATION_CONFIG_PATH)
jabronibetz-migrations-migrate: sf

## —— Troubleshooting 🔎  ———————————————————————————————————————————————————————
own: ## On Linux host, set current user as owner of the project files that were created by the docker container
	@$(CD_DOCKER_COMP) run --rm php chown -R $$(id -u):$$(id -g) .

## —— Ollama 🦙  ————————————————————————————————————————————————————————————————
ollama: ## Run ollama, pass the parameter "c=" to run a given command
	@$(eval c ?=)
	@$(OLLAMA) $(c)

llm: ## Create an llm from an ollama modelfile, pass the parameter "c=<model-name>" to complete the arguments
	@$(eval c ?=)
	@$(OLLAMA) create $(c) -f ollama/$(c).Modelfile

## —— Jabronibetz: Footy Stats ⚽  ——————————————————————————————————————————————
footy-stats-database-diff: ## Insert or update Footy Stats table data, pass the parameter "c=" to add options or arguments
	@$(eval c ?=)
	@$(SYMFONY) app:jabronibetz:footy-stats:database:diff $(c)

footy-stats-database-sync: ## Sync current Footy Stats seasons, pass the parameter "c=" to add options or arguments
	@$(eval c ?=)
	@$(SYMFONY) app:jabronibetz:footy-stats:database:sync $(c)

footy-stats-deduction-list: ## List point deductions, pass the parameter "c=" to add options or arguments
	@$(eval c ?=)
	@$(SYMFONY) app:jabronibetz:footy-stats:deduction:list $(c)

footy-stats-deduction-update: ## Update point deductions, pass the parameter "c=" to add options or arguments
	@$(eval c ?=)
	@$(SYMFONY) app:jabronibetz:footy-stats:deduction:update $(c)

footy-stats-match-chance-list: ## List (pending) match chances, pass the parameter "c=" to add options or arguments
	@$(eval c ?=)
	@$(SYMFONY) app:jabronibetz:footy-stats:match:chance:list $(c)

footy-stats-match-list: ## List matches, pass the parameter "c=" to add options or arguments
	@$(eval c ?=)
	@$(SYMFONY) app:jabronibetz:footy-stats:match:list $(c)

footy-stats-match-xg-list: ## List (pending) match expected goals, pass the parameter "c=" to add options or arguments
	@$(eval c ?=)
	@$(SYMFONY) app:jabronibetz:footy-stats:match:xg:list $(c)

footy-stats-team-standing-list: ## List team standings, pass the parameter "c=" to add options or arguments
	@$(eval c ?=)
	@$(SYMFONY) app:jabronibetz:footy-stats:team-standing:list $(c)

footy-stats-team-standing-predict: ## Predict team standings, pass the parameter "c=" to add options or arguments
	@$(eval c ?=)
	@$(SYMFONY) app:jabronibetz:footy-stats:team-standing:predict $(c)

footy-stats-team-strength-list: ## List team strengths, pass the parameter "c=" to add options or arguments
	@$(eval c ?=)
	@$(SYMFONY) app:jabronibetz:footy-stats:team-strength:list $(c)
