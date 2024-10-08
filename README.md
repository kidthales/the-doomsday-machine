# The Doomsday Machine

Personal web application.

## Requirements

1. [Docker Compose](https://docs.docker.com/compose/install/) (v2.10+).
2. [Git](https://git-scm.com/).
3. [Make](https://www.gnu.org/software/make/).

## Getting Started

1. Clone the project to your local machine, in the directory of your choosing:
    ```shell
    git clone https://github.com/kidthales/the-doomsday-machine.git
    ```
2. Enter our newly cloned project (`cd the-doomsday-machine`), and perform some initial setup tasks:
   1. Create a new file `docker/env.local.hcl`; this file is ignored by `git`. Contents of the file should look similar to:
      ```hcl
      APP_ENV="dev"
      COMPOSER_INSTALL_MODE="skip"
      FRANKENPHP_CONFIG=""
      IMAGES_TAG="local"
      PHP_INI_ENV="development"
      WITH_XDEBUG="true"
      ```
   2. Create a new file named `.env.local`; this file is ignored by `git`. Contents of the file should look similar to:
      ```dotenv
       IMAGES_POSTFIX=-dev-local
      ```
3. Run `make build` to build fresh Docker images for local development.
4. Run `make up` to start the Docker containers.
5. Run `make down` to stop the Docker containers.
