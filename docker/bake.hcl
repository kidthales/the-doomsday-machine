variable "APP_ENV" { default = "dev" }
variable "IMAGES_PREFIX" { default = "the-doomsday-machine" }
variable "IMAGES_TAG" { default = "latest" }

target "app" {
    context    = ".."
    contexts = { frankenphp_upstream = "docker-image://dunglas/frankenphp:1.9-php8.3-trixie" }
    dockerfile = "docker/Dockerfile"
    tags = [notequal("prod", APP_ENV) ? "${IMAGES_PREFIX}:${APP_ENV}-${IMAGES_TAG}" : "${IMAGES_PREFIX}:${IMAGES_TAG}"]
    target     = "app_${APP_ENV}"
}

group "default" {
    targets = ["app"]
}
