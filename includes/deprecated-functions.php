<?php
/**
 * Deprecated Functions
 *
 * All functions that have been deprecated.
 *
 * @package     EDD
 * @subpackage  Deprecated
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

use EDD\Emails\Registry;
use EDD\Reports;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

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

	if ( $downloads ) {
		return $downloads;
	}

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

	if ( !isset( $post->ID ) ) {
		return;
	}

	if ( edd_has_user_purchased( $user_ID, $post->ID ) ) {
		$alert = '<p class="edd_has_purchased">' . __( 'You have already purchased this item, but you may purchase it again.', 'easy-digital-downloads' ) . '</p>';
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

		if ( ! empty( $discounts ) ) {
			// Apply the discounted amount from discounts already applied
			$amount -= edd_get_cart_discounted_amount();
		}
	}

	if ( edd_use_taxes() && edd_is_cart_taxed() && $add_taxes ) {
		$tax = edd_get_cart_tax();
		$amount += $tax;
	}

	if ( $amount < 0 ) {
		$amount = 0.00;
	}

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
	$tags = __('Enter the email that is sent to users after completing a successful purchase. HTML is accepted. Available template tags:','easy-digital-downloads' ) . '<br/>' .
			'{download_list} - ' . __('A list of download links for each download purchased','easy-digital-downloads' ) . '<br/>' .
			'{file_urls} - ' . __('A plain-text list of download URLs for each download purchased','easy-digital-downloads' ) . '<br/>' .
			'{name} - ' . __('The buyer\'s first name','easy-digital-downloads' ) . '<br/>' .
			'{fullname} - ' . __('The buyer\'s full name, first and last','easy-digital-downloads' ) . '<br/>' .
			'{username} - ' . __('The buyer\'s user name on the site, if they registered an account','easy-digital-downloads' ) . '<br/>' .
			'{user_email} - ' . __('The buyer\'s email address','easy-digital-downloads' ) . '<br/>' .
			'{billing_address} - ' . __('The buyer\'s billing address','easy-digital-downloads' ) . '<br/>' .
			'{date} - ' . __('The date of the purchase','easy-digital-downloads' ) . '<br/>' .
			'{subtotal} - ' . __('The price of the purchase before taxes','easy-digital-downloads' ) . '<br/>' .
			'{tax} - ' . __('The taxed amount of the purchase','easy-digital-downloads' ) . '<br/>' .
			'{price} - ' . __('The total price of the purchase','easy-digital-downloads' ) . '<br/>' .
			'{payment_id} - ' . __('The unique ID number for this purchase','easy-digital-downloads' ) . '<br/>' .
			'{receipt_id} - ' . __('The unique ID number for this purchase receipt','easy-digital-downloads' ) . '<br/>' .
			'{payment_method} - ' . __('The method of payment used for this purchase','easy-digital-downloads' ) . '<br/>' .
			'{sitename} - ' . __('Your site name','easy-digital-downloads' ) . '<br/>' .
			'{receipt_link} - ' . __( 'Adds a link so users can view their receipt directly on your website if they are unable to view it in the browser correctly.', 'easy-digital-downloads' );

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
	$tags = __( 'Enter the email that is sent to sale notification emails after completion of a purchase. HTML is accepted. Available template tags:', 'easy-digital-downloads' ) . '<br/>' .
			'{download_list} - ' . __('A list of download links for each download purchased','easy-digital-downloads' ) . '<br/>' .
			'{file_urls} - ' . __('A plain-text list of download URLs for each download purchased','easy-digital-downloads' ) . '<br/>' .
			'{name} - ' . __('The buyer\'s first name','easy-digital-downloads' ) . '<br/>' .
			'{fullname} - ' . __('The buyer\'s full name, first and last','easy-digital-downloads' ) . '<br/>' .
			'{username} - ' . __('The buyer\'s user name on the site, if they registered an account','easy-digital-downloads' ) . '<br/>' .
			'{user_email} - ' . __('The buyer\'s email address','easy-digital-downloads' ) . '<br/>' .
			'{billing_address} - ' . __('The buyer\'s billing address','easy-digital-downloads' ) . '<br/>' .
			'{date} - ' . __('The date of the purchase','easy-digital-downloads' ) . '<br/>' .
			'{subtotal} - ' . __('The price of the purchase before taxes','easy-digital-downloads' ) . '<br/>' .
			'{tax} - ' . __('The taxed amount of the purchase','easy-digital-downloads' ) . '<br/>' .
			'{price} - ' . __('The total price of the purchase','easy-digital-downloads' ) . '<br/>' .
			'{payment_id} - ' . __('The unique ID number for this purchase','easy-digital-downloads' ) . '<br/>' .
			'{receipt_id} - ' . __('The unique ID number for this purchase receipt','easy-digital-downloads' ) . '<br/>' .
			'{payment_method} - ' . __('The method of payment used for this purchase','easy-digital-downloads' ) . '<br/>' .
			'{sitename} - ' . __('Your site name','easy-digital-downloads' );

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
function edd_apply_email_template( $body, $payment_id, $payment_data = array() ) {
	global $edd_options;

	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '2.0', '', $backtrace );

	$template_name = isset( $edd_options['email_template'] ) ? $edd_options['email_template'] : 'default';
	$template_name = apply_filters( 'edd_email_template', $template_name, $payment_id );

	if ( $template_name == 'none' ) {
		if ( is_admin() ) {
			$body = edd_email_preview_template_tags( $body );
		}

		// Return the plain email with no template
		return $body;
	}

	ob_start();

	do_action( 'edd_email_template_' . $template_name );

	$template = ob_get_clean();

	if ( is_admin() ) {
		$body = edd_email_preview_template_tags( $body );
	}

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

					if ( $cart_item['id'] != $download_id ) {
						continue;
					}

					$price_options 	= isset( $cart_item['item_number']['options'] ) ? $cart_item['item_number']['options'] : false;
					$price_id 		= isset( $price_options['price_id'] ) ? $price_options['price_id'] : false;

					$file_condition = edd_get_file_price_condition( $cart_item['id'], $file_key );

					// Check to see if the file download limit has been reached
					if ( edd_is_file_at_download_limit( $cart_item['id'], $payment->ID, $file_key, $price_id ) ) {
						wp_die( apply_filters( 'edd_download_limit_reached_text', __( 'Sorry but you have hit your download limit for this file.', 'easy-digital-downloads' ) ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
					}

					// If this download has variable prices, we have to confirm that this file was included in their purchase
					if ( ! empty( $price_options ) && $file_condition != 'all' && edd_has_variable_prices( $cart_item['id'] ) ) {
						if ( $file_condition == $price_options['price_id'] ) {
							return $payment->ID;
						}
					}

					// Make sure the link hasn't expired

					if ( base64_encode( base64_decode( $expire, true ) ) === $expire ) {
						$expire = base64_decode( $expire ); // If it is a base64 string, decode it. Old expiration dates were in base64
					}

					if ( current_time( 'timestamp' ) > $expire ) {
						wp_die( apply_filters( 'edd_download_link_expired_text', __( 'Sorry but your download link has expired.', 'easy-digital-downloads' ) ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
					}
					return $payment->ID; // Payment has been verified and link is still valid
				}
			}
		}

	} else {
		wp_die( __( 'No payments matching your request were found.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}
	// Payment not verified
	return false;
}

/**
 * Get Success Page URL
 *
 * @param string $query_string
 * @since       1.0
 * @deprecated  2.6 Please avoid usage of this function in favor of edd_get_success_page_uri()
 * @return      string
*/
function edd_get_success_page_url( $query_string = null ) {

	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '2.6', 'edd_get_success_page_uri()', $backtrace );

	return apply_filters( 'edd_success_page_url', edd_get_success_page_uri( $query_string ) );
}

/**
 * Reduces earnings and sales stats when a purchase is refunded
 *
 * @since 1.8.2
 * @param int $payment_id the ID number of the payment
 * @param string $new_status the status of the payment, probably "publish"
 * @param string $old_status the status of the payment prior to being marked as "complete", probably "pending"
 * @deprecated  2.5.7 Please avoid usage of this function in favor of refund() in EDD_Payment
 * @internal param Arguments $data passed
 */
