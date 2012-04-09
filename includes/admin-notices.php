<?php

function edd_admin_messages() {
	$edd_access_level = edd_get_menu_access_level();
	if(isset($_GET['edd-message']) && $_GET['edd-message'] == 'discount_updated' && current_user_can($edd_access_level)) {
		 add_settings_error( 'edd-notices', 'edd-discount-updated', __('Discount code updated.', 'edd'), 'updated' );
	}
	if(isset($_GET['edd-message']) && $_GET['edd-message'] == 'discount_update_failed' && current_user_can($edd_access_level)) {
		add_settings_error( 'edd-notices', 'edd-discount-updated-fail', __('There was a problem updating your discount code, please try again.', 'edd'), 'error' );
	}
	settings_errors( 'edd-notices' );
}
add_action('admin_notices', 'edd_admin_messages');
