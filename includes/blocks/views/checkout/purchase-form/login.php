<fieldset id="edd_login_fields" class="edd-blocks-form">
	<legend><?php esc_html_e( 'Log Into Your Account', 'easy-digital-downloads' ); ?></legend>
	<?php
	EDD\Forms\Handler::render_fields(
		array(
			'\\EDD\\Forms\\Login\\Username',
			'\\EDD\\Forms\\Login\\Password',
		)
	);
	?>
	<div id="edd-user-login-submit">
		<?php if ( edd_no_guest_checkout() ) : ?>
			<input type="hidden" name="edd-purchase-var" value="needs-to-login"/>
		<?php endif; ?>
		<input type="submit" class="<?php echo esc_attr( implode( ' ', EDD\Blocks\Functions\get_button_classes() ) ); ?>" name="edd_login_submit" value="<?php esc_html_e( 'Log in', 'easy-digital-downloads' ); ?>"/>
		<?php wp_nonce_field( 'edd-login-form', 'edd_login_nonce', false, true ); ?>
	</div>
	<?php
	$login_page = edd_get_login_page_uri();
	if ( $login_page ) {
		?>
		<p class="edd-blocks-form__group edd-blocks-form__group-lost-password">
			<a href="<?php echo esc_url( add_query_arg( 'action', 'lostpassword', $login_page ) ); ?>">
				<?php esc_html_e( 'Lost Password?', 'easy-digital-downloads' ); ?>
			</a>
		</p>
		<?php
	}
	?>
</fieldset>
