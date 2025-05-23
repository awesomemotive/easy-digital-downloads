<?php
/**
 * Login Form.
 *
 * @package EDD\Blocks\Views\Forms
 * @var string $redirect_url
 */

?>
<form id="edd-blocks-form__login" class="edd-blocks-form edd-blocks-form__login" action="" method="post">
	<?php
	do_action( 'edd_login_fields_before' );
	EDD\Forms\Handler::render_fields(
		array(
			'\\EDD\\Forms\\Login\\Username',
			'\\EDD\\Forms\\Login\\Password',
			'\\EDD\\Forms\\Login\\Remember',
		)
	);
	?>
	<div class="edd-blocks-form__group edd-blocks-form__group-submit">
		<input type="hidden" name="edd_redirect" value="<?php echo esc_url( $redirect_url ); ?>"/>
		<input type="hidden" name="edd_login_nonce" value="<?php echo esc_attr( wp_create_nonce( 'edd-login-nonce' ) ); ?>"/>
		<input type="hidden" name="edd_action" value="user_login"/>
		<input id="edd_login_submit" type="submit" class="<?php echo esc_attr( implode( ' ', EDD\Blocks\Functions\get_button_classes() ) ); ?>" value="<?php esc_html_e( 'Log In', 'easy-digital-downloads' ); ?>"/>
	</div>
	<p class="edd-blocks-form__group edd-blocks-form__group-lost-password">
		<a href="<?php echo esc_url( add_query_arg( 'action', 'lostpassword', edd_get_current_page_url() ) ); ?>">
			<?php esc_html_e( 'Lost Password?', 'easy-digital-downloads' ); ?>
		</a>
	</p>
	<?php do_action( 'edd_login_fields_after' ); ?>
</form>