function edd_undo_purchase_on_refund( $payment_id, $new_status, $old_status ) {

	$backtrace = debug_backtrace();
	_edd_deprecated_function( 'edd_undo_purchase_on_refund', '2.5.7', 'EDD_Payment->refund()', $backtrace );

	$payment = new EDD_Payment( $payment_id );
	$payment->refund();
}

/**
 * Get Earnings By Date
 *
 * @since 1.0
 * @deprecated 2.7
 * @param int $day Day number
 * @param int $month_num Month number
 * @param int $year Year
 * @param int $hour Hour
 * @return int $earnings Earnings
 */
function edd_get_earnings_by_date( $day, $month_num = null, $year = null, $hour = null, $include_taxes = true ) {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '2.7', 'EDD_Payment_Stats()->get_earnings()', $backtrace );

	global $wpdb;

	$args = array(
		'post_type'      => 'edd_payment',
		'nopaging'       => true,
		'year'           => $year,
		'monthnum'       => $month_num,
		'post_status'    => array( 'publish', 'revoked' ),
		'fields'         => 'ids',
		'include_taxes'  => $include_taxes,
		'update_post_term_cache' => false,
	);

	if ( ! empty( $day ) ) {
		$args['day'] = $day;
	}

	if ( ! empty( $hour ) || $hour == 0 ) {
		$args['hour'] = $hour;
	}

	$args   = apply_filters( 'edd_get_earnings_by_date_args', $args );
	$cached = get_transient( 'edd_stats_earnings' );
	$key    = md5( json_encode( $args ) );

	if ( ! isset( $cached[ $key ] ) ) {
		$sales = get_posts( $args );
		$earnings = 0;
		if ( $sales ) {
			$sales = implode( ',', $sales );

			$total_earnings = $wpdb->get_var( "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = '_edd_payment_total' AND post_id IN ({$sales})" );
			$total_tax      = 0;

			if ( ! $include_taxes ) {
				$total_tax = $wpdb->get_var( "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = '_edd_payment_tax' AND post_id IN ({$sales})" );
			}

			$earnings += ( $total_earnings - $total_tax );
		}
		// Cache the results for one hour
		$cached[ $key ] = $earnings;
		set_transient( 'edd_stats_earnings', $cached, HOUR_IN_SECONDS );
	}

	$result = $cached[ $key ];

	return round( $result, 2 );
}

/**
 * Get Sales By Date
 *
 * @since 1.1.4.0
 * @deprecated 2.7
 * @author Sunny Ratilal
 * @param int $day Day number
 * @param int $month_num Month number
 * @param int $year Year
 * @param int $hour Hour
 * @return int $count Sales
 */
function edd_get_sales_by_date( $day = null, $month_num = null, $year = null, $hour = null ) {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '2.7', 'EDD_Payment_Stats()->get_sales()', $backtrace );

	$args = array(
		'post_type'      => 'edd_payment',
		'nopaging'       => true,
		'year'           => $year,
		'fields'         => 'ids',
		'post_status'    => array( 'publish', 'revoked' ),
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false
	);

	$show_free = apply_filters( 'edd_sales_by_date_show_free', true, $args );

	if ( false === $show_free ) {
		$args['meta_query'] = array(
			array(
				'key' => '_edd_payment_total',
				'value' => 0,
				'compare' => '>',
				'type' => 'NUMERIC',
			),
		);
	}

	if ( ! empty( $month_num ) ) {
		$args['monthnum'] = $month_num;
	}

	if ( ! empty( $day ) ) {
		$args['day'] = $day;
	}

	if ( ! empty( $hour ) ) {
		$args['hour'] = $hour;
	}

	$args = apply_filters( 'edd_get_sales_by_date_args', $args  );

	$cached = get_transient( 'edd_stats_sales' );
	$key    = md5( json_encode( $args ) );

	if ( ! isset( $cached[ $key ] ) ) {
		$sales = new WP_Query( $args );
		$count = (int) $sales->post_count;

		// Cache the results for one hour
		$cached[ $key ] = $count;
		set_transient( 'edd_stats_sales', $cached, HOUR_IN_SECONDS );
	}

	$result = $cached[ $key ];

	return $result;
}

/**
 * Set the Page Style for PayPal Purchase page
 *
 * @since 1.4.1
 * @deprecated 2.8
 * @return string
 */
function edd_get_paypal_page_style() {

	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '2.8', 'edd_get_paypal_image_url', $backtrace );

	$page_style = trim( edd_get_option( 'paypal_page_style', 'PayPal' ) );
	return apply_filters( 'edd_paypal_page_style', $page_style );
}

/**
 * Should we add schema.org microdata?
 *
 * @since 1.7
 * @since 3.0 - Deprecated as the switch was made to JSON-LD.
 * @see https://github.com/easydigitaldownloads/easy-digital-downloads/issues/5240
 *
 * @return bool
 */
function edd_add_schema_microdata() {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '3.0', 'EDD_Structured_Data', $backtrace );

	// Don't modify anything until after wp_head() is called
	$ret = (bool)did_action( 'wp_head' );
	return apply_filters( 'edd_add_schema_microdata', $ret );
}

/**
 * Add Microdata to download titles
 *
 * @since 1.5
 * @since 3.0 - Deprecated as the switch was made to JSON-LD.
 * @see https://github.com/easydigitaldownloads/easy-digital-downloads/issues/5240
 *
 * @param string $title Post Title
 * @param int $id Post ID
 * @return string $title New title
 */
function edd_microdata_title( $title, $id = 0 ) {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '3.0', 'EDD_Structured_Data', $backtrace );

	global $post;

	if ( ! edd_add_schema_microdata() || ! is_object( $post ) ) {
		return $title;
	}

	if ( $post->ID == $id && is_singular( 'download' ) && 'download' == get_post_type( intval( $id ) ) ) {
		$title = '<span itemprop="name">' . $title . '</span>';
	}

	return $title;
}

/**
 * Start Microdata to wrapper download
 *
 * @since 2.3
 * @since 3.0 - Deprecated as the switch was made to JSON-LD.
 * @see https://github.com/easydigitaldownloads/easy-digital-downloads/issues/5240
 *
 * @return void
 */
function edd_microdata_wrapper_open( $query ) {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '3.0', 'EDD_Structured_Data', $backtrace );

	static $microdata_open = NULL;

	if ( ! edd_add_schema_microdata() || true === $microdata_open || ! is_object( $query ) ) {
		return;
	}

	if ( $query && ! empty( $query->query['post_type'] ) && $query->query['post_type'] == 'download' && is_singular( 'download' ) && $query->is_main_query() ) {
		$microdata_open = true;
		echo '<div itemscope itemtype="http://schema.org/Product">';
	}
}

/**
 * End Microdata to wrapper download
 *
 * @since 2.3
 * @since 3.0 - Deprecated as the switch was made to JSON-LD.
 * @see https://github.com/easydigitaldownloads/easy-digital-downloads/issues/5240
 *
 * @return void
 */
function edd_microdata_wrapper_close() {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '3.0', 'EDD_Structured_Data', $backtrace );

	global $post;

	static $microdata_close = NULL;

	if ( ! edd_add_schema_microdata() || true === $microdata_close || ! is_object( $post ) ) {
		return;
	}

	if ( $post && $post->post_type == 'download' && is_singular( 'download' ) && is_main_query() ) {
		$microdata_close = true;
		echo '</div>';
	}
}

/**
 * Add Microdata to download description
 *
 * @since 1.5
 * @since 3.0 - Deprecated as the switch was made to JSON-LD.
 * @see https://github.com/easydigitaldownloads/easy-digital-downloads/issues/5240
 *
 * @param $content
 * @return mixed|void New title
 */
function edd_microdata_description( $content ) {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '3.0', 'EDD_Structured_Data', $backtrace );

	global $post;

	static $microdata_description = NULL;

	if ( ! edd_add_schema_microdata() || true === $microdata_description || ! is_object( $post ) ) {
		return $content;
	}

	if ( $post && $post->post_type == 'download' && is_singular( 'download' ) && is_main_query() ) {
		$microdata_description = true;
		$content = apply_filters( 'edd_microdata_wrapper', '<div itemprop="description">' . $content . '</div>' );
	}
	return $content;
}

