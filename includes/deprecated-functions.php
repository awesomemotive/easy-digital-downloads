<?php
/**
 * Deprecated Functions
 *
 * All functions that have been deprecated.
 *
 * @package     EDD
 * @subpackage  Deprecated
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get Download Sales Log
 *
 * Returns an array of sales and sale info for a download.
 *
 * @since       1.0
 * @deprecated  1.3.4
 *
 * @param int $download_id ID number of the download to retrieve a log for
 * @param bool $paginate Whether to paginate the results or not
 * @param int $number Number of results to return
 * @param int $offset Number of items to skip
 *
 * @return mixed array|bool
*/
function edd_get_download_sales_log( $download_id, $paginate = false, $number = 10, $offset = 0 ) {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '1.3.4', null, $backtrace );

	$sales_log = get_post_meta( $download_id, '_edd_sales_log', true );

	if ( $sales_log ) {
		$sales_log = array_reverse( $sales_log );
		$log = array();
		$log['number'] = count( $sales_log );
		$log['sales'] = $sales_log;

		if ( $paginate ) {
			$log['sales'] = array_slice( $sales_log, $offset, $number );
		}

		return $log;
	}

	return false;
}

/**
 * Get File Download Log
 *
 * Returns an array of file download dates and user info.
 *
 * @deprecated 1.3.4
 * @since 1.0
 *
 * @param int $download_id the ID number of the download to retrieve a log for
 * @param bool $paginate whether to paginate the results or not
 * @param int $number the number of results to return
 * @param int $offset the number of items to skip
 *
 * @return mixed array|bool
*/
function edd_get_file_download_log( $download_id, $paginate = false, $number = 10, $offset = 0 ) {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '1.3.4', null, $backtrace );

	$download_log = get_post_meta( $download_id, '_edd_file_download_log', true );

	if ( $download_log ) {
		$download_log = array_reverse( $download_log );
		$log = array();
		$log['number'] = count( $download_log );
		$log['downloads'] = $download_log;

		if ( $paginate ) {
			$log['downloads'] = array_slice( $download_log, $offset, $number );
		}

		return $log;
	}

	return false;
}

/**
 * Get Downloads Of Purchase
 *
 * Retrieves an array of all files purchased.
 *
 * @since 1.0
 * @deprecated 1.4
 *
 * @param int  $payment_id ID number of the purchase
 * @param null $payment_meta
 * @return bool|mixed
 */
function edd_get_downloads_of_purchase( $payment_id, $payment_meta = null ) {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '1.4', 'edd_get_payment_meta_downloads', $backtrace );

	if ( is_null( $payment_meta ) ) {
		$payment_meta = edd_get_payment_meta( $payment_id );
	}

	$downloads = maybe_unserialize( $payment_meta['downloads'] );

	if ( $downloads )
		return $downloads;

	return false;
}

/**
 * Get Menu Access Level
 *
 * Returns the access level required to access the downloads menu. Currently not
 * changeable, but here for a future update.
 *
 * @since 1.0
 * @deprecated 1.4.4
 * @return string
*/
function edd_get_menu_access_level() {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '1.4.4', 'current_user_can(\'manage_shop_settings\')', $backtrace );

	return apply_filters( 'edd_menu_access_level', 'manage_options' );
}



/**
 * Check if only local taxes are enabled meaning users must opt in by using the
 * option set from the EDD Settings.
 *
 * @since 1.3.3
 * @deprecated 1.6
 * @global $edd_options
 * @return bool $local_only
 */
function edd_local_taxes_only() {

	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '1.6', 'no alternatives', $backtrace );

	global $edd_options;

	$local_only = isset( $edd_options['tax_condition'] ) && $edd_options['tax_condition'] == 'local';

	return apply_filters( 'edd_local_taxes_only', $local_only );
}

/**
 * Checks if a customer has opted into local taxes
 *
 * @since 1.4.1
 * @deprecated 1.6
 * @uses EDD_Session::get()
 * @return bool
 */
function edd_local_tax_opted_in() {

	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '1.6', 'no alternatives', $backtrace );

	$opted_in = EDD()->session->get( 'edd_local_tax_opt_in' );
	return ! empty( $opted_in );
}

/**
 * Show taxes on individual prices?
 *
 * @since 1.4
 * @deprecated 1.9
 * @global $edd_options
 * @return bool Whether or not to show taxes on prices
 */
