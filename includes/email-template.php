<?php
/**
 * Email Template
 *
 * @package     Easy Digital Downloads
 * @subpackage  Email Template
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.8.2
*/


/**
 * Email Template Tags
 *
 * @access      private
 * @since       1.0 
 * @return      string
*/

function edd_email_templage_tags($message, $payment_data) {
	
	$user_info = maybe_unserialize($payment_data['user_info']);
	
	if(isset($user_info['id']) && $user_info['id'] > 0) {
		$user_data = get_userdata($user_info['id']);
		$name = $user_data->display_name;
	} elseif(isset($user_info['first_name'])) {
		$name = $user_info['first_name'];
	} else {
		$name = $user_info['email'];
	}	
	
	$download_list = '<ul>';
		foreach(maybe_unserialize($payment_data['downloads']) as $download) {
			$id = isset($payment_data['cart_details']) ? $download['id'] : $download;
			$download_list .= '<li>' . get_the_title($id) . '<br/>';
			$download_list .= '<ul>';
				$files = edd_get_download_files($id);
				if($files) {
					foreach($files as $filekey => $file) {
						$download_list .= '<li>';
							$file_url = edd_get_download_file_url($payment_data['key'], $payment_data['email'], $filekey, $id);
							$download_list .= '<a href="' . $file_url . '">' . $file['name'] . '</a>';
						$download_list .= '</li>';
					}
				}
			$download_list .= '</ul></li>';
		}
	$download_list .= '</ul>';
	
	$price = $payment_data['amount'];	
	
	$message = str_replace('{name}', $name, $message);
	$message = str_replace('{download_list}', $download_list, $message);
	$message = str_replace('{date}', $payment_data['date'], $message);
	$message = str_replace('{sitename}', get_bloginfo('name'), $message);
	$message = str_replace('{price}', $price, $message);
	$message = apply_filters('edd_email_template_tags', $message, $payment_data);
	
	return $message;
}


/**
 * Email Preview Template Tags
 *
 * @access      private
 * @since       1.0 
 * @return      string
*/

function edd_email_preview_templage_tags( $message ) {

	$download_list = '<ul>';
		$download_list .= '<li>' . __('Sample Product Title', 'edd') . '<br/>';
		$download_list .= '<ul>';
			$download_list .= '<li>';
				$download_list .= '<a href="#">' . __('Sample Download File Name', 'edd') . '</a>';
			$download_list .= '</li>';
		$download_list .= '</ul></li>';
	$download_list .= '</ul>';
	
	$price = edd_currency_filter(9.50);	
	
	$message = str_replace('{name}', 'John Doe', $message);
	$message = str_replace('{download_list}', $download_list, $message);
	$message = str_replace('{date}', date( get_option('date_format'), time() ), $message);
	$message = str_replace('{sitename}', get_bloginfo('name'), $message);
	$message = str_replace('{price}', $price, $message);
	
	return wpautop($message);
	
}

/**
 * Email Default Formatting
 *
 * @access      private
 * @since       1.0 
 * @return      string
*/

function edd_email_default_formatting($message) {
	return wpautop($message);	
}
add_filter('edd_purchase_receipt', 'edd_email_default_formatting');


/**
 * Email Template Preview
 *
 * @access     private
 * @since      1.0.8.2
 * @echo      	string
*/

function edd_email_template_preview() {
	global $edd_options;
	ob_start(); ?>
		<a href="#TB_inline?&amp;inlineId=email-preview" id="open-email-preview" class="thickbox button-secondary" title="<?php _e('Purchase Receipt Preview', 'edd'); ?> "><?php _e('Preview Purchase Receipt', 'edd'); ?></a>
		<div id="email-preview" style="display:none;">
					
			<?php echo edd_apply_email_template($edd_options['purchase_receipt'], null, null); ?>			
						
			<p><a id="edd-close-preview" class="button-secondary" onclick="tb_remove();" title="<?php _e('Close', 'edd'); ?>"><?php _e('Close', 'edd'); ?></a></p>
		</div>
	<?php
	echo ob_get_clean();
}
add_action('edd_email_settings', 'edd_email_template_preview');


/**
 * Email Template Header
 *
 * @access     private
 * @since      1.0.8.2
 * @echo      	string
*/

function edd_get_email_body_header() {
	ob_start(); ?>
	<html><body>
	<?php
	do_action('edd_email_body_header');
	return ob_get_clean();	
}


/**
 * Email Template Body
 *
 * @access     private
 * @since      1.0.8.2
 * @echo      	string
*/

function edd_get_email_body_content( $payment_id, $payment_data ) {
	
	global $edd_options;	
	
	$default_email_body = __("Dear", "edd") . " {name},\n\n";
	$default_email_body .= __("Thank you for your purchase. Please click on the link(s) below to download your files.", "edd") . "\n\n";
	$default_email_body .= "{download_list}\n\n";
	$default_email_body .= "{sitename}";
	
	$email = isset($edd_options['purchase_receipt']) ? $edd_options['purchase_receipt'] : $default_email_body;
	
	$email_body = edd_email_templage_tags($email, $payment_data);
	return apply_filters('edd_purchase_receipt', $email_body, $payment_id, $payment_data);
}


/**
 * Email Template Footer
 *
 * @access     private
 * @since      1.0.8.2
 * @return     string
*/

function edd_get_email_body_footer() {
	ob_start(); 
	do_action('edd_email_body_footer');	
	?>
	</body></html>
	<?php
	return ob_get_clean();
}

/**
 * Applies the Chosen Email Template
 *
 * @access     private
 * @since      1.0.8.2
 * @param		string - the contents of the receipt email
 * @param		int - the ID of the payment we are sending a receipt for
 * @param		array - an array of meta information for the payment
 * @return     string
*/

function edd_apply_email_template( $body, $payment_id, $payment_data ) {
		
	global $edd_options;	
	
	$template_name = isset( $edd_options['email_template'] ) ? $edd_options['email_template'] : 'default';
	
	if( $template_name == 'none' ) {
		if(is_admin())
			$body = edd_email_preview_templage_tags($body);	
		
		return $body; // return the plain email with no template	
	}
	
	ob_start();
		
		do_action('edd_email_template_' . $template_name);
	
	$template = ob_get_clean();	
	
	if(is_admin())
		$body = edd_email_preview_templage_tags($body);	
	
	$email = str_replace('{email}', $body, $template );
	
	$first_p = strpos($email, '<p>');
	$email = substr_replace($email, '<p style="margin-top:0;">', $first_p, 3);
		
	return $email;	
	
}
add_filter('edd_purchase_receipt', 'edd_apply_email_template', 10, 3);


/**
 * Default Email Template
 *
 * @access     private
 * @since      1.0.8.2
 * @echo      	string
*/

function edd_default_email_template() {	
	
	echo '<div style="width: 550px; border: 1px solid #ccc; background: #f0f0f0; padding: 8px 10px; margin: 0 auto;">';
		echo '<div id="edd-email-content" style="background: #fff; border: 1px solid #ccc; padding: 10px;">';
			echo '{email}'; // this tag is required in order for the contents of the email to be shown
		echo '</div>';	
	echo '</div>';
	
}
add_action('edd_email_template_default', 'edd_default_email_template');


/**
 * Get Email Templates
 *
 * @access     private
 * @since      1.0.8.2
 * @return     array
*/

function edd_get_email_templates() {
	$templates = array(
		'default' => __('Default Template', 'edd'),
		'none' => __('No template, plain text only', 'edd')
	);
	return apply_filters( 'edd_email_templates', $templates );
}	
