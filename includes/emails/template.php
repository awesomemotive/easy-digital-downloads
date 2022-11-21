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
	$templates = new EDD_Emails;
	return $templates->get_templates();
}

/**
 * Email Template Tags
 *
 * @since 1.0
 *
 * @param string $message Message with the template tags
 * @param array $payment_data Payment Data
 * @param int $payment_id Payment ID
 * @param bool $admin_notice Whether or not this is a notification email
 *
 * @return string $message Fully formatted message
 */
function edd_email_template_tags( $message, $payment_data, $payment_id, $admin_notice = false ) {
	return edd_do_email_tags( $message, $payment_id );
}

/**
 * Email Preview Template Tags
 *
 * @since 1.0
 * @param string $message Email message with template tags
 * @return string $message Fully formatted message
 */
function edd_email_preview_template_tags( $message ) {
	$download_list = '<ul>';
	$download_list .= '<li>' . __( 'Sample Product Title', 'easy-digital-downloads' ) . '<br />';
	$download_list .= '<div>';
	$download_list .= '<a href="#">' . __( 'Sample Download File Name', 'easy-digital-downloads' ) . '</a> - <small>' . __( 'Optional notes about this download.', 'easy-digital-downloads' ) . '</small>';
	$download_list .= '</div>';
	$download_list .= '</li>';
	$download_list .= '</ul>';

	$file_urls = esc_html( trailingslashit( get_site_url() ) . 'test.zip?test=key&key=123' );

	$price = edd_currency_filter( edd_format_amount( 10.50 ) );

	$gateway = edd_get_gateway_admin_label( edd_get_default_gateway() );

	$receipt_id = strtolower( md5( uniqid() ) );

	$notes = __( 'These are some sample notes added to a product.', 'easy-digital-downloads' );

	$tax = edd_currency_filter( edd_format_amount( 1.00 ) );

	$sub_total = edd_currency_filter( edd_format_amount( 9.50 ) );

	$payment_id = rand(1, 100);

	$user = wp_get_current_user();

	$message = str_replace( '{download_list}', $download_list, $message );
	$message = str_replace( '{file_urls}', $file_urls, $message );
	$message = str_replace( '{name}', $user->display_name, $message );
	$message = str_replace( '{fullname}', $user->display_name, $message );
 	$message = str_replace( '{username}', $user->user_login, $message );
	$message = str_replace( '{date}', edd_date_i18n( current_time( 'timestamp' ) ), $message );
	$message = str_replace( '{subtotal}', $sub_total, $message );
	$message = str_replace( '{tax}', $tax, $message );
	$message = str_replace( '{price}', $price, $message );
	$message = str_replace( '{receipt_id}', $receipt_id, $message );
	$message = str_replace( '{payment_method}', $gateway, $message );
	$message = str_replace( '{sitename}', get_bloginfo( 'name' ), $message );
	$message = str_replace( '{product_notes}', $notes, $message );
	$message = str_replace( '{payment_id}', $payment_id, $message );
	$message = str_replace( '{receipt_link}', edd_email_tag_receipt_link( $payment_id ), $message );
	$message = str_replace( '{receipt}', edd_email_tag_receipt( $payment_id ), $message );

	$message = apply_filters( 'edd_email_preview_template_tags', $message );

	return apply_filters( 'edd_email_template_wpautop', true ) ? wpautop( $message ) : $message;
}

/**
 * Email Template Preview
 *
 * @access private
 * @since 1.0.8.2
 */
function edd_email_template_preview() {
	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	ob_start();
	?>
	<a href="<?php echo esc_url( add_query_arg( array( 'edd_action' => 'preview_email' ), home_url() ) ); ?>" class="button-secondary" target="_blank"><?php _e( 'Preview Purchase Receipt', 'easy-digital-downloads' ); ?></a>
	<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'edd_action' => 'send_test_email' ) ), 'edd-test-email' ) ); ?>" class="button-secondary"><?php _e( 'Send Test Email', 'easy-digital-downloads' ); ?></a>
	<?php
	echo ob_get_clean();
}
add_action( 'edd_purchase_receipt_email_settings', 'edd_email_template_preview' );

/**
 * Displays the email preview
 *
 * @since 2.1
 * @return void
 */
