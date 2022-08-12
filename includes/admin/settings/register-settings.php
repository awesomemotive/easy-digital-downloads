<?php
/**
 * Register Settings
 *
 * @package     EDD
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since 1.8.4
 * @global $edd_options Array of all the EDD Options
 * @return mixed
 */
function edd_get_option( $key = '', $default = false ) {
	global $edd_options;

	$value = $default;

	if ( isset( $edd_options[ $key ] ) ) {
		if ( is_numeric( $edd_options[ $key ] ) ) {
			$value = $edd_options[ $key ];
		} else {
			$value = ! empty( $edd_options[ $key ] ) ? $edd_options[ $key ] : $default;
		}
	}

	$value = apply_filters( 'edd_get_option', $value, $key, $default );

	return apply_filters( 'edd_get_option_' . $key, $value, $key, $default );
}

/**
 * Update an option
 *
 * Updates an edd setting value in both the db and the global variable.
 * Warning: Passing in an empty, false or null string value will remove
 *          the key from the edd_options array.
 *
 * @since 2.3
 *
 * @param string          $key         The Key to update
 * @param string|bool|int $value       The value to set the key to
 *
 * @global                $edd_options Array of all the EDD Options
 * @return boolean True if updated, false if not.
 */
function edd_update_option( $key = '', $value = false ) {
	global $edd_options;

	// If no key, exit
	if ( empty( $key ) ) {
		return false;
	}

	if ( empty( $value ) ) {
		$remove_option = edd_delete_option( $key );

		return $remove_option;
	}

	// First let's grab the current settings
	$options = get_option( 'edd_settings' );

	// Let's let devs alter that value coming in
	$value = apply_filters( 'edd_update_option', $value, $key );

	// Next let's try to update the value
	$options[ $key ] = $value;
	$did_update      = update_option( 'edd_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ) {
		$edd_options[ $key ] = $value;
	}

	return $did_update;
}

/**
 * Remove an option
 *
 * Removes an edd setting value in both the db and the global variable.
 *
 * @since 2.3
 *
 * @param string $key         The Key to delete
 *
 * @global       $edd_options Array of all the EDD Options
 * @return boolean True if removed, false if not.
 */
function edd_delete_option( $key = '' ) {
	global $edd_options;

	// If no key, exit
	if ( empty( $key ) ) {
		return false;
	}

	// First let's grab the current settings
	$options = get_option( 'edd_settings' );

	// Next let's try to update the value
	if ( isset( $options[ $key ] ) ) {

		unset( $options[ $key ] );

	}

	// Remove this option from the global EDD settings to the array_merge in edd_settings_sanitize() doesn't re-add it.
	if ( isset( $edd_options[ $key ] ) ) {
		unset( $edd_options[ $key ] );
	}

	$did_update = update_option( 'edd_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ) {
		$edd_options = $options;
	}

	return $did_update;
}

/**
 * Get Settings
 *
 * Retrieves all plugin settings
 *
 * @since 1.0
 * @return array EDD settings
 */
function edd_get_settings() {

	// Get the option key
	$settings = get_option( 'edd_settings' );

	// Look for old option keys
	if ( empty( $settings ) ) {

		// Old option keys
		$old_keys = array(
			'edd_settings_general',
			'edd_settings_gateways',
			'edd_settings_emails',
			'edd_settings_styles',
			'edd_settings_taxes',
			'edd_settings_extensions',
			'edd_settings_licenses',
			'edd_settings_misc'
		);

		// Merge old keys together
		foreach ( $old_keys as $key ) {
			$settings[ $key ] = get_option( $key, array() );
		}

		// Remove empties
		$settings = array_filter( array_values( $settings ) );

		// Update the main option
		update_option( 'edd_settings', $settings );
	}

	// Filter & return
	return apply_filters( 'edd_get_settings', $settings );
}

/**
 * Add all settings sections and fields
 *
 * @since 1.0
 * @return void
 */
function edd_register_settings() {

	// Get registered settings
	$edd_settings = edd_get_registered_settings();

	// Loop through settings
	foreach ( $edd_settings as $tab => $sections ) {

		// Loop through sections
		foreach ( $sections as $section => $settings ) {

			// Check for backwards compatibility
			$section_tabs = edd_get_settings_tab_sections( $tab );
			if ( ! is_array( $section_tabs ) || ! array_key_exists( $section, $section_tabs ) ) {
				$section  = 'main';
				$settings = $sections;
			}

			// Current page
			$page = "edd_settings_{$tab}_{$section}";

			// Add the settings section
			add_settings_section(
				$page,
				__return_null(),
				'__return_false',
				$page
			);

			foreach ( $settings as $option ) {

				// For backwards compatibility
				if ( empty( $option['id'] ) ) {
					continue;
				}

				// Parse args
				$args = wp_parse_args( $option, array(
					'section'       => $section,
					'id'            => null,
					'desc'          => '',
					'name'          => '',
					'size'          => null,
					'options'       => '',
					'std'           => '',
					'min'           => null,
					'max'           => null,
					'step'          => null,
					'chosen'        => null,
					'multiple'      => null,
					'placeholder'   => null,
					'allow_blank'   => true,
					'readonly'      => false,
					'faux'          => false,
					'tooltip_title' => false,
					'tooltip_desc'  => false,
					'field_class'   => '',
					'label_for'     => false
				) );

				// Callback fallback
				$func     = 'edd_' . $args['type'] . '_callback';
				$callback = ! function_exists( $func )
					? 'edd_missing_callback'
					: $func;

				// Link the label to the form field
				if ( empty( $args['label_for'] ) ) {
					$args['label_for'] = 'edd_settings[' . $args['id'] . ']';
				}

				// Add the settings field
				add_settings_field(
					'edd_settings[' . $args['id'] . ']',
					$args['name'],
					$callback,
					$page,
					$page,
					$args
				);
			}
		}
	}

	// Register our setting in the options table
	register_setting( 'edd_settings', 'edd_settings', 'edd_settings_sanitize' );
}
add_action( 'admin_init', 'edd_register_settings' );

/**
 * Retrieve the array of plugin settings
 *
 * @since 1.8
 * @since 3.0 Use a static variable internally to store registered settings
 * @return array
 */