function edd_taxes_on_prices() {
	global $edd_options;

	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '1.9', 'no alternatives', $backtrace );

	return apply_filters( 'edd_taxes_on_prices', isset( $edd_options['taxes_on_prices'] ) );
}

/**
 * Show Has Purchased Item Message
 *
 * Prints a notice when user has already purchased the item.
 *
 * @since 1.0
 * @deprecated 1.8
 * @global $user_ID
 */
function edd_show_has_purchased_item_message() {

	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '1.8', 'no alternatives', $backtrace );

	global $user_ID, $post;

	if( !isset( $post->ID ) )
		return;

	if ( edd_has_user_purchased( $user_ID, $post->ID ) ) {
		$alert = '<p class="edd_has_purchased">' . __( 'You have already purchased this item, but you may purchase it again.', 'edd' ) . '</p>';
		echo apply_filters( 'edd_show_has_purchased_item_message', $alert );
	}
}

/**
 * Flushes the total earning cache when a new payment is created
 *
 * @since 1.2
 * @deprecated 1.8.4
 * @param int $payment Payment ID
 * @param array $payment_data Payment Data
 * @return void
 */
function edd_clear_earnings_cache( $payment, $payment_data ) {

	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '1.8.4', 'no alternatives', $backtrace );

	delete_transient( 'edd_total_earnings' );
}
//add_action( 'edd_insert_payment', 'edd_clear_earnings_cache', 10, 2 );

/**
 * Get Cart Amount
 *
 * @since 1.0
 * @deprecated 1.9
 * @param bool $add_taxes Whether to apply taxes (if enabled) (default: true)
 * @param bool $local_override Force the local opt-in param - used for when not reading $_POST (default: false)
 * @return float Total amount
*/
function edd_get_cart_amount( $add_taxes = true, $local_override = false ) {

	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '1.9', 'edd_get_cart_subtotal() or edd_get_cart_total()', $backtrace );

	$amount = edd_get_cart_subtotal( );
	if ( ! empty( $_POST['edd-discount'] ) || edd_get_cart_discounts() !== false ) {
		// Retrieve the discount stored in cookies
		$discounts = edd_get_cart_discounts();

		// Check for a posted discount
		$posted_discount = isset( $_POST['edd-discount'] ) ? trim( $_POST['edd-discount'] ) : '';

		if ( $posted_discount && ! in_array( $posted_discount, $discounts ) ) {
			// This discount hasn't been applied, so apply it
			$amount = edd_get_discounted_amount( $posted_discount, $amount );
		}

		if( ! empty( $discounts ) ) {
			// Apply the discounted amount from discounts already applied
			$amount -= edd_get_cart_discounted_amount();
		}
	}

	if ( edd_use_taxes() && edd_is_cart_taxed() && $add_taxes ) {
		$tax = edd_get_cart_tax();
		$amount += $tax;
	}

	if( $amount < 0 )
		$amount = 0.00;

	return apply_filters( 'edd_get_cart_amount', $amount, $add_taxes, $local_override );
}

/**
 * Get Purchase Receipt Template Tags
 *
 * Displays all available template tags for the purchase receipt.
 *
 * @since 1.6
 * @deprecated 1.9
 * @author Daniel J Griffiths
 * @return string $tags
 */
function edd_get_purchase_receipt_template_tags() {
	$tags = __('Enter the email that is sent to users after completing a successful purchase. HTML is accepted. Available template tags:', 'edd') . '<br/>' .
			'{download_list} - ' . __('A list of download links for each download purchased', 'edd') . '<br/>' .
			'{file_urls} - ' . __('A plain-text list of download URLs for each download purchased', 'edd') . '<br/>' .
			'{name} - ' . __('The buyer\'s first name', 'edd') . '<br/>' .
			'{fullname} - ' . __('The buyer\'s full name, first and last', 'edd') . '<br/>' .
			'{username} - ' . __('The buyer\'s user name on the site, if they registered an account', 'edd') . '<br/>' .
			'{user_email} - ' . __('The buyer\'s email address', 'edd') . '<br/>' .
			'{billing_address} - ' . __('The buyer\'s billing address', 'edd') . '<br/>' .
			'{date} - ' . __('The date of the purchase', 'edd') . '<br/>' .
			'{subtotal} - ' . __('The price of the purchase before taxes', 'edd') . '<br/>' .
			'{tax} - ' . __('The taxed amount of the purchase', 'edd') . '<br/>' .
			'{price} - ' . __('The total price of the purchase', 'edd') . '<br/>' .
			'{payment_id} - ' . __('The unique ID number for this purchase', 'edd') . '<br/>' .
			'{receipt_id} - ' . __('The unique ID number for this purchase receipt', 'edd') . '<br/>' .
			'{payment_method} - ' . __('The method of payment used for this purchase', 'edd') . '<br/>' .
			'{sitename} - ' . __('Your site name', 'edd') . '<br/>' .
			'{receipt_link} - ' . __( 'Adds a link so users can view their receipt directly on your website if they are unable to view it in the browser correctly.', 'edd' );

	return apply_filters( 'edd_purchase_receipt_template_tags_description', $tags );
}


