#!/usr/bin/env bash

set -e pipefail

printf "Running PHPStan\n"

# Default to copying the repo elsewhere
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

printf "\n"
printf "Installing composer dependencies"
composer -q --no-cache install
printf "\r\xE2\x9C\x94 Installing composer dependencies"
printf "\n"

vendor/bin/phpstan analyze --memory-limit=1G --no-ansi --no-progress > /tmp/artifacts/${BUILDKITE_BUILD_ID}/phpstan-report.txt