function edd_get_registered_settings() {
	static $edd_settings = null;

	/**
	 * 'Whitelisted' EDD settings, filters are provided for each settings
	 * section to allow extensions and other plugins to add their own settings
	 */

	// Only build settings if not already build
	if ( null === $edd_settings ) {
		$options = array(
			'none'      => __( 'Do Nothing', 'easy-digital-downloads' ),
			'anonymize' => __( 'Anonymize',  'easy-digital-downloads' ),
			'delete'    => __( 'Delete',     'easy-digital-downloads' )
		);
		$debug_log_url    = edd_get_admin_url( array( 'page' => 'edd-tools', 'tab' => 'debug_log' ) );
		$debug_log_link   = '<a href="' . esc_url( $debug_log_url ) . '">' . __( 'View the Log', 'easy-digital-downloads' ) . '</a>';
		$payment_statuses = edd_get_payment_statuses();
		$pages            = edd_get_pages();
		$gateways         = edd_get_payment_gateways();
		$admin_email      = get_bloginfo( 'admin_email' );
		$site_name        = get_bloginfo( 'name' );
		$site_hash        = substr( md5( $site_name ), 0, 10 );
		$edd_settings     = array(

			// General Settings
			'general' => apply_filters( 'edd_settings_general', array(
				'main' => array(
					'business_settings' => array(
						'id'            => 'business_settings',
						'name'          => '<h3>' . __( 'Business Info', 'easy-digital-downloads' ) . '</h3>',
						'desc'          => '',
						'type'          => 'header',
						'tooltip_title' => __( 'Business Information', 'easy-digital-downloads' ),
						'tooltip_desc'  => __( 'Easy Digital Downloads uses the following business information for things like pre-populating tax fields, and connecting third-party services with the same information.', 'easy-digital-downloads' ),
					),
					'entity_name' => array(
						'id'          => 'entity_name',
						'name'        => __( 'Business Name', 'easy-digital-downloads' ),
						'desc'        => __( 'The official (legal) name of your store. Defaults to Site Title if empty.', 'easy-digital-downloads' ),
						'type'        => 'text',
						'std'         => $site_name,
						'placeholder' => $site_name
					),
					'entity_type' => array(
						'id'          => 'entity_type',
						'name'        => __( 'Business Type', 'easy-digital-downloads' ),
						'desc'        => __( 'Choose "Individual" if you do not have an official/legal business ID, or "Company" if a registered business entity exists.', 'easy-digital-downloads' ),
						'type'        => 'select',
						'options'     => array(
							'individual' => esc_html__( 'Individual', 'easy-digital-downloads' ),
							'company'    => esc_html__( 'Company',    'easy-digital-downloads' )
						)
					),
					'business_address' => array(
						'id'          => 'business_address',
						'name'        => __( 'Business Address', 'easy-digital-downloads' ),
						//'desc'        => __( 'Your company or home address, based on business type above.', 'easy-digital-downloads' ),
						'type'        => 'text',
						'placeholder' => ''
					),
					'business_address_2' => array(
						'id'          => 'business_address_2',
						'name'        => __( 'Business Address (Extra)', 'easy-digital-downloads' ),
						//'desc'        => __( 'Anything requiring an extra line (suite, attention, etc...)', 'easy-digital-downloads' ),
						'type'        => 'text',
						'placeholder' => ''
					),
					'business_city' => array(
						'id'          => 'business_city',
						'name'        => __( 'Business City', 'easy-digital-downloads' ),
						//'desc'        => __( 'The physical city your company or home is in.', 'easy-digital-downloads' ),
						'type'        => 'text',
						'placeholder' => ''
					),
					'business_postal_code' => array(
						'id'          => 'business_postal_code',
						'name'        => __( 'Business Postal Code', 'easy-digital-downloads' ),
						//'desc'        => __( 'The zip/postal code for your company or home address.', 'easy-digital-downloads' ),
						'type'        => 'text',
						'size'        => 'medium',
						'placeholder' => ''
					),
					'base_country' => array(
						'id'          => 'base_country',
						'name'        => __( 'Business Country', 'easy-digital-downloads' ),
						//'desc'        => __( 'The country your company or home is in.', 'easy-digital-downloads' ),
						'type'        => 'select',
						'options'     => edd_get_country_list(),
						'chosen'      => true,
						'field_class' => 'edd_countries_filter',
						'placeholder' => __( 'Select a country', 'easy-digital-downloads' ),
						'data'        => array(
							'nonce' => wp_create_nonce( 'edd-country-field-nonce' )
						)
					),
					'base_state' => array(
						'id'          => 'base_state',
						'name'        => __( 'Business Region', 'easy-digital-downloads' ),
						//'desc'        => __( 'The state/province/territory your company or home is in.', 'easy-digital-downloads' ),
						'type'        => 'shop_states',
						'chosen'      => true,
						'field_class' => 'edd_regions_filter',
						'placeholder' => __( 'Select a region', 'easy-digital-downloads' ),
					),
				),
				'pages' => array(
					'page_settings' => array(
						'id'            => 'page_settings',
						'name'          => '<h3>' . __( 'Pages', 'easy-digital-downloads' ) . '</h3>',
						'desc'          => '',
						'type'          => 'header',
						'tooltip_title' => __( 'Page Settings', 'easy-digital-downloads' ),
						'tooltip_desc'  => __( 'Easy Digital Downloads uses the pages below for handling the display of checkout, purchase confirmation, purchase history, and purchase failures. If pages are deleted or removed in some way, they can be recreated manually from the Pages menu. When re-creating the pages, enter the shortcode shown in the page content area.', 'easy-digital-downloads' ),
					),
					'purchase_page' => array(
						'id'          => 'purchase_page',
						'name'        => __( 'Primary Checkout Page', 'easy-digital-downloads' ),
						'desc'        => __( 'This is the checkout page where buyers will complete their purchases.<br>The <code>[download_checkout]</code> shortcode must be on this page.', 'easy-digital-downloads' ),
						'type'        => 'select',
						'options'     => $pages,
						'chosen'      => true,
						'placeholder' => __( 'Select a page', 'easy-digital-downloads' ),
					),
					'success_page' => array(
						'id'          => 'success_page',
						'name'        => __( 'Success Page', 'easy-digital-downloads' ),
						'desc'        => __( 'This is the page buyers are sent to after completing their purchases.<br>The <code>[edd_receipt]</code> shortcode should be on this page.', 'easy-digital-downloads' ),
						'type'        => 'select',
						'options'     => $pages,
						'chosen'      => true,
						'placeholder' => __( 'Select a page', 'easy-digital-downloads' ),
					),
					'failure_page' => array(
						'id'          => 'failure_page',
						'name'        => __( 'Failed Transaction Page', 'easy-digital-downloads' ),
						'desc'        => __( 'This is the page buyers are sent to if their transaction is cancelled or fails.', 'easy-digital-downloads' ),
						'type'        => 'select',
						'options'     => $pages,
						'chosen'      => true,
						'placeholder' => __( 'Select a page', 'easy-digital-downloads' ),
					),
					'purchase_history_page' => array(
						'id'          => 'purchase_history_page',
						'name'        => __( 'Purchase History Page', 'easy-digital-downloads' ),
						'desc'        => __( 'This page shows a complete purchase history for the current user, including download links.<br>The <code>[purchase_history]</code> shortcode should be on this page.', 'easy-digital-downloads' ),
						'type'        => 'select',
						'options'     => $pages,
						'chosen'      => true,
						'placeholder' => __( 'Select a page', 'easy-digital-downloads' ),
					),
					'login_redirect_page' => array(
						'id'          => 'login_redirect_page',
						'name'        => __( 'Login Redirect Page', 'easy-digital-downloads' ),
						'desc'        => sprintf(
							__( 'If a customer logs in using the <code>[edd_login]</code> shortcode, this is the page they will be redirected to.<br>Note: override using the redirect shortcode attribute: <code>[edd_login redirect="%s"]</code>.', 'easy-digital-downloads' ),
							trailingslashit( home_url() )
						),
						'type'        => 'select',
						'options'     => $pages,
						'chosen'      => true,
						'placeholder' => __( 'Select a page', 'easy-digital-downloads' ),
					)
				),
				'currency' => array(
					'currency_settings' => array(
						'id'            => 'currency_settings',
						'name'          => '<h3>' . __( 'Currency', 'easy-digital-downloads' ) . '</h3>',
						'desc'          => '',
						'type'          => 'header',
						'tooltip_title' => __( 'Currency Settings', 'easy-digital-downloads' ),
						'tooltip_desc'  => __( 'Different countries use different formatting for their currency. You will want to pick what most of your users will expect to use.', 'easy-digital-downloads' ),
					),
					'currency' => array(
						'id'      => 'currency',
						'name'    => __( 'Currency', 'easy-digital-downloads' ),
						'desc'    => __( 'Choose your currency. Note that some payment gateways have currency restrictions.', 'easy-digital-downloads' ),
						'type'    => 'select',
						'chosen'  => true,
						'options' => edd_get_currencies(),
					),
					'currency_position' => array(
						'id'      => 'currency_position',
						'name'    => __( 'Currency Position', 'easy-digital-downloads' ),
						'desc'    => __( 'Choose the location of the currency sign.', 'easy-digital-downloads' ),
						'type'    => 'select',
						'options' => array(
							'before' => __( 'Before ($10)', 'easy-digital-downloads' ),
							'after'  => __( 'After (10$)',  'easy-digital-downloads' )
						),
					),
					'thousands_separator' => array(
						'id'          => 'thousands_separator',
						'name'        => __( 'Thousandths Separator', 'easy-digital-downloads' ),
						'desc'        => __( 'The symbol to separate thousandths. Usually <code>,</code> or <code>.</code>.', 'easy-digital-downloads' ),
						'type'        => 'text',
						'size'        => 'small',
						'field_class' => 'code',
						'std'         => ',',
						'placeholder' => ','
					),
					'decimal_separator' => array(
						'id'          => 'decimal_separator',
						'name'        => __( 'Decimal Separator', 'easy-digital-downloads' ),
						'desc'        => __( 'The symbol to separate decimal points. Usually <code>,</code> or <code>.</code>.', 'easy-digital-downloads' ),
						'type'        => 'text',
						'size'        => 'small',
						'field_class' => 'code',
						'std'         => '.',
						'placeholder' => '.'
					),
				),
				'api' => array(
					'api_settings' => array(
						'id'            => 'api_settings',
						'name'          => '<h3>' . __( 'API', 'easy-digital-downloads' ) . '</h3>',
						'desc'          => '',
						'type'          => 'header',
						'tooltip_title' => __( 'API Settings', 'easy-digital-downloads' ),
						'tooltip_desc'  => __( 'The Easy Digital Downloads REST API provides access to store data through our API endpoints. Enable this setting if you would like all user accounts to be able to generate their own API keys.', 'easy-digital-downloads' ),
					),
					'api_allow_user_keys' => array(
						'id'    => 'api_allow_user_keys',
						'name'  => __( 'Allow User Keys', 'easy-digital-downloads' ),
						'check' => __( 'Check this box to allow all users to generate API keys.', 'easy-digital-downloads' ),
						'desc'  => __( 'Users who can <code>manage_shop_settings</code> are always allowed to generate keys.', 'easy-digital-downloads' ),
						'type'  => 'checkbox_description',
					),
					'api_help' => array(
						'id'   => 'api_help',
						'desc' => sprintf( __( 'Visit the <a href="%s" target="_blank">REST API documentation</a> for further information.', 'easy-digital-downloads' ), 'http://docs.easydigitaldownloads.com/article/1131-edd-rest-api-introduction' ),
						'type' => 'descriptive_text',
					),
				),
				'tracking' => array(
					'tracking_settings' => array(
						'id'   => 'tracking_settings',
						'name' => '<h3>' . __( 'Tracking', 'easy-digital-downloads' ) . '</h3>',
						'desc' => '',
						'type' => 'header',
					),
					'allow_tracking' => array(
						'id'    => 'allow_tracking',
						'name'  => __( 'Usage Tracking', 'easy-digital-downloads' ),
						'check' => __( 'Allow',          'easy-digital-downloads' ),
						'desc'  => sprintf(
							/* translators: %1$s Link to tracking information, do not translate. %2$s Link to EDD newsleter, do not translate. %3$s Link to EDD extensions, do not translate */
							__( 'Help us make Easy Digital Downloads better by opting into anonymous usage tracking. <a href="%1$s" target="_blank">Here is what we track</a>.<br>If you opt-in here and to <a href="%2$s" target="_blank">our newsletter</a>, we will email you a discount code for our <a href="%3$s" target="_blank">extension shop</a>.', 'easy-digital-downloads' ),
							esc_url( 'https://docs.easydigitaldownloads.com/article/1419-what-information-will-be-tracked-by-opting-into-usage-tracking' ),
							esc_url( 'https://easydigitaldownloads.com/subscribe/?utm_source=' . esc_attr( $site_hash ) . '&utm_medium=admin&utm_term=settings&utm_campaign=EDDUsageTracking' ),
							esc_url( 'https://easydigitaldownloads.com/downloads/?utm_source=' . esc_attr( $site_hash ) . '&utm_medium=admin&utm_term=settings&utm_campaign=EDDUsageTracking' )
						),
						'type' => 'checkbox_description',
					)
				),
			) ),

			// Payment Gateways Settings
			'gateways' => apply_filters( 'edd_settings_gateways', array(
				'main' => array(
					'test_mode' => array(
						'id'    => 'test_mode',
						'name'  => __( 'Test Mode', 'easy-digital-downloads' ),
						'check' => __( 'Enabled',   'easy-digital-downloads' ),
						'desc'  => __( 'While test mode is enabled, no live transactions are processed.<br>Use test mode in conjunction with the sandbox/test account for the payment gateways to test.', 'easy-digital-downloads' ),
						'type'  => 'checkbox_description'
					),
					'gateways' => array(
						'id'      => 'gateways',
						'name'    => __( 'Active Gateways', 'easy-digital-downloads' ),
						'desc'    => __( 'Choose the payment gateways you want to enable.', 'easy-digital-downloads' ),
						'type'    => 'gateways',
						'options' => $gateways,
					),
					'default_gateway' => array(
						'id'      => 'default_gateway',
						'name'    => __( 'Default Gateway', 'easy-digital-downloads' ),
						'desc'    => __( 'Automatically select this gateway on checkout pages.<br>If empty, the first active gateway is selected instead.', 'easy-digital-downloads' ),
						'type'    => 'gateway_select',
						'options' => $gateways,
					),
					'accepted_cards' => array(
						'id'      => 'accepted_cards',
						'name'    => __( 'Payment Method Icons', 'easy-digital-downloads' ),
						'desc'    => __( 'Display icons for the selected payment methods.', 'easy-digital-downloads' ) . '<br/>' . __( 'You will also need to configure your gateway settings if you are accepting credit cards.', 'easy-digital-downloads' ),
						'type'    => 'payment_icons',
						'options' => apply_filters( 'edd_accepted_payment_icons', array(
							'mastercard'      => 'Mastercard',
							'visa'            => 'Visa',
							'americanexpress' => 'American Express',
							'discover'        => 'Discover',
							'paypal'          => 'PayPal'
						) ),
					),
				),
				'checkout' => array(
					'enforce_ssl' => array(
						'id'    => 'enforce_ssl',
						'name'  => __( 'Enforce SSL on Checkout', 'easy-digital-downloads' ),
						'check' => __( 'Enforced',                'easy-digital-downloads' ),
						'desc'  => __( 'Redirect all customers to the secure checkout page. You must have an SSL certificate installed to use this option.', 'easy-digital-downloads' ),
						'type'  => 'checkbox_description',
					),
					'redirect_on_add'    => array(
						'id'            => 'redirect_on_add',
						'name'          => __( 'Redirect to Checkout', 'easy-digital-downloads' ),
						'desc'          => __( 'Immediately redirect to checkout after adding an item to the cart?', 'easy-digital-downloads' ),
						'type'          => 'checkbox',
						'tooltip_title' => __( 'Redirect to Checkout', 'easy-digital-downloads' ),
						'tooltip_desc'  => __( 'When enabled, once an item has been added to the cart, the customer will be redirected directly to your checkout page. This is useful for stores that sell single items.', 'easy-digital-downloads' ),
					),
					'logged_in_only' => array(
						'id'            => 'logged_in_only',
						'name'          => __( 'Require Login', 'easy-digital-downloads' ),
						'desc'          => __( 'Require that users be logged-in to purchase files.', 'easy-digital-downloads' ),
						'type'          => 'checkbox',
						'tooltip_title' => __( 'Require Login', 'easy-digital-downloads' ),
						'tooltip_desc'  => __( 'You can require that customers create and login to user accounts prior to purchasing from your store by enabling this option. When unchecked, users can purchase without being logged in by using their name and email address.', 'easy-digital-downloads' ),
					),
					'show_register_form' => array(
						'id'      => 'show_register_form',
						'name'    => __( 'Show Register / Login Form', 'easy-digital-downloads' ),
						'desc'    => __( 'Display the registration and login forms on the checkout page for non-logged-in users.', 'easy-digital-downloads' ),
						'type'    => 'select',
						'std'     => 'none',
						'options' => array(
							'both'         => __( 'Registration and Login Forms', 'easy-digital-downloads' ),
							'registration' => __( 'Registration Form Only', 'easy-digital-downloads' ),
							'login'        => __( 'Login Form Only', 'easy-digital-downloads' ),
							'none'         => __( 'None', 'easy-digital-downloads' ),
						),
					),
					'enable_cart_saving' => array(
						'id'            => 'enable_cart_saving',
						'name'          => __( 'Enable Cart Saving', 'easy-digital-downloads' ),
						'desc'          => __( 'Check this to enable cart saving on the checkout.', 'easy-digital-downloads' ),
						'type'          => 'checkbox',
						'tooltip_title' => __( 'Cart Saving', 'easy-digital-downloads' ),
						'tooltip_desc'  => __( 'Cart saving allows shoppers to create a temporary link to their current shopping cart so they can come back to it later, or share it with someone.', 'easy-digital-downloads' ),
					),
					'moderation_settings' => array(
							'id'   => 'moderation_settings',
							'name' => '<h3>' . __( 'Moderation', 'easy-digital-downloads' ) . '</h3>',
							'desc' => '',
							'type' => 'header',
							'tooltip_title' => __( 'Moderation', 'easy-digital-downloads' ),
							'tooltip_desc'  => __( 'It is sometimes necessary to temporarily prevent certain potential customers from checking out. Use these settings to control who can make purchases.', 'easy-digital-downloads' ),
					),
					'banned_emails' => array(
							'id'    => 'banned_emails',
							'name'  => __( 'Banned Emails', 'easy-digital-downloads' ),
							'desc'  => __( 'Emails placed in the box above will not be allowed to make purchases.', 'easy-digital-downloads' ) . '<br>' . __( 'One per line, enter: email addresses, domains (<code>@example.com</code>), or TLDs (<code>.gov</code>).', 'easy-digital-downloads' ),
							'type'  => 'textarea',
							'placeholder' => __( '@example.com', 'easy-digital-downloads' )
					),
				),
				'refunds' => array(
						'refunds_settings' => array(
								'id'   => 'refunds_settings',
								'name' => '<h3>' . __( 'Refunds', 'easy-digital-downloads' ) . '</h3>',
								'desc' => '',
								'type' => 'header',
								'tooltip_title' => __( 'Refunds', 'easy-digital-downloads' ),
								'tooltip_desc'  => __( 'As a shop owner, sometimes refunds are necessary. Use these settings to decide how refunds will work in your shop.', 'easy-digital-downloads' ),
						),
						'refundability' => array(
								'id'      => 'refundability',
								'name'    => __( 'Default Status', 'easy-digital-downloads' ),
								'desc'    => __( 'This will be the store default. It can be changed at a per-product level.', 'easy-digital-downloads' ),
								'type'    => 'select',
								'std'     => 'refundable',
								'options' => edd_get_refundability_types(),
						),
						'refund_window' => array(
								'id'   => 'refund_window',
								'name' => __( 'Refund Window', 'easy-digital-downloads' ),
								'desc' => __( 'Number of days (after a sale) when refunds can be processed.<br>Default is <code>30</code> days. Set to <code>0</code> for infinity. It can be changed at a per-product level.', 'easy-digital-downloads' ),
								'std'  => 30,
								'type' => 'number',
								'size' => 'small',
								'max'  => 3650, // Ten year maximum, because why explicitly support longer
								'min'  => 0,
								'step' => 1,
						),
				),
				'accounting' => array(
					'enable_skus' => array(
						'id'    => 'enable_skus',
						'name'  => __( 'Enable SKU Entry', 'easy-digital-downloads' ),
						'check' => __( 'Check this box to allow entry of product SKUs.', 'easy-digital-downloads' ),
						'desc'  => __( 'SKUs will be shown on purchase receipt and exported purchase histories.', 'easy-digital-downloads' ),
						'type'  => 'checkbox_description',
					),
					'enable_sequential' => array(
						'id'    => 'enable_sequential',
						'name'  => __( 'Sequential Order Numbers', 'easy-digital-downloads' ),
						'check' => __( 'Check this box to enable sequential order numbers.', 'easy-digital-downloads' ),
						'desc'  => __( 'Does not impact previous orders. Future orders will be sequential.', 'easy-digital-downloads' ),
						'type'  => 'checkbox_description',
					),
					'sequential_start' => array(
						'id'   => 'sequential_start',
						'name' => __( 'Sequential Starting Number', 'easy-digital-downloads' ),
						'desc' => __( 'The number at which the sequence should begin.', 'easy-digital-downloads' ),
						'type' => 'number',
						'size' => 'small',
						'std'  => '1',
					),
					'sequential_prefix' => array(
						'id'   => 'sequential_prefix',
						'name' => __( 'Sequential Number Prefix', 'easy-digital-downloads' ),
						'desc' => __( 'A prefix to prepend to all sequential order numbers.', 'easy-digital-downloads' ),
						'type' => 'text',
					),
					'sequential_postfix' => array(
						'id'   => 'sequential_postfix',
						'name' => __( 'Sequential Number Postfix', 'easy-digital-downloads' ),
						'desc' => __( 'A postfix to append to all sequential order numbers.', 'easy-digital-downloads' ),
						'type' => 'text',
					)
				),
			) ),

			// Emails Settings
			'emails' => apply_filters( 'edd_settings_emails', array(
				'main' => array(
					'email_header' => array(
						'id'   => 'email_header',
						'name' => '<strong>' . __( 'Email Configuration', 'easy-digital-downloads' ) . '</strong>',
						'type' => 'header',
					),
					'email_template' => array(
						'id'      => 'email_template',
						'name'    => __( 'Template', 'easy-digital-downloads' ),
						'desc'    => __( 'Choose a template. Click "Save Changes" then "Preview Purchase Receipt" to see the new template.', 'easy-digital-downloads' ),
						'type'    => 'select',
						'options' => edd_get_email_templates(),
					),
					'email_logo' => array(
						'id'      => 'email_logo',
						'name'    => __( 'Logo', 'easy-digital-downloads' ),
						'desc'    => __( 'Upload or choose a logo to be displayed at the top of sales receipt emails. Displayed on HTML emails only.', 'easy-digital-downloads' ),
						'type'    => 'upload',
					),
					'from_name' => array(
						'id'          => 'from_name',
						'name'        => __( 'From Name', 'easy-digital-downloads' ),
						'desc'        => __( 'This should be your site or shop name. Defaults to Site Title if empty.', 'easy-digital-downloads' ),
						'type'        => 'text',
						'std'         => $site_name,
						'placeholder' => $site_name
					),
					'from_email' => array(
						'id'          => 'from_email',
						'name'        => __( 'From Email', 'easy-digital-downloads' ),
						'desc'        => __( 'This will act as the "from" and "reply-to" addresses.', 'easy-digital-downloads' ),
						'type'        => 'email',
						'std'         => $admin_email,
						'placeholder' => $admin_email
					),
					'email_settings' => array(
						'id'      => 'email_settings',
						'name'    => '',
						'desc'    => '',
						'type'    => 'hook',
					),
				),
				'purchase_receipts' => array(
					'purchase_receipt_email_settings' => array(
						'id'   => 'purchase_receipt_email_settings',
						'name' => '',
						'desc' => '',
						'type' => 'hook',
					),
					'purchase_subject' => array(
						'id'   => 'purchase_subject',
						'name' => __( 'Purchase Email Subject', 'easy-digital-downloads' ),
						'desc' => __( 'Enter the subject line for the purchase receipt email.', 'easy-digital-downloads' ),
						'type' => 'text',
						'std'  => __( 'Purchase Receipt', 'easy-digital-downloads' ),
					),
					'purchase_heading' => array(
						'id'   => 'purchase_heading',
						'name' => __( 'Purchase Email Heading', 'easy-digital-downloads' ),
						'desc' => __( 'Enter the heading for the purchase receipt email.', 'easy-digital-downloads' ),
						'type' => 'text',
						'std'  => __( 'Purchase Receipt', 'easy-digital-downloads' ),
					),
					'purchase_receipt' => array(
						'id'   => 'purchase_receipt',
						'name' => __( 'Purchase Receipt', 'easy-digital-downloads' ),
						'desc' => __( 'Text to email customers after completing a purchase. Personalize with HTML and <code>{tag}</code> markers.', 'easy-digital-downloads' ) . '<br/><br/>' . edd_get_emails_tags_list(),
						'type' => 'rich_editor',
						'std'  => __( "Dear", "easy-digital-downloads" ) . " {name},\n\n" . __( "Thank you for your purchase. Please click on the link(s) below to download your files.", "easy-digital-downloads" ) . "\n\n{download_list}\n\n{sitename}",
					),
				),
				'sale_notifications' => array(
					'sale_notification_subject' => array(
						'id'   => 'sale_notification_subject',
						'name' => __( 'Sale Notification Subject', 'easy-digital-downloads' ),
						'desc' => __( 'Enter the subject line for the sale notification email.', 'easy-digital-downloads' ),
						'type' => 'text',
						'std'  => 'New download purchase - Order #{payment_id}',
					),
					'sale_notification_heading' => array(
						'id'   => 'sale_notification_heading',
						'name' => __( 'Sale Notification Heading', 'easy-digital-downloads' ),
						'desc' => __( 'Enter the heading for the sale notification email.', 'easy-digital-downloads' ),
						'type' => 'text',
						'std'  => __( 'New Sale!', 'easy-digital-downloads' ),
					),
					'sale_notification' => array(
						'id'   => 'sale_notification',
						'name' => __( 'Sale Notification', 'easy-digital-downloads' ),
						'desc' => __( 'Text to email as a notification for every completed purchase. Personalize with HTML and <code>{tag}</code> markers.', 'easy-digital-downloads' ) . '<br/><br/>' . edd_get_emails_tags_list(),
						'type' => 'rich_editor',
						'std'  => edd_get_default_sale_notification_email(),
					),
					'admin_notice_emails' => array(
						'id'   => 'admin_notice_emails',
						'name' => __( 'Sale Notification Emails', 'easy-digital-downloads' ),
						'desc' => __( 'Enter the email address(es) that should receive a notification anytime a sale is made. One per line.', 'easy-digital-downloads' ),
						'type' => 'textarea',
						'std'  => $admin_email,
					),
					'disable_admin_notices' => array(
						'id'   => 'disable_admin_notices',
						'name' => __( 'Disable Admin Notifications', 'easy-digital-downloads' ),
						'desc' => __( 'Check this box if you do not want to receive sales notification emails.', 'easy-digital-downloads' ),
						'type' => 'checkbox',
					),
				),
			) ),

			// Marketing Settings
			'marketing'  => apply_filters(
				'edd_settings_marketing',
				array(
					'main' => array(
						'recapture'                => array(
							'id'   => 'recapture',
							'name' => __( 'Abandoned Cart Recovery', 'easy-digital-downloads' ),
							'desc' => '',
							'type' => 'recapture',
						),
						'allow_multiple_discounts' => array(
							'id'   => 'allow_multiple_discounts',
							'name' => __( 'Multiple Discounts', 'easy-digital-downloads' ),
							'desc' => __( 'Allow customers to use multiple discounts on the same purchase?', 'easy-digital-downloads' ),
							'type' => 'checkbox',
						),
					),
				)
			),

			// Taxes Settings
			'taxes' => apply_filters( 'edd_settings_taxes', array(
				'main' => array(
					'enable_taxes' => array(
						'id'            => 'enable_taxes',
						'name'          => __( 'Taxes', 'easy-digital-downloads' ),
						'check'         => __( 'Enabled', 'easy-digital-downloads' ),
						'desc'          => __( 'Check this to enable taxes on purchases.', 'easy-digital-downloads' ),
						'type'          => 'checkbox_description',
						'tooltip_title' => __( 'Enabling Taxes', 'easy-digital-downloads' ),
						'tooltip_desc'  => __( 'With taxes enabled, customers will be taxed based on the rates you define, and are required to input their address on checkout so rates can be calculated accordingly.', 'easy-digital-downloads' ),
					),
					'tax_help' => array(
						'id'   => 'tax_help',
						'name' => '',
						'desc' => sprintf( __( 'Visit the <a href="%s" target="_blank">Tax setup documentation</a> for further information. <p class="description">If you need VAT support, there are options listed on the documentation page.</p>', 'easy-digital-downloads' ), 'https://docs.easydigitaldownloads.com/article/238-tax-settings' ),
						'type' => 'descriptive_text',
					),
					'prices_include_tax' => array(
						'id'            => 'prices_include_tax',
						'name'          => __( 'Prices Include Tax', 'easy-digital-downloads' ),
						'desc'          => __( 'This option affects how you enter prices.', 'easy-digital-downloads' ),
						'type'          => 'radio',
						'std'           => 'no',
						'options'       => array(
							'yes' => __( 'Yes, I will enter prices inclusive of tax', 'easy-digital-downloads' ),
							'no'  => __( 'No, I will enter prices exclusive of tax', 'easy-digital-downloads' ),
						),
						'tooltip_title' => __( 'Prices Inclusive of Tax', 'easy-digital-downloads' ),
						'tooltip_desc'  => __( 'When using prices inclusive of tax, you will be entering your prices as the total amount you want a customer to pay for the download, including tax. Easy Digital Downloads will calculate the proper amount to tax the customer for the defined total price.', 'easy-digital-downloads' ),
					),
					'display_tax_rate' => array(
						'id'    => 'display_tax_rate',
						'name'  => __( 'Show Tax Rate on Prices', 'easy-digital-downloads' ),
						'check' => __( 'Show', 'easy-digital-downloads' ),
						'desc'  => __( 'Some countries require a notice that product prices include tax.', 'easy-digital-downloads' ),
						'type' => 'checkbox_description',
					),
					'checkout_include_tax' => array(
						'id'            => 'checkout_include_tax',
						'name'          => __( 'Show in Checkout', 'easy-digital-downloads' ),
						'desc'          => __( 'Should prices on the checkout page be shown with or without tax?', 'easy-digital-downloads' ),
						'type'          => 'select',
						'std'           => 'no',
						'options'       => array(
							'yes' => __( 'Including tax', 'easy-digital-downloads' ),
							'no'  => __( 'Excluding tax', 'easy-digital-downloads' ),
						),
						'tooltip_title' => __( 'Taxes Displayed for Products on Checkout', 'easy-digital-downloads' ),
						'tooltip_desc'  => __( 'This option will determine whether the product price displays with or without tax on checkout.', 'easy-digital-downloads' ),
					),
				),
				'rates' => array(
					'tax_rates' => array(
						'id'   => 'tax_rates',
						'name' => '<strong>' . __( 'Regional Rates', 'easy-digital-downloads' ) . '</strong>',
						'desc' => __( 'Configure rates for each region you wish to collect sales tax in.', 'easy-digital-downloads' ),
						'type' => 'tax_rates',
					),
				)
			) ),

			// Extension Settings
			'extensions' => apply_filters( 'edd_settings_extensions', array() ),
			'licenses'   => apply_filters( 'edd_settings_licenses',   array() ),

			// Misc Settings
			'misc' => apply_filters( 'edd_settings_misc', array(
				'main' => array(
					'debug_mode' => array(
						'id'    => 'debug_mode',
						'name'  => __( 'Debug Mode', 'easy-digital-downloads' ),
						'check' => __( 'Enabled',    'easy-digital-downloads' ),
						'desc'  => __( 'Check this box to enable Debug Mode.', 'easy-digital-downloads' ) . ' ' . $debug_log_link,
						'type'  => 'checkbox_description',
					),
					'disable_styles' => array(
						'id'            => 'disable_styles',
						'name'          => __( 'Disable Styles', 'easy-digital-downloads' ),
						'check'         => __( 'Check this box to disable all included styling.', 'easy-digital-downloads' ),
						'desc'          => __( 'This includes buttons, checkout fields, product pages, and all other elements', 'easy-digital-downloads' ),
						'type'          => 'checkbox_description',
						'tooltip_title' => __( 'Disabling Styles', 'easy-digital-downloads' ),
						'tooltip_desc'  => __( "If your theme has a complete custom CSS file for Easy Digital Downloads, you may wish to disable our default styles. This is not recommended unless you're sure your theme has a complete custom CSS.", 'easy-digital-downloads' ),
					),
					'item_quantities' => array(
						'id'   => 'item_quantities',
						'name' => __( 'Cart Item Quantities', 'easy-digital-downloads' ),
						'desc' => sprintf( __( 'Allow quantities to be adjusted when adding %s to the cart, and while viewing the checkout cart.', 'easy-digital-downloads' ), edd_get_label_plural( true ) ),
						'type' => 'checkbox',
					),
					'uninstall_on_delete' => array(
						'id'   => 'uninstall_on_delete',
						'name' => __( 'Remove Data on Uninstall', 'easy-digital-downloads' ),
						'desc' => __( 'Check this box if you would like EDD to completely remove all of its data when the plugin is deleted.', 'easy-digital-downloads' ),
						'type' => 'checkbox',
					),
				),
				'button_text' => array(
					'button_style'   => array(
						'id'      => 'button_style',
						'name'    => __( 'Default Button Style', 'easy-digital-downloads' ),
						'desc'    => __( 'Choose the style you want to use for the buttons.', 'easy-digital-downloads' ),
						'type'    => 'select',
						'options' => edd_get_button_styles(),
					),
					'checkout_color' => array(
						'id'      => 'checkout_color',
						'name'    => __( 'Default Button Color', 'easy-digital-downloads' ),
						'desc'    => __( 'Choose the color you want to use for the buttons.', 'easy-digital-downloads' ),
						'type'    => 'color_select',
						'options' => edd_get_button_colors(),
						'std'     => 'blue'
					),
					'checkout_label' => array(
						'id'   => 'checkout_label',
						'name' => __( 'Complete Purchase Text', 'easy-digital-downloads' ),
						'desc' => __( 'The button label for completing a purchase.', 'easy-digital-downloads' ),
						'type' => 'text',
						'std'  => __( 'Purchase', 'easy-digital-downloads' ),
					),
					'free_checkout_label' => array(
						'id'   => 'free_checkout_label',
						'name' => __( 'Complete Free Purchase Text', 'easy-digital-downloads' ),
						'desc' => __( 'The button label for completing a free purchase.', 'easy-digital-downloads' ),
						'type' => 'text',
						'std'  => __( 'Free Download', 'easy-digital-downloads' ),
					),
					'add_to_cart_text' => array(
						'id'   => 'add_to_cart_text',
						'name' => __( 'Add to Cart Text', 'easy-digital-downloads' ),
						'desc' => __( 'Text shown on the Add to Cart Buttons.', 'easy-digital-downloads' ),
						'type' => 'text',
						'std'  => __( 'Add to Cart', 'easy-digital-downloads' ),
					),
					'checkout_button_text' => array(
						'id'   => 'checkout_button_text',
						'name' => __( 'Checkout Button Text', 'easy-digital-downloads' ),
						'desc' => __( 'Text shown on the Add to Cart Button when the product is already in the cart.', 'easy-digital-downloads' ),
						'type' => 'text',
						'std'  => _x( 'Checkout', 'text shown on the Add to Cart Button when the product is already in the cart', 'easy-digital-downloads' ),
					),
					'buy_now_text' => array(
						'id'   => 'buy_now_text',
						'name' => __( 'Buy Now Text', 'easy-digital-downloads' ),
						'desc' => __( 'Text shown on the Buy Now Buttons.', 'easy-digital-downloads' ),
						'type' => 'text',
						'std'  => __( 'Buy Now', 'easy-digital-downloads' ),
					),
				),
				'file_downloads' => array(
					'download_method' => array(
						'id'            => 'download_method',
						'name'          => __( 'Download Method', 'easy-digital-downloads' ),
						'desc'          => sprintf( __( 'Select the file download method. Note, not all methods work on all servers.', 'easy-digital-downloads' ), edd_get_label_singular() ),
						'type'          => 'select',
						'tooltip_title' => __( 'Download Method', 'easy-digital-downloads' ),
						'tooltip_desc'  => __( 'Due to its consistency in multiple platforms and better file protection, \'forced\' is the default method. Because Easy Digital Downloads uses PHP to process the file with the \'forced\' method, larger files can cause problems with delivery, resulting in hitting the \'max execution time\' of the server. If users are getting 404 or 403 errors when trying to access their purchased files when using the \'forced\' method, changing to the \'redirect\' method can help resolve this.', 'easy-digital-downloads' ),
						'options'       => array(
							'direct'   => __( 'Forced', 'easy-digital-downloads' ),
							'redirect' => __( 'Redirect', 'easy-digital-downloads' ),
						),
					),
					'symlink_file_downloads' => array(
						'id'   => 'symlink_file_downloads',
						'name' => __( 'Symbolically Link Files', 'easy-digital-downloads' ),
						'desc' => __( 'Check this if you are delivering really large files or having problems with file downloads completing.', 'easy-digital-downloads' ),
						'type' => 'checkbox',
					),
					'file_download_limit' => array(
						'id'            => 'file_download_limit',
						'name'          => __( 'File Download Limit', 'easy-digital-downloads' ),
						'desc'          => sprintf( __( 'The maximum number of times files can be downloaded for purchases. Can be overwritten for each %s.', 'easy-digital-downloads' ), edd_get_label_singular() ),
						'type'          => 'number',
						'size'          => 'small',
						'tooltip_title' => __( 'File Download Limits', 'easy-digital-downloads' ),
						'tooltip_desc'  => sprintf( __( 'Set the global default for the number of times a customer can download items they purchase. Using a value of 0 is unlimited. This can be defined on a %s-specific level as well. Download limits can also be reset for an individual purchase.', 'easy-digital-downloads' ), edd_get_label_singular( true ) ),
					),
					'download_link_expiration' => array(
						'id'            => 'download_link_expiration',
						'name'          => __( 'Download Link Expiration', 'easy-digital-downloads' ),
						'desc'          => __( 'How long should download links be valid for? Default is 24 hours from the time they are generated. Enter a time in hours.', 'easy-digital-downloads' ),
						'tooltip_title' => __( 'Download Link Expiration', 'easy-digital-downloads' ),
						'tooltip_desc'  => __( 'When a customer receives a link to their downloads via email, in their receipt, or in their purchase history, the link will only be valid for the timeframe (in hours) defined in this setting. Sending a new purchase receipt or visiting the account page will re-generate a valid link for the customer.', 'easy-digital-downloads' ),
						'type'          => 'number',
						'size'          => 'small',
						'std'           => '24',
						'min'           => '0',
					),
					'disable_redownload' => array(
						'id'   => 'disable_redownload',
						'name' => __( 'Disable Redownload', 'easy-digital-downloads' ),
						'desc' => __( 'Check this if you do not want to allow users to redownload items from their purchase history.', 'easy-digital-downloads' ),
						'type' => 'checkbox',
					),
				),
			) ),

			// Privacy Settings
			'privacy' => apply_filters( 'edd_settings_privacy', array(
				'main' => array(
					'' => array(
						'id'            => 'privacy_settings',
						'name'          => '<h3>' . __( 'Privacy Policy', 'easy-digital-downloads' ) . '</h3>',
						'desc'          => '',
						'type'          => 'header',
						'tooltip_title' => __( 'Privacy Policy Settings', 'easy-digital-downloads' ),
						'tooltip_desc'  => __( 'Depending on legal and regulatory requirements, it may be necessary for your site to show a checkbox for agreement to a privacy policy.','easy-digital-downloads' ),
					),
					'show_agree_to_privacy_policy' => array(
						'id'    => 'show_agree_to_privacy_policy',
						'name'  => __( 'Agreement', 'easy-digital-downloads' ),
						'check' => __( 'Check this box to show an "Agree to Privacy Policy" checkbox on checkout.', 'easy-digital-downloads' ),
						'desc'  => __( 'Customers must agree to your privacy policy before purchasing.', 'easy-digital-downloads' ),
						'type'  => 'checkbox_description',
					),
					'agree_privacy_label' => array(
						'id'          => 'privacy_agree_label',
						'name'        => __( 'Agreement Label', 'easy-digital-downloads' ),
						'desc'        => __( 'Label for the "Agree to Privacy Policy" checkbox.', 'easy-digital-downloads' ),
						'type'        => 'text',
						'placeholder' => __( 'I agree to the privacy policy', 'easy-digital-downloads' ),
						'size'        => 'regular',
					),
					'show_privacy_policy_on_checkout' => array(
						'id'    => 'show_privacy_policy_on_checkout',
						'name'  => __( 'Privacy Policy on Checkout',                     'easy-digital-downloads' ),
						'check' => __( 'Display the entire Privacy Policy at checkout.', 'easy-digital-downloads' ) . ' <a href="' . esc_url( admin_url( 'options-privacy.php' ) ) . '">' . __( 'Set your Privacy Policy here', 'easy-digital-downloads' ) .'</a>.',
						'desc' =>
							__( 'Display your Privacy Policy on checkout.', 'easy-digital-downloads' ) . ' <a href="' . esc_url( admin_url( 'options-privacy.php' ) ) . '">' . __( 'Set your Privacy Policy here', 'easy-digital-downloads' ) .'</a>.' .
							'<p>' . sprintf( __( 'Need help creating a Privacy Policy? We recommend %sTermageddon%s.', 'easy-digital-downloads' ), '<a href="https://termageddon.com/i/easy-digital-downloads-edd-termageddon-promotion/" target="_blank" rel="noopener noreferrer">', '</a>' ) . '</p>',
						'type'  => 'checkbox',
					),
				),
				'site_terms' => array(
					'' => array(
						'id'            => 'terms_settings',
						'name'          => '<h3>' . __( 'Terms & Agreements', 'easy-digital-downloads' ) . '</h3>',
						'desc'          => '',
						'type'          => 'header',
						'tooltip_title' => __( 'Terms & Agreements Settings', 'easy-digital-downloads' ),
						'tooltip_desc'  => __( 'Depending on legal and regulatory requirements, it may be necessary for your site to show checkbox for agreement to terms.','easy-digital-downloads' ),
					),
					'show_agree_to_terms' => array(
						'id'    => 'show_agree_to_terms',
						'name'  => __( 'Agreement', 'easy-digital-downloads' ),
						'check' => __( 'Check this box to show an "Agree to Terms" checkbox on checkout.', 'easy-digital-downloads' ),
						'desc' =>
							__( 'Check this to show an agree to terms on checkout that users must agree to before purchasing.', 'easy-digital-downloads' ) .
							'<p>' .
							sprintf(
								__( 'Need help creating a Terms of Agreement? We recommend using %sTermageddon%s.', 'easy-digital-downloads' ),
								'<a href="https://termageddon.com/i/easy-digital-downloads-edd-termageddon-promotion/" target="_blank" rel="noopener noreferrer">',
								'</a>'
							) .
							'</p>',
						'type'  => 'checkbox_description',
					),
					'agree_label' => array(
						'id'          => 'agree_label',
						'name'        => __( 'Agreement Label', 'easy-digital-downloads' ),
						'desc'        => __( 'Label for the "Agree to Terms" checkbox.', 'easy-digital-downloads' ),
						'placeholder' => __( 'I agree to the terms', 'easy-digital-downloads' ),
						'type'        => 'text',
						'size'        => 'regular',
					),
					'agree_text' => array(
						'id'   => 'agree_text',
						'name' => __( 'Agreement Text', 'easy-digital-downloads' ),
						'type' => 'rich_editor',
					),
				),
				'export_erase' => array(
					array(
						'id'            => 'payment_privacy_status_action_header',
						'name'          => '<h3>' . __( 'Order Statuses', 'easy-digital-downloads' ) . '</h3>',
						'type'          => 'header',
						'desc'          => __( 'When a user requests to be anonymized or removed from a site, these are the actions that will be taken on payments associated with their customer, by status.','easy-digital-downloads' ),
						'tooltip_title' => __( 'What settings should I use?', 'easy-digital-downloads' ),
						'tooltip_desc'  => __( 'By default, Easy Digital Downloads sets suggested actions based on the Payment Status. These are purely recommendations, and you may need to change them to suit your store\'s needs. If you are unsure, you can safely leave these settings as is.','easy-digital-downloads' ),
					),
					array(
						'id'   => 'payment_privacy_status_action_text',
						'name' => __( 'Rules', 'easy-digital-downloads' ),
						'type' => 'descriptive_text',
						'desc' => __( 'When a user wants their order history anonymized or removed, the following rules will be used:','easy-digital-downloads' ),
					)
				)
			) )
		);

		// Add Privacy settings for statuses
		foreach ( $payment_statuses as $status => $label ) {
			switch ( $status ) {
				case 'complete':
				case 'refunded':
				case 'revoked':
					$action = 'anonymize';
					break;

				case 'failed':
				case 'abandoned':
					$action = 'delete';
					break;

				case 'pending':
				case 'processing':
				default:
					$action = 'none';
					break;
			}

			$edd_settings['privacy']['export_erase'][] = array(
				'id'      => 'payment_privacy_status_action_' . $status,
				'name'    => $label,
				'desc'    => '',
				'type'    => 'select',
				'std'     => $action,
				'options' => $options,
			);
		}

		if ( ! edd_shop_supports_buy_now() ) {
			$edd_settings['misc']['button_text']['buy_now_text']['disabled']      = true;
			$edd_settings['misc']['button_text']['buy_now_text']['tooltip_title'] = __( 'Buy Now Disabled', 'easy-digital-downloads' );
			$edd_settings['misc']['button_text']['buy_now_text']['tooltip_desc']  = __( 'Buy Now buttons are only available for stores that have a single supported gateway active and that do not use taxes.', 'easy-digital-downloads' );
		}

		// Show a disabled "Default Rate" in "Tax Rates" if the value is not 0.
		if ( false !== edd_get_option( 'tax_rate' ) ) {
			$edd_settings['taxes']['rates'] = array_merge(
				array(
					'tax_rate' => array(
						'id'            => 'tax_rate',
						'type'          => 'tax_rate',
						'name'          => __( 'Default Rate', 'easy-digital-downloads' ),
						'desc'          => (
							'<div class="notice inline notice-error"><p>' . __( 'This setting is no longer used in this version of Easy Digital Downloads. We have migrated any fallback tax rates for you to verify below. Click "Save Changes" to dismiss this notice.', 'easy-digital-downloads' ) . '</p></div>'
						),
					),
				),
				$edd_settings['taxes']['rates']
			);
		}

		// Allow registered settings to surface the deprecated "Styles" tab.
		if ( has_filter( 'edd_settings_styles' ) ) {
			$edd_settings['styles'] = edd_apply_filters_deprecated(
				'edd_settings_styles',
				array(
					array(
						'main'    => array(),
						'buttons' => array(),
					),
				),
				'3.0',
				'edd_settings_misc'
			);
		}
	}

	// Filter & return
	return apply_filters( 'edd_registered_settings', $edd_settings );
}

