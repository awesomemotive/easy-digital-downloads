<?php
/**
 * Tools
 *
 * These are functions used for displaying EDD tools such as the import/export system.
 *
 * @package     EDD
 * @subpackage  Admin/Tools
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Tools
 *
 * Shows the tools panel which contains EDD-specific tools including the
 * built-in import/export system.
 *
 * @since       1.8
 * @author      Daniel J Griffiths
 * @return      void
 */
function edd_tools_page() {
?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2><?php _e( 'Tools', 'edd' ) ?></h2>
		<div class="metabox-holder">
			<?php do_action( 'edd_tools_before' ); ?>
			<div class="postbox">
				<h3><span><?php _e( 'Export Settings', 'edd' ); ?></span></h3>
				<div class="inside">
					<p><?php _e( 'Export the Easy Digital Downloads settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'edd' ); ?></p>
					<p><?php printf( __( 'To export shop data (purchases, customers, etc), visit the <a href="%s">Reports</a> page.', 'edd' ), admin_url( 'edit.php?post_type=download&page=edd-reports&tab=export' ) ); ?>
					<form method="post" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-tools' ); ?>">
						<p><input type="hidden" name="edd_action" value="export_settings" /></p>
						<p>
							<?php wp_nonce_field( 'edd_export_nonce', 'edd_export_nonce' ); ?>
							<?php submit_button( __( 'Export', 'edd' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->

			<div class="postbox">
				<h3><span><?php _e( 'Import Settings', 'edd' ); ?></span></h3>
				<div class="inside">
					<p><?php _e( 'Import the Easy Digital Downloads settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.', 'edd' ); ?></p>
					<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-tools' ); ?>">
						<p>
							<input type="file" name="import_file"/>
						</p>
						<p>
							<input type="hidden" name="edd_action" value="import_settings" />
							<?php wp_nonce_field( 'edd_import_nonce', 'edd_import_nonce' ); ?>
							<?php submit_button( __( 'Import', 'edd' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->
			<?php do_action( 'edd_tools_after' ); ?>
		</div><!-- .metabox-holder -->
	</div><!-- .wrap -->
<?php
}


/**
 * Process a settings export that generates a .json file of the shop settings
 *
 * @since       1.7
 * @return      void
 */
function edd_process_settings_export() {

	if( empty( $_POST['edd_export_nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_POST['edd_export_nonce'], 'edd_export_nonce' ) )
		return;

	if( ! current_user_can( 'manage_shop_settings' ) )
		return;

	$settings = array();
	$settings = get_option( 'edd_settings' );

	ignore_user_abort( true );

	if ( ! edd_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) )
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
 * @since 1.7
 * @return void
 */
function edd_process_settings_import() {

	if( empty( $_POST['edd_import_nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_POST['edd_import_nonce'], 'edd_import_nonce' ) )
		return;

	if( ! current_user_can( 'manage_shop_settings' ) )
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

	wp_safe_redirect( admin_url( 'edit.php?post_type=download&page=edd-tools&edd-message=settings-imported' ) ); exit;

}
add_action( 'edd_import_settings', 'edd_process_settings_import' );
