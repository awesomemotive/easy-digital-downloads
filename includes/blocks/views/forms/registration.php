<?php
/**
 * Registration form.
 *
 * @var string $redirect_url
 * @var array $block_attributes
 * @package EDD\Blocks\Views\Forms
 */

wp_enqueue_script( 'utils' );
wp_enqueue_script( 'user-profile' );
wp_enqueue_style( 'dashicons' );
?>
<form id="edd-blocks-form__register" class="edd-blocks-form edd-blocks-form__register" action="" method="post">
	<?php
	if ( $block_attributes['username'] ) {
		EDD\Forms\Handler::render_field( '\\EDD\\Forms\\Register\\Username' );
	}
	EDD\Forms\Handler::render_fields(
		array(
			'\\EDD\\Forms\\Register\\Email',
			'\\EDD\\Forms\\Register\\Password',
			'\\EDD\\Forms\\Register\\Weak',
			'\\EDD\\Forms\\Register\\PasswordConfirm',
		)
	);
	?>

	<p class="description indicator-hint"><?php echo wp_kses_post( wp_get_password_hint() ); ?></p>

	<?php do_action( 'edd_register_form_fields_before_submit' ); ?>
	<div class="edd-blocks-form__group edd-blocks-form__group-submit">
		<input type="hidden" name="edd_honeypot" value="" />
		<input type="hidden" name="edd_action" value="user_register" />
		<input type="hidden" name="edd_submit" value="edd_register_submit" />
		<input type="hidden" name="edd_redirect" value="<?php echo esc_url( $redirect_url ); ?>"/>
		<button type="button" class="button wp-generate-pw edd-has-js edd-button-secondary"><?php esc_html_e( 'Generate Password', 'easy-digital-downloads' ); ?></button>
		<input class="<?php echo esc_attr( implode( ' ', EDD\Blocks\Functions\get_button_classes() ) ); ?>" name="edd_register_submit" id="submit" type="submit" value="<?php esc_html_e( 'Register', 'easy-digital-downloads' ); ?>" />
	</div>
	<?php do_action( 'edd_register_form_fields_after' ); ?>
</form>
