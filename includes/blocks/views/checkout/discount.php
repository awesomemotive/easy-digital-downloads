<?php
if ( isset( $_GET['payment-mode'] ) && edd_is_ajax_disabled() ) {
	return; // Only show before a payment method has been selected if ajax is disabled
}

if ( ! edd_has_active_discounts() || ! edd_get_cart_total() ) {
	return;
}
?>
<div id="edd_discount_code" class="edd-blocks-cart__discount">
	<div id="edd_show_discount" class="edd-has-js">
		<button class="edd-button-secondary edd_discount_link"><?php esc_html_e( 'Enter a discount code', 'easy-digital-downloads' ); ?></button>
	</div>
	<div id="edd-discount-code-wrap" class="edd-cart-adjustment edd-no-js">
		<label class="screen-reader-text" for="edd-discount">
			<?php esc_html_e( 'Discount', 'easy-digital-downloads' ); ?>
		</label>
		<span class="edd-discount-code-field-wrap">
			<input class="edd-input" type="text" id="edd-discount" name="edd-discount" placeholder="<?php esc_html_e( 'Enter discount code', 'easy-digital-downloads' ); ?>"/>
			<input type="submit" class="edd-apply-discount edd-submit wp-block-button__link" value="<?php echo esc_html( _x( 'Apply', 'Apply discount at checkout', 'easy-digital-downloads' ) ); ?>"/>
		</span>
		<span class="edd-discount-loader edd-loading" id="edd-discount-loader" style="display:none;"></span>
		<span id="edd-discount-error-wrap" class="edd_error edd-alert edd-alert-error" aria-hidden="true" style="display:none;"></span>
	</div>
</div>
