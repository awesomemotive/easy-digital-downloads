<?php
/**
 * Register Settings
 *
 * @package     EDD
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since 1.8.4
 * @return mixed
 */
function edd_get_option( $key = '', $default = false ) {
	global $edd_options;
	$value = ! empty( $edd_options[ $key ] ) ? $edd_options[ $key ] : $default;
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
 * @param string $key The Key to update
 * @param string|bool|int $value The value to set the key to
 * @return boolean True if updated, false if not.
 */
function edd_update_option( $key = '', $value = false ) {

	// If no key, exit
	if ( empty( $key ) ){
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
	$did_update = update_option( 'edd_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ){
		global $edd_options;
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
 * @param string $key The Key to delete
 * @return boolean True if updated, false if not.
 */
function edd_delete_option( $key = '' ) {

	// If no key, exit
	if ( empty( $key ) ){
		return false;
	}

	// First let's grab the current settings
	$options = get_option( 'edd_settings' );

	// Next let's try to update the value
	if( isset( $options[ $key ] ) ) {

		unset( $options[ $key ] );

	}

	$did_update = update_option( 'edd_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ){
		global $edd_options;
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

	$settings = get_option( 'edd_settings' );

	if( empty( $settings ) ) {

		// Update old settings with new single option

		$general_settings = is_array( get_option( 'edd_settings_general' ) )    ? get_option( 'edd_settings_general' )    : array();
		$gateway_settings = is_array( get_option( 'edd_settings_gateways' ) )   ? get_option( 'edd_settings_gateways' )   : array();
		$email_settings   = is_array( get_option( 'edd_settings_emails' ) )     ? get_option( 'edd_settings_emails' )     : array();
		$style_settings   = is_array( get_option( 'edd_settings_styles' ) )     ? get_option( 'edd_settings_styles' )     : array();
		$tax_settings     = is_array( get_option( 'edd_settings_taxes' ) )      ? get_option( 'edd_settings_taxes' )      : array();
		$ext_settings     = is_array( get_option( 'edd_settings_extensions' ) ) ? get_option( 'edd_settings_extensions' ) : array();
		$license_settings = is_array( get_option( 'edd_settings_licenses' ) )   ? get_option( 'edd_settings_licenses' )   : array();
		$misc_settings    = is_array( get_option( 'edd_settings_misc' ) )       ? get_option( 'edd_settings_misc' )       : array();

		$settings = array_merge( $general_settings, $gateway_settings, $email_settings, $style_settings, $tax_settings, $ext_settings, $license_settings, $misc_settings );

		update_option( 'edd_settings', $settings );

	}
	return apply_filters( 'edd_get_settings', $settings );
}

/**
 * Add all settings sections and fields
 *
 * @since 1.0
 * @return void
*/
function edd_register_settings() {

	if ( false == get_option( 'edd_settings' ) ) {
		add_option( 'edd_settings' );
	}

	foreach( edd_get_registered_settings() as $tab => $settings ) {

		add_settings_section(
			'edd_settings_' . $tab,
			__return_null(),
			'__return_false',
			'edd_settings_' . $tab
		);

		foreach ( $settings as $option ) {

			$name = isset( $option['name'] ) ? $option['name'] : '';

			add_settings_field(
				'edd_settings[' . $option['id'] . ']',
				$name,
				function_exists( 'edd_' . $option['type'] . '_callback' ) ? 'edd_' . $option['type'] . '_callback' : 'edd_missing_callback',
				'edd_settings_' . $tab,
				'edd_settings_' . $tab,
				array(
					'section'     => $tab,
					'id'          => isset( $option['id'] )          ? $option['id']          : null,
					'desc'        => ! empty( $option['desc'] )      ? $option['desc']        : '',
					'name'        => isset( $option['name'] )        ? $option['name']        : null,
					'size'        => isset( $option['size'] )        ? $option['size']        : null,
					'options'     => isset( $option['options'] )     ? $option['options']     : '',
					'std'         => isset( $option['std'] )         ? $option['std']         : '',
					'min'         => isset( $option['min'] )         ? $option['min']         : null,
					'max'         => isset( $option['max'] )         ? $option['max']         : null,
					'step'        => isset( $option['step'] )        ? $option['step']        : null,
					'chosen'      => isset( $option['chosen'] )      ? $option['chosen']      : null,
					'placeholder' => isset( $option['placeholder'] ) ? $option['placeholder'] : null,
					'allow_blank' => isset( $option['allow_blank'] ) ? $option['allow_blank'] : true,
					'readonly'    => isset( $option['readonly'] )    ? $option['readonly']    : false,
					'faux'        => isset( $option['faux'] )        ? $option['faux']        : false,
				)
			);
		}

	}

	// Creates our settings in the options table
	register_setting( 'edd_settings', 'edd_settings', 'edd_settings_sanitize' );

}
add_action('admin_init', 'edd_register_settings');

/**
 * Retrieve the array of plugin settings
 *
 * @since 1.8
 * @return array
*/
function edd_get_registered_settings() {

	/**
	 * 'Whitelisted' EDD settings, filters are provided for each settings
	 * section to allow extensions and other plugins to add their own settings
	 */
	$edd_settings = array(
		/** General Settings */
		'general' => apply_filters( 'edd_settings_general',
			array(
				'purchase_page' => array(
					'id' => 'purchase_page',
					'name' => __( 'Checkout Page', 'easy-digital-downloads' ),
					'desc' => __( 'This is the checkout page where buyers will complete their purchases. The [download_checkout] short code must be on this page.', 'easy-digital-downloads' ),
					'type' => 'select',
					'options' => edd_get_pages(),
					'chosen' => true,
					'placeholder' => __( 'Select a page', 'easy-digital-downloads' )
				),
				'success_page' => array(
					'id' => 'success_page',
					'name' => __( 'Success Page', 'easy-digital-downloads' ),
					'desc' => __( 'This is the page buyers are sent to after completing their purchases. The [edd_receipt] short code should be on this page.', 'easy-digital-downloads' ),
					'type' => 'select',
					'options' => edd_get_pages(),
					'chosen' => true,
					'placeholder' => __( 'Select a page', 'easy-digital-downloads' )
				),
				'failure_page' => array(
					'id' => 'failure_page',
					'name' => __( 'Failed Transaction Page', 'easy-digital-downloads' ),
					'desc' => __( 'This is the page buyers are sent to if their transaction is cancelled or fails', 'easy-digital-downloads' ),
					'type' => 'select',
					'options' => edd_get_pages(),
					'chosen' => true,
					'placeholder' => __( 'Select a page', 'easy-digital-downloads' )
				),
				'purchase_history_page' => array(
					'id' => 'purchase_history_page',
					'name' => __( 'Purchase History Page', 'easy-digital-downloads' ),
					'desc' => __( 'This page shows a complete purchase history for the current user, including download links', 'easy-digital-downloads' ),
					'type' => 'select',
					'options' => edd_get_pages(),
					'chosen' => true,
					'placeholder' => __( 'Select a page', 'easy-digital-downloads' )
				),
				'base_country' => array(
					'id' => 'base_country',
					'name' => __( 'Base Country', 'easy-digital-downloads' ),
					'desc' => __( 'Where does your store operate from?', 'easy-digital-downloads' ),
					'type' => 'select',
					'options' => edd_get_country_list(),
					'chosen' => true,
					'placeholder' => __( 'Select a country', 'easy-digital-downloads' )
				),
				'base_state' => array(
					'id' => 'base_state',
					'name' => __( 'Base State / Province', 'easy-digital-downloads' ),
					'desc' => __( 'What state / province does your store operate from?', 'easy-digital-downloads' ),
					'type' => 'shop_states',
					'chosen' => true,
					'placeholder' => __( 'Select a state', 'easy-digital-downloads' )
				),
				'currency_settings' => array(
					'id' => 'currency_settings',
					'name' => '<strong>' . __( 'Currency Settings', 'easy-digital-downloads' ) . '</strong>',
					'desc' => __( 'Configure the currency options', 'easy-digital-downloads' ),
					'type' => 'header'
				),
				'currency' => array(
					'id' => 'currency',
					'name' => __( 'Currency', 'easy-digital-downloads' ),
					'desc' => __( 'Choose your currency. Note that some payment gateways have currency restrictions.', 'easy-digital-downloads' ),
					'type' => 'select',
					'options' => edd_get_currencies(),
					'chosen' => true
				),
				'currency_position' => array(
					'id'      => 'currency_position',
					'name'    => __( 'Currency Position', 'easy-digital-downloads' ),
					'desc'    => __( 'Choose the location of the currency sign.', 'easy-digital-downloads' ),
					'type'    => 'select',
					'options' => array(
						'before' => __( 'Before - $10', 'easy-digital-downloads' ),
						'after'  => __( 'After - 10$', 'easy-digital-downloads' )
					)
				),
				'thousands_separator' => array(
					'id'   => 'thousands_separator',
					'name' => __( 'Thousands Separator', 'easy-digital-downloads' ),
					'desc' => __( 'The symbol (usually , or .) to separate thousands', 'easy-digital-downloads' ),
					'type' => 'text',
					'size' => 'small',
					'std'  => ','
				),
				'decimal_separator' => array(
					'id'   => 'decimal_separator',
					'name' => __( 'Decimal Separator', 'easy-digital-downloads' ),
					'desc' => __( 'The symbol (usually , or .) to separate decimal points', 'easy-digital-downloads' ),
					'type' => 'text',
					'size' => 'small',
					'std'  => '.'
				),
				'api_settings' => array(
					'id' => 'api_settings',
					'name' => '<strong>' . __( 'API Settings', 'easy-digital-downloads' ) . '</strong>',
					'desc' => '',
					'type' => 'header'
				),
				'api_allow_user_keys' => array(
					'id'   => 'api_allow_user_keys',
					'name' => __( 'Allow User Keys', 'easy-digital-downloads' ),
					'desc' => __( 'Check this box to allow all users to generate API keys. Users with the \'manage_shop_settings\' capability are always allowed to generate keys.', 'easy-digital-downloads' ),
					'type' => 'checkbox'
				),
				'tracking_settings' => array(
					'id' => 'tracking_settings',
					'name' => '<strong>' . __( 'Tracking Settings', 'easy-digital-downloads' ) . '</strong>',
					'desc' => '',
					'type' => 'header'
				),
				'allow_tracking' => array(
					'id'   => 'allow_tracking',
					'name' => __( 'Allow Usage Tracking?', 'easy-digital-downloads' ),
					'desc' => sprintf(
						__( 'Allow Easy Digital Downloads to anonymously track how this plugin is used and help us make the plugin better. Opt-in to tracking and our newsletter and immediately be emailed a 20&#37; discount to the EDD shop, valid towards the <a href="%s" target="_blank">purchase of extensions</a>. No sensitive data is tracked.', 'easy-digital-downloads' ),
						'https://easydigitaldownloads.com/extensions?utm_source=' . substr( md5( get_bloginfo( 'name' ) ), 0, 10 ) . '&utm_medium=admin&utm_term=settings&utm_campaign=EDDUsageTracking'
					),
					'type' => 'checkbox'
				),
				'uninstall_on_delete' => array(
					'id'   => 'uninstall_on_delete',
					'name' => __( 'Remove Data on Uninstall?', 'easy-digital-downloads' ),
					'desc' => __( 'Check this box if you would like EDD to completely remove all of its data when the plugin is deleted.', 'easy-digital-downloads' ),
					'type' => 'checkbox'
				)
			)
		),
		/** Payment Gateways Settings */
		'gateways' => apply_filters('edd_settings_gateways',
			array(
				'test_mode' => array(
					'id' => 'test_mode',
					'name' => __( 'Test Mode', 'easy-digital-downloads' ),
					'desc' => __( 'While in test mode no live transactions are processed. To fully use test mode, you must have a sandbox (test) account for the payment gateway you are testing.', 'easy-digital-downloads' ),
					'type' => 'checkbox'
				),
				'gateways' => array(
					'id'      => 'gateways',
					'name'    => __( 'Payment Gateways', 'easy-digital-downloads' ),
					'desc'    => __( 'Choose the payment gateways you want to enable.', 'easy-digital-downloads' ),
					'type'    => 'gateways',
					'options' => edd_get_payment_gateways()
				),
				'default_gateway' => array(
					'id'      => 'default_gateway',
					'name'    => __( 'Default Gateway', 'easy-digital-downloads' ),
					'desc'    => __( 'This gateway will be loaded automatically with the checkout page.', 'easy-digital-downloads' ),
					'type'    => 'gateway_select',
					'options' => edd_get_payment_gateways()
				),
				'accepted_cards' => array(
					'id'      => 'accepted_cards',
					'name'    => __( 'Accepted Payment Method Icons', 'easy-digital-downloads' ),
					'desc'    => __( 'Display icons for the selected payment methods', 'easy-digital-downloads' ) . '<br/>' . __( 'You will also need to configure your gateway settings if you are accepting credit cards', 'easy-digital-downloads' ),
					'type'    => 'payment_icons',
					'options' => apply_filters('edd_accepted_payment_icons', array(
							'mastercard'      => 'Mastercard',
							'visa'            => 'Visa',
							'americanexpress' => 'American Express',
							'discover'        => 'Discover',
							'paypal'          => 'PayPal',
						)
					)
				),
				'paypal' => array(
					'id' => 'paypal',
					'name' => '<strong>' . __( 'PayPal Settings', 'easy-digital-downloads' ) . '</strong>',
					'desc' => __( 'Configure the PayPal settings', 'easy-digital-downloads' ),
					'type' => 'header'
				),
				'paypal_email' => array(
					'id'   => 'paypal_email',
					'name' => __( 'PayPal Email', 'easy-digital-downloads' ),
					'desc' => __( 'Enter your PayPal account\'s email', 'easy-digital-downloads' ),
					'type' => 'text',
					'size' => 'regular'
				),
				'paypal_page_style' => array(
					'id'   => 'paypal_page_style',
					'name' => __( 'PayPal Page Style', 'easy-digital-downloads' ),
					'desc' => __( 'Enter the name of the page style to use, or leave blank for default', 'easy-digital-downloads' ),
					'type' => 'text',
					'size' => 'regular'
				),
				'disable_paypal_verification' => array(
					'id'   => 'disable_paypal_verification',
					'name' => __( 'Disable PayPal IPN Verification', 'easy-digital-downloads' ),
					'desc' => __( 'If payments are not getting marked as complete, then check this box. This forces the site to use a slightly less secure method of verifying purchases.', 'easy-digital-downloads' ),
					'type' => 'checkbox'
				),
			)
		),
		/** Emails Settings */
		'emails' => apply_filters('edd_settings_emails',
			array(
				'email_template' => array(
					'id'      => 'email_template',
					'name'    => __( 'Email Template', 'easy-digital-downloads' ),
					'desc'    => __( 'Choose a template. Click "Save Changes" then "Preview Purchase Receipt" to see the new template.', 'easy-digital-downloads' ),
					'type'    => 'select',
					'options' => edd_get_email_templates()
				),
				'email_logo' => array(
					'id'   => 'email_logo',
					'name' => __( 'Logo', 'easy-digital-downloads' ),
					'desc' => __( 'Upload or choose a logo to be displayed at the top of the purchase receipt emails. Displayed on HTML emails only.', 'easy-digital-downloads' ),
					'type' => 'upload'
				),
				'email_settings' => array(
					'id'   => 'email_settings',
					'name' => '',
					'desc' => '',
					'type' => 'hook'
				),
				'from_name' => array(
					'id'   => 'from_name',
					'name' => __( 'From Name', 'easy-digital-downloads' ),
					'desc' => __( 'The name purchase receipts are said to come from. This should probably be your site or shop name.', 'easy-digital-downloads' ),
					'type' => 'text',
					'std'  => get_bloginfo( 'name' )
				),
				'from_email' => array(
					'id'   => 'from_email',
					'name' => __( 'From Email', 'easy-digital-downloads' ),
					'desc' => __( 'Email to send purchase receipts from. This will act as the "from" and "reply-to" address.', 'easy-digital-downloads' ),
					'type' => 'text',
					'std'  => get_bloginfo( 'admin_email' )
				),
				'purchase_subject' => array(
					'id'   => 'purchase_subject',
					'name' => __( 'Purchase Email Subject', 'easy-digital-downloads' ),
					'desc' => __( 'Enter the subject line for the purchase receipt email', 'easy-digital-downloads' ),
					'type' => 'text',
					'std'  => __( 'Purchase Receipt', 'easy-digital-downloads' )
				),
				'purchase_heading' => array(
					'id'   => 'purchase_heading',
					'name' => __( 'Purchase Email Heading', 'easy-digital-downloads' ),
					'desc' => __( 'Enter the heading for the purchase receipt email', 'easy-digital-downloads' ),
					'type' => 'text',
					'std'  => __( 'Purchase Receipt', 'easy-digital-downloads' )
				),
				'purchase_receipt' => array(
					'id'   => 'purchase_receipt',
					'name' => __( 'Purchase Receipt', 'easy-digital-downloads' ),
					'desc' => __('Enter the text that is sent as purchase receipt email to users after completion of a successful purchase. HTML is accepted. Available template tags:','easy-digital-downloads' ) . '<br/>' . edd_get_emails_tags_list(),
					'type' => 'rich_editor',
					'std'  => __( "Dear", "easy-digital-downloads" ) . " {name},\n\n" . __( "Thank you for your purchase. Please click on the link(s) below to download your files.", "easy-digital-downloads" ) . "\n\n{download_list}\n\n{sitename}"
				),
				'sale_notification_header' => array(
					'id' => 'sale_notification_header',
					'name' => '<strong>' . __('New Sale Notifications','easy-digital-downloads' ) . '</strong>',
					'desc' => __('Configure new sale notification emails','easy-digital-downloads' ),
					'type' => 'header'
				),
				'sale_notification_subject' => array(
					'id'   => 'sale_notification_subject',
					'name' => __( 'Sale Notification Subject', 'easy-digital-downloads' ),
					'desc' => __( 'Enter the subject line for the sale notification email', 'easy-digital-downloads' ),
					'type' => 'text',
					'std'  => 'New download purchase - Order #{payment_id}'
				),
				'sale_notification' => array(
					'id'   => 'sale_notification',
					'name' => __( 'Sale Notification', 'easy-digital-downloads' ),
					'desc' => __( 'Enter the text that is sent as sale notification email after completion of a purchase. HTML is accepted. Available template tags:', 'easy-digital-downloads' ) . '<br/>' . edd_get_emails_tags_list(),
					'type' => 'rich_editor',
					'std'  => edd_get_default_sale_notification_email()
				),
				'admin_notice_emails' => array(
					'id'   => 'admin_notice_emails',
					'name' => __( 'Sale Notification Emails', 'easy-digital-downloads' ),
					'desc' => __( 'Enter the email address(es) that should receive a notification anytime a sale is made, one per line', 'easy-digital-downloads' ),
					'type' => 'textarea',
					'std'  => get_bloginfo( 'admin_email' )
				),
				'disable_admin_notices' => array(
					'id'   => 'disable_admin_notices',
					'name' => __( 'Disable Admin Notifications', 'easy-digital-downloads' ),
					'desc' => __( 'Check this box if you do not want to receive emails when new sales are made.', 'easy-digital-downloads' ),
					'type' => 'checkbox'
				)
			)
		),
		/** Styles Settings */
		'styles' => apply_filters('edd_settings_styles',
			array(
				'disable_styles' => array(
					'id'   => 'disable_styles',
					'name' => __( 'Disable Styles', 'easy-digital-downloads' ),
					'desc' => __( 'Check this to disable all included styling of buttons, checkout fields, and all other elements.', 'easy-digital-downloads' ),
					'type' => 'checkbox'
				),
				'button_header' => array(
					'id' => 'button_header',
					'name' => '<strong>' . __( 'Buttons', 'easy-digital-downloads' ) . '</strong>',
					'desc' => __( 'Options for add to cart and purchase buttons', 'easy-digital-downloads' ),
					'type' => 'header'
				),
				'button_style' => array(
					'id'      => 'button_style',
					'name'    => __( 'Default Button Style', 'easy-digital-downloads' ),
					'desc'    => __( 'Choose the style you want to use for the buttons.', 'easy-digital-downloads' ),
					'type'    => 'select',
					'options' => edd_get_button_styles()
				),
				'checkout_color' => array(
					'id'      => 'checkout_color',
					'name'    => __( 'Default Button Color', 'easy-digital-downloads' ),
					'desc'    => __( 'Choose the color you want to use for the buttons.', 'easy-digital-downloads' ),
					'type'    => 'color_select',
					'options' => edd_get_button_colors()
				)
			)
		),
		/** Taxes Settings */
		'taxes' => apply_filters('edd_settings_taxes',
			array(
				'enable_taxes' => array(
					'id'   => 'enable_taxes',
					'name' => __( 'Enable Taxes', 'easy-digital-downloads' ),
					'desc' => __( 'Check this to enable taxes on purchases.', 'easy-digital-downloads' ),
					'type' => 'checkbox',
				),
				'tax_rates' => array(
					'id' => 'tax_rates',
					'name' => '<strong>' . __( 'Tax Rates', 'easy-digital-downloads' ) . '</strong>',
					'desc' => __( 'Enter tax rates for specific regions.', 'easy-digital-downloads' ),
					'type' => 'tax_rates'
				),
				'tax_rate' => array(
					'id'   => 'tax_rate',
					'name' => __( 'Fallback Tax Rate', 'easy-digital-downloads' ),
					'desc' => __( 'Enter a percentage, such as 6.5. Customers not in a specific rate will be charged this rate.', 'easy-digital-downloads' ),
					'type' => 'text',
					'size' => 'small'
				),
				'prices_include_tax' => array(
					'id'   => 'prices_include_tax',
					'name' => __( 'Prices entered with tax', 'easy-digital-downloads' ),
					'desc' => __( 'This option affects how you enter prices.', 'easy-digital-downloads' ),
					'type' => 'radio',
					'std'  => 'no',
					'options' => array(
						'yes' => __( 'Yes, I will enter prices inclusive of tax', 'easy-digital-downloads' ),
						'no'  => __( 'No, I will enter prices exclusive of tax', 'easy-digital-downloads' )
					)
				),
				'display_tax_rate' => array(
					'id'   => 'display_tax_rate',
					'name' => __( 'Display Tax Rate on Prices', 'easy-digital-downloads' ),
					'desc' => __( 'Some countries require a notice when product prices include tax.', 'easy-digital-downloads' ),
					'type' => 'checkbox',
				),
				'checkout_include_tax' => array(
					'id'   => 'checkout_include_tax',
					'name' => __( 'Display during checkout', 'easy-digital-downloads' ),
					'desc' => __( 'Should prices on the checkout page be shown with or without tax?', 'easy-digital-downloads' ),
					'type' => 'select',
					'std'  => 'no',
					'options' => array(
						'yes' => __( 'Including tax', 'easy-digital-downloads' ),
						'no'  => __( 'Excluding tax', 'easy-digital-downloads' )
					)
				)
			)
		),
		/** Extension Settings */
		'extensions' => apply_filters('edd_settings_extensions',
			array()
		),
		'licenses' => apply_filters('edd_settings_licenses',
			array()
		),
		/** Misc Settings */
		'misc' => apply_filters('edd_settings_misc',
			array(
				'enable_ajax_cart' => array(
					'id'   => 'enable_ajax_cart',
					'name' => __( 'Enable Ajax', 'easy-digital-downloads' ),
					'desc' => __( 'Check this to enable AJAX for the shopping cart.', 'easy-digital-downloads' ),
					'type' => 'checkbox',
					'std'  => '1'
				),
				'redirect_on_add' => array(
					'id'   => 'redirect_on_add',
					'name' => __( 'Redirect to Checkout', 'easy-digital-downloads' ),
					'desc' => __( 'Immediately redirect to checkout after adding an item to the cart?', 'easy-digital-downloads' ),
					'type' => 'checkbox'
				),
				'enforce_ssl' => array(
					'id'   => 'enforce_ssl',
					'name' => __( 'Enforce SSL on Checkout', 'easy-digital-downloads' ),
					'desc' => __( 'Check this to force users to be redirected to the secure checkout page. You must have an SSL certificate installed to use this option.', 'easy-digital-downloads' ),
					'type' => 'checkbox'
				),
				'logged_in_only' => array(
					'id'   => 'logged_in_only',
					'name' => __( 'Disable Guest Checkout', 'easy-digital-downloads' ),
					'desc' => __( 'Require that users be logged-in to purchase files.', 'easy-digital-downloads' ),
					'type' => 'checkbox'
				),
				'show_register_form' => array(
					'id'      => 'show_register_form',
					'name'    => __( 'Show Register / Login Form?', 'easy-digital-downloads' ),
					'desc'    => __( 'Display the registration and login forms on the checkout page for non-logged-in users.', 'easy-digital-downloads' ),
					'type'    => 'select',
					'std'     => 'none',
					'options' => array(
						'both'         => __( 'Registration and Login Forms', 'easy-digital-downloads' ),
						'registration' => __( 'Registration Form Only', 'easy-digital-downloads' ),
						'login'        => __( 'Login Form Only', 'easy-digital-downloads' ),
						'none'         => __( 'None', 'easy-digital-downloads' )
					),
				),
				'item_quantities' => array(
					'id'   => 'item_quantities',
					'name' => __('Item Quantities','easy-digital-downloads' ),
					'desc' => __('Allow item quantities to be changed.','easy-digital-downloads' ),
					'type' => 'checkbox'
				),
				'allow_multiple_discounts' => array(
					'id'   => 'allow_multiple_discounts',
					'name' => __('Multiple Discounts','easy-digital-downloads' ),
					'desc' => __('Allow customers to use multiple discounts on the same purchase?','easy-digital-downloads' ),
					'type' => 'checkbox'
				),
				'enable_cart_saving' => array(
					'id'   => 'enable_cart_saving',
					'name' => __( 'Enable Cart Saving', 'easy-digital-downloads' ),
					'desc' => __( 'Check this to enable cart saving on the checkout.', 'easy-digital-downloads' ),
					'type' => 'checkbox'
				),
				'field_downloads' => array(
					'id' => 'field_downloads',
					'name' => '<strong>' . __( 'File Downloads', 'easy-digital-downloads' ) . '</strong>',
					'desc' => '',
					'type' => 'header'
				),
				'download_method' => array(
					'id'      => 'download_method',
					'name'    => __( 'Download Method', 'easy-digital-downloads' ),
					'desc'    => sprintf( __( 'Select the file download method. Note, not all methods work on all servers.', 'easy-digital-downloads' ), edd_get_label_singular() ),
					'type'    => 'select',
					'options' => array(
						'direct'   => __( 'Forced', 'easy-digital-downloads' ),
						'redirect' => __( 'Redirect', 'easy-digital-downloads' )
					)
				),
				'symlink_file_downloads' => array(
					'id'   => 'symlink_file_downloads',
					'name' => __( 'Symlink File Downloads?', 'easy-digital-downloads' ),
					'desc' => __( 'Check this if you are delivering really large files or having problems with file downloads completing.', 'easy-digital-downloads' ),
					'type' => 'checkbox'
				),
				'file_download_limit' => array(
					'id'   => 'file_download_limit',
					'name' => __( 'File Download Limit', 'easy-digital-downloads' ),
					'desc' => sprintf( __( 'The maximum number of times files can be downloaded for purchases. Can be overwritten for each %s.', 'easy-digital-downloads' ), edd_get_label_singular() ),
					'type' => 'number',
					'size' => 'small'
				),
				'download_link_expiration' => array(
					'id'   => 'download_link_expiration',
					'name' => __( 'Download Link Expiration', 'easy-digital-downloads' ),
					'desc' => __( 'How long should download links be valid for? Default is 24 hours from the time they are generated. Enter a time in hours.', 'easy-digital-downloads' ),
					'type' => 'number',
					'size' => 'small',
					'std'  => '24',
					'min'  => '0'
				),
				'disable_redownload' => array(
					'id'   => 'disable_redownload',
					'name' => __( 'Disable Redownload?', 'easy-digital-downloads' ),
					'desc' => __( 'Check this if you do not want to allow users to redownload items from their purchase history.', 'easy-digital-downloads' ),
					'type' => 'checkbox'
				),
				'accounting_settings' => array(
					'id' => 'accounting_settings',
					'name' => '<strong>' . __( 'Accounting Settings', 'easy-digital-downloads' ) . '</strong>',
					'desc' => '',
					'type' => 'header'
				),
				'enable_skus' => array(
					'id'   => 'enable_skus',
					'name' => __( 'Enable SKU Entry', 'easy-digital-downloads' ),
					'desc' => __( 'Check this box to allow entry of product SKUs. SKUs will be shown on purchase receipt and exported purchase histories.', 'easy-digital-downloads' ),
					'type' => 'checkbox'
				),
				'enable_sequential' => array(
					'id'   => 'enable_sequential',
					'name' => __( 'Sequential Order Numbers', 'easy-digital-downloads' ),
					'desc' => __( 'Check this box to enable sequential order numbers.', 'easy-digital-downloads' ),
					'type' => 'checkbox'
				),
				'sequential_start' => array(
					'id'   => 'sequential_start',
					'name' => __( 'Sequential Starting Number', 'easy-digital-downloads' ),
					'desc' => __( 'The number that sequential order numbers should start at.', 'easy-digital-downloads' ),
					'type' => 'number',
					'size' => 'small',
					'std'  => '1'
				),
				'sequential_prefix' => array(
					'id'   => 'sequential_prefix',
					'name' => __( 'Sequential Number Prefix', 'easy-digital-downloads' ),
					'desc' => __( 'A prefix to prepend to all sequential order numbers.', 'easy-digital-downloads' ),
					'type' => 'text'
				),
				'sequential_postfix' => array(
					'id'   => 'sequential_postfix',
					'name' => __( 'Sequential Number Postfix', 'easy-digital-downloads' ),
					'desc' => __( 'A postfix to append to all sequential order numbers.', 'easy-digital-downloads' ),
					'type' => 'text',
				),
				'terms' => array(
					'id' => 'terms',
					'name' => '<strong>' . __( 'Terms of Agreement', 'easy-digital-downloads' ) . '</strong>',
					'desc' => '',
					'type' => 'header'
				),
				'show_agree_to_terms' => array(
					'id'   => 'show_agree_to_terms',
					'name' => __( 'Agree to Terms', 'easy-digital-downloads' ),
					'desc' => __( 'Check this to show an agree to terms on the checkout that users must agree to before purchasing.', 'easy-digital-downloads' ),
					'type' => 'checkbox'
				),
				'agree_label' => array(
					'id'   => 'agree_label',
					'name' => __( 'Agree to Terms Label', 'easy-digital-downloads' ),
					'desc' => __( 'Label shown next to the agree to terms check box.', 'easy-digital-downloads' ),
					'type' => 'text',
					'size' => 'regular'
				),
				'agree_text' => array(
					'id'   => 'agree_text',
					'name' => __( 'Agreement Text', 'easy-digital-downloads' ),
					'desc' => __( 'If Agree to Terms is checked, enter the agreement terms here.', 'easy-digital-downloads' ),
					'type' => 'rich_editor'
				),
				'checkout_label' => array(
					'id'   => 'checkout_label',
					'name' => __( 'Complete Purchase Text', 'easy-digital-downloads' ),
					'desc' => __( 'The button label for completing a purchase.', 'easy-digital-downloads' ),
					'type' => 'text',
					'std'  => __( 'Purchase', 'easy-digital-downloads' )
				),
				'add_to_cart_text' => array(
					'id'   => 'add_to_cart_text',
					'name' => __( 'Add to Cart Text', 'easy-digital-downloads' ),
					'desc' => __( 'Text shown on the Add to Cart Buttons.', 'easy-digital-downloads' ),
					'type' => 'text',
					'std'  => __( 'Add to Cart', 'easy-digital-downloads' )
				),
				'buy_now_text' => array(
					'id' => 'buy_now_text',
					'name' => __( 'Buy Now Text', 'easy-digital-downloads' ),
					'desc' => __( 'Text shown on the Buy Now Buttons.', 'easy-digital-downloads' ),
					'type' => 'text',
					'std' => __( 'Buy Now', 'easy-digital-downloads' )
				)
			)
		)
	);

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
 * @param array $input The value inputted in the field
 *
 * @return string $input Sanitizied value
 */
function edd_settings_sanitize( $input = array() ) {

	global $edd_options;

	if ( empty( $_POST['_wp_http_referer'] ) ) {
		return $input;
	}

	parse_str( $_POST['_wp_http_referer'], $referrer );

	$settings = edd_get_registered_settings();
	$tab      = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';

	$input = $input ? $input : array();
	$input = apply_filters( 'edd_settings_' . $tab . '_sanitize', $input );

	// Loop through each setting being saved and pass it through a sanitization filter
	foreach ( $input as $key => $value ) {

		// Get the setting type (checkbox, select, etc)
		$type = isset( $settings[$tab][$key]['type'] ) ? $settings[$tab][$key]['type'] : false;

		if ( $type ) {
			// Field type specific filter
			$input[$key] = apply_filters( 'edd_settings_sanitize_' . $type, $value, $key );
		}

		// General filter
		$input[$key] = apply_filters( 'edd_settings_sanitize', $input[$key], $key );
	}

	// Loop through the whitelist and unset any that are empty for the tab being saved
	if ( ! empty( $settings[$tab] ) ) {
		foreach ( $settings[$tab] as $key => $value ) {

			// settings used to have numeric keys, now they have keys that match the option ID. This ensures both methods work
			if ( is_numeric( $key ) ) {
				$key = $value['id'];
			}

			if ( empty( $input[$key] ) ) {
				unset( $edd_options[$key] );
			}

		}
	}

	// Merge our new settings with the existing
	$output = array_merge( $edd_options, $input );

	add_settings_error( 'edd-notices', '', __( 'Settings updated.', 'easy-digital-downloads' ), 'updated' );

	return $output;
}

/**
 * Misc Settings Sanitization
 *
 * @since 1.6
 * @param array $input The value inputted in the field
 * @return string $input Sanitizied value
 */
function edd_settings_sanitize_misc( $input ) {

	global $edd_options;

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return $input;
	}

	if( edd_get_file_download_method() != $input['download_method'] || ! edd_htaccess_exists() ) {
		// Force the .htaccess files to be updated if the Download method was changed.
		edd_create_protection_files( true, $input['download_method'] );
	}

	if( ! empty( $input['enable_sequential'] ) && ! edd_get_option( 'enable_sequential' ) ) {

		// Shows an admin notice about upgrading previous order numbers
		EDD()->session->set( 'upgrade_sequential', '1' );

	}

	return $input;
}
add_filter( 'edd_settings_misc_sanitize', 'edd_settings_sanitize_misc' );

/**
 * Taxes Settings Sanitization
 *
 * Adds a settings error (for the updated message)
 * This also saves the tax rates table
 *
 * @since 1.6
 * @param array $input The value inputted in the field
 * @return string $input Sanitizied value
 */
function edd_settings_sanitize_taxes( $input ) {

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return $input;
	}

	$new_rates = ! empty( $_POST['tax_rates'] ) ? array_values( $_POST['tax_rates'] ) : array();

	update_option( 'edd_tax_rates', $new_rates );

	return $input;
}
add_filter( 'edd_settings_taxes_sanitize', 'edd_settings_sanitize_taxes' );

/**
 * Sanitize text fields
 *
 * @since 1.8
 * @param array $input The field value
 * @return string $input Sanitizied value
 */
function edd_sanitize_text_field( $input ) {
	return trim( $input );
}
add_filter( 'edd_settings_sanitize_text', 'edd_sanitize_text_field' );

/**
 * Retrieve settings tabs
 *
 * @since 1.8
 * @return array $tabs
 */
function edd_get_settings_tabs() {

	$settings = edd_get_registered_settings();

	$tabs             = array();
	$tabs['general']  = __( 'General', 'easy-digital-downloads' );
	$tabs['gateways'] = __( 'Payment Gateways', 'easy-digital-downloads' );
	$tabs['emails']   = __( 'Emails', 'easy-digital-downloads' );
	$tabs['styles']   = __( 'Styles', 'easy-digital-downloads' );
	$tabs['taxes']    = __( 'Taxes', 'easy-digital-downloads' );

	if( ! empty( $settings['extensions'] ) ) {
		$tabs['extensions'] = __( 'Extensions', 'easy-digital-downloads' );
	}
	if( ! empty( $settings['licenses'] ) ) {
		$tabs['licenses'] = __( 'Licenses', 'easy-digital-downloads' );
	}

	$tabs['misc']      = __( 'Misc', 'easy-digital-downloads' );

	return apply_filters( 'edd_settings_tabs', $tabs );
}

/**
 * Retrieve a list of all published pages
 *
 * On large sites this can be expensive, so only load if on the settings page or $force is set to true
 *
 * @since 1.9.5
 * @param bool $force Force the pages to be loaded even if not on settings
 * @return array $pages_options An array of the pages
 */
function edd_get_pages( $force = false ) {

	$pages_options = array( '' => '' ); // Blank option

	if( ( ! isset( $_GET['page'] ) || 'edd-settings' != $_GET['page'] ) && ! $force ) {
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
 * @param array $args Arguments passed by the setting
 * @return void
 */
function edd_header_callback( $args ) {
	echo '<hr/>';
}

/**
 * Checkbox Callback
 *
 * Renders checkboxes.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_checkbox_callback( $args ) {
	global $edd_options;

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$name = '';
	} else {
		$name = 'name="edd_settings[' . $args['id'] . ']"';
	}

	$checked = isset( $edd_options[ $args['id'] ] ) ? checked( 1, $edd_options[ $args['id'] ], false ) : '';
	$html = '<input type="checkbox" id="edd_settings[' . $args['id'] . ']"' . $name . ' value="1" ' . $checked . '/>';
	$html .= '<label for="edd_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Multicheck Callback
 *
 * Renders multiple checkboxes.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_multicheck_callback( $args ) {
	global $edd_options;

	if ( ! empty( $args['options'] ) ) {
		foreach( $args['options'] as $key => $option ):
			if( isset( $edd_options[$args['id']][$key] ) ) { $enabled = $option; } else { $enabled = NULL; }
			echo '<input name="edd_settings[' . $args['id'] . '][' . $key . ']" id="edd_settings[' . $args['id'] . '][' . $key . ']" type="checkbox" value="' . $option . '" ' . checked($option, $enabled, false) . '/>&nbsp;';
			echo '<label for="edd_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
		endforeach;
		echo '<p class="description">' . $args['desc'] . '</p>';
	}
}

/**
 * Payment method icons callback
 *
 * @since 2.1
 * @param array $args Arguments passed by the setting
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_payment_icons_callback( $args ) {
	global $edd_options;

	if ( ! empty( $args['options'] ) ) {
		foreach( $args['options'] as $key => $option ) {

			if( isset( $edd_options[$args['id']][$key] ) ) {
				$enabled = $option;
			} else {
				$enabled = NULL;
			}

			echo '<label for="edd_settings[' . $args['id'] . '][' . $key . ']" style="margin-right:10px;line-height:16px;height:16px;display:inline-block;">';

				echo '<input name="edd_settings[' . $args['id'] . '][' . $key . ']" id="edd_settings[' . $args['id'] . '][' . $key . ']" type="checkbox" value="' . esc_attr( $option ) . '" ' . checked( $option, $enabled, false ) . '/>&nbsp;';

				if( edd_string_is_image_url( $key ) ) {

					echo '<img class="payment-icon" src="' . esc_url( $key ) . '" style="width:32px;height:24px;position:relative;top:6px;margin-right:5px;"/>';

				} else {

					$card = strtolower( str_replace( ' ', '', $option ) );

					if( has_filter( 'edd_accepted_payment_' . $card . '_image' ) ) {

						$image = apply_filters( 'edd_accepted_payment_' . $card . '_image', '' );

					} else {

						$image       = edd_locate_template( 'images' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR . $card . '.gif', false );
						$content_dir = WP_CONTENT_DIR;

						if( function_exists( 'wp_normalize_path' ) ) {

							// Replaces backslashes with forward slashes for Windows systems
							$image = wp_normalize_path( $image );
							$content_dir = wp_normalize_path( $content_dir );

						}

						$image = str_replace( $content_dir, WP_CONTENT_URL, $image );

					}

					echo '<img class="payment-icon" src="' . esc_url( $image ) . '" style="width:32px;height:24px;position:relative;top:6px;margin-right:5px;"/>';
				}


			echo $option . '</label>';

		}
		echo '<p class="description" style="margin-top:16px;">' . $args['desc'] . '</p>';
	}
}

/**
 * Radio Callback
 *
 * Renders radio boxes.
 *
 * @since 1.3.3
 * @param array $args Arguments passed by the setting
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_radio_callback( $args ) {
	global $edd_options;

	foreach ( $args['options'] as $key => $option ) :
		$checked = false;

		if ( isset( $edd_options[ $args['id'] ] ) && $edd_options[ $args['id'] ] == $key )
			$checked = true;
		elseif( isset( $args['std'] ) && $args['std'] == $key && ! isset( $edd_options[ $args['id'] ] ) )
			$checked = true;

		echo '<input name="edd_settings[' . $args['id'] . ']"" id="edd_settings[' . $args['id'] . '][' . $key . ']" type="radio" value="' . $key . '" ' . checked(true, $checked, false) . '/>&nbsp;';
		echo '<label for="edd_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
	endforeach;

	echo '<p class="description">' . $args['desc'] . '</p>';
}

/**
 * Gateways Callback
 *
 * Renders gateways fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_gateways_callback( $args ) {
	global $edd_options;

	foreach ( $args['options'] as $key => $option ) :
		if ( isset( $edd_options['gateways'][ $key ] ) )
			$enabled = '1';
		else
			$enabled = null;

		echo '<input name="edd_settings[' . $args['id'] . '][' . $key . ']"" id="edd_settings[' . $args['id'] . '][' . $key . ']" type="checkbox" value="1" ' . checked('1', $enabled, false) . '/>&nbsp;';
		echo '<label for="edd_settings[' . $args['id'] . '][' . $key . ']">' . $option['admin_label'] . '</label><br/>';
	endforeach;
}

/**
 * Gateways Callback (drop down)
 *
 * Renders gateways select menu
 *
 * @since 1.5
 * @param array $args Arguments passed by the setting
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_gateway_select_callback($args) {
	global $edd_options;

	echo '<select name="edd_settings[' . $args['id'] . ']"" id="edd_settings[' . $args['id'] . ']">';

	foreach ( $args['options'] as $key => $option ) :
		$selected = isset( $edd_options[ $args['id'] ] ) ? selected( $key, $edd_options[$args['id']], false ) : '';
		echo '<option value="' . esc_attr( $key ) . '"' . $selected . '>' . esc_html( $option['admin_label'] ) . '</option>';
	endforeach;

	echo '</select>';
	echo '<label for="edd_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
}

/**
 * Text Callback
 *
 * Renders text fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_text_callback( $args ) {
	global $edd_options;

	if ( isset( $edd_options[ $args['id'] ] ) ) {
		$value = $edd_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$args['readonly'] = true;
		$value = isset( $args['std'] ) ? $args['std'] : '';
		$name  = '';
	} else {
		$name = 'name="edd_settings[' . $args['id'] . ']"';
	}

	$readonly = $args['readonly'] === true ? ' readonly="readonly"' : '';
	$size     = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html     = '<input type="text" class="' . $size . '-text" id="edd_settings[' . $args['id'] . ']"' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"' . $readonly . '/>';
	$html    .= '<label for="edd_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Number Callback
 *
 * Renders number fields.
 *
 * @since 1.9
 * @param array $args Arguments passed by the setting
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_number_callback( $args ) {
	global $edd_options;

	if ( isset( $edd_options[ $args['id'] ] ) ) {
		$value = $edd_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$args['readonly'] = true;
		$value = isset( $args['std'] ) ? $args['std'] : '';
		$name  = '';
	} else {
		$name = 'name="edd_settings[' . $args['id'] . ']"';
	}

	$max  = isset( $args['max'] ) ? $args['max'] : 999999;
	$min  = isset( $args['min'] ) ? $args['min'] : 0;
	$step = isset( $args['step'] ) ? $args['step'] : 1;

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . $size . '-text" id="edd_settings[' . $args['id'] . ']" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<label for="edd_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Textarea Callback
 *
 * Renders textarea fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_textarea_callback( $args ) {
	global $edd_options;

	if ( isset( $edd_options[ $args['id'] ] ) ) {
		$value = $edd_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$html = '<textarea class="large-text" cols="50" rows="5" id="edd_settings[' . $args['id'] . ']" name="edd_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	$html .= '<label for="edd_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Password Callback
 *
 * Renders password fields.
 *
 * @since 1.3
 * @param array $args Arguments passed by the setting
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_password_callback( $args ) {
	global $edd_options;

	if ( isset( $edd_options[ $args['id'] ] ) ) {
		$value = $edd_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="password" class="' . $size . '-text" id="edd_settings[' . $args['id'] . ']" name="edd_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';
	$html .= '<label for="edd_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Missing Callback
 *
 * If a function is missing for settings callbacks alert the user.
 *
 * @since 1.3.1
 * @param array $args Arguments passed by the setting
 * @return void
 */
function edd_missing_callback($args) {
	printf( __( 'The callback function used for the <strong>%s</strong> setting is missing.', 'easy-digital-downloads' ), $args['id'] );
}

/**
 * Select Callback
 *
 * Renders select fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_select_callback($args) {
	global $edd_options;

	if ( isset( $edd_options[ $args['id'] ] ) ) {
		$value = $edd_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['placeholder'] ) ) {
		$placeholder = $args['placeholder'];
	} else {
		$placeholder = '';
	}

	if ( isset( $args['chosen'] ) ) {
		$chosen = 'class="edd-chosen"';
	} else {
		$chosen = '';
	}

	$html = '<select id="edd_settings[' . $args['id'] . ']" name="edd_settings[' . $args['id'] . ']" ' . $chosen . 'data-placeholder="' . $placeholder . '" />';

	foreach ( $args['options'] as $option => $name ) {
		$selected = selected( $option, $value, false );
		$html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
	}

	$html .= '</select>';
	$html .= '<label for="edd_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Color select Callback
 *
 * Renders color select fields.
 *
 * @since 1.8
 * @param array $args Arguments passed by the setting
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_color_select_callback( $args ) {
	global $edd_options;

	if ( isset( $edd_options[ $args['id'] ] ) ) {
		$value = $edd_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$html = '<select id="edd_settings[' . $args['id'] . ']" name="edd_settings[' . $args['id'] . ']"/>';

	foreach ( $args['options'] as $option => $color ) {
		$selected = selected( $option, $value, false );
		$html .= '<option value="' . $option . '" ' . $selected . '>' . $color['label'] . '</option>';
	}

	$html .= '</select>';
	$html .= '<label for="edd_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Rich Editor Callback
 *
 * Renders rich editor fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $edd_options Array of all the EDD Options
 * @global $wp_version WordPress Version
 */
function edd_rich_editor_callback( $args ) {
	global $edd_options, $wp_version;

	if ( isset( $edd_options[ $args['id'] ] ) ) {
		$value = $edd_options[ $args['id'] ];

		if( empty( $args['allow_blank'] ) && empty( $value ) ) {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$rows = isset( $args['size'] ) ? $args['size'] : 20;

	if ( $wp_version >= 3.3 && function_exists( 'wp_editor' ) ) {
		ob_start();
		wp_editor( stripslashes( $value ), 'edd_settings_' . $args['id'], array( 'textarea_name' => 'edd_settings[' . $args['id'] . ']', 'textarea_rows' => $rows ) );
		$html = ob_get_clean();
	} else {
		$html = '<textarea class="large-text" rows="10" id="edd_settings[' . $args['id'] . ']" name="edd_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	}

	$html .= '<br/><label for="edd_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Upload Callback
 *
 * Renders upload fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_upload_callback( $args ) {
	global $edd_options;

	if ( isset( $edd_options[ $args['id'] ] ) ) {
		$value = $edd_options[$args['id']];
	} else {
		$value = isset($args['std']) ? $args['std'] : '';
	}

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="' . $size . '-text" id="edd_settings[' . $args['id'] . ']" name="edd_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<span>&nbsp;<input type="button" class="edd_settings_upload_button button-secondary" value="' . __( 'Upload File', 'easy-digital-downloads' ) . '"/></span>';
	$html .= '<label for="edd_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}


/**
 * Color picker Callback
 *
 * Renders color picker fields.
 *
 * @since 1.6
 * @param array $args Arguments passed by the setting
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_color_callback( $args ) {
	global $edd_options;

	if ( isset( $edd_options[ $args['id'] ] ) ) {
		$value = $edd_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$default = isset( $args['std'] ) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="edd-color-picker" id="edd_settings[' . $args['id'] . ']" name="edd_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '" data-default-color="' . esc_attr( $default ) . '" />';
	$html .= '<label for="edd_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Shop States Callback
 *
 * Renders states drop down based on the currently selected country
 *
 * @since 1.6
 * @param array $args Arguments passed by the setting
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_shop_states_callback($args) {
	global $edd_options;

	if ( isset( $args['placeholder'] ) ) {
		$placeholder = $args['placeholder'];
	} else {
		$placeholder = '';
	}

	$states = edd_get_shop_states();

	$chosen = ( $args['chosen'] ? ' edd-chosen' : '' );
	$class = empty( $states ) ? ' class="edd-no-states' . $chosen . '"' : 'class="' . $chosen . '"';
	$html = '<select id="edd_settings[' . $args['id'] . ']" name="edd_settings[' . $args['id'] . ']"' . $class . 'data-placeholder="' . $placeholder . '"/>';

	foreach ( $states as $option => $name ) {
		$selected = isset( $edd_options[ $args['id'] ] ) ? selected( $option, $edd_options[$args['id']], false ) : '';
		$html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
	}

	$html .= '</select>';
	$html .= '<label for="edd_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Tax Rates Callback
 *
 * Renders tax rates table
 *
 * @since 1.6
 * @param array $args Arguments passed by the setting
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_tax_rates_callback($args) {
	global $edd_options;
	$rates = edd_get_tax_rates();
	ob_start(); ?>
	<p><?php echo $args['desc']; ?></p>
	<table id="edd_tax_rates" class="wp-list-table widefat fixed posts">
		<thead>
			<tr>
				<th scope="col" class="edd_tax_country"><?php _e( 'Country', 'easy-digital-downloads' ); ?></th>
				<th scope="col" class="edd_tax_state"><?php _e( 'State / Province', 'easy-digital-downloads' ); ?></th>
				<th scope="col" class="edd_tax_global" title="<?php _e( 'Apply rate to whole country, regardless of state / province', 'easy-digital-downloads' ); ?>"><?php _e( 'Country Wide', 'easy-digital-downloads' ); ?></th>
				<th scope="col" class="edd_tax_rate"><?php _e( 'Rate', 'easy-digital-downloads' ); ?></th>
				<th scope="col"><?php _e( 'Remove', 'easy-digital-downloads' ); ?></th>
			</tr>
		</thead>
		<?php if( ! empty( $rates ) ) : ?>
			<?php foreach( $rates as $key => $rate ) : ?>
			<tr>
				<td class="edd_tax_country">
					<?php
					echo EDD()->html->select( array(
						'options'          => edd_get_country_list(),
						'name'             => 'tax_rates[' . $key . '][country]',
						'selected'         => $rate['country'],
						'show_option_all'  => false,
						'show_option_none' => false,
						'class'            => 'edd-select edd-tax-country',
						'chosen'           => false,
						'placeholder'      => __( 'Choose a country', 'easy-digital-downloads' )
					) );
					?>
				</td>
				<td class="edd_tax_state">
					<?php
					$states = edd_get_shop_states( $rate['country'] );
					if( ! empty( $states ) ) {
						echo EDD()->html->select( array(
							'options'          => $states,
							'name'             => 'tax_rates[' . $key . '][state]',
							'selected'         => $rate['state'],
							'show_option_all'  => false,
							'show_option_none' => false,
							'chosen'           => false,
							'placeholder'      => __( 'Choose a state', 'easy-digital-downloads' )
						) );
					} else {
						echo EDD()->html->text( array(
							'name'  => 'tax_rates[' . $key . '][state]', $rate['state'],
							'value' => ! empty( $rate['state'] ) ? $rate['state'] : '',
						) );
					}
					?>
				</td>
				<td class="edd_tax_global">
					<input type="checkbox" name="tax_rates[<?php echo $key; ?>][global]" id="tax_rates[<?php echo $key; ?>][global]" value="1"<?php checked( true, ! empty( $rate['global'] ) ); ?>/>
					<label for="tax_rates[<?php echo $key; ?>][global]"><?php _e( 'Apply to whole country', 'easy-digital-downloads' ); ?></label>
				</td>
				<td class="edd_tax_rate"><input type="number" class="small-text" step="0.0001" min="0.0" max="99" name="tax_rates[<?php echo $key; ?>][rate]" value="<?php echo $rate['rate']; ?>"/></td>
				<td><span class="edd_remove_tax_rate button-secondary"><?php _e( 'Remove Rate', 'easy-digital-downloads' ); ?></span></td>
			</tr>
			<?php endforeach; ?>
		<?php else : ?>
			<tr>
				<td class="edd_tax_country">
					<?php
					echo EDD()->html->select( array(
						'options'          => edd_get_country_list(),
						'name'             => 'tax_rates[0][country]',
						'show_option_all'  => false,
						'show_option_none' => false,
						'class'            => 'edd-select edd-tax-country',
						'chosen'           => false,
						'placeholder'      => __( 'Choose a country', 'easy-digital-downloads' )
					) ); ?>
				</td>
				<td class="edd_tax_state">
					<?php echo EDD()->html->text( array(
						'name' => 'tax_rates[0][state]'
					) ); ?>
				</td>
				<td class="edd_tax_global">
					<input type="checkbox" name="tax_rates[0][global]" value="1"/>
					<label for="tax_rates[0][global]"><?php _e( 'Apply to whole country', 'easy-digital-downloads' ); ?></label>
				</td>
				<td class="edd_tax_rate"><input type="number" class="small-text" step="0.0001" min="0.0" name="tax_rates[0][rate]" value=""/></td>
				<td><span class="edd_remove_tax_rate button-secondary"><?php _e( 'Remove Rate', 'easy-digital-downloads' ); ?></span></td>
			</tr>
		<?php endif; ?>
	</table>
	<p>
		<span class="button-secondary" id="edd_add_tax_rate"><?php _e( 'Add Tax Rate', 'easy-digital-downloads' ); ?></span>
	</p>
	<?php
	echo ob_get_clean();
}

/**
 * Descriptive text callback.
 *
 * Renders descriptive text onto the settings field.
 *
 * @since 2.1.3
 * @param array $args Arguments passed by the setting
 * @return void
 */
function edd_descriptive_text_callback( $args ) {
	echo wp_kses_post( $args['desc'] );
}

/**
 * Registers the license field callback for Software Licensing
 *
 * @since 1.5
 * @param array $args Arguments passed by the setting
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
if ( ! function_exists( 'edd_license_key_callback' ) ) {
	function edd_license_key_callback( $args ) {
		global $edd_options;

		if ( isset( $edd_options[ $args['id'] ] ) ) {
			$value = $edd_options[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="text" class="' . $size . '-text" id="edd_settings[' . $args['id'] . ']" name="edd_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';

		if ( 'valid' == get_option( $args['options']['is_valid_license_option'] ) ) {
			$html .= '<input type="submit" class="button-secondary" name="' . $args['id'] . '_deactivate" value="' . __( 'Deactivate License',  'easy-digital-downloads' ) . '"/>';
		}
		$html .= '<label for="edd_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

		wp_nonce_field( $args['id'] . '-nonce', $args['id'] . '-nonce' );

		echo $html;
	}
}

/**
 * Hook Callback
 *
 * Adds a do_action() hook in place of the field
 *
 * @since 1.0.8.2
 * @param array $args Arguments passed by the setting
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
