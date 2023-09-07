<?php

// Display a notice if the order was not found in the database.
if ( ! $order ) : ?>

	<div class="edd_errors edd-alert edd-alert-error">
		<?php esc_html_e( 'The specified receipt ID appears to be invalid.', 'easy-digital-downloads' ); ?>
	</div>

	<?php
	return;
endif;

$order_items = $order->get_items();
if ( empty( $order_items ) ) {
	return;
}
?>

<h3><?php echo esc_html( apply_filters( 'edd_payment_receipt_products_title', __( 'Products', 'easy-digital-downloads' ) ) ); ?></h3>

<div class="edd-blocks-receipt__items">
	<?php
	foreach ( $order_items as $key => $item ) {
		// Skip this item if we can't view it.
		if ( ! apply_filters( 'edd_user_can_view_receipt_item', true, $item ) ) {
			continue;
		}
		include 'receipt-item.php';
	}
	?>
</div>
