<?php foreach ( edd_get_cart_fees() as $fee_id => $fee ) : ?>
	<div class="edd-blocks-cart__row edd-blocks-cart__row-footer edd_cart_fee" id="edd_cart_fee_<?php echo esc_attr( $fee_id ); ?>">

		<?php do_action( 'edd_cart_fee_rows_before', $fee_id, $fee ); ?>

		<div class="edd_cart_fee_label"><?php echo esc_html( $fee['label'] ); ?></div>
		<div class="edd_cart_fee_amount">
			<?php echo esc_html( edd_currency_filter( edd_format_amount( $fee['amount'] ) ) ); ?>
			<?php if ( ! empty( $fee['type'] ) && 'item' === $fee['type'] ) : ?>
				<div class="edd_cart_actions">
					<a
						class="edd-blocks-cart__action-remove"
						href="<?php echo esc_url( edd_remove_cart_fee_url( $fee_id ) ); ?>"
					>
						<span class="screen-reader-text"><?php esc_html_e( 'Remove', 'easy-digital-downloads' ); ?></span>
					</a>
				</div>
			<?php endif; ?>
		</div>

		<?php do_action( 'edd_cart_fee_rows_after', $fee_id, $fee ); ?>

	</div>
<?php endforeach;