/**
 * Settings Sanitization
 *
 * Adds a settings error (for the updated message)
 * At some point this will validate input
 *
 * @since 1.0.8.2
 *
 * @param array  $input       The value inputted in the field
 *
 * @global array $edd_options Array of all the EDD Options
 *
 * @return array $input Sanitized value
 */
function edd_settings_sanitize( $input = array() ) {
	global $edd_options;

	// Default values
	$referrer      = '';
	$setting_types = edd_get_registered_settings_types();
	$doing_section = ! empty( $_POST['_wp_http_referer'] );
	$input         = ! empty( $input )
		? $input
		: array();

	if ( true === $doing_section ) {

		// Pull out the tab and section
		parse_str( $_POST['_wp_http_referer'], $referrer );
		$tab     = ! empty( $referrer['tab']     ) ? sanitize_key( $referrer['tab']     ) : 'general';
		$section = ! empty( $referrer['section'] ) ? sanitize_key( $referrer['section'] ) : 'main';

		// Maybe override the tab section
		if ( ! empty( $_POST['edd_section_override'] ) ) {
			$section = sanitize_text_field( $_POST['edd_section_override'] );
		}

		// Get setting types for this section
		$setting_types = edd_get_registered_settings_types( $tab, $section );

		// Run a general sanitization for the tab for special fields (like taxes)
		$input = apply_filters( 'edd_settings_' . $tab . '_sanitize', $input );

		// Run a general sanitization for the section so custom tabs with sub-sections can save special data
		$input = apply_filters( 'edd_settings_' . $tab . '-' . $section . '_sanitize', $input );
	}

	// Remove non setting types and merge settings together
	$non_setting_types = edd_get_non_setting_types();
	$setting_types     = array_diff( $setting_types, $non_setting_types );
	$output            = array_merge( $edd_options, $input );

	// Loop through settings, and apply any filters
	foreach ( $setting_types as $key => $type ) {

		// Skip if type is empty
		if ( empty( $type ) ) {
			continue;
		}

		if ( array_key_exists( $key, $output ) ) {
			$output[ $key ] = apply_filters( 'edd_settings_sanitize_' . $type, $output[ $key ], $key );
			$output[ $key ] = apply_filters( 'edd_settings_sanitize', $output[ $key ], $key );
		}

		if ( true === $doing_section ) {
			switch ( $type ) {
				case 'checkbox':
				case 'checkbox_description':
				case 'gateways':
				case 'multicheck':
				case 'payment_icons':
					if ( array_key_exists( $key, $input ) && $output[ $key ] === '-1' ) {
						unset( $output[ $key ] );
					}
					break;
				case 'text':
					if ( array_key_exists( $key, $input ) && empty( $input[ $key ] ) ) {
						unset( $output[ $key ] );
					}
					break;
				case 'number':
					if ( array_key_exists( $key, $output ) && ! array_key_exists( $key, $input ) ) {
						unset( $output[ $key ] );
					}

					$setting_details = edd_get_registered_setting_details( $tab, $section, $key );
					$number_type     = ! empty( $setting_details['step'] ) && false !== strpos( $setting_details['step'], '.' ) ? 'floatval' : 'intval';
					$minimum         = isset( $setting_details['min'] ) ? $number_type( $setting_details['min'] ) : false;
					$maximum         = isset( $setting_details['max'] ) ? $number_type( $setting_details['max'] ) : false;
					$new_value       = $number_type( $input[ $key ] );

					if ( ( false !== $minimum && $minimum > $new_value ) || ( false !== $maximum && $maximum < $new_value ) ) {
						unset( $output[ $key ] );
					}
					break;
				default:
					if ( array_key_exists( $key, $input ) && empty( $input[ $key ] ) || ( array_key_exists( $key, $output ) && ! array_key_exists( $key, $input ) ) ) {
						unset( $output[ $key ] );
					}
					break;
			}
		} elseif ( empty( $input[ $key ] ) ) {
			unset( $output[ $key ] );
		}
	}

	// Return output.
	return (array) $output;
}

