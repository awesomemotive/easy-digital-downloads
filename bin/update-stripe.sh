# Remove anything that already exists.
rm -rf includes/gateways/stripe

# Initial clone
git clone -b release/2.8.5 git@github.com:easydigitaldownloads/edd-stripe includes/gateways/stripe

# Install dependencies
cd includes/gateways/stripe
composer install --no-dev
npm install && npm run build

git rev-parse HEAD > ../.stripe-hash

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
