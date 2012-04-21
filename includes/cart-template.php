<?php

function edd_checkout_cart() {
	if(file_exists(trailingslashit(get_stylesheet_directory()) . 'edd_templates/checkout_cart.php')) {
		include_once(trailingslashit(get_stylesheet_directory()) . 'edd_templates/checkout_cart.php');
	} else {
		include_once(EDD_PLUGIN_DIR . '/includes/templates/checkout_cart.php');
	}
}

function edd_shopping_cart($echo = false) {
	global $edd_options;
	ob_start(); ?>
	
	<?php do_action('edd_before_cart'); ?>
	<ul class="edd-cart">
	<?php
		$cart_items = edd_get_cart_contents();
		if($cart_items) :
			foreach( $cart_items as $key => $item ) :
				echo edd_get_cart_item_template($key, $item, false);
			endforeach;
			echo '<li class="cart_item edd_checkout"><a href="' . get_permalink($edd_options['purchase_page']) . '">' . __('Checkout', 'edd') . '</a></li>';
		else :
			echo '<li class="cart_item empty">' . apply_filters('edd_empty_cart_message', __('Your cart is empty', 'edd')) . '</li>';
			echo '<li class="cart_item edd_checkout" style="display:none;"><a href="' . get_permalink($edd_options['purchase_page']) . '">' . __('Checkout', 'edd') . '</a></li>';
		endif; ?>
	</ul>
	<?php do_action('edd_after_cart'); ?>
	<?php
	if($echo)
		echo ob_get_clean();
	else
		return ob_get_clean();
}

function edd_get_cart_item_template($cart_key, $item, $ajax = false) {
	global $post;
	
	$remove_url = edd_remove_item_url($cart_key, $post, $ajax);
	$title = get_the_title($item); 
	$remove = '<a href="' . $remove_url . '" data-cart-item="' . $cart_key . '" data-action="edd_remove_from_cart" class="edd-remove-from-cart">' . __('remove', 'edd') . '</a>';	
	$item = '<li class="edd-cart-item">' . $title . ' - ' . $remove . '</li>';
	
	return apply_filters('edd_cart_item', $item);
}