/**
 * Flattens the set of registered settings and their type so we can easily sanitize all the settings
 * in a much cleaner set of logic in edd_settings_sanitize
 *
 * @since  2.6.5
 * @since  2.8 - Added the ability to filter setting types by tab and section
 *
 * @param $filtered_tab     bool|string     A tab to filter setting types by.
 * @param $filtered_section bool|string A section to filter setting types by.
 *
 * @return array Key is the setting ID, value is the type of setting it is registered as
 */
function edd_get_registered_settings_types( $filtered_tab = false, $filtered_section = false ) {
	$settings      = edd_get_registered_settings();
	$setting_types = array();

	foreach ( $settings as $tab_id => $tab ) {

		if ( false !== $filtered_tab && $filtered_tab !== $tab_id ) {
			continue;
		}

		foreach ( $tab as $section_id => $section_or_setting ) {

			// See if we have a setting registered at the tab level for backwards compatibility
			if ( false !== $filtered_section && is_array( $section_or_setting ) && array_key_exists( 'type', $section_or_setting ) ) {
				$setting_types[ $section_or_setting['id'] ] = $section_or_setting['type'];
				continue;
			}

			if ( false !== $filtered_section && $filtered_section !== $section_id ) {
				continue;
			}

			foreach ( $section_or_setting as $section_settings ) {
				if ( ! empty( $section_settings['type'] ) ) {
					$setting_types[ $section_settings['id'] ] = $section_settings['type'];
				}
			}
		}
	}

	return $setting_types;
}

