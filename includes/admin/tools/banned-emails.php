<?php
/**
 * Ban Emails
 *
 * @package     EDD\Tools\BanEmails
 * @copyright   Copyright (c) 2014, Pippin Williamson
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Display the ban emails tab
 *
 * @since       2.0
 * @return      void
 */
function edd_tools_banned_emails_display() {
	do_action( 'edd_tools_banned_emails_before' );
?>
	<div class="postbox">
		<h3><span><?php _e( 'Banned Emails', 'edd' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Emails placed in the box below will not be allowed to make purchases.', 'edd' ); ?></p>
			<form method="post" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-tools&tab=tools' ); ?>">
				<p>
					<textarea name="banned_emails" rows="10" class="large-text"><?php echo implode( "\n", edd_tools_banned_emails_get() ); ?></textarea>
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
	do_action( 'edd_tools_banned_emails_after' );
}
add_action( 'edd_tools_tab_tools', 'edd_tools_banned_emails_display' );


/**
 * Retrieve an array of banned_emails
 *
 * @since       2.0
 * @return      array
 */
function edd_tools_banned_emails_get() {
	$emails = edd_get_option( 'banned_emails', array() );

	return apply_filters( 'edd_get_banned_emails', $emails );
}


/**
 * Save banned emails
 *
 * @since       2.0
 * @return      void
 */
function edd_tools_banned_emails_save() {
	if( !wp_verify_nonce( $_POST['edd_banned_emails_nonce'], 'edd_banned_emails_nonce' ) )
		return;

	global $edd_options;

	// Sanitize the input
	$emails = array_map( 'trim', explode( "\n", $_POST['banned_emails'] ) );
	$emails = array_filter( array_map( 'is_email', $emails ) );

	$edd_options['banned_emails'] = $emails;
	update_option( 'edd_settings', $edd_options );
}
add_action( 'edd_save_banned_emails', 'edd_tools_banned_emails_save' );
