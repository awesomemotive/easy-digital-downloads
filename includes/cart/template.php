<?php
/**
 * Cart Template
 *
 * @package     EDD
 * @subpackage  Cart
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Builds the Cart by providing hooks and calling all the hooks for the Cart
 *
 * @since 1.0
 * @return void
 */
function edd_checkout_cart() {
	do_action( 'edd_before_checkout_cart' );
	echo '<!--dynamic-cached-content-->';
	echo '<form id="edd_checkout_cart_form" method="post">';
		echo '<div id="edd_checkout_cart_wrap">';
			edd_get_template_part( 'checkout_cart' );
		echo '</div>';
	echo '</form>';
	echo '<!--/dynamic-cached-content-->';
	do_action( 'edd_after_checkout_cart' );
}

/**
 * Renders the Shopping Cart
 *
 * @since 1.0
 *
 * @param bool $echo
 * @return string Fully formatted cart
 */
function edd_shopping_cart( $echo = false ) {
	global $edd_options;

	ob_start();
	do_action('edd_before_cart');
	$display = 'style="display:none;"';
  	$cart_quantity = edd_get_cart_quantity();

  	if ( $cart_quantity > 0 ){
  	  $display = "";
  	}

  	echo "<p class='edd-cart-number-of-items' {$display}>" . __( 'Number of items in cart', 'edd' ) . ': <span class="edd-cart-quantity">' . $cart_quantity . '</span></p>';
 	?>

	<ul class="edd-cart">
	<!--dynamic-cached-content-->
	<?php
		$cart_items = edd_get_cart_contents();
		if($cart_items) :
			foreach( $cart_items as $key => $item ) :
				echo edd_get_cart_item_template( $key, $item, false );
			endforeach;
			edd_get_template_part( 'widget', 'cart-checkout' );
		else :
			edd_get_template_part( 'widget', 'cart-empty' );
		endif;
	?>
	<!--/dynamic-cached-content-->
	</ul>
	<?php

	do_action( 'edd_after_cart' );

	if ( $echo )
		echo ob_get_clean();
	else
		return ob_get_clean();
}

/**
 * Get Cart Item Template
 *
 * @since 1.0
 * @param int $cart_key Cart key
 * @param array $item Cart item
 * @param bool $ajax AJAX?
 * @return string Cart item
*/
function edd_get_cart_item_template( $cart_key, $item, $ajax = false ) {
	global $post;

	$id = is_array( $item ) ? $item['id'] : $item;

	$remove_url = edd_remove_item_url( $cart_key, $post, $ajax );
	$title      = get_the_title( $id );
	$options    = !empty( $item['options'] ) ? $item['options'] : array();
	$price      = edd_get_cart_item_price( $id, $options );

	if ( ! empty( $options ) ) {
		$title .= ( edd_has_variable_prices( $item['id'] ) ) ? ' <span class="edd-cart-item-separator">-</span> ' . edd_get_price_name( $id, $item['options'] ) : edd_get_price_name( $id, $item['options'] );
	}

	ob_start();

	edd_get_template_part( 'widget', 'cart-item' );

	$item = ob_get_clean();

	$item = str_replace( '{item_title}', $title, $item );
	$item = str_replace( '{item_amount}', edd_currency_filter( edd_format_amount( $price ) ), $item );
	$item = str_replace( '{cart_item_id}', absint( $cart_key ), $item );
	$item = str_replace( '{item_id}', absint( $id ), $item );
	$item = str_replace( '{remove_url}', $remove_url, $item );
  	$subtotal = '';
  	if ( $ajax ){
   	 $subtotal = edd_currency_filter( edd_format_amount( edd_get_cart_amount( false ) ) ) ;
  	}
 	$item = str_replace( '{subtotal}', $subtotal, $item );

	return apply_filters( 'edd_cart_item', $item, $id );
}

/**
 * Returns the Empty Cart Message
 *
 * @since 1.0
 * @return string Cart is empty message
 */
function edd_empty_cart_message() {
	return apply_filters( 'edd_empty_cart_message', '<span class="edd_empty_cart">' . __( 'Your cart is empty.', 'edd' ) . '</span>' );
}

