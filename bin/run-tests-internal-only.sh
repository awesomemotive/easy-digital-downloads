#!/usr/bin/env bash

set -e

if [[ $# -lt 3 ]]; then
	echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version] [force download]"
	exit 1
fi

DB_NAME="$1"
DB_USER="$2"
DB_PASS="$3"
DB_HOST="${4-localhost}"
WP_VERSION="${5-latest}"
FORCE="${6-false}"

bin/install-wp-tests.sh "${DB_NAME}" "${DB_USER}" "${DB_PASS}" "${DB_HOST}" "${WP_VERSION}" "${FORCE}"

# Default to copying the repo elsewhere
if [[ "${TEST_INPLACE:-x}" == "x" ]] || [[ "${TEST_INPLACE}" == "0" ]]; then
	REPO_DIR="/tmp/$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1)"
	echo "Copying repo to ${REPO_DIR} to and deleting composer files..."
	mkdir "${REPO_DIR}"
	cp -R . "${REPO_DIR}"
	cd "${REPO_DIR}"
	if [[ -d vendor ]]; then
		rm -rf vendor
	fi
	if [[ -f composer.lock ]]; then
		rm composer.lock
	fi
else
	echo "Running inplace..."
fi

composer --no-cache install
vendor/bin/phpunit
