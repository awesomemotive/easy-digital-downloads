<?php
$fees = $order->get_fees();
if ( empty( $fees ) ) {
	return;
}
?>
<div class="edd-blocks__row edd-blocks-receipt__row-item">
	<div class="edd-blocks__row-label"><?php echo esc_html( _n( 'Fee', 'Fees', count( $fees ), 'easy-digital-downloads' ) ); ?>:</div>
</div>
<?php
foreach ( $fees as $fee ) :
	$label = __( 'Fee', 'easy-digital-downloads' );
	if ( ! empty( $fee->description ) ) {
		$label = $fee->description;
	}
	?>
	<div class="edd-blocks__row edd-blocks-receipt__row-item">
		<div><span class="edd_fee_label"><?php echo esc_html( $label ); ?></span></div>
		<div><span class="edd_fee_amount"><?php echo esc_html( edd_display_amount( $fee->subtotal, $order->currency ) ); ?></span></div>
	</div>
	<?php
endforeach;
