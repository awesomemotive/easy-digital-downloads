<?php
/**
 * Email Template
 *
 * @package     EDD
 * @subpackage  Emails
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

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
 * @global $edd_options Array of all the EDD Options
 * @param string $message Email message with template tags
 * @return string $message Fully formatted message
 */
function edd_email_preview_template_tags( $message ) {
	global $edd_options;

	$download_list = '<ul>';
	$download_list .= '<li>' . __( 'Sample Product Title', 'edd' ) . '<br />';
	$download_list .= '<div>';
	$download_list .= '<a href="#">' . __( 'Sample Download File Name', 'edd' ) . '</a> - <small>' . __( 'Optional notes about this download.', 'edd' ) . '</small>';
	$download_list .= '</div>';
	$download_list .= '</li>';
	$download_list .= '</ul>';

	$file_urls = esc_html( trailingslashit( get_site_url() ) . 'test.zip?test=key&key=123' );

	$price = edd_currency_filter( edd_format_amount( 10.50 ) );

	$gateway = 'PayPal';

	$receipt_id = strtolower( md5( uniqid() ) );

	$notes = __( 'These are some sample notes added to a product.', 'edd' );

	$tax = edd_currency_filter( edd_format_amount( 1.00 ) );

	$sub_total = edd_currency_filter( edd_format_amount( 9.50 ) );

	$payment_id = rand(1, 100);

	$user = wp_get_current_user();

	$message = str_replace( '{download_list}', $download_list, $message );
	$message = str_replace( '{file_urls}', $file_urls, $message );
	$message = str_replace( '{name}', $user->display_name, $message );
	$message = str_replace( '{fullname}', $user->display_name, $message );
 	$message = str_replace( '{username}', $user->user_login, $message );
	$message = str_replace( '{date}', date( get_option( 'date_format' ), current_time( 'timestamp' ) ), $message );
	$message = str_replace( '{subtotal}', $sub_total, $message );
	$message = str_replace( '{tax}', $tax, $message );
	$message = str_replace( '{price}', $price, $message );
	$message = str_replace( '{receipt_id}', $receipt_id, $message );
	$message = str_replace( '{payment_method}', $gateway, $message );
	$message = str_replace( '{sitename}', get_bloginfo( 'name' ), $message );
	$message = str_replace( '{product_notes}', $notes, $message );
	$message = str_replace( '{payment_id}', $payment_id, $message );
	$message = str_replace( '{receipt_link}', sprintf( __( '%1$sView it in your browser.%2$s', 'edd' ), '<a href="' . add_query_arg( array ( 'payment_key' => $receipt_id, 'edd_action' => 'view_receipt' ), home_url() ) . '">', '</a>' ), $message );

	return wpautop( apply_filters( 'edd_email_preview_template_tags', $message ) );
}

/**
 * Email Template Preview
 *
 * @access private
 * @global $edd_options Array of all the EDD Options
 * @since 1.0.8.2
 */
