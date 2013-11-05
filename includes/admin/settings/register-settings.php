<?php
/**
 * Register Settings
 *
 * @package     EDD
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

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

		$general_settings = is_array( get_option( 'edd_settings_general' ) )    ? get_option( 'edd_settings_general' )  	: array();
		$gateway_settings = is_array( get_option( 'edd_settings_gateways' ) )   ? get_option( 'edd_settings_gateways' ) 	: array();
		$email_settings   = is_array( get_option( 'edd_settings_emails' ) )     ? get_option( 'edd_settings_emails' )   	: array();
		$style_settings   = is_array( get_option( 'edd_settings_styles' ) )     ? get_option( 'edd_settings_styles' )   	: array();
		$tax_settings     = is_array( get_option( 'edd_settings_taxes' ) )      ? get_option( 'edd_settings_taxes' )    	: array();
		$ext_settings     = is_array( get_option( 'edd_settings_extensions' ) ) ? get_option( 'edd_settings_extensions' )	: array();
		$license_settings = is_array( get_option( 'edd_settings_licenses' ) )   ? get_option( 'edd_settings_licenses' )		: array();
		$misc_settings    = is_array( get_option( 'edd_settings_misc' ) )       ? get_option( 'edd_settings_misc' )			: array();

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
			add_settings_field(
				'edd_settings[' . $option['id'] . ']',
				$option['name'],
				function_exists( 'edd_' . $option['type'] . '_callback' ) ? 'edd_' . $option['type'] . '_callback' : 'edd_missing_callback',
				'edd_settings_' . $tab,
				'edd_settings_' . $tab,
				array(
					'id'      => $option['id'],
					'desc'    => ! empty( $option['desc'] ) ? $option['desc'] : '',
					'name'    => $option['name'],
					'section' => $tab,
					'size'    => isset( $option['size'] ) ? $option['size'] : null,
					'options' => isset( $option['options'] ) ? $option['options'] : '',
					'std'     => isset( $option['std'] ) ? $option['std'] : ''
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

	$pages = get_pages();
	$pages_options = array( 0 => '' ); // Blank option
	if ( $pages ) {
		foreach ( $pages as $page ) {
			$pages_options[ $page->ID ] = $page->post_title;
		}
	}

	/**
	 * 'Whitelisted' EDD settings, filters are provided for each settings
	 * section to allow extensions and other plugins to add their own settings
	 */
	$edd_settings = array(
		/** General Settings */
		'general' => apply_filters( 'edd_settings_general',
			array(
				'test_mode' => array(
					'id' => 'test_mode',
					'name' => __( 'Test Mode', 'edd' ),
					'desc' => __( 'While in test mode no live transactions are processed. To fully use test mode, you must have a sandbox (test) account for the payment gateway you are testing.', 'edd' ),
					'type' => 'checkbox'
				),
				'purchase_page' => array(
					'id' => 'purchase_page',
					'name' => __( 'Checkout Page', 'edd' ),
					'desc' => __( 'This is the checkout page where buyers will complete their purchases. The [download_checkout] short code must be on this page.', 'edd' ),
					'type' => 'select',
					'options' => $pages_options
				),
				'success_page' => array(
					'id' => 'success_page',
					'name' => __( 'Success Page', 'edd' ),
					'desc' => __( 'This is the page buyers are sent to after completing their purchases. The [edd_receipt] short code should be on this page.', 'edd' ),
					'type' => 'select',
					'options' => $pages_options
				),
				'failure_page' => array(
					'id' => 'failure_page',
					'name' => __( 'Failed Transaction Page', 'edd' ),
					'desc' => __( 'This is the page buyers are sent to if their transaction is cancelled or fails', 'edd' ),
					'type' => 'select',
					'options' => $pages_options
				),
				'currency_settings' => array(
					'id' => 'currency_settings',
					'name' => '<strong>' . __( 'Currency Settings', 'edd' ) . '</strong>',
					'desc' => __( 'Configure the currency options', 'edd' ),
					'type' => 'header'
				),
				'currency' => array(
					'id' => 'currency',
					'name' => __( 'Currency', 'edd' ),
					'desc' => __( 'Choose your currency. Note that some payment gateways have currency restrictions.', 'edd' ),
					'type' => 'select',
					'options' => edd_get_currencies()
				),
				'currency_position' => array(
					'id' => 'currency_position',
					'name' => __( 'Currency Position', 'edd' ),
					'desc' => __( 'Choose the location of the currency sign.', 'edd' ),
					'type' => 'select',
					'options' => array(
						'before' => __( 'Before - $10', 'edd' ),
						'after' => __( 'After - 10$', 'edd' )
					)
				),
				'thousands_separator' => array(
					'id' => 'thousands_separator',
					'name' => __( 'Thousands Separator', 'edd' ),
					'desc' => __( 'The symbol (usually , or .) to separate thousands', 'edd' ),
					'type' => 'text',
					'size' => 'small',
					'std' => ','
				),
				'decimal_separator' => array(
					'id' => 'decimal_separator',
					'name' => __( 'Decimal Separator', 'edd' ),
					'desc' => __( 'The symbol (usually , or .) to separate decimal points', 'edd' ),
					'type' => 'text',
					'size' => 'small',
					'std' => '.'
				),
				'api_settings' => array(
					'id' => 'api_settings',
					'name' => '<strong>' . __( 'API Settings', 'edd' ) . '</strong>',
					'desc' => '',
					'type' => 'header'
				),
				'api_allow_user_keys' => array(
					'id' => 'api_allow_user_keys',
					'name' => __( 'Allow User Keys', 'edd' ),
					'desc' => __( 'Check this box to allow all users to generate API keys. Users with the \'manage_shop_settings\' capability are always allowed to generate keys.', 'edd' ),
					'type' => 'checkbox'
				),
				'tracking_settings' => array(
					'id' => 'tracking_settings',
					'name' => '<strong>' . __( 'Tracking Settings', 'edd' ) . '</strong>',
					'desc' => '',
					'type' => 'header'
				),
				'allow_tracking' => array(
					'id' => 'allow_tracking',
					'name' => __( 'Allow Usage Tracking?', 'edd' ),
					'desc' => __( 'Allow Easy Digital Downloads to annonymously track how this plugin is used and help us make the plugin better. Opt-in and receive a 20% discount code for any purchase from the <a href="https://easydigitaldownloads.com/extensions" target="_blank">Easy Digital Downloads store</a>. Your discount code will be emailed to you.', 'edd' ),
					'type' => 'checkbox'
				)
			)
		),
		/** Payment Gateways Settings */
		'gateways' => apply_filters('edd_settings_gateways',
			array(
				'gateways' => array(
					'id' => 'gateways',
					'name' => __( 'Payment Gateways', 'edd' ),
					'desc' => __( 'Choose the payment gateways you want to enable.', 'edd' ),
					'type' => 'gateways',
					'options' => edd_get_payment_gateways()
				),
				'default_gateway' => array(
					'id' => 'default_gateway',
					'name' => __( 'Default Gateway', 'edd' ),
					'desc' => __( 'This gateway will be loaded automatically with the checkout page.', 'edd' ),
					'type' => 'gateway_select',
					'options' => edd_get_payment_gateways()
				),
				'accepted_cards' => array(
					'id' => 'accepted_cards',
					'name' => __( 'Accepted Payment Method Icons', 'edd' ),
					'desc' => __( 'Display icons for the selected payment methods', 'edd' ) . '<br/>' . __( 'You will also need to configure your gateway settings if you are accepting credit cards', 'edd' ),
					'type' => 'multicheck',
					'options' => apply_filters('edd_accepted_payment_icons', array(
							'mastercard' => 'Mastercard',
							'visa' => 'Visa',
							'americanexpress' => 'American Express',
							'discover' => 'Discover',
							'paypal' => 'PayPal'
						)
					)
				),
				'paypal' => array(
					'id' => 'paypal',
					'name' => '<strong>' . __( 'PayPal Settings', 'edd' ) . '</strong>',
					'desc' => __( 'Configure the PayPal settings', 'edd' ),
					'type' => 'header'
				),
				'paypal_email' => array(
					'id' => 'paypal_email',
					'name' => __( 'PayPal Email', 'edd' ),
					'desc' => __( 'Enter your PayPal account\'s email', 'edd' ),
					'type' => 'text',
					'size' => 'regular'
				),
				'paypal_page_style' => array(
					'id' => 'paypal_page_style',
					'name' => __( 'PayPal Page Style', 'edd' ),
					'desc' => __( 'Enter the name of the page style to use, or leave blank for default', 'edd' ),
					'type' => 'text',
					'size' => 'regular'
				),
				'disable_paypal_verification' => array(
					'id' => 'disable_paypal_verification',
					'name' => __( 'Disable PayPal IPN Verification', 'edd' ),
					'desc' => __( 'If payments are not getting marked as complete, then check this box. This forces the site to use a slightly less secure method of verifying purchases.', 'edd' ),
					'type' => 'checkbox'
				)
			)
		),
		/** Emails Settings */
		'emails' => apply_filters('edd_settings_emails',
			array(
				'email_template' => array(
					'id' => 'email_template',
					'name' => __( 'Email Template', 'edd' ),
					'desc' => __( 'Choose a template. Click "Save Changes" then "Preview Purchase Receipt" to see the new template.', 'edd' ),
					'type' => 'select',
					'options' => edd_get_email_templates()
				),
				'email_settings' => array(
					'id' => 'email_settings',
					'name' => '',
					'desc' => '',
					'type' => 'hook'
				),
				'from_name' => array(
					'id' => 'from_name',
					'name' => __( 'From Name', 'edd' ),
					'desc' => __( 'The name purchase receipts are said to come from. This should probably be your site or shop name.', 'edd' ),
					'type' => 'text'
				),
				'from_email' => array(
					'id' => 'from_email',
					'name' => __( 'From Email', 'edd' ),
					'desc' => __( 'Email to send purchase receipts from. This will act as the "from" and "reply-to" address.', 'edd' ),
					'type' => 'text'
				),
				'purchase_subject' => array(
					'id' => 'purchase_subject',
					'name' => __( 'Purchase Email Subject', 'edd' ),
					'desc' => __( 'Enter the subject line for the purchase receipt email', 'edd' ),
					'type' => 'text'
				),
				'purchase_receipt' => array(
					'id' => 'purchase_receipt',
					'name' => __( 'Purchase Receipt', 'edd' ),
					'desc' => edd_get_purchase_receipt_template_tags(),
					'type' => 'rich_editor'
				),
				'sale_notification_header' => array(
					'id' => 'sale_notification_header',
					'name' => '<strong>' . __('New Sale Notifications', 'edd') . '</strong>',
					'desc' => __('Configure new sale notification emails', 'edd'),
					'type' => 'header'
				),
				'sale_notification_subject' => array(
					'id' => 'sale_notification_subject',
					'name' => __( 'Sale Notification Subject', 'edd' ),
					'desc' => __( 'Enter the subject line for the sale notification email', 'edd' ),
					'type' => 'text',
					'std' => 'New download purchase - Order #{payment_id}'
				),
				'sale_notification' => array(
					'id' => 'sale_notification',
					'name' => __( 'Sale Notification', 'edd' ),
					'desc' => edd_get_sale_notification_template_tags(),
					'type' => 'rich_editor',
					'std' => edd_get_default_sale_notification_email()
				),
				'admin_notice_emails' => array(
					'id' => 'admin_notice_emails',
					'name' => __( 'Sale Notification Emails', 'edd' ),
					'desc' => __( 'Enter the email address(es) that should receive a notification anytime a sale is made, one per line', 'edd' ),
					'type' => 'textarea',
					'std'  => get_bloginfo( 'admin_email' )
				),
				'disable_admin_notices' => array(
					'id' => 'disable_admin_notices',
					'name' => __( 'Disable Admin Notifications', 'edd' ),
					'desc' => __( 'Check this box if you do not want to receive emails when new sales are made.', 'edd' ),
					'type' => 'checkbox'
				)
			)
		),
		/** Styles Settings */
		'styles' => apply_filters('edd_settings_styles',
			array(
				'disable_styles' => array(
					'id' => 'disable_styles',
					'name' => __( 'Disable Styles', 'edd' ),
					'desc' => __( 'Check this to disable all included styling of buttons, checkout fields, and all other elements.', 'edd' ),
					'type' => 'checkbox'
				),
				'button_header' => array(
					'id' => 'button_header',
					'name' => '<strong>' . __( 'Buttons', 'edd' ) . '</strong>',
					'desc' => __( 'Options for add to cart and purchase buttons', 'edd' ),
					'type' => 'header'
				),
				'button_style' => array(
					'id' => 'button_style',
					'name' => __( 'Default Button Style', 'edd' ),
					'desc' => __( 'Choose the style you want to use for the buttons.', 'edd' ),
					'type' => 'select',
					'options' => edd_get_button_styles()
				),
				'checkout_color' => array(
					'id' => 'checkout_color',
					'name' => __( 'Default Button Color', 'edd' ),
					'desc' => __( 'Choose the color you want to use for the buttons.', 'edd' ),
					'type' => 'color_select',
					'options' => edd_get_button_colors()
				)
			)
		),
		/** Taxes Settings */
		'taxes' => apply_filters('edd_settings_taxes',
			array(
				'enable_taxes' => array(
					'id' => 'enable_taxes',
					'name' => __( 'Enable Taxes', 'edd' ),
					'desc' => __( 'Check this to enable taxes on purchases.', 'edd' ),
					'type' => 'checkbox',
					'std' => 'no'
				),
				'tax_rate' => array(
					'id' => 'tax_rate',
					'name' => __( 'Default Tax Rate', 'edd' ),
					'desc' => __( 'Enter a percentage, such as 6.5. Customers not in a specific rate below will be charged this rate.', 'edd' ),
					'type' => 'text',
					'size' => 'small'
				),
				'base_country' => array(
					'id' => 'base_country',
					'name' => __( 'Base Country', 'edd' ),
					'desc' => __( 'Where does your store operate from?', 'edd' ),
					'type' => 'select',
					'options' => edd_get_country_list()
				),
				'base_state' => array(
					'id' => 'base_state',
					'name' => __( 'Base State / Province', 'edd' ),
					'desc' => __( 'What state / provice does your store operate from?', 'edd' ),
					'type' => 'shop_states'
				),
				'prices_include_tax' => array(
					'id' => 'prices_include_tax',
					'name' => __( 'Prices entered with tax', 'edd' ),
					'desc' => __( 'This option affects how you enter prices.', 'edd' ),
					'type' => 'radio',
					'std' => 'no',
					'options' => array(
						'yes' => __( 'Yes, I will enter prices inclusive of tax', 'edd' ),
						'no'  => __( 'No, I will enter prices exclusive of tax', 'edd' )
					)
				),
				'display_tax_rate' => array(
					'id' => 'display_tax_rate',
					'name' => __( 'Display Tax Rate on Prices', 'edd' ),
					'desc' => __( 'Some countries require a notice when product prices include tax.', 'edd' ),
					'type' => 'checkbox',
					'std' => 'no'
				),
				'checkout_include_tax' => array(
					'id' => 'checkout_include_tax',
					'name' => __( 'Display during checkout', 'edd' ),
					'desc' => __( 'Should prices on the checkout page be shown with or without tax?', 'edd' ),
					'type' => 'select',
					'std' => 'no',
					'options' => array(
						'yes' => __( 'Including tax', 'edd' ),
						'no'  => __( 'Excluding tax', 'edd' )
					)
				),
				'taxes_after_discounts' => array(
					'id' => 'taxes_after_discounts',
					'name' => __( 'Calculate Tax After Discounts?', 'edd' ),
					'desc' => __( 'Check this if you would like taxes calculated after discounts. By default taxes are calculated before discounts are applied.', 'edd' ),
					'std' => 'no',
					'type' => 'checkbox'
				),
				'tax_rates' => array(
					'id' => 'tax_rates',
					'name' => '<strong>' . __( 'Additional Tax Rates', 'edd' ) . '</strong>',
					'desc' => __( 'Specify additional tax rates for other regions.', 'edd' ),
					'type' => 'tax_rates'
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
				'disable_ajax_cart' => array(
					'id' => 'disable_ajax_cart',
					'name' => __( 'Disable Ajax', 'edd' ),
					'desc' => __( 'Check this to disable AJAX for the shopping cart.', 'edd' ),
					'type' => 'checkbox'
				),
				'redirect_on_add' => array(
					'id' => 'redirect_on_add',
					'name' => __( 'Redirect to Checkout', 'edd' ),
					'desc' => __( 'Immediately redirect to checkout after adding an item to the cart?', 'edd' ),
					'type' => 'checkbox'
				),
				'live_cc_validation' => array(
					'id' => 'live_cc_validation',
					'name' => __( 'Disable Live Credit Card Validation', 'edd' ),
					'desc' => __( 'Live credit card validation means that that card type and number will be validated as the customer enters the number.', 'edd' ),
					'type' => 'checkbox'
				),
				'logged_in_only' => array(
					'id' => 'logged_in_only',
					'name' => __( 'Disable Guest Checkout', 'edd' ),
					'desc' => __( 'Require that users be logged-in to purchase files.', 'edd' ),
					'type' => 'checkbox'
				),
				'show_register_form' => array(
					'id' => 'show_register_form',
					'name' => __( 'Show Register / Login Form?', 'edd' ),
					'desc' => __( 'Display the registration and login forms on the checkout page for non-logged-in users.', 'edd' ),
					'type' => 'checkbox'
				),
				'item_quantities' => array(
					'id' => 'item_quantities',
					'name' => __('Item Quantities', 'edd'),
					'desc' => __('Allow item quantities to be changed at checkout.', 'edd'),
					'type' => 'checkbox'
				),
				'allow_multiple_discounts' => array(
					'id' => 'allow_multiple_discounts',
					'name' => __('Multiple Discounts', 'edd'),
					'desc' => __('Allow customers to use multiple discounts on the same purchase?', 'edd'),
					'type' => 'checkbox'
				),
				'disable_cart_saving' => array(
					'id' => 'disable_cart_saving',
					'name' => __( 'Disable Cart Saving', 'edd' ),
					'desc' => __( 'Check this to disable cart saving on the checkout', 'edd' ),
					'type' => 'checkbox'
				),
				'field_downloads' => array(
					'id' => 'field_downloads',
					'name' => '<strong>' . __( 'File Downloads', 'edd' ) . '</strong>',
					'desc' => '',
					'type' => 'header'
				),
				'download_method' => array(
					'id' => 'download_method',
					'name' => __( 'Download Method', 'edd' ),
					'desc' => sprintf( __( 'Select the file download method. Note, not all methods work on all servers.', 'edd' ), edd_get_label_singular() ),
					'type' => 'select',
					'options' => array(
						'direct' => __( 'Forced', 'edd' ),
						'redirect' => __( 'Redirect', 'edd' )
					)
				),
				'symlink_file_downloads' => array(
					'id' => 'symlink_file_downloads',
					'name' => __( 'Symlink File Downloads?', 'edd' ),
					'desc' => __( 'Check this if you are delivering really large files or having problems with file downloads completing.', 'edd' ),
					'type' => 'checkbox'
				),
				'file_download_limit' => array(
					'id' => 'file_download_limit',
					'name' => __( 'File Download Limit', 'edd' ),
					'desc' => sprintf( __( 'The maximum number of times files can be downloaded for purchases. Can be overwritten for each %s.', 'edd' ), edd_get_label_singular() ),
					'type' => 'text',
					'size' => 'small'
				),
				'download_link_expiration' => array(
					'id' => 'download_link_expiration',
					'name' => __( 'Download Link Expiration', 'edd' ),
					'desc' => __( 'How long should download links be valid for? Default is 24 hours from the time they are generated. Enter a time in hours.', 'edd' ),
					'type' => 'text',
					'size' => 'small',
					'std'  => '24'
				),
				'disable_redownload' => array(
					'id' => 'disable_redownload',
					'name' => __( 'Disable Redownload?', 'edd' ),
					'desc' => __( 'Check this if you do not want to allow users to redownload items from their purchase history.', 'edd' ),
					'type' => 'checkbox'
				),
				'accounting_settings' => array(
					'id' => 'accounting_settings',
					'name' => '<strong>' . __( 'Accounting Settings', 'edd' ) . '</strong>',
					'desc' => '',
					'type' => 'header'
				),
				'enable_skus' => array(
					'id' => 'enable_skus',
					'name' => __( 'Enable SKU Entry', 'edd' ),
					'desc' => __( 'Check this box to allow entry of product SKUs. SKUs will be shown on purchase receipt and exported purchase histories.', 'edd' ),
					'type' => 'checkbox'
				),
				'terms' => array(
					'id' => 'terms',
					'name' => '<strong>' . __( 'Terms of Agreement', 'edd' ) . '</strong>',
					'desc' => '',
					'type' => 'header'
				),
				'show_agree_to_terms' => array(
					'id' => 'show_agree_to_terms',
					'name' => __( 'Agree to Terms', 'edd' ),
					'desc' => __( 'Check this to show an agree to terms on the checkout that users must agree to before purchasing.', 'edd' ),
					'type' => 'checkbox'
				),
				'agree_label' => array(
					'id' => 'agree_label',
					'name' => __( 'Agree to Terms Label', 'edd' ),
					'desc' => __( 'Label shown next to the agree to terms check box.', 'edd' ),
					'type' => 'text',
					'size' => 'regular'
				),
				'agree_text' => array(
					'id' => 'agree_text',
					'name' => __( 'Agreement Text', 'edd' ),
					'desc' => __( 'If Agree to Terms is checked, enter the agreement terms here.', 'edd' ),
					'type' => 'rich_editor'
				),
				'checkout_label' => array(
					'id' => 'checkout_label',
					'name' => __( 'Complete Purchase Text', 'edd' ),
					'desc' => __( 'The button label for completing a purchase.', 'edd' ),
					'type' => 'text'
				),
				'add_to_cart_text' => array(
					'id' => 'add_to_cart_text',
					'name' => __( 'Add to Cart Text', 'edd' ),
					'desc' => __( 'Text shown on the Add to Cart Buttons', 'edd' ),
					'type' => 'text'
				)
			)
		)
	);

	return $edd_settings;
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
	echo '';
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

	$checked = isset($edd_options[$args['id']]) ? checked(1, $edd_options[$args['id']], false) : '';
	$html = '<input type="checkbox" id="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" name="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" value="1" ' . $checked . '/>';
	$html .= '<label for="edd_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

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

	foreach( $args['options'] as $key => $option ):
		if( isset( $edd_options[$args['id']][$key] ) ) { $enabled = $option; } else { $enabled = NULL; }
		echo '<input name="edd_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']" id="edd_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']" type="checkbox" value="' . $option . '" ' . checked($option, $enabled, false) . '/>&nbsp;';
		echo '<label for="edd_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
	endforeach;
	echo '<p class="description">' . $args['desc'] . '</p>';
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

		echo '<input name="edd_settings_' . $args['section'] . '[' . $args['id'] . ']"" id="edd_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']" type="radio" value="' . $key . '" ' . checked(true, $checked, false) . '/>&nbsp;';
		echo '<label for="edd_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
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

		echo '<input name="edd_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']"" id="edd_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']" type="checkbox" value="1" ' . checked('1', $enabled, false) . '/>&nbsp;';
		echo '<label for="edd_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']">' . $option['admin_label'] . '</label><br/>';
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

	echo '<select name="edd_settings_' . $args['section'] . '[' . $args['id'] . ']"" id="edd_settings_' . $args['section'] . '[' . $args['id'] . ']">';

	foreach ( $args['options'] as $key => $option ) :
		$selected = isset( $edd_options[ $args['id'] ] ) ? selected( $key, $edd_options[$args['id']], false ) : '';
		echo '<option value="' . esc_attr( $key ) . '"' . $selected . '>' . esc_html( $option['admin_label'] ) . '</option>';
	endforeach;

	echo '</select>';
	echo '<label for="edd_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
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

	if ( isset( $edd_options[ $args['id'] ] ) )
		$value = $edd_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="' . $size . '-text" id="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" name="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<label for="edd_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

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

	if ( isset( $edd_options[ $args['id'] ] ) )
		$value = $edd_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<textarea class="large-text" cols="50" rows="5" id="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" name="edd_settings_' . $args['section'] . '[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	$html .= '<label for="edd_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

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

	if ( isset( $edd_options[ $args['id'] ] ) )
		$value = $edd_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="password" class="' . $size . '-text" id="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" name="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';
	$html .= '<label for="edd_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

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
	printf( __( 'The callback function used for the <strong>%s</strong> setting is missing.', 'edd' ), $args['id'] );
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

	if ( isset( $edd_options[ $args['id'] ] ) )
		$value = $edd_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$html = '<select id="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" name="edd_settings_' . $args['section'] . '[' . $args['id'] . ']"/>';

	foreach ( $args['options'] as $option => $name ) :
		$selected = selected( $option, $value, false );
		$html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
	endforeach;

	$html .= '</select>';
	$html .= '<label for="edd_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

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

	if ( isset( $edd_options[ $args['id'] ] ) )
		$value = $edd_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$html = '<select id="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" name="edd_settings_' . $args['section'] . '[' . $args['id'] . ']"/>';

	foreach ( $args['options'] as $option => $color ) :
		$selected = selected( $option, $value, false );
		$html .= '<option value="' . $option . '" ' . $selected . '>' . $color['label'] . '</option>';
	endforeach;

	$html .= '</select>';
	$html .= '<label for="edd_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

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

	if ( isset( $edd_options[ $args['id'] ] ) )
		$value = $edd_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	if ( $wp_version >= 3.3 && function_exists( 'wp_editor' ) ) {
		$html = wp_editor( stripslashes( $value ), 'edd_settings_' . $args['section'] . '[' . $args['id'] . ']', array( 'textarea_name' => 'edd_settings_' . $args['section'] . '[' . $args['id'] . ']' ) );
	} else {
		$html = '<textarea class="large-text" rows="10" id="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" name="edd_settings_' . $args['section'] . '[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	}

	$html .= '<br/><label for="edd_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

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

	if ( isset( $edd_options[ $args['id'] ] ) )
		$value = $edd_options[$args['id']];
	else
		$value = isset($args['std']) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="' . $size . '-text edd_upload_field" id="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" name="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<span>&nbsp;<input type="button" class="edd_settings_upload_button button-secondary" value="' . __( 'Upload File', 'edd' ) . '"/></span>';
	$html .= '<label for="edd_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

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

	if ( isset( $edd_options[ $args['id'] ] ) )
		$value = $edd_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$default = isset( $args['std'] ) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="edd-color-picker" id="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" name="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" value="' . esc_attr( $value ) . '" data-default-color="' . esc_attr( $default ) . '" />';
	$html .= '<label for="edd_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

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

	$html = '<select id="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" name="edd_settings_' . $args['section'] . '[' . $args['id'] . ']"/>';

	$states = edd_get_shop_states();

	foreach ( $states as $option => $name ) :
		$selected = isset( $edd_options[ $args['id'] ] ) ? selected( $option, $edd_options[$args['id']], false ) : '';
		$html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
	endforeach;

	$html .= '</select>';
	$html .= '<label for="edd_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

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
				<th scope="col" class="edd_tax_country"><?php _e( 'Country', 'edd' ); ?></th>
				<th scope="col" class="edd_tax_state"><?php _e( 'State / Province', 'edd' ); ?></th>
				<th scope="col" class="edd_tax_global" title="<?php _e( 'Apply rate to whole country, regardless of state / province', 'edd' ); ?>"><?php _e( 'Country Wide', 'edd' ); ?></th>
				<th scope="col" class="edd_tax_rate"><?php _e( 'Rate', 'edd' ); ?></th>
				<th scope="col"><?php _e( 'Remove', 'edd' ); ?></th>
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
						'show_option_none' => false
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
							'show_option_none' => false
						) );
					} else {
						echo EDD()->html->text( 'tax_rates[' . $key . '][state]', $rate['state'] );
					}
					?>
				</td>
				<td class="edd_tax_global">
					<input type="checkbox" name="tax_rates[<?php echo $key; ?>][global]" id="tax_rates[<?php echo $key; ?>][global]" value="1"<?php checked( true, ! empty( $rate['global'] ) ); ?>/>
					<label for="tax_rates[<?php echo $key; ?>][global]"><?php _e( 'Apply to whole country', 'edd' ); ?></label>
				</td>
				<td class="edd_tax_rate"><input type="number" class="small-text" step="0.0001" min="0.0" max="99" name="tax_rates[<?php echo $key; ?>][rate]" value="<?php echo $rate['rate']; ?>"/></td>
				<td><span class="edd_remove_tax_rate button-secondary"><?php _e( 'Remove Rate', 'edd' ); ?></span></td>
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
						'show_option_none' => false
					) ); ?>
				</td>
				<td class="edd_tax_state">
					<?php echo EDD()->html->text( 'tax_rates[0][state]' ); ?>
				</td>
				<td class="edd_tax_global">
					<input type="checkbox" name="tax_rates[0][global]" value="1"/>
					<label for="tax_rates[0][global]"><?php _e( 'Apply to whole country', 'edd' ); ?></label>
				</td>
				<td class="edd_tax_rate"><input type="number" class="small-text" step="0.0001" min="0.0" name="tax_rates[0][rate]" value=""/></td>
				<td><span class="edd_remove_tax_rate button-secondary"><?php _e( 'Remove Rate', 'edd' ); ?></span></td>
			</tr>
		<?php endif; ?>
	</table>
	<p>
		<span class="button-secondary" id="edd_add_tax_rate"><?php _e( 'Add Tax Rate', 'edd' ); ?></span>
	</p>
	<?php
	echo ob_get_clean();
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

		if ( isset( $edd_options[ $args['id'] ] ) )
			$value = $edd_options[ $args['id'] ];
		else
			$value = isset( $args['std'] ) ? $args['std'] : '';

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="text" class="' . $size . '-text" id="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" name="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';

		if ( 'valid' == get_option( $args['options']['is_valid_license_option'] ) ) {
			$html .= wp_nonce_field( $args['id'] . '_nonce', $args['id'] . '_nonce', false );
			$html .= '<input type="submit" class="button-secondary" name="' . $args['id'] . '_deactivate" value="' . __( 'Deactivate License',  'edd' ) . '"/>';
		}

		$html .= '<label for="edd_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

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
	do_action( 'edd_' . $args['id'] );
}

