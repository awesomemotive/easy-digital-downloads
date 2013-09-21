<?php

ini_set('display_errors','on');
error_reporting(E_ALL);
define( 'EDD_PLUGIN_DIR', dirname( dirname( __FILE__ ) ) . '/'  );
ob_start();
require_once dirname( __FILE__ ) . '/../tmp/wordpress-tests/includes/functions.php';
ob_get_clean();
function _install_and_load_edd() {
	ob_start();
	require dirname( __FILE__ ) . '/includes/loader.php';
	ob_get_clean();
}
tests_add_filter( 'muplugins_loaded', '_install_and_load_edd' );
ob_start();
require dirname( __FILE__ ) . '/../tmp/wordpress-tests/includes/bootstrap.php';
ob_get_clean();
require dirname( __FILE__ ) . '/framework/testcase.php';
