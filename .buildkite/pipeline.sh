#!/bin/bash

# exit immediately on failure, or if an undefined variable is used
set -eu

# begin the pipeline.yml file
echo "steps:"

phpVersions=('5.3' '5.4' '5.5' '5.6' '7.0' '7.1' '7.2' '7.3' '7.4')
wpVersions=('4.4.23' '4.5.22' '4.6.19' '4.7.18' '4.8.14' '4.9.15' '5.0.10' '5.1.6' '5.2.7' '5.3.4' '5.4.2' '5.5.3' '5.6.3' 'latest')

# Exclude combinations with <php version>-<wp version>
exclusions=('5.3-4.8.14' '5.3-4.9.15' '5.3-5.0.10' '5.3-5.1.6' '5.3-5.2.7' '5.3-5.3.4' '5.3-5.4.2' '5.3-5.5.3' '5.3-5.6.3' '5.3-latest' '5.4-5.0.10' '5.4-5.1.6' '5.4-5.2.7' '5.4-5.3.4' '5.4-5.4.2' '5.4-5.5.3' '5.4-5.6.3' '5.4-latest' '5.5-5.0.10' '5.5-5.1.6' '5.5-5.2.7' '5.5-5.3.4' '5.5-5.4.2' '5.5-5.5.3' '5.5-5.6.3' '5.5-latest' '7.1-4.4.23' '7.1-4.5.22' '7.1-4.6.19' 	'7.2-4.4.23' '7.2-4.5.22' '7.3-4.4.23' '7.3-4.5.22' '7.3-4.6.19' '7.3-4.7.18' '7.3-4.8.14' '7.3-4.9.15' '7.4-4.4.23' '7.4-4.5.22' '7.4-4.6.19' '7.4-4.7.18' '7.4-4.8.14' '7.4-4.9.15' '7.4-5.0.10' '7.4-5.1.6' '7.4-5.2.7' )

# add a new command step to run the tests in each test directory
for phpVersion in ${phpVersions[@]}; do
  for wpVersion in ${wpVersions[@]}; do

    if [[ " ${exclusions[@]} " =~ " ${phpVersion}-${wpVersion} " ]]; then
      continue
    fi

    echo "  - env:"
    echo "      TEST_INPLACE: \"0\""
    echo "      TEST_PHP_VERSION: \""$phpVersion"\""
    echo "      TEST_WP_VERSION: "$wpVersion""
    echo "      WP_MULTISITE: \"0\""
    echo "    label: 'PHP: "$phpVersion" | WP: "$wpVersion" | Multisite: No'"
    echo "    plugins:"
    echo "      - docker-compose#v3.7.0:"
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
echo "    label: 'PHP: "$phpVersion" | WP: "$wpVersion" | Multisite: Yes'"
echo "    plugins:"
echo "      - docker-compose#v3.7.0:"
echo "          config: docker-compose-phpunit.yml"
echo "          env:"
echo "            - WP_MULTISITE"
echo "            - TEST_INPLACE"
echo "          propagate-uid-gid: true"
echo "          pull-retries: 3"
echo "          run: wordpress"
