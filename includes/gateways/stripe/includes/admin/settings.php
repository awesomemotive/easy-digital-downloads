<?php

/**
 * Register our settings section
 *
 * @param array $sections The Registered EDD Sections array.
 *
 * @return array
 */
function edds_settings_section( $sections ) {
	$sections['edd-stripe'] = __( 'Stripe', 'easy-digital-downloads' );

	return $sections;
}
add_filter( 'edd_settings_sections_gateways', 'edds_settings_section' );

/**
 * Register the gateway settings.
 *
 * @access      public
 * @since       1.0
 *
 * @param array $settings The currently registered settings.
 *
 * @return      array
 */
function edds_add_settings( $settings ) {
	$stripe_settings        = new EDD\Gateways\Stripe\Admin\Settings();
	$settings['edd-stripe'] = $stripe_settings->get();
	$settings               = $stripe_settings->insert_toggle_notice( $settings );

	return $settings;
}
add_filter( 'edd_settings_gateways', 'edds_add_settings' );
add_action( 'edd_stripe_payment_methods', array( 'EDD\\Gateways\\Stripe\\Admin\\Settings', 'render_payment_methods' ) );

/**
 * Filter the output of the statement descriptor option to add a max length to the text string
 *
 * @since 2.6
 * @param string $html  The full html for the setting output.
 * @param array  $args  The original arguments passed in to output the html.
 *
 * @return string
 */
function edd_stripe_max_length_statement_descriptor( $html, $args ) {
	if ( 'stripe_statement_descriptor' !== $args['id'] ) {
		return $html;
	}

	$html = str_replace( '<input type="text"', '<input type="text" maxlength="22"', $html );

	return $html;
}
add_filter( 'edd_after_setting_output', 'edd_stripe_max_length_statement_descriptor', 10, 2 );

/**
 * Callback for the stripe_connect_notice field type.
 *
 * @since 2.6.14
 *
 * @param array $args The setting field arguments.
 */
function edd_stripe_connect_notice_callback( $args ) {

	$value = isset( $args['desc'] ) ? $args['desc'] : '';

	$class = edd_sanitize_html_class( $args['field_class'] );

	?>
	<div class="<?php echo esc_attr( $class ); ?>" id="edd_settings[<?php echo edd_sanitize_key( $args['id'] ); ?>]">
		<?php echo wp_kses_post( $value ); ?>
	</div>
	<?php
}

/**
 * Callback for the stripe_checkout_notice field type.
 *
 * @since 2.7.0
 *
 * @param array $args The setting field arguments.
 */
function edd_stripe_checkout_notice_callback( $args ) {
	$value = isset( $args['desc'] ) ? $args['desc'] : '';

	$html = '<div class="notice notice-warning inline' . edd_sanitize_html_class( $args['field_class'] ) . '" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']">' . wpautop( $value ) . '</div>';

	echo $html;
}

/**
 * Outputs information when Stripe has been activated but application requirements are not met.
 *
 * @since 2.8.1
 */
