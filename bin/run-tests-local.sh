#!/bin/bash

set -eo pipefail

source "$(dirname "$0")/lib/auth.sh"

show_help() {
  printf -- 'Usage: %s --php|-p PHP_VERSION --wp|-w WP_VERSION [OPTIONS]\n\n' "$0";

  echo "Options:";
  printf -- '-i, --in-place\t\tDoes tests in-place without copying the repo to another dir and nuking composer files. This is faster but will add composer.lock/vendor dirs to your checkout. (Default: no)\n';
  printf -- '-m, --multisite\t\tRun tests as multisite?\n';
  printf -- '-p, --php\t\tSets the PHP version to test with (Default: 8.2)\n';
  printf -- '-w, --wp\t\tSets WP version to test against (Default: latest)\n';
  printf -- '-f, --filter\t\tPasses filters into PHPUnit\n';
  printf -- '-g, --group\t\tRuns only tests in the given PHPUnit @group (comma-separate for several)\n';
  printf -- '-x, --extra\t\tSets additional plugins to include (e.g. "recurring" for EDD Recurring)\n';
  printf -- '-a, --actions\t\tRuns the edd_action heartbeat check against your local WP-CLI site (e.g. edd.local) before the test run.\n';
  printf -- '-h, --help\t\tShow help.\n';
  echo "";
  echo "Authentication (for --extra recurring):";
  printf -- 'A GitHub token is resolved automatically from the gh CLI (gh auth login).\n';
  printf -- 'Falls back to COMPOSER_AUTH, then Composer'"'"'s global auth.json.\n';
  echo "";
  echo "Environment Variables:";
  printf -- 'COMPOSER_AUTH\t\tComposer authentication JSON. Overrides the gh CLI when set.\n';
  echo '              		Example: export COMPOSER_AUTH='"'"'{"github-oauth":{"github.com":"your_token_here"}}'"'"
}

# Portable manual argument parsing (POSIX/bash, no GNU getopt — works on macOS BSD too).
# Supports long `--opt value` and `--opt=value`, short `-x value`, and `-h/--help`.
# require_arg ensures a value-taking flag was actually given an argument.
require_arg() {
  # $1 = flag name, $2 = candidate value (passed as "${2-}", so empty when missing).
  case "${2-}" in
    '' | -* )
      echo "Error: option '$1' requires an argument." >&2
      show_help
      exit 1
      ;;
  esac
}

while [ $# -gt 0 ]; do
  case "$1" in
    --in-place|-i )
        TEST_INPLACE=1
        shift
        ;;
    --multisite|-m )
        TEST_WP_MULTISITE="1"
        shift
        ;;
    --php=*|-p=* )
        export TEST_PHP_VERSION="${1#*=}"
        shift
        ;;
    --php|-p )
        require_arg "$1" "${2-}"
        export TEST_PHP_VERSION="$2"
        shift 2
        ;;
    --wp=*|-w=* )
        export TEST_WP_VERSION="${1#*=}"
        shift
        ;;
    --wp|-w )
        require_arg "$1" "${2-}"
        export TEST_WP_VERSION="$2"
        shift 2
        ;;
    --help|-h )
        show_help
        exit 0
        ;;
    --filter=*|-f=* )
        export FILTER="${1#*=}"
        shift
        ;;
    --filter|-f )
        require_arg "$1" "${2-}"
        export FILTER="$2"
        shift 2
        ;;
    --group=*|-g=* )
        export GROUP="${1#*=}"
        shift
        ;;
    --group|-g )
        require_arg "$1" "${2-}"
        export GROUP="$2"
        shift 2
        ;;
    --extra=*|-x=* )
        export TEST_EXTRA_PLUGINS="${1#*=}"
        shift
        ;;
    --extra|-x )
        require_arg "$1" "${2-}"
        export TEST_EXTRA_PLUGINS="$2"
        shift 2
        ;;
    --actions|-a )
        TEST_ACTIONS=1
        shift
        ;;
    --)
        shift
        break
        ;;
    -*)
        echo "Error: unknown option '$1'." >&2
        show_help
        exit 1
        ;;
    *)
        echo "Error: unexpected argument '$1'." >&2
        show_help
        exit 1
        ;;
  esac
done

# Set defaults if not provided
export TEST_PHP_VERSION="${TEST_PHP_VERSION:-8.2}"
export TEST_WP_VERSION="${TEST_WP_VERSION:-latest}"

# Default WP_MULTISITE to 0
export WP_MULTISITE="${TEST_WP_MULTISITE:-0}"
# Default TEST_INPLACE to 0
export TEST_INPLACE="${TEST_INPLACE:-0}"

# Default TEST_ACTIONS to 0
export TEST_ACTIONS="${TEST_ACTIONS:-0}"

# Default FILTER to empty string
export FILTER="${FILTER:-}"

# Default GROUP to empty string
export GROUP="${GROUP:-}"

# Default TEST_EXTRA_PLUGINS to empty
export TEST_EXTRA_PLUGINS="${TEST_EXTRA_PLUGINS:-}"

# Default COMPOSER_AUTH to empty so docker-compose does not warn about an unset
# variable on runs that don't need auth. When --extra recurring is used,
# resolve_and_validate_auth below overwrites this with the resolved token.
export COMPOSER_AUTH="${COMPOSER_AUTH:-}"

# Resolve and validate auth before spinning up Docker for flags that require it.
if [[ "${TEST_EXTRA_PLUGINS}" == "recurring" ]]; then
    resolve_and_validate_auth "EDD Recurring"
fi

# Create a random project name
export COMPOSE_PROJECT_NAME="$(head -c 1000 /dev/urandom | LC_ALL=C tr -dc 'a-z0-9' | head -c 32)"

# If TEST_ACTIONS is set to 1 and the wp command exists, try and run our action checks.
if [[ $TEST_ACTIONS == 1 ]]; then
	if command -v wp &> /dev/null; then
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
fi

# Tear down containers and volumes on any exit (success, failure, or Ctrl-C),
# so interrupted runs don't leave orphans under their random project name.
cleanup() {
  echo "Removing Docker containers..."
  docker compose --progress quiet -f docker-compose-phpunit.yml down -v
}
trap cleanup EXIT

echo "Starting Docker containers..."
docker compose --progress quiet -f docker-compose-phpunit.yml run \
  -e "TEST_INPLACE=${TEST_INPLACE}" \
  -e "TEST_EXTRA_PLUGINS=${TEST_EXTRA_PLUGINS}" \
  -e "FILTER=${FILTER}" \
  -e "GROUP=${GROUP}" \
  -e "WP_MULTISITE=${WP_MULTISITE}" \
  -e "COMPOSER_AUTH=${COMPOSER_AUTH}" \
  --rm --user $(id -u):$(id -g) wordpress
