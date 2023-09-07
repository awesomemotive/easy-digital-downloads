#!/usr/bin/env bash

set -e

if [[ $# -lt 3 ]]; then
	echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version] [filter] [force download]"
	exit 1
fi

DB_NAME="$1"
DB_USER="$2"
DB_PASS="$3"
DB_HOST="${4-localhost}"
WP_VERSION="${5-latest}"
FILTER="${6-''}"
FORCE="${7-false}"

# Download and extract WordPress
bin/install-wp-tests.sh "${DB_NAME}" "${DB_USER}" "${DB_PASS}" "${DB_HOST}" "${WP_VERSION}" "${FORCE}"

# Default to copying the repo elsewhere
if [[ "${TEST_INPLACE:-x}" == "x" ]] || [[ "${TEST_INPLACE}" == "0" ]]; then
	REPO_DIR="/tmp/$(cat /dev/urandom | tr -dc 'a-z0-9' | fold -w 32 | head -n 1)"
	printf "\n"
	printf "Copying repo to %s" ${REPO_DIR}
	mkdir "${REPO_DIR}"
	tar -cf - --exclude-from="./bin/.exclude" . | tar -xC "${REPO_DIR}"
	cd "${REPO_DIR}"
	if [[ -d vendor ]]; then
		rm -rf vendor
	fi
	if [[ -f composer.lock ]]; then
		rm composer.lock
	fi

	printf "\r\xE2\x9C\x94 Copying repo to %s" ${REPO_DIR}
	printf "\n"
else
	printf "Running inplace..."
fi

if [[ $WP_VERSION == 5.8* ]]; then
	sed -i 's/\"phpunit\/phpunit\": \"\^8\"/\"phpunit\/phpunit\": \"\^7\.5\"/' composer.json
fi

printf "\n"
printf "Installing composer dependencies"
composer -q --no-cache install
printf "\r\xE2\x9C\x94 Installing compser dependencies"
printf "\n"

coverage=""
if [[ ${COVERAGE} == 1 ]]; then

coverage=" --coverage-clover coverage.xml"

# Set the XDEBUG_MODE environment variable to 'coverage'
export XDEBUG_MODE=coverage
fi

if [[ -z "${FILTER}" ]]; then
	printf "No filter provided, running all tests."
	filter=""
else
	printf "Filter provided, running only tests matching: %s" ${filter}
	filter=" --filter ${FILTER}"
fi

printf "\n"
printf "Running tests with flags ${coverage} ${filter}"
printf "\n"

vendor/bin/phpunit${filter} -c phpunit.xml${coverage}

# Reset the XDEBUG_MODE environment variable to 'off'
export XDEBUG_MODE=off


if [[ -z "${CODECOV_TOKEN}" ]]; then
  printf "\n"
  printf "No CodeCov token provided, skipping upload."
else
	printf "\n"
	printf "Uploading coverage report to CodeCov."
	printf "\n"

	# Get the CodeCoverage uploader
	curl -Os https://uploader.codecov.io/latest/alpine/codecov

	# Make it executable
	chmod +x codecov

	# Now upload the coverage report with the token, file, commit hash, and branch.
	./codecov -t ${CODECOV_TOKEN} -f coverage.xml -C ${BUILDKITE_COMMIT} -B ${BUILDKITE_BRANCH}
fi
