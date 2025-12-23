#!/usr/bin/env bash

set -e

if [[ $# -lt 3 ]]; then
	echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version (default: latest)] [filter] [force download] [extra-plugins]"
	exit 1
fi

DB_NAME="$1"
DB_USER="$2"
DB_PASS="$3"
DB_HOST="${4-localhost}"
WP_VERSION="${5-latest}"
FILTER="${FILTER:-${6-''}}"
FORCE="${7-false}"
EXTRA_PLUGINS="${TEST_EXTRA_PLUGINS:-${8-false}}"

# Download and extract WordPress
bin/install-wp-tests.sh "${DB_NAME}" "${DB_USER}" "${DB_PASS}" "${DB_HOST}" "${WP_VERSION}" "${FORCE}" "${EXTRA_PLUGINS}"

printf "\n"
echo "🐘 PHP version:       $(php -v | head -n 1 | cut -d' ' -f2)"
echo "🌍 WordPress version: $WP_VERSION"
if [[ -n "${FILTER}" ]] && [[ "${FILTER}" != "false" ]]; then
	echo "🔍 Filter:            ${FILTER}"
fi
if [[ "${WP_MULTISITE}" == "1" ]]; then
	echo "🔀 Multisite:         enabled"
fi
if [[ "${EXTRA_PLUGINS}" == "recurring" ]]; then
	echo "🔌 Extra plugins:     EDD Recurring"
fi
printf "\n"

# Default to copying the repo elsewhere
if [[ "${TEST_INPLACE:-x}" == "x" ]] || [[ "${TEST_INPLACE}" == "0" ]]; then
	REPO_DIR="/tmp/$(cat /dev/urandom | tr -dc 'a-z0-9' | fold -w 32 | head -n 1)"
	printf "\n"
	printf "Copying repo to %s" ${REPO_DIR}
	mkdir "${REPO_DIR}"
	tar -cf - --exclude-from="./bin/.exclude" . | tar -xC "${REPO_DIR}"
	cd "${REPO_DIR}"

	printf "\r✔ Copying repo to %s" ${REPO_DIR}
	printf "\n"
else
	printf "Running inplace..."
fi

printf "\n"
printf "Installing composer dependencies"
composer -q --no-cache install
printf "\r✔ Installing composer dependencies"
printf "\n"

# Move files from the bin/compat directory into place, replacing any files we find.
printf "Copying compat files into place"
cp -R bin/compat/* .
printf "\r✔ Copying compat files into place"
printf "\n\n"

# Set up filter argument for phpunit
if [[ -z "${FILTER}" ]] || [[ "${FILTER}" == "false" ]]; then
	filter=""
else
	filter=" --filter ${FILTER}"
fi

vendor/bin/phpunit${filter} -c phpunit.xml
