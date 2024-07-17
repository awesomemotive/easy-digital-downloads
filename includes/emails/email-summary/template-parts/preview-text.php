<?php
/**
 * Email Summary Preview Text
 */
?>
<div style="display: none; max-height: 0px; overflow: hidden;">
	<?php echo esc_html( __( 'Store performance summary', 'easy-digital-downloads' ) ); ?> <?php echo esc_html( $date_range['start_date']->format( $wp_date_format ) ); ?> - <?php echo esc_html( $date_range['end_date']->format( $wp_date_format ) ); ?>
</div>