/**
 * Output schema markup for single price products.
 *
 * @since  2.6.14
 * @since 3.0 - Deprecated as the switch was made to JSON-LD.
 * @see https://github.com/easydigitaldownloads/easy-digital-downloads/issues/5240
 *
 * @param  int $download_id The download being output.
 * @return void
 */
function edd_purchase_link_single_pricing_schema( $download_id = 0, $args = array() ) {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '3.0', 'EDD_Structured_Data', $backtrace );

	// Bail if the product has variable pricing, or if we aren't showing schema data.
	if ( edd_has_variable_prices( $download_id ) || ! edd_add_schema_microdata() ) {
		return;
	}

	// Grab the information we need.
	$download = new EDD_Download( $download_id );
	?>
    <span itemprop="offers" itemscope itemtype="http://schema.org/Offer">
		<meta itemprop="price" content="<?php echo esc_attr( $download->price ); ?>" />
		<meta itemprop="priceCurrency" content="<?php echo esc_attr( edd_get_currency() ); ?>" />
	</span>
	<?php
}

/**
 * Renders the Logs tab in the Reports screen.
 *
 * @since 1.3
 * @deprecated 3.0 Use edd_tools_tab_logs() instead.
 * @see edd_tools_tab_logs()
 * @return void
 */
function edd_reports_tab_logs() {
	_edd_deprecated_function( __FUNCTION__, '3.0', 'edd_tools_tab_logs' );

	if ( ! function_exists( 'edd_tools_tab_logs' ) ) {
		require_once EDD_PLUGIN_DIR . 'includes/admin/tools/logs.php';
	}

	edd_tools_tab_logs();
}

/**
 * Defines views for the legacy 'Reports' tab.
 *
 * @since 1.4
 * @deprecated 3.0 Use \EDD\Reports\get_reports()
 * @see \EDD\Reports\get_reports()
 *
 * @return array $views Report Views
 */
function edd_reports_default_views() {
	_edd_deprecated_function( __FUNCTION__, '3.0', '\EDD\Reports\get_reports' );

	return Reports\get_reports();
}

/**
 * Renders the Reports page
 *
 * @since 1.3
 * @deprecated 3.0 Unused.
 */
function edd_reports_tab_reports() {

	_edd_deprecated_function( __FUNCTION__, '3.0' );

	if ( ! current_user_can( 'view_shop_reports' ) ) {
		wp_die( __( 'You do not have permission to access this report', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	$current_view = 'earnings';
	$views        = edd_reports_default_views();

	if ( isset( $_GET['view'] ) && array_key_exists( $_GET['view'], $views ) ) {
		$current_view = $_GET['view'];
	}

	/**
	 * Legacy: fired inside the old global 'Reports' tab.
	 *
	 * The dynamic portion of the hook name, `$current_view`, represented the parsed value of
	 * the 'view' query variable.
	 *
	 * @since 1.3
	 * @deprecated 3.0 Unused.
	 */
	edd_do_action_deprecated( 'edd_reports_view_' . $current_view, array(), '3.0' );

}

/**
 * Default Report Views
 *
 * Checks the $_GET['view'] parameter to ensure it exists within the default allowed views.
 *
 * @param string $default Default view to use.
 *
 * @since 1.9.6
 * @deprecated 3.0 Unused.
 *
 * @return string $view Report View
 */
function edd_get_reporting_view( $default = 'earnings' ) {

	_edd_deprecated_function( __FUNCTION__, '3.0' );

	if ( ! isset( $_GET['view'] ) || ! in_array( $_GET['view'], array_keys( edd_reports_default_views() ) ) ) {
		$view = $default;
	} else {
		$view = $_GET['view'];
	}

	/**
	 * Legacy: filters the current reporting view (now implemented solely via the 'tab' var).
	 *
	 * @since 1.9.6
	 * @deprecated 3.0 Unused.
	 *
	 * @param string $view View slug.
	 */
	return edd_apply_filters_deprecated( 'edd_get_reporting_view', array( $view ), '3.0' );
}

/**
 * Renders the Reports Page Views Drop Downs
 *
 * @since 1.3
 * @deprecated 3.0 Unused.
 *
 * @return void
 */
function edd_report_views() {

	_edd_deprecated_function( __FUNCTION__, '3.0' );

	/**
	 * Legacy: fired before the view actions drop-down was output.
	 *
	 * @since 1.3
	 * @deprecated 3.0 Unused.
	 */
	edd_do_action_deprecated( 'edd_report_view_actions', array(), '3.0' );

	/**
	 * Legacy: fired after the view actions drop-down was output.
	 *
	 * @since 1.3
	 * @deprecated 3.0 Unused.
	 */
	edd_do_action_deprecated( 'edd_report_view_actions_after', array(), '3.0' );

	return;
}

/**
 * Show report graph date filters.
 *
 * @since 1.3
 * @deprecated 3.0 Unused.
 */
function edd_reports_graph_controls() {
	_edd_deprecated_function( __FUNCTION__, 'EDD 3.0' );
}

/**
 * Sets up the dates used to filter graph data
 *
 * Date sent via $_GET is read first and then modified (if needed) to match the
 * selected date-range (if any)
 *
 * @since 1.3
 * @deprecated 3.0 Use \EDD\Reports\get_dates_filter() instead
 * @see \EDD\Reports\get_dates_filter()
 *
 * @param string $timezone Optional. Timezone to force for report filter dates calculations.
 *                         Default is the WP timezone.
 * @return array Array of report filter dates.
 */
function edd_get_report_dates( $timezone = null ) {

	_edd_deprecated_function( __FUNCTION__, '3.0', '\EDD\Reports\get_dates_filter' );

	Reports\Init::bootstrap();

	add_filter( 'edd_get_dates_filter_range', '\EDD\Reports\compat_filter_date_range' );

	$filter_dates = Reports\get_dates_filter( 'objects', $timezone );
	$range        = Reports\get_dates_filter_range();

	remove_filter( 'edd_get_report_dates_default_range', '\EDD\Reports\compat_filter_date_range' );

	$dates = array(
		'range'    => $range,
		'day'      => $filter_dates['start']->format( 'd' ),
		'day_end'  => $filter_dates['end']->format( 'd' ),
		'm_start'  => $filter_dates['start']->month,
		'm_end'    => $filter_dates['end']->month,
		'year'     => $filter_dates['start']->year,
		'year_end' => $filter_dates['end']->year,
	);

	/**
	 * Filters the legacy list of parsed report dates for use in the Reports API.
	 *
	 * @since 1.3
	 * @deprecated 3.0
	 *
	 * @param array $dates Array of legacy date parts.
	 */
	return edd_apply_filters_deprecated( 'edd_report_dates', array( $dates ), '3.0' );
}

/**
 * Intercept default Edit post links for EDD orders and rewrite them to the View Order Details screen.
 *
 * @since 1.8.3
 * @deprecated 3.0 No alternative present as get_post() does not work with orders.
 *
 * @param $url
 * @param $post_id
 * @param $context
 *
 * @return string
 */
function edd_override_edit_post_for_payment_link( $url = '', $post_id = 0, $context = '') {
	_edd_deprecated_function( __FUNCTION__, '3.0', '' );

	$post = get_post( $post_id );

	if ( empty( $post ) ) {
		return $url;
	}

	if ( 'edd_payment' !== $post->post_type ) {
		return $url;
	}

	return edd_get_admin_url( array(
		'page' => 'edd-payment-history',
		'view' => 'view-order-details',
		'id'   => absint( $post_id ),
	) );
}

/**
 * Record sale as a log.
 *
 * Stores log information for a download sale.
 *
 * @since 1.0
 * @deprecated 3.0 Sales logs are no longed stored.
 *
 * @param int    $download_id Download ID
 * @param int    $payment_id  Payment ID.
 * @param int    $price_id    Optional. Price ID.
 * @param string $sale_date   Optional. Date of the sale.
 */
function edd_record_sale_in_log( $download_id, $payment_id, $price_id = false, $sale_date = null ) {
	_edd_deprecated_function( __FUNCTION__, '3.0' );

	$edd_logs = EDD()->debug_log;

	$log_data = array(
		'post_parent'   => $download_id,
		'log_type'      => 'sale',
		'post_date'     => ! empty( $sale_date ) ? $sale_date : null,
		'post_date_gmt' => ! empty( $sale_date ) ? get_gmt_from_date( $sale_date ) : null,
	);

	$log_meta = array(
		'payment_id' => $payment_id,
		'price_id'   => (int) $price_id,
	);

	$edd_logs->insert_log( $log_data, $log_meta );
}

/**
 * Outputs the JavaScript code for the Agree to Terms section to toggle
 * the T&Cs text
 *
 * @since 1.0
 * @deprecated 3.0 Moved to external scripts in assets/js/frontend/checkout/components/agree-to-terms
 */
function edd_agree_to_terms_js() {
	_edd_deprecated_function( __FUNCTION__, '3.0' );
}

/**
 * Record payment status change
 *
 * @since 1.4.3
 * @deprecated since 3.0
 * @param int    $payment_id the ID number of the payment.
 * @param string $new_status the status of the payment, probably "publish".
 * @param string $old_status the status of the payment prior to being marked as "complete", probably "pending".
 * @return void
 */
function edd_record_status_change( $payment_id, $new_status, $old_status ) {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '3.0', 'edd_record_order_status_change', $backtrace );

	// Get the list of statuses so that status in the payment note can be translated
	$stati      = edd_get_payment_statuses();
	$old_status = isset( $stati[ $old_status ] ) ? $stati[ $old_status ] : $old_status;
	$new_status = isset( $stati[ $new_status ] ) ? $stati[ $new_status ] : $new_status;

	$status_change = sprintf( __( 'Status changed from %s to %s', 'easy-digital-downloads' ), $old_status, $new_status );

	edd_insert_payment_note( $payment_id, $status_change );
}

/**
 * Shows checkbox to automatically refund payments made in PayPal.
 *
 * @deprecated 3.0 In favour of `edd_paypal_refund_checkbox()`
 * @see edd_paypal_refund_checkbox()
 *
 * @since  2.6.0
 *
 * @param int $payment_id The current payment ID.
 * @return void
 */
function edd_paypal_refund_admin_js( $payment_id = 0 ) {

	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '3.0', 'edd_paypal_refund_checkbox', $backtrace );

	// If not the proper gateway, return early.
	if ( 'paypal' !== edd_get_payment_gateway( $payment_id ) ) {
		return;
	}

	// If our credentials are not set, return early.
	$key       = edd_get_payment_meta( $payment_id, '_edd_payment_mode', true );
	$username  = edd_get_option( 'paypal_' . $key . '_api_username' );
	$password  = edd_get_option( 'paypal_' . $key . '_api_password' );
	$signature = edd_get_option( 'paypal_' . $key . '_api_signature' );

	if ( empty( $username ) || empty( $password ) || empty( $signature ) ) {
		return;
	}

	// Localize the refund checkbox label.
	$label = __( 'Refund Payment in PayPal', 'easy-digital-downloads' );

	?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('select[name=edd-payment-status]').change(function() {
				if ( 'refunded' === $(this).val() ) {
					$(this).parent().parent().append('<input type="checkbox" id="edd-paypal-refund" name="edd-paypal-refund" value="1" style="margin-top:0">');
					$(this).parent().parent().append('<label for="edd-paypal-refund"><?php echo $label; ?></label>');
				} else {
					$('#edd-paypal-refund').remove();
					$('label[for="edd-paypal-refund"]').remove();
				}
			});
		});
	</script>
	<?php
}