/**
 * Get Sale Notification Template Tags
 *
 * Displays all available template tags for the sale notification email
 *
 * @since 1.7
 * @deprecated 1.9
 * @author Daniel J Griffiths
 * @return string $tags
 */
function edd_get_sale_notification_template_tags() {
	$tags = __( 'Enter the email that is sent to sale notification emails after completion of a purchase. HTML is accepted. Available template tags:', 'edd' ) . '<br/>' .
			'{download_list} - ' . __('A list of download links for each download purchased', 'edd') . '<br/>' .
			'{file_urls} - ' . __('A plain-text list of download URLs for each download purchased', 'edd') . '<br/>' .
			'{name} - ' . __('The buyer\'s first name', 'edd') . '<br/>' .
			'{fullname} - ' . __('The buyer\'s full name, first and last', 'edd') . '<br/>' .
			'{username} - ' . __('The buyer\'s user name on the site, if they registered an account', 'edd') . '<br/>' .
			'{user_email} - ' . __('The buyer\'s email address', 'edd') . '<br/>' .
			'{billing_address} - ' . __('The buyer\'s billing address', 'edd') . '<br/>' .
			'{date} - ' . __('The date of the purchase', 'edd') . '<br/>' .
			'{subtotal} - ' . __('The price of the purchase before taxes', 'edd') . '<br/>' .
			'{tax} - ' . __('The taxed amount of the purchase', 'edd') . '<br/>' .
			'{price} - ' . __('The total price of the purchase', 'edd') . '<br/>' .
			'{payment_id} - ' . __('The unique ID number for this purchase', 'edd') . '<br/>' .
			'{receipt_id} - ' . __('The unique ID number for this purchase receipt', 'edd') . '<br/>' .
			'{payment_method} - ' . __('The method of payment used for this purchase', 'edd') . '<br/>' .
			'{sitename} - ' . __('Your site name', 'edd');

	return apply_filters( 'edd_sale_notification_template_tags_description', $tags );
}

/**
 * Email Template Header
 *
 * @access private
 * @since 1.0.8.2
 * @deprecated 2.0
 * @return string Email template header
 */
function edd_get_email_body_header() {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '2.0', '', $backtrace );

	ob_start();
	?>
	<html>
	<head>
		<style type="text/css">#outlook a { padding: 0; }</style>
	</head>
	<body dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
	<?php
	do_action( 'edd_email_body_header' );
	return ob_get_clean();
}

/**
 * Email Template Footer
 *
 * @since 1.0.8.2
 * @deprecated 2.0
 * @return string Email template footer
 */
function edd_get_email_body_footer() {

	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '2.0', '', $backtrace );

	ob_start();
	do_action( 'edd_email_body_footer' );
	?>
	</body>
	</html>
	<?php
	return ob_get_clean();
}

/**
 * Applies the Chosen Email Template
 *
 * @since 1.0.8.2
 * @deprecated 2.0
 * @param string $body The contents of the receipt email
 * @param int $payment_id The ID of the payment we are sending a receipt for
 * @param array $payment_data An array of meta information for the payment
 * @return string $email Formatted email with the template applied
 */
