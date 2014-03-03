<?php
/**
 * Tools
 *
 * These are functions used for displaying EDD tools such as the import/export system.
 *
 * @package     EDD
 * @subpackage  Admin/Tools
 * @copyright   Copyright (c) 2014, Pippin Williamson
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

	$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'tools';
?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2 class="nav-tab-wrapper">
			<?php
			foreach( edd_get_tools_tabs() as $tab_id => $tab_name ) {

				$tab_url = add_query_arg( array(
					'tab' => $tab_id
				) );

				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';
				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">' . esc_html( $tab_name ) . '</a>';

			}
			?>
		</h2>
		<div class="metabox-holder">
			<?php
			do_action( 'edd_tools_tab_' . $active_tab );
			?>
		</div><!-- .metabox-holder -->
	</div><!-- .wrap -->
<?php
}


/**
 * Retrieve tools tabs
 *
 * @since       2.0
 * @return      array
 */
function edd_get_tools_tabs() {

	$tabs                  = array();
	$tabs['tools']         = __( 'Tools', 'edd' );
	$tabs['system_info']   = __( 'System Info', 'edd' );
	$tabs['import_export'] = __( 'Import/Export', 'edd' );

	return apply_filters( 'edd_tools_tabs', $tabs );
}


/**
 * Display the tools tab
 *
 * @since       2.0
 * @return      void
 */
function edd_tools_tab_tools() {
	do_action( 'edd_tools_before' );
?>
	<div class="postbox">
		<h3><span><?php _e( 'Banned Emails', 'edd' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Emails placed in the box below will not be allowed to make purchases.', 'edd' ); ?></p>
			<form method="post" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-tools' ); ?>">
				<p>
					<textarea name="banned_emails" rows="10" class="large-text"><?php echo implode( "\n", edd_get_banned_emails() ); ?></textarea>
					<span class="description"><?php _e( 'Enter emails to disallow, one per line', 'edd' ); ?></span>
				</p>
				<p>
					<input type="hidden" name="edd_action" value="save_banned_emails" />
					<?php wp_nonce_field( 'edd_banned_emails_nonce', 'edd_banned_emails_nonce' ); ?>
					<?php submit_button( __( 'Save', 'edd' ), 'secondary', 'submit', false ); ?>
				</p>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->
<?php
	do_action( 'edd_tools_after' );
}
add_action( 'edd_tools_tab_tools', 'edd_tools_tab_tools' );


/**
 * Display the tools import/export tab
 *
 * @since       2.0
 * @return      void
 */
function edd_tools_tab_import_export() {
	do_action( 'edd_import_export_before' );
?>
	<div class="postbox">
		<h3><span><?php _e( 'Export Settings', 'edd' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Export the Easy Digital Downloads settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'edd' ); ?></p>
			<p><?php printf( __( 'To export shop data (purchases, customers, etc), visit the <a href="%s">Reports</a> page.', 'edd' ), admin_url( 'edit.php?post_type=download&page=edd-reports&tab=export' ) ); ?></p>
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
<?php
	do_action( 'edd_import_export_after' );
}
add_action( 'edd_tools_tab_import_export', 'edd_tools_tab_import_export' );


/**
 * Display the tools system info tab
 *
 * @since       2.0
 * @return void
 */
function edd_tools_tab_system_info() {
	if( !class_exists( 'EDD_SysInfo' ) )
		require_once EDD_PLUGIN_DIR . 'includes/admin/tools/sysinfo.php';
?>

	<form action="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-tools&tab=system_info' ) ); ?>" method="post" dir="ltr">
		<textarea readonly="readonly" onclick="this.focus(); this.select()" id="system-info-textarea" name="edd-sysinfo" title="To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac).">
<?php
		$sysinfo = new EDD_SysInfo;
		echo $sysinfo->get();
?>
		</textarea>
		<p class="submit">
			<input type="hidden" name="edd-action" value="download_sysinfo" />
			<?php submit_button( 'Download System Info File', 'primary', 'edd-download-sysinfo', false ); ?>
		</p>
	</form>
<?php
}
add_action( 'edd_tools_tab_system_info', 'edd_tools_tab_system_info' );

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

/**
 * Generates the System Info Download File
 *
 * @since 1.4
 * @return void
 */
function edd_generate_sysinfo_download() {
	nocache_headers();

	header( "Content-type: text/plain" );
	header( 'Content-Disposition: attachment; filename="edd-system-info.txt"' );

	echo wp_strip_all_tags( $_POST['edd-sysinfo'] );
	edd_die();
}
add_action( 'edd_download_sysinfo', 'edd_generate_sysinfo_download' );


/**
 * Retrieve an array of banned emails
 *
 * @since       2.0
 * @return      array
 */
function edd_get_banned_emails() {
	$emails = edd_get_option( 'banned_emails', array() );

	return apply_filters( 'edd_get_banned_emails', $emails );
}


/**
 * Save banned emails
 *
 * @since       2.0
 * @return      void
 */
function edd_save_banned_emails() {
	if ( ! wp_verify_nonce( $_POST['edd_banned_emails_nonce'], 'edd_banned_emails_nonce' ) )
		return;

	global $edd_options;

	// Sanitize the input
	$emails = array_map( 'trim', explode( "\n", $_POST['banned_emails'] ) );
	$emails = array_filter( array_map( 'is_email', $emails ) );

	$edd_options['banned_emails'] = $emails;
	update_option( 'edd_settings', $edd_options );
}
add_action( 'edd_save_banned_emails', 'edd_save_banned_emails' );


/**
 * Check the purchase to ensure a banned email is not allowed through
 *
 * @since       2.0
 * @return      void
 */
function edd_check_purchase_email( $valid_data, $posted ) {
	$is_banned = false;
	$banned    = edd_get_banned_emails();

    if( empty( $banned ) )
		return;

	if( is_user_logged_in() ) {
		// The user is logged in, check that their account email is not banned
		$user_data = get_userdata( get_current_user_id() );
		if( in_array( $user_data->user_email, $banned ) ) {
			$is_banned = true;
		}

		if( in_array( $posted['edd_email'], $banned ) ) {
			$is_banned = true;
		}
	} elseif ( isset( $posted['edd-purchase-var'] ) && $posted['edd-purchase-var'] == 'needs-to-login' ) {
		// The user is logging in, check that their user account email is not banned
		$user_data = get_user_by( 'login', $posted['edd_user_login'] );
		if( $user_data && in_array( $user_data->user_email, $banned ) ) {
			$is_banned = true;
		}
	} else {
		// Guest purchase, check that the email is not banned
		if( in_array( $posted['edd_email'], $banned ) ) {
			$is_banned = true;
		}
	}

	if( $is_banned ) {
		// Set an error and give the customer a general error (don't alert them that they were banned)
		edd_set_error( 'email_banned', __( 'An internal error has occured, please try again or contact support.', 'edd' ) );
	}
}
add_action( 'edd_checkout_error_checks', 'edd_check_purchase_email', 10, 2 );
