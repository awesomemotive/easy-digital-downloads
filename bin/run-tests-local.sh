#!/bin/bash

set -eo pipefail

OPTS=$(getopt -a --options w:p:f:imh --longoptions wp:filter:,php:,multisite,in-place,help --name "$0" -- "$@") || exit 1
eval set -- "$OPTS"

show_help() {
  printf -- 'Usage: %s --php|-p PHP_VERSION --wp|-w WP_VERSION [OPTIONS]\n\n' "$0";

  echo "Options:";
  printf -- '-i, --in-place\t\tDoes tests in-place without copying the repo to another dir and nuking composer files. This is faster but will add composer.lock/vendor dirs to your checkout. (Default: no)\n';
  printf -- '-m, --multisite\t\tRun tests as multisite?\n';
  printf -- '-p, --php\t\tSets the PHP version to test with (Required)\n';
  printf -- '-w, --wp\t\tSets WP version to test against (Required)\n';
  printf -- '-f, --filter\t\tPasses filters into PHPUnit\n';
  printf -- '-h, --help\t\tShow help.\n';
}

while true; do
  case "$1" in
    --in-place|-i )
        TEST_INPLACE=1
        shift
        ;;
    --multisite|-m )
        TEST_WP_MULTISITE="1"
        shift
        ;;
    --php|-p )
        export TEST_PHP_VERSION="$2"
        shift 2
        ;;
    --wp|-w )
        export TEST_WP_VERSION="$2"
        shift 2
        ;;
    --help|-h )
        show_help
        exit 0
        shift
        ;;
    --filter|-f )
        export FILTER="$2"
        shift 2
        ;;
    --)
        shift
        break
        ;;
    *)
        show_help
        exit 1
        ;;
  esac
done

if [[ -z "${TEST_PHP_VERSION}" ]] || [[ -z "${TEST_WP_VERSION}" ]]; then
	show_help;
	exit 1;
fi

# Default WP_MULTISITE to 0
export WP_MULTISITE="${TEST_WP_MULTISITE:-0}"
# Default TEST_INPLACE to 0
export TEST_INPLACE="${TEST_INPLACE:-0}"

# Default TEST_ACTIONS to 0
export TEST_ACTIONS="${TEST_ACTIONS:-0}"

# Create a random project name
export COMPOSE_PROJECT_NAME="$(cat /dev/urandom | tr -dc 'a-z0-9' | fold -w 32 | head -n 1)"

# If TEST_ACTIONS is set to 1 and the wp command exists, try and run our action checks.
if [[ $TEST_ACTIONS == 1 ]]; then
	if [[ -f "$(which wp)" ]]; then
		set +e
		site_url=$(wp option get siteurl 2>&1 | tail -n1)

		# If the last command had a non 0 exit code, then we know that the siteurl option is not set.
		if [[ $? != 0 ]]; then
			printf "\e[1;31mError: The siteurl option is not available. Skipping action tests.\e[0m"
			printf "\n"
			printf "\n"
		elif [[ $(curl -s -o /dev/null -w "%{http_code}" "$site_url") != "200" ]]; then
			printf "\e[1;31mError: The site at ${site_url} is not available. Skipping action tests.\e[0m"
			printf "\n"
			printf "\n"
		else
			printf "\e[1;32mSite found at ${site_url}. Running action tests...\e[0m"
			printf "\n"
			printf "\n"

			# Run our checks on admin actions.
			./bin/check-actions.sh
		fi
	else
		printf "\e[1;31mWP-CLI not found or Offline. Skipping action tests.\e[0m"
		printf "\n"
		printf "\n"
	fi
else
	printf "\e[1;31mTEST_ACTIONS is not enabled. Skipping action tests.\e[0m"
	printf "\n"
	printf "To enable Action tests set the TEST_ACTIONS environment variable to '1' either in your shell's .rc file or by running the following command:"
	printf "\n"
	printf "export TEST_ACTIONS=1"
	printf "\n"
	printf "\n"
fi

# Do this to make sure we cleanup
set +e
echo "Starting Docker containers..."
docker-compose --progress quiet -f docker-compose-phpunit.yml run -e "TEST_INPLACE=${TEST_INPLACE}" --rm --user $(id -u):$(id -g) wordpress

echo "Removing Docker containers..."
docker-compose --progress quiet -f docker-compose-phpunit.yml down -v
