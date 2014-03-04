<?php
/**
 * Import/export settings
 *
 * @package       EDD\Tools\ImportExport
 * @copyright     Copyright (c) 2014, Pippin Williamson
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Process a settings export that generates a .json file of the shop settings
 *
 * @since       1.7
 * @return		void
 */
function edd_process_settings_export() {

	if( empty( $_POST['edd_export_nonce'] ) )
		return;

	if( !wp_verify_nonce( $_POST['edd_export_nonce'], 'edd_export_nonce' ) )
		return;

	if( !current_user_can( 'manage_shop_settings' ) )
		return;

	$settings = array();
	$settings = get_option( 'edd_settings' );

	ignore_user_abort( true );

	if( !edd_is_func_disabled( 'set_time_limit' ) && !ini_get( 'safe_mode' ) )
		set_time_limit( 0 );

	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=edd-settings-export-' . date( 'm-d-Y' ) . '.json' );
	header( "Expires: 0" );

	echo json_encode( $settings );
	exit;
}
add_action( 'edd_export_settings', 'edd_process_settings_export' );


/**
 * Process a settings import from a json file
 *
 * @since        1.7
 * @return       void
 */
function edd_process_settings_import() {

	if( empty( $_POST['edd_import_nonce'] ) )
		return;

	if( !wp_verify_nonce( $_POST['edd_import_nonce'], 'edd_import_nonce' ) )
		return;

	if( !current_user_can( 'manage_shop_settings' ) )
		return;

	if( edd_get_file_extension( $_FILES['import_file']['name'] ) != 'json' ) {
		wp_die( __( 'Please upload a valid .json file', 'edd' ) );
	}

	$import_file = $_FILES['import_file']['tmp_name'];

	if( empty( $import_file ) ) {
		wp_die( __( 'Please upload a file to import', 'edd' ) );
	}

	// Retrieve the settings from the file and convert the json object to an array
	$settings = edd_object_to_array( json_decode( file_get_contents( $import_file ) ) );

	update_option( 'edd_settings', $settings );

	wp_safe_redirect( admin_url( 'edit.php?post_type=download&page=edd-tools&tab=import_export&message=settings-imported' ) ); exit;
}
add_action( 'edd_import_settings', 'edd_process_settings_import' );
