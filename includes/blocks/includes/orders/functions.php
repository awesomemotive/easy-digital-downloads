<?php
/**
 * Functions for orders blocks.
 *
 * @package   edd-blocks
 * @copyright 2022 Easy Digital Downloads
 * @license   GPL2+
 * @since 2.0
 */

namespace EDD\Blocks\Orders\Functions;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Gets the order details/downloads link.
 * Also pulls data from the original order history row.
 *
 * @since 2.0
 * @param \EDD\Orders\Order $order
 * @return void
 */
function get_details( $order ) {
	if ( $order->is_recoverable() ) :
		$recovery_url = $order->get_recovery_url();
		if ( $recovery_url ) :
			?>
			&mdash; <a href="<?php echo esc_url( $recovery_url ); ?>">
				<?php esc_html_e( 'Complete Purchase', 'easy-digital-downloads' ); ?>
			</a>
			<?php
		endif;
		?>
	<?php else : ?>
		<div class="edd-blocks-orders__order-downloads-link">
			<a href="<?php echo esc_url( edd_get_receipt_page_uri( $order->id ) ); ?>">
				<?php esc_html_e( 'View Order Details', 'easy-digital-downloads' ); ?>
			</a>
		</div>
		<?php
	endif;
	do_action( 'edd_order_history_order_details', $order );
	do_order_details( $order );
}

/**
 * Outputs the additional order data. Table markup is replaced.
 *
 * @since 2.0
 * @param \EDD\Orders\Order $order
 * @param string $action The action being called.
 * @param mixed  $args
 * @return string
 */
function do_order_details( $order, $action = 'edd_order_history_row_end', ...$args ) {
	ob_start();
	do_action( $action, $order, $args );

	$details = ob_get_clean();
	if ( empty( $details ) ) {
		return;
	}
	$details = replace_table_markup( $details );

	echo wp_kses_post( $details );
}

/**
 * Replaces table markup in a string with <div> elements.
 *
 * @since 2.0
 * @param string $string
 * @return string
 */
function replace_table_markup( $string ) {
	$string = str_replace( '<td', '<div', $string );
	$string = str_replace( '</td', '</div', $string );

	return $string;
}

/**
 * Gets the array of args for retrieving orders for the order history block.
 *
 * @since 2.0
 * @param array $block_attributes The block attributes.
 * @return array
 */
function get_order_history_args( $block_attributes ) {
	$number       = (int) $block_attributes['number'];
	$current_page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
	$args         = array(
		'user_id'        => get_current_user_id(),
		'type'           => 'sale',
		'number'         => $number,
		'offset'         => $number * ( intval( $current_page ) - 1 ),
		'status__not_in' => array( 'trash' )
	);
	if ( ! empty( $block_attributes['recurring'] ) ) {
		$args['status__not_in'] = array( 'edd_subscription' );
	}

	return apply_filters( 'edd_blocks_order_history_args', $args, $block_attributes );
}

/**
 * Gets the EDD purchase session, unless the user is in the block editor.
 * Then it creates a dummy purchase session and uses that to show a sample confirmation screen.
 *
 * @since 2.0
 * @return array|false
 */
function get_purchase_session() {
	if ( ! \EDD\Blocks\Functions\is_block_editor() ) {
		return edd_get_purchase_session();
	}
	$sample_order_key = get_sample_order_payment_key();
	if ( $sample_order_key ) {
		return array(
			'purchase_key' => $sample_order_key,
		);
	}

	return false;
}

/**
 * Gets the payment key:
 *     Checks the URL directly
 *     Checks for the order ID and hashed order value
 *     Checks the current purchase session
 *
 * @since 2.0
 * @return string|false
 */
function get_payment_key() {
	if ( \EDD\Blocks\Functions\is_block_editor( 'edit_shop_payments' ) ) {
		$sample_order_key = get_sample_order_payment_key();
		if ( $sample_order_key ) {
			return $sample_order_key;
		}
	}
	if ( isset( $_GET['payment_key'] ) ) {
		return urldecode( $_GET['payment_key'] );
	}

	if ( ! empty( $_GET['order'] ) && ! empty( $_GET['id'] ) ) {
		return edd_get_payment_key( absint( $_GET['id'] ) );
	}

	$session = edd_get_purchase_session();
	if ( $session ) {
		return $session['purchase_key'];
	}

	return false;
}

/**
 * Gets some orders from the site for use in the editor.
 *
 * @since 2.0
 * @return false|array
 */
function get_sample_order_payment_key() {
	if ( ! \EDD\Blocks\Functions\is_block_editor( 'edit_shop_payments' ) ) {
		return false;
	}

	$orders = edd_get_orders(
		array(
			'number'        => 10,
			'type'          => 'sale',
			'status'        => 'complete',
			'fields'        => 'payment_key',
			'orderby'       => array_rand( array_flip( array( 'email', 'id', 'payment_key', 'total', 'customer_id', 'date_created' ) ) ),
			'order'         => 'ASC',
			'no_found_rows' => true,
		)
	);

	return $orders ? array_rand( array_flip( $orders ) ) : false;
}
