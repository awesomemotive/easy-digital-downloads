<?php
$options = array();


$options[] = array( 'name' => __( 'General', 'geczy' ), 'type' => 'heading' );
$options[] = array( 'name' => __( 'General options', 'geczy' ), 'type' => 'title', 'desc' => __( '', 'geczy' ) );

$options[] = array(
	'id' => 'test_mode',
	'name' => __('Test Mode', 'edd'),
	'desc' => __('While in test mode no live transactions are processed. To fully use test mode, you must have a sandbox (test) account for the payment gateway you are testing.', 'edd'),
	'type' => 'checkbox'
);

$options[] = array(
	'id' => 'purchase_page',
	'name' => __('Checkout Page', 'edd'),
	'desc' => __('This is the checkout page where buyers will complete their purchases', 'edd'),
	'type' => 'single_select_page',
);

$options[] = array(
	'id' => 'success_page',
	'name' => __('Success Page', 'edd'),
	'desc' => __('This is the page buyers are sent to after completing their purchases', 'edd'),
	'type' => 'single_select_page',
);

$options[] = array(
	'id' => 'failure_page',
	'name' => __('Failed Transaction Page', 'edd'),
	'desc' => __('This is the page buyers are sent to if their transaction is cancelled or fails', 'edd'),
	'type' => 'single_select_page',
);

$options[] = array(
	'id' => 'currency_settings',
	'name' => '<strong>' . __('Currency Settings', 'edd') . '</strong>',
	'desc' => __('Configure the currency options', 'edd'),
	'type' => 'header'
);

$options[] = array(
	'id' => 'currency',
	'name' => __('Currency', 'edd'),
	'desc' => __('Choose your currency. Note that some payment gateways have currency restrictions.', 'edd'),
	'type' => 'select',
	'options' => edd_get_currencies()
);

$options[] = array(
	'id' => 'currency_position',
	'name' => __('Currency Position', 'edd'),
	'desc' => __('Choose the location of the currency sign.', 'edd'),
	'type' => 'select',
	'options' => array(
		'before' => __('Before - $10', 'edd'),
		'after' => __('After - 10$', 'edd')
	)
);

$options[] = array(
	'id' => 'thousands_separator',
	'name' => __('Thousands Separator', 'edd'),
	'desc' => __('The symbol (usually , or .) to separate thousands', 'edd'),
	'type' => 'text',
	'size' => 'small',
	'std' => ','
);

$options[] = array(
	'id' => 'decimal_separator',
	'name' => __('Decimal Separator', 'edd'),
	'desc' => __('The symbol (usually , or .) to separate decimal points', 'edd'),
	'type' => 'text',
	'size' => 'small',
	'std' => '.'
);

$options[] = array(
	'id' => 'tracking_settings',
	'name' => '<strong>' . __('Usage Tracking', 'edd') . '</strong>',
	'desc' => '',
	'type' => 'header'
);

$options[] = array(
	'id' => 'presstrends',
	'name' => __('Enable Tracking', 'edd'),
	'desc' => __('Check this box to allow Easy Digital Downloads to track how the plugin is used. No personal info is ever collected. This helps us better improve the plugin.', 'edd'),
	'type' => 'checkbox'
);

$options[] = array( 'name' => __( 'Gateways', 'geczy' ), 'type' => 'heading' );
$options[] = array( 'name' => __( 'Gateways', 'geczy' ), 'type' => 'title', 'desc' => __( 'There\'s awesome options on this page to configure!', 'geczy' ) );

foreach (edd_get_payment_gateways() as $gateway_id => $gateway ) {
	$gateways[$gateway_id] = $gateway['admin_label'];
}

$options[] = array(
	'id' => 'gateways',
	'name' => __('Payment Gateways', 'edd'),
	'type' => 'checkbox',
	'multiple' => true,
	'options' => $gateways,
);


$options[] = array(
	'id' => 'accepted_cards',
	'name' => __('Accepted Payment Method Icons', 'edd'),
	'desc' => __('Display icons for the selected payment methods', 'edd') . '<br/>' . __('You will also need to configure your gateway settings if you are accepting credit cards', 'edd'),
	'css' => 'height:100px;',
	'type' => 'select',
	'multiple' => true,
	'options' => apply_filters('edd_accepted_payment_icons', array(
		'mastercard'      => 'Mastercard',
		'visa'            => 'Visa',
		'americanexpress' => 'American Express',
		'discover'        => 'Discover',
		'paypal'          => 'PayPal'
	) )
);

$options[] = array(
	'id' => 'paypal',
	'name' => '<strong>' . __('PayPal Settings', 'edd') . '</strong>',
	'desc' => __('Configure the PayPal settings', 'edd'),
	'type' => 'header'
);

$options[] = array(
	'id' => 'paypal_email',
	'name' => __('PayPal Email', 'edd'),
	'desc' => __('Enter your PayPal account\'s email', 'edd'),
	'type' => 'text',
	'size' => 'regular'
);

