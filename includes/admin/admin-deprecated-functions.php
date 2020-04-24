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
