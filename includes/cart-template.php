<?php

function edd_checkout_cart() {
    global $post;
	ob_start(); ?>
	<?php do_action('edd_before_checkout_cart'); ?>
	<table id="edd_checkout_cart" <?php if(edd_is_ajax_enabled()) { echo 'class="ajaxed"'; } ?>>
		<thead>
			<tr>
				<?php do_action('edd_checkout_table_header_first'); ?>
				<th><?php _e('Item Name', 'edd'); ?></th>
				<th><?php _e('Item Price', 'edd'); ?></th>
				<th><?php _e('Actions', 'edd'); ?></th>
				<?php do_action('edd_checkout_table_header_last'); ?>
			</tr>
		</thead>
		<tbody>
			<?php $cart_items = edd_get_cart_contents(); ?>
			<?php if($cart_items) : ?>
				<?php foreach($cart_items as $key => $item) : ?>
					<tr>
						<?php do_action('edd_checkout_table_body_first', $item); ?>
						<td><?php echo get_the_title($item); ?></td>
						<td><?php echo edd_currency_filter(edd_get_download_price($item)); ?></td>
						<td><a href="<?php echo edd_remove_item_url($key, $post); ?>"><?php _e('remove', 'edd'); ?></td>
						<?php do_action('edd_checkout_table_body_last', $item); ?>
					</tr>
				<?php endforeach; ?>
			<?php else: ?>
				<tr class="edd_cart_item" id="edd_cart_item_<?php echo $item; ?>">
					<td colspan="3"><?php _e('Your shopping cart is empty', 'edd'); ?></td>
				</tr>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<?php do_action('edd_checkout_table_footer_first'); ?>
				<th colspan="3" class="edd_cart_total"><?php _e('Total', 'edd'); ?>: <span class="edd_cart_amount"><?php echo edd_currency_filter(edd_format_amount(edd_get_cart_amount())); ?></span></th>
				<?php do_action('edd_checkout_table_footer_last'); ?>
			</tr>
		</tfoot>
	</table>
	<?php do_action('edd_after_checkout_cart'); ?>
	<?php
	return ob_get_clean();
}

function edd_cart_widget($echo = false) {
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