/**
 * Allow getting a specific setting's details.
 *
 * @since 3.0
 *
 * @param string $filtered_tab      The tab the setting's section is in.
 * @param string $filtered_section  The section the setting is located in.
 * @param string $setting_key       The key associated with the setting.
 *
 * @return array
 */
function edd_get_registered_setting_details( $filtered_tab = '', $filtered_section = '', $setting_key = '' ) {
	$settings        = edd_get_registered_settings();
	$setting_details = array();

	if ( isset( $settings[ $filtered_tab ][ $filtered_section][ $setting_key ] ) ) {
		$setting_details = $settings[ $filtered_tab ][ $filtered_section][ $setting_key ];
	}

	return $setting_details;
}

/**
 * Return array of settings field types that aren't settings.
 *
 * @since 3.0
 *
 * @return array
 */
function edd_get_non_setting_types() {
	return apply_filters( 'edd_non_setting_types', array(
		'header',
		'descriptive_text',
		'hook',
	) );
}

/**
 * Misc File Download Settings Sanitization
 *
 * @since 2.5
 *
 * @param array $input The value inputted in the field
 *
 * @return string $input Sanitized value
 */
function edd_settings_sanitize_misc_file_downloads( $input ) {

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return $input;
	}

	if ( edd_get_file_download_method() != $input['download_method'] || ! edd_htaccess_exists() ) {
		// Force the .htaccess files to be updated if the Download method was changed.
		edd_create_protection_files( true, $input['download_method'] );
	}

	return $input;
}

add_filter( 'edd_settings_misc-file_downloads_sanitize', 'edd_settings_sanitize_misc_file_downloads' );

/**
 * Misc Accounting Settings Sanitization
 *
 * @since 2.5
 *
 * @param array $input The value inputted in the field
 *
 * @return array $input Sanitized value
 */
function edd_settings_sanitize_misc_accounting( $input ) {

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return $input;
	}

	if ( ! empty( $input['enable_sequential'] ) && ! edd_get_option( 'enable_sequential' ) ) {

		// Shows an admin notice about upgrading previous order numbers
		update_option( 'edd_upgrade_sequential', time() );

	}

	return $input;
}

add_filter( 'edd_settings_gateways-accounting_sanitize', 'edd_settings_sanitize_misc_accounting' );

/**
 * Taxes Settings Sanitization
 *
 * Adds a settings error (for the updated message)
 * This also saves the tax rates table
 *
 * @since 1.6
 *
 * @param array $input The value inputted in the field
 *
 * @return array $input Sanitized value.
 */
function edd_settings_sanitize_taxes( $input ) {

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return $input;
	}

	if ( ! isset( $_POST['tax_rates'] ) ) {
		return $input;
	}

	$tax_rates = ! empty( $_POST['tax_rates'] )
		? $_POST['tax_rates']
		: array();

	foreach ( $tax_rates as $tax_rate ) {

		$scope = isset( $tax_rate['global'] )
			? 'country'
			: 'region';

		$region = isset( $tax_rate['state'] )
			? sanitize_text_field( $tax_rate['state'] )
			: '';

		$name = '*' === $tax_rate['country']
			? ''
			: sanitize_text_field( $tax_rate['country'] );

		if ( empty( $name ) ) {
			$scope  = 'global';
		}

		$adjustment_data = array(
			'name'        => $name,
			'type'        => 'tax_rate',
			'scope'       => $scope,
			'amount_type' => 'percent',
			'amount'      => floatval( $tax_rate['rate'] ),
			'description' => $region,
		);

		if ( ( empty( $adjustment_data['name'] ) && 'global' !== $adjustment_data['scope'] ) || $adjustment_data['amount'] <= 0 ) {
			continue;
		}

		$existing_adjustment = edd_get_adjustments( $adjustment_data );

		if ( ! empty( $existing_adjustment ) ) {
			$adjustment                = $existing_adjustment[0];
			$adjustment_data['status'] = sanitize_text_field( $tax_rate['status'] );

			edd_update_adjustment( $adjustment->id, $adjustment_data );
		} else {
			$adjustment_data['status'] = 'active';

			edd_add_tax_rate( $adjustment_data );
		}
	}

	return $input;
}
add_filter( 'edd_settings_taxes_sanitize', 'edd_settings_sanitize_taxes' );

/**
 * Payment Gateways Settings Sanitization
 *
 * @since 2.7
 *
 * @param array $input The value inputted in the field
 *
 * @return string $input Sanitized value
 */
function edd_settings_sanitize_gateways( $input = array() ) {

	// Bail if user cannot manage shop settings
	if ( ! current_user_can( 'manage_shop_settings' ) || empty( $input['default_gateway'] ) ) {
		return $input;
	}

	// Unset the default gateway if there are no `gateways` enabled
	if ( empty( $input['gateways'] ) || '-1' == $input['gateways'] ) {
		unset( $input['default_gateway'] );

	// Current gateway is no longer enabled, so
	} elseif ( ! array_key_exists( $input['default_gateway'], $input['gateways'] ) ) {
		$enabled_gateways = $input['gateways'];

		reset( $enabled_gateways );

		$first_gateway = key( $enabled_gateways );

		if ( $first_gateway ) {
			$input['default_gateway'] = $first_gateway;
		}
	}

	return $input;
}
add_filter( 'edd_settings_gateways_sanitize', 'edd_settings_sanitize_gateways' );

/**
 * Sanitize text fields
 *
 * @since 1.8
 *
 * @param array $input The field value
 *
 * @return string $input Sanitized value
 */
function edd_sanitize_text_field( $input = '' ) {
	$allowed_tags = edd_get_allowed_tags();

	return trim( wp_kses( $input, $allowed_tags ) );
}

add_filter( 'edd_settings_sanitize_text', 'edd_sanitize_text_field' );

/**
 * Sanitize HTML Class Names
 *
 * @since 2.6.11
 *
 * @param  string|array $class HTML Class Name(s)
 *
 * @return string $class
 */
function edd_sanitize_html_class( $class = '' ) {

	if ( is_string( $class ) ) {
		$class = sanitize_html_class( $class );
	} else if ( is_array( $class ) ) {
		$class = array_values( array_map( 'sanitize_html_class', $class ) );
		$class = implode( ' ', array_unique( $class ) );
	}

	return $class;
}


/**
 * Sanitizes banned emails.
 *
 * @since 3.0
 */
function edd_sanitize_banned_emails( $input ) {

	$emails = '';
	if ( ! empty( $input['banned_emails'] ) ) {
		// Sanitize the input
		$emails = array_map( 'trim', explode( "\n", $input['banned_emails'] ) );
		$emails = array_unique( $emails );
		$emails = array_map( 'sanitize_text_field', $emails );

		foreach ( $emails as $id => $email ) {
			if ( ! is_email( $email ) && $email[0] != '@' && $email[0] != '.' ) {
				unset( $emails[ $id ] );
			}
		}
	}
	$input['banned_emails'] = $emails;

	return $input;
}
add_filter( 'edd_settings_gateways-checkout_sanitize', 'edd_sanitize_banned_emails' );