/**
 * Possibly refunds a payment made with PayPal Standard or PayPal Express.
 *
 * @deprecated 3.0 In favour of `edd_paypal_maybe_refund_transaction()`
 * @see edd_paypal_maybe_refund_transaction()
 *
 * @since  2.6.0
 *
 * @param object|EDD_Payment $payment The current payment ID.
 * @return void
 */
function edd_maybe_refund_paypal_purchase( EDD_Payment $payment ) {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '3.0', 'edd_paypal_maybe_refund_transaction', $backtrace );

	if ( ! current_user_can( 'edit_shop_payments', $payment->ID ) ) {
		return;
	}

	if ( empty( $_POST['edd-paypal-refund'] ) ) {
		return;
	}

	$processed = $payment->get_meta( '_edd_paypal_refunded', true );

	// If the status is not set to "refunded", return early.
	if ( 'complete' !== $payment->old_status && 'revoked' !== $payment->old_status ) {
		return;
	}

	// If not PayPal/PayPal Express, return early.
	if ( 'paypal' !== $payment->gateway ) {
		return;
	}

	// If the payment has already been refunded in the past, return early.
	if ( $processed ) {
		return;
	}

	// Process the refund in PayPal.
	edd_refund_paypal_purchase( $payment );
}

/**
 * Jilt Callback
 *
 * Renders Jilt Settings
 *
 * @deprecated 2.10.2
 *
 * @param array $args arguments passed by the setting.
 * @return void
 */
function edd_jilt_callback( $args ) {

	_edd_deprecated_function( __FUNCTION__, '2.10.2' );

	$activated   = is_callable( 'edd_jilt' );
	$connected   = $activated && edd_jilt()->get_integration()->is_jilt_connected();
	$connect_url = $activated ? edd_jilt()->get_connect_url() : '';
	$account_url = $connected ? edd_jilt()->get_integration()->get_jilt_app_url() : '';

	echo wp_kses_post( $args['desc'] );

	if ( $activated ) :
		?>

		<?php if ( $connected ) : ?>

		<p>
			<button id="edd-jilt-disconnect" class="button"><?php esc_html_e( 'Disconnect Jilt', 'easy-digital-downloads' ); ?></button>
		</p>

		<p>
			<?php
			wp_kses_post(
				sprintf(
				/* Translators: %1$s - <a> tag, %2$s - </a> tag */
					__( '%1$sClick here%2$s to visit your Jilt dashboard', 'easy-digital-downloads' ),
					'<a href="' . esc_url( $account_url ) . '" target="_blank">',
					'</a>'
				)
			);
			?>
		</p>

	<?php else : ?>

		<p>
			<a id="edd-jilt-connect" class="button button-primary" href="<?php echo esc_url( $connect_url ); ?>">
				<?php esc_html_e( 'Connect to Jilt', 'easy-digital-downloads' ); ?>
			</a>
		</p>

	<?php endif; ?>

	<?php elseif( current_user_can( 'install_plugins' ) ) : ?>

		<p>
			<button id="edd-jilt-connect" class="button button-primary">
				<?php esc_html_e( 'Install Jilt', 'easy-digital-downloads' ); ?>
			</button>
		</p>

	<?php
	endif;
}

/**
 * Handle installation and activation for Jilt via AJAX
 *
 * @deprecated 2.10.2
 * @since n.n.n
 */
function edd_jilt_remote_install_handler() {

	_edd_deprecated_function( __FUNCTION__, '2.10.2' );

	if ( ! current_user_can( 'manage_shop_settings' ) || ! current_user_can( 'install_plugins' ) ) {
		wp_send_json_error(
			array(
				'error' => __( 'You do not have permission to do this.', 'easy-digital-downloads' ),
			)
		);
	}

	include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	include_once ABSPATH . 'wp-admin/includes/file.php';
	include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

	$plugins = get_plugins();

	if ( ! array_key_exists( 'jilt-for-edd/jilt-for-edd.php', $plugins ) ) {
		/*
		* Use the WordPress Plugins API to get the plugin download link.
		*/
		$api = plugins_api(
			'plugin_information',
			array(
				'slug' => 'jilt-for-edd',
			)
		);

		if ( is_wp_error( $api ) ) {
			wp_send_json_error(
				array(
					'error' => $api->get_error_message(),
					'debug' => $api,
				)
			);
		}

		/*
		* Use the AJAX Upgrader skin to quietly install the plugin.
		*/
		$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
		$install  = $upgrader->install( $api->download_link );
		if ( is_wp_error( $install ) ) {
			wp_send_json_error(
				array(
					'error' => $install->get_error_message(),
					'debug' => $api,
				)
			);
		}

		activate_plugin( $upgrader->plugin_info() );

	} else {

		activate_plugin( 'jilt-for-edd/jilt-for-edd.php' );
	}

	/*
	* Final check to see if Jilt is available.
	*/
	if ( ! class_exists( 'EDD_Jilt_Loader' ) ) {
		wp_send_json_error(
			array(
				'error' => __( 'Something went wrong. Jilt was not installed correctly.', 'easy-digital-downloads' ),
			)
		);
	}

	wp_send_json_success();
}