function edd_apply_email_template( $body, $payment_id, $payment_data=array() ) {
	global $edd_options;

	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '2.0', '', $backtrace );

	$template_name = isset( $edd_options['email_template'] ) ? $edd_options['email_template'] : 'default';
	$template_name = apply_filters( 'edd_email_template', $template_name, $payment_id );

	if ( $template_name == 'none' ) {
		if ( is_admin() )
			$body = edd_email_preview_template_tags( $body );

		return $body; // Return the plain email with no template
	}

	ob_start();

	do_action( 'edd_email_template_' . $template_name );

	$template = ob_get_clean();

	if ( is_admin() )
		$body = edd_email_preview_template_tags( $body );

	$body = apply_filters( 'edd_purchase_receipt_' . $template_name, $body );

	$email = str_replace( '{email}', $body, $template );

	return $email;

}

/**
 * Checks if the user has enabled the option to calculate taxes after discounts
 * have been entered
 *
 * @since 1.4.1
 * @deprecated 2.1
 * @global $edd_options
 * @return bool Whether or not taxes are calculated after discount
 */
function edd_taxes_after_discounts() {

	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '2.1', 'none', $backtrace );

	global $edd_options;
	$ret = isset( $edd_options['taxes_after_discounts'] ) && edd_use_taxes();
	return apply_filters( 'edd_taxes_after_discounts', $ret );
}

/**
 * Verifies a download purchase using a purchase key and email.
 *
 * @deprecated Please avoid usage of this function in favor of the tokenized urls with edd_validate_url_token()
 * introduced in EDD 2.3
 *
 * @since 1.0
 *
 * @param int    $download_id
 * @param string $key
 * @param string $email
 * @param string $expire
 * @param int    $file_key
 *
 * @return bool True if payment and link was verified, false otherwise
 */
function edd_verify_download_link( $download_id = 0, $key = '', $email = '', $expire = '', $file_key = 0 ) {

	$meta_query = array(
		'relation'  => 'AND',
		array(
			'key'   => '_edd_payment_purchase_key',
			'value' => $key
		),
		array(
			'key'   => '_edd_payment_user_email',
			'value' => $email
		)
	);

	$accepted_stati = apply_filters( 'edd_allowed_download_stati', array( 'publish', 'complete' ) );

	$payments = get_posts( array( 'meta_query' => $meta_query, 'post_type' => 'edd_payment', 'post_status' => $accepted_stati ) );

	if ( $payments ) {
		foreach ( $payments as $payment ) {

			$cart_details = edd_get_payment_meta_cart_details( $payment->ID, true );

			if ( ! empty( $cart_details ) ) {
				foreach ( $cart_details as $cart_key => $cart_item ) {

					if ( $cart_item['id'] != $download_id )
						continue;

					$price_options 	= isset( $cart_item['item_number']['options'] ) ? $cart_item['item_number']['options'] : false;
					$price_id 		= isset( $price_options['price_id'] ) ? $price_options['price_id'] : false;

					$file_condition = edd_get_file_price_condition( $cart_item['id'], $file_key );

					// Check to see if the file download limit has been reached
					if ( edd_is_file_at_download_limit( $cart_item['id'], $payment->ID, $file_key, $price_id ) )
						wp_die( apply_filters( 'edd_download_limit_reached_text', __( 'Sorry but you have hit your download limit for this file.', 'edd' ) ), __( 'Error', 'edd' ), array( 'response' => 403 ) );

					// If this download has variable prices, we have to confirm that this file was included in their purchase
					if ( ! empty( $price_options ) && $file_condition != 'all' && edd_has_variable_prices( $cart_item['id'] ) ) {
						if ( $file_condition == $price_options['price_id'] )
							return $payment->ID;
					}

					// Make sure the link hasn't expired

					if ( base64_encode( base64_decode( $expire, true ) ) === $expire ) {
						$expire = base64_decode( $expire ); // If it is a base64 string, decode it. Old expiration dates were in base64
					}

					if ( current_time( 'timestamp' ) > $expire ) {
						wp_die( apply_filters( 'edd_download_link_expired_text', __( 'Sorry but your download link has expired.', 'edd' ) ), __( 'Error', 'edd' ), array( 'response' => 403 ) );
					}
					return $payment->ID; // Payment has been verified and link is still valid
				}

			}

		}

	} else {
		wp_die( __( 'No payments matching your request were found.', 'edd' ), __( 'Error', 'edd' ), array( 'response' => 403 ) );
	}
	// Payment not verified
	return false;
}