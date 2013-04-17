<?php

putenv('WP_TESTS_DIR=../tmp/wordpress-tests/');

define( 'EDD_PLUGIN_DIR', dirname( dirname( __FILE__ ) ) . '/' );

require_once getenv( 'WP_TESTS_DIR' ) . '/includes/functions.php';

function _install_and_load_edd() {
	require dirname( __FILE__ ) . '/includes/loader.php';
}
tests_add_filter( 'muplugins_loaded', '_install_and_load_edd' );

require getenv( 'WP_TESTS_DIR' ) . '/includes/bootstrap.php';