/**
 * Handle connection for Jilt via AJAX
 *
 * @deprecated 2.10.2
 * @since n.n.n
 */
function edd_jilt_connect_handler() {

	_edd_deprecated_function( __FUNCTION__, '2.10.2' );

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_send_json_error(
			array(
				'error' => __( 'You do not have permission to do this.', 'easy-digital-downloads' ),
			)
		);
	}

	if ( ! is_callable( 'edd_jilt' ) ) {
		wp_send_json_error(
			array(
				'error' => __( 'Something went wrong. Jilt was not installed correctly.', 'easy-digital-downloads' ),
			)
		);
	}

	wp_send_json_success( array( 'connect_url' => edd_jilt()->get_connect_url() ) );
}

/**
 * Handle disconnection and deactivation for Jilt via AJAX
 *
 * @deprecated 2.10.2
 * @since n.n.n
 */
function edd_jilt_disconnect_handler() {

	_edd_deprecated_function( __FUNCTION__, '2.10.2' );

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_send_json_error(
			array(
				'error' => __( 'You do not have permission to do this.', 'easy-digital-downloads' ),
			)
		);
	}

	if ( is_callable( 'edd_jilt' ) ) {

		edd_jilt()->get_integration()->unlink_shop();
		edd_jilt()->get_integration()->revoke_authorization();
		edd_jilt()->get_integration()->clear_connection_data();
	}

	deactivate_plugins( 'jilt-for-edd/jilt-for-edd.php' );

	wp_send_json_success();
}

/**
 * Maybe adds a notice to abandoned payments if Jilt isn't installed.
 *
 * @deprecated 2.10.2
 * @since n.n.n
 *
 * @param int $payment_id The ID of the abandoned payment, for which a jilt notice is being thrown.
 */
function maybe_add_jilt_notice_to_abandoned_payment( $payment_id ) {

	_edd_deprecated_function( __FUNCTION__, '2.10.2' );

	if ( ! is_callable( 'edd_jilt' )
		&& ! is_plugin_active( 'recapture-for-edd/recapture.php' )
		&& 'abandoned' === edd_get_payment_status( $payment_id )
		&& ! get_user_meta( get_current_user_id(), '_edd_try_jilt_dismissed', true )
	) {
		?>
		<div class="notice notice-warning jilt-notice">
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						/* Translators: %1$s - <strong> tag, %2$s - </strong> tag, %3$s - <a> tag, %4$s - </a> tag */
						__( '%1$sRecover abandoned purchases like this one.%2$s %3$sTry Jilt for free%4$s.', 'easy-digital-downloads' ),
						'<strong>',
						'</strong>',
						'<a href="https://easydigitaldownloads.com/downloads/jilt" target="_blank">',
						'</a>'
					)
				);
				?>
			</p>
			<?php
			echo wp_kses_post(
				sprintf(
					/* Translators: %1$s - Opening anchor tag, %2$s - The url to dismiss the ajax notice, %3$s - Complete the opening of the anchor tag, %4$s - Open span tag, %4$s - Close span tag */
					__( '%1$s %2$s %3$s %4$s Dismiss this notice. %5$s', 'easy-digital-downloads' ),
					'<a href="',
					esc_url(
						add_query_arg(
							array(
								'edd_action' => 'dismiss_notices',
								'edd_notice' => 'try_jilt',
							)
						)
					),
					'" type="button" class="notice-dismiss">',
					'<span class="screen-reader-text">',
					'</span>
				</a>'
				)
			);
			?>
		</div>
		<?php
	}
}

/**
 * SendWP Callback
 *
 * Renders SendWP Settings
 *
 * @since 2.9.15
 * @param array $args Arguments passed by the setting
 * @return void
 */
function edd_sendwp_callback( $args ) {

	_edd_deprecated_function( __FUNCTION__, '2.11.4' );

	// Connection status partial label based on the state of the SendWP email sending setting (Tools -> SendWP)
	$connected  = '<a href="https://app.sendwp.com/dashboard" target="_blank" rel="noopener noreferrer">';
	$connected .= __( 'Access your SendWP account', 'easy-digital-downloads' );
	$connected .= '</a>.';

	$disconnected = sprintf(
		__( '<em><strong>Note:</strong> Email sending is currently disabled. <a href="' . esc_url( admin_url( 'tools.php?page=sendwp' ) ) . '">Click here</a> to enable it.</em>', 'easy-digital-downloads' )
	);

	// Checks if SendWP is connected
	$client_connected = function_exists( 'sendwp_client_connected' ) && sendwp_client_connected() ? true : false;

	// Checks if email sending is enabled in SendWP
	$forwarding_enabled = function_exists( 'sendwp_forwarding_enabled' ) && sendwp_forwarding_enabled() ? true : false;

	ob_start();

	echo $args['desc'];

	// Output the appropriate button and label based on connection status
	if( $client_connected ) :
		?>
		<div class="inline notice notice-success">
			<p><?php _e( 'SendWP plugin activated.', 'easy-digital-downloads' ); ?> <?php echo $forwarding_enabled ? $connected : $disconnected ; ?></p>

			<p>
				<button id="edd-sendwp-disconnect" class="button"><?php _e( 'Disconnect SendWP', 'easy-digital-downloads' ); ?></button>
			</p>
		</div>
		<?php
	else :
		?>
		<p>
			<?php _e( 'We recommend SendWP to ensure quick and reliable delivery of all emails sent from your store, such as purchase receipts, subscription renewal reminders, password resets, and more.', 'easy-digital-downloads' ); ?> <?php printf( __( '%sLearn more%s', 'easy-digital-downloads' ), '<a href="https://sendwp.com/" target="_blank" rel="noopener noreferrer">', '</a>' ); ?>
		</p>
		<p>
			<button type="button" id="edd-sendwp-connect" class="button button-primary"><?php esc_html_e( 'Connect with SendWP', 'easy-digital-downloads' ); ?>
			</button>
		</p>

		<?php
	endif;

	echo ob_get_clean();
}

/**
 * Handle installation and connection for SendWP via ajax
 *
 * @since 2.9.15
 */
function edd_sendwp_remote_install_handler () {

	_edd_deprecated_function( __FUNCTION__, '2.11.4' );

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_send_json_error( array(
			'error' => __( 'You do not have permission to do this.', 'easy-digital-downloads' )
		) );
	}

	include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	include_once ABSPATH . 'wp-admin/includes/file.php';
	include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

	$plugins = get_plugins();

	if( ! array_key_exists( 'sendwp/sendwp.php', $plugins ) ) {

		/*
		* Use the WordPress Plugins API to get the plugin download link.
		*/
		$api = plugins_api( 'plugin_information', array(
			'slug' => 'sendwp',
		) );

		if ( is_wp_error( $api ) ) {
			wp_send_json_error( array(
				'error' => $api->get_error_message(),
				'debug' => $api
			) );
		}

		/*
		* Use the AJAX Upgrader skin to quietly install the plugin.
		*/
		$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
		$install = $upgrader->install( $api->download_link );
		if ( is_wp_error( $install ) ) {
			wp_send_json_error( array(
				'error' => $install->get_error_message(),
				'debug' => $api
			) );
		}

		$activated = activate_plugin( $upgrader->plugin_info() );

	} else {

		$activated = activate_plugin( 'sendwp/sendwp.php' );

	}

	/*
	* Final check to see if SendWP is available.
	*/
	if( ! function_exists('sendwp_get_server_url') ) {
		wp_send_json_error( array(
			'error' => __( 'Something went wrong. SendWP was not installed correctly.', 'easy-digital-downloads' )
		) );
	}

	wp_send_json_success( array(
		'partner_id'      => 81,
		'register_url'    => sendwp_get_server_url() . '_/signup',
		'client_name'     => sendwp_get_client_name(),
		'client_secret'   => sendwp_get_client_secret(),
		'client_redirect' => admin_url( 'edit.php?post_type=download&page=edd-settings&tab=emails&edd-message=sendwp-connected' ),
	) );
}
add_action( 'wp_ajax_edd_sendwp_remote_install', 'edd_sendwp_remote_install_handler' );

