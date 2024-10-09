#syntax=docker/dockerfile:1

FROM builder AS builder
FROM cli-base AS cli

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /

# hadolint ignore=DL3008
RUN set -eux; \
	apt-get update && apt-get install -y --no-install-recommends \
	bash \
	postgresql-client-15; \
	rm -rf /var/lib/apt/lists/*;

COPY --link --chmod=444 docker/containers/cli/rootfs ./

WORKDIR /usr/local/bin
RUN set -eux; \
	chmod 555 init-doom.sh psql-doom.sh; \
	ln -s init-doom.sh init-doom; \
	ln -s psql-doom.sh psql-doom;

WORKDIR /usr/local/bin
COPY --from=builder --chmod=555 /usr/local/bin/composer .

WORKDIR /app