/**
 * Retrieve settings tabs
 *
 * @since 1.8
 * @since 2.11.4 Any tabs with no registered settings are filtered out in `edd_options_page`.
 *
 * @return array $tabs
 */
function edd_get_settings_tabs() {
	return apply_filters( 'edd_settings_tabs', array(
		'general'    => __( 'General', 'easy-digital-downloads' ),
		'gateways'   => __( 'Payments', 'easy-digital-downloads' ),
		'emails'     => __( 'Emails', 'easy-digital-downloads' ),
		'marketing'  => __( 'Marketing', 'easy-digital-downloads' ),
		'styles'     => __( 'Styles', 'easy-digital-downloads' ),
		'taxes'      => __( 'Taxes', 'easy-digital-downloads' ),
		'privacy'    => __( 'Policies', 'easy-digital-downloads' ),
		'extensions' => __( 'Extensions', 'easy-digital-downloads' ),
		'licenses'   => __( 'Licenses', 'easy-digital-downloads' ),
		'misc'       => __( 'Misc', 'easy-digital-downloads' ),
	) );
}

/**
 * Retrieve settings tabs
 *
 * @since 2.5
 * @return array $section
 */
function edd_get_settings_tab_sections( $tab = false ) {
	$tabs     = array();
	$sections = edd_get_registered_settings_sections();

	if ( $tab && ! empty( $sections[ $tab ] ) ) {
		$tabs = $sections[ $tab ];
	} else if ( $tab ) {
		$tabs = array();
	}

	return $tabs;
}

/**
 * Get the settings sections for each tab
 * Uses a static to avoid running the filters on every request to this function
 *
 * @since  2.5
 * @return array Array of tabs and sections
 */
function edd_get_registered_settings_sections() {
	static $sections = null;

	if ( null === $sections ) {
		$sections = array(
			'general'    => apply_filters( 'edd_settings_sections_general', array(
				'main'               => __( 'Store',      'easy-digital-downloads' ),
				'currency'           => __( 'Currency',   'easy-digital-downloads' ),
				'pages'              => __( 'Pages',      'easy-digital-downloads' ),
				'api'                => __( 'API',        'easy-digital-downloads' ),
				'tracking'           => __( 'Tracking',   'easy-digital-downloads' )
			) ),
			'gateways'   => apply_filters( 'edd_settings_sections_gateways', array(
				'main'               => __( 'General',         'easy-digital-downloads' ),
				'checkout'           => __( 'Checkout',        'easy-digital-downloads' ),
				'refunds'            => __( 'Refunds',         'easy-digital-downloads' ),
				'accounting'         => __( 'Accounting',      'easy-digital-downloads' ),
			) ),
			'emails'     => apply_filters( 'edd_settings_sections_emails', array(
				'main'               => __( 'General',            'easy-digital-downloads' ),
				'purchase_receipts'  => __( 'Purchase Receipts',  'easy-digital-downloads' ),
				'sale_notifications' => __( 'Sale Notifications', 'easy-digital-downloads' )
			) ),
			'marketing'  => apply_filters( 'edd_settings_sections_marketing', array(
				'main' => __( 'General', 'easy-digital-downloads' ),
			) ),
			'styles'     => apply_filters( 'edd_settings_sections_styles', array(
				'main'               => __( 'General', 'easy-digital-downloads' ),
				'buttons'            => __( 'Buttons', 'easy-digital-downloads' )
			) ),
			'taxes'      => apply_filters( 'edd_settings_sections_taxes', array(
				'main'               => __( 'General', 'easy-digital-downloads' ),
				'rates'              => __( 'Rates',   'easy-digital-downloads' ),
			) ),
			'privacy'    => apply_filters( 'edd_settings_section_privacy', array(
				'main'               => __( 'Privacy Policy',     'easy-digital-downloads' ),
				'site_terms'         => __( 'Terms & Agreements', 'easy-digital-downloads' ),
				'export_erase'       => __( 'Export & Erase',     'easy-digital-downloads' )
			) ),
			'extensions' => apply_filters( 'edd_settings_sections_extensions', array(
				'main'               => __( 'Main', 'easy-digital-downloads' )
			) ),
			'licenses'   => apply_filters( 'edd_settings_sections_licenses', array() ),
			'misc'       => apply_filters( 'edd_settings_sections_misc', array(
				'main'               => __( 'General',            'easy-digital-downloads' ),
				'button_text'        => __( 'Purchase Buttons',   'easy-digital-downloads' ),
				'file_downloads'     => __( 'File Downloads',     'easy-digital-downloads' ),
			) )
		);
	}

	// Filter & return
	return apply_filters( 'edd_settings_sections', $sections );
}

/**
 * Retrieve a list of all published pages
 *
 * On large sites this can be expensive, so only load if on the settings page or $force is set to true
 *
 * @since 1.9.5
 *
 * @param bool $force Force the pages to be loaded even if not on settings
 *
 * @return array $pages_options An array of the pages
 */
function edd_get_pages( $force = false ) {

	$pages_options = array( '' => '' ); // Blank option

	if ( ( ! isset( $_GET['page'] ) || 'edd-settings' != $_GET['page'] ) && ! $force ) {
		return $pages_options;
	}

	$pages = get_pages();
	if ( $pages ) {
		foreach ( $pages as $page ) {
			$pages_options[ $page->ID ] = $page->post_title;
		}
	}

	return $pages_options;
}

/**
 * Header Callback
 *
 * Renders the header.
 *
 * @since 1.0
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_header_callback( $args ) {
	echo apply_filters( 'edd_after_setting_output', '', $args );
}

/**
 * Checkbox Callback
 *
 * Renders checkboxes.
 *
 * @since 1.0
 * @since 3.0 Updated to use `EDD_HTML_Elements`.
 *
 * @param array $args Arguments passed by the setting.
 */
