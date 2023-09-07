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
 * Register the gateway settings
 *
 * @access      public
 * @since       1.0
 *
 * @param array $settings The currently registered settings.
 *
 * @return      array
 */
function edds_add_settings( $settings ) {
	$stripe_settings = array(
		'stripe_connect_button'         => array(
			'id'    => 'stripe_connect_button',
			'name'  => __( 'Connection Status', 'easy-digital-downloads' ),
			'desc'  => edds_stripe_connect_setting_field(),
			'type'  => 'descriptive_text',
			'class' => 'edd-stripe-connect-row',
		),
		'test_publishable_key'          => array(
			'id'    => 'test_publishable_key',
			'name'  => __( 'Test Publishable Key', 'easy-digital-downloads' ),
			'desc'  => __( 'Enter your test publishable key, found in your Stripe Account Settings', 'easy-digital-downloads' ),
			'type'  => 'text',
			'size'  => 'regular',
			'class' => 'edd-hidden edds-api-key-row',
		),
		'test_secret_key'             => array(
			'id'    => 'test_secret_key',
			'name'  => __( 'Test Secret Key', 'easy-digital-downloads' ),
			'desc'  => __( 'Enter your test secret key, found in your Stripe Account Settings', 'easy-digital-downloads' ),
			'type'  => 'text',
			'size'  => 'regular',
			'class' => 'edd-hidden edds-api-key-row',
		),
		'live_publishable_key'          => array(
			'id'    => 'live_publishable_key',
			'name'  => __( 'Live Publishable Key', 'easy-digital-downloads' ),
			'desc'  => __( 'Enter your live publishable key, found in your Stripe Account Settings', 'easy-digital-downloads' ),
			'type'  => 'text',
			'size'  => 'regular',
			'class' => 'edd-hidden edds-api-key-row',
		),
		'live_secret_key'               => array(
			'id'    => 'live_secret_key',
			'name'  => __( 'Live Secret Key', 'easy-digital-downloads' ),
			'desc'  => __( 'Enter your live secret key, found in your Stripe Account Settings', 'easy-digital-downloads' ),
			'type'  => 'text',
			'size'  => 'regular',
			'class' => 'edd-hidden edds-api-key-row',
		),
		'stripe_webhook_description'    => array(
			'id'   => 'stripe_webhook_description',
			'type' => 'descriptive_text',
			'name' => __( 'Webhooks', 'easy-digital-downloads' ),
			'desc' =>
			'<p>' . sprintf(
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				__( 'In order for Stripe to function completely, you must configure your Stripe webhooks. Visit your %1$saccount dashboard%2$s to configure them. Please add a webhook endpoint for the URL below.', 'easy-digital-downloads' ),
				'<a href="https://dashboard.stripe.com/account/webhooks" target="_blank" rel="noopener noreferrer">',
				'</a>'
			) .
			'</p>' .
			'<p><strong>' .
			sprintf(
				/* translators: %s Webhook URL. Do not translate. */
				__( 'Webhook URL: %s', 'easy-digital-downloads' ),
				home_url( 'index.php?edd-listener=stripe' )
			) .
			'</strong></p>' .
			'<p>' .
			sprintf(
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				__( 'See our %1$sdocumentation%2$s for more information.', 'easy-digital-downloads' ),
				'<a href="' . esc_url( edds_documentation_route( 'stripe' ) ) . '#webhook-configuration' . '" target="_blank" rel="noopener noreferrer">',
				'</a>'
			) .
			'</p>',
		),
		'stripe_billing_fields'         => array(
			'id'      => 'stripe_billing_fields',
			'name'    => __( 'Billing Address Display', 'easy-digital-downloads' ),
			'desc'    => __( 'Select how you would like to display the billing address fields on the checkout form. <p><strong>Notes</strong>:</p><p>If taxes are enabled, this option cannot be changed from "Full address".</p><p>If set to "No address fields", you <strong>must</strong> disable "zip code verification" in your Stripe account.</p>', 'easy-digital-downloads' ),
			'type'    => 'select',
			'std'     => 'full',
			'options' => array(
				'full'        => __( 'Full address', 'easy-digital-downloads' ),
				'zip_country' => __( 'Zip / Postal Code and Country only', 'easy-digital-downloads' ),
				'none'        => __( 'No address fields', 'easy-digital-downloads' ),
			),
		),
		'stripe_statement_descriptor'   => array(
			'id'   => 'stripe_statement_descriptor',
			'name' => __( 'Statement Descriptor', 'easy-digital-downloads' ),
			'desc' => __( 'Choose how charges will appear on customer\'s credit card statements. <em>Max 22 characters</em>', 'easy-digital-downloads' ),
			'type' => 'text',
		),
		'stripe_restrict_assets'        => array(
			'id'    => 'stripe_restrict_assets',
			'name'  => ( __( 'Restrict Stripe Assets', 'easy-digital-downloads' ) ),
			'check' => ( __( 'Only load Stripe.com hosted assets on pages that specifically utilize Stripe functionality.', 'easy-digital-downloads' ) ),
			'type'  => 'checkbox_description',
			'desc'  => sprintf(
				/* translators: 1. opening link tag; 2. closing link tag */
				__( 'Stripe advises that their Javascript library be loaded on every page to take advantage of their advanced fraud detection rules. If you are not concerned with this, enable this setting to only load the Javascript when necessary. %1$sLearn more about Stripe\'s recommended setup.%2$s', 'easy-digital-downloads' ),
				'<a href="https://stripe.com/docs/web/setup" target="_blank" rel="noopener noreferrer">',
				'</a>'
			),
		),
	);

	if ( _edds_legacy_elements_enabled() ) {
		$elements_mode = edds_get_elements_mode();

		if ( ! edds_stripe_connect_can_manage_keys() ) {
			$stripe_settings['stripe_elements_mode'] = array(
				'id'      => 'stripe_elements_mode',
				'name'    => __( 'Elements Mode', 'easy-digital-downloads' ),
				'desc'    => __( 'Toggle between using the legacy Card Elements Stripe integration and the new Payment Elements experience.', 'easy-digital-downloads' ),
				'type'    => 'select',
				'options' => array(
					'card-elements'    => __( 'Card Element', 'easy-digital-downloads' ),
					'payment-elements' => __( 'Payment Element', 'easy-digital-downloads' ),
				),
				'class'   => 'stripe-elements-mode',
				'tooltip_title' => __( 'Transitioning to Payment Elements', 'easy-digital-downloads' ),
				'tooltip_desc'  => __( 'You are seeing this option because your store has been using Card Elements prior to the EDD Stripe 2.9.0 update.<br /><br />To ensure that we do not affect your current checkout experience, you can use this setting to toggle between the Card Elements (legacy) and Payment Elements (updated version) to ensure that any customizations or theming you have done still function properly.<br /><br />Please be advised, that in a future version of the Stripe extension, we will deprecate the Card Elements, so take this time to update your store!', 'easy-digital-downloads' ),
			);
		}

		$stripe_settings['stripe_allow_prepaid'] = array(
			'id'    => 'stripe_allow_prepaid',
			'name'  => __( 'Prepaid Cards', 'easy-digital-downloads' ),
			'desc'  => __( 'Allow prepaid cards as valid payment method.', 'easy-digital-downloads' ),
			'type'  => 'checkbox',
			'class' => 'payment-elements' === $elements_mode ? 'edd-hidden card-elements-feature' : 'card-elements-feature',
		);

		$radar_rules_url = sprintf(
			'https://dashboard.stripe.com%s/settings/radar/rules',
			edd_is_test_mode() ? '/test' : ''
		);

		$stripe_settings['stripe_allow_prepaid_elements_note'] = array(
			'id'    => 'stripe_allow_prepaid_elements_note',
			'name'  => __( 'Prepaid Cards', 'easy-digital-downloads' ),
			'desc'  => sprintf(
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				__( 'Prepaid card allowance can now be managed in your %1$sStripe Radar Rules%2$s.', 'easy-digital-downloads' ),
				'<a href="' . $radar_rules_url . '" target="_blank">',
				'</a>'
			),
			'type'  => 'descriptive_text',
			'class' => 'payment-elements' === $elements_mode ? 'payment-elements-feature' : 'edd-hidden payment-elements-feature',
		);

		$stripe_settings['stripe_split_payment_fields'] = array(
			'id'    => 'stripe_split_payment_fields',
			'name'  => __( 'Split Credit Card Form', 'easy-digital-downloads' ),
			'desc'  => __( 'Use separate card number, expiration, and CVC fields in payment forms.', 'easy-digital-downloads' ),
			'type'  => 'checkbox',
			'class' => 'payment-elements' === $elements_mode ? 'edd-hidden card-elements-feature' : 'card-elements-feature',
		);

		$stripe_settings['stripe_use_existing_cards'] = array(
			'id'    => 'stripe_use_existing_cards',
			'name'  => __( 'Show Previously Used Cards', 'easy-digital-downloads' ),
			'desc'  => __( 'Provides logged in customers with a list of previous used payment methods for faster checkout.', 'easy-digital-downloads' ),
			'type'  => 'checkbox',
			'class' => 'payment-elements' === $elements_mode ? 'edd-hidden card-elements-feature' : 'card-elements-feature',
		);

		$stripe_settings['stripe_use_existing_cards_elements_note'] = array(
			'id'    => 'stripe_use_existing_cards_elements_note',
			'name'  => __( 'Show Previously Used Cards', 'easy-digital-downloads' ),
			'desc'  => sprintf(
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				__( 'Previously used cards are now managed by %1$sLink by Stripe%2$s, for even better conversions and security.', 'easy-digital-downloads' ),
				'<a href="https://link.co/" target="_blank">',
				'</a>'
			),
			'type'  => 'descriptive_text',
			'class' => 'payment-elements' === $elements_mode ? 'payment-elements-feature' : 'edd-hidden payment-elements-feature',
		);
	}

	$settings['edd-stripe'] = $stripe_settings;

	// If EDD is in Debug Mode, add some 'hidden' settings to the Stripe settings.
	if ( edd_is_debug_mode() ) {
		$card_elements_enabled = get_option( '_edds_legacy_elements_enabled', false );

		$debug_settings = array(
			'stripe_debug' => array(
				'id'   => 'stripe_debug',
				'name' => __( 'Debugging Settings', 'easy-digital-downloads' ),
				'desc' => '<div class="notice inline notice-warning">' .
					'<p>' . __( 'The following settings are available while Easy Digital Downloads is in debug mode. They are not designed to be primary settings and should be used only while debugging or when instructed to be used by the Easy Digital Downloads Team.', 'easy-digital-downloads' ) . '</p>' .
					'<p>' . __( 'There is no guarantee that these settings will remain available in future versions of Easy Digital Downloads. Easy Digital Downloads Debug Mode should be disabled once changes to these settings have been made.', 'easy-digital-downloads' ) . '</p>' .
				'</p></div>',
				'type' => 'descriptive_text',
			),
		);

		$card_elements_action       = $card_elements_enabled ? 'disable-card-elements' : 'enable-card-elements';
		$card_elements_button_label = $card_elements_enabled ? __( 'Disable access to Card Elements', 'easy-digital-downloads' ) : __( 'Enable access to Card Elements', 'easy-digital-downloads' );
		$card_elements_state_label  = $card_elements_enabled  ? __( 'Access to Legacy Card Elements is Enabled', 'easy-digital-downloads' ) : __( 'Access to Legacy Card Elements is Disabled', 'easy-digital-downloads' );

		$link_class = $card_elements_enabled ? 'edd-button__toggle--enabled' : 'edd-button__toggle--disabled';

		$debug_settings['toggle_card_elements'] = array(
				'id'   => 'stripe_toggle_card_elements',
				'name' => __( 'Toggle Card Elements', 'easy-digital-downloads' ),
				'type' => 'descriptive_text',
				'desc' => sprintf(
					'%1$s<span class="screen-reader-text">' . $card_elements_button_label . '</span>%2$s',
					'<a class="edd-button__toggle ' . $link_class . '" href="' . wp_nonce_url( edd_get_admin_url( array(
						'page'    => 'edd-settings',
						'tab'     => 'gateways',
						'section' => 'edd-stripe',
						'flag'    => $card_elements_action,
					) ), $card_elements_action ) . '">',
					'</a>'
				) .'<strong>' . $card_elements_state_label . '</strong><br />' . __( 'Card Elements is the legacy Stripe integration. Easy Digital Downloads has updated to use the more secure and reliable Payment Elements feature of Stripe. This toggle allows sites without access to Card Elements to enable or disable it.', 'easy-digital-downloads' ),
		);

		$settings['edd-stripe'] = array_merge( $settings['edd-stripe'], $debug_settings );
	}

	// Set up the new setting field for the Test Mode toggle notice.
	$notice = array(
		'stripe_connect_test_mode_toggle_notice' => array(
			'id'          => 'stripe_connect_test_mode_toggle_notice',
			'desc'        => '<p>' . __( 'You have disabled the "Test Mode" option. Once you have saved your changes, please verify your Stripe connection, especially if you have not previously connected in with "Test Mode" disabled.', 'easy-digital-downloads' ) . '</p>',
			'type'        => 'stripe_connect_notice',
			'field_class' => 'edd-hidden',
		),
	);

	// Insert the new setting after the Test Mode checkbox.
	$position = array_search( 'test_mode', array_keys( $settings['main'] ), true );
	$settings = array_merge(
		array_slice( $settings['main'], $position, 1, true ),
		$notice,
		$settings
	);

	return $settings;
}
add_filter( 'edd_settings_gateways', 'edds_add_settings' );

/**
 * Force full billing address display when taxes are enabled
 *
 * @access      public
 * @since       2.5
 *
 * @param string $value The value currently set for the Stripe billing fields setting.
 * @param string $key   The Stripe setting key to detect, stripe_billing_fields.
 *
 * @return      string
 */
function edd_stripe_sanitize_stripe_billing_fields_save( $value, $key ) {

	if ( 'stripe_billing_fields' === $key && edd_use_taxes() ) {

		$value = 'full';

	}

	return $value;

}
add_filter( 'edd_settings_sanitize_select', 'edd_stripe_sanitize_stripe_billing_fields_save', 10, 2 );

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
