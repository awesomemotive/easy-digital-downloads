<?php
/**
 * Payment Request Button: Template
 *
 * @package EDD_Stripe
 * @since   2.8.0
 */

/**
 * Outputs a Payment Request Button via `edd_get_purchase_link()`
 * (which is implemented via [purchase_link])
 *
 * @since 2.8.0
 *
 * @param int   $download_id Current Download ID.
 * @param array $args Arguments for displaying the purchase link.
 */
function edds_prb_purchase_link( $download_id, $args ) {
	// Don't output if context is not enabled.
	$context = is_singular() && 0 === did_action( 'edd_downloads_list_before' )
		? 'single'
		: 'archive';

	if ( false === edds_prb_is_enabled( $context ) ) {
		return;
	}

	// Don't output if the item is free. Stripe won't process < $0.50
	if ( true === edd_is_free_download( $download_id ) ) {
		return;
	}

	// Don't output if the item is already in the cart.
	if ( true === edd_item_in_cart( $download_id ) ) {
		return;
	}

	// Don't output if `edd_get_purchase_link` is filtered to disable.
	if (
		isset( $args['payment-request'] ) &&
		true !== edds_truthy_to_bool( $args['payment-request'] )
	) {
		return;
	}

	// Don't output if Recurring is enabled, and a free trial is present.
	// @link https://github.com/easydigitaldownloads/edd-stripe/issues/594
	if ( function_exists( 'edd_recurring' ) ) {
		// Don't output if non-variable price has a free trial.
		if ( edd_recurring()->has_free_trial( $download_id ) ) {
			return;
		}

		// ...or if a variable price options has a free trial.
		$prices = edd_get_variable_prices( $download_id );

		if ( ! empty( $prices ) ) {
			foreach ( $prices as $price ) {
				if ( edd_recurring()->has_free_trial( $download_id, $price['index'] ) ) {
					return;
				}
			}
		}
	}

	// Don't output if it has been filtered off for any reason.
	$enabled = true;

	/**
	 * Filters the output of Payment Request Button in purchase links.
	 *
	 * @since 2.8.0
	 *
	 * @param bool  $enabled If the Payment Request Button is enabled.
	 * @param int   $download_id Current Download ID.
	 * @param array $args Purchase link arguments.
	 */
	$enabled = apply_filters( 'edds_prb_purchase_link_enabled', $enabled, $download_id, $args );

	if ( true !== $enabled ) {
		return;
	}

	static $instance_id = 0;

	echo edds_get_prb_markup(
		edds_prb_get_download_data( $download_id ),
		array(
			'id'      => sprintf(
				'edds-prb-download-%d-%d',
				$download_id,
				$instance_id
			),
			'classes' => array(
				'edds-prb--download',
			),
		)
	); // WPCS: XSS okay.

	// Shim the Checkout processing nonce.
	wp_nonce_field( 'edd-process-checkout', 'edd-process-checkout-nonce', false );
	echo edds_get_tokenizer_input( $download_id );

	$instance_id++;
}
add_action( 'edd_purchase_link_top', 'edds_prb_purchase_link', 20, 2 );

/**
 * Outputs a Payment Request Button on the Checkout.
 *
 * @since 2.8.0
 */
function edds_prb_checkout_button_purchase( $button ) {
	// Do nothing if Payment Requests are not enabled.
	if ( false === edds_prb_is_enabled( 'checkout' ) ) {
		return $button;
	}

	$tokenizer = edds_get_tokenizer_input();
	$errors    = '<div id="edds-prb-error-wrap"></div>';

	$button = edds_get_prb_markup(
		edds_prb_get_cart_data(),
		array(
			'id'      => 'edds-prb-checkout',
			'classes' => array(
				'edds-prb--checkout',
			),
		)
	);

	return $tokenizer . $errors . $button;
}

/**
 * Retrieves HTML used to mount a Payment Request Button.
 *
 * @since 2.8.0
 * @see edds_prb_get_download_data()
 * @link https://stripe.com/docs/js/appendix/payment_item_object
 *
 * @param PaymentItem[] $data {
 *   PaymentItems.
 *
 *   @type int    $amount The amount in the currency's subunit.
 *   @type string $label A name the browser shows the customer in the payment interface.
 * }
 * @param array $args {
 *   Mount arguments.
 *
 *   @type string $id HTML ID attribute.
 *   @type array  $classes HTML classes.
 * }
 * @return string
 */
function edds_get_prb_markup( $data, $args = array() ) {
	$defaults = array(
		'id'      => '',
		'classes' => array(),
	);

	$args = wp_parse_args( $args, $defaults );

	// ID/Class
	$id    = $args['id'];
	$class = implode(
		' ',
		array_merge(
			$args['classes'],
			array(
				'edds-prb',
			)
		)
	);

	// Data
	$_data = array();

	foreach ( $data as $key => $value ) {
		$_data[] = sprintf(
			'data-%s="%s"',
			esc_attr( $key ),
			esc_attr( is_array( $value ) ? wp_json_encode( $value ) : $value )
		);
	}

	$_data = implode( ' ', $_data );

	edd_stripe_js( true );
	edd_stripe_css( true );

	return sprintf(
		'<div id="%1$s" class="%2$s" %3$s>
			<div class="edds-prb__button"></div>
			<div class="edds-prb__or">%4$s</div>
		</div>',
		esc_attr( $id ),
		esc_attr( $class ),
		$_data,
		esc_html_x( 'or', 'payment request button divider', 'easy-digital-downloads' )
	);
}