function edd_checkbox_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$name = '';
	} else {
		$name = 'edd_settings[' . edd_sanitize_key( $args['id'] ) . ']';
	}

	$class = edd_sanitize_html_class( $args['field_class'] );

	$args['name']    = $name;
	$args['class']   = $class;
	$args['current'] = ! empty( $edd_option )
		? $edd_option
		: '';
	$args['label']   = wp_kses_post( $args['desc'] );
	$args['value']   = 1;

	$html    = '<input type="hidden" name="' . $name . '" value="-1" />';
	$html   .= '<div class="edd-check-wrapper">';
	$html   .= EDD()->html->checkbox( $args );
	$html   .= '</div>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Checkbox with description Callback
 *
 * Renders checkboxes with a description.
 *
 * @since 3.0
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_checkbox_description_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$name = '';
	} else {
		$name = 'edd_settings[' . edd_sanitize_key( $args['id'] ) . ']';
	}

	$args['name']    = $name;
	$args['class']   = edd_sanitize_html_class( $args['field_class'] );
	$args['current'] = ! empty( $edd_option ) ? $edd_option : '';
	$args['label']   = false;
	$args['value']   = 1;

	$html  = '<input type="hidden" name="' . esc_attr( $name ) . '" value="-1" />';
	$html .= '<div class="edd-check-wrapper">';
	$html .= EDD()->html->checkbox( $args );
	$html .= '<label for="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']"> ' . wp_kses_post( $args['check'] ) . '</label>';
	$html .= '</div>';
	if ( ! empty( $args['desc'] ) ) {
		$html .= '<p class="description">' . wp_kses_post( $args['desc'] ) . '</p>';
	}

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Multicheck Callback
 *
 * Renders multiple checkboxes.
 *
 * @since 1.0
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_multicheck_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	$class = edd_sanitize_html_class( $args['field_class'] );

	$html = '';
	if ( ! empty( $args['options'] ) ) {
		$html .= '<input type="hidden" name="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" value="-1" />';

		foreach ( $args['options'] as $key => $option ):
			if ( isset( $edd_option[ $key ] ) ) {
				$enabled = $option;
			} else {
				$enabled = null;
			}
			$html .= '<div class="edd-check-wrapper">';
			$html .= '<input name="edd_settings[' . edd_sanitize_key( $args['id'] ) . '][' . edd_sanitize_key( $key ) . ']" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . '][' . edd_sanitize_key( $key ) . ']" class="' . $class . '" type="checkbox" value="' . esc_attr( $option ) . '" ' . checked( $option, $enabled, false ) . '/>&nbsp;';
			$html .= '<label for="edd_settings[' . edd_sanitize_key( $args['id'] ) . '][' . edd_sanitize_key( $key ) . ']">' . wp_kses_post( $option ) . '</label>';
			$html .= '</div>';
		endforeach;
		$html .= '<p class="description">' . $args['desc'] . '</p>';
	}

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Payment method icons callback
 *
 * @since 2.1
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_payment_icons_callback( $args = array() ) {

	// Start an output buffer
	ob_start();

	$edd_option = edd_get_option( $args['id'] );
	$class      = edd_sanitize_html_class( $args['field_class'] ); ?>

	<input type="hidden" name="edd_settings[<?php echo edd_sanitize_key( $args['id'] ); ?>]" value="-1" />
	<input type="hidden" name="edd_settings[payment_icons_order]" class="edd-order" value="<?php echo edd_get_option( 'payment_icons_order' ); ?>" />

	<?php

	// Only go if options exist
	if ( ! empty( $args['options'] ) ) :
		$class = edd_sanitize_html_class( $args['field_class'] );

		// Everything is wrapped in a sortable UL
		?><ul id="edd-payment-icons-list" class="edd-sortable-list">

		<?php foreach ( $args['options'] as $key => $option ) :
			$enabled = isset( $edd_option[ $key ] )
				? $option
				: null; ?>

			<li class="edd-check-wrapper" data-key="<?php echo edd_sanitize_key( $key ); ?>">
				<label>
					<input name="edd_settings[<?php echo edd_sanitize_key( $args['id'] ); ?>][<?php echo edd_sanitize_key( $key ); ?>]" id="edd_settings[<?php echo edd_sanitize_key( $args['id'] ); ?>][<?php echo edd_sanitize_key( $key ); ?>]" class="<?php echo $class; ?>" type="checkbox" value="<?php echo esc_attr( $option ); ?>" <?php echo checked( $option, $enabled, false ); ?> />

				<?php if ( edd_string_is_image_url( $key ) ) : ?>
					<span class="payment-icon-image"><img class="payment-icon" src="<?php echo esc_url( $key ); ?>" /></span>
				<?php else :

					$type = '';
					$card = strtolower( str_replace( ' ', '', $option ) );

					if ( has_filter( 'edd_accepted_payment_' . $card . '_image' ) ) {
						$image = apply_filters( 'edd_accepted_payment_' . $card . '_image', '' );

					} elseif ( has_filter( 'edd_accepted_payment_' . $key . '_image' ) ) {
						$image = apply_filters( 'edd_accepted_payment_' . $key . '_image', '' );
					} else {
						// Set the type to SVG.
						$type = 'svg';

						// Get SVG dimensions.
						$dimensions = edd_get_payment_icon_dimensions( $key );

						// Get SVG markup.
						$image = edd_get_payment_icon( array(
							'icon'    => $key,
							'width'   => $dimensions['width'],
							'height'  => $dimensions['height'],
							'title'   => $option,
							'classes' => array( 'payment-icon' )
						) );
					}

					// SVG or IMG
					if ( 'svg' === $type ) : ?>

						<span class="payment-icon-image"><?php echo $image; // Contains trusted HTML ?></span>

					<?php else : ?>

						<span class="payment-icon-image"><img class="payment-icon" src="<?php echo esc_url( $image ); ?>"/></span>

					<?php endif; ?>

				<?php endif; ?>
					<span class="payment-option-name"><?php echo $option; ?></span>
				</label>
			</li>

		<?php endforeach; ?>

		</ul>

		<p class="description" style="margin-top:16px;"><?php echo wp_kses_post( $args['desc'] ); ?></p>

	<?php endif;

	// Get the contents of the current output buffer
	$html = ob_get_clean();

	// Filter & return
	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Enforce the payment icon order (from the sortable admin area UI)
 *
 * @since 3.0
 *
 * @param array $icons
 * @return array
 */
function edd_order_accepted_payment_icons( $icons = array() ) {

	// Get the order option
	$order = edd_get_option( 'payment_icons_order', '' );

	// If order is set, enforce it
	if ( ! empty( $order ) ) {
		$order = array_flip( explode( ',', $order ) );
		$order = array_intersect_key( $order, $icons );
		$icons = array_merge( $order, $icons );
	}

	// Return ordered icons
	return $icons;
}
add_filter( 'edd_accepted_payment_icons', 'edd_order_accepted_payment_icons', 99 );

/**
 * Radio Callback
 *
 * Renders radio boxes.
 *
 * @since 1.3.3
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_radio_callback( $args ) {
	$edd_options = edd_get_option( $args['id'] );

	$html = '';

	$class = edd_sanitize_html_class( $args['field_class'] );

	foreach ( $args['options'] as $key => $option ) :
		$checked = false;

		if ( $edd_options && $edd_options == $key ) {
			$checked = true;
		} elseif ( isset( $args['std'] ) && $args['std'] == $key && ! $edd_options ) {
			$checked = true;
		}

		$html .= '<div class="edd-check-wrapper">';
		$html .= '<input name="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . '][' . edd_sanitize_key( $key ) . ']" class="' . $class . '" type="radio" value="' . edd_sanitize_key( $key ) . '" ' . checked( true, $checked, false ) . '/>&nbsp;';
		$html .= '<label for="edd_settings[' . edd_sanitize_key( $args['id'] ) . '][' . edd_sanitize_key( $key ) . ']">' . esc_html( $option ) . '</label>';
		$html .= '</div>';
	endforeach;

	$html .= '<p class="description">' . apply_filters( 'edd_after_setting_output', wp_kses_post( $args['desc'] ), $args ) . '</p>';

	echo $html;
}

/**
 * Gateways Callback
 *
 * Renders gateways fields.
 *
 * @since 1.0
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_gateways_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	$html  = '<input type="hidden" name="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" value="-1" />';
	$html .= '<input type="hidden" name="edd_settings[gateways_order]" class="edd-order" value="' . edd_get_option( 'gateways_order' ) . '" />';

	if ( ! empty( $args['options'] ) ) {
		$class = edd_sanitize_html_class( $args['field_class'] );
		$html .= '<ul id="edd-payment-gateways" class="edd-sortable-list">';

		foreach ( $args['options'] as $key => $option ) {
			if ( isset( $edd_option[ $key ] ) ) {
				$enabled = '1';
			} else {
				$enabled = null;
			}

			$html .= '<li class="edd-check-wrapper" data-key="' . edd_sanitize_key( $key ) . '">';
			$html .= '<label>';
			$html .= '<input name="edd_settings[' . esc_attr( $args['id'] ) . '][' . edd_sanitize_key( $key ) . ']" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . '][' . edd_sanitize_key( $key ) . ']" class="' . $class . '" type="checkbox" value="1" data-gateway-key="' . edd_sanitize_key( $key ) . '" ' . checked( '1', $enabled, false ) . '/>&nbsp;';
			$html .= esc_html( $option['admin_label'] );
			if ( 'manual' === $key ) {
				$html .= '<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<strong>' . esc_html__( 'Store Gateway', 'easy-digital-downloads' ) . '</strong>: ' . esc_html__( 'This is an internal payment gateway which can be used for manually added orders or test purchases. No money is actually processed.', 'easy-digital-downloads' ) . '"></span>';
			}
			$html .= '</label>';
			$html .= '</li>';
		}

		$html .= '</ul>';

		$url_args = array(
			'utm_source'   => 'settings',
			'utm_medium'   => 'gateways',
			'utm_campaign' => 'admin',
		);

		$url   = add_query_arg( $url_args, 'https://easydigitaldownloads.com/downloads/category/extensions/gateways/' );
		$html .= '<p class="description">' . esc_html__( 'These gateways will be offered at checkout.', 'easy-digital-downloads' ) . '<br>' . sprintf( __( 'More <a href="%s">Payment Gateways</a> are available.', 'easy-digital-downloads' ), esc_url( $url ) ) . '</p>';
	}

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Gateways Callback (drop down)
 *
 * Renders gateways select menu
 *
 * @since 1.5
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_gateway_select_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	$class = edd_sanitize_html_class( $args['field_class'] );
	if ( isset( $args['chosen'] ) ) {
		$class .= ' edd-select-chosen';
		if ( is_rtl() ) {
			$class .= ' chosen-rtl';
		}
	}

	$html     = '<select name="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']"" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" class="' . $class . '">';
	$html    .= '<option value="">' . __( '&mdash; No gateway &mdash;', 'easy-digital-downloads' ) . '</option>';
	$gateways = edd_get_payment_gateways();

	foreach ( $gateways as $key => $option ) {
		$selected = isset( $edd_option )
			? selected( $key, $edd_option, false )
			: '';
		$disabled = disabled( edd_is_gateway_active( $key ), false, false );
		$html    .= '<option value="' . edd_sanitize_key( $key ) . '"' . $selected . ' ' . $disabled . '>' . esc_html( $option['admin_label'] ) . '</option>';
	}

	$html .= '</select>';
	$html .= '<p class="description"> ' . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Text Callback
 *
 * Renders text fields.
 *
 * @since 1.0
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_text_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	if ( $edd_option ) {
		$value = $edd_option;
	} elseif ( ! empty( $args['allow_blank'] ) && empty( $edd_option ) ) {
		$value = '';
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$args['readonly'] = true;
		$value            = isset( $args['std'] ) ? $args['std'] : '';
		$name             = '';
	} else {
		$name = 'name="edd_settings[' . esc_attr( $args['id'] ) . ']"';
	}

	$class = edd_sanitize_html_class( $args['field_class'] );

	$placeholder = ! empty( $args['placeholder'] )
		? ' placeholder="' . esc_attr( $args['placeholder'] ) . '"'
		: '';

	$disabled = ! empty( $args['disabled'] ) ? ' disabled="disabled"' : '';
	$readonly = $args['readonly'] === true   ? ' readonly="readonly"' : '';
	$size     = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html     = '<input type="text" class="' . $class . ' ' . sanitize_html_class( $size ) . '-text" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"' . $readonly . $disabled . $placeholder . ' />';
	$html    .= '<p class="description"> ' . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Email Callback
 *
 * Renders email fields.
 *
 * @since 2.8
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_email_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	if ( $edd_option ) {
		$value = $edd_option;
	} elseif ( ! empty( $args['allow_blank'] ) && empty( $edd_option ) ) {
		$value = '';
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$args['readonly'] = true;
		$value            = isset( $args['std'] ) ? $args['std'] : '';
		$name             = '';
	} else {
		$name = 'name="edd_settings[' . esc_attr( $args['id'] ) . ']"';
	}

	$class = edd_sanitize_html_class( $args['field_class'] );

	$placeholder = isset( $args['placeholder'] )
		? $args['placeholder']
		: '';

	$disabled = ! empty( $args['disabled'] ) ? ' disabled="disabled"' : '';
	$readonly = $args['readonly'] === true ? ' readonly="readonly"' : '';
	$size     = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html     = '<input type="email" class="' . $class . ' ' . sanitize_html_class( $size ) . '-text" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '" ' . $readonly . $disabled . ' placeholder="' . esc_attr( $placeholder ) . '" />';
	$html    .= '<p class="description"> ' . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Number Callback
 *
 * Renders number fields.
 *
 * @since 1.9
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_number_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	if ( is_numeric( $edd_option ) ) {
		$value = $edd_option;
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$args['readonly'] = true;
		$value            = isset( $args['std'] ) ? $args['std'] : '';
		$name             = '';
	} else {
		$name = 'name="edd_settings[' . esc_attr( $args['id'] ) . ']"';
	}

	$class = edd_sanitize_html_class( $args['field_class'] );

	$max  = isset( $args['max']  ) ? $args['max']  : 999999;
	$min  = isset( $args['min']  ) ? $args['min']  : 0;
	$step = isset( $args['step'] ) ? $args['step'] : 1;

	$disabled = ! empty( $args['disabled'] ) ? ' disabled="disabled"' : '';

	$size  = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html  = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . $class . ' ' . sanitize_html_class( $size ) . '-text" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"' . $disabled . ' />';
	$html .= '<p class="description"> ' . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Textarea Callback
 *
 * Renders textarea fields.
 *
 * @since 1.0
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_textarea_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	if ( $edd_option ) {
		if ( is_array( $edd_option ) ) {
			$value = implode( "\n", maybe_unserialize( $edd_option ) );
		} else {
			$value = $edd_option;
		}
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$class       = edd_sanitize_html_class( $args['field_class'] );
	$placeholder = ! empty( $args['placeholder'] )
		? ' placeholder="' . esc_attr( $args['placeholder'] ) . '"'
		: '';

	$html  = '<textarea class="' . $class . '" cols="50" rows="5" ' . $placeholder . ' id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" name="edd_settings[' . esc_attr( $args['id'] ) . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	$html .= '<p class="description"> ' . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Password Callback
 *
 * Renders password fields.
 *
 * @since 1.3
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_password_callback( $args ) {
	$edd_options = edd_get_option( $args['id'] );

	if ( $edd_options ) {
		$value = $edd_options;
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$class = edd_sanitize_html_class( $args['field_class'] );

	$size  = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html  = '<input type="password" class="' . $class . ' ' . sanitize_html_class( $size ) . '-text" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" name="edd_settings[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( $value ) . '"/>';
	$html .= '<p class="description"> ' . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Missing Callback
 *
 * If a function is missing for settings callbacks alert the user.
 *
 * @since 1.3.1
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_missing_callback( $args ) {
	printf(
		__( 'The callback function used for the %s setting is missing.', 'easy-digital-downloads' ),
		'<strong>' . $args['id'] . '</strong>'
	);
}

/**
 * Select Callback
 *
 * Renders select fields.
 *
 * @since 1.0
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_select_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	if ( $edd_option ) {
		$value = $edd_option;
	} else {

		// Properly set default fallback if the Select Field allows Multiple values
		if ( empty( $args['multiple'] ) ) {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		} else {
			$value = ! empty( $args['std'] ) ? $args['std'] : array();
		}

	}

	$placeholder = isset( $args['placeholder'] )
		? $args['placeholder']
		: '';

	$class = edd_sanitize_html_class( $args['field_class'] );

	if ( isset( $args['chosen'] ) ) {
		$class .= ' edd-select-chosen';
		if ( is_rtl() ) {
			$class .= ' chosen-rtl';
		}
	}

	// Nonce
	$nonce = isset( $args['data']['nonce'] )
		? ' data-nonce="' . sanitize_text_field( $args['data']['nonce'] ) . '"'
		: '';

	// If the Select Field allows Multiple values, save as an Array
	$name_attr = 'edd_settings[' . esc_attr( $args['id'] ) . ']';
	$name_attr = ( $args['multiple'] ) ? $name_attr . '[]' : $name_attr;

	$html = '<select ' . $nonce . ' id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" name="' . $name_attr . '" class="' . $class . '" data-placeholder="' . esc_html( $placeholder ) . '" ' . ( ( $args['multiple'] ) ? 'multiple="true"' : '' ) . '>';

	foreach ( $args['options'] as $option => $name ) {

		if ( ! $args['multiple'] ) {
			$selected = selected( $option, $value, false );
			$html    .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
		} else {
			// Do an in_array() check to output selected attribute for Multiple
			$html .= '<option value="' . esc_attr( $option ) . '" ' . ( ( in_array( $option, $value ) ) ? 'selected="true"' : '' ) . '>' . esc_html( $name ) . '</option>';
		}

	}

	$html .= '</select>';
	$html .= '<p class="description"> ' . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Color select Callback
 *
 * Renders color select fields.
 *
 * @since 1.8
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_color_select_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	if ( $edd_option ) {
		$value = $edd_option;
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$class = edd_sanitize_html_class( $args['field_class'] );
	if ( $args['chosen'] ) {
		$class .= 'edd-select-chosen';
		if ( is_rtl() ) {
			$class .= ' chosen-rtl';
		}
	}

	$html = '<select id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" class="' . $class . '" name="edd_settings[' . esc_attr( $args['id'] ) . ']"/>';

	foreach ( $args['options'] as $option => $color ) {
		$selected = selected( $option, $value, false );
		$html     .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( $color['label'] ) . '</option>';
	}

	$html .= '</select>';
	$html .= '<p class="description"> ' . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Rich Editor Callback
 *
 * Renders rich editor fields.
 *
 * @since 1.0
 *
 * @param array $args Arguments passed by the setting
 */
function edd_rich_editor_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	if ( $edd_option ) {
		$value = $edd_option;
	} else {
		if ( ! empty( $args['allow_blank'] ) && empty( $edd_option ) ) {
			$value = '';
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}
	}

	$rows = isset( $args['size'] ) ? $args['size'] : 20;

	$class = edd_sanitize_html_class( $args['field_class'] );

	ob_start();

	wp_editor( stripslashes( $value ), 'edd_settings_' . esc_attr( $args['id'] ), array(
		'textarea_name' => 'edd_settings[' . esc_attr( $args['id'] ) . ']',
		'textarea_rows' => absint( $rows ),
		'editor_class'  => $class,
	) );

	if ( ! empty( $args['desc'] ) ) {
		echo '<p class="edd-rich-editor-desc">' . wp_kses_post( $args['desc'] ) . '</p>';
	}

	$html = ob_get_clean();

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Upload Callback
 *
 * Renders upload fields.
 *
 * @since 1.0
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_upload_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	if ( $edd_option ) {
		$value = $edd_option;
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$class = edd_sanitize_html_class( $args['field_class'] );

	$size  = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html  = '<input type="text" class="' . sanitize_html_class( $size ) . '-text" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" class="' . $class . '" name="edd_settings[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<span>&nbsp;<input type="button" data-uploader-title="' . esc_html__( 'Attach File', 'easy-digital-downloads' ) . '" data-uploader-button-text="' . esc_html__( 'Attach', 'easy-digital-downloads' ) . '" class="edd_settings_upload_button button-secondary" value="' . __( 'Attach File', 'easy-digital-downloads' ) . '"/></span>';
	$html .= '<p class="description"> ' . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Color picker Callback
 *
 * Renders color picker fields.
 *
 * @since 1.6
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_color_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	if ( $edd_option ) {
		$value = $edd_option;
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$default = isset( $args['std'] ) ? $args['std'] : '';

	$class = edd_sanitize_html_class( $args['field_class'] );

	$html  = '<input type="text" class="' . $class . ' edd-color-picker" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" name="edd_settings[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( $value ) . '" data-default-color="' . esc_attr( $default ) . '" />';
	$html .= '<p class="description"> ' . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Shop States Callback
 *
 * Renders states drop down based on the currently selected country
 *
 * @since 1.6
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_shop_states_callback( $args ) {
	$edd_option  = edd_get_option( $args['id'] );
	$states      = edd_get_shop_states();
	$class       = edd_sanitize_html_class( $args['field_class'] );
	$placeholder = isset( $args['placeholder'] )
		? $args['placeholder']
		: '';

	if ( $args['chosen'] ) {
		$class .= ' edd-select-chosen';
		if ( is_rtl() ) {
			$class .= ' chosen-rtl';
		}
	}

	if ( empty( $states ) ) {
		$class .= ' edd-no-states';
	}

	$html = '<select id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" name="edd_settings[' . esc_attr( $args['id'] ) . ']" class="' . esc_attr( trim( $class ) ) . '" data-placeholder="' . esc_html( $placeholder ) . '">';

	foreach ( $states as $option => $name ) {
		$selected = isset( $edd_option ) ? selected( $option, $edd_option, false ) : '';
		$html     .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
	}

	$html .= '</select>';
	$html .= '<p class="description"> ' . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Outputs the "Default Rate" setting.
 *
 * @since 3.0
 *
 * @param array $args Arguments passed to the setting.
 */
function edd_tax_rate_callback( $args ) {
	echo '<input type="hidden" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" name="edd_settings[' . esc_attr( $args['id'] ) . ']" value="" />';
	echo wp_kses_post( $args['desc'] );
}

/**
 * Recapture Callback
 *
 * Renders Recapture Settings
 *
 * @since 2.10.2
 * @param array $args Arguments passed by the setting
 * @return void
 */
function edd_recapture_callback($args) {
	$client_connected = false;

	if ( class_exists( 'RecaptureEDD' ) ) {
		$client_connected = RecaptureEDD::is_ready();
	}

	ob_start();

	echo $args['desc'];

	// Output the appropriate button and label based on connection status
	if ( $client_connected ) :
		$connection_complete = get_option( 'recapture_api_key' );
		?>
		<div class="inline notice notice-<?php echo $connection_complete ? 'success' : 'warning'; ?>">
			<p>
				<?php _e( 'Recapture plugin activated.', 'easy-digital-downloads' ); ?>
				<?php printf( __( '%sAccess your Recapture account%s.', 'easy-digital-downloads' ), '<a href="https://recapture.io/account" target="_blank" rel="noopener noreferrer">', '</a>' ); ?>
			</p>

			<?php if ( $connection_complete ) : ?>
				<p>
					<a id="edd-recapture-disconnect" class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=recapture-confirm-disconnect' ) ); ?>"><?php esc_html_e( 'Disconnect Recapture', 'easy-digital-downloads' ); ?></a>
				</p>
			<?php else : ?>
				<p>
					<?php printf( __( '%sComplete your connection to Recapture%s', 'easy-digital-downloads' ), '<a href="' . esc_url( admin_url( 'admin.php?page=recapture' ) ) . '">', '</a>' ); ?>
				</p>
			<?php endif; ?>
		</div>
	<?php
	else :
		?>
		<p>
			<?php _e( 'We recommend Recapture for recovering lost revenue by automatically sending effective, targeted emails to customers who abandon their shopping cart.', 'easy-digital-downloads' ); ?> <?php printf( __( '%sLearn more%s (Free trial available)', 'easy-digital-downloads' ), '<a href="https://recapture.io/abandoned-carts-easy-digital-downloads" target="_blank" rel="noopener noreferrer">', '</a>' ); ?>
		</p>
		<?php if ( current_user_can( 'install_plugins' ) ) : ?>
		<p>
			<button type="button" id="edd-recapture-connect" class="button button-primary"><?php esc_html_e( 'Connect with Recapture', 'easy-digital-downloads' ); ?>
			</button>
		</p>
	<?php endif; ?>

	<?php
	endif;

	echo ob_get_clean();
}

/**
 * Tax Rates Callback
 *
 * Renders tax rates table
 *
 * @since 1.6
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_tax_rates_callback( $args ) {
	$rates = edd_get_tax_rates( array(), OBJECT );

	wp_enqueue_script( 'edd-admin-tax-rates' );
	wp_enqueue_style( 'edd-admin-tax-rates' );

	wp_localize_script( 'edd-admin-tax-rates', 'eddTaxRates', array(
		'rates' => $rates,
		'nonce' => wp_create_nonce( 'edd-country-field-nonce' ),
		'i18n'  => array(
			/* translators: Tax rate country code */
			'duplicateRate' => esc_html__( 'Duplicate tax rates are not allowed. Please deactivate the existing %s tax rate before adding or activating another.', 'easy-digital-downloads' ),
			'emptyCountry'  => esc_html__( 'Please select a country.', 'easy-digital-downloads' ),
			'emptyTax'      => esc_html__( 'Please enter a tax rate greater than 0.', 'easy-digital-downloads' ),
		),
	) );

	$templates = array(
		'meta',
		'row',
		'row-empty',
		'add',
		'bulk-actions'
	);

	echo '<p>' . $args['desc'] . '</p>';

	echo '<div id="edd-admin-tax-rates"></div>';

	foreach ( $templates as $tmpl ) {
?>

<script type="text/html" id="tmpl-edd-admin-tax-rates-table-<?php echo esc_attr( $tmpl ); ?>">
	<?php require_once EDD_PLUGIN_DIR . 'includes/admin/views/tmpl-tax-rates-table-' . $tmpl . '.php'; ?>
</script>

<?php
	}

}

