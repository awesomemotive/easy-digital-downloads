<?php
/**
 * Register Settings
 *
 * @package     Easy Digital Downloads
 * @subpackage  Register Settings
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0 
*/


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
	
	// setup some default option sets
	$pages = get_pages();	
	$pages_options = array();
	if($pages) {
		foreach ( $pages as $page ) {
		  	$pages_options[$page->ID] = $page->post_title;
		}
	}
	
	/* white list our settings, each in their respective section
	   filters can be used to add more options to each section */
	$edd_settings = array(
		'general' => apply_filters('edd_settings_general', 
			array(
				array(
					'id' => 'test_mode',
					'name' => __('Test Mode', 'edd'),
					'desc' => __('While in test mode no live transactions are processed. To fully use test mode, you must have a sandbox (test) account for the payment gateway you are testing.', 'edd'),
					'type' => 'checkbox'
				),
				array(
					'id' => 'purchase_page',
					'name' => __('Purchase Page', 'edd'),
					'desc' => __('This is the checkout page where buyers will complete their purchases', 'edd'),
					'type' => 'select',
					'options' => $pages_options
				),
				array(
					'id' => 'success_page',
					'name' => __('Success Page', 'edd'),
					'desc' => __('This is the page buyers are sent to after completing their purchases', 'edd'),
					'type' => 'select',
					'options' => $pages_options
				),
				array(
					'id' => 'currency_settings',
					'name' => '<strong>' . __('Currency Settings', 'edd') . '</strong>',
					'desc' => __('Configure the currency options', 'edd'),
					'type' => 'header'
				),
				array(
					'id' => 'currency',
					'name' => __('Currency', 'edd'),
					'desc' => __('Choose your currency. Note that some payment gateways have currency restrictions.', 'edd'),
					'type' => 'select',
					'options' => edd_get_currencies()
				),
				array(
					'id' => 'currency_position',
					'name' => __('Currency Position', 'edd'),
					'desc' => __('Choose the location of the currency sign.', 'edd'),
					'type' => 'select',
					'options' => array(
						'before' => __('Before - $10', 'edd'),
						'after' => __('After - 10$', 'edd')
					)
				),
				array(
					'id' => 'thousands_separator',
					'name' => __('Thousands Separator', 'edd'),
					'desc' => __('The symbol (usually , or .) to separate thousands', 'edd'),
					'type' => 'text',
					'size' => 'small',
					'std' => ','
				),
				array(
					'id' => 'decimal_separator',
					'name' => __('Decimal Separator', 'edd'),
					'desc' => __('The symbol (usually , or .) to separate decimal points', 'edd'),
					'type' => 'text',
					'size' => 'small',
					'std' => '.'
				)				
			)
		),
		'gateways' => apply_filters('edd_settings_gateways', 
			array(
				array(
					'id' => 'gateways',
					'name' => __('Payment Gateways', 'edd'),
					'desc' => __('Choose the payment gateways you want to enable.', 'edd'),
					'type' => 'gateways',
					'options' => edd_get_payment_gateways()
				),
				array(
					'id' => 'accepted_cards',
					'name' => __('Accepted Payment Method Icons', 'edd'),
					'desc' => __('Display icons for the selected payment methods', 'edd') . '<br/>' . __('You will also need to configure your gateway settings if you are accepting credit cards', 'edd'),
					'type' => 'multicheck',
					'options' => array(
						'Mastercard',
						'Visa',
						'American Express',
						'Discover',
						'PayPal'
					)
				),
				array(
					'id' => 'paypal',
					'name' => '<strong>' . __('PayPal Settings', 'edd') . '</strong>',
					'desc' => __('Configure the PayPal settings', 'edd'),
					'type' => 'header'
				),
				array(
					'id' => 'paypal_email',
					'name' => __('PayPal Email', 'edd'),
					'desc' => __('Enter your PayPal account\'s email', 'edd'),
					'type' => 'text',
					'size' => 'regular'
				),
				array(
					'id' => 'paypal_disable_curl',
					'name' => __('Disable cURL for PayPal IPN', 'edd'),
					'desc' => __('If payments are not getting marked as complete, check this option', 'edd'),
					'type' => 'checkbox'
				),
				array(
					'id' => 'paypal_alternate_verification',
					'name' => __('Alternate PayPal Purchase Verification', 'edd'),
					'desc' => __('If payments are not getting marked as complete, and disabling cURL does not fix the problem, then check this box', 'edd'),
					'type' => 'checkbox'
				)
			)
		),
		'emails' => apply_filters('edd_settings_emails', 
			array(
				array(
					'id' => 'from_name',
					'name' => __('From Name', 'edd'),
					'desc' => __('The name purchase receipts are said to come from. This should probably be your site or shop name.', 'edd'),
					'type' => 'text'
				),
				array(
					'id' => 'from_email',
					'name' => __('From Email', 'edd'),
					'desc' => __('Email to send purchase receipts from. This will act as the "from" and "reply-to" address.', 'edd'),
					'type' => 'text'
				),
				array(
					'id' => 'purchase_subject',
					'name' => __('Purchase Email Subject', 'edd'),
					'desc' => __('Enter the subject line for the purchase receipt email', 'edd'),
					'type' => 'text'
				),
				array(
					'id' => 'purchase_receipt',
					'name' => __('Purchase Receipt', 'edd'),
					'desc' => __('Enter the email that is sent to users after completing a successful purchase. HTML is accepted. Available template tags:', 'edd') . '<br/>' .
						'{download_list} - ' . __('A list of download URLs for each download purchased', 'edd') . '<br/>' .
						'{name} - ' . __('The buyer\'s name', 'edd') . '<br/>' .
						'{date} - ' . __('The date of the purchase', 'edd') . '<br/>' .
						'{price} - ' . __('The total price of the purchase', 'edd') . '<br/>' .
						'{sitename} - ' . __('Your site name', 'edd'),
					'type' => 'rich_editor'
				)
			)
		),
		'styles' => apply_filters('edd_settings_styles', 
			array(
				array(
					'id' => 'disable_styles',
					'name' => __('Disable Styles', 'edd'),
					'desc' => __('Check this to disable all included styling', 'edd'),
					'type' => 'checkbox'
				),
				array(
					'id' => 'checkout_color',
					'name' => __('Checkout Button Color', 'edd'),
					'desc' => __('Choose the button color you want to use for the checkout buttons.', 'edd'),
					'type' => 'select',
					'options' => edd_get_button_colors()
				)
			)
		),		
		'misc' => apply_filters('edd_settings_misc', 
			array(
				array(
					'id' => 'ajax_cart',
					'name' => __('Enable Ajax', 'edd'),
					'desc' => __('Check this to enable AJAX for the shopping cart.', 'edd'),
					'type' => 'checkbox'
				),
				array(
					'id' => 'jquery_validation',
					'name' => __('Enable jQuery Validation', 'edd'),
					'desc' => __('Check this to enable jQuery validation on the checkout form.', 'edd'),
					'type' => 'checkbox'
				),
				array(
					'id' => 'logged_in_only',
					'name' => __('Disable Guest Checkout', 'edd'),
					'desc' => __('Require that users be logged-in to purchase files.', 'edd'),
					'type' => 'checkbox'
				),
				array(
					'id' => 'show_register_form',
					'name' => __('Show Register / Login Form?', 'edd'),
					'desc' => __('Display the registration and login forms on the checkout page for non-logged-in users', 'edd'),
					'type' => 'checkbox',
				),
				array(
					'id' => 'uses_can_redownload',
					'name' => __('Disable Redownload?', 'edd'),
					'desc' => __('Check this if you do not want to allow users to redownload items from their purchase history', 'edd'),
					'type' => 'checkbox',
				),
				array(
					'id' => 'terms',
					'name' => '<strong>' . __('Terms of Agreement', 'edd') . '</strong>',
					'desc' => '',
					'type' => 'header',
				),
				array(
					'id' => 'show_agree_to_terms',
					'name' => __('Agree to Terms', 'edd'),
					'desc' => __('Check this to show an agree to terms on the checkout that users must agree to before purchasing', 'edd'),
					'type' => 'checkbox',
				),
				array(
					'id' => 'agree_label',
					'name' => __('Agree to Terms Label', 'edd'),
					'desc' => __('Label shown next to the agree to terms check box', 'edd'),
					'type' => 'text',
					'size' => 'regular'
				),
				array(
					'id' => 'agree_text',
					'name' => __('Agreement Text', 'edd'),
					'desc' => __('If Agree to Terms is checked, enter the agreement terms here', 'edd'),
					'type' => 'rich_editor',
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
	if( false == get_option( 'edd_settings_misc' ) ) {  
        add_option( 'edd_settings_misc' );  
   } 
	
	
	add_settings_section(
		'edd_settings_general',
		__('General Settings', 'edd'),
		'edd_settings_general_description_callback',
		'edd_settings_general'
	);
	
	foreach($edd_settings['general'] as $option) {
		add_settings_field(
			'edd_settings_general[' . $option['id'] . ']',
			$option['name'],
			'edd_' . $option['type'] . '_callback',
			'edd_settings_general',
			'edd_settings_general',
			array(
				'id' => $option['id'],
				'desc' => $option['desc'],
				'name' => $option['name'],
				'section' => 'general',
				'size' => isset($option['size']) ? $option['size'] : null,
				'options' => isset($option['options']) ? $option['options'] : '',
				'std' => isset($option['std']) ? $option['std'] : ''
	    	)
		);
	}

	add_settings_section(
		'edd_settings_gateways',
		__('Payment Gateway Settings', 'edd'),
		'edd_settings_gateways_description_callback',
		'edd_settings_gateways'
	);
	
	foreach($edd_settings['gateways'] as $option) {
		add_settings_field(
			'edd_settings_gateways[' . $option['id'] . ']',
			$option['name'],
			'edd_' . $option['type'] . '_callback',
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
		'edd_settings_emails_description_callback',
		'edd_settings_emails'
	);
	
	foreach($edd_settings['emails'] as $option) {
		add_settings_field(
			'edd_settings_emails[' . $option['id'] . ']',
			$option['name'],
			'edd_' . $option['type'] . '_callback',
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
		'edd_settings_styles_description_callback',
		'edd_settings_styles'
	);
	
	foreach($edd_settings['styles'] as $option) {
		add_settings_field(
			'edd_settings_styles[' . $option['id'] . ']',
			$option['name'],
			'edd_' . $option['type'] . '_callback',
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
		'edd_settings_misc',
		__('Misc Settings', 'edd'),
		'edd_settings_misc_description_callback',
		'edd_settings_misc'
	);
	
	foreach($edd_settings['misc'] as $option) {
		add_settings_field(
			'edd_settings_misc[' . $option['id'] . ']',
			$option['name'],
			'edd_' . $option['type'] . '_callback',
			'edd_settings_misc',
			'edd_settings_misc',
			array(
				'id' => $option['id'],
				'desc' => $option['desc'],
				'name' => $option['name'],
				'section' => 'misc',
				'size' => isset($option['size']) ? $option['size'] : '' ,
				'options' => isset($option['options']) ? $option['options'] : '',
				'std' => isset($option['std']) ? $option['std'] : ''
	    	)
		);
	}
	
	// creates our settings in the options table
	register_setting('edd_settings_general', 'edd_settings_general');
	register_setting('edd_settings_gateways', 'edd_settings_gateways');
	register_setting('edd_settings_emails', 'edd_settings_emails');
	register_setting('edd_settings_styles', 'edd_settings_styles');
	register_setting('edd_settings_misc', 'edd_settings_misc');
}
add_action('admin_init', 'edd_register_settings');


/**
 * Settings General Description Callback
 *
 * Renders the general section description.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_settings_general_description_callback() {
	//echo __('Configure the settings below', 'edd');
}


/**
 * Settings Gateways Description Callback
 *
 * Renders the gateways section description.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_settings_gateways_description_callback() {
	//echo __('Configure the settings below', 'edd');
}


/**
 * Settings Emails Description Callback
 *
 * Renders the emails section description.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_settings_emails_description_callback() {
	//echo __('Configure the settings below', 'edd');
}


/**
 * Settings Styles Description Callback
 *
 * Renders the styles section description.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_settings_styles_description_callback() {
	//echo __('Configure the settings below', 'edd');
}


/**
 * Settings Misc Description Callback
 *
 * Renders the misc section description.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_settings_misc_description_callback() {
	//echo __('Configure the settings below', 'edd');
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

	foreach($args['options'] as $key => $option) :
		if(isset($edd_options[$args['id']][$key])) { $enabled = $option; } else { $enabled = NULL; }
		echo '<input name="edd_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']"" id="edd_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']" type="checkbox" value="' . $option . '" ' . checked($option, $enabled, false) . '/>&nbsp;';
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

	foreach($args['options'] as $key => $option) :
		if(isset($edd_options['gateways'][$key])) { $enabled = '1'; } else { $enabled = NULL; }
		echo '<input name="edd_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']"" id="edd_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']" type="checkbox" value="1" ' . checked('1', $enabled, false) . '/>&nbsp;';
		echo '<label for="edd_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']">' . $option['admin_label'] . '</label><br/>';
	endforeach;

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

	if(isset($edd_options[$args['id']])) { $value = $edd_options[$args['id']]; } else { $value = isset($args['std']) ? $args['std'] : ''; }
	$size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
    $html = '<input type="text" class="' . $args['size'] . '-text" id="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" name="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" value="' . $value . '"/>';   
    $html .= '<label for="edd_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';  
 
    echo $html; 
 
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
    foreach($args['options'] as $option => $name) {
		$selected = isset($edd_options[$args['id']]) ? selected($option, $edd_options[$args['id']], false) : '';
		$html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
	}
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
	
	if(isset($edd_options[$args['id']])) { $value = $edd_options[$args['id']]; } else { $value = isset($args['std']) ? $args['std'] : ''; }
    if($wp_version >= 3.3 && function_exists('wp_editor')) {
		$html = wp_editor($value, 'edd_settings_' . $args['section'] . '[' . $args['id'] . ']', array('textarea_name' => 'edd_settings_' . $args['section'] . '[' . $args['id'] . ']'));
    } else {
		$html = '<textarea class="large-text" rows="10" id="edd_settings_' . $args['section'] . '[' . $args['id'] . ']" name="edd_settings_' . $args['section'] . '[' . $args['id'] . ']">' . $value . '</textarea>';
	}	
	$html .= '<br/><label for="edd_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';  
 
    echo $html;
 
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
	$page_settings = is_array(get_option('edd_settings_general')) ? get_option('edd_settings_general') : array();
	$gateway_settings = is_array(get_option('edd_settings_gateways')) ? get_option('edd_settings_gateways') : array();
	$email_settings = is_array(get_option('edd_settings_emails')) ? get_option('edd_settings_emails') : array();
	$style_settings = is_array(get_option('edd_settings_styles')) ? get_option('edd_settings_styles') : array();
	$misc_settings = is_array(get_option('edd_settings_misc')) ? get_option('edd_settings_misc') : array();

	return array_merge($page_settings, $gateway_settings, $email_settings, $style_settings, $misc_settings);
}