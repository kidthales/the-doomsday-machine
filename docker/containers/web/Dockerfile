#syntax=docker/dockerfile:1

FROM frankenphp-upstream AS web

ARG app_env=prod
ARG common_packages=''
ARG frankenphp_config='import worker.Caddyfile'
ARG php_extensions_timestamp=20230831
ARG php_ini_env=production
ARG timezone=UTC
ARG with_xdebug=false
ARG xdebug_version=3.3.2

ENV TZ=${timezone}
ENV PHP_DATE_TIMEZONE=${timezone}
ENV APP_ENV=${app_env}
ENV XDEBUG_MODE=off
ENV PHP_INI_SCAN_DIR=":$PHP_INI_DIR/app.conf.d"
ENV FRANKENPHP_CONFIG='${frankenphp_config}'

WORKDIR /

# hadolint ignore=DL3008
RUN set -eux; \
	# Timezone
	ln -snf /usr/share/zoneinfo/${timezone} /etc/localtime && echo ${timezone} > /etc/timezone; \
	# Runtime dependencies
	apt-get update && apt-get install -y --no-install-recommends \
	${common_packages} \
	acl \
	file \
	gettext; \
	rm -rf /var/lib/apt/lists/*; \
	# PHP extensions
	if [ ${with_xdebug} == true ]; then \
		pecl install xdebug-${xdebug_version}; \
		docker-php-ext-enable xdebug; \
		docker-php-source delete; \
	fi;

COPY --link --chmod=444 docker/containers/web/rootfs ./

WORKDIR /usr/local/bin
RUN set -eux; \
	chmod 555 docker-entrypoint.sh; \
	ln -s docker-entrypoint.sh docker-entrypoint;

WORKDIR $PHP_INI_DIR
RUN set -eux; \
	if [ ${php_ini_env} == development ]; then \
		ln -s php.ini-development php.ini; \
		rm app.conf.d/20-app.prod.ini; \
	else \
		ln -s php.ini-production php.ini; \
		rm app.conf.d/20-app.dev.ini; \
	fi;

WORKDIR $PHP_INI_DIR/conf.d
COPY --from=builder --chmod=444 $PHP_INI_DIR/conf.d/*.ini .

WORKDIR /usr/local/lib/php/extensions/no-debug-zts-${php_extensions_timestamp}
COPY --from=builder --chmod=444 /usr/local/lib/php/extensions/no-debug-zts-${php_extensions_timestamp}/*.so .

WORKDIR /app
COPY --from=builder --chmod=444 /app ./

ENTRYPOINT ["docker-entrypoint"]

HEALTHCHECK --start-period=60s CMD curl -f http://localhost:2019/metrics || exit 1
CMD [ "frankenphp", "run", "--config", "/etc/caddy/Caddyfile" ]

VOLUME /app/var
