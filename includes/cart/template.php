<?php
/**
 * Cart Template
 *
 * @package     EDD
 * @subpackage  Cart
 * @copyright   Copyright (c) 2015, Pippin Williamson
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

	// Check if the Update cart button should be shown
	if( edd_item_quantities_enabled() ) {
		add_action( 'edd_cart_footer_buttons', 'edd_update_cart_button' );
	}

	// Check if the Save Cart button should be shown
	if( ! edd_is_cart_saving_disabled() ) {
		add_action( 'edd_cart_footer_buttons', 'edd_save_cart_button' );
	}

	do_action( 'edd_before_checkout_cart' );
	echo '<form id="edd_checkout_cart_form" method="post">';
		echo '<div id="edd_checkout_cart_wrap">';
			do_action( 'edd_checkout_cart_top' );
			edd_get_template_part( 'checkout_cart' );
			do_action( 'edd_checkout_cart_bottom' );
		echo '</div>';
	echo '</form>';
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
	ob_start();

	do_action( 'edd_before_cart' );

	edd_get_template_part( 'widget', 'cart' );

	do_action( 'edd_after_cart' );

	if ( $echo ) {
		echo ob_get_clean();
	} else {
		return ob_get_clean();
	}
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

	$remove_url = edd_remove_item_url( $cart_key );
	$title      = get_the_title( $id );
	$options    = !empty( $item['options'] ) ? $item['options'] : array();
	$quantity   = edd_get_cart_item_quantity( $id, $options );
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
	$item = str_replace( '{item_quantity}', absint( $quantity ), $item );
	$item = str_replace( '{remove_url}', $remove_url, $item );
  	$subtotal = '';
  	if ( $ajax ){
   	 $subtotal = edd_currency_filter( edd_format_amount( edd_get_cart_subtotal() ) ) ;
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
	return apply_filters( 'edd_empty_cart_message', '<span class="edd_empty_cart">' . __( 'Your cart is empty.', 'easy-digital-downloads' ) . '</span>' );
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
	global $wp_filter, $wp_version;

	$columns_count = 3;

	if ( ! empty( $wp_filter['edd_checkout_table_header_first'] ) ) {
		$header_first_count = 0;
		$callbacks = version_compare( $wp_version, '4.7', '>=' ) ? $wp_filter['edd_checkout_table_header_first']->callbacks : $wp_filter['edd_checkout_table_header_first'] ;

		foreach ( $callbacks as $callback ) {
			$header_first_count += count( $callback );
		}
		$columns_count += $header_first_count;
	}

	if ( ! empty( $wp_filter['edd_checkout_table_header_last'] ) ) {
		$header_last_count = 0;
		$callbacks = version_compare( $wp_version, '4.7', '>=' ) ? $wp_filter['edd_checkout_table_header_last']->callbacks : $wp_filter['edd_checkout_table_header_last'] ;

		foreach ( $callbacks as $callback ) {
			$header_last_count += count( $callback );
		}
		$columns_count += $header_last_count;
	}

	return apply_filters( 'edd_checkout_cart_columns', $columns_count );
}

/**
 * Display the "Save Cart" button on the checkout
 *
 * @since 1.8
 * @return void
 */
function edd_save_cart_button() {
	if ( edd_is_cart_saving_disabled() )
		return;

	$color = edd_get_option( 'checkout_color', 'blue' );
	$color = ( $color == 'inherit' ) ? '' : $color;

	if ( edd_is_cart_saved() ) : ?>
		<a class="edd-cart-saving-button edd-submit button<?php echo ' ' . $color; ?>" id="edd-restore-cart-button" href="<?php echo esc_url( add_query_arg( array( 'edd_action' => 'restore_cart', 'edd_cart_token' => edd_get_cart_token() ) ) ); ?>"><?php _e( 'Restore Previous Cart', 'easy-digital-downloads' ); ?></a>
	<?php endif; ?>
	<a class="edd-cart-saving-button edd-submit button<?php echo ' ' . $color; ?>" id="edd-save-cart-button" href="<?php echo esc_url( add_query_arg( 'edd_action', 'save_cart' ) ); ?>"><?php _e( 'Save Cart', 'easy-digital-downloads' ); ?></a>
	<?php
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
		echo ' <a class="edd-cart-saving-link" id="edd-restore-cart-link" href="' . esc_url( add_query_arg( array( 'edd_action' => 'restore_cart', 'edd_cart_token' => edd_get_cart_token() ) ) ) . '">' . __( 'Restore Previous Cart.', 'easy-digital-downloads' ) . '</a>';
	}
}
add_action( 'edd_cart_empty', 'edd_empty_cart_restore_cart_link' );

/**
 * Display the "Save Cart" button on the checkout
 *
 * @since 1.8
 * @return void
 */
function edd_update_cart_button() {
	if ( ! edd_item_quantities_enabled() )
		return;

	$color = edd_get_option( 'checkout_color', 'blue' );
	$color = ( $color == 'inherit' ) ? '' : $color;
?>
	<input type="submit" name="edd_update_cart_submit" class="edd-submit edd-no-js button<?php echo ' ' . $color; ?>" value="<?php _e( 'Update Cart', 'easy-digital-downloads' ); ?>"/>
	<input type="hidden" name="edd_action" value="update_cart"/>
<?php

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
		foreach ( $messages as $message_id => $message ) {

			// Try and detect what type of message this is
			if ( strpos( strtolower( $message ), 'error' ) ) {
				$type = 'error';
			} elseif ( strpos( strtolower( $message ), 'success' ) ) {
				$type = 'success';
			} else {
				$type = 'info';
			}

			$classes = apply_filters( 'edd_' . $type . '_class', array(
				'edd_errors', 'edd-alert', 'edd-alert-' . $type
			) );

			echo '<div class="' . implode( ' ', $classes ) . '">';
				// Loop message codes and display messages
					echo '<p class="edd_error" id="edd_msg_' . $message_id . '">' . $message . '</p>';
			echo '</div>';

		}

		// Remove all of the cart saving messages
		EDD()->session->set( 'edd_cart_messages', null );
	}
}
add_action( 'edd_before_checkout_cart', 'edd_display_cart_messages' );

/**
 * Show Added To Cart Messages
 *
 * @since 1.0
 * @param int $download_id Download (Post) ID
 * @return void
 */
function edd_show_added_to_cart_messages( $download_id ) {
	if ( isset( $_POST['edd_action'] ) && $_POST['edd_action'] == 'add_to_cart' ) {
		if ( $download_id != absint( $_POST['download_id'] ) )
			$download_id = absint( $_POST['download_id'] );

		$alert = '<div class="edd_added_to_cart_alert">'
		. sprintf( __('You have successfully added %s to your shopping cart.','easy-digital-downloads' ), get_the_title( $download_id ) )
		. ' <a href="' . edd_get_checkout_uri() . '" class="edd_alert_checkout_link">' . __('Checkout.','easy-digital-downloads' ) . '</a>'
		. '</div>';

		echo apply_filters( 'edd_show_added_to_cart_messages', $alert );
	}
}
add_action('edd_after_download_content', 'edd_show_added_to_cart_messages');
