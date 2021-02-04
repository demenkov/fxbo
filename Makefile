# Setup —————————————————————————————————————————————————————————————————————————
PROJECT        = fxbo-rate
DOCKER_COMPOSE = docker-compose
DOCKER         = docker
OPEN_API       = ./vendor/bin/openapi
PHPSTAN        = ./vendor/bin/phpstan
PHPCS          = ./vendor/bin/phpcs
CONSOLE        = ./bin/console
.DEFAULT_GOAL  := help

.PHONY : help

help: ## Show this help
	@printf "\033[33m%s:\033[0m\n" 'Available commands'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  \033[32m%-18s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

## —— docker —————————————————————————————————————————————————————————————————
start: ## Run local environment
	$(DOCKER_COMPOSE) -f docker-compose.yml up -d --force-recreate
build: ## Run local environment with rebuild containers
	$(DOCKER_COMPOSE) -f docker-compose.yml up -d --force-recreate --build
init:
	$(CONSOLE) doctrine:migrations:migrate --allow-no-migration --no-interaction --query-time
down:
	$(DOCKER_COMPOSE) down --remove-orphans
## —— docs —————————————————————————————————————————————————————————————————
doc: ## Generate openapi yaml file
	$(OPEN_API) src -o open-api.yaml
## —— qa —————————————————————————————————————————————————————————————————
cs: ## Run code check
	$(PHPCS) -v -n
	$(PHPSTAN) analyse --no-ansi --memory-limit=-1
test: ## Run tests
	$(DOCKER_COMPOSE) -f docker-compose.yml -f docker-compose.test.yml run --rm -T php ./bin/console lint:container --no-ansi
	$(DOCKER_COMPOSE) -f docker-compose.yml -f docker-compose.test.yml up -d --force-recreate --remove-orphans --no-color
	$(DOCKER_COMPOSE) -f docker-compose.yml -f docker-compose.test.yml exec -T php ./bin/console doctrine:database:create --if-not-exists
	$(DOCKER_COMPOSE) -f docker-compose.yml -f docker-compose.test.yml exec -T php ./bin/console doctrine:migrations:migrate -n --allow-no-migration
	$(DOCKER_COMPOSE) -f docker-compose.yml -f docker-compose.test.yml exec -T php ./bin/console doctrine:schema:validate --skip-sync
	$(DOCKER_COMPOSE) -f docker-compose.yml -f docker-compose.test.yml exec -T php ./bin/console doctrine:fixtures:load --no-interaction
	$(DOCKER_COMPOSE) -f docker-compose.yml -f docker-compose.test.yml exec -T php ./vendor/bin/phpunit
#dont forget about eval $(ssh-agent)
install: ## Install composer packages
	$(DOCKER) run --rm -t \
	  --volume ${PWD}:/app \
	  composer validate --no-ansi || { echo 'Composer validation failed (PHP)' ; exit 1; }
	$(DOCKER) run --rm -t \
	  --volume ${PWD}:/app \
	  --volume ${SSH_AUTH_SOCK}:/ssh-auth.sock \
	  --volume /etc/passwd:/etc/passwd:ro \
	  --volume /etc/group:/etc/group:ro \
	  --env ${SSH_AUTH_SOCK}=/ssh-auth.sock \
	  --user $(id -u):$(id -g) \
	  composer install \
	  --no-ansi \
	  --no-scripts \
	  --ignore-platform-reqs \
	  --no-interaction || { echo 'Composer install failed (PHP)' ; exit 1; }
