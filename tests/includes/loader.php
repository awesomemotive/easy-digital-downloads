<?php

// Install EDD
$config_file_path = dirname( __FILE__ ) . '/../../tmp/wordpress-tests/wp-tests-config.php';
$multisite = (int) ( defined( 'WP_TESTS_MULTISITE') && WP_TESTS_MULTISITE );
system( WP_PHP_BINARY . ' ' . escapeshellarg( dirname( __FILE__ ) . '/install.php' ) . ' ' . escapeshellarg( $config_file_path ) . ' ' . $multisite );

// Bootstrap EDD
require dirname( __FILE__ ) . '/../../easy-digital-downloads.php';

// Load Die Handler
$die_handler = dirname( __FILE__ ) . '/die-handler.php';

require_once $die_handler;

new EDD_Die_Handler;