/**
 * Descriptive text callback.
 *
 * Renders descriptive text onto the settings field.
 *
 * @since 2.1.3
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_descriptive_text_callback( $args ) {
	$html = wp_kses_post( $args['desc'] );

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Registers the license field callback for Software Licensing
 *
 * @since 1.5
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
if ( ! function_exists( 'edd_license_key_callback' ) ) {
	function edd_license_key_callback( $args ) {
		$edd_option = edd_get_option( $args['id'] );

		$messages = array();
		$license  = get_option( $args['options']['is_valid_license_option'] );

		if ( $edd_option ) {
			$value = $edd_option;
		} else {
			$value = isset( $args['std'] )
				? $args['std']
				: '';
		}

		if ( ! empty( $license ) && is_object( $license ) ) {
			$now        = current_time( 'timestamp' );
			$expiration = ! empty( $license->expires )
				? strtotime( $license->expires, $now )
				: false;

			// activate_license 'invalid' on anything other than valid, so if there was an error capture it
			if ( false === $license->success ) {

				switch ( $license->error ) {

					case 'expired' :
						$class      = 'expired';
						$messages[] = sprintf(
							__( 'Your license key expired on %s. Please <a href="%s" target="_blank">renew your license key</a>.', 'easy-digital-downloads' ),
							edd_date_i18n( $expiration ),
							'https://easydigitaldownloads.com/checkout/?edd_license_key=' . esc_attr( $value ) . '&utm_campaign=admin&utm_source=licenses&utm_medium=expired'
						);

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'revoked' :
						$class      = 'error';
						$messages[] = sprintf(
							__( 'Your license key has been disabled. Please <a href="%s" target="_blank">contact support</a> for more information.', 'easy-digital-downloads' ),
							'https://easydigitaldownloads.com/support?utm_campaign=admin&utm_source=licenses&utm_medium=revoked'
						);

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'missing' :
						$class      = 'error';
						$messages[] = sprintf(
							__( 'Invalid license. Please <a href="%s" target="_blank">visit your account page</a> and verify it.', 'easy-digital-downloads' ),
							'https://easydigitaldownloads.com/your-account?utm_campaign=admin&utm_source=licenses&utm_medium=missing'
						);

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'invalid' :
					case 'site_inactive' :
						$class      = 'error';
						$messages[] = sprintf(
							__( 'Your %s is not active for this URL. Please <a href="%s" target="_blank">visit your account page</a> to manage your license key URLs.', 'easy-digital-downloads' ),
							esc_html( $args['name'] ),
							'https://easydigitaldownloads.com/your-account?utm_campaign=admin&utm_source=licenses&utm_medium=invalid'
						);

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'item_name_mismatch' :
						$class      = 'error';
						$messages[] = sprintf( __( 'This appears to be an invalid license key for %s.', 'easy-digital-downloads' ), $args['name'] );

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'no_activations_left':
						$class      = 'error';
						$messages[] = sprintf( __( 'Your license key has reached its activation limit. <a href="%s">View possible upgrades</a> now.', 'easy-digital-downloads' ), 'https://easydigitaldownloads.com/your-account/' );

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'license_not_activable':
						$class      = 'error';
						$messages[] = __( 'The key you entered belongs to a bundle, please use the product specific license key.', 'easy-digital-downloads' );

						$license_status = 'license-' . $class . '-notice';
						break;

					default :
						$class      = 'error';
						$error      = ! empty( $license->error ) ? $license->error : __( 'unknown_error', 'easy-digital-downloads' );
						$messages[] = sprintf( __( 'There was an error with this license key: %s. Please <a href="%s">contact our support team</a>.', 'easy-digital-downloads' ), $error, 'https://easydigitaldownloads.com/support' );

						$license_status = 'license-' . $class . '-notice';
						break;
				}

			} else {

				switch ( $license->license ) {

					case 'valid' :
					default:

						$class = 'valid';

						if ( 'lifetime' === $license->expires ) {
							$messages[] = __( 'License key never expires.', 'easy-digital-downloads' );

							$license_status = 'license-lifetime-notice';

						} elseif ( ( $expiration > $now ) && ( $expiration - $now < ( DAY_IN_SECONDS * 30 ) ) ) {
							$messages[] = sprintf(
								__( 'Your license key expires soon! It expires on %s.', 'easy-digital-downloads' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) )
							);

							$license_status = 'license-expires-soon-notice';

						} else {
							$messages[] = sprintf(
								__( 'Your license key expires on %s.', 'easy-digital-downloads' ),
								edd_date_i18n( $expiration )
							);

							$license_status = 'license-expiration-date-notice';
						}

						break;
				}
			}

		} else {
			$class = 'empty';

			$messages[] = sprintf(
				__( 'To receive updates, please enter your valid %s license key.', 'easy-digital-downloads' ),
				$args['name']
			);

			$license_status = null;
		}

		$class .= ' ' . edd_sanitize_html_class( $args['field_class'] );

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="password" autocomplete="off" class="' . sanitize_html_class( $size ) . '-text" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" name="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" value="' . sanitize_key( $value ) . '"/>';

		if ( ( is_object( $license ) && ! empty( $license->license ) && 'valid' == $license->license ) || 'valid' == $license ) {
			$html .= '<input type="submit" class="button-secondary" name="' . $args['id'] . '_deactivate" value="' . __( 'Deactivate License', 'easy-digital-downloads' ) . '"/>';
		}

		$html .= '<label for="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']"> ' . wp_kses_post( $args['desc'] ) . '</label>';

		if ( ! empty( $messages ) ) {
			foreach ( $messages as $message ) {

				$html .= '<div class="edd-license-data edd-license-' . esc_attr( $class ) . ' ' . esc_attr( $license_status ) . '">';
				$html .= '<p>' . wp_kses_post( $message ) . '</p>';
				$html .= '</div>';

			}
		}

		wp_nonce_field( edd_sanitize_key( $args['id'] ) . '-nonce', edd_sanitize_key( $args['id'] ) . '-nonce' );

		echo $html;
	}
}

/**
 * Hook Callback
 *
 * Adds a do_action() hook in place of the field
 *
 * @since 1.0.8.2
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_hook_callback( $args ) {
	do_action( 'edd_' . $args['id'], $args );
}

/**
 * Set manage_shop_settings as the cap required to save EDD settings pages
 *
 * @since 1.9
 * @return string capability required
 */
function edd_set_settings_cap() {
	return 'manage_shop_settings';
}
add_filter( 'option_page_capability_edd_settings', 'edd_set_settings_cap' );

/**
 * Maybe attach a tooltip to a setting
 *
 * @since 1.9
 * @param string $html
 * @param type $args
 * @return string
 */
function edd_add_setting_tooltip( $html = '', $args = array() ) {

	// Tooltip has title & description
	if ( ! empty( $args['tooltip_title'] ) && ! empty( $args['tooltip_desc'] ) ) {
		$tooltip   = '<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<strong>' . esc_html( $args['tooltip_title'] ) . '</strong>: ' . esc_html( $args['tooltip_desc'] ) . '"></span>';
		$has_p_tag = strstr( $html, '</p>'     );
		$has_label = strstr( $html, '</label>' );

		// Insert tooltip at end of paragraph
		if ( false !== $has_p_tag ) {
			$html = str_replace( '</p>', $tooltip . '</p>', $html );

		// Insert tooltip at end of label
		} elseif ( false !== $has_label ) {
			$html = str_replace( '</label>', '</label>' . $tooltip, $html );

		// Append tooltip to end of HTML
		} else {
			$html .= $tooltip;
		}
	}

	return $html;
}
add_filter( 'edd_after_setting_output', 'edd_add_setting_tooltip', 10, 2 );
