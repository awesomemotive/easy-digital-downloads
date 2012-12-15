<?php
/**
 * Email Template
 *
 * @package Easy Digital Downloads
 * @subpackage Email Template
 * @copyright Copyright (c) 2012, Pippin Williamson
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.0.8.2
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Get Email Templates
 *
 * @access private
 * @since 1.0.8.2
 * @return array
 */

function edd_get_email_templates() {
	$templates = array(
		'default' => __( 'Default Template', 'edd' ),
		'none' => __( 'No template, plain text only', 'edd' )
	);
	return apply_filters( 'edd_email_templates', $templates );
}


/**
 * Email Template Tags
 *
 * @access private
 * @since 1.0
 * @return string
 */

function edd_email_template_tags( $message, $payment_data, $payment_id ) {

	$user_info = maybe_unserialize( $payment_data['user_info'] );

	if ( isset( $user_info['id'] ) && $user_info['id'] > 0 && isset( $user_info['first_name'] ) ) {

		$user_data = get_userdata( $user_info['id'] );
		$name      = $user_info['first_name'];
		$fullname  = $user_info['first_name'] . ' ' . $user_info['last_name'];
		$username  = $user_data->user_login;

	} elseif ( isset( $user_info['first_name'] ) ) {

		$name      = $user_info['first_name'];
		$fullname  = $user_info['first_name'] . ' ' . $user_info['last_name'];
		$username  = $user_info['first_name'];

	} else {

		$name      = $user_info['email'];
		$username  = $user_info['email'];

	}

	$download_list = '<ul>';
	$downloads     = edd_get_payment_meta_downloads( $payment_id );
	if ( $downloads ) {

		$show_names = apply_filters( 'edd_email_show_names', true );

		foreach ( $downloads as $download ) {

			$id = isset( $payment_data['cart_details'] ) ? $download['id'] : $download;

			if ( $show_names ) {
				$download_list .= '<li>' . get_the_title( $id ) . '<br/>';
				$download_list .= '<ul>';
			}

			$price_id = isset( $download['options']['price_id'] ) ? $download['options']['price_id'] : null;

			$files = edd_get_download_files( $id, $price_id );

			if ( $files ) {
				foreach ( $files as $filekey => $file ) {
					$download_list .= '<li>';
					$file_url = edd_get_download_file_url( $payment_data['key'], $payment_data['email'], $filekey, $id ) ;
					$download_list .= '<a href="' . esc_url( $file_url ) . '">' . $file['name'] . '</a>';

					$download_list .= '</li>';
				}
			}
			if ( $show_names ) {
				$download_list .= '</ul>';
			}

			if ( '' != edd_get_product_notes( $id ) )
				$download_list .= ' &mdash; <small>' . edd_get_product_notes( $id ) . '</small>';

			if ( $show_names ) {
				$download_list .= '</li>';
			}
		}
	}
	$download_list .= '</ul>';

	$subtotal   = isset( $payment_data['subtotal'] ) ? $payment_data['subtotal'] : $payment_data['amount'];
	$subtotal   = edd_currency_filter( edd_format_amount( $subtotal ) );
	$tax        = isset( $payment_data['tax'] ) ? $payment_data['tax'] : 0;
	$tax        = edd_currency_filter( edd_format_amount( $tax ) );
	$price      = edd_currency_filter( edd_format_amount( $payment_data['amount'] ) );
	$gateway    = edd_get_gateway_checkout_label( get_post_meta( $payment_id, '_edd_payment_gateway', true ) );
	$receipt_id = $payment_data['key'];

	$message = str_replace( '{name}',           $name, $message );
	$message = str_replace( '{fullname}',       $fullname, $message );
	$message = str_replace( '{username}',       $username, $message );
	$message = str_replace( '{download_list}',  $download_list, $message );
	$message = str_replace( '{date}',           date_i18n( get_option( 'date_format' ), strtotime( $payment_data['date'] ) ), $message );
	$message = str_replace( '{sitename}',       get_bloginfo( 'name' ), $message );
	$message = str_replace( '{subtotal}',       $subtotal, $message );
	$message = str_replace( '{tax}',            $tax, $message );
	$message = str_replace( '{price}',          $price, $message );
	$message = str_replace( '{payment_method}', $gateway, $message );
	$message = str_replace( '{receipt_id}',     $receipt_id, $message );
	$message = apply_filters( 'edd_email_template_tags', $message, $payment_data, $payment_id );

	return $message;
}


/**
 * Email Preview Template Tags
 *
 * @access private
 * @since 1.0
 * @return string
 */

function edd_email_preview_templage_tags( $message ) {

	$download_list = '<ul>';
	$download_list .= '<li>' . __( 'Sample Product Title', 'edd' ) . '<br />';
	$download_list .= '<ul>';
	$download_list .= '<li>';
	$download_list .= '<a href="#">' . __( 'Sample Download File Name', 'edd' ) . '</a> - <small>' . __( 'Optional notes about this download.', 'edd' ) . '</small>';
	$download_list .= '</li>';
	$download_list .= '</ul></li>';
	$download_list .= '</ul>';

	$price = edd_currency_filter( edd_format_amount( 9.50 ) );

	$gateway = 'PayPal';

	$receipt_id = strtolower( md5( uniqid() ) );

	$notes = __( 'These are some sample notes added to a product.', 'edd' );

	$message = str_replace( '{name}', 'John Doe', $message );
	$message = str_replace( '{download_list}', $download_list, $message );
	$message = str_replace( '{date}', date( get_option( 'date_format' ), time() ), $message );
	$message = str_replace( '{sitename}', get_bloginfo( 'name' ), $message );
	$message = str_replace( '{price}', $price, $message );
	$message = str_replace( '{payment_method}', $gateway, $message );
	$message = str_replace( '{receipt_id}', $receipt_id, $message );

	return wpautop( $message );

}

