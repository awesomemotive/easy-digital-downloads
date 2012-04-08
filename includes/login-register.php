<?php

function edd_log_user_in($user_id, $user_login, $user_pass) {
	wp_set_auth_cookie($user_id);
	wp_set_current_user($user_id, $user_login);	
	do_action('wp_login', $user_login);
}