<?php

// updates the purchase data for a payment. Used primarily for adding new downloads to a purchase
function edd_update_edited_purchase($data) {
	if(wp_verify_nonce($data['edd-payment-nonce'], 'edd_payment_nonce')) {
		$payment_id = $_POST['payment-id'];
		$payment_data = get_post_meta($payment_id, '_edd_payment_meta', true);
		$payment_data['downloads'] = serialize($_POST['edd-purchased-downloads']);
		$payment_data['email'] = strip_tags($_POST['edd-buyer-email']);
		update_post_meta($payment_id, '_edd_payment_meta', $payment_data);
		if($_POST['edd-old-status'] != $_POST['edd-payment-status']) {
			wp_update_post(array('ID' => $payment_id, 'post_status' => $_POST['edd-payment-status']));
		}
	}
}
add_action('edd_edit_payment', 'edd_update_edited_purchase');

// removes a payment
function edd_delete_purchase($data) {
	if(wp_verify_nonce($data['_wpnonce'], 'edd_payment_nonce')) {
		$payment_id = $data['purchase_id'];
		wp_delete_post($payment_id, true);
		wp_redirect(admin_url('/edit.php?post_type=download&page=edd-payment-history&edd-message=payment_deleted')); exit;
	}
}
add_action('edd_delete_payment', 'edd_delete_purchase');