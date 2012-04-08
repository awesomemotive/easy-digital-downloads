<?php

function edd_admin_messages() {
	if(isset($_GET['edd-message']) && $_GET['edd-message'] == 'discount_updated') {
		echo '<div class="updated"><p>' . __('Discount code updated.', 'edd') . '</p></div>';
	}
	if(isset($_GET['edd-message']) && $_GET['edd-message'] == 'discount_update_failed') {
		echo '<div class="error"><p>' . __('There was a problem updating your discount code, please try again.', 'edd') . '</p></div>';
	}
}
add_action('admin_notices', 'edd_admin_messages');
