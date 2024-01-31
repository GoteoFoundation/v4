# The v4 API
This repository holds the code for the the v4 goteo API.

## Installation
This application requires [Docker](https://docs.docker.com/get-docker/) and the [Docker Compose](https://docs.docker.com/compose/install/) plugin.

### 1. Clone or download this repository.

```shell
git clone https://github.com/GoteoFoundation/v4
cd v4
```

### 2. Build the Docker containers.

```shell
docker compose up -d --build
```

To avoid ownership issues for files generated inside the PHP container, the default Compose config will export **1001** as the ID for your user and user group. If that is not your ID you can pass your actual IDs using the env vars `USER_ID` and  `USER_GROUP_ID`.

In a similar fashion you can override the nginx container binding to the ports **:8090** (for http) and **:8091** (for https) on your host with the env vars `APP_HTTP_PORT` and `APP_HTTPS_PORT`.

```dotenv
# .env.local
USER_ID=1002
USER_GROUP_ID=1002

APP_HTTP_PORT=8080
APP_HTTPS_PORT=8433
```

Then feed your custom env vars to Compose:
```shell
docker compose --env-file .env.local up -d --build
```

After the Docker containers are first built, you'll need to finish the PHP setup.
```shell
# Install composer.json dependencies
bin/docker php composer install

# Create the database
# May throw an error if the DB already exists, you don't need to do anything in that case
bin/docker php bin/console doctrine:database:create

# Update the database schema
# Might be changed to use doctrine migrations in the future
bin/docker php bin/console doctrine:schema:update --force
```

## Usage

The app should be live at [http://localhost:8091](http://localhost:8091) (or your specified ports). Keep in mind that the API address is [/v4](http://localhost:8091/v4).

You can access a real-time build of the OpenAPI spec file for v4 at [http://localhost:8090/v4/docs.json](http://localhost:8090/v4/docs.json), to be used, for example, with API development suites such as Hoppscotch. This file will be up to date with most of your latest changes.

A Swagger UI version of the docs is also available at [http://localhost:8090/v4/docs?ui=swagger_ui](http://localhost:8090/v4/docs?ui=swagger_ui).

## Testing

This app uses PHPUnit.

```shell
bin/docker php bin/phpunit
```
