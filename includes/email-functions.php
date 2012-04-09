<?php

// email the download link and payment confirmation to the buyer
function edd_email_download_link($payment_id, $admin_notice = true) {
	global $edd_options;
	$payment_data = get_post_meta($payment_id, '_edd_payment_meta', true);

	$message = '<html><body>';
		$message .= edd_email_templage_tags($edd_options['purchase_receipt'], $payment_data);
		$message = apply_filters('edd_purchase_receipt', $message);
	$message .= '</body></html>';
	
	$from_email = isset($edd_options['from_email']) ? $edd_options['from_email'] : get_option('admin_email');
	
	$headers = "From: " . $from_email . "\r\n";
	$headers .= "Reply-To: ". $from_email . "\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";	
		
	wp_mail( $payment_data['email'], $edd_options['purchase_subject'], $message, $headers);
	
	if($admin_notice) {
		/* send an email notification to the admin */
		$admin_email = isset($edd_options['from_email']) ? $edd_options['from_email'] : get_option('admin_email');
		$admin_message = __('Hello', 'edd') . "\n\n" . __('A download purchase has been made') . ".\n\n";
		$admin_message .= __('Downloads sold:', 'edd') .  "\n\n";
			
		$download_list = '';	
		foreach(maybe_unserialize($payment_data['downloads']) as $download) {
			$download_list .= get_the_title($download) . "\n";
		}
		
		$admin_message .= $download_list . "\n";
		$admin_message .= __('Amount: ', 'edd') . " " . html_entity_decode(edd_currency_filter($payment_data['amount'])) . "\n\n";
		$admin_message .= __('Thank you', 'edd');
		$admin_message = apply_filters('edd_admin_purchase_notification', $admin_message);
		wp_mail( $admin_email, __('New download purchase', 'edd'), $admin_message );
	}
}

function edd_email_templage_tags($message, $payment_data) {
	
	$user_info = maybe_unserialize($payment_data['user_info']);
	
	if(isset($user_info['first_name'])) {
		$name = $user_info['first_name'];
	} elseif(isset($user_info['id'])) {
		$user_data = get_userdata($user_info['id']);
		$name = $user_data->display_name;
	}
	
	$download_list = '<ul>';
		foreach(maybe_unserialize($payment_data['downloads']) as $download) {
			$download_list .= '<li>' . get_the_title($download) . '<br/>';
			$download_list .= '<ul>';
				$files = edd_get_download_files($download);
				foreach($files as $filekey => $file) {
					$download_list .= '<li>';
						$file_url = edd_get_download_file_url($payment_data['key'], $payment_data['email'], $filekey, $download);
						$download_list .= '<a href="' . $file_url . '">' . $file['name'] . '</a>';
					$download_list .= '</li>';
				}
			$download_list .= '</ul></li>';
		}
	$download_list .= '</ul>';
	
	$message = str_replace('{name}', $name, $message);
	$message = str_replace('{download_list}', $download_list, $message);
	$message = str_replace('{date}', $payment_data['date'], $message);
	$message = str_replace('{sitename}', get_bloginfo('name'), $message);
	$message = apply_filters('edd_email_template_tags', $message);
	
	return $message;
}

function edd_resend_email_links($data) {
	$purchase_id = $data['purchase_id'];
	edd_email_download_link($purchase_id, false);
}
add_action('edd_email_links', 'edd_resend_email_links');