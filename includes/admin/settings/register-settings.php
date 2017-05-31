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
 * @global $edd_options Array of all the EDD Options
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
 * @global $edd_options Array of all the EDD Options
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
 * @global $edd_options Array of all the EDD Options
 * @return boolean True if removed, false if not.
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

	foreach ( edd_get_registered_settings() as $tab => $sections ) {
		foreach ( $sections as $section => $settings) {

			// Check for backwards compatibility
			$section_tabs = edd_get_settings_tab_sections( $tab );
			if ( ! is_array( $section_tabs ) || ! array_key_exists( $section, $section_tabs ) ) {
				$section = 'main';
				$settings = $sections;
			}

			add_settings_section(
				'edd_settings_' . $tab . '_' . $section,
				__return_null(),
				'__return_false',
				'edd_settings_' . $tab . '_' . $section
			);

			foreach ( $settings as $option ) {
				// For backwards compatibility
				if ( empty( $option['id'] ) ) {
					continue;
				}

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
				    'placeholder'   => null,
				    'allow_blank'   => true,
				    'readonly'      => false,
				    'faux'          => false,
				    'tooltip_title' => false,
				    'tooltip_desc'  => false,
				    'field_class'   => '',
				) );

				add_settings_field(
					'edd_settings[' . $args['id'] . ']',
					$args['name'],
					function_exists( 'edd_' . $args['type'] . '_callback' ) ? 'edd_' . $args['type'] . '_callback' : 'edd_missing_callback',
					'edd_settings_' . $tab . '_' . $section,
					'edd_settings_' . $tab . '_' . $section,
					$args
				);
			}
		}

	}

	// Creates our settings in the options table
	register_setting( 'edd_settings', 'edd_settings', 'edd_settings_sanitize' );

}
add_action( 'admin_init', 'edd_register_settings' );

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
				'main' => array(
					'page_settings' => array(
						'id'   => 'page_settings',
						'name' => '<h3>' . __( 'Page Settings', 'easy-digital-downloads' ) . '</h3>',
						'desc' => '',
						'type' => 'header',
						'tooltip_title' => __( 'Page Settings', 'easy-digital-downloads' ),
						'tooltip_desc'  => __( 'Easy Digital Downloads uses the pages below for handling the display of checkout, purchase confirmation, purchase history, and purchase failures. If pages are deleted or removed in some way, they can be recreated manually from the Pages menu. When re-creating the pages, enter the shortcode shown in the page content area.','easy-digital-downloads' ),
					),
					'purchase_page' => array(
						'id'          => 'purchase_page',
						'name'        => __( 'Checkout Page', 'easy-digital-downloads' ),
						'desc'        => __( 'This is the checkout page where buyers will complete their purchases. The [download_checkout] shortcode must be on this page.', 'easy-digital-downloads' ),
						'type'        => 'select',
						'options'     => edd_get_pages(),
						'chosen'      => true,
						'placeholder' => __( 'Select a page', 'easy-digital-downloads' ),
					),
					'success_page' => array(
						'id'          => 'success_page',
						'name'        => __( 'Success Page', 'easy-digital-downloads' ),
						'desc'        => __( 'This is the page buyers are sent to after completing their purchases. The [edd_receipt] shortcode should be on this page.', 'easy-digital-downloads' ),
						'type'        => 'select',
						'options'     => edd_get_pages(),
						'chosen'      => true,
						'placeholder' => __( 'Select a page', 'easy-digital-downloads' ),
					),
					'failure_page' => array(
						'id'          => 'failure_page',
						'name'        => __( 'Failed Transaction Page', 'easy-digital-downloads' ),
						'desc'        => __( 'This is the page buyers are sent to if their transaction is cancelled or fails.', 'easy-digital-downloads' ),
						'type'        => 'select',
						'options'     => edd_get_pages(),
						'chosen'      => true,
						'placeholder' => __( 'Select a page', 'easy-digital-downloads' ),
					),
					'purchase_history_page' => array(
						'id'          => 'purchase_history_page',
						'name'        => __( 'Purchase History Page', 'easy-digital-downloads' ),
						'desc'        => __( 'This page shows a complete purchase history for the current user, including download links. The [purchase_history] shortcode should be on this page.', 'easy-digital-downloads' ),
						'type'        => 'select',
						'options'     => edd_get_pages(),
						'chosen'      => true,
						'placeholder' => __( 'Select a page', 'easy-digital-downloads' ),
					),
					'login_redirect_page' => array(
						'id'          => 'login_redirect_page',
						'name'        => __( 'Login Redirect Page', 'easy-digital-downloads' ),
						'desc'        => sprintf(
								__( 'This is the page where buyers will be redirected by default once they log in. The [edd_login redirect="%s"] shortcode with the redirect attribute can override this setting.', 'easy-digital-downloads' ), trailingslashit( home_url() )
						),
						'type'        => 'select',
						'options'     => edd_get_pages(),
						'chosen'      => true,
						'placeholder' => __( 'Select a page', 'easy-digital-downloads' ),
					),
					'locale_settings' => array(
						'id'            => 'locale_settings',
						'name'          => '<h3>' . __( 'Store Location', 'easy-digital-downloads' ) . '</h3>',
						'desc'          => '',
						'type'          => 'header',
						'tooltip_title' => __( 'Store Location Settings', 'easy-digital-downloads' ),
						'tooltip_desc'  => __( 'Easy Digital Downloads will use the following Country and State to pre-fill fields at checkout. This will also pre-calculate any taxes defined if the location below has taxes enabled.','easy-digital-downloads' ),
					),
					'base_country' => array(
						'id'          => 'base_country',
						'name'        => __( 'Base Country', 'easy-digital-downloads' ),
						'desc'        => __( 'Where does your store operate from?', 'easy-digital-downloads' ),
						'type'        => 'select',
						'options'     => edd_get_country_list(),
						'chosen'      => true,
						'placeholder' => __( 'Select a country', 'easy-digital-downloads' ),
					),
					'base_state' => array(
						'id'          => 'base_state',
						'name'        => __( 'Base State / Province', 'easy-digital-downloads' ),
						'desc'        => __( 'What state / province does your store operate from?', 'easy-digital-downloads' ),
						'type'        => 'shop_states',
						'chosen'      => true,
						'placeholder' => __( 'Select a state', 'easy-digital-downloads' ),
					),
					'tracking_settings' => array(
						'id'   => 'tracking_settings',
						'name' => '<h3>' . __( 'Tracking Settings', 'easy-digital-downloads' ) . '</h3>',
						'desc' => '',
						'type' => 'header',
					),
					'allow_tracking' => array(
						'id'   => 'allow_tracking',
						'name' => __( 'Allow Usage Tracking?', 'easy-digital-downloads' ),
						'desc' => sprintf(
							__( 'Allow Easy Digital Downloads to anonymously track how this plugin is used and help us make the plugin better. Opt-in to tracking and our newsletter and immediately be emailed a 20&#37; discount to the EDD shop, valid towards the <a href="%s" target="_blank">purchase of extensions</a>. No sensitive data is tracked.', 'easy-digital-downloads' ),
							'https://easydigitaldownloads.com/downloads/?utm_source=' . substr( md5( get_bloginfo( 'name' ) ), 0, 10 ) . '&utm_medium=admin&utm_term=settings&utm_campaign=EDDUsageTracking'
						),
						'type' => 'checkbox',
					),
				),
				'currency' => array(
					'currency_settings' => array(
						'id'   => 'currency_settings',
						'name' => '<h3>' . __( 'Currency Settings', 'easy-digital-downloads' ) . '</h3>',
						'desc' => '',
						'type' => 'header',
					),
					'currency' => array(
						'id'      => 'currency',
						'name'    => __( 'Currency', 'easy-digital-downloads' ),
						'desc'    => __( 'Choose your currency. Note that some payment gateways have currency restrictions.', 'easy-digital-downloads' ),
						'type'    => 'select',
						'options' => edd_get_currencies(),
						'chosen'  => true,
					),
					'currency_position' => array(
						'id'      => 'currency_position',
						'name'    => __( 'Currency Position', 'easy-digital-downloads' ),
						'desc'    => __( 'Choose the location of the currency sign.', 'easy-digital-downloads' ),
						'type'    => 'select',
						'options' => array(
							'before' => __( 'Before - $10', 'easy-digital-downloads' ),
							'after'  => __( 'After - 10$', 'easy-digital-downloads' ),
						),
					),
					'thousands_separator' => array(
						'id'   => 'thousands_separator',
						'name' => __( 'Thousands Separator', 'easy-digital-downloads' ),
						'desc' => __( 'The symbol (usually , or .) to separate thousands', 'easy-digital-downloads' ),
						'type' => 'text',
						'size' => 'small',
						'std'  => ',',
					),
					'decimal_separator' => array(
						'id'   => 'decimal_separator',
						'name' => __( 'Decimal Separator', 'easy-digital-downloads' ),
						'desc' => __( 'The symbol (usually , or .) to separate decimal points', 'easy-digital-downloads' ),
						'type' => 'text',
						'size' => 'small',
						'std'  => '.',
					),
				),
				'api' => array(
					'api_settings' => array(
						'id'   => 'api_settings',
						'name' => '<h3>' . __( 'API Settings', 'easy-digital-downloads' ) . '</h3>',
						'desc' => '',
						'type' => 'header',
						'tooltip_title' => __( 'API Settings', 'easy-digital-downloads' ),
						'tooltip_desc'  => __( 'The Easy Digital Downloads REST API provides access to store data through our API endpoints. Enable this setting if you would like all user accounts to be able to generate their own API keys.', 'easy-digital-downloads' ),
					),
					'api_allow_user_keys' => array(
						'id'   => 'api_allow_user_keys',
						'name' => __( 'Allow User Keys', 'easy-digital-downloads' ),
						'desc' => __( 'Check this box to allow all users to generate API keys. Users with the \'manage_shop_settings\' capability are always allowed to generate keys.', 'easy-digital-downloads' ),
						'type' => 'checkbox',
					),
					'api_help' => array(
						'id'   => 'api_help',
						'desc' => sprintf( __( 'Visit the <a href="%s" target="_blank">REST API documentation</a> for further information.', 'easy-digital-downloads' ), 'http://docs.easydigitaldownloads.com/article/1131-edd-rest-api-introduction' ),
						'type' => 'descriptive_text',
					),
				),
			)
		),
		/** Payment Gateways Settings */
		'gateways' => apply_filters('edd_settings_gateways',
			array(
				'main' => array(
					'gateway_settings' => array(
						'id'   => 'api_header',
						'name' => '<h3>' . __( 'Gateway Settings', 'easy-digital-downloads' ) . '</h3>',
						'desc' => '',
						'type' => 'header',
					),
					'test_mode' => array(
						'id'   => 'test_mode',
						'name' => __( 'Test Mode', 'easy-digital-downloads' ),
						'desc' => __( 'While in test mode no live transactions are processed. To fully use test mode, you must have a sandbox (test) account for the payment gateway you are testing.', 'easy-digital-downloads' ),
						'type' => 'checkbox',
					),
					'gateways' => array(
						'id'      => 'gateways',
						'name'    => __( 'Payment Gateways', 'easy-digital-downloads' ),
						'desc'    => __( 'Choose the payment gateways you want to enable.', 'easy-digital-downloads' ),
						'type'    => 'gateways',
						'options' => edd_get_payment_gateways(),
					),
					'default_gateway' => array(
						'id'      => 'default_gateway',
						'name'    => __( 'Default Gateway', 'easy-digital-downloads' ),
						'desc'    => __( 'This gateway will be loaded automatically with the checkout page.', 'easy-digital-downloads' ),
						'type'    => 'gateway_select',
						'options' => edd_get_payment_gateways(),
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
						),
					),
				),
			)
		),
		/** Emails Settings */
		'emails' => apply_filters('edd_settings_emails',
			array(
				'main' => array(
					'email_settings_header' => array(
						'id'   => 'email_settings_header',
						'name' => '<h3>' . __( 'Email Settings', 'easy-digital-downloads' ) . '</h3>',
						'type' => 'header',
					),
					'email_template' => array(
						'id'      => 'email_template',
						'name'    => __( 'Email Template', 'easy-digital-downloads' ),
						'desc'    => __( 'Choose a template. Click "Save Changes" then "Preview Purchase Receipt" to see the new template.', 'easy-digital-downloads' ),
						'type'    => 'select',
						'options' => edd_get_email_templates(),
					),
					'email_logo' => array(
						'id'   => 'email_logo',
						'name' => __( 'Logo', 'easy-digital-downloads' ),
						'desc' => __( 'Upload or choose a logo to be displayed at the top of the purchase receipt emails. Displayed on HTML emails only.', 'easy-digital-downloads' ),
						'type' => 'upload',
					),
					'email_settings' => array(
						'id'   => 'email_settings',
						'name' => '',
						'desc' => '',
						'type' => 'hook',
					),
				),
				'purchase_receipts' => array(
					'purchase_receipt_settings' => array(
						'id'   => 'purchase_receipt_settings',
						'name' => '<h3>' . __( 'Purchase Receipts', 'easy-digital-downloads' ) . '</h3>',
						'type' => 'header',
					),
					'from_name' => array(
						'id'   => 'from_name',
						'name' => __( 'From Name', 'easy-digital-downloads' ),
						'desc' => __( 'The name purchase receipts are said to come from. This should probably be your site or shop name.', 'easy-digital-downloads' ),
						'type' => 'text',
						'std'  => get_bloginfo( 'name' ),
					),
					'from_email' => array(
						'id'   => 'from_email',
						'name' => __( 'From Email', 'easy-digital-downloads' ),
						'desc' => __( 'Email to send purchase receipts from. This will act as the "from" and "reply-to" address.', 'easy-digital-downloads' ),
						'type' => 'text',
						'std'  => get_bloginfo( 'admin_email' ),
					),
					'purchase_subject' => array(
						'id'   => 'purchase_subject',
						'name' => __( 'Purchase Email Subject', 'easy-digital-downloads' ),
						'desc' => __( 'Enter the subject line for the purchase receipt email', 'easy-digital-downloads' ),
						'type' => 'text',
						'std'  => __( 'Purchase Receipt', 'easy-digital-downloads' ),
					),
					'purchase_heading' => array(
						'id'   => 'purchase_heading',
						'name' => __( 'Purchase Email Heading', 'easy-digital-downloads' ),
						'desc' => __( 'Enter the heading for the purchase receipt email', 'easy-digital-downloads' ),
						'type' => 'text',
						'std'  => __( 'Purchase Receipt', 'easy-digital-downloads' ),
					),
					'purchase_receipt' => array(
						'id'   => 'purchase_receipt',
						'name' => __( 'Purchase Receipt', 'easy-digital-downloads' ),
						'desc' => __('Enter the text that is sent as purchase receipt email to users after completion of a successful purchase. HTML is accepted. Available template tags:','easy-digital-downloads' ) . '<br/>' . edd_get_emails_tags_list(),
						'type' => 'rich_editor',
						'std'  => __( "Dear", "easy-digital-downloads" ) . " {name},\n\n" . __( "Thank you for your purchase. Please click on the link(s) below to download your files.", "easy-digital-downloads" ) . "\n\n{download_list}\n\n{sitename}",
					),
				),
				'sale_notifications' => array(
					'sale_notification_settings' => array(
						'id'   => 'sale_notification_settings',
						'name' => '<h3>' . __( 'Sale Notifications', 'easy-digital-downloads' ) . '</h3>',
						'type' => 'header',
					),
					'sale_notification_subject' => array(
						'id'   => 'sale_notification_subject',
						'name' => __( 'Sale Notification Subject', 'easy-digital-downloads' ),
						'desc' => __( 'Enter the subject line for the sale notification email', 'easy-digital-downloads' ),
						'type' => 'text',
						'std'  => 'New download purchase - Order #{payment_id}',
					),
					'sale_notification' => array(
						'id'   => 'sale_notification',
						'name' => __( 'Sale Notification', 'easy-digital-downloads' ),
						'desc' => __( 'Enter the text that is sent as sale notification email after completion of a purchase. HTML is accepted. Available template tags:', 'easy-digital-downloads' ) . '<br/>' . edd_get_emails_tags_list(),
						'type' => 'rich_editor',
						'std'  => edd_get_default_sale_notification_email(),
					),
					'admin_notice_emails' => array(
						'id'   => 'admin_notice_emails',
						'name' => __( 'Sale Notification Emails', 'easy-digital-downloads' ),
						'desc' => __( 'Enter the email address(es) that should receive a notification anytime a sale is made, one per line', 'easy-digital-downloads' ),
						'type' => 'textarea',
						'std'  => get_bloginfo( 'admin_email' ),
					),
					'disable_admin_notices' => array(
						'id'   => 'disable_admin_notices',
						'name' => __( 'Disable Admin Notifications', 'easy-digital-downloads' ),
						'desc' => __( 'Check this box if you do not want to receive sales notification emails.', 'easy-digital-downloads' ),
						'type' => 'checkbox',
					),
				),
			)
		),
		/** Styles Settings */
		'styles' => apply_filters('edd_settings_styles',
			array(
				'main' => array(
					'style_settings' => array(
						'id'   => 'style_settings',
						'name' => '<h3>' . __( 'Style Settings', 'easy-digital-downloads' ) . '</h3>',
						'type' => 'header',
					),
					'disable_styles' => array(
						'id'            => 'disable_styles',
						'name'          => __( 'Disable Styles', 'easy-digital-downloads' ),
						'desc'          => __( 'Check this to disable all included styling of buttons, checkout fields, and all other elements.', 'easy-digital-downloads' ),
						'type'          => 'checkbox',
						'tooltip_title' => __( 'Disabling Styles', 'easy-digital-downloads' ),
						'tooltip_desc'  => __( 'If your theme has a complete custom CSS file for Easy Digital Downloads, you may wish to disable our default styles. This is not recommended unless your sure your theme has a complete custom CSS.', 'easy-digital-downloads' ),
					),
					'button_header' => array(
						'id'   => 'button_header',
						'name' => '<strong>' . __( 'Buttons', 'easy-digital-downloads' ) . '</strong>',
						'desc' => __( 'Options for add to cart and purchase buttons', 'easy-digital-downloads' ),
						'type' => 'header',
					),
					'button_style' => array(
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
					),
				),
			)
		),
		/** Taxes Settings */
		'taxes' => apply_filters('edd_settings_taxes',
			array(
				'main' => array(
					'tax_settings' => array(
						'id'   => 'tax_settings',
						'name' => '<h3>' . __( 'Tax Settings', 'easy-digital-downloads' ) . '</h3>',
						'type' => 'header',
					),
					'tax_help' => array(
						'id'   => 'tax_help',
						'name' => __( 'Need help?', 'easy-digital-downloads' ),
						'desc' => sprintf( __( 'Visit the <a href="%s" target="_blank">Tax setup documentation</a> for further information. If you need VAT support, there are options listed on the documentation page.', 'easy-digital-downloads' ), 'http://docs.easydigitaldownloads.com/article/238-tax-settings' ),
						'type' => 'descriptive_text',
					),
					'enable_taxes' => array(
						'id'            => 'enable_taxes',
						'name'          => __( 'Enable Taxes', 'easy-digital-downloads' ),
						'desc'          => __( 'Check this to enable taxes on purchases.', 'easy-digital-downloads' ),
						'type'          => 'checkbox',
						'tooltip_title' => __( 'Enabling Taxes', 'easy-digital-downloads' ),
						'tooltip_desc'  => __( 'With taxes enabled, Easy Digital Downloads will use the rules below to charge tax to customers. With taxes enabled, customers are required to input their address on checkout so that taxes can be properly calculated.', 'easy-digital-downloads' ),
					),
					'tax_rates' => array(
						'id'   => 'tax_rates',
						'name' => '<strong>' . __( 'Tax Rates', 'easy-digital-downloads' ) . '</strong>',
						'desc' => __( 'Add tax rates for specific regions. Enter a percentage, such as 6.5 for 6.5%.', 'easy-digital-downloads' ),
						'type' => 'tax_rates',
					),
					'tax_rate' => array(
						'id'   => 'tax_rate',
						'name' => __( 'Fallback Tax Rate', 'easy-digital-downloads' ),
						'desc' => __( 'Customers not in a specific rate will be charged this tax rate. Enter a percentage, such as 6.5 for 6.5%. ', 'easy-digital-downloads' ),
						'type' => 'text',
						'size' => 'small',
						'tooltip_title' => __( 'Fallback Tax Rate', 'easy-digital-downloads' ),
						'tooltip_desc'  => __( 'If the customer\'s address fails to meet the above tax rules, you can define a `default` tax rate to be applied to all other customers. Enter a percentage, such as 6.5 for 6.5%.', 'easy-digital-downloads' ),
					),
					'prices_include_tax' => array(
						'id'   => 'prices_include_tax',
						'name' => __( 'Prices entered with tax', 'easy-digital-downloads' ),
						'desc' => __( 'This option affects how you enter prices.', 'easy-digital-downloads' ),
						'type' => 'radio',
						'std'  => 'no',
						'options' => array(
							'yes' => __( 'Yes, I will enter prices inclusive of tax', 'easy-digital-downloads' ),
							'no'  => __( 'No, I will enter prices exclusive of tax', 'easy-digital-downloads' ),
						),
						'tooltip_title' => __( 'Prices Inclusive of Tax', 'easy-digital-downloads' ),
						'tooltip_desc'  => __( 'When using prices inclusive of tax, you will be entering your prices as the total amount you want a customer to pay for the download, including tax. Easy Digital Downloads will calculate the proper amount to tax the customer for the defined total price.', 'easy-digital-downloads' ),
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
							'no'  => __( 'Excluding tax', 'easy-digital-downloads' ),
						),
						'tooltip_title' => __( 'Taxes Displayed for Products on Checkout', 'easy-digital-downloads' ),
						'tooltip_desc'  => __( 'This option will determine whether the product price displays with or without tax on checkout.', 'easy-digital-downloads' ),
					),
				),
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
				'main' => array(
					'misc_settings' => array(
						'id'   => 'misc_settings',
						'name' => '<h3>' . __( 'Misc Settings', 'easy-digital-downloads' ) . '</h3>',
						'type' => 'header',
					),
					'redirect_on_add' => array(
						'id'   => 'redirect_on_add',
						'name' => __( 'Redirect to Checkout', 'easy-digital-downloads' ),
						'desc' => __( 'Immediately redirect to checkout after adding an item to the cart?', 'easy-digital-downloads' ),
						'type' => 'checkbox',
						'tooltip_title' => __( 'Redirect to Checkout', 'easy-digital-downloads' ),
						'tooltip_desc'  => __( 'When enabled, once an item has been added to the cart, the customer will be redirected directly to your checkout page. This is useful for stores that sell single items.', 'easy-digital-downloads' ),
					),
					'item_quantities' => array(
						'id'   => 'item_quantities',
						'name' => __('Item Quantities','easy-digital-downloads' ),
						'desc' => __('Allow item quantities to be changed.','easy-digital-downloads' ),
						'type' => 'checkbox',
					),
					'uninstall_on_delete' => array(
						'id'   => 'uninstall_on_delete',
						'name' => __( 'Remove Data on Uninstall?', 'easy-digital-downloads' ),
						'desc' => __( 'Check this box if you would like EDD to completely remove all of its data when the plugin is deleted.', 'easy-digital-downloads' ),
						'type' => 'checkbox',
					),
				),
				'checkout' => array(
					'checkout_settings' => array(
						'id'   => 'checkout_settings',
						'name' => '<h3>' . __( 'Checkout Settings', 'easy-digital-downloads' ) . '</h3>',
						'type' => 'header',
					),
					'enforce_ssl' => array(
						'id'   => 'enforce_ssl',
						'name' => __( 'Enforce SSL on Checkout', 'easy-digital-downloads' ),
						'desc' => __( 'Check this to force users to be redirected to the secure checkout page. You must have an SSL certificate installed to use this option.', 'easy-digital-downloads' ),
						'type' => 'checkbox',
					),
					'logged_in_only' => array(
						'id'   => 'logged_in_only',
						'name' => __( 'Require Login', 'easy-digital-downloads' ),
						'desc' => __( 'Require that users be logged-in to purchase files.', 'easy-digital-downloads' ),
						'type' => 'checkbox',
						'tooltip_title' => __( 'Require Login', 'easy-digital-downloads' ),
						'tooltip_desc'  => __( 'You can require that customers create and login to user accounts prior to purchasing from your store by enabling this option. When unchecked, users can purchase without being logged in by using their name and email address.', 'easy-digital-downloads' ),
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
							'none'         => __( 'None', 'easy-digital-downloads' ),
						),
					),
					'allow_multiple_discounts' => array(
						'id'   => 'allow_multiple_discounts',
						'name' => __('Multiple Discounts','easy-digital-downloads' ),
						'desc' => __('Allow customers to use multiple discounts on the same purchase?','easy-digital-downloads' ),
						'type' => 'checkbox',
					),
					'enable_cart_saving' => array(
						'id'   => 'enable_cart_saving',
						'name' => __( 'Enable Cart Saving', 'easy-digital-downloads' ),
						'desc' => __( 'Check this to enable cart saving on the checkout.', 'easy-digital-downloads' ),
						'type' => 'checkbox',
						'tooltip_title' => __( 'Cart Saving', 'easy-digital-downloads' ),
						'tooltip_desc'  => __( 'Cart saving allows shoppers to create a temporary link to their current shopping cart so they can come back to it later, or share it with someone.', 'easy-digital-downloads' ),
					),
				),
				'button_text' => array(
					'button_settings' => array(
						'id'   => 'button_settings',
						'name' => '<h3>' . __( 'Button Text', 'easy-digital-downloads' ) . '</h3>',
						'type' => 'header',
					),
					'checkout_label' => array(
						'id'   => 'checkout_label',
						'name' => __( 'Complete Purchase Text', 'easy-digital-downloads' ),
						'desc' => __( 'The button label for completing a purchase.', 'easy-digital-downloads' ),
						'type' => 'text',
						'std'  => __( 'Purchase', 'easy-digital-downloads' ),
					),
					'add_to_cart_text' => array(
						'id'   => 'add_to_cart_text',
						'name' => __( 'Add to Cart Text', 'easy-digital-downloads' ),
						'desc' => __( 'Text shown on the Add to Cart Buttons.', 'easy-digital-downloads' ),
						'type' => 'text',
						'std'  => __( 'Add to Cart', 'easy-digital-downloads' ),
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
					'file_settings' => array(
						'id'   => 'file_settings',
						'name' => '<h3>' . __( 'File Download Settings', 'easy-digital-downloads' ) . '</h3>',
						'type' => 'header',
					),
					'download_method' => array(
						'id'      => 'download_method',
						'name'    => __( 'Download Method', 'easy-digital-downloads' ),
						'desc'    => sprintf( __( 'Select the file download method. Note, not all methods work on all servers.', 'easy-digital-downloads' ), edd_get_label_singular() ),
						'type'    => 'select',
						'tooltip_title' => __( 'Download Method', 'easy-digital-downloads' ),
						'tooltip_desc' => __( 'Due to its consistency in multiple platforms and better file protection, \'forced\' is the default method. Because Easy Digital Downloads uses PHP to process the file with the \'forced\' method, larger files can cause problems with delivery, resulting in hitting the \'max execution time\' of the server. If users are getting 404 or 403 errors when trying to access their purchased files when using the \'forced\' method, changing to the \'redirect\' method can help resolve this.', 'easy-digital-downloads' ),
						'options' => array(
							'direct'   => __( 'Forced', 'easy-digital-downloads' ),
							'redirect' => __( 'Redirect', 'easy-digital-downloads' ),
						),
					),
					'symlink_file_downloads' => array(
						'id'   => 'symlink_file_downloads',
						'name' => __( 'Symlink File Downloads?', 'easy-digital-downloads' ),
						'desc' => __( 'Check this if you are delivering really large files or having problems with file downloads completing.', 'easy-digital-downloads' ),
						'type' => 'checkbox',
					),
					'file_download_limit' => array(
						'id'   => 'file_download_limit',
						'name' => __( 'File Download Limit', 'easy-digital-downloads' ),
						'desc' => sprintf( __( 'The maximum number of times files can be downloaded for purchases. Can be overwritten for each %s.', 'easy-digital-downloads' ), edd_get_label_singular() ),
						'type' => 'number',
						'size' => 'small',
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
						'name' => __( 'Disable Redownload?', 'easy-digital-downloads' ),
						'desc' => __( 'Check this if you do not want to allow users to redownload items from their purchase history.', 'easy-digital-downloads' ),
						'type' => 'checkbox',
					),
				),
				'accounting'     => array(
					'accounting_settings' => array(
						'id'   => 'accounting_settings',
						'name' => '<h3>' . __( 'Accounting Settings', 'easy-digital-downloads' ) . '</h3>',
						'type' => 'header',
					),
					'enable_skus' => array(
						'id'   => 'enable_skus',
						'name' => __( 'Enable SKU Entry', 'easy-digital-downloads' ),
						'desc' => __( 'Check this box to allow entry of product SKUs. SKUs will be shown on purchase receipt and exported purchase histories.', 'easy-digital-downloads' ),
						'type' => 'checkbox',
					),
					'enable_sequential' => array(
						'id'   => 'enable_sequential',
						'name' => __( 'Sequential Order Numbers', 'easy-digital-downloads' ),
						'desc' => __( 'Check this box to enable sequential order numbers.', 'easy-digital-downloads' ),
						'type' => 'checkbox',
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
					),
				),
				'site_terms'     => array(
					'terms_settings' => array(
						'id'   => 'terms_settings',
						'name' => '<h3>' . __( 'Agreement Settings', 'easy-digital-downloads' ) . '</h3>',
						'type' => 'header',
					),
					'show_agree_to_terms' => array(
						'id'   => 'show_agree_to_terms',
						'name' => __( 'Agree to Terms', 'easy-digital-downloads' ),
						'desc' => __( 'Check this to show an agree to terms on the checkout that users must agree to before purchasing.', 'easy-digital-downloads' ),
						'type' => 'checkbox',
					),
					'agree_label' => array(
						'id'   => 'agree_label',
						'name' => __( 'Agree to Terms Label', 'easy-digital-downloads' ),
						'desc' => __( 'Label shown next to the agree to terms check box.', 'easy-digital-downloads' ),
						'type' => 'text',
						'size' => 'regular',
					),
					'agree_text' => array(
						'id'   => 'agree_text',
						'name' => __( 'Agreement Text', 'easy-digital-downloads' ),
						'desc' => __( 'If Agree to Terms is checked, enter the agreement terms here.', 'easy-digital-downloads' ),
						'type' => 'rich_editor',
					),
				),
			)
		)
	);

	if ( ! edd_shop_supports_buy_now() ) {
		$edd_settings['misc']['button_text']['buy_now_text']['disabled']      = true;
		$edd_settings['misc']['button_text']['buy_now_text']['tooltip_title'] = __( 'Buy Now Disabled', 'easy-digital-downloads' );
		$edd_settings['misc']['button_text']['buy_now_text']['tooltip_desc']  = __( 'Buy Now buttons are only available for stores that have a single supported gateway active and that do not use taxes.', 'easy-digital-downloads' );
	}

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
 * @global $edd_options Array of all the EDD Options
 *
 * @return string $input Sanitizied value
 */
