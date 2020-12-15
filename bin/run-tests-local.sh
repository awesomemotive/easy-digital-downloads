#!/bin/bash

set -eo pipefail

OPTS=$(getopt -a --options w:p:imh --longoptions wp:,php:,multisite,in-place,help --name "$0" -- "$@") || exit 1
eval set -- "$OPTS"

show_help() {
  printf -- 'Usage: %s --php|-p PHP_VERSION --wp|-w WP_VERSION [OPTIONS]\n\n' "$0";

  echo "Options:";
  printf -- '-i, --in-place\t\tDoes tests in-place without copying the repo to another dir and nuking composer files. This is faster but will add composer.lock/vendor dirs to your checkout. (Default: no)\n';
  printf -- '-m, --multisite\t\tRun tests as multisite?\n';
  printf -- '-p, --php\t\tSets the PHP version to test with (Required)\n';
  printf -- '-w, --wp\t\tSets WP version to test against (Required)\n';
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

# Create a random project name
export COMPOSE_PROJECT_NAME="$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1)"

# Do this to make sure we cleanup
set +e
docker-compose -f docker-compose-phpunit.yml run -e "TEST_INPLACE=${TEST_INPLACE}" --rm --user $(id -u):$(id -g) wordpress
docker-compose -f docker-compose-phpunit.yml down --remove-orphans
