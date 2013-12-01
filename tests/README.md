# Easy Digital Downloads Test Suite [![Build Status](https://secure.travis-ci.org/easydigitaldownloads/Easy-Digital-Downloads.png?branch=master)](http://travis-ci.org/easydigitaldownloads/Easy-Digital-Downloads)

Version: 1.1

Authors: [Chris Christoff](http://www.github.com/chriscct7) and [Sunny Ratilal](http://github.com/sunnyratilal)

-------------------------

The Easy Digital Downloads Test Suite uses PHPUnit to maintain Easy Digital Downloads's code quality.

Travis-CI Automated Testing
-----------

The master branch of Easy Digital Downloads is automatically tested on [travis-ci.org](http://travis-ci.org). The image above will show you the latest test's output. Travis-CI will also automatically test all new Pull Requests to make sure they will not break our build.

Quick Start (For Manual Runs)
-----------------------------

	# Clone the repository
    git clone git://github.com/easydigitaldownloads/Easy-Digital-Downloads.git
    cd Easy-Digital-Downloads

    # Download Sunny Ratilal's Adapted WordPress Testing Suite
	wget -O testsuite.zip https://github.com/sunnyratilal/wordpress-tests/zipball/master

    # Unzip it into vendors
	unzip testsuite.zip -d travis/vendor
    cp -r tests/vendor/sunnyratilal-WordPress-Tests-* tests/vendor/wordpress-tests

Copy and edit the WordPress Unit Tests Configuration

    cp tests/includes/unittests-config.travis.php tests/vendor/wordpress-tests/unittests-config.php

Now edit `unittests-config.php` in a code editor. Make sure to have an empty database ready (all data will die) and that your path to WordPress is correct.

Easy Digital Downloads does not need to be in the `wp-content/plugins` directory. For example in Travis-CI's `.travis.yml` we copy WordPress into `vendor/wordpress`

    <?php
    /* Path to the WordPress codebase you'd like to test. Add a backslash in the end. */
    define( 'ABSPATH', 'path-to-WP/' );
    define( 'DB_NAME', 'edd_test' );
    define( 'DB_USER', 'user' );
    define( 'DB_PASSWORD', 'password' );

    # .. more you probably don't need to edit

Load up the Terminal and cd into the directory where Easy Digital Downloads is stored and run this command:

    phpunit

Please note: MySQL will need to be on otherwise the unit tests will fail and you'll receive WordPress's 'Error Establishing a Database Connection.'

# Installing PHPUnit on Ubuntu

If you're using Ubuntu 12+, you can install PHPUnit like this:

    sudo apt-get install pear
    sudo pear config-set auto_discover 1
    sudo pear install pear.phpunit.de/PHPUnit
	

# Installing PHPUnit on Windows

###Step 1A: Install PEAR (if your localhost **does not** come with it)
Easiest way is to install PEAR is to download go-pear.phar.
Put it in your PHP bin folder.
Then open CMD (make sure you right click run as admin, **even** if you are admin) and run:

	php -d phar.require_hash=0 PEAR/go-pear.phar

For prompts:
Enter, Enter, Enter, Enter

Then open a new CMD and type in

	pear

Should produce output.

### Step 1B: Install PEAR (if your localhost **does** come with it)
Depending on your OS distribution and/or your PHP environment, you may need to install PEAR or update your existing PEAR installation before you can proceed with the instructions in this section.

	pear upgrade PEAR

Above usually suffices to upgrade an existing PEAR installation. The PEAR Manual explains how to perform a fresh installation of PEAR.

### Step 2: Install PHPUnit by doing:

	pear config-set auto_discover 1
	pear install --force --alldeps pear.phpunit.de/PHPUnit

-------------------------	
	
##### Note: Due to a bug present in WordPress 3.5 MU, you may see database errors on install.

# External Testing URLs #
* Travis-CI (automated unit testing): https://travis-ci.org/easydigitaldownloads/Easy-Digital-Downloads/
* Coveralls.io (automated code coverage): https://coveralls.io/r/easydigitaldownloads/Easy-Digital-Downloads

Other Supported (manually run):
* PHPCI: http://www.phptesting.org/
* PHPUnit: http://phpunit.de/manual/current/en/index.html

# Changelog:
Version 1.0: 
* Initial development

Version 1.1 (Developed alongside EDD v1.8 with #1559 + independent unit test commits):
* Reverts the use of the "strict" PHPUnit option
* Removes the old EDD coverage calculator
* Integrates with the new Coveralls.io powered code-coverage calculator
* Adds coveralls.io icon to readme.md
* Removes the Travis kill switch (added in 76db0691eec21ed066ad4fe81174d4b6177d4c88) its now an option in Travis-CI
* Covers points of #1020 to keep
* Forces unit testing to use phpunit.xml
* Adds coverall.yml config file
* Adds composer.yml file (required to use coveralls.io)
* Solves #1556 (Travis System Upgrades)
* Updates the filterlist for blacklisted code coverage files/dirs
