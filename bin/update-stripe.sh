# Remove anything that already exists.
rm -rf includes/gateways/stripe

# Initial clone
git clone -b try/separate-pro git@github.com:easydigitaldownloads/edd-stripe includes/gateways/stripe

# Install dependencies
cd includes/gateways/stripe
composer install --no-dev
npm install && npm run build

# Edit bootstrap function name to avoid collision.
sed -i '' 's/edd_stripe_bootstrap/edd_stripe_core_bootstrap/g' edd-stripe.php
sed -i '' '$ d' edd-stripe.php

# Clean up files for distribution.
# @todo Maybe use git archive? However composer.json would
# need to be removed from .gitattributes export-ignore
rm -rf node_modules
rm -rf tests
rm -rf bin
rm -rf languages
rm -rf includes/pro
rm -rf .git
rm -rf .github
rm .gitattributes
rm .gitignore
rm .npmrc
rm package-lock.json
rm package.json
rm composer.json
rm composer.lock
rm webpack.config.js
rm phpcs.ruleset.xml
rm phpunit.xml.dist

# Reset cwd
cd ../../../
