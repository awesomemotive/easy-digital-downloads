<?php
/**
 * Register Settings
 *
 * @package     Easy Digital Downloads
 * @subpackage  Register Settings
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register Settings
 *
 * Registers the required settings.
 *
 * @access      private
 * @since       1.0
 * @return      void
*/
function edd_register_settings() {
	// Setup some default option sets
	$pages = get_pages();
	$pages_options = array( 0 => '' ); // Blank option
	if ( $pages ) {
		foreach ( $pages as $page ) {
			$pages_options[ $page->ID ] = $page->post_title;
		}
	}

	/* White list our settings, each in their respective section
	   filters can be used to add more options to each section */
	$edd_settings = array(
		'general' => apply_filters('edd_settings_general',
			array(
				'test_mode' => array(
					'id' => 'test_mode',
					'name' => __('Test Mode', 'edd'),
					'desc' => __('While in test mode no live transactions are processed. To fully use test mode, you must have a sandbox (test) account for the payment gateway you are testing.', 'edd'),
					'type' => 'checkbox'
				),
				'purchase_page' => array(
					'id' => 'purchase_page',
					'name' => __('Checkout Page', 'edd'),
					'desc' => __('This is the checkout page where buyers will complete their purchases. The [download_checkout] short code must be on this page.', 'edd'),
					'type' => 'select',
					'options' => $pages_options
				),
				'success_page' => array(
					'id' => 'success_page',
					'name' => __('Success Page', 'edd'),
					'desc' => __('This is the page buyers are sent to after completing their purchases. The [edd_receipt] short code should be on this page.', 'edd'),
					'type' => 'select',
					'options' => $pages_options
				),
				'failure_page' => array(
					'id' => 'failure_page',
					'name' => __('Failed Transaction Page', 'edd'),
					'desc' => __('This is the page buyers are sent to if their transaction is cancelled or fails', 'edd'),
					'type' => 'select',
					'options' => $pages_options
				),
				'currency_settings' => array(
					'id' => 'currency_settings',
					'name' => '<strong>' . __('Currency Settings', 'edd') . '</strong>',
					'desc' => __('Configure the currency options', 'edd'),
					'type' => 'header'
				),
				'currency' => array(
					'id' => 'currency',
					'name' => __('Currency', 'edd'),
					'desc' => __('Choose your currency. Note that some payment gateways have currency restrictions.', 'edd'),
					'type' => 'select',
					'options' => edd_get_currencies()
				),
				'currency_position' => array(
					'id' => 'currency_position',
					'name' => __('Currency Position', 'edd'),
					'desc' => __('Choose the location of the currency sign.', 'edd'),
					'type' => 'select',
					'options' => array(
						'before' => __('Before - $10', 'edd'),
						'after' => __('After - 10$', 'edd')
					)
				),
				'thousands_separator' => array(
					'id' => 'thousands_separator',
					'name' => __('Thousands Separator', 'edd'),
					'desc' => __('The symbol (usually , or .) to separate thousands', 'edd'),
					'type' => 'text',
					'size' => 'small',
					'std' => ','
				),
				'decimal_separator' => array(
					'id' => 'decimal_separator',
					'name' => __('Decimal Separator', 'edd'),
					'desc' => __('The symbol (usually , or .) to separate decimal points', 'edd'),
					'type' => 'text',
					'size' => 'small',
					'std' => '.'
				),
				'tracking_settings' => array(
					'id' => 'tracking_settings',
					'name' => '<strong>' . __('Usage Tracking', 'edd') . '</strong>',
					'desc' => '',
					'type' => 'header'
				),
				'presstrends' => array(
					'id' => 'presstrends',
					'name' => __('Enable Tracking', 'edd'),
					'desc' => __('Check this box to allow Easy Digital Downloads to track how the plugin is used. No personal info is ever collected. This helps us better improve the plugin.', 'edd'),
					'type' => 'checkbox'
				),
				'api_settings' => array(
					'id' => 'api_settings',
					'name' => '<strong>' . __('API Settings', 'edd') . '</strong>',
					'desc' => '',
					'type' => 'header'
				),
				'api_allow_user_keys' => array(
					'id' => 'api_allow_user_keys',
					'name' => __('Allow User Keys', 'edd') . '</strong>',
					'desc' => __('Check this box to allow all users to generate API keys. Users with the \'manage_shop_settings\' capability are always allowed to generate keys.', 'edd'),
					'type' => 'checkbox'
				)
			)
		),
		'gateways' => apply_filters('edd_settings_gateways',
			array(
				'gateways' => array(
					'id' => 'gateways',
					'name' => __('Payment Gateways', 'edd'),
					'desc' => __('Choose the payment gateways you want to enable.', 'edd'),
					'type' => 'gateways',
					'options' => edd_get_payment_gateways()
				),
				'default_gateway' => array(
					'id' => 'default_gateway',
					'name' => __('Default Gateway', 'edd'),
					'desc' => __('This gateway will be loaded automatically with the checkout page.', 'edd'),
					'type' => 'gateway_select',
					'options' => edd_get_payment_gateways()
				),
				'accepted_cards' => array(
					'id' => 'accepted_cards',
					'name' => __('Accepted Payment Method Icons', 'edd'),
					'desc' => __('Display icons for the selected payment methods', 'edd') . '<br/>' . __('You will also need to configure your gateway settings if you are accepting credit cards', 'edd'),
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
					'name' => '<strong>' . __('PayPal Settings', 'edd') . '</strong>',
					'desc' => __('Configure the PayPal settings', 'edd'),
					'type' => 'header'
				),
				'paypal_email' => array(
					'id' => 'paypal_email',
					'name' => __('PayPal Email', 'edd'),
					'desc' => __('Enter your PayPal account\'s email', 'edd'),
					'type' => 'text',
					'size' => 'regular'
				),
				'paypal_page_style' => array(
					'id' => 'paypal_page_style',
					'name' => __('PayPal Page Style', 'edd'),
					'desc' => __('Enter the name of the page style to use, or leave blank for default', 'edd'),
					'type' => 'text',
					'size' => 'regular'
				),
				'disable_paypal_verification' => array(
					'id' => 'disable_paypal_verification',
					'name' => __('Disable PayPal IPN Verification', 'edd'),
					'desc' => __('If payments are not getting marked as complete, then check this box. This forces the site to use a slightly less secure method of verifying purchases.', 'edd'),
					'type' => 'checkbox'
				)
			)
		),
		'emails' => apply_filters('edd_settings_emails',
			array(
				'email_template' => array(
					'id' => 'email_template',
					'name' => __('Email Template', 'edd'),
					'desc' => __('Choose a template. Click "Save Changes" then "Preview Purchase Receipt" to see the new template.', 'edd'),
					'type' => 'select',
					'options' => edd_get_email_templates()
				),
				'email_settings' => array(
					'id' => 'email_settings',
					'name' => '',
					'desc' => '',
					'type' => 'hook',
				),
				'from_name' => array(
					'id' => 'from_name',
					'name' => __('From Name', 'edd'),
					'desc' => __('The name purchase receipts are said to come from. This should probably be your site or shop name.', 'edd'),
					'type' => 'text'
				),
				'from_email' => array(
					'id' => 'from_email',
					'name' => __('From Email', 'edd'),
					'desc' => __('Email to send purchase receipts from. This will act as the "from" and "reply-to" address.', 'edd'),
					'type' => 'text'
				),
				'purchase_subject' => array(
					'id' => 'purchase_subject',
					'name' => __('Purchase Email Subject', 'edd'),
					'desc' => __('Enter the subject line for the purchase receipt email', 'edd'),
					'type' => 'text'
				),
				'purchase_receipt' => array(
					'id' => 'purchase_receipt',
					'name' => __('Purchase Receipt', 'edd'),
					'desc' => __('Enter the email that is sent to users after completing a successful purchase. HTML is accepted. Available template tags:', 'edd') . '<br/>' .
						'{download_list} - ' . __('A list of download links for each download purchased', 'edd') . '<br/>' .
						'{file_urls} - ' . __('A plain-text list of download URLs for each download purchased', 'edd') . '<br/>' .
						'{name} - ' . __('The buyer\'s first name', 'edd') . '<br/>' .
						'{fullname} - ' . __('The buyer\'s full name, first and last', 'edd') . '<br/>' .
						'{username} - ' . __('The buyer\'s user name on the site, if they registered an account', 'edd') . '<br/>' .
						'{date} - ' . __('The date of the purchase', 'edd') . '<br/>' .
						'{subtotal} - ' . __('The price of the purchase before taxes', 'edd') . '<br/>' .
						'{tax} - ' . __('The taxed amount of the purchase', 'edd') . '<br/>' .
						'{price} - ' . __('The total price of the purchase', 'edd') . '<br/>' .
						'{payment_id} - ' . __('The unique ID number for this purchase', 'edd') . '<br/>' .
						'{receipt_id} - ' . __('The unique ID number for this purchase receipt', 'edd') . '<br/>' .
						'{payment_method} - ' . __('The method of payment used for this purchase', 'edd') . '<br/>' .
						'{sitename} - ' . __('Your site name', 'edd') . '<br/>' .
						'{receipt_link} - ' . __( 'Adds a link so users can view their receipt directly on your website if they are unable to view it in the browser correctly.', 'edd' ),
					'type' => 'rich_editor'
				),
				'admin_notice_emails' => array(
					'id' => 'admin_notice_emails',
					'name' => __( 'Sale Notification Emails', 'edd' ),
					'desc' => __( 'Enter the email address(es) that should receive a notification anytime a sale is made, one per line', 'edd' ),
					'type' => 'textarea',
					'std'  => get_bloginfo( 'admin_email' )
				)
			)
		),
		'styles' => apply_filters('edd_settings_styles',
			array(
				'disable_styles' => array(
					'id' => 'disable_styles',
					'name' => __('Disable Styles', 'edd'),
					'desc' => __('Check this to disable all included styling of buttons, checkout fields, and all other elements.', 'edd'),
					'type' => 'checkbox'
				),
				'buton_header' => array(
					'id' => 'buton_header',
					'name' => '<strong>' . __('Buttons', 'edd') . '</strong>',
					'desc' => __('Options for add to cart and purchase buttons', 'edd'),
					'type' => 'header'
				),
				'button_style' => array(
					'id' => 'button_style',
					'name' => __('Default Button Style', 'edd'),
					'desc' => __('Choose the style you want to use for the buttons.', 'edd'),
					'type' => 'select',
					'options' => edd_get_button_styles()
				),
				'checkout_color' => array(
					'id' => 'checkout_color',
					'name' => __('Default Button Color', 'edd'),
					'desc' => __('Choose the color you want to use for the buttons.', 'edd'),
					'type' => 'select',
					'options' => edd_get_button_colors()
				)
			)
		),
		'taxes' => apply_filters('edd_settings_taxes',
			array(
				'enable_taxes' => array(
					'id' => 'enable_taxes',
					'name' => __('Enable Taxes', 'edd'),
					'desc' => __('Check this to enable taxes on purchases.', 'edd'),
					'type' => 'checkbox',
					'std' => 'no',
				),
				'tax_rate' => array(
					'id' => 'tax_rate',
					'name' => __('Tax Rate', 'edd'),
					'desc' => __('Enter a percentage, such as 6.5.', 'edd'),
					'type' => 'text',
					'size' => 'small'
				),
				'prices_include_tax' => array(
					'id' => 'prices_include_tax',
					'name' => __('Prices entered with tax', 'edd'),
					'desc' => __('This option affects how you enter prices.', 'edd'),
					'type' => 'radio',
					'std' => 'no',
					'options' => array(
						'yes' => __('Yes, I will enter prices inclusive of tax', 'edd'),
						'no'  => __('No, I will enter prices exclusive of tax', 'edd')
					)
				),
				'tax_condition' => array(
					'id' => 'tax_condition',
					'name' => __('Apply Taxes to:', 'edd'),
					'desc' => __('Who should have tax added to their purchases?', 'edd'),
					'type' => 'radio',
					'std' => 'all',
					'options' => array(
						'all' 	=> __('Everyone', 'edd'),
						'local' => __('Local residents only', 'edd')
					)
				),
				'tax_location' => array(
					'id' => 'tax_location',
					'name' => __('Tax Opt-In', 'edd'),
					'desc' => __('Customers will be given a checkbox to click if they reside in your local area. Please enter directions for them here. Customers <strong>must</strong> opt into this.', 'edd'),
					'type' => 'text',
					'size' => 'large'
				),
				'checkout_include_tax' => array(
					'id' => 'checkout_include_tax',
					'name' => __('Display during checkout', 'edd'),
					'desc' => __('Should prices on the checkout page be shown with or without tax?', 'edd'),
					'type' => 'select',
					'std' => 'no',
					'options' => array(
						'yes' => __('Including tax', 'edd'),
						'no'  => __('Excluding tax', 'edd')
					)
				),
				'taxes_after_discounts' => array(
					'id' => 'taxes_after_discounts',
					'name' => __('Calculate Tax After Discounts?', 'edd'),
					'desc' => __('Check this if you would like taxes calculated after discounts. By default taxes are calculated before discounts are applied.', 'edd'),
					'std' => 'no',
					'type' => 'checkbox'
				)
			)
		),
		'misc' => apply_filters('edd_settings_misc',
			array(
				'disable_ajax_cart' => array(
					'id' => 'disable_ajax_cart',
					'name' => __('Disable Ajax', 'edd'),
					'desc' => __('Check this to disable AJAX for the shopping cart.', 'edd'),
					'type' => 'checkbox'
				),
				'redirect_on_add' => array(
					'id' => 'redirect_on_add',
					'name' => __('Redirect to Checkout', 'edd'),
					'desc' => __('Immediately redirect to checkout after adding an item to the cart?', 'edd'),
					'type' => 'checkbox'
				),
				'live_cc_validation' => array(
					'id' => 'live_cc_validation',
					'name' => __('Disable Live Credit Card Validation', 'edd'),
					'desc' => __('Live credit card validation means that that card type and number will be validated as the customer enters the number.', 'edd'),
					'type' => 'checkbox'
				),
				'logged_in_only' => array(
					'id' => 'logged_in_only',
					'name' => __('Disable Guest Checkout', 'edd'),
					'desc' => __('Require that users be logged-in to purchase files.', 'edd'),
					'type' => 'checkbox'
				),
				'show_register_form' => array(
					'id' => 'show_register_form',
					'name' => __('Show Register / Login Form?', 'edd'),
					'desc' => __('Display the registration and login forms on the checkout page for non-logged-in users.', 'edd'),
					'type' => 'checkbox',
				),
				'download_link_expiration' => array(
					'id' => 'download_link_expiration',
					'name' => __('Download Link Expiration', 'edd'),
					'desc' => __('How long should download links be valid for? Default is 24 hours from the time they are generated. Enter a time in hours.', 'edd'),
					'type' => 'text',
					'size' => 'small'
				),
				'disable_redownload' => array(
					'id' => 'disable_redownload',
					'name' => __('Disable Redownload?', 'edd'),
					'desc' => __('Check this if you do not want to allow users to redownload items from their purchase history.', 'edd'),
					'type' => 'checkbox',
				),
				'symlink_file_downloads' => array(
					'id' => 'symlink_file_downloads',
					'name' => __('Symlink File Downloads?', 'edd'),
					'desc' => __('Check this if you are delivering really large files or having problems with file downloads completing.', 'edd'),
					'type' => 'checkbox',
				),
				'terms' => array(
					'id' => 'terms',
					'name' => '<strong>' . __('Terms of Agreement', 'edd') . '</strong>',
					'desc' => '',
					'type' => 'header',
				),
				'show_agree_to_terms' => array(
					'id' => 'show_agree_to_terms',
					'name' => __('Agree to Terms', 'edd'),
					'desc' => __('Check this to show an agree to terms on the checkout that users must agree to before purchasing.', 'edd'),
					'type' => 'checkbox',
				),
				'agree_label' => array(
					'id' => 'agree_label',
					'name' => __('Agree to Terms Label', 'edd'),
					'desc' => __('Label shown next to the agree to terms check box.', 'edd'),
					'type' => 'text',
					'size' => 'regular'
				),
				'agree_text' => array(
					'id' => 'agree_text',
					'name' => __('Agreement Text', 'edd'),
					'desc' => __('If Agree to Terms is checked, enter the agreement terms here.', 'edd'),
					'type' => 'rich_editor',
				),
				'checkout_label' => array(
					'id' => 'checkout_label',
					'name' => __('Complete Purchase Text', 'edd'),
					'desc' => __('The button label for completing a purchase.', 'edd'),
					'type' => 'text',
				),
				'add_to_cart_text' => array(
					'id' => 'add_to_cart_text',
					'name' => __('Add to Cart Text', 'edd'),
					'desc' => __('Text shown on the Add to Cart Buttons', 'edd'),
					'type' => 'text'
				)
			)
		)
	);

	if( false == get_option( 'edd_settings_general' ) ) {
		add_option( 'edd_settings_general' );
	}
	if( false == get_option( 'edd_settings_gateways' ) ) {
		add_option( 'edd_settings_gateways' );
	}
	if( false == get_option( 'edd_settings_emails' ) ) {
		add_option( 'edd_settings_emails' );
	}
	if( false == get_option( 'edd_settings_styles' ) ) {
		add_option( 'edd_settings_styles' );
	}
	if( false == get_option( 'edd_settings_taxes' ) ) {
        add_option( 'edd_settings_taxes' );
   	}
	if( false == get_option( 'edd_settings_misc' ) ) {
		add_option( 'edd_settings_misc' );
	}


	add_settings_section(
		'edd_settings_general',
		__('General Settings', 'edd'),
		'__return_false',
		'edd_settings_general'
	);

	foreach( $edd_settings['general'] as $option ) {
		add_settings_field(
			'edd_settings_general[' . $option['id'] . ']',
			$option['name'],
			function_exists( 'edd_' . $option['type'] . '_callback' ) ? 'edd_' . $option['type'] . '_callback' : 'edd_missing_callback',
			'edd_settings_general',
			'edd_settings_general',
			array(
				'id' => $option['id'],
				'desc' => $option['desc'],
				'name' => $option['name'],
				'section' => 'general',
				'size' => isset($option['size']) ? $option['size'] : null,
				'options' => isset($option['options']) ? $option['options'] : '',
				'stripslashes_deep( $value )' => isset($option['std']) ? $option['std'] : ''
			)
		);
	}

	add_settings_section(
		'edd_settings_gateways',
		__('Payment Gateway Settings', 'edd'),
		'__return_false',
		'edd_settings_gateways'
	);

	foreach( $edd_settings['gateways'] as $option ) {
		add_settings_field(
			'edd_settings_gateways[' . $option['id'] . ']',
			$option['name'],
			function_exists( 'edd_' . $option['type'] . '_callback' ) ? 'edd_' . $option['type'] . '_callback' : 'edd_missing_callback',
			'edd_settings_gateways',
			'edd_settings_gateways',
			array(
				'id' => $option['id'],
				'desc' => $option['desc'],
				'name' => $option['name'],
				'section' => 'gateways',
				'size' => isset($option['size']) ? $option['size'] : null,
				'options' => isset($option['options']) ? $option['options'] : '',
				'std' => isset($option['std']) ? $option['std'] : ''
			)
		);
	}

	add_settings_section(
		'edd_settings_emails',
		__('Email Settings', 'edd'),
		'__return_false',
		'edd_settings_emails'
	);

	foreach( $edd_settings['emails'] as $option ) {
		add_settings_field(
			'edd_settings_emails[' . $option['id'] . ']',
			$option['name'],
			function_exists( 'edd_' . $option['type'] . '_callback' ) ? 'edd_' . $option['type'] . '_callback' : 'edd_missing_callback',
			'edd_settings_emails',
			'edd_settings_emails',
			array(
				'id' => $option['id'],
				'desc' => $option['desc'],
				'name' => $option['name'],
				'section' => 'emails',
				'size' => isset($option['size']) ? $option['size'] : null,
				'options' => isset($option['options']) ? $option['options'] : '',
				'std' => isset($option['std']) ? $option['std'] : ''
			)
		);
	}

	add_settings_section(
		'edd_settings_styles',
		__('Style Settings', 'edd'),
		'__return_false',
		'edd_settings_styles'
	);

	foreach( $edd_settings['styles'] as $option ) {
		add_settings_field(
			'edd_settings_styles[' . $option['id'] . ']',
			$option['name'],
			function_exists( 'edd_' . $option['type'] . '_callback' ) ? 'edd_' . $option['type'] . '_callback' : 'edd_missing_callback',
			'edd_settings_styles',
			'edd_settings_styles',
			array(
				'id' => $option['id'],
				'desc' => $option['desc'],
				'name' => $option['name'],
				'section' => 'styles',
				'size' => isset($option['size']) ? $option['size'] : '' ,
				'options' => isset($option['options']) ? $option['options'] : '',
				'std' => isset($option['std']) ? $option['std'] : ''
			)
		);
	}

	add_settings_section(
		'edd_settings_taxes',
		__('Tax Settings', 'edd'),
		'edd_settings_taxes_description_callback',
		'edd_settings_taxes'
	);

	foreach($edd_settings['taxes'] as $option) {
		add_settings_field(
			'edd_settings_taxes[' . $option['id'] . ']',
			$option['name'],
			'edd_' . $option['type'] . '_callback',
			'edd_settings_taxes',
			'edd_settings_taxes',
			array(
				'id' => $option['id'],
				'desc' => $option['desc'],
				'name' => $option['name'],
				'section' => 'taxes',
				'size' => isset($option['size']) ? $option['size'] : '' ,
				'options' => isset($option['options']) ? $option['options'] : '',
				'std' => isset($option['std']) ? $option['std'] : ''
	    	)
		);
	}

	add_settings_section(
		'edd_settings_misc',
		__('Misc Settings', 'edd'),
		'__return_false',
		'edd_settings_misc'
	);

	foreach($edd_settings['misc'] as $option) {
		add_settings_field(
			'edd_settings_misc[' . $option['id'] . ']',
			$option['name'],
			function_exists( 'edd_' . $option['type'] . '_callback' ) ? 'edd_' . $option['type'] . '_callback' : 'edd_missing_callback',
			'edd_settings_misc',
			'edd_settings_misc',
			array(
				'id' => $option['id'],
				'desc' => $option['desc'],
				'name' => $option['name'],
				'section' => 'misc',
				'size' => isset( $option['size'] ) ? $option['size'] : '' ,
				'options' => isset( $option['options'] ) ? $option['options'] : '',
				'std' => isset( $option['std'] ) ? $option['std'] : ''
			)
		);
	}

	// Creates our settings in the options table
	register_setting( 'edd_settings_general', 'edd_settings_general', 'edd_settings_sanitize' );
	register_setting( 'edd_settings_gateways', 'edd_settings_gateways', 'edd_settings_sanitize' );
	register_setting( 'edd_settings_emails', 'edd_settings_emails', 'edd_settings_sanitize' );
	register_setting( 'edd_settings_styles', 'edd_settings_styles', 'edd_settings_sanitize' );
	register_setting( 'edd_settings_taxes', 'edd_settings_taxes', 'edd_settings_sanitize' );
	register_setting( 'edd_settings_misc', 'edd_settings_misc', 'edd_settings_sanitize' );
}
add_action('admin_init', 'edd_register_settings');

