variable "APP_ENV" { default = "prod" }
variable "APP_TIMEZONE" { default = "UTC" }
variable "COMPOSER_INSTALL_MODE" { default = "no-dev" }
variable "COMPOSER_VERSION" { default = "2.8.0" }
variable "DEBIAN_COMMON_PACKAGES" { default = "libicu72 libpq5 libzip4 zlib1g" }
variable "DEBIAN_VERSION" { default = "bookworm" }
variable "FRANKENPHP_CONFIG" { default = "import worker.Caddyfile" }
variable "FRANKENPHP_VERSION" { default = "1.2.5" }
variable "IMAGES_PREFIX" { default = "the-doomsday-machine:" }
variable "IMAGES_TAG" { default = "latest" }
variable "PECL_APCU_VERSION" { default = "5.1.24" }
variable "PHP_EXTENSIONS_TIMESTAMP" { default = "20230831" }
variable "PHP_INI_ENV" { default = "production" }
variable "PHP_VERSION" { default = "8.3.12" }
variable "WITH_XDEBUG" { default = "false" }
variable "XDEBUG_VERSION" { default = "3.3.2" }

function "tag" {
    params = [name]
    result = [notequal("prod",APP_ENV) ? "${IMAGES_PREFIX}${name}-${APP_ENV}-${IMAGES_TAG}": "${IMAGES_PREFIX}${name}-${IMAGES_TAG}"]
}

function "php-cli-upstream" {
    params = []
    result = "docker-image://php:${PHP_VERSION}-zts-${DEBIAN_VERSION}"
}

target "builder" {
    args = {
        app_env = "${APP_ENV}"
        composer_install_mode = "${COMPOSER_INSTALL_MODE}"
        pecl_apcu_version = "${PECL_APCU_VERSION}"
        timezone = "${APP_TIMEZONE}"
    }
    contexts = {
        composer-upstream = "docker-image://composer:${COMPOSER_VERSION}"
        php-cli-upstream = php-cli-upstream()
    }
    dockerfile = "docker/containers/builder/Dockerfile"
    tags = tag("builder")
}

target "cli-base" {
    args = {
        app_env = "${APP_ENV}"
        common_packages = "${DEBIAN_COMMON_PACKAGES}"
        php_extensions_timestamp = "${PHP_EXTENSIONS_TIMESTAMP}"
        php_ini_env = "${PHP_INI_ENV}"
        timezone = "${APP_TIMEZONE}"
        with_xdebug = "${WITH_XDEBUG}"
        xdebug_version = "${XDEBUG_VERSION}"
    }
    contexts = {
        builder = "target:builder"
        php-cli-upstream = php-cli-upstream()
    }
    dockerfile = "docker/containers/cli/base.Dockerfile"
    tags = tag("cli-base")
}

target "cli" {
    contexts = {
        builder = "target:builder"
        cli-base =  "target:cli-base"
    }
    dockerfile = "docker/containers/cli/Dockerfile"
    tags = tag("cli")
}

target "web" {
    args = {
        app_env = "${APP_ENV}"
        common_packages = "${DEBIAN_COMMON_PACKAGES}"
        frankenphp_config = "${FRANKENPHP_CONFIG}"
        php_extensions_timestamp = "${PHP_EXTENSIONS_TIMESTAMP}"
        php_ini_env = "${PHP_INI_ENV}"
        timezone = "${APP_TIMEZONE}"
        with_xdebug = "${WITH_XDEBUG}"
        xdebug_version = "${XDEBUG_VERSION}"
    }
    contexts = {
        builder = "target:builder"
        frankenphp-upstream = "docker-image://dunglas/frankenphp:${FRANKENPHP_VERSION}-php${PHP_VERSION}-${DEBIAN_VERSION}"
    }
    dockerfile = "docker/containers/web/Dockerfile"
    tags = tag("web")
}

group "default" {
    targets = ["web", "cli"]
}
