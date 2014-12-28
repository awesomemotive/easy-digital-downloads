#!/bin/bash
set -ev
if [ "${TRAVIS_PULL_REQUEST}" != "false" ]
then
	wget https://scrutinizer-ci.com/ocular.phar
	php ocular.phar code-coverage:upload --format=php-clover coverage.clover
fi
