#!/usr/bin/env bash

if [ $# -lt 3 ]; then
	echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-database-creation] [extra-plugins]"
	exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4-localhost}
WP_VERSION=${5-nightly}
SKIP_DB_CREATE=${6-false}
EXTRA_PLUGINS=$7

# Account for these variables being empty not just missing
if [[ -z "$7" ]]; then
	EXTRA_PLUGINS=false
fi

TMPDIR=${TMPDIR-/tmp}
TMPDIR=$(echo $TMPDIR | sed -e "s/\/$//")
WP_TESTS_DIR=${WP_TESTS_DIR-$TMPDIR/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR-$TMPDIR/wordpress}

source "$(dirname "$0")/lib/auth.sh"
source "$(dirname "$0")/lib/github.sh"

recreate_db() {
	mysqladmin drop $DB_NAME -f --user="$DB_USER" --password="$DB_PASS"$EXTRA
	create_db
	echo "Recreated the database ($DB_NAME)."
}

create_db() {
	mysqladmin create $DB_NAME --user="$DB_USER" --password="$DB_PASS"$EXTRA
}

install_db() {

	if [ ${SKIP_DB_CREATE} = "true" ]; then
		return 0
	fi

	# parse DB_HOST for port or socket references
	local PARTS=(${DB_HOST//\:/ })
	local DB_HOSTNAME=${PARTS[0]};
	local DB_SOCK_OR_PORT=${PARTS[1]};
	local EXTRA=""

	if ! [ -z $DB_HOSTNAME ] ; then
		if [ $(echo $DB_SOCK_OR_PORT | grep -e '^[0-9]\{1,\}$') ]; then
			EXTRA=" --host=$DB_HOSTNAME --port=$DB_SOCK_OR_PORT --protocol=tcp"
		elif ! [ -z $DB_SOCK_OR_PORT ] ; then
			EXTRA=" --socket=$DB_SOCK_OR_PORT"
		elif ! [ -z $DB_HOSTNAME ] ; then
			EXTRA=" --host=$DB_HOSTNAME --protocol=tcp"
		fi
	fi

	# create database
	if [ $(mysql --user="$DB_USER" --password="$DB_PASS"$EXTRA --execute='show databases;' | grep ^$DB_NAME$) ]
	then
		recreate_db
	else
		create_db
	fi
}

install_recurring() {
	if [[ "$EXTRA_PLUGINS" == "recurring" ]]; then
		# Resolve and validate the token (COMPOSER_AUTH > gh CLI > composer auth.json),
		# then delegate the download and install to the installer script.
		resolve_and_validate_auth "EDD Recurring"
		GITHUB_TOKEN=$(extract_github_token "${COMPOSER_AUTH}")
		"$(dirname "$0")/installers/recurring.sh" "${GITHUB_TOKEN}" "${WP_CORE_DIR}"
	fi
}

install_elementor() {
	# Elementor is public on wp.org, so no token is required. Match "elementor"
	# anywhere in the extra value so a future comma/multi-extra list still triggers it.
	if [[ "$EXTRA_PLUGINS" == *"elementor"* ]]; then
		"$(dirname "$0")/installers/elementor.sh" "${WP_CORE_DIR}"
	fi
}

set -e

printf "\n"
"$(dirname "$0")/installers/wp.sh" "${WP_VERSION}" "${WP_CORE_DIR}" "${WP_TESTS_DIR}" "${DB_NAME}" "${DB_USER}" "${DB_PASS}" "${DB_HOST}"

printf "\n"
printf "Installing database"
install_db
printf "\r✔ Installing database"

printf "\n"
install_recurring
printf "\n"
install_elementor
printf "\n"