/**
 * Echoes the Empty Cart Message
 *
 * @since 1.0
 * @return void
 */
function edd_empty_checkout_cart() {
	echo edd_empty_cart_message();
}
add_action( 'edd_cart_empty', 'edd_empty_checkout_cart' );

/*
 * Calculate the number of columns in the cart table dynamically.
 *
 * @since 1.8
 * @return int The number of columns
 */
function edd_checkout_cart_columns() {
	$head_first = did_action( 'edd_checkout_table_header_first' );
	$head_last  = did_action( 'edd_checkout_table_header_last' );
	$default    = 3;

	return apply_filters( 'edd_checkout_cart_columns', $head_first + $head_last + $default );
}

/**
 * Display the "Save Cart" button on the checkout
 *
 * @since 1.8
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_save_cart_button() {
	global $edd_options;

	if ( edd_is_cart_saving_disabled() )
		return;

	$color = isset( $edd_options[ 'checkout_color' ] ) ? $edd_options[ 'checkout_color' ] : 'gray';
	$color = ( $color == 'inherit' ) ? '' : $color;

	if ( edd_is_cart_saved() ) : ?>
		<a class="edd-cart-saving-button edd-submit button<?php echo ' ' . $color; ?>" id="edd-restore-cart-button" href="<?php echo add_query_arg( 'edd_action', 'restore_cart' ) ?>"><?php _e( 'Restore Previous Cart', 'edd' ); ?></a>
	<?php endif; ?>
	<a class="edd-cart-saving-button edd-submit button<?php echo ' ' . $color; ?>" id="edd-save-cart-button" href="<?php echo add_query_arg( 'edd_action', 'save_cart' ) ?>"><?php _e( 'Save Cart', 'edd' ); ?></a>
	<?php
}
if( ! edd_is_cart_saving_disabled() ) {
	add_action( 'edd_cart_footer_buttons', 'edd_save_cart_button' );
}

/**
 * Displays the restore cart link on the empty cart page, if a cart is saved
 *
 * @since 1.8
 * @return void
 */
function edd_empty_cart_restore_cart_link() {

	if( edd_is_cart_saving_disabled() )
		return;

	if( edd_is_cart_saved() ) {
		echo ' <a class="edd-cart-saving-link" id="edd-restore-cart-link" href="' . add_query_arg( array( 'edd_action' => 'restore_cart', 'edd_cart_token' => edd_get_cart_token() ) ) . '">' . __( 'Restore Previous Cart.', 'edd' ) . '</a>';
	}
}
add_action( 'edd_cart_empty', 'edd_empty_cart_restore_cart_link' );

/**
 * Display the "Save Cart" button on the checkout
 *
 * @since 1.8
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_update_cart_button() {
	global $edd_options;

	if ( ! edd_item_quanities_enabled() )
		return;

	$color = isset( $edd_options[ 'checkout_color' ] ) ? $edd_options[ 'checkout_color' ] : 'gray';
	$color = ( $color == 'inherit' ) ? '' : $color;
?>
	<input type="submit" name="edd_update_cart_submit" class="edd-submit button<?php echo ' ' . $color; ?>" value="<?php _e( 'Update Cart', 'edd' ); ?>"/>
	<input type="hidden" name="edd_action" value="update_cart"/>
<?php

}
if( edd_item_quanities_enabled() ) {
	add_action( 'edd_cart_footer_buttons', 'edd_update_cart_button' );
}

/**
 * Display the messages that are related to cart saving
 *
 * @since 1.8
 * @return void
 */
function edd_display_cart_messages() {
	$messages = EDD()->session->get( 'edd_cart_messages' );

	if ( $messages ) {
		$classes = apply_filters( 'edd_error_class', array(
			'edd_errors'
		) );
		echo '<div class="' . implode( ' ', $classes ) . '">';
		    // Loop message codes and display messages
		   foreach ( $messages as $message_id => $message ){
		        echo '<p class="edd_error" id="edd_msg_' . $message_id . '">' . $message . '</p>';
		   }
		echo '</div>';

		// Remove all of the cart saving messages
		EDD()->session->set( 'edd_cart_messages', null );
	}
}
add_action( 'edd_before_checkout_cart', 'edd_display_cart_messages' );
