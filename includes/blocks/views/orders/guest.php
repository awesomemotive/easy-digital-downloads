<?php
/**
 * @var string $redirect_url
 */
?>
<form id="edd-blocks-form__guest" class="edd-blocks-form edd-blocks-form__guest" action="" method="post">
	<div class="edd-blocks-form__group edd-blocks-form__group-email">
		<label for="edd_guest_email">
			<?php
			esc_html_e( 'Email Address', 'easy-digital-downloads' );
			echo EDD()->html->show_required();
			?>
		</label>
		<div class="edd-blocks-form__control">
			<input name="edd_guest_email" id="edd_guest_email" class="edd-required edd-input" type="email" maxlength="100" required/>
		</div>
	</div>
	<div class="edd-blocks-form__group edd-blocks-form__group-submit">
		<input type="hidden" name="order_id" value="<?php echo absint( $order->id ); ?>"/>
		<input type="hidden" name="edd_guest_nonce" value="<?php echo esc_attr( wp_create_nonce( 'edd-guest-nonce' ) ); ?>"/>
		<input type="hidden" name="edd_action" value="view_receipt_guest"/>
		<input id="edd_guest_submit" type="submit" class="<?php echo esc_attr( implode( ' ', EDD\Blocks\Functions\get_button_classes() ) ); ?>" value="<?php esc_html_e( 'Confirm Email', 'easy-digital-downloads' ); ?>"/>
	</div>
</form>
