<?php

function edd_query_vars( $vars ) {
	$vars[] = 'edd_action';
	$vars[] = 'cart_item';
	return $vars;
}
add_filter('query_vars', 'edd_query_vars');