$options[] = array(
	'id' => 'paypal_page_style',
	'name' => __('PayPal Page Style', 'edd'),
	'desc' => __('Enter the name of the page style to use, or leave blank for default', 'edd'),
	'type' => 'text',
	'size' => 'regular'
);

$options[] = array(
	'id' => 'paypal_alternate_verification',
	'name' => __('Alternate PayPal Purchase Verification', 'edd'),
	'desc' => __('If payments are not getting marked as complete, then check this box. Note, this requires that buyers return to your site from PayPal.', 'edd'),
	'type' => 'checkbox'
);

$options[] = array(
	'id' => 'disable_paypal_verification',
	'name' => __('Disable PayPal IPN Verification', 'edd'),
	'desc' => __('If payments are not getting marked as complete, then check this box. This forces the site to use a slightly less secure method of verifying purchases.', 'edd'),
	'type' => 'checkbox'
);

$options[] = array( 'name' => __( 'Emails', 'geczy' ), 'type' => 'heading' );
$options[] = array( 'name' => __( 'Emails', 'geczy' ), 'type' => 'title', 'desc' => __( 'There\'s awesome options on this page to configure!', 'geczy' ) );

$options[] = array(
	'id' => 'email_template',
	'name' => __('Email Template', 'edd'),
	'desc' => __('Choose a template. Click "Save Changes" then "Preview Purchase Receipt" to see the new template.', 'edd'),
	'type' => 'select',
	'options' => edd_get_email_templates()
);

$options[] = array(
	'id' => 'email_settings',
	'name' => '',
	'desc' => '',
	'type' => 'hook',
);

$options[] = array(
	'id' => 'from_name',
	'name' => __('From Name', 'edd'),
	'desc' => __('The name purchase receipts are said to come from. This should probably be your site or shop name.', 'edd'),
	'type' => 'text'
);

$options[] = array(
	'id' => 'from_email',
	'name' => __('From Email', 'edd'),
	'desc' => __('Email to send purchase receipts from. This will act as the "from" and "reply-to" address.', 'edd'),
	'type' => 'text'
);

$options[] = array(
	'id' => 'purchase_subject',
	'name' => __('Purchase Email Subject', 'edd'),
	'desc' => __('Enter the subject line for the purchase receipt email', 'edd'),
	'type' => 'text'
);

$options[] = array(
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
		'{sitename} - ' . __('Your site name', 'edd'),
	'type' => 'rich_editor'
);

$options[] = array(
	'id' => 'admin_notice_emails',
	'name' => __( 'Sale Notification Emails', 'edd' ),
	'desc' => __( 'Enter the email address(es) that should receive a notification anytime a sale is made, one per line', 'edd' ),
	'type' => 'textarea',
	'std'  => get_bloginfo( 'admin_email' )
);

$options[] = array( 'name' => __( 'Styles', 'geczy' ), 'type' => 'heading' );
$options[] = array( 'name' => __( 'Styles', 'geczy' ), 'type' => 'title', 'desc' => __( 'There\'s awesome options on this page to configure!', 'geczy' ) );

$options[] = array(
	'id' => 'disable_styles',
	'name' => __('Disable Styles', 'edd'),
	'desc' => __('Check this to disable all included styling of buttons, checkout fields, and all other elements.', 'edd'),
	'type' => 'checkbox'
);

$options[] = array(
	'id' => 'buton_header',
	'name' => '<strong>' . __('Buttons', 'edd') . '</strong>',
	'desc' => __('Options for add to cart and purchase buttons', 'edd'),
	'type' => 'header',
);

$options[] = array(
	'id' => 'button_style',
	'name' => __('Default Button Style', 'edd'),
	'desc' => __('Choose the style you want to use for the buttons.', 'edd'),
	'type' => 'select',
	'options' => edd_get_button_styles()
);

$options[] = array(
	'id' => 'checkout_color',
	'name' => __('Default Button Color', 'edd'),
	'desc' => __('Choose the color you want to use for the buttons.', 'edd'),
	'type' => 'select',
	'options' => edd_get_button_colors()
);

$options[] = array( 'name' => __( 'Taxes', 'geczy' ), 'type' => 'heading' );
$options[] = array( 'name' => __( 'Taxes', 'geczy' ), 'type' => 'title', 'desc' => __( 'There\'s awesome options on this page to configure!', 'geczy' ) );

$options[] = array(
	'id' => 'enable_taxes',
	'name' => __('Enable Taxes', 'edd'),
	'desc' => __('Check this to enable taxes on purchases.', 'edd'),
	'type' => 'checkbox'
);

$options[] = array(
	'id' => 'tax_rate',
	'name' => __('Tax Rate', 'edd'),
	'desc' => __('Enter a percentage, such as 6.5.', 'edd'),
	'type' => 'text',
	'size' => 'small'
);

$options[] = array(
	'id' => 'prices_include_tax',
	'name' => __('Prices entered with tax', 'edd'),
	'desc' => __('This option effects how you enter prices.', 'edd'),
	'type' => 'radio',
	'options' => array(
		'yes' => __('Yes, I will enter prices inclusive of tax', 'edd'),
		'no'  => __('No, I will enter prices exclusive of tax', 'edd')
	)
);