/**
 * Settings Taxes Description Callback
 *
 * Renders the taxes section description.
 *
 * @access      private
 * @since       1.3.3
 * @return      void
 */
function edd_settings_taxes_description_callback() {
	echo __('These settings will let you configure simple tax rules for purchases.', 'edd');
}

/**
 * Header Callback
 *
 * Renders the header.
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function edd_header_callback($args) {
	echo '';
}

/**
 * Checkbox Callback
 *
 * Renders checkboxes.
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function edd_checkbox_callback($args) {
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
 * @access      private
 * @since       1.0
 * @return      void
 */
function edd_multicheck_callback($args) {
	global $edd_options;

	foreach( $args['options'] as $key => $option ):
		if( isset( $edd_options[$args['id']][$key] ) ) { $enabled = $option; } else { $enabled = NULL; }
		echo '<input name="edd_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']"" id="edd_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']" type="checkbox" value="' . $option . '" ' . checked($option, $enabled, false) . '/>&nbsp;';
		echo '<label for="edd_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
	endforeach;
	echo '<p class="description">' . $args['desc'] . '</p>';
}

/**
 * Radio Callback
 *
 * Renders radio boxes.
 *
 * @access      private
 * @since       1.3.3
 * @return      void
 */
function edd_radio_callback($args) {

	global $edd_options;

	foreach($args['options'] as $key => $option) :
		$checked = false;
		if( isset( $edd_options[ $args['id'] ] ) && $edd_options[ $args['id'] ] == $key ) $checked = true;
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
 * @access      private
 * @since       1.0
 * @return      void
 */
function edd_gateways_callback($args) {
	global $edd_options;

	foreach( $args['options'] as $key => $option ):
		if( isset( $edd_options['gateways'][$key] ) ) { $enabled = '1'; } else { $enabled = NULL; }
		echo '<input name="edd_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']"" id="edd_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']" type="checkbox" value="1" ' . checked('1', $enabled, false) . '/>&nbsp;';
		echo '<label for="edd_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']">' . $option['admin_label'] . '</label><br/>';
	endforeach;
}


