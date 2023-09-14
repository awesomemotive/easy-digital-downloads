<?php
namespace EDD\Tests;

use Yoast\WPTestUtils\WPIntegration;

$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['SERVER_NAME'] = '';
$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

define( 'EDD_USE_PHP_SESSIONS', false );
define( 'WP_USE_THEMES', false );
define( 'EDD_DOING_TESTS', true );

$plugin_dir = dirname( dirname( __FILE__ ) );

require_once $plugin_dir . '/vendor/yoast/wp-test-utils/src/WPIntegration/bootstrap-functions.php';

// Find WordPress.
$_tests_dir = WPIntegration\get_path_to_wp_test_dir();

// Find WordPress core.
$_core_dir = getenv( 'WP_CORE_DIR' );

if ( ! $_core_dir ) {
	$_core_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // WPCS: XSS ok.
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugins.
 *
 * @since 1.0.0
 */
tests_add_filter(
	'muplugins_loaded',
	function() use ( $plugin_dir ) {
		require $plugin_dir . '/easy-digital-downloads.php';
	}
);

WPIntegration\bootstrap_it();

activate_plugin( 'easy-digital-downloads-pro/easy-digital-downloads.php' );

echo "Setting up Easy Digital Downloads...\n";

$components = EDD()->components;

foreach ( $components as $component ) {
	$thing = $component->get_interface( 'table' );

	if ( $thing instanceof \EDD\Database\Table ) {
		if ( $thing->exists() ) {
			$thing->uninstall();
		}

		$thing->install();
	}

	$thing = $component->get_interface( 'meta' );

	if ( $thing instanceof \EDD\Database\Table ) {
		if ( $thing->exists() ) {
			$thing->uninstall();
		}

		$thing->install();
	}
}

function _disable_reqs( $status = false, $args = array(), $url = '') {
}
add_filter( 'pre_http_request', function( $status = false, $args = array(), $url = '' ) {
	return new \WP_Error( 'no_reqs_in_unit_tests', __( 'HTTP Requests disabled for unit tests', 'easy-digital-downloads' ) );
} );

require_once 'helpers/shims.php';

remove_all_actions( 'send_headers' );
