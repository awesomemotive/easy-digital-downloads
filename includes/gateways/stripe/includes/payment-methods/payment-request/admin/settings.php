<?php
/**
 * Payment Request Button: Settings
 *
 * @package EDD_Stripe
 * @since   2.8.0
 */

/**
 * Adds settings to the Stripe subtab.
 *
 * @since 2.8.0
 *
 * @param array $settings Gateway settings.
 * @return array Filtered gateway settings.
 */
function edds_prb_add_settings( $settings ) {
	/**
	 * In Version 2.9.0, PRBs are no longer necessary as they are part of the
	 * Payment Element.
	 */
	if ( false === _edds_legacy_elements_enabled() ) {
		return $settings;
	}

	// Prevent adding the extra settings if the requirements are not met.
	// The `edd_settings_gateways` filter runs regardless of the short circuit
	// inside of `edds_add_settings()`
	if ( false === edds_is_pro() ) {
		return $settings;
	}

	if ( true === edd_use_taxes() ) {
		$prb_settings = array(
			array(
				'id'   => 'stripe_prb_taxes',
				'name' => __( 'Apple Pay/Google Pay', 'easy-digital-downloads' ),
				'type' => 'edds_stripe_prb_taxes',
			),
		);
	} else {
		$elements_mode = edds_get_elements_mode();
		$prb_settings  = array(
			array(
				'id'      => 'stripe_prb',
				'name'    => __( 'Apple Pay/Google Pay', 'easy-digital-downloads' ),
				'desc'    => wp_kses(
					(
						sprintf(
							/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. %3$s Closing anchor tag, do not translate. */
							__( '"Express Checkout" via Apple Pay, Google Pay, or Microsoft Pay digital wallets. By using Apple Pay, you agree to %1$sStripe%3$s and %2$sApple\'s%3$s terms of service.', 'easy-digital-downloads' ),
							'<a href="https://stripe.com/apple-pay/legal" target="_blank" rel="noopener noreferrer">',
							'<a href="https://developer.apple.com/apple-pay/acceptable-use-guidelines-for-websites/" target="_blank" rel="noopener noreferrer">',
							'</a>'
						) . (
						edd_is_test_mode()
							? '<br /><strong>' . __( 'Apple Pay is not available in Test Mode.', 'easy-digital-downloads' ) . '</strong> ' . sprintf(
								/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
								__( 'See our %1$sdocumentation%2$s for more information.', 'easy-digital-downloads' ),
								'<a href="' . esc_url( edds_documentation_route( 'stripe-express-checkout-apple-pay-google-pay' ) ) . '" target="_blank" rel="noopener noreferrer">',
								'</a>'
							)
							: ''
						)
					),
					array(
						'br'     => true,
						'strong' => true,
						'a'      => array(
							'href'   => true,
							'target' => true,
							'rel'    => true,
						),
					)
				),
				'type'    => 'multicheck',
				'options' => array(
					'single'   => sprintf(
						/* translators: %s Download noun */
						__( 'Single %s', 'easy-digital-downloads' ),
						edd_get_label_singular()
					),
					'archive'  => sprintf(
						/* translators: 1. Download noun; 2. shortcode tag wrapped in <code> span */
						__( '%1$s Archive (includes %2$s shortcode)', 'easy-digital-downloads' ),
						edd_get_label_singular(),
						'<code>[downloads]</code>'
					),
					'checkout' => __( 'Checkout', 'easy-digital-downloads' ),
				),
				'class'   => 'payment-elements' === $elements_mode ? 'edd-hidden card-elements-feature' : 'card-elements-feature',
			),
			array(
				'id'    => 'stripe_prb_elements_note',
				'name'  => __( 'Apple Pay/Google Pay', 'easy-digital-downloads' ),
				'desc'  => __( 'Apple Pay and Google Pay support is now provided via the Payment Elements integration.', 'easy-digital-downloads' ),
				'type'  => 'descriptive_text',
				'class' => 'payment-elements' === $elements_mode ? 'payment-elements-feature' : 'edd-hidden payment-elements-feature',
			),

		);
	}

	$position = array_search(
		'stripe_use_existing_cards',
		array_values( wp_list_pluck( $settings['edd-stripe'], 'id' ) ),
		true
	);

	$settings['edd-stripe'] = array_merge(
		array_slice( $settings['edd-stripe'], 0, $position + 1 ),
		$prb_settings,
		array_slice( $settings['edd-stripe'], $position + 1 )
	);

	return $settings;
}
add_filter( 'edd_settings_gateways', 'edds_prb_add_settings', 20 );

/**
 * Removes multicheck options and outputs a message about "Express Checkout" incompatibility with taxes.
 *
 * @since 2.8.7
 */
function edd_edds_stripe_prb_taxes_callback() {
	echo esc_html__(
		'This feature is not available when taxes are enabled.',
		'easy-digital-downloads'
	) . ' ';

	echo wp_kses(
		sprintf(
			/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
			__(
				'See the %1$sExpress Checkout documentation%2$s for more information.',
				'easy-digital-downloads'
			),
			'<a href="' . esc_url( edds_documentation_route( 'stripe-express-checkout-apple-pay-google-pay' ) ) . '#edds-prb-faqs" target="_blank" rel="noopener noreferrer">',
			'</a>'
		),
		array(
			'a' => array(
				'href'   => true,
				'target' => true,
				'rel'    => true,
			),
		)
	);
}

/**
 * Force "Payment Request Buttons" to be disabled if taxes are enabled.
 *
 * @since 2.8.0
 *
 * @param mixed  $value Setting value.
 * @param string $key Setting key.
 * @return string Setting value.
 */
function edds_prb_sanitize_setting( $value, $key ) {
	if ( 'stripe_prb' === $key && edd_use_taxes() ) {
		$value = array();
	}

	return $value;
}
add_filter( 'edd_settings_sanitize_multicheck', 'edds_prb_sanitize_setting', 10, 2 );