/**
 * Email Default Formatting
 *
 * @access private
 * @since 1.0
 * @return string
 */

function edd_email_default_formatting( $message ) {
	return wpautop( $message );
}
add_filter( 'edd_purchase_receipt', 'edd_email_default_formatting' );


/**
 * Email Template Preview
 *
 * @access private
 * @since 1.0.8.2
 * @echo string
 */

function edd_email_template_preview() {
	global $edd_options;

	$default_email_body = __( "Dear", "edd" ) . " {name},\n\n";
	$default_email_body .= __( "Thank you for your purchase. Please click on the link(s) below to download your files.", "edd" ) . "\n\n";
	$default_email_body .= "{download_list}\n\n";
	$default_email_body .= "{sitename}";

	$email_body = isset( $edd_options['purchase_receipt'] ) ? $edd_options['purchase_receipt'] : $default_email_body;
	ob_start(); ?>
	<a href="#email-preview" id="open-email-preview" class="button-secondary" title="<?php _e( 'Purchase Receipt Preview', 'edd' ); ?> "><?php _e( 'Preview Purchase Receipt', 'edd' ); ?></a>
	<div id="email-preview-wrap" style="display:none;">
		<div id="email-preview">
			<?php echo edd_apply_email_template( $email_body, null, null ); ?>
		</div>
	</div>
	<?php
	echo ob_get_clean();
}
add_action( 'edd_email_settings', 'edd_email_template_preview' );


/**
 * Email Template Header
 *
 * @access private
 * @since 1.0.8.2
 * @echo string
 */

function edd_get_email_body_header() {
	ob_start(); ?>
	<html><head><style type="text/css">#outlook a{padding: 0;}</style></head><body>
	<?php
	do_action( 'edd_email_body_header' );
	return ob_get_clean();
}


/**
 * Email Template Body
 *
 * @access private
 * @since 1.0.8.2
 * @echo string
 */

function edd_get_email_body_content( $payment_id, $payment_data ) {

	global $edd_options;

	$default_email_body = __( "Dear", "edd" ) . " {name},\n\n";
	$default_email_body .= __( "Thank you for your purchase. Please click on the link(s) below to download your files.", "edd" ) . "\n\n";
	$default_email_body .= "{download_list}\n\n";
	$default_email_body .= "{sitename}";

	$email = isset( $edd_options['purchase_receipt'] ) ? $edd_options['purchase_receipt'] : $default_email_body;

	$email_body = edd_email_template_tags( $email, $payment_data, $payment_id );
	return apply_filters( 'edd_purchase_receipt', $email_body, $payment_id, $payment_data );
}


/**
 * Email Template Footer
 *
 * @access private
 * @since 1.0.8.2
 * @return string
 */

function edd_get_email_body_footer() {
	ob_start();
	do_action( 'edd_email_body_footer' );
	?>
	</body></html>
	<?php
	return ob_get_clean();
}

/**
 * Applies the Chosen Email Template
 *
 * @access private
 * @since 1.0.8.2
 * @param string  - the contents of the receipt email
 * @param int     - the ID of the payment we are sending a receipt for
 * @param array   - an array of meta information for the payment
 * @return string
 */

function edd_apply_email_template( $body, $payment_id, $payment_data ) {

	global $edd_options;

	$template_name = isset( $edd_options['email_template'] ) ? $edd_options['email_template'] : 'default';

	if ( $template_name == 'none' ) {
		if ( is_admin() )
			$body = edd_email_preview_templage_tags( $body );

		return $body; // return the plain email with no template
	}

	ob_start();

	do_action( 'edd_email_template_' . $template_name );

	$template = ob_get_clean();

	if ( is_admin() )
		$body = edd_email_preview_templage_tags( $body );

	$body = apply_filters( 'edd_purchase_receipt_' . $template_name, $body );

	$email = str_replace( '{email}', $body, $template );

	return $email;

}
add_filter( 'edd_purchase_receipt', 'edd_apply_email_template', 20, 3 );


/**
 * Default Email Template
 *
 * @access private
 * @since 1.0.8.2
 * @echo string
 */

function edd_default_email_template() {

	echo '<div style="width: 550px; border: 1px solid #ccc; background: #f0f0f0; padding: 8px 10px; margin: 0 auto;">';
	echo '<div id="edd-email-content" style="background: #fff; border: 1px solid #ccc; padding: 10px;">';
	echo '{email}'; // this tag is required in order for the contents of the email to be shown
	echo '</div>';
	echo '</div>';

}
add_action( 'edd_email_template_default', 'edd_default_email_template' );


/**
 * Default Email Template Styling Extras
 *
 * @access private
 * @since 1.0.9.1
 * @return string
 */

function edd_default_email_styling( $email_body ) {

	$first_p = strpos( $email_body, '<p>' );
	$email_body = substr_replace( $email_body, '<p style="margin-top:0;">', $first_p, 3 );

	return $email_body;
}
add_filter( 'edd_purchase_receipt_default', 'edd_default_email_styling' );
