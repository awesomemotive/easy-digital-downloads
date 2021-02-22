<?php

/**
 * Admin Deprecated Functions
 *
 * All admin functions that have been deprecated.
 *
 * @package     EDD
 * @subpackage  Deprecated
 * @copyright   Copyright (c) 2020, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

/**
 * Display the ban emails tab
 *
 * @since 2.0
 * @deprecated 3.0 replaced by Order Blocking in settings.
 */
function edd_tools_banned_emails_display() {
	_edd_deprecated_function( __FUNCTION__, '3.0' );
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	do_action( 'edd_tools_banned_emails_before' );
	?>
	<div class="postbox">
		<h3><span><?php _e( 'Banned Emails', 'easy-digital-downloads' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Emails placed in the box below will not be allowed to make purchases.', 'easy-digital-downloads' ); ?></p>
			<form method="post"
					action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-tools&tab=general' ); ?>">
				<p>
					<textarea name="banned_emails" rows="10"
								class="large-text"><?php echo implode( "\n", edd_get_banned_emails() ); ?></textarea>
					<span class="description"><?php _e( 'Enter emails and/or domains (starting with "@") and/or TLDs (starting with ".") to disallow, one per line.', 'easy-digital-downloads' ); ?></span>
				</p>
				<p>
					<input type="hidden" name="edd_action" value="save_banned_emails"/>
					<?php wp_nonce_field( 'edd_banned_emails_nonce', 'edd_banned_emails_nonce' ); ?>
					<?php submit_button( __( 'Save', 'easy-digital-downloads' ), 'secondary', 'submit', false ); ?>
				</p>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->
	<?php
	do_action( 'edd_tools_banned_emails_after' );
	do_action( 'edd_tools_after' );
}

/**
 * Trigger a Purchase Deletion
 *
 * @since 1.3.4
 * @deprecated 3.0 replaced by edd_trigger_destroy_order.
 * @param array $data Arguments passed.
 * @return void
 */
function edd_trigger_purchase_delete( $data ) {
	if ( wp_verify_nonce( $data['_wpnonce'], 'edd_payment_nonce' ) ) {

		$payment_id = absint( $data['purchase_id'] );

		if ( ! current_user_can( 'delete_shop_payments', $payment_id ) ) {
			wp_die( __( 'You do not have permission to edit this payment record', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		edd_delete_purchase( $payment_id );

		edd_redirect( admin_url( '/edit.php?post_type=download&page=edd-payment-history&edd-message=payment_deleted' ) );
	}
}
add_action( 'edd_delete_payment', 'edd_trigger_purchase_delete' );
