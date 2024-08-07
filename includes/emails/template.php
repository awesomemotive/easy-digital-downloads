<?php
/**
 * Email Template
 *
 * @package     EDD
 * @subpackage  Emails
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Gets all the email templates that have been registerd. The list is extendable
 * and more templates can be added.
 *
 * As of 2.0, this is simply a wrapper to EDD_Email_Templates->get_templates()
 *
 * @since 1.0.8.2
 * @return array $templates All the registered email templates
 */
function edd_get_email_templates() {
	$templates = new EDD_Emails();
	return $templates->get_templates();
}

/**
 * Email Template Tags
 *
 * @since 1.0
 *
 * @param string $message Message with the template tags
 * @param array  $payment_data Payment Data
 * @param int    $payment_id Payment ID
 * @param bool   $admin_notice Whether or not this is a notification email
 *
 * @return string $message Fully formatted message
 */
function edd_email_template_tags( $message, $payment_data, $payment_id, $admin_notice = false ) {
	return edd_do_email_tags( $message, $payment_id );
}

/**
 * Email Preview Template Tags
 *
 * @todo Currently this has a hardcoded set of tags for replacement, and doesn't include
 * all tags registered, when we udpate the tag registration we should update to allow adding a 'sample' data
 * for each tag.
 *
 * @since 1.0
 * @since 3.2.0 - Added $wpautop parameter.
 * @param string $message Email message with template tags
 * @param bool   $disable_wpautop If we should fully disable wpautop for this content.
 *
 * @return string $message Fully formatted message
 */
function edd_email_preview_template_tags( $message, $disable_wpautop = false, $order_id = 0 ) {

	$notes   = __( 'These are some sample notes added to a product.', 'easy-digital-downloads' );
	$message = str_replace( '{product_notes}', $notes, $message );

	if ( empty( $order_id ) ) {
		$order_numbers = new EDD\Orders\Number();
		$order_number  = $order_numbers->format( wp_rand( 100, 987 ) );
		$price         = edd_currency_filter( edd_format_amount( 10.50 ) );
		$gateway       = edd_get_gateway_admin_label( edd_get_default_gateway() );
		$receipt_id    = strtolower( md5( uniqid() ) );
		$tax           = edd_currency_filter( edd_format_amount( 1.00 ) );
		$sub_total     = edd_currency_filter( edd_format_amount( 9.50 ) );
		$message       = str_replace( '{date}', edd_date_i18n( current_time( 'timestamp' ) ), $message );
		$message       = str_replace( '{subtotal}', $sub_total, $message );
		$message       = str_replace( '{tax}', $tax, $message );
		$message       = str_replace( '{price}', $price, $message );
		$message       = str_replace( '{receipt_id}', $receipt_id, $message );
		$message       = str_replace( '{payment_method}', $gateway, $message );
		$message       = str_replace( '{sitename}', get_bloginfo( 'name' ), $message );
		$message       = str_replace( '{payment_id}', $order_number, $message );
		$message       = str_replace( '{receipt_link}', edd_email_tag_receipt_link( 0 ), $message );
		$message       = str_replace( '{receipt}', edd_email_tag_receipt( 0 ), $message );
	}

	$user    = wp_get_current_user();
	$message = str_replace( '{name}', $user->display_name, $message );
	$message = str_replace( '{fullname}', $user->display_name, $message );
	$message = str_replace( '{username}', $user->user_login, $message );

	$message = apply_filters( 'edd_email_preview_template_tags', $message );

	$wpautop = $disable_wpautop ? false : apply_filters( 'edd_email_preview_template_wpautop', true );

	return $wpautop ? wpautop( $message ) : $message;
}

/**
 * Render Receipt in the Browser
 *
 * A link is added to the Purchase Receipt to view the email in the browser and
 * this function renders the Purchase Receipt in the browser. It overrides the
 * Purchase Receipt template and provides its only styling.
 *
 * @since 1.5
 * @author Sunny Ratilal
 * @param array $data The request data.
 */
function edd_render_receipt_in_browser( $data ) {
	if ( ! isset( $data['payment_key'] ) ) {
		wp_die( __( 'Missing purchase key.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ) );
	}

	if ( ! empty( $_POST['edd_action'] ) && ! empty( $_POST['edd_user_login'] ) && ! empty( $_POST['edd_login_nonce'] ) ) {
		return;
	}

	$key = urlencode( $data['payment_key'] );

	ob_start();

	// Disallows caching of the page
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
	header( 'Cache-Control: no-store, no-cache, must-revalidate' ); // HTTP/1.1
	header( 'Cache-Control: post-check=0, pre-check=0', false );
	header( 'Pragma: no-cache' ); // HTTP/1.0
	header( 'Expires: Sat, 23 Oct 1977 05:00:00 PST' ); // Date in the past
	?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php esc_html_e( 'Receipt', 'easy-digital-downloads' ); ?></title>
		<meta charset="utf-8" />
		<meta name="robots" content="noindex, nofollow" />
		<?php wp_head(); ?>
		<style>
			body.edd_receipt_page {
				margin: 12px auto;
				align-items: center;
				border: 1px solid #cfcfcf;
				max-width: fit-content;
				padding: 12px 24px;
				border-radius: 8px;
			}

			.edd_receipt_page #edd_login_form fieldset {
				border: none;
				display: grid;
			}

			.edd_receipt_page #edd_login_form label,
			.edd_receipt_page #edd_login_form input[type=text],
			.edd_receipt_page #edd_login_form input[type=password]{
				display: block;
				width: 100%;
			}

			.edd_receipt_page th {
				text-align: left;
			}
		</style>
	</head>
<body class="<?php echo esc_attr( apply_filters( 'edd_receipt_page_body_class', 'edd_receipt_page' ) ); ?>">
	<div id="edd_receipt_wrapper">
		<?php do_action( 'edd_render_receipt_in_browser_before' ); ?>
		<?php echo do_shortcode( '[edd_receipt payment_key=' . $key . ']' ); ?>
		<?php do_action( 'edd_render_receipt_in_browser_after' ); ?>
	</div>
	<?php wp_footer(); ?>
</body>
</html>
	<?php
	echo ob_get_clean();
	die();
}
add_action( 'edd_view_receipt', 'edd_render_receipt_in_browser' );
