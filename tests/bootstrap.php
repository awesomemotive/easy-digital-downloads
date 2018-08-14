<?php

$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['SERVER_NAME'] = '';
$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

define( 'EDD_USE_PHP_SESSIONS', false );
define( 'WP_USE_THEMES', false );
define( 'EDD_DOING_TESTS', true );

$_tests_dir = getenv('WP_TESTS_DIR');
if ( !$_tests_dir ) $_tests_dir = '/tmp/wordpress-tests-lib';

require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
	require dirname( __FILE__ ) . '/../easy-digital-downloads.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';

activate_plugin( 'easy-digital-downloads/easy-digital-downloads.php' );

echo "Setting up Easy Digital Downloads...\n";

// Block HTTP requests.
add_filter( 'pre_http_request', function() {
	return new WP_Error( 'no_reqs_in_unit_tests', __( 'HTTP Requests are disabled when running unit tests.', 'easy-digital-downloads' ) );
} );

// Include helpers
require_once 'helpers/shims.php';
require_once 'helpers/class-helper-download.php';
require_once 'helpers/class-helper-payment.php';
require_once 'helpers/class-helper-discount.php';
require_once 'phpunit/class-ajax-unittestcase.php';
require_once 'phpunit/class-edd-unittestcase.php';

