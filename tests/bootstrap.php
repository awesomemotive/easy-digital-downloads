<?php

define( 'EDD_PLUGIN_DIR', dirname( dirname( __FILE__ ) ) . '/' );

require_once '../tmp/wordpress-tests/includes/functions.php';

function _install_and_load_edd() {
	require dirname( __FILE__ ) . '/includes/loader.php';
}
tests_add_filter( 'muplugins_loaded', '_install_and_load_edd' );

require '../tmp/wordpress-tests/includes/bootstrap.php';