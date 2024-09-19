# [Easy Digital Downloads](https://easydigitaldownloads.com) #

![Plugin Version](https://img.shields.io/wordpress/plugin/v/easy-digital-downloads.svg?maxAge=172800) ![Total Downloads](https://img.shields.io/wordpress/plugin/dt/easy-digital-downloads.svg?maxAge=172800) ![Plugin Rating](https://img.shields.io/wordpress/plugin/r/easy-digital-downloads.svg?maxAge=172800) ![WordPress Compatibility](https://img.shields.io/wordpress/v/easy-digital-downloads.svg?maxAge=172800) [![License](https://img.shields.io/badge/license-GPL--2.0%2B-blue.svg)](https://github.com/easydigitaldownloads/easy-digital-downloads/blob/master/license.txt)

### Welcome to our GitHub Repository

Selling digital downloads is something that not a single one of the large WordPress ecommerce plugins has ever gotten really right. This plugin aims to fix that. Instead of focusing on providing every single feature under the sun, Easy Digital Downloads tries to provide only the ones that you really need. It aims to make selling digital downloads through WordPress easy, and complete.

More information can be found at [easydigitaldownloads.com](https://easydigitaldownloads.com/).

## Installation ##

For detailed setup instructions, visit the official [Documentation](https://easydigitaldownloads.com/documentation/) page.

1. You can clone the GitHub repository: `https://github.com/easydigitaldownloads/easy-digital-downloads.git`
2. Or download it directly as a ZIP file: `https://github.com/easydigitaldownloads/easy-digital-downloads/archive/master.zip`

This will download the latest developer copy of Easy Digital Downloads.

## Bugs ##
If you find an issue, let us know [here](https://github.com/easydigitaldownloads/easy-digital-downloads/issues?state=open)!

## Support ##
This is a developer's portal for Easy Digital Downloads and should _not_ be used for support. Please visit the [support page](https://easydigitaldownloads.com/support) if you need to submit a support request.

## Contributions ##
Anyone is welcome to contribute to Easy Digital Downloads. Please read the [guidelines for contributing](https://github.com/easydigitaldownloads/easy-digital-downloads/blob/master/CONTRIBUTING.md) to this repository.

There are various ways you can contribute:

1. Raise an [Issue](https://github.com/easydigitaldownloads/easy-digital-downloads/issues) on GitHub
2. Send us a Pull Request with your bug fixes and/or new features
3. Translate Easy Digital Downloads into [different languages](https://translate.wordpress.org/projects/wp-plugins/easy-digital-downloads/)
4. Provide feedback and suggestions on [enhancements](https://github.com/easydigitaldownloads/easy-digital-downloads/issues?direction=desc&labels=Enhancement&page=1&sort=created&state=open)

## Build Scripts
EDD has multiple scripts to prepare installable packages:
* `build`: Generate the final files for a release. This script regenerates all CSS/JS, runs translations, and copies files to a build directory. This does not generate zip files.
* `build:blocks`: This is for internal use only and rebuilds the blocks asset files.
* `dev`: Watch and rebuild CSS/JS files; this will watch for changes and automatically rebuild assets.
* `lite`: Generate an `easy-digital-downloads-x.x.x.zip` file, the lite version of EDD. Does not rebuild assets. Useful for quickly creating an installable package.
* `local`: Generate zip files for both lite and pro. Does not rebuild assets.
* `package`: Rebuild assets, run translations, copy files, and compress files for installation on any WordPress site, for both lite and pro versions of EDD.
* `postinstall`: Runs automatically after `npm install`; do not use directly.
* `pro`: Generate an `easy-digital-downloads-pro-x.x.x.zip` file, the pro version of EDD. Does not rebuild assets. Useful for quickly creating an installable package.
* `repo`: Generate a directory of files to copy over to the public repository. Updates the translation.
* `test:unit`: Run JS unit tests.
* `translate`: Translate EDD (Pro).
* `translate:lite`: Translate the public version of EDD. Runs as part of `npm run lite`; generally do not run this directly.
* `translate:repo`: Translate the repository copy of EDD. Runs as part of `npm run repo`; generally do not run this directly.
* `update`: Updates all Composer packages and runs the Mozart script. Only run this if a Composer library needs to be updated.
