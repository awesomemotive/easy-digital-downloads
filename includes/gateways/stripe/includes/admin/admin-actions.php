<?php

/**
 * Admin Messages
 *
 * @since 1.6
 * @return void
 */
function edds_admin_messages() {

	if( isset( $_GET['edd_gateway_connect_error'], $_GET['edd-message'] ) ) {
		/* translators: %1$s Stripe Connect error message. %2$s Retry URL. */
		echo '<div class="notice notice-error"><p>' . sprintf( __( 'There was an error connecting your Stripe account. Message: %1$s. Please <a href="%2$s">try again</a>.', 'easy-digital-downloads' ), esc_html( urldecode( $_GET['edd-message'] ) ), esc_url( admin_url( 'edit.php?post_type=download&page=edd-settings&tab=gateways&section=edd-stripe' ) ) ) . '</p></div>';
		add_filter( 'wp_parse_str', function( $ar ) {
			if( isset( $ar['edd_gateway_connect_error'] ) ) {
				unset( $ar['edd_gateway_connect_error'] );
			}

			if( isset( $ar['edd-message'] ) ) {
				unset( $ar['edd-message'] );
			}
			return $ar;
		});
	}
}
add_action( 'admin_notices', 'edds_admin_messages' );

/**
 * Add payment meta item to payments that used an existing card
 *
 * @since 2.6
 * @param $payment_id
 * @return void
 */
function edds_show_existing_card_meta( $payment_id ) {
	$payment = new EDD_Payment( $payment_id );
	$existing_card = $payment->get_meta( '_edds_used_existing_card' );
	if ( ! empty( $existing_card ) ) {
		?>
		<div class="edd-order-stripe-existing-card edd-admin-box-inside">
			<p>
				<span class="label"><?php _e( 'Used Existing Card:', 'easy-digital-downloads' ); ?></span>&nbsp;
				<span><?php _e( 'Yes', 'easy-digital-downloads' ); ?></span>
			</p>
		</div>
		<?php
	}
}
add_action( 'edd_view_order_details_payment_meta_after', 'edds_show_existing_card_meta', 10, 1 );

/**
 * Handles redirects to the Stripe settings page under certain conditions.
 *
 * @since 2.6.14
 */
function edds_stripe_connect_test_mode_toggle_redirect() {

	// Check for our marker
	if( ! isset( $_POST['edd-test-mode-toggled'] ) ) {
		return;
	}

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	if ( false === edds_is_gateway_active() ) {
		return;
	}

	/**
	 * Filter the redirect that happens when options are saved and
	 * add query args to redirect to the Stripe settings page
	 * and to show a notice about connecting with Stripe.
	 */
	add_filter( 'wp_redirect', function( $location ) {
		if( false !== strpos( $location, 'page=edd-settings' ) && false !== strpos( $location, 'settings-updated=true' ) ) {
			$location = add_query_arg(
				array(
					'edd-message' => 'connect-to-stripe',
				),
				$location
			);
		}
		return $location;
	} );

}
add_action( 'admin_init', 'edds_stripe_connect_test_mode_toggle_redirect' );

/**
 * Adds a "Refund Charge in Stripe" checkbox to the refund UI.
 *
 * @param \EDD\Orders\Order $order
 *
 * @since 2.8.7
 */
function edds_show_refund_checkbox( \EDD\Orders\Order $order ) {
	if ( 'stripe' !== $order->gateway ) {
		return;
	}
	?>
	<div class="edd-form-group edd-stripe-refund-transaction">
		<div class="edd-form-group__control">
			<input
				type="checkbox"
				id="edd-stripe-refund"
				name="edd-stripe-refund"
				class="edd-form-group__input"
				value="1"
				<?php echo esc_attr( 'on_hold' === $order->status ? 'disabled' : '' ); ?>
			>
			<label for="edd-stripe-refund" class="edd-form-group__label">
				<?php esc_html_e( 'Refund Charge in Stripe', 'easy-digital-downloads' ); ?>
			</label>
		</div>
		<?php if ( 'on_hold' === $order->status ) : ?>
			<p class="edd-form-group__help description">
				<?php esc_html_e( 'This order is currently on hold. You can create the refund transaction in EDD; Stripe may have already issued a refund.', 'easy-digital-downloads' ); ?>
			</p>
		<?php endif; ?>
	</div>
	<?php
}
add_action( 'edd_after_submit_refund_table', 'edds_show_refund_checkbox' );

/**
 * Allows processing flags for the EDD Stripe settings.
 *
 * As we transition settings like the Card Elements, we need a way to be able to toggle
 * these things back on for some people. Enabling debug mode, setting flags, and then disabling
 * debug mode allows us to handle this.
 *
 * @since 2.9.4
 */
function edds_process_settings_flags() {
	// If we're not on the settings page, bail.
	if ( ! edd_is_admin_page( 'settings', 'gateways' ) ) {
		return;
	}

	// If it isn't the Stripe section, bail.
	if ( ! isset( $_GET['section'] ) || 'edd-stripe' !== $_GET['section'] ) {
		return;
	}

	// Gather the flag we're trying to set.
	$flag = isset( $_GET['flag'] ) ? $_GET['flag'] : false;

	if ( false === $flag ) {
		return;
	}

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	$nonce = isset( $_GET['_wpnonce'] ) ? $_GET['_wpnonce'] : false;
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $flag ) ) {
		return;
	}

	switch( $flag ) {
		case 'disable-card-elements':
			delete_option( '_edds_legacy_elements_enabled' );
			break;

		case 'enable-card-elements':
			add_option( '_edds_legacy_elements_enabled', 1, false );
			break;
	}

	// Redirect to the settings page.
	wp_safe_redirect(
		edd_get_admin_url(
			array(
				'page'        => 'edd-settings',
				'tab'         => 'gateways',
				'section'     => 'edd-stripe',
			)
		)
	);

	exit;
}
add_action( 'admin_init', 'edds_process_settings_flags', 1 );
