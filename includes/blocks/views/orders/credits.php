<?php
$credits = $order->get_credits();
if ( empty( $credits ) ) {
	return;
}
?>
<div class="edd-blocks__row edd-blocks-receipt__row-item">
	<div class="edd-blocks__row-label"><?php echo esc_html( _n( 'Credit', 'Credits', count( $credits ), 'easy-digital-downloads' ) ); ?>:</div>
</div>
<?php
foreach ( $credits as $credit ) {
	$label = __( 'Credit', 'easy-digital-downloads' );
	if ( ! empty( $credit->description ) ) {
		$label = $credit->description;
	}
	?>
	<div class="edd-blocks__row edd-blocks-receipt__row-item">
		<div class="edd-blocks__row-label"><?php echo esc_html( $label ); ?></div>
		<div><?php echo esc_html( edd_display_amount( edd_negate_amount( $credit->total ), $order->currency ) ); ?></div>
	</div>
	<?php
}