$options[] = array(
	'id' => 'tax_condition',
	'name' => __('Apply Taxes to:', 'edd'),
	'desc' => __('Who should have tax added to their purchases?', 'edd'),
	'type' => 'radio',
	'options' => array(
		'all' 	=> __('Everyone', 'edd'),
		'local' => __('Local residents only', 'edd')
	)
);

$options[] = array(
	'id' => 'tax_location',
	'name' => __('Tax Opt-In', 'edd'),
	'desc' => __('Customers will be given a checkbox to click if they reside in your local area. Please enter directions for them here. Customers <strong>must</strong> opt into this.', 'edd'),
	'type' => 'text',
	'size' => 'large'
);

$options[] = array(
	'id' => 'checkout_include_tax',
	'name' => __('Display during checkout', 'edd'),
	'desc' => __('', 'edd'),
	'type' => 'select',
	'options' => array(
		'yes' => __('Including tax', 'edd'),
		'no'  => __('Excluding tax', 'edd')
	)
);

$options[] = array(
	'id' => 'taxes_after_discounts',
	'name' => __('Calculate Tax After Discounts?', 'edd'),
	'desc' => __('Check this if you would like taxes calculated after discounts. By default taxes are calculated before discounts are applied.', 'edd'),
	'type' => 'checkbox'
);

$options[] = array( 'name' => __( 'Misc', 'geczy' ), 'type' => 'heading' );
$options[] = array( 'name' => __( 'Misc', 'geczy' ), 'type' => 'title', 'desc' => __( 'There\'s awesome options on this page to configure!', 'geczy' ) );

$options[] = array(
	'id' => 'disable_ajax_cart',
	'name' => __('Scripts', 'edd'),
	'desc' => __('Disable AJAX', 'edd'),
	'type' => 'checkbox'
);

$options[] = array(
	'id' => 'live_cc_validation',
	'desc' => __('Disable live credit card validation', 'edd'),
	'tip' => __('Live credit card validation means that that card type and number will be validated as the customer enters the number.', 'edd'),
	'type' => 'checkbox'
);

$options[] = array(
	'id' => 'redirect_on_add',
	'name' => __('Checkout', 'edd'),
	'desc' => __('Redirect to Checkout', 'edd'),
	'tip' => __('Immediately redirect to checkout after adding an item to the cart?', 'edd'),
	'type' => 'checkbox'
);

$options[] = array(
	'id' => 'logged_in_only',
	'desc' => __('Disable Guest Checkout', 'edd'),
	'tip' => __('Require that users be logged-in to purchase files.', 'edd'),
	'type' => 'checkbox'
);

$options[] = array(
	'id' => 'show_register_form',
	'desc' => __('Show Register / Login Form?', 'edd'),
	'tip' => __('Display the registration and login forms on the checkout page for non-logged-in users.', 'edd'),
	'type' => 'checkbox',
);

$options[] = array(
	'id' => 'download_link_expiration',
	'name' => __('Downloads', 'edd'),
	'desc' => __('Download Link Expiration', 'edd'),
	'tip' => __('How long should download links be valid for? Default is 24 hours from the time they are generated. Enter a time in hours.', 'edd'),
	'type' => 'text',
	'size' => 'small'
);

$options[] = array(
	'id' => 'disable_redownload',
	'desc' => __('Disable Redownload?', 'edd'),
	'tip' => __('Check this if you do not want to allow users to redownload items from their purchase history.', 'edd'),
	'type' => 'checkbox',
);

$options[] = array(
	'id' => 'terms',
	'name' => '<strong>' . __('Terms of Agreement', 'edd') . '</strong>',
	'desc' => '',
	'type' => 'header',
);

$options[] = array(
	'id' => 'show_agree_to_terms',
	'name' => __('Agree to Terms', 'edd'),
	'desc' => __('Check this to show an agree to terms on the checkout that users must agree to before purchasing.', 'edd'),
	'type' => 'checkbox',
);

$options[] = array(
	'id' => 'agree_label',
	'name' => __('Agree to Terms Label', 'edd'),
	'desc' => __('Label shown next to the agree to terms check box.', 'edd'),
	'type' => 'text',
	'size' => 'regular'
);

$options[] = array(
	'id' => 'agree_text',
	'name' => __('Agreement Text', 'edd'),
	'desc' => __('If Agree to Terms is checked, enter the agreement terms here.', 'edd'),
	'type' => 'rich_editor',
);

$options[] = array(
	'id' => 'checkout_label',
	'name' => __('Complete Purchase Text', 'edd'),
	'desc' => __('The button label for completing a purchase.', 'edd'),
	'type' => 'text',
);

$options[] = array(
	'id' => 'add_to_cart_text',
	'name' => __('Add to Cart Text', 'edd'),
	'desc' => __('Text shown on the Add to Cart Buttons', 'edd'),
	'type' => 'text'
);