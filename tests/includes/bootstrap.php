<?php
/**
 * Install WordPress and Easy Digital Downloads
 */

error_reporting( E_ALL & ~E_DEPRECATED & ~E_STRICT );

echo "\n\nWelcome to the Easy Digital Downloads PHPUnit Test Suite \n";
echo "Version: 1.0 \n";
echo "Authors: Chris Christoff and Sunny Ratilal \n\n\n";
echo "Preparing to load WordPress Bootstrap File...\n";

$path = './tests/vendor/wordpress-tests/bootstrap.php';

if ( file_exists( $path ) ) {
	require_once $path;
} else {
	exit( "The WordPress bootstrap file couldn't be loaded.\n" );
}

echo "WordPress Boostrap File Loaded \n\n";
echo "Preparing to load Easy Digital Downloads... \n";

$loader = './easy-digital-downloads.php';

if ( file_exists( $loader ) ) {
	require_once $loader;
} else {
	exit( "Couldn't load Easy Digital Downloads \n" );
}

echo "Easy Digital Downloads Loaded \n";
echo "Installing Easy Digital Downloads... \n";
edd_install();
echo "Easy Digital Downloads Installed \n";

echo "Loading Custom Die Handler...\n";

$die_handler = './tests/includes/die-handler.php';

if ( file_exists( $die_handler ) ) {
	require_once $die_handler;
} else {
	exit( "Couldn't load custom die handler \n" );
}

echo "Loaded Custom Die Handler\n";

echo "Preparing to execute PHPUnit Tests...\n";

$_SESSION['travis'] = 'true';