/**
 * Handle deactivation of SendWP via ajax
 *
 * @since 2.9.15
 */
function edd_sendwp_disconnect () {

	_edd_deprecated_function( __FUNCTION__, '2.11.4' );

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_send_json_error( array(
			'error' => __( 'You do not have permission to do this.', 'easy-digital-downloads' )
		) );
	}

	sendwp_disconnect_client();

	deactivate_plugins( 'sendwp/sendwp.php' );

	wp_send_json_success();
}
add_action( 'wp_ajax_edd_sendwp_disconnect', 'edd_sendwp_disconnect' );

/**
 * Reverts to the original download URL validation.
 *
 * @since 2.11.4
 * @todo  Remove this function in 3.0.
 *
 * @param bool   $ret
 * @param string $url
 * @param array  $query_args
 * @param string $original_url
 */
add_filter( 'edd_validate_url_token', function( $ret, $url, $query_args, $original_url ) {
	// If the URL is already validated, we don't need to validate it again.
	if ( $ret ) {
		return $ret;
	}
	$allowed = edd_get_url_token_parameters();
	$remove  = array();
	foreach ( $query_args as $key => $value ) {
		if ( ! in_array( $key, $allowed, true ) ) {
			$remove[] = $key;
		}
	}

	if ( ! empty( $remove ) ) {
		$original_url = remove_query_arg( $remove, $original_url );
	}

	return isset( $query_args['token'] ) && hash_equals( $query_args['token'], edd_get_download_token( $original_url ) );
}, 10, 4 );

/**
 * Get the path of the Product Reviews plugin
 *
 * @since 2.9.20
 *
 * @return mixed|string
 */
function edd_reviews_location() {

	_edd_deprecated_function( __FUNCTION__, '2.11.4' );

	$possible_locations = array( 'edd-reviews/edd-reviews.php', 'EDD-Reviews/edd-reviews.php' );
	$reviews_location   = '';

	foreach ( $possible_locations as $location ) {

		if ( 0 !== validate_plugin( $location ) ) {
			continue;
		}
		$reviews_location = $location;
	}

	return $reviews_location;
}

/**
 * Outputs a metabox for the Product Reviews extension to show or activate it.
 *
 * @since 2.8
 * @return void
 */
function edd_render_review_status_metabox() {

	_edd_deprecated_function( __FUNCTION__, '2.11.4' );

	$reviews_location = edd_reviews_location();

	ob_start();

	if ( ! empty( $reviews_location ) ) {
		$review_path  = '';
		$base_url     = wp_nonce_url( admin_url( 'plugins.php' ), 'activate-plugin_' . sanitize_key( $reviews_location ) );
		$args         = array(
			'action'        => 'activate',
			'plugin'        => sanitize_text_field( $reviews_location ),
			'plugin_status' => 'all',
		);
		$activate_url = add_query_arg( $args, $base_url );
		?><p style="text-align: center;"><a href="<?php echo esc_url( $activate_url ); ?>" class="button-secondary"><?php _e( 'Activate Reviews', 'easy-digital-downloads' ); ?></a></p><?php

	} else {
		$url = edd_link_helper(
			'https://easydigitaldownloads.com/downloads/product-reviews/',
			array(
				'utm_medium'  => 'edit-download',
				'utm_content' => 'product-reviews',
			)
		);
		?>
		<p>
			<?php
			// Translators: The %s represents the link to the Product Reviews extension.
			echo wp_kses_post( sprintf( __( 'Would you like to enable reviews for this product? Check out our <a target="_blank" href="%s">Product Reviews</a> extension.', 'easy-digital-downloads' ), $url ) );
			?>
		</p>
		<?php
	}

	$rendered = ob_get_contents();
	ob_end_clean();

	echo wp_kses_post( $rendered );
}

/**
 *
 * Increases the sale count of a download.
 *
 * @since 1.0
 *
 * @param int $download_id Download ID.
 * @param int $quantity    Quantity to increase purchase count by.
 *
 * @return bool|int Updated sale count, false if download does not exist.
 */
function edd_increase_purchase_count( $download_id = 0, $quantity = 1 ) {

	_edd_deprecated_function( __FUNCTION__, '3.0' );

	// Bail if no download ID was passed.
	if ( empty( $download_id ) ) {
		return false;
	}

	$download = edd_get_download( $download_id );
	if ( ! $download ) {
		return false;
	}

	return $download->get_sales();
}

/**
 * Decreases the sale count of a download. Primarily for when a purchase is
 * refunded.
 *
 * @since 1.0.8.1
 *
 * @param int $download_id Download ID.
 * @param int $quantity    Optional. Quantity to decrease by. Default 1.
 *
 * @return bool|int Updated sale count, false if download does not exist.
 */
function edd_decrease_purchase_count( $download_id = 0, $quantity = 1 ) {

	_edd_deprecated_function( __FUNCTION__, '3.0' );

	// Bail if no download ID was passed.
	if ( empty( $download_id ) ) {
		return false;
	}

	$download = edd_get_download( $download_id );
	if ( ! $download ) {
		return false;
	}

	return $download->get_sales();
}

/**
 * Increases the total earnings of a download.
 *
 * @since 1.0
 *
 * @param int   $download_id Download ID.
 * @param float $amount      Earnings to increase by.
 *
 * @return float|false Updated earnings, false if invalid data passed.
 */
function edd_increase_earnings( $download_id = 0, $amount = 0.00 ) {

	_edd_deprecated_function( __FUNCTION__, '3.0' );

	// Bail if no download ID or amount was passed.
	if ( empty( $download_id ) || empty( $amount ) ) {
		return false;
	}

	$download = edd_get_download( $download_id );
	if ( ! $download ) {
		return false;
	}

	return $download->get_earnings();
}

/**
 * Decreases the total earnings of a download. Primarily for when a purchase
 * is refunded.
 *
 * @since 1.0.8.1
 *
 * @param int   $download_id Download ID.
 * @param float $amount      Earnings to decrease by.
 *
 * @return float|false Updated earnings, false if invalid data passed.
 */
function edd_decrease_earnings( $download_id = 0, $amount = 0.00 ) {

	_edd_deprecated_function( __FUNCTION__, '3.0' );

	// Bail if no download ID or amount was passed.
	if ( empty( $download_id ) || empty( $amount ) ) {
		return false;
	}

	$download = edd_get_download( $download_id );
	if ( ! $download ) {
		return false;
	}

	return $download->get_earnings();
}

/**
 * Check to see if we should be displaying promotional content
 *
 * In various parts of the plugin, we may choose to promote something like a sale for a limited time only. This
 * function should be used to set the conditions under which the promotions will display.
 *
 * @since 2.9.20
 * @deprecated 3.1
 *
 * @return bool
 */
function edd_is_promo_active() {
	_edd_deprecated_function( __FUNCTION__, '3.1' );

	return false;
}

/**
 * Outputs a metabox for promotional content.
 *
 * @since 2.9.20
 * @deprecated 3.1
 *
 * @return void
 */
function edd_render_promo_metabox() {
	_edd_deprecated_function( __FUNCTION__, '3.1' );
	return;
}

/**
 * Plugin row meta links
 *
 * @since 1.8
 * @deprecated 3.1
 * @param  array  $links already defined meta links.
 * @param  string $file  plugin file path and name being processed.
 * @return array  $input
 */
function edd_plugin_row_meta( $links = array(), $file = '' ) {
	_edd_deprecated_function( __FUNCTION__, '3.1' );
	return $links;
}

/**
 * Listens to the updated_postmeta hook for our backwards compatible payment_meta updates, and runs through them
 *
 * Previously hooked into: updated_postmeta
 *
 * @since  2.3
 * @deprecated 3.1.0.3
 * @param  int $meta_id    The Meta ID that was updated
 * @param  int $object_id  The Object ID that was updated (post ID)
 * @param  string $meta_key   The Meta key that was updated
 * @param  string|int|float $meta_value The Value being updated
 * @return bool|int             If successful the number of rows updated, if it fails, false
 */
