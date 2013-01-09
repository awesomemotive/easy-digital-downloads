[Easy Digital Downloads](http://www.easydigitaldownloads.com) Testing Suite [![Build Status](https://secure.travis-ci.org/pippinsplugins/Easy-Digital-Downloads.png?branch=master)](http://travis-ci.org/pippinsplugins/Easy-Digital-Downloads)
Version: 0.9.0
Author: @chriscct7
=================

This test-suite uses PHPUnit to ensure Easy Digital Downloads's code quality.

Travis-CI Automated Testing
-----------

The dev branch of Easy Digital Downloads is automatically tested on Travis-ci.org. 
Click on the image above to see the latest test's output.
Travis-CI will also automatically test all new pull requests to make sure they will not break our build.


Quick start (for manual runs)
-----------------------------

Clone the repo.

	# Get a copy of EDD
    git clone git://github.com/pippinsplugins/Easy-Digital-Downloads.git
    cd Easy-Digital-Downloads
    # init submodules to grab wordpress test helper
    git submodule init && git submodule update
    # Download the WP Testing Suite
	wget -O testsuite.zip https://github.com/nb/wordpress-tests/archive/460b9c4ad9db7eea4710f151851060ae1921ea7c.zip
    # Unzip it into vendors
	unzip testsuite.zip -d travis/vendor
    cp -r travis/vendor/wordpress-tests-460b9c4ad9db7eea4710f151851060ae1921ea7c travis/vendor/wordpress-tests

Copy & edit wordpress test environment file

    cp travis/tests/unittests-config.travis.php travis/vendor/wordpress-tests/unittests-config.php

Now edit unittests-config.php in your favorite editor. Make sure to have an empty database ready(all data will die) and
that your path to wordpress is correct.

EDD does not need to be in the wp/plugins dir. For example in travis-ci's .travis.yml we copy wordpress into vendor/wordpress

    <?php
    /* Path to the WordPress codebase you'd like to test. Add a backslash in the end. */
    define( 'ABSPATH', 'path-to-WP/' );
    define( 'DB_NAME', 'edd_test' );
    define( 'DB_USER', 'user' );
    define( 'DB_PASSWORD', 'password' );

    # .. more you probably don't need to edit


Run the test from EDD plugin root folder

    phpunit


Install phpunit on Ubuntu
-------------------------

In case your are using ubuntu(12+), install phpunit like this:

    sudo apt-get install pear
    sudo pear config-set auto_discover 1
    sudo pear install pear.phpunit.de/PHPUnit
	
##Install phpunit on Windows
--------------------------
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
Depending on your OS distribution and/or your PHP environment, you may need to install 
PEAR or update your existing PEAR installation before you can proceed with the instructions
in this section.

	pear upgrade PEAR

Above usually suffices to upgrade an existing PEAR installation. 
The PEAR Manual explains how to perform a fresh installation of PEAR.

### Step 2: Install PHPUnit by doing:

	pear config-set auto_discover 1
	pear install --force --alldeps pear.phpunit.de/PHPUnit
	
	
### Note: Due to a bug present in WordPress 3.5 MU, you may see database errors on install.