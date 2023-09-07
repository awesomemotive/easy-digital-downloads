<?php
if ( empty( $orders ) ) {
	?>
	<p><?php esc_html_e( 'You have no orders.', 'easy-digital-downloads' ); ?></p>
	<?php
	return;
}
?>
<div class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>">
	<?php
	foreach ( $orders as $order ) {
		?>
		<div class="edd-blocks-orders__order">
			<div class="edd-blocks-orders__order-header">
				<div class="edd-blocks-orders__order-id">
					<?php
					printf(
						/* translators: the order */
						esc_html__( 'Order: %s', 'easy-digital-downloads' ),
						esc_html( $order->get_number() )
					);
					?>
				</div>
				<div class="edd-blocks-orders__order-status <?php echo esc_html( $order->status ); ?>"><?php echo esc_html( edd_get_status_label( $order->status ) ); ?></div>
			</div>
			<div class="edd-blocks-orders__order-data">
				<div class="edd-blocks-orders__order-date">
					<?php echo esc_html( edd_date_i18n( EDD()->utils->date( $order->date_created, null, true )->toDateTimeString() ) ); ?>
				</div>
				<div class="edd-blocks-orders__order-amount">
					<?php echo esc_html( edd_display_amount( $order->total, $order->currency ) ); ?>
				</div>
			</div>
			<div class="edd-blocks-orders__order-details">
				<?php echo \EDD\Blocks\Orders\Functions\get_details( $order ); ?>
			</div>
		</div>
		<?php
	}
	?>
</div>