function edd_settings_sanitize( $input = array() ) {
	global $edd_options;

	$doing_section = false;
	if ( ! empty( $_POST['_wp_http_referer'] ) ) {
		$doing_section = true;
	}

	$setting_types = edd_get_registered_settings_types();
	$input         = $input ? $input : array();

	if ( $doing_section ) {

		parse_str( $_POST['_wp_http_referer'], $referrer ); // Pull out the tab and section
		$tab      = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';
		$section  = isset( $referrer['section'] ) ? $referrer['section'] : 'main';

		// Run a general sanitization for the tab for special fields (like taxes)
		$input = apply_filters( 'edd_settings_' . $tab . '_sanitize', $input );

		// Run a general sanitization for the section so custom tabs with sub-sections can save special data
		$input = apply_filters( 'edd_settings_' . $tab . '-' . $section . '_sanitize', $input );

	}

	// Merge our new settings with the existing
	$output = array_merge( $edd_options, $input );

	foreach ( $setting_types as $key => $type ) {

		if ( empty( $type ) ) {
			continue;
		}

		// Some setting types are not actually settings, just keep moving along here
		$non_setting_types = apply_filters( 'edd_non_setting_types', array(
			'header', 'descriptive_text', 'hook',
		) );

		if ( in_array( $type, $non_setting_types ) ) {
			continue;
		}

		if ( array_key_exists( $key, $output ) ) {
			$output[ $key ] = apply_filters( 'edd_settings_sanitize_' . $type, $output[ $key ], $key );
			$output[ $key ] = apply_filters( 'edd_settings_sanitize', $output[ $key ], $key );
		}

		if ( $doing_section ) {
			switch( $type ) {
				case 'checkbox':
				case 'gateways':
				case 'multicheck':
				case 'payment_icons':
					if ( array_key_exists( $key, $input ) && $output[ $key ] === '-1' ) {
						unset( $output[ $key ] );
					}
					break;
				default:
					if ( array_key_exists( $key, $input ) && empty( $input[ $key ] ) ) {
						unset( $output[ $key ] );
					}
					break;
			}
		} else {
			if ( empty( $input[ $key ] ) ) {
				unset( $output[ $key ] );
			}
		}

	}

	if ( $doing_section ) {
		add_settings_error( 'edd-notices', '', __( 'Settings updated.', 'easy-digital-downloads' ), 'updated' );
	}

	return $output;
}

