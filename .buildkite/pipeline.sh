#!/bin/bash

# exit immediately on failure, or if an undefined variable is used
set -eu

# begin the pipeline.yml file
echo "steps:"

# First, handle all the 'modern' PHP/WP version combinations, that can run on PHPUnit 8.x.
phpVersions=('7.2' '7.3' '7.4' '8.0')
wpVersions=('6.1.1' 'latest')

# For now we don't have any exclusions, add them in `<PHP Version-WP Version> if we do`
# And then uncomment out the if check in the loop below.
# exclusions=()

# add a new command step to run the tests in each test directory
for phpVersion in ${phpVersions[@]}; do
  for wpVersion in ${wpVersions[@]}; do

    #if [[ " ${exclusions[@]} " =~ " ${phpVersion}-${wpVersion} " ]]; then
      #continue
    #fi

    echo "  - env:"
    echo "      TEST_INPLACE: \"0\""
    echo "      TEST_PHP_VERSION: \""$phpVersion"\""
    echo "      TEST_WP_VERSION: "$wpVersion""
    echo "      WP_MULTISITE: \"0\""
    echo "      COMPOSE_HTTP_TIMEOUT: 180"
    echo "      DOCKER_CLIENT_TIMEOUT: 180"
    echo "    label: 'PHP: "$phpVersion" | WP: "$wpVersion" | Multisite: No'"
    echo "    plugins:"
    echo "      - docker-compose#v4.9.0:"
    echo "          config: docker-compose-phpunit.yml"
    echo "          env:"
    echo "            - WP_MULTISITE"
    echo "            - TEST_INPLACE"
    echo "          propagate-uid-gid: true"
    echo "          pull-retries: 3"
    echo "          run: wordpress"
  done
done

echo "  - env:"
echo "      TEST_INPLACE: \"0\""
echo "      TEST_PHP_VERSION: \""$phpVersion"\""
echo "      TEST_WP_VERSION: "$wpVersion""
echo "      WP_MULTISITE: \"1\""
echo "      COMPOSE_HTTP_TIMEOUT: 180"
echo "      DOCKER_CLIENT_TIMEOUT: 180"
echo "    label: 'PHP: "$phpVersion" | WP: "$wpVersion" | Multisite: Yes'"
echo "    plugins:"
echo "      - docker-compose#v4.9.0:"
echo "          config: docker-compose-phpunit.yml"
echo "          env:"
echo "            - WP_MULTISITE"
echo "            - TEST_INPLACE"
echo "          propagate-uid-gid: true"
echo "          pull-retries: 3"
echo "          run: wordpress"

# WP Versions lower than 5% just get tested against PHP 8.x
lowUseWpVersions=('5.9.5' '6.0.3')
for lowUseWpVersion in ${lowUseWpVersions[@]}; do
  echo "  - env:"
  echo "      TEST_INPLACE: \"0\""
  echo "      TEST_PHP_VERSION: \"8.0\""
  echo "      TEST_WP_VERSION: "$lowUseWpVersion""
  echo "      WP_MULTISITE: \"0\""
  echo "      COMPOSE_HTTP_TIMEOUT: 180"
  echo "      DOCKER_CLIENT_TIMEOUT: 180"
  echo "    label: 'PHP: 8.0 | WP: "$lowUseWpVersion" | Multisite: No'"
  echo "    plugins:"
  echo "      - docker-compose#v4.9.0:"
  echo "          config: docker-compose-phpunit.yml"
  echo "          env:"
  echo "            - WP_MULTISITE"
  echo "            - TEST_INPLACE"
  echo "          propagate-uid-gid: true"
  echo "          pull-retries: 3"
  echo "          run: wordpress"
done

# Now, we can handle all the PHPUnit 7.x combinations.
# We will be working to slowly work the required minimums up, so these should get smaller and smaller.
legacyPhpVersions=('7.1')
legacyWpVersions=('5.4.12' '5.5.11' '5.6.10' '5.7.8' '5.8.6')

# For now we don't have any exclusions, add them in `<PHP Version-WP Version> if we do`
# And then uncomment out the if check in the loop below.
#legacyExclusions=()

# add a new command step to run the tests in each test directory
for legacyPhpVersion in ${legacyPhpVersions[@]}; do
  for legacyWpVersion in ${legacyWpVersions[@]}; do

    #if [[ " ${legacyExclusions[@]} " =~ " ${legacyPhpVersion}-${legacyWpVersion} " ]]; then
      #continue
    #fi

    echo "  - env:"
    echo "      TEST_INPLACE: \"0\""
    echo "      TEST_PHP_VERSION: \""$legacyPhpVersion"\""
    echo "      TEST_WP_VERSION: "$legacyWpVersion""
    echo "      WP_MULTISITE: \"0\""
    echo "      COMPOSE_HTTP_TIMEOUT: 180"
    echo "      DOCKER_CLIENT_TIMEOUT: 180"
    echo "    label: 'PHP: "$legacyPhpVersion" | WP: "$legacyWpVersion" | Multisite: No'"
    echo "    plugins:"
    echo "      - docker-compose#v4.9.0:"
    echo "          config: docker-compose-phpunit.yml"
    echo "          env:"
    echo "            - WP_MULTISITE"
    echo "            - TEST_INPLACE"
    echo "          propagate-uid-gid: true"
    echo "          pull-retries: 3"
    echo "          run: wordpress"
  done
done
