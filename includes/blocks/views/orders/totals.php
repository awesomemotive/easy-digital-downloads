<?php
/**
 * Allows additional output before displaying the receipt table.
 *
 * @since 3.0
 *
 * @param \EDD\Orders\Order $order          Current order.
 * @param array             $edd_receipt_args [edd_receipt] shortcode arguments.
 */
do_action( 'edd_order_receipt_before_table', $order, array() );
?>
<div class="edd-blocks-receipt__totals">
	<div class="edd-blocks__row edd-blocks__row-header edd-blocks-receipt__row-header">
		<div class="edd-blocks__row-label"><?php echo esc_html_x( 'Order', 'heading', 'easy-digital-downloads' ); ?>:</div>
		<div class="edd-blocks__row-value"><?php echo esc_html( $order->get_number() ); ?></div>
	</div>

	<div class="edd-blocks__row edd-blocks-receipt__row-item">
		<div class="edd-blocks__row-label"><?php esc_html_e( 'Order Status', 'easy-digital-downloads' ); ?>:</div>
		<div class="edd-blocks__row-value edd_receipt_payment_status <?php echo esc_attr( strtolower( $order->status ) ); ?>"><?php echo esc_html( edd_get_status_label( $order->status ) ); ?></div>
	</div>

	<?php if ( $edd_receipt_args['payment_key'] ) : ?>
		<div class="edd-blocks__row edd-blocks-receipt__row-item">
			<div class="edd-blocks__row-label"><?php esc_html_e( 'Payment Key', 'easy-digital-downloads' ); ?>:</div>
			<div class="edd-blocks__row-value"><?php echo esc_html( $order->payment_key ); ?></div>
		</div>
	<?php endif; ?>

	<?php if ( $edd_receipt_args['payment_method'] ) : ?>
		<div class="edd-blocks__row edd-blocks-receipt__row-item">
			<div class="edd-blocks__row-label"><?php esc_html_e( 'Payment Method', 'easy-digital-downloads' ); ?>:</div>
			<div class="edd-blocks__row-value"><?php echo esc_html( edd_get_gateway_checkout_label( $order->gateway ) ); ?></div>
		</div>
	<?php endif; ?>

	<div class="edd-blocks__row edd-blocks-receipt__row-item">
		<div class="edd-blocks__row-label"><?php esc_html_e( 'Date', 'easy-digital-downloads' ); ?>:</div>
		<div class="edd-blocks__row-value"><?php echo esc_html( edd_date_i18n( EDD()->utils->date( $order->date_created, null, true )->toDateTimeString() ) ); ?></div>
	</div>

	<div class="edd-blocks__row edd-blocks-receipt__row-item">
		<div class="edd-blocks__row-label"><?php esc_html_e( 'Subtotal', 'easy-digital-downloads' ); ?>:</div>
		<div class="edd-blocks__row-value">
			<?php echo esc_html( edd_payment_subtotal( $order->id ) ); ?>
		</div>
	</div>

	<?php
	require EDD_BLOCKS_DIR . 'views/orders/discounts.php';
	require EDD_BLOCKS_DIR . 'views/orders/fees.php';
	?>

	<?php if ( $order->tax > 0 ) : ?>
		<div class="edd-blocks__row edd-blocks-receipt__row-item">
			<div class="edd-blocks__row-label"><?php esc_html_e( 'Tax', 'easy-digital-downloads' ); ?>:</div>
			<div class="edd-blocks__row-value"><?php echo esc_html( edd_payment_tax( $order->id ) ); ?></div>
		</div>
	<?php endif; ?>
	<?php require EDD_BLOCKS_DIR . 'views/orders/credits.php'; ?>

	<div class="edd-blocks__row edd-blocks-receipt__row-item">
		<div class="edd-blocks__row-label"><?php esc_html_e( 'Total', 'easy-digital-downloads' ); ?>:</div>
		<div class="edd-blocks__row-value"><?php echo esc_html( edd_payment_amount( $order->id ) ); ?></div>
	</div>

	<?php
	do_action( 'edd_order_receipt_order_details', $order );
	if ( has_action( 'edd_order_receipt_after' ) ) {
		?>
		<div class="edd-blocks-receipt__row-item">
			<?php EDD\Blocks\Orders\Functions\do_order_details( $order, 'edd_order_receipt_after', array() ); ?>
		</div>
		<?php
	}
	?>
</div>