/**
 * Gateways Callback (drop down)
 *
 * Renders gateways select menu
 *
 * @access      private
 * @since       1.5
 * @return      void
 */
function edd_gateway_select_callback($args) {
	global $edd_options;

	echo '<select name="edd_settings_' . $args['section'] . '[' . $args['id'] . ']"" id="edd_settings_' . $args['section'] . '[' . $args['id'] . ']">';
	foreach( $args['options'] as $key => $option ):
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
 * @access      private
 * @since       1.0
 * @return      void
 */
function edd_text_callback($args) {
	global $edd_options;

	if( isset( $edd_options[ $args['id'] ] ) ) { $value = $edd_options[ $args['id'] ]; } else { $value = isset( $args['std'] ) ? $args['std'] : ''; }
	$size = isset( $args['size'] ) && !is_null($args['size']) ? $args['size'] : 'regular';
	$html = '<input type="text" class="' . $args['size'] . '-text" id="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" name="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';
	$html .= '<label for="edd_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Textarea Callback
 *
 * Renders textarea fields.
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function edd_textarea_callback($args) {
	global $edd_options;

	if( isset( $edd_options[ $args['id'] ] ) ) { $value = $edd_options[ $args['id'] ]; } else { $value = isset( $args['std'] ) ? $args['std'] : ''; }
	$size = isset( $args['size'] ) && !is_null($args['size']) ? $args['size'] : 'regular';
	$html = '<textarea class="large-text" cols="50" rows="5" id="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" name="edd_settings_' . $args['section'] . '[' . $args['id'] . ']">' . esc_textarea( $value ) . '</textarea>';
	$html .= '<label for="edd_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Password Callback
 *
 * Renders password fields.
 *
 * @access      private
 * @since       1.3
 * @return      void
 */
function edd_password_callback($args) {
	global $edd_options;

	if( isset( $edd_options[ $args['id'] ] ) ) { $value = $edd_options[ $args['id'] ]; } else { $value = isset( $args['std'] ) ? $args['std'] : ''; }
	$size = isset( $args['size'] ) && !is_null($args['size']) ? $args['size'] : 'regular';
	$html = '<input type="password" class="' . $args['size'] . '-text" id="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" name="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';
	$html .= '<label for="edd_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Missing Callback
 *
 * If a function is missing for settings callbacks alert the user.
 *
 * @access      private
 * @since       1.3.1
 * @return      void
 */
function edd_missing_callback($args) {
	printf( __( 'The callback function used for the <strong>%s</strong> setting is missing.', 'edd' ), $args['id'] );
}

/**
 * Select Callback
 *
 * Renders select fields.
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function edd_select_callback($args) {
	global $edd_options;

	$html = '<select id="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" name="edd_settings_' . $args['section'] . '[' . $args['id'] . ']"/>';
	foreach( $args['options'] as $option => $name ) {
		$selected = isset( $edd_options[ $args['id'] ] ) ? selected( $option, $edd_options[$args['id']], false ) : '';
		$html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
	}
	$html .= '</select>';
	$html .= '<label for="edd_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Rich Editor Callback
 *
 * Renders rich editor fields.
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function edd_rich_editor_callback($args) {
	global $edd_options, $wp_version;

	if( isset( $edd_options[ $args['id'] ] ) ) { $value = $edd_options[ $args['id'] ]; } else { $value = isset( $args['std'] ) ? $args['std'] : ''; }
	if( $wp_version >= 3.3 && function_exists('wp_editor')) {
		$html = wp_editor( $value, 'edd_settings_' . $args['section'] . '[' . $args['id'] . ']', array( 'textarea_name' => 'edd_settings_' . $args['section'] . '[' . $args['id'] . ']' ) );
	} else {
		$html = '<textarea class="large-text" rows="10" id="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" name="edd_settings_' . $args['section'] . '[' . $args['id'] . ']">' . esc_textarea( $value ) . '</textarea>';
	}
	$html .= '<br/><label for="edd_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Upload Callback
 *
 * Renders upload fields.
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function edd_upload_callback($args) {
	global $edd_options;

	if( isset( $edd_options[ $args['id'] ] ) ) { $value = $edd_options[$args['id']]; } else { $value = isset($args['std']) ? $args['std'] : ''; }
	$size = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="' . $args['size'] . '-text edd_upload_field" id="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" name="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';
	$html .= '<span>&nbsp;<input type="button" class="edd_upload_image_button button-secondary" value="' . __('Upload File', 'edd') . '"/></span>';
	$html .= '<label for="edd_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Registers the license field callback for Software Licensing
 * *
 * @access      private
 * @since       1.5
 * @return      void
 */
if ( ! function_exists( 'edd_license_key_callback' ) ) {
	function edd_license_key_callback( $args ) {
		global $edd_options;

		if( isset( $edd_options[ $args['id'] ] ) ) { $value = $edd_options[ $args['id'] ]; } else { $value = isset( $args['std'] ) ? $args['std'] : ''; }
		$size = isset( $args['size'] ) && !is_null($args['size']) ? $args['size'] : 'regular';
		$html = '<input type="text" class="' . $args['size'] . '-text" id="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" name="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';

		if( 'valid' == get_option( $args['options']['is_valid_license_option'] ) ) {
			$html .= wp_nonce_field( $args['id'] . '_nonce', $args['id'] . '_nonce', false );
			$html .= '<input type="submit" class="button-secondary" name="' . $args['id'] . '_deactivate" value="' . __( 'Deactivate License',  'edd-recurring' ) . '"/>';
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
 * @access      private
 * @since       1.0.8.2
 * @return      void
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
 * @access      private
 * @since       1.0.8.2
 * @return      void
 */
function edd_settings_sanitize( $input ) {
	add_settings_error( 'edd-notices', '', __('Settings Updated', 'edd'), 'updated' );
	return $input;
}

/**
 * Get Settings
 *
 * Retrieves all plugin settings and returns them
 * as a combined array.
 *
 * @access      public
 * @since       1.0
 * @return      array
 */
function edd_get_settings() {
	$general_settings = is_array( get_option( 'edd_settings_general' ) )  ? get_option( 'edd_settings_general' )  : array();
	$gateway_settings = is_array( get_option( 'edd_settings_gateways' ) ) ? get_option( 'edd_settings_gateways' ) : array();
	$email_settings   = is_array( get_option( 'edd_settings_emails' ) )   ? get_option( 'edd_settings_emails' )   : array();
	$style_settings   = is_array( get_option( 'edd_settings_styles' ) )   ? get_option( 'edd_settings_styles' )   : array();
	$tax_settings     = is_array( get_option( 'edd_settings_taxes' ) )    ? get_option( 'edd_settings_taxes' )    : array();
	$misc_settings    = is_array( get_option( 'edd_settings_misc' ) )     ? get_option( 'edd_settings_misc' )     : array();

	return array_merge( $general_settings, $gateway_settings, $email_settings, $style_settings, $tax_settings, $misc_settings );
}
