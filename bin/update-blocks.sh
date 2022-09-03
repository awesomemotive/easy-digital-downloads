# Remove anything that already exists.
rm -rf includes/blocks

# Initial clone (todo: change to main)
git clone -b rock/core-blocks git@github.com:awesomemotive/edd-blocks includes/blocks

# Install dependencies
cd includes/blocks
composer install --no-dev
npm install && npm run build

git rev-parse HEAD > ../.blocks-hash

# Clean up files for distribution.
# @todo Maybe use git archive? However composer.json would
# need to be removed from .gitattributes export-ignore
rm -rf node_modules
rm -rf languages
rm -rf src
rm -rf .git
rm -rf .github
rm .gitattributes
rm .gitignore
rm .editorconfig
rm package-lock.json
rm package.json
rm readme.txt

# Reset cwd
cd ../../
