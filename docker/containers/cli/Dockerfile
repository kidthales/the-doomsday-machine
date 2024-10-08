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

WORKDIR /usr/local/bin
COPY --from=builder --chmod=555 /usr/local/bin/composer .

WORKDIR /app
