# Easy Digital Downloads Unit Tests [![Build status](https://badge.buildkite.com/e318e1649f3a28f4029272231b926126d5664837d575cd507a.svg)](https://buildkite.com/sandhills-development-llc/easy-digital-downloads)


This folder contains all the tests for Easy Digital Downloads.

## Running Tests
### How to run the tests locally
The ideal way to run the unit tests locally is by using the included Docker Compose files for the stack and for PHPUnit.

Requirements:
- Docker (19.03.13 or newer)
- Docker Compose (1.25.5 or newer)

To run the tests, use the following command:
```
bin/run-tests-local.sh -p 8.2 -w latest
```

The flags `-p` and `-w` control the PHP and WordPress versions for the tests, respectively.

This command sends the `phpunit` command to the `wordpress_phpunit` container.

### Running Tests with EDD Recurring
To run tests that require EDD Recurring (such as subscription-related IPN tests), you can use the `--extra` flag:

```bash
bin/run-tests-local.sh -p 8.2 -w latest --extra recurring
```

**Important:** Running tests with EDD Recurring requires a GitHub personal access token with access to private repositories:

```bash
export COMPOSER_AUTH='{"github-oauth":{"github.com":"your_github_token_here"}}'
bin/run-tests-local.sh -p 8.2 -w latest --extra recurring
```

You can create a GitHub token at https://github.com/settings/tokens with the `repo` scope.

When `--extra recurring` is used:
1. The test scripts will download and install EDD Recurring from GitHub
2. The test bootstrap will automatically load and activate EDD Recurring
3. Tests requiring `EDD_Recurring` or `EDD_Subscription` classes will run (not be skipped)

Example running PayPal IPN tests with Recurring:
```bash
export COMPOSER_AUTH='{"github-oauth":{"github.com":"your_token"}}'
bin/run-tests-local.sh -p 8.2 -w latest --extra recurring -f 'EDD\\Tests\\Gateways\\PayPal\\IPN'
```

## Writing Tests
For more information on how to write PHPUnit Tests, see [PHPUnit's Website](http://www.phpunit.de/manual/3.6/en/writing-tests-for-phpunit.html).

While writing tests, you may found it helpful to only run the specific tests, file or namespace you are working in. To do this you can use the `-f` filter.

```
# Test for a specific test
bin/run-tests-local.sh -p 8.2 -w latest -f test_full_function_symbol

# Test for any tests whose function starts with the string
bin/run-tests-local.sh -p 8.2 -w latest -f test_get_order_

# Test for a namespace or class
bin/run-tests-local.sh -p 8.2 -w latest -f 'EDD\\Tests\\Settings'
bin/run-tests-local.sh -p 8.2 -w latest -f 'EDD\\Tests\Settings\Setting'