/**
 * Settings Sanitization
 *
 * Adds a settings error (for the updated message)
 * At some point this will validate input
 *
 * @since 1.0.8.2
 * @param array $input The value inputted in the field
 * @return string $input Sanitizied value
 */
function edd_settings_sanitize( $input = array() ) {

	global $edd_options;

	parse_str( $_POST['_wp_http_referer'], $referrer );

	$output    = array();
	$settings  = edd_get_registered_settings();
	$tab       = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';
	$post_data = isset( $_POST[ 'edd_settings_' . $tab ] ) ? $_POST[ 'edd_settings_' . $tab ] : array();

	$input = apply_filters( 'edd_settings_' . $tab . '_sanitize', $post_data );

	// Loop through each setting being saved and pass it through a sanitization filter
	foreach( $input as $key => $value ) {

		// Get the setting type (checkbox, select, etc)
		$type = isset( $settings[ $key ][ 'type' ] ) ? $settings[ $key ][ 'type' ] : false;

		if( $type ) {
			// Field type specific filter
			$output[ $key ] = apply_filters( 'edd_settings_sanitize_' . $type, $value, $key );
		}

		// General filter
		$output[ $key ] = apply_filters( 'edd_settings_sanitize', $value, $key );
	}


	// Loop through the whitelist and unset any that are empty for the tab being saved
	if( ! empty( $settings[ $tab ] ) ) {
		foreach( $settings[ $tab ] as $key => $value ) {

			// settings used to have numeric keys, now they have keys that match the option ID. This ensures both methods work
			if( is_numeric( $key ) ) {
				$key = $value['id'];
			}

			if( empty( $_POST[ 'edd_settings_' . $tab ][ $key ] ) ) {
				unset( $edd_options[ $key ] );
			}

		}
	}

	// Merge our new settings with the existing
	$output = array_merge( $edd_options, $output );

	add_settings_error( 'edd-notices', '', __( 'Settings Updated', 'edd' ), 'updated' );

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

	if( edd_get_file_download_method() != $input['download_method'] || ! edd_htaccess_exists() ) {
		// Force the .htaccess files to be updated if the Download method was changed.
		edd_create_protection_files( true, $input['download_method'] );
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
 * @param array $input The field value
 * @return string $input Sanitizied value
 */
function edd_get_settings_tabs() {

	$settings = edd_get_registered_settings();

	$tabs             = array();
	$tabs['general']  = __( 'General', 'edd' );
	$tabs['gateways'] = __( 'Payment Gateways', 'edd' );
	$tabs['emails']   = __( 'Emails', 'edd' );
	$tabs['styles']   = __( 'Styles', 'edd' );
	$tabs['taxes']    = __( 'Taxes', 'edd' );

	if( ! empty( $settings['extensions'] ) ) {
		$tabs['extensions'] = __( 'Extensions', 'edd' );
	}
	if( ! empty( $settings['licenses'] ) ) {
		$tabs['licenses'] = __( 'Licenses', 'edd' );
	}

	$tabs['misc']      = __( 'Misc', 'edd' );

	return apply_filters( 'edd_settings_tabs', $tabs );
}
