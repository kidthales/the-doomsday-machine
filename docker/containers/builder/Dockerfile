#syntax=docker/dockerfile:1

FROM composer-upstream AS composer
FROM php-cli-upstream AS builder

ARG app_env=prod
# skip, dev, no-dev (default)
ARG composer_install_mode=no-dev
ARG pecl_apcu_version=5.1.24
ARG timezone=UTC

ENV TZ=${timezone}
# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /

# hadolint ignore=DL3008,SC2086
RUN set -eux; \
	# Timezone
	ln -snf /usr/share/zoneinfo/${timezone} /etc/localtime && echo ${timezone} > /etc/timezone; \
	# Build dependencies
	apt-get update && apt-get install -y --no-install-recommends \
	$PHPIZE_DEPS \
	libicu-dev \
	libpq-dev \
	libzip-dev \
	unzip \
	zlib1g-dev; \
	rm -rf /var/lib/apt/lists/*; \
	# PHP extensions
	docker-php-ext-configure intl; \
	docker-php-ext-install intl opcache pdo_pgsql zip; \
	pecl install apcu-${pecl_apcu_version}; \
	docker-php-ext-enable apcu; \
	docker-php-source delete;

WORKDIR /usr/local/bin
COPY --from=composer --chmod=555 /usr/bin/composer .

WORKDIR /app
COPY --link --chmod=444 . ./

RUN set -eux; \
	mkdir -p var/cache var/log; \
	case ${composer_install_mode} in \
		skip) \
			mkdir -p vendor; \
			;; \
		dev) \
			composer install --no-cache --prefer-dist --no-autoloader --no-scripts --no-progress; \
			composer dump-autoload --classmap-authoritative; \
			composer dump-env ${app_env}; \
			composer run-script post-install-cmd; \
			;; \
		*) \
			composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress; \
			composer dump-autoload --classmap-authoritative --no-dev; \
			composer dump-env ${app_env}; \
			composer run-script --no-dev post-install-cmd; \
			;; \
	esac;