function edd_display_email_template_preview() {

	if( empty( $_GET['edd_action'] ) ) {
		return;
	}

	if( 'preview_email' !== $_GET['edd_action'] ) {
		return;
	}

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}


	EDD()->emails->heading = edd_email_preview_template_tags( edd_get_option( 'purchase_heading', __( 'Purchase Receipt', 'easy-digital-downloads' ) ) );

	echo EDD()->emails->build_email( edd_email_preview_template_tags( edd_get_email_body_content( 0, array() ) ) );

	exit;

}
add_action( 'template_redirect', 'edd_display_email_template_preview' );

/**
 * Email Template Body
 *
 * @since 1.0.8.2
 * @param int $payment_id Payment ID
 * @param array $payment_data Payment Data
 * @return string $email_body Body of the email
 */
function edd_get_email_body_content( $payment_id = 0, $payment_data = array() ) {
	$default_email_body = __( "Dear", "easy-digital-downloads" ) . " {name},\n\n";
	$default_email_body .= __( "Thank you for your purchase. Please click on the link(s) below to download your files.", "easy-digital-downloads" ) . "\n\n";
	$default_email_body .= "{download_list}\n\n";
	$default_email_body .= "{sitename}";

	$email = edd_get_option( 'purchase_receipt', false );
	$email = $email ? stripslashes( $email ) : $default_email_body;

	$email_body = apply_filters( 'edd_email_template_wpautop', true ) ? wpautop( $email ) : $email;

	$email_body = apply_filters( 'edd_purchase_receipt_' . EDD()->emails->get_template(), $email_body, $payment_id, $payment_data );

	return apply_filters( 'edd_purchase_receipt', $email_body, $payment_id, $payment_data );
}

/**
 * Sale Notification Template Body
 *
 * @since 1.7
 * @author Daniel J Griffiths
 * @param int $payment_id Payment ID
 * @param array $payment_data Payment Data
 * @return string $email_body Body of the email
 */
function edd_get_sale_notification_body_content( $payment_id = 0, $payment_data = array() ) {
	$payment = edd_get_payment( $payment_id );
	$order   = edd_get_order( $payment_id );

	$name = $payment->email;
	if ( $payment->user_id > 0 ) {
		$user_data = get_userdata( $payment->user_id );
		if ( ! empty( $user_data->display_name ) ) {
			$name = $user_data->display_name;
		}
	} elseif ( ! empty( $payment->first_name ) && ! empty( $payment->last_name ) ) {
		$name = $payment->first_name . ' ' . $payment->last_name;
	}

	$download_list = '';

	$order_items = $order->get_items();
	if( ! empty( $order_items ) ) {
		foreach( $order_items as $item ) {
			$download_list .= html_entity_decode( $item->product_name, ENT_COMPAT, 'UTF-8' ) . "\n";
		}
	}

	$gateway = edd_get_gateway_checkout_label( $payment->gateway );

	$default_email_body = __( 'Hello', 'easy-digital-downloads' ) . "\n\n" . sprintf( __( 'A %s purchase has been made', 'easy-digital-downloads' ), edd_get_label_plural() ) . ".\n\n";
	$default_email_body .= sprintf( __( '%s sold:', 'easy-digital-downloads' ), edd_get_label_plural() ) . "\n\n";
	$default_email_body .= $download_list . "\n\n";
	$default_email_body .= __( 'Purchased by: ', 'easy-digital-downloads' ) . " " . html_entity_decode( $name, ENT_COMPAT, 'UTF-8' ) . "\n";
	$default_email_body .= __( 'Amount: ', 'easy-digital-downloads' ) . " " . html_entity_decode( edd_currency_filter( edd_format_amount( $payment->total ) ), ENT_COMPAT, 'UTF-8' ) . "\n";
	$default_email_body .= __( 'Payment Method: ', 'easy-digital-downloads' ) . " " . $gateway . "\n\n";
	$default_email_body .= __( 'Thank you', 'easy-digital-downloads' );

	$message = edd_get_option( 'sale_notification', false );
	$message   = $message ? stripslashes( $message ) : $default_email_body;

	//$email_body = edd_email_template_tags( $email, $payment_data, $payment_id, true );
	$email_body = edd_do_email_tags( $message, $payment_id );

	$email_body = apply_filters( 'edd_email_template_wpautop', true ) ? wpautop( $email_body ) : $email_body;

	return apply_filters( 'edd_sale_notification', $email_body, $payment_id, $payment_data );
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
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache"); // HTTP/1.0
	header("Expires: Sat, 23 Oct 1977 05:00:00 PST"); // Date in the past
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