function edd_update_payment_backwards_compat( $meta_id, $object_id, $meta_key, $meta_value ) {

	_edd_deprecated_function( __FUNCTION__, '3.1.0.3' );

	$meta_keys = array( '_edd_payment_meta', '_edd_payment_tax' );

	if ( ! in_array( $meta_key, $meta_keys ) ) {
		return;
	}

	global $wpdb;
	switch( $meta_key ) {

		case '_edd_payment_meta':
			$meta_value   = maybe_unserialize( $meta_value );

			if( ! isset( $meta_value['tax'] ) ){
				return;
			}

			$tax_value    = $meta_value['tax'];

			$data         = array( 'meta_value' => $tax_value );
			$where        = array( 'post_id'  => $object_id, 'meta_key' => '_edd_payment_tax' );
			$data_format  = array( '%f' );
			$where_format = array( '%d', '%s' );
			break;

		case '_edd_payment_tax':
			$tax_value    = ! empty( $meta_value ) ? $meta_value : 0;
			$current_meta = edd_get_payment_meta( $object_id, '_edd_payment_meta', true );

			$current_meta['tax'] = $tax_value;
			$new_meta            = maybe_serialize( $current_meta );

			$data         = array( 'meta_value' => $new_meta );
			$where        = array( 'post_id' => $object_id, 'meta_key' => '_edd_payment_meta' );
			$data_format  = array( '%s' );
			$where_format = array( '%d', '%s' );

			break;

	}

	$updated = $wpdb->update( $wpdb->postmeta, $data, $where, $data_format, $where_format );

	if ( ! empty( $updated ) ) {
		// Since we did a direct DB query, clear the postmeta cache.
		wp_cache_delete( $object_id, 'post_meta' );
	}

	return $updated;

}

/**
 * Deletes edd_stats_ transients that have expired to prevent database clogs
 *
 * Previously hooked into: edd_daily_scheduled_events
 *
 * @since 2.6.7
 * @deprecated 3.1.0.3
 * @return void
*/
function edd_cleanup_stats_transients() {

	_edd_deprecated_function( __FUNCTION__, '3.1.0.3' );

	global $wpdb;

	if ( defined( 'WP_SETUP_CONFIG' ) ) {
		return;
	}

	if ( defined( 'WP_INSTALLING' ) ) {
		return;
	}

	$now        = current_time( 'timestamp' );
	$transients = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE '%\_transient_timeout\_edd\_stats\_%' AND option_value+0 < $now LIMIT 0, 200;" );
	$to_delete  = array();

	if( ! empty( $transients ) ) {

		foreach( $transients as $transient ) {

			$to_delete[] = $transient->option_name;
			$to_delete[] = str_replace( '_timeout', '', $transient->option_name );

		}

	}

	if ( ! empty( $to_delete ) ) {

		$option_names = implode( "','", $to_delete );
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name IN ('$option_names')"  );

	}

}

/**
 * Updates all old payments, prior to 1.2, with new
 * meta for the total purchase amount
 *
 * This is so that payments can be queried by their totals
 *
 * Prevsiouly hooked into: edd_upgrade_payments
 *
 * @since 1.2
 * @deprecated 3.1.0.3
 * @param array $data Arguments passed
 * @return void
*/
function edd_update_old_payments_with_totals( $data ) {
	_edd_deprecated_function( __FUNCTION__, '3.1.0.3' );

	if ( ! wp_verify_nonce( $data['_wpnonce'], 'edd_upgrade_payments_nonce' ) ) {
		return;
	}

	if ( get_option( 'edd_payment_totals_upgraded' ) ) {
		return;
	}

	$payments = edd_get_payments( array(
		'offset' => 0,
		'number' => 9999999,
		'mode'   => 'all',
	) );

	if ( $payments ) {
		foreach ( $payments as $payment ) {

			$payment = new EDD_Payment( $payment->ID );
			$meta    = $payment->get_meta();

			$payment->total = $meta['amount'];
			$payment->save();
		}
	}

	add_option( 'edd_payment_totals_upgraded', 1 );
}

/**
 * Flushes the current user's purchase history transient when a payment status
 * is updated
 *
 * Previously hooked into: edd_update_payment_status
 *
 * @since 1.2.2
 * @deprecated 3.1.0.3
 * @param int $payment_id the ID number of the payment
 * @param string $new_status the status of the payment, probably "publish"
 * @param string $old_status the status of the payment prior to being marked as "complete", probably "pending"
 */
function edd_clear_user_history_cache( $payment_id, $new_status, $old_status ) {

	_edd_deprecated_function( __FUNCTION__, '3.1.0.3' );

	$payment = new EDD_Payment( $payment_id );

	if( ! empty( $payment->user_id ) ) {
		delete_transient( 'edd_user_' . $payment->user_id . '_purchases' );
	}
}

/**
 * Filters the WHERE SQL query for the edd_download_search.
 * This searches the download titles only, not the excerpt/content.
 *
 * @since 3.1.0.2
 * @deprecated 3.1.0.5
 * @param string $where
 * @param WP_Query $wp_query
 * @return string
 */
function edd_ajax_filter_download_where( $where, $wp_query ) {

	_edd_deprecated_function( __FUNCTION__, '3.1.0.5' );

	$search  = new EDD\Downloads\Search();

	return $search->filter_where( $where, $wp_query );
}

/**
 * Gets the next available order number.
 *
 * This is used when inserting a new order.
 *
 * @deprecated 3.1.2
 * @since 2.0
 * @return false|int $number The next available order number, unformatted.
 */
function edd_get_next_payment_number() {

	_edd_deprecated_function( __FUNCTION__, '3.1.2', 'EDD\Orders\Number\get_next_payment_number' );
	$order_number = new EDD\Orders\Number();

	return $order_number->get_next_payment_number();
}

/**
 * Schedules the one time event via WP_Cron to fire after purchase actions.
 *
 * Is run on the edd_complete_purchase action.
 *
 * @since 2.8
 * @since 3.2.0 - Updated to call the DeferredActions class. Throwing an edd_debug_log entry instead of full deprecation.
 *
 * @deprecated 3.2.0 Moved to EDD\Orders\DeferredActions::schedule_deferred_actions(). This should not be used by anyone.
 *
 * @param $payment_id
 */
function edd_schedule_after_payment_action( $payment_id ) {
	edd_debug_log( 'Calling edd_scheduled_after_payment_action directly has been deprecated in EDD 3.2. Please use the EDD\Orders\DeferredActions class instead.' );

	/**
	 * @todo offiically throw a deprecated function notice.
	 */

	EDD\Orders\DeferredActions::schedule_deferred_actions( $payment_id );
}

/**
 * Executes the one time event used for after purchase actions.
 *
 * This function should have never been called from anywhere but outselves and on our cron.
 *
 * @since 2.8
 * @since 3.1.0.4 This also verifies that all order items have the synced status as the order.
 *
 * @deprecated 3.2.0 - Moved to \EDD\Orders\DeferredActions\run_deferred_actions(). This should not be used by anyone.
 *
 * @param $payment_id
 * @param $force
 */
function edd_process_after_payment_actions( $payment_id = 0, $force = false ) {
	_edd_deprecated_function( __FUNCTION__, '3.2.0', '\EDD\Orders\DeferredActions\run_deferred_actions' );
}

/*
 * Registers custom post statuses which are used by the Payments and Discount
 * Codes.
 *
 * @since 1.0.9.1
 * @deprecated 3.2.0
 */
function edd_register_post_type_statuses() {
	_edd_deprecated_function( __FUNCTION__, '3.2.0' );
}

/**
 * Triggers Purchase Receipt to be sent after the payment status is updated.
 *
 * This is a deprecated function but still exists, so that we can detect if a plugin has removed the action.
 *
 * @since 1.0.8.4
 * @since 2.8 - Add parameters for EDD_Payment and EDD_Customer object.
 *
 * @deprecated 3.2.0 - Use EDD\Emails\Types\OrderReceipt instead.
 *
 * @param int          $payment_id Payment ID.
 * @param EDD_Payment  $payment    Payment object for payment ID.
 * @param EDD_Customer $customer   Customer object for associated payment.
 * @return void
 */
