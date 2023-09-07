<div class="edd-blocks__row edd-blocks-receipt__row-item edd-blocks-receipt__item">
	<div class="edd-blocks-receipt__item-details">
		<div class="edd-blocks__row-label">
			<?php
			echo esc_html( $item->product_name );

			if ( ! empty( $item->status ) && 'complete' !== $item->status ) {
				echo ' &ndash; ' . esc_html( edd_get_status_label( $item->status ) );
			}
			?>
		</div>
		<?php
		$notes = edd_get_product_notes( $item->product_id );
		if ( ! empty( $notes ) ) :
			?>
			<div class="edd_purchase_receipt_product_notes"><?php echo wp_kses_post( wpautop( $notes ) ); ?></div>
			<?php
		endif;

		require 'receipt-files.php';

		if ( edd_use_skus() ) :
			?>
			<div class="edd-blocks-receipt__item-sku">
				<span class="edd-blocks__row-label"><?php esc_html_e( 'SKU:' ); ?></span>
				<?php echo esc_html( edd_get_download_sku( $item->product_id ) ); ?>
			</div>
		<?php endif; ?>
		<?php if ( edd_item_quantities_enabled() ) { ?>
			<div class="edd-blocks-receipt__item-quantity">
				<span class="edd-blocks__row-label"><?php esc_html_e( 'Quantity:' ); ?></span>
				<?php echo esc_html( $item->quantity ); ?>
			</div>
			<?php
		}
		/**
		 * Allow extensions to extend the product cell.
		 * @since 3.0
		 * @param \EDD\Orders\Order_Item $item The current order item.
		 * @param \EDD\Orders\Order $order     The current order object.
		 */
		do_action( 'edd_order_receipt_after_files', $item, $order );
		?>
	</div>
	<div class="edd-blocks-receipt__item-price">
		<?php echo esc_html( edd_display_amount( $item->total, $order->currency ) ); ?>
	</div>
</div>