function edd_email_template_preview() {
	global $edd_options;

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	ob_start();
	?>
	<a href="<?php echo esc_url( add_query_arg( array( 'edd_action' => 'preview_email' ), home_url() ) ); ?>" class="button-secondary" target="_blank" title="<?php _e( 'Purchase Receipt Preview', 'edd' ); ?> "><?php _e( 'Preview Purchase Receipt', 'edd' ); ?></a>
	<a href="<?php echo wp_nonce_url( add_query_arg( array( 'edd_action' => 'send_test_email' ) ), 'edd-test-email' ); ?>" title="<?php _e( 'This will send a demo purchase receipt to the emails listed below.', 'edd' ); ?>" class="button-secondary"><?php _e( 'Send Test Email', 'edd' ); ?></a>
	<?php
	echo ob_get_clean();
}
add_action( 'edd_email_settings', 'edd_email_template_preview' );

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


	EDD()->emails->heading = __( 'Purchase Receipt', 'edd' );

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
	global $edd_options;

	$default_email_body = __( "Dear", "edd" ) . " {name},\n\n";
	$default_email_body .= __( "Thank you for your purchase. Please click on the link(s) below to download your files.", "edd" ) . "\n\n";
	$default_email_body .= "{download_list}\n\n";
	$default_email_body .= "{sitename}";

	$email = isset( $edd_options['purchase_receipt'] ) ? stripslashes( $edd_options['purchase_receipt'] ) : $default_email_body;

	$email_body = wpautop( $email );

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
	global $edd_options;

	$user_info = maybe_unserialize( $payment_data['user_info'] );
	$email = edd_get_payment_user_email( $payment_id );

	if( isset( $user_info['id'] ) && $user_info['id'] > 0 ) {
		$user_data = get_userdata( $user_info['id'] );
		$name = $user_data->display_name;
	} elseif( isset( $user_info['first_name'] ) && isset( $user_info['last_name'] ) ) {
		$name = $user_info['first_name'] . ' ' . $user_info['last_name'];
	} else {
		$name = $email;
	}

	$download_list = '';
	$downloads = maybe_unserialize( $payment_data['downloads'] );

	if( is_array( $downloads ) ) {
		foreach( $downloads as $download ) {
			$id = isset( $payment_data['cart_details'] ) ? $download['id'] : $download;
			$title = get_the_title( $id );
			if( isset( $download['options'] ) ) {
				if( isset( $download['options']['price_id'] ) ) {
					$title .= ' - ' . edd_get_price_option_name( $id, $download['options']['price_id'], $payment_id );
				}
			}
			$download_list .= html_entity_decode( $title, ENT_COMPAT, 'UTF-8' ) . "\n";
		}
	}

	$gateway = edd_get_gateway_admin_label( get_post_meta( $payment_id, '_edd_payment_gateway', true ) );

	$default_email_body = __( 'Hello', 'edd' ) . "\n\n" . sprintf( __( 'A %s purchase has been made', 'edd' ), edd_get_label_plural() ) . ".\n\n";
	$default_email_body .= sprintf( __( '%s sold:', 'edd' ), edd_get_label_plural() ) . "\n\n";
	$default_email_body .= $download_list . "\n\n";
	$default_email_body .= __( 'Purchased by: ', 'edd' ) . " " . html_entity_decode( $name, ENT_COMPAT, 'UTF-8' ) . "\n";
	$default_email_body .= __( 'Amount: ', 'edd' ) . " " . html_entity_decode( edd_currency_filter( edd_format_amount( edd_get_payment_amount( $payment_id ) ) ), ENT_COMPAT, 'UTF-8' ) . "\n";
	$default_email_body .= __( 'Payment Method: ', 'edd' ) . " " . $gateway . "\n\n";
	$default_email_body .= __( 'Thank you', 'edd' );

	$email = isset( $edd_options['sale_notification'] ) ? stripslashes( $edd_options['sale_notification'] ) : $default_email_body;

	//$email_body = edd_email_template_tags( $email, $payment_data, $payment_id, true );
	$email_body = edd_do_email_tags( $email, $payment_id );

	return apply_filters( 'edd_sale_notification', wpautop( $email_body ), $payment_id, $payment_data );
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
 */
function edd_render_receipt_in_browser() {
	if ( ! isset( $_GET['payment_key'] ) )
		wp_die( __( 'Missing purchase key.', 'edd' ), __( 'Error', 'edd' ) );

	$key = urlencode( $_GET['payment_key'] );

	ob_start();
?>
<!DOCTYPE html>
<html lang="en">
	<title><?php _e( 'Receipt', 'edd' ); ?></title>
	<meta charset="utf-8" />
	<?php wp_head(); ?>
</html>
<body class="<?php echo apply_filters('edd_receipt_page_body_class', 'edd_receipt_page' ); ?>">
	<div id="edd_receipt_wrapper">
		<?php do_action( 'edd_render_receipt_in_browser_before' ); ?>
		<?php echo do_shortcode('[edd_receipt payment_key='. $key .']'); ?>
		<?php do_action( 'edd_render_receipt_in_browser_after' ); ?>
	</div>
<?php wp_footer(); ?>
</body>
<?php
	echo ob_get_clean();
	die();
}
add_action( 'edd_view_receipt', 'edd_render_receipt_in_browser' );
