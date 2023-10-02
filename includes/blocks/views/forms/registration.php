<?php
/**
 * @var string $redirect_url
 */

wp_enqueue_script( 'utils' );
wp_enqueue_script( 'user-profile' );
wp_enqueue_style( 'dashicons' );
?>
<form id="edd-blocks-form__register" class="edd-blocks-form edd-blocks-form__register" action="" method="post">
	<div class="edd-blocks-form__group edd-blocks-form__group-username">
		<label for="edd_user_register">
			<?php
			esc_html_e( 'Username or Email', 'easy-digital-downloads' );
			echo EDD()->html->show_required();
			?>
		</label>
		<div class="edd-blocks-form__control">
			<input name="edd_user_login" id="edd_user_register" class="edd-required edd-input" type="text" required/>
		</div>
	</div>
	<div class="edd-blocks-form__group edd-blocks-form__group-email">
		<label for="edd-user-email">
			<?php
			esc_html_e( 'Email', 'easy-digital-downloads' );
			echo EDD()->html->show_required();
			?>
		</label>
		<div class="edd-blocks-form__control">
			<input name="edd_user_email" id="edd-user-email" class="edd-password edd-required edd-input" type="email" required/>
		</div>
	</div>
	<div class="edd-blocks-form__group edd-blocks-form__group-password user-pass1-wrap">
		<label for="pass1">
			<?php
			esc_html_e( 'Password', 'easy-digital-downloads' );
			echo EDD()->html->show_required();
			?>
		</label>
		<div class="edd-blocks-form__control wp-pwd">
			<input type="password" data-reveal="1" data-pw="<?php echo esc_attr( wp_generate_password( 16 ) ); ?>" name="edd_user_pass" id="pass1" class="input password-input" value="" autocomplete="new-password" aria-describedby="pass-strength-result" />

			<button type="button" class="button button-secondary wp-hide-pw edd-has-js" data-toggle="0" aria-label="<?php esc_attr_e( 'Hide password', 'easy-digital-downloads' ); ?>">
				<span class="dashicons dashicons-hidden" aria-hidden="true"></span>
			</button>
			<div id="pass-strength-result" class="edd-has-js" aria-live="polite"><?php esc_html_e( 'Strength indicator', 'easy-digital-downloads' ); ?></div>
		</div>
	</div>
	<div class="edd-blocks-form__group edd-blocks-form__group-pw-weak pw-weak">
		<div class="edd-blocks-form__control">
			<input type="checkbox" name="pw_weak" id="pw-weak" class="pw-checkbox" />
			<label for="pw-weak"><?php esc_html_e( 'Confirm use of weak password', 'easy-digital-downloads' ); ?></label>
		</div>
	</div>
	<div class="edd-blocks-form__group edd-blocks-form__group-password-confirm user-pass2-wrap">
		<label for="pass2">
			<?php
			esc_html_e( 'Confirm Password', 'easy-digital-downloads' );
			echo EDD()->html->show_required();
			?>
		</label>
		<div class="edd-blocks-form__control">
			<input id="pass2" class="password required edd-input" type="password" name="edd_user_pass2" />
		</div>
	</div>

	<p class="description indicator-hint"><?php echo wp_kses_post( wp_get_password_hint() ); ?></p>
	<div class="edd-blocks-form__group edd-blocks-form__group-submit reset-pass-submit">
		<input type="hidden" name="edd_honeypot" value="" />
		<input type="hidden" name="edd_action" value="user_register" />
		<input type="hidden" name="edd_submit" value="edd_register_submit" />
		<input type="hidden" name="edd_redirect" value="<?php echo esc_url( $redirect_url ); ?>"/>
		<button type="button" class="button wp-generate-pw edd-has-js edd-button-secondary"><?php esc_html_e( 'Generate Password', 'easy-digital-downloads' ); ?></button>
		<input class="<?php echo esc_attr( implode( ' ', EDD\Blocks\Functions\get_button_classes() ) ); ?>" name="edd_register_submit" id="edd_register_submit" type="submit" value="<?php esc_html_e( 'Register', 'easy-digital-downloads' ); ?>" />
	</div>
	<?php do_action( 'edd_register_form_fields_after' ); ?>
</form>
