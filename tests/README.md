# Easy Digital Downloads Unit Tests [![Build status](https://badge.buildkite.com/e318e1649f3a28f4029272231b926126d5664837d575cd507a.svg)](https://buildkite.com/sandhills-development-llc/easy-digital-downloads)


This folder contains all the tests for Easy Digital Downloads.

## Running Tests
### How to run the tests locally
The ideal way to run the unit tests locally is by using the included Docker Compose files for the stack and for PHPUnit.

Requirements:
- Docker (19.03.13 or newer)
- Docker Compose (1.25.5 or newer)

The configuration for these are located in the following files:
`./docker-compose.yml`, `./docker-compose.phpunit.yml`, `Dockerfile`

### 1) Start the Containers
This step should only need to be run once (or after a system reboot). You may also need to run it again if you change the Environment variables to test other PHP or WordPress Versions.
```
$ docker-compose -f docker-compose.yml -f docker-compose.phpunit.yml up -d
```

The `up` command starts the container for the Database and PHP/App server, downloading any images necessary to build the containers.

### 2) Setup the test environment
```
docker-compose -f docker-compose.phpunit.yml run --rm wordpress_phpunit /app/bin/install-wp-tests.sh wordpress_test root '' mysql_phpunit latest true
```

This command sends the command `/app/bin/install-wp-tests.sh wordpress_test root '' mysql_phpunit latest true` to the `wordpress_phpunit` container, to prepair the test run by downloading any WordPress core files and setting up the database.

### 3) Run the test suite
```
docker-compose -f docker-compose.phpunit.yml run --rm wordpress_phpunit phpunit
```

This command sends the `phpunit` command to the `wordpress_phpunit` container.

## Writing Tests
For more information on how to write PHPUnit Tests, see [PHPUnit's Website](http://www.phpunit.de/manual/3.6/en/writing-tests-for-phpunit.html).
