<?php
$order_discounts = $order->get_discounts();
if ( ! $order_discounts ) {
	return;
}

$label = _n( 'Discount', 'Discounts', count( $order_discounts ), 'easy-digital-downloads' );
?>
<div class="edd-blocks__row edd-blocks-receipt__row-item">
	<div class="edd-blocks__row-label"><?php echo esc_html( $label ); ?>:</div>
</div>
<?php
foreach ( $order_discounts as $order_discount ) {
	$label = $order_discount->description;
	if ( 'percent' === edd_get_discount_type( $order_discount->type_id ) ) {
		$rate   = edd_format_discount_rate( 'percent', edd_get_discount_amount( $order_discount->type_id ) );
		$label .= "&nbsp;({$rate})";
	}
	?>
	<div class="edd-blocks__row edd-blocks-receipt__row-item">
		<div><?php echo esc_html( $label ); ?></div>
		<div><?php echo esc_html( edd_display_amount( edd_negate_amount( $order_discount->total ), $order->currency ) ); ?></div>
	</div>
	<?php
}