function edd_stripe_requirements_not_met_callback() {
	$required_version = 7.1;
	$current_version  = phpversion();

	echo '<div class="notice inline notice-warning">';
	echo '<p>';
	echo wp_kses(
		sprintf(
			/* translators: %1$s PHP version requirement. %2$s Current PHP version. %3$s Opening strong tag, do not translate. %4$s Closing strong tag, do not translate. */
			__(
				'Processing credit cards with Stripe requires PHP version %1$s or higher. It looks like you\'re using version %2$s, which means you will need to %3$supgrade your version of PHP before acceping credit card payments%4$s.',
				'easy-digital-downloads'
			),
			'<code>' . $required_version . '</code>',
			'<code>' . $current_version . '</code>',
			'<strong>',
			'</strong>'
		),
		array(
			'code'   => true,
			'strong' => true,
		)
	);
	echo '</p>';
	echo '<p>';

	echo '<strong>';
	esc_html_e( 'Need help upgrading? Ask your web host!', 'easy-digital-downloads' );
	echo '</strong><br />';

	echo wp_kses(
		sprintf(
			/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
			__(
				'Many web hosts can give you instructions on how/where to upgrade your version of PHP through their control panel, or may even be able to do it for you. If you need to change hosts, please see %1$sour hosting recommendations%2$s.',
				'easy-digital-downloads'
			),
			'<a href="https://easydigitaldownloads.com/recommended-wordpress-hosting/" target="_blank" rel="noopener noreferrer">',
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
	echo '</p>';
	echo '</div>';
}

/**
 * Adds a notice to the "Payment Gateways" selector if Stripe has been activated but does
 * not meet application requirements.
 *
 * @since 2.8.1
 * @deprecated 3.2.0
 *
 * @param string $html Setting HTML.
 * @param array  $args Setting arguments.
 * @return string
 */
function edds_payment_gateways_notice( $html, $args ) {
	if ( 'gateways' !== $args['id'] ) {
		return $html;
	}

	if (
		true === edds_is_pro() ||
		true === edds_has_met_requirements( 'php' )
	) {
		return $html;
	}

	$required_version = 7.1;
	$current_version  = phpversion();

	$html .= '<div id="edds-payment-gateways-stripe-unmet-requirements" class="notice inline notice-info"><p>' .
		wp_kses(
			sprintf(
				/* translators: %1$s PHP version requirement. %2$s Current PHP version. %3$s Opening strong tag, do not translate. %4$s Closing strong tag, do not translate. */
				__(
					'Processing credit cards with Stripe requires PHP version %1$s or higher. It looks like you\'re using version %2$s, which means you will need to %3$supgrade your version of PHP before acceping credit card payments%4$s.',
					'easy-digital-downloads'
				),
				'<code>' . $required_version . '</code>',
				'<code>' . $current_version . '</code>',
				'<strong>',
				'</strong>'
			),
			array(
				'code'   => true,
				'strong' => true,
			)
		) .
	'</p><p><strong>' .
		esc_html__( 'Need help upgrading? Ask your web host!', 'easy-digital-downloads' ) .
	'</strong><br />' .
	wp_kses(
		sprintf(
			/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
			__(
				'Many web hosts can give you instructions on how/where to upgrade your version of PHP through their control panel, or may even be able to do it for you. If you need to change hosts, please see %1$sour hosting recommendations%2$s.',
				'easy-digital-downloads'
			),
			'<a href="https://easydigitaldownloads.com/recommended-wordpress-hosting/" target="_blank" rel="noopener noreferrer">',
			'</a>'
		),
		array(
			'a' => array(
				'href'   => true,
				'target' => true,
				'rel'    => true,
			),
		)
	) . '</p></div>';

	return $html;
}

/**
 * Potentially updates the Stripe settings if there is regional support enabled.
 *
 * @since 3.2.0
 *
 * @param array $settings The settings array.
 * @return array
 */
function edds_maybe_add_billing_support_message( $settings ) {
	if ( ! edd_stripe()->has_regional_support() ) {
		return $settings;
	}

	return edd_stripe()->regional_support->add_billing_address_message( $settings );
}
add_filter( 'edd_settings_gateways', 'edds_maybe_add_billing_support_message', 30 );

/**
 * Filter the flyout documentation link.
 *
 * Links users to the Stripe setup documentation when on the Stripe settings pgae.
 *
 * @since 3.2.8
 *
 * @param string $link The route to the documentation.
 *
 * @return string
 */
function edds_documentation_flyout_link( $link ) {
	if ( edd_is_admin_page() && isset( $_GET['section'] ) && 'edd-stripe' === $_GET['section'] ) {
		$link = 'https://easydigitaldownloads.com/docs/stripe/';
	}

	return $link;
}
add_filter( 'edd_flyout_docs_link', 'edds_documentation_flyout_link' );

/**
 * Force full billing address display when taxes are enabled.
 *
 * @since 2.5
 * @deprecated 3.3.8
 *
 * @param string $value The value currently set for the Stripe billing fields setting.
 * @param string $key   The Stripe setting key to detect, stripe_billing_fields.
 *
 * @return      string
 */
function edd_stripe_sanitize_stripe_billing_fields_save( $value, $key ) {
	return $value;
}