/**
 * Flattens the set of registered settings and their type so we can easily sanitize all the settings
 * in a much cleaner set of logic in edd_settings_sanitize
 *
 * @since  2.6.5
 * @return array Key is the setting ID, value is the type of setting it is registered as
 */
function edd_get_registered_settings_types() {
	$settings      = edd_get_registered_settings();
	$setting_types = array();

	foreach ( $settings as $tab ) {

		foreach ( $tab as $section_or_setting ) {

			// See if we have a setting registered at the tab level for backwards compatibility
			if ( is_array( $section_or_setting ) && array_key_exists( 'type', $section_or_setting ) ) {
				$setting_types[ $section_or_setting['id'] ] = $section_or_setting['type'];
				continue;
			}

			foreach ( $section_or_setting as $section => $section_settings ) {
				$setting_types[ $section_settings['id'] ] = $section_settings['type'];
			}
		}

	}

	return $setting_types;
}

/**
 * Misc File Download Settings Sanitization
 *
 * @since 2.5
 * @param array $input The value inputted in the field
 * @return string $input Sanitizied value
 */
function edd_settings_sanitize_misc_file_downloads( $input ) {

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return $input;
	}

	if( edd_get_file_download_method() != $input['download_method'] || ! edd_htaccess_exists() ) {
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
 * @param array $input The value inputted in the field
 * @return string $input Sanitizied value
 */
function edd_settings_sanitize_misc_accounting( $input ) {

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return $input;
	}

	if( ! empty( $input['enable_sequential'] ) && ! edd_get_option( 'enable_sequential' ) ) {

		// Shows an admin notice about upgrading previous order numbers
		EDD()->session->set( 'upgrade_sequential', '1' );

	}

	return $input;
}
add_filter( 'edd_settings_misc-accounting_sanitize', 'edd_settings_sanitize_misc_accounting' );

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

	if( ! isset( $_POST['tax_rates'] ) ) {
		return $input;
	}

	$new_rates = ! empty( $_POST['tax_rates'] ) ? array_values( $_POST['tax_rates'] ) : array();

	update_option( 'edd_tax_rates', $new_rates );

	return $input;
}
add_filter( 'edd_settings_taxes_sanitize', 'edd_settings_sanitize_taxes' );

