<?php
/**
 * Order Overview: Actions
 *
 * @package     EDD
 * @subpackage  Admin/Views
 * @copyright   Copyright (c) 2020, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

$is_refundable    = edd_is_order_refundable( $order->id );
$is_override      = edd_is_order_refundable_by_override( $order->id );
$is_window_passed = edd_is_order_refund_window_passed( $order->id );

if ( true === edd_is_add_order_page() ) :
?>
	<button
		id="add-adjustment"
		class="button button-secondary"
	>
		<?php echo esc_html_x( 'Add Adjustment', 'Apply an adjustment to an order', 'easy-digital-downloads' ); ?>
	</button>

	<?php if ( true === edd_has_active_discounts() ) : ?>
	<button
		id="add-discount"
		class="button button-secondary"
	>
		<?php echo esc_html_x( 'Add Discount', 'Apply a discount to an order', 'easy-digital-downloads' ); ?>
	</button>
	<?php endif; ?>

	<button
		id="add-item"
		class="button button-secondary"
		autofocus
	>
		<?php echo esc_html( sprintf( __( 'Add %s', 'easy-digital-downloads' ), edd_get_label_singular() ) ); ?>
	</button>
<?php elseif ( 'refunded' !== $order->status ) : ?>
	<div class="edd-order-overview-actions__locked">
		<?php esc_html_e( 'Order items cannot be modified.', 'easy-digital-downloads' ); ?>
		<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php esc_attr_e( 'Issue a refund to adjust the net total for this order.', 'easy-digital-downloads' ); ?>"></span>
	</div>

	<div class="edd-order-overview-actions__refund">
		<?php if ( true === $is_refundable && true === $is_override && true === $is_window_passed ) : ?>
			<span class="edd-help-tip dashicons dashicons-unlock" title="<?php esc_attr_e( 'The refund window for this Order has passed; however, you have the ability to override this.', 'easy-digital-downloads' ); ?>"></span>
		<?php elseif ( false === $is_refundable && true === $is_window_passed ) : ?>
			<span class="edd-help-tip dashicons dashicons-lock" title="<?php esc_attr_e( 'The refund window for this Order has passed.', 'easy-digital-downloads' ); ?>"></span>
		<?php endif; ?>

		<button
			id="refund"
			class="button button-secondary edd-refund-order"
			<?php if ( false === $is_refundable && false === $is_override ) : ?>
				disabled
			<?php endif; ?>
		>
			<?php esc_html_e( 'Issue Refund', 'easy-digital-downloads' ); ?>
		</button>
	</div>
<?php endif; ?>