function edd_trigger_purchase_receipt( $payment_id = 0, $payment = null, $customer = null ) {
	_edd_deprecated_function( __FUNCTION__, '3.2.0', 'EDD\Emails\Types\OrderReceipt' );

	/**
	 * In EDD 3.2.0 we will be using the EDD\Emails\Triggers\send_purchase_receipt() function to send the email.
	 *
	 * Previously if you wanted to disable this email from sending you would just unhook this action. In order to still support this,
	 * we'll set a meta here, and check for it when it comes time to send the email.
	 *
	 * Developers: If you want to disable sending the purchase receipt email you should use the filter `edd_disable_order_receipt` filter.
	 */
	edd_add_order_meta( $payment_id, '_edd_should_send_order_receipt', '1' );
	edd_debug_log( 'Adding meta for sending the order_receipt for order #' . $payment_id );

	// Trigger this to setup the edd_admin_sale_notice hook.
	do_action( 'edd_admin_sale_notice', $payment_id, array() );
}
add_action( 'edd_complete_purchase', 'edd_trigger_purchase_receipt', 999, 3 );

/**
 * Email the download link(s) and payment confirmation to the buyer in a
 * customizable Purchase Receipt
 *
 * @since 1.0
 * @since 2.8 - Add parameters for EDD_Payment and EDD_Customer object.
 * @since 3.2.0 - Removed most logic and using the EDD\Emails\Types\OrderReceipt class to send the email.
 *
 * @deprecated 3.2.0 Use EDD\Emails\Types\OrderReceipt instead.
 *
 * @param int          $payment_id   Payment ID
 * @param bool         $admin_notice Whether to send the admin email notification or not (default: true)
 * @param EDD_Payment  $payment      Payment object for payment ID.
 * @param EDD_Customer $customer     Customer object for associated payment.
 * @return bool Whether the email was sent successfully.
 */
function edd_email_purchase_receipt( $payment_id, $admin_notice = true, $to_email = '', $payment = null, $customer = null ) {
	_edd_deprecated_function( __FUNCTION__, '3.2.0', 'EDD\Emails\Types\OrderReceipt' );

	if ( ! empty( $payment_id ) ) {
		$order = edd_get_order( $payment_id );
	}

	$order_receipt          = EDD\Emails\Registry::get( 'order_receipt', array( $order ) );
	$order_receipt->send_to = $to_email;

	$sent = $order_receipt->send();

	return $sent;
}

/**
 * Resend the Email Purchase Receipt. (This can be done from the Payment History page)
 *
 * @since 1.0
 *
 * @deprecated 3.2.0 - Handeled with EDD\Emails\Triggers\resend_purchase_receipt instead.
 *
 * @param array $data Payment Data
 * @return void
 */
function edd_resend_purchase_receipt( $data ) {
	_edd_deprecated_function( __FUNCTION__, '3.2.0', 'EDD\Emails\Triggers\resend_purchase_receipt' );

	/**
	 * In EDD 3.2.0 we will be using the EDD\Emails\Triggers\send_purchase_receipt() function to send the email.
	 *
	 * Previously if you wanted to disable this email from sending you would just unhook this action. In order to still support this,
	 * we'll set a meta here, and check for it when it comes time to send the email.
	 *
	 * Developers: If you want to disable sending the purchase receipt email you should use the filter `edd_disable_order_receipt` filter.
	 */
	edd_add_order_meta( $data['purchase_id'], '_edd_should_send_order_receipt', '1' );
}
add_action( 'edd_email_links', 'edd_resend_purchase_receipt' );

/**
 * Trigger the sending of a Test Email
 *
 * @since 1.5
 *
 * @deprecated 3.2.0 - Handeled with EDD\Emails\Triggers\send_test_email instead.
 *
 * @param array $data Parameters sent from Settings page
 * @return void
 */
function edd_send_test_email( $data ) {
	_edd_deprecated_function( __FUNCTION__, '3.2.0', 'EDD\Emails\Triggers\send_test_email' );
}

/**
 * Email the download link(s) and payment confirmation to the admin accounts for testing.
 *
 * @since 1.5
 *
 * @since 3.2.0 - Removed all logic as we are now using the EDD\Emails\Triggers and EDD\Emails\Types\OrderReceipt class to send the email.
 *
 * @return void
 */
function edd_email_test_purchase_receipt() {
	_edd_deprecated_function( __FUNCTION__, '3.2.0', 'EDD\Emails\Triggers' );
}

/**
 * This is a deprecated function now, but still exists so we can know if users are removeing it's action,
 * to know if we are supposed to send the admin notices or not.
 *
 * @since 1.4.2
 * @since 3.2.0 - Removed all logic as we are now using the EDD\Emails\Types\AdminOrderNotice class to send the email.
 *
 * @param int $payment_id Payment ID (default: 0)
 * @param array $payment_data Payment Meta and Data
 * @return void
 */
function edd_admin_email_notice( $payment_id = 0, $payment_data = array() ) {
	_edd_deprecated_function( __FUNCTION__, '3.2.0', 'EDD\Emails\Types\AdminOrderNotice' );

	/**
	 * In EDD 3.2.0 we will be using the EDD\Emails\Triggers\edd_email_sent_order_receipt() function to send the email.
	 *
	 * Previously if you wanted to disable this email from sending you would just unhook this action. In order to still support this,
	 * we'll set a meta here, and check for it when it comes time to send the email.
	 *
	 * Developers: If you want to disable sending the admin order notice email you should use the filter `edd_disable_admin_order_notice` filter.
	 */
	edd_add_order_meta( $payment_id, '_edd_should_send_admin_order_notice', '1' );
	edd_debug_log( 'Adding meta for sending the admin_order_notice for order #' . $payment_id );
}
add_action( 'edd_admin_sale_notice', 'edd_admin_email_notice', 10, 2 );

/**
 * Displays the email preview
 *
 * @since 2.1
 *
 * @deprecated 3.2.0 This is now handeled by EDD\Emails\Triggers\preview_email()
 *
 * @return void
 */
function edd_display_email_template_preview() {
	_edd_deprecated_function( __FUNCTION__, '3.2.0', 'EDD\Emails\Triggers\preview_email()' );
}

/**
 * Get sale notification email text
 *
 * Returns the stored email text if available, the standard email text if not
 *
 * @since 1.7
 * @since 3.2.0 - Uses the EDD\Emails\Types\AdminOrderNotice class.
 *
 * @deprecated 3.2.0
 *
 * @return string $message
 */
function edd_get_default_sale_notification_email() {
	$admin_order_notice = EDD\Emails\Registry::get( 'admin_order_notice', array( false ) );

	return $admin_order_notice->get_raw_body_content();
}

/**
 * Add rating links to the admin dashboard
 *
 * @since       1.8.5
 * @deprecated  3.2.4
 * @global      string $typenow
 * @param       string $footer_text The existing footer text.
 * @return      string
 */
function edd_admin_rate_us( $footer_text = '' ) {
	_edd_deprecated_function( __FUNCTION__, '3.2.4' );

	return $footer_text;
}

/**
 * Email Template Body
 *
 * @since 1.0.8.2
 *
 * @deprecated 3.2.0
 *
 * @param int $payment_id Payment ID
 * @param array $payment_data Payment Data
 * @return string $email_body Body of the email
 */
function edd_get_email_body_content( $payment_id = 0, $payment_data = array() ) {
	_deprecated_function( __FUNCTION__, '3.2.0', 'EDD\Emails\Types\OrderReceipt' );

	$order         = edd_get_order( $payment_id );
	$order_receipt = EDD\Emails\Registry::get( 'order_receipt', array( $order ) );

	return $order_receipt->get_raw_body_content();
}

/**
 * Sale Notification Template Body
 *
 * @since 1.7
 *
 * @deprecated 3.2.0
 *
 * @param int $payment_id Payment ID
 * @param array $payment_data Payment Data
 * @return string $email_body Body of the email
 */
function edd_get_sale_notification_body_content( $payment_id = 0, $payment_data = array() ) {
	_edd_deprecated_function( __FUNCTION__, '3.2.0', 'EDD\Emails\Types\AdminOrderNotice' );

	$order              = edd_get_order( $payment_id );
	$admin_order_notice = EDD\Emails\Registry::get( 'admin_order_notice', array( $order ) );

	return $admin_order_notice->get_raw_body_content();
}