/**
 * Payment Gateways Settings Sanitization
 *
 * Adds a settings error (for the updated message)
 *
 * @since 2.7
 * @param array $input The value inputted in the field
 * @return string $input Sanitizied value
 */
function edd_settings_sanitize_gateways( $input ) {

	if ( ! current_user_can( 'manage_shop_settings' ) || empty( $input['default_gateway'] ) ) {
		return $input;
	}

	if ( empty( $input['gateways'] ) || '-1' == $input['gateways'] )  {

		add_settings_error( 'edd-notices', '', __( 'Error setting default gateway. No gateways are enabled.', 'easy-digital-downloads' ) );
		unset( $input['default_gateway'] );

	} else if ( ! array_key_exists( $input['default_gateway'], $input['gateways'] ) ) {

		$enabled_gateways = $input['gateways'];
		$all_gateways     = edd_get_payment_gateways();
		$selected_default = $all_gateways[ $input['default_gateway'] ];

		reset( $enabled_gateways );
		$first_gateway = key( $enabled_gateways );

		if ( $first_gateway ) {
			add_settings_error( 'edd-notices', '', sprintf( __( '%s could not be set as the default gateway. It must first be enabled.', 'easy-digital-downloads' ), $selected_default['admin_label'] ), 'error' );
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
 * @param array $input The field value
 * @return string $input Sanitizied value
 */
function edd_sanitize_text_field( $input ) {
	$tags = array(
		'p' => array(
			'class' => array(),
			'id'    => array(),
		),
		'span' => array(
			'class' => array(),
			'id'    => array(),
		),
		'a' => array(
			'href' => array(),
			'title' => array(),
			'class' => array(),
			'title' => array(),
			'id'    => array(),
		),
		'strong' => array(),
		'em' => array(),
		'br' => array(),
		'img' => array(
			'src'   => array(),
			'title' => array(),
			'alt'   => array(),
			'id'    => array(),
		),
		'div' => array(
			'class' => array(),
			'id'    => array(),
		),
		'ul' => array(
			'class' => array(),
			'id'    => array(),
		),
		'li' => array(
			'class' => array(),
			'id'    => array(),
		)
	);

	$allowed_tags = apply_filters( 'edd_allowed_html_tags', $tags );

	return trim( wp_kses( $input, $allowed_tags ) );
}
add_filter( 'edd_settings_sanitize_text', 'edd_sanitize_text_field' );

/**
 * Sanitize HTML Class Names
 *
 * @since 2.6.11
 * @param  string|array $class HTML Class Name(s)
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
 * Retrieve settings tabs
 *
 * @since 2.5
 * @return array $section
 */
function edd_get_settings_tab_sections( $tab = false ) {

	$tabs     = false;
	$sections = edd_get_registered_settings_sections();

	if( $tab && ! empty( $sections[ $tab ] ) ) {
		$tabs = $sections[ $tab ];
	} else if ( $tab ) {
		$tabs = false;
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

	static $sections = false;

	if ( false !== $sections ) {
		return $sections;
	}

	$sections = array(
		'general'    => apply_filters( 'edd_settings_sections_general', array(
			'main'               => __( 'General Settings', 'easy-digital-downloads' ),
			'currency'           => __( 'Currency Settings', 'easy-digital-downloads' ),
			'api'                => __( 'API Settings', 'easy-digital-downloads' ),
		) ),
		'gateways'   => apply_filters( 'edd_settings_sections_gateways', array(
			'main'               => __( 'Gateway Settings', 'easy-digital-downloads' ),
			'paypal'             => __( 'PayPal Standard', 'easy-digital-downloads' ),
		) ),
		'emails'     => apply_filters( 'edd_settings_sections_emails', array(
			'main'               => __( 'Email Settings', 'easy-digital-downloads' ),
			'purchase_receipts'  => __( 'Purchase Receipts', 'easy-digital-downloads' ),
			'sale_notifications' => __( 'New Sale Notifications', 'easy-digital-downloads' ),
		) ),
		'styles'     => apply_filters( 'edd_settings_sections_styles', array(
			'main'               => __( 'Style Settings', 'easy-digital-downloads' ),
		) ),
		'taxes'      => apply_filters( 'edd_settings_sections_taxes', array(
			'main'               => __( 'Tax Settings', 'easy-digital-downloads' ),
		) ),
		'extensions' => apply_filters( 'edd_settings_sections_extensions', array(
			'main'               => __( 'Main', 'easy-digital-downloads' )
		) ),
		'licenses'   => apply_filters( 'edd_settings_sections_licenses', array() ),
		'misc'       => apply_filters( 'edd_settings_sections_misc', array(
			'main'               => __( 'Misc Settings', 'easy-digital-downloads' ),
			'checkout'           => __( 'Checkout Settings', 'easy-digital-downloads' ),
			'button_text'        => __( 'Button Text', 'easy-digital-downloads' ),
			'file_downloads'     => __( 'File Downloads', 'easy-digital-downloads' ),
			'accounting'         => __( 'Accounting Settings', 'easy-digital-downloads' ),
			'site_terms'         => __( 'Terms of Agreement', 'easy-digital-downloads' ),
		) ),
	);

	$sections = apply_filters( 'edd_settings_sections', $sections );

	return $sections;
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
	echo apply_filters( 'edd_after_setting_output', '', $args );
}

/**
 * Checkbox Callback
 *
 * Renders checkboxes.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_checkbox_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$name = '';
	} else {
		$name = 'name="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']"';
	}

	$class = edd_sanitize_html_class( $args['field_class'] );

	$checked  = ! empty( $edd_option ) ? checked( 1, $edd_option, false ) : '';
	$html     = '<input type="hidden"' . $name . ' value="-1" />';
	$html    .= '<input type="checkbox" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']"' . $name . ' value="1" ' . $checked . ' class="' . $class . '"/>';
	$html    .= '<label for="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Multicheck Callback
 *
 * Renders multiple checkboxes.
 *
 * @since 1.0
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
		foreach( $args['options'] as $key => $option ):
			if( isset( $edd_option[ $key ] ) ) { $enabled = $option; } else { $enabled = NULL; }
			$html .= '<input name="edd_settings[' . edd_sanitize_key( $args['id'] ) . '][' . edd_sanitize_key( $key ) . ']" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . '][' . edd_sanitize_key( $key ) . ']" class="' . $class . '" type="checkbox" value="' . esc_attr( $option ) . '" ' . checked($option, $enabled, false) . '/>&nbsp;';
			$html .= '<label for="edd_settings[' . edd_sanitize_key( $args['id'] ) . '][' . edd_sanitize_key( $key ) . ']">' . wp_kses_post( $option ) . '</label><br/>';
		endforeach;
		$html .= '<p class="description">' . $args['desc'] . '</p>';
	}

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Payment method icons callback
 *
 * @since 2.1
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_payment_icons_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	$class = edd_sanitize_html_class( $args['field_class'] );

	$html = '<input type="hidden" name="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" value="-1" />';
	if ( ! empty( $args['options'] ) ) {
		foreach( $args['options'] as $key => $option ) {

			if( isset( $edd_option[ $key ] ) ) {
				$enabled = $option;
			} else {
				$enabled = NULL;
			}

			$html .= '<label for="edd_settings[' . edd_sanitize_key( $args['id'] ) . '][' . edd_sanitize_key( $key ) . ']" style="margin-right:10px;line-height:16px;height:16px;display:inline-block;">';

				$html .= '<input name="edd_settings[' . edd_sanitize_key( $args['id'] ) . '][' . edd_sanitize_key( $key ) . ']" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . '][' . edd_sanitize_key( $key ) . ']" class="' . $class . '" type="checkbox" value="' . esc_attr( $option ) . '" ' . checked( $option, $enabled, false ) . '/>&nbsp;';

				if( edd_string_is_image_url( $key ) ) {

					$html .= '<img class="payment-icon" src="' . esc_url( $key ) . '" style="width:32px;height:24px;position:relative;top:6px;margin-right:5px;"/>';

				} else {

					$card = strtolower( str_replace( ' ', '', $option ) );

					if( has_filter( 'edd_accepted_payment_' . $card . '_image' ) ) {

						$image = apply_filters( 'edd_accepted_payment_' . $card . '_image', '' );

					} else {

						$image       = edd_locate_template( 'images' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR . $card . '.png', false );
						$content_dir = WP_CONTENT_DIR;

						if( function_exists( 'wp_normalize_path' ) ) {

							// Replaces backslashes with forward slashes for Windows systems
							$image = wp_normalize_path( $image );
							$content_dir = wp_normalize_path( $content_dir );

						}

						$image = str_replace( $content_dir, content_url(), $image );

					}

					$html .= '<img class="payment-icon" src="' . esc_url( $image ) . '" style="width:32px;height:24px;position:relative;top:6px;margin-right:5px;"/>';
				}


			$html .= $option . '</label>';

		}
		$html .= '<p class="description" style="margin-top:16px;">' . wp_kses_post( $args['desc'] ) . '</p>';
	}

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Radio Callback
 *
 * Renders radio boxes.
 *
 * @since 1.3.3
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

		if ( $edd_options && $edd_options == $key )
			$checked = true;
		elseif( isset( $args['std'] ) && $args['std'] == $key && ! $edd_options )
			$checked = true;

		$html .= '<input name="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . '][' . edd_sanitize_key( $key ) . ']" class="' . $class . '" type="radio" value="' . edd_sanitize_key( $key ) . '" ' . checked(true, $checked, false) . '/>&nbsp;';
		$html .= '<label for="edd_settings[' . edd_sanitize_key( $args['id'] ) . '][' . edd_sanitize_key( $key ) . ']">' . esc_html( $option ) . '</label><br/>';
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
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_gateways_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	$class = edd_sanitize_html_class( $args['field_class'] );

	$html = '<input type="hidden" name="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" value="-1" />';

	foreach ( $args['options'] as $key => $option ) :
		if ( isset( $edd_option[ $key ] ) )
			$enabled = '1';
		else
			$enabled = null;

		$html .= '<input name="edd_settings[' . esc_attr( $args['id'] ) . '][' . edd_sanitize_key( $key ) . ']" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . '][' . edd_sanitize_key( $key ) . ']" class="' . $class . '" type="checkbox" value="1" ' . checked('1', $enabled, false) . '/>&nbsp;';
		$html .= '<label for="edd_settings[' . edd_sanitize_key( $args['id'] ) . '][' . edd_sanitize_key( $key ) . ']">' . esc_html( $option['admin_label'] ) . '</label><br/>';
	endforeach;

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Gateways Callback (drop down)
 *
 * Renders gateways select menu
 *
 * @since 1.5
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_gateway_select_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	$class = edd_sanitize_html_class( $args['field_class'] );

	$html = '';

	$html .= '<select name="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']"" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" class="' . $class . '">';

	foreach ( $args['options'] as $key => $option ) :
		$selected = isset( $edd_option ) ? selected( $key, $edd_option, false ) : '';
		$html .= '<option value="' . edd_sanitize_key( $key ) . '"' . $selected . '>' . esc_html( $option['admin_label'] ) . '</option>';
	endforeach;

	$html .= '</select>';
	$html .= '<label for="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Text Callback
 *
 * Renders text fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_text_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	if ( $edd_option ) {
		$value = $edd_option;
	} elseif( ! empty( $args['allow_blank'] ) && empty( $edd_option ) ) {
		$value = '';
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$args['readonly'] = true;
		$value = isset( $args['std'] ) ? $args['std'] : '';
		$name  = '';
	} else {
		$name = 'name="edd_settings[' . esc_attr( $args['id'] ) . ']"';
	}

	$class = edd_sanitize_html_class( $args['field_class'] );

	$disabled = ! empty( $args['disabled'] ) ? ' disabled="disabled"' : '';
	$readonly = $args['readonly'] === true ? ' readonly="readonly"' : '';
	$size     = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html     = '<input type="text" class="' . $class . ' ' . sanitize_html_class( $size ) . '-text" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"' . $readonly . $disabled . ' placeholder="' . esc_attr( $args['placeholder'] ) . '"/>';
	$html    .= '<label for="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Number Callback
 *
 * Renders number fields.
 *
 * @since 1.9
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_number_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	if ( $edd_option ) {
		$value = $edd_option;
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$args['readonly'] = true;
		$value = isset( $args['std'] ) ? $args['std'] : '';
		$name  = '';
	} else {
		$name = 'name="edd_settings[' . esc_attr( $args['id'] ) . ']"';
	}

	$class = edd_sanitize_html_class( $args['field_class'] );

	$max  = isset( $args['max'] ) ? $args['max'] : 999999;
	$min  = isset( $args['min'] ) ? $args['min'] : 0;
	$step = isset( $args['step'] ) ? $args['step'] : 1;

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . $class . ' ' . sanitize_html_class( $size ) . '-text" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<label for="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Textarea Callback
 *
 * Renders textarea fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_textarea_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	if ( $edd_option ) {
		$value = $edd_option;
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$class = edd_sanitize_html_class( $args['field_class'] );

	$html = '<textarea class="' . $class . ' large-text" cols="50" rows="5" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" name="edd_settings[' . esc_attr( $args['id'] ) . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	$html .= '<label for="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Password Callback
 *
 * Renders password fields.
 *
 * @since 1.3
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

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="password" class="' . $class . ' ' . sanitize_html_class( $size ) . '-text" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" name="edd_settings[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( $value ) . '"/>';
	$html .= '<label for="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']"> ' . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
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
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_select_callback($args) {
	$edd_option = edd_get_option( $args['id'] );

	if ( $edd_option ) {
		$value = $edd_option;
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['placeholder'] ) ) {
		$placeholder = $args['placeholder'];
	} else {
		$placeholder = '';
	}

	$class = edd_sanitize_html_class( $args['field_class'] );

	if ( isset( $args['chosen'] ) ) {
		$class .= ' edd-chosen';
	}

	$html = '<select id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" name="edd_settings[' . esc_attr( $args['id'] ) . ']" class="' . $class . '" data-placeholder="' . esc_html( $placeholder ) . '" />';

	foreach ( $args['options'] as $option => $name ) {
		$selected = selected( $option, $value, false );
		$html .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
	}

	$html .= '</select>';
	$html .= '<label for="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']"> ' . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Color select Callback
 *
 * Renders color select fields.
 *
 * @since 1.8
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

	$html = '<select id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" class="' . $class . '" name="edd_settings[' . esc_attr( $args['id'] ) . ']"/>';

	foreach ( $args['options'] as $option => $color ) {
		$selected = selected( $option, $value, false );
		$html .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( $color['label'] ) . '</option>';
	}

	$html .= '</select>';
	$html .= '<label for="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Rich Editor Callback
 *
 * Renders rich editor fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 */
function edd_rich_editor_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	if ( $edd_option ) {
		$value = $edd_option;
	} else {
		if( ! empty( $args['allow_blank'] ) && empty( $edd_option ) ) {
			$value = '';
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}
	}

	$rows = isset( $args['size'] ) ? $args['size'] : 20;

	$class = edd_sanitize_html_class( $args['field_class'] );

	ob_start();
	wp_editor( stripslashes( $value ), 'edd_settings_' . esc_attr( $args['id'] ), array( 'textarea_name' => 'edd_settings[' . esc_attr( $args['id'] ) . ']', 'textarea_rows' => absint( $rows ), 'editor_class' => $class ) );
	$html = ob_get_clean();

	$html .= '<br/><label for="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']"> ' . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Upload Callback
 *
 * Renders upload fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_upload_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	if ( $edd_option ) {
		$value = $edd_option;
	} else {
		$value = isset($args['std']) ? $args['std'] : '';
	}

	$class = edd_sanitize_html_class( $args['field_class'] );

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="' . sanitize_html_class( $size ) . '-text" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" clas="' . $class . '" name="edd_settings[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<span>&nbsp;<input type="button" class="edd_settings_upload_button button-secondary" value="' . __( 'Upload File', 'easy-digital-downloads' ) . '"/></span>';
	$html .= '<label for="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']"> ' . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}


/**
 * Color picker Callback
 *
 * Renders color picker fields.
 *
 * @since 1.6
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

	$html = '<input type="text" class="' . $class . ' edd-color-picker" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" name="edd_settings[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( $value ) . '" data-default-color="' . esc_attr( $default ) . '" />';
	$html .= '<label for="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Shop States Callback
 *
 * Renders states drop down based on the currently selected country
 *
 * @since 1.6
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_shop_states_callback($args) {
	$edd_option = edd_get_option( $args['id'] );

	if ( isset( $args['placeholder'] ) ) {
		$placeholder = $args['placeholder'];
	} else {
		$placeholder = '';
	}

	$class = edd_sanitize_html_class( $args['field_class'] );

	$states = edd_get_shop_states();

	if ( $args['chosen'] ) {
		$class .= ' edd-chosen';
	}

	if ( empty( $states ) ) {
		$class .= ' edd-no-states';
	}

	$html = '<select id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" name="edd_settings[' . esc_attr( $args['id'] ) . ']"' . $class . 'data-placeholder="' . esc_html( $placeholder ) . '"/>';

	foreach ( $states as $option => $name ) {
		$selected = isset( $edd_option ) ? selected( $option, $edd_option, false ) : '';
		$html .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
	}

	$html .= '</select>';
	$html .= '<label for="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Tax Rates Callback
 *
 * Renders tax rates table
 *
 * @since 1.6
 * @param array $args Arguments passed by the setting
 * @return void
 */
function edd_tax_rates_callback($args) {
	$rates = edd_get_tax_rates();

	$class = edd_sanitize_html_class( $args['field_class'] );

	ob_start(); ?>
	<p><?php echo $args['desc']; ?></p>
	<table id="edd_tax_rates" class="wp-list-table widefat fixed posts <?php echo $class; ?>">
		<thead>
			<tr>
				<th scope="col" class="edd_tax_country"><?php _e( 'Country', 'easy-digital-downloads' ); ?></th>
				<th scope="col" class="edd_tax_state"><?php _e( 'State / Province', 'easy-digital-downloads' ); ?></th>
				<th scope="col" class="edd_tax_global"><?php _e( 'Country Wide', 'easy-digital-downloads' ); ?></th>
				<th scope="col" class="edd_tax_rate"><?php _e( 'Rate', 'easy-digital-downloads' ); ?><span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Regional tax rates: </strong>When a customer enters an address on checkout that matches the specified region for this tax rate, the cart tax will adjust automatically. Enter a percentage, such as 6.5 for 6.5%.' ); ?>"></span></th>
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
						'name'             => 'tax_rates[' . edd_sanitize_key( $key ) . '][country]',
						'selected'         => $rate['country'],
						'show_option_all'  => false,
						'show_option_none' => false,
						'class'            => 'edd-tax-country',
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
							'name'             => 'tax_rates[' . edd_sanitize_key( $key ) . '][state]',
							'selected'         => $rate['state'],
							'show_option_all'  => false,
							'show_option_none' => false,
							'chosen'           => false,
							'placeholder'      => __( 'Choose a state', 'easy-digital-downloads' )
						) );
					} else {
						echo EDD()->html->text( array(
							'name'  => 'tax_rates[' . edd_sanitize_key( $key ) . '][state]', $rate['state'],
							'value' => ! empty( $rate['state'] ) ? $rate['state'] : '',
						) );
					}
					?>
				</td>
				<td class="edd_tax_global">
					<input type="checkbox" name="tax_rates[<?php echo edd_sanitize_key( $key ); ?>][global]" id="tax_rates[<?php echo edd_sanitize_key( $key ); ?>][global]" value="1"<?php checked( true, ! empty( $rate['global'] ) ); ?>/>
					<label for="tax_rates[<?php echo edd_sanitize_key( $key ); ?>][global]"><?php _e( 'Apply to whole country', 'easy-digital-downloads' ); ?></label>
				</td>
				<td class="edd_tax_rate"><input type="number" class="small-text" step="0.0001" min="0.0" max="99" name="tax_rates[<?php echo edd_sanitize_key( $key ); ?>][rate]" value="<?php echo esc_html( $rate['rate'] ); ?>"/></td>
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
						'selected'         => '',
						'show_option_all'  => false,
						'show_option_none' => false,
						'class'            => 'edd-tax-country',
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
	$html = wp_kses_post( $args['desc'] );

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Registers the license field callback for Software Licensing
 *
 * @since 1.5
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
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		if( ! empty( $license ) && is_object( $license ) ) {

			// activate_license 'invalid' on anything other than valid, so if there was an error capture it
			if ( false === $license->success ) {

				switch( $license->error ) {

					case 'expired' :

						$class = 'expired';
						$messages[] = sprintf(
							__( 'Your license key expired on %s. Please <a href="%s" target="_blank">renew your license key</a>.', 'easy-digital-downloads' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
							'https://easydigitaldownloads.com/checkout/?edd_license_key=' . $value . '&utm_campaign=admin&utm_source=licenses&utm_medium=expired'
						);

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'revoked' :

						$class = 'error';
						$messages[] = sprintf(
							__( 'Your license key has been disabled. Please <a href="%s" target="_blank">contact support</a> for more information.', 'easy-digital-downloads' ),
							'https://easydigitaldownloads.com/support?utm_campaign=admin&utm_source=licenses&utm_medium=revoked'
						);

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'missing' :

						$class = 'error';
						$messages[] = sprintf(
							__( 'Invalid license. Please <a href="%s" target="_blank">visit your account page</a> and verify it.', 'easy-digital-downloads' ),
							'https://easydigitaldownloads.com/your-account?utm_campaign=admin&utm_source=licenses&utm_medium=missing'
						);

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'invalid' :
					case 'site_inactive' :

						$class = 'error';
						$messages[] = sprintf(
							__( 'Your %s is not active for this URL. Please <a href="%s" target="_blank">visit your account page</a> to manage your license key URLs.', 'easy-digital-downloads' ),
							$args['name'],
							'https://easydigitaldownloads.com/your-account?utm_campaign=admin&utm_source=licenses&utm_medium=invalid'
						);

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'item_name_mismatch' :

						$class = 'error';
						$messages[] = sprintf( __( 'This appears to be an invalid license key for %s.', 'easy-digital-downloads' ), $args['name'] );

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'no_activations_left':

						$class = 'error';
						$messages[] = sprintf( __( 'Your license key has reached its activation limit. <a href="%s">View possible upgrades</a> now.', 'easy-digital-downloads' ), 'https://easydigitaldownloads.com/your-account/' );

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'license_not_activable':

						$class = 'error';
						$messages[] = __( 'The key you entered belongs to a bundle, please use the product specific license key.', 'easy-digital-downloads' );

						$license_status = 'license-' . $class . '-notice';
						break;

					default :

						$class = 'error';
						$error = ! empty(  $license->error ) ?  $license->error : __( 'unknown_error', 'easy-digital-downloads' );
						$messages[] = sprintf( __( 'There was an error with this license key: %s. Please <a href="%s">contact our support team</a>.', 'easy-digital-downloads' ), $error, 'https://easydigitaldownloads.com/support' );

						$license_status = 'license-' . $class . '-notice';
						break;
				}

			} else {

				switch( $license->license ) {

					case 'valid' :
					default:

						$class = 'valid';

						$now        = current_time( 'timestamp' );
						$expiration = strtotime( $license->expires, current_time( 'timestamp' ) );

						if( 'lifetime' === $license->expires ) {

							$messages[] = __( 'License key never expires.', 'easy-digital-downloads' );

							$license_status = 'license-lifetime-notice';

						} elseif( $expiration > $now && $expiration - $now < ( DAY_IN_SECONDS * 30 ) ) {

							$messages[] = sprintf(
								__( 'Your license key expires soon! It expires on %s. <a href="%s" target="_blank">Renew your license key</a>.', 'easy-digital-downloads' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
								'https://easydigitaldownloads.com/checkout/?edd_license_key=' . $value . '&utm_campaign=admin&utm_source=licenses&utm_medium=renew'
							);

							$license_status = 'license-expires-soon-notice';

						} else {

							$messages[] = sprintf(
								__( 'Your license key expires on %s.', 'easy-digital-downloads' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) )
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
		$html = '<input type="text" class="' . sanitize_html_class( $size ) . '-text" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" name="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" value="' . esc_attr( $value ) . '"/>';

		if ( ( is_object( $license ) && 'valid' == $license->license ) || 'valid' == $license ) {
			$html .= '<input type="submit" class="button-secondary" name="' . $args['id'] . '_deactivate" value="' . __( 'Deactivate License',  'easy-digital-downloads' ) . '"/>';
		}

		$html .= '<label for="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

		if ( ! empty( $messages ) ) {
			foreach( $messages as $message ) {

				$html .= '<div class="edd-license-data edd-license-' . $class . ' ' . $license_status . '">';
					$html .= '<p>' . $message . '</p>';
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

function edd_add_setting_tooltip( $html, $args ) {

	if ( ! empty( $args['tooltip_title'] ) && ! empty( $args['tooltip_desc'] ) ) {
		$tooltip = '<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<strong>' . $args['tooltip_title'] . '</strong>: ' . $args['tooltip_desc'] . '"></span>';
		$html .= $tooltip;
	}

	return $html;
}
add_filter( 'edd_after_setting_output', 'edd_add_setting_tooltip', 10, 2 );
