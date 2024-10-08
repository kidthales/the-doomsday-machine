#syntax=docker/dockerfile:1

FROM builder
FROM php-cli-upstream AS cli-base

ARG app_env=prod
ARG common_packages=''
ARG php_extensions_timestamp=20230831
ARG php_ini_env=production
ARG timezone=UTC
ARG with_xdebug=false
ARG xdebug_version=3.3.2

ENV TZ=${timezone}
ENV PHP_DATE_TIMEZONE=${timezone}
ENV APP_ENV=${app_env}
ENV XDEBUG_MODE=off

WORKDIR /

# hadolint ignore=DL3008
RUN set -eux; \
	# Timezone
	ln -snf /usr/share/zoneinfo/${timezone} /etc/localtime && echo ${timezone} > /etc/timezone; \
	# Runtime dependencies
	apt-get update && apt-get install -y --no-install-recommends \
	${common_packages} \
	procps; \
	rm -rf /var/lib/apt/lists/*; \
	# PHP extensions
	if [ ${with_xdebug} = true ]; then \
		pecl install xdebug-${xdebug_version}; \
		docker-php-ext-enable xdebug; \
		docker-php-source delete; \
	fi;

WORKDIR $PHP_INI_DIR
RUN set -eux; \
	ln -s php.ini-${php_ini_env} php.ini;

WORKDIR $PHP_INI_DIR/conf.d
COPY --from=builder --chmod=444 $PHP_INI_DIR/conf.d/*.ini .

WORKDIR /usr/local/lib/php/extensions/no-debug-zts-${php_extensions_timestamp}
COPY --from=builder --chmod=444 /usr/local/lib/php/extensions/no-debug-zts-${php_extensions_timestamp}/*.so .

WORKDIR /app
COPY --from=builder --chmod=444 /app ./

RUN set -eux; \
	chmod +x bin/console;

VOLUME /app/var
