# The v4 API
This repository holds the code for the Goteo **v4** API.

> **NOTE**: Review the trusted certificates by OpenSSL. See: https://github.com/GoteoFoundation/v4/issues/43

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

#### 2.1 Configuring the containers.

To avoid ownership issues for files generated inside the PHP container the default Compose config will try to export your user's and group's ID, or **1000** if it can't find them, to the user inside the container. If that is not your ID you can pass your actual IDs using the env vars `UID` and  `GID`.

In a similar fashion you can override the nginx container binding to the ports **:8090** (for http) and **:8091** (for https) on your host with the env vars `APP_HTTP_PORT` and `APP_HTTPS_PORT`.

- Option A. Using a custom `.env.local` file.
```dotenv
# .env.local

UID=1001
GID=1001

APP_HTTP_PORT=8080
APP_HTTPS_PORT=8443
```

Then feed your custom env vars to Compose:
```shell
docker compose --env-file .env.local up -d --build
```

- Option B. Passing the variables through the shell.
```shell
export APP_HTTP_PORT=8080 && export APP_HTTPS_PORT=8443

# Dynamic user and group id
export UID=$(id -u) && export GID=$(id -g)

# Custom user and group id
export UID=1001 && export GID=1001

docker compose up -d --build
```

#### 2.2 Post-build setup.

After the Docker containers are first built, you'll need to finish the PHP setup.
```shell
# Install composer.json dependencies
bin/docker php composer install

# Create the database
# May throw an error if the DB already exists, no further action required if so
bin/docker php bin/console doctrine:database:create

# Update the database schema
# Might be changed to use doctrine migrations in the future
bin/docker php bin/console doctrine:schema:update --force

# Setup Gateway services
bin/docker php bin/console app:gateways:setup
```

## Usage

The app should be live at [http://localhost:8090](http://localhost:8090) (or your specified ports). Keep in mind that the API address is [/v4](http://localhost:8090/v4).

You can access a real-time build of the OpenAPI spec file for v4 at [http://localhost:8090/v4/docs.json](http://localhost:8090/v4/docs.json), to be used, for example, with API development suites such as Hoppscotch. This file will be up to date with most of your latest changes.

A Swagger UI version of the docs is also available at [http://localhost:8090/v4/docs?ui=swagger_ui](http://localhost:8090/v4/docs?ui=swagger_ui).

For quick Docker access you can use the `bin/docker` shortcut to quickly `exec` anything into one of the containers. It expects the name of the Docker Compose service as first parameter, then you can pass whatever it is that you wish to exec into that container, e.g:

- Login to mysql CLI: `bin/docker mariadb mysql -u goteo -pgoteo goteo`
- Debug the symfony services: `bin/docker php bin/console debug:container`
- List app custom commands: `bin/docker php bin/console list app`

## Testing

This app uses PHPUnit.

```shell
bin/docker php bin/phpunit
```

Beware you might experience the following error:

```
RuntimeException: Error running "doctrine:database:create": Could not create database `goteo_test` for connection named default
An exception occurred while executing a query: SQLSTATE[42000]: Syntax error or access violation: 1044 Access denied for user 'goteo'@'%' to database 'goteo_test'
 in /app/vendor/zenstruck/foundry/src/Test/AbstractSchemaResetter.php:33
```

To fix it:

1. Login into the MariaDB instance as *root*.
```shell
bin/docker mariadb mysql -u root -pgoteo goteo
```

2. Grant *goteo* all privileges.
```mysql
GRANT ALL PRIVILEGES ON *.* TO 'goteo'@'%'
```
