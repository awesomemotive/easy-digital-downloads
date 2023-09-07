# Remove anything that already exists.
rm -rf includes/blocks

# Initial clone
git clone git@github.com:awesomemotive/edd-blocks includes/blocks

# Install dependencies
cd includes/blocks
composer install --no-dev
npm install && npm run build

git rev-parse HEAD > ../.blocks-hash

# Clean up files for distribution.
# @todo Maybe use git archive? However composer.json would
# need to be removed from .gitattributes export-ignore
rm -rf languages
rm -rf .git
rm -rf .github
rm -rf vendor
rm .gitattributes
rm .gitignore
rm .editorconfig
rm package-lock.json
rm readme.md
rm readme.txt
rm composer.lock

# Reset cwd
cd ../../
