<?php
wp_enqueue_script( 'utils' );
wp_enqueue_script( 'user-profile' );
wp_enqueue_style( 'dashicons' );
?>
<p class="message">
	<?php esc_html_e( 'Enter your new password below or generate one.', 'easy-digital-downloads' ); ?>
</p>
<form id="edd-blocks-form__reset-password" class="edd-blocks-form edd-blocks-form__reset-password" name="resetpassform" action="" method="post" autocomplete="off">
	<input type="hidden" id="user_login" name="user_login" value="<?php echo esc_attr( $rp_login ); ?>" autocomplete="off" />

	<div class="edd-blocks-form__group edd-blocks-form__group-pass1 user-pass1-wrap">
		<label for="pass1"><?php esc_html_e( 'New password', 'easy-digital-downloads' ); ?></label>
		<div class="wp-pwd edd-blocks-form__control">
			<input type="password" data-reveal="1" data-pw="<?php echo esc_attr( wp_generate_password( 16 ) ); ?>" name="pass1" id="pass1" class="input password-input" size="24" value="" autocomplete="new-password" aria-describedby="pass-strength-result" />

			<button type="button" class="button button-secondary wp-hide-pw edd-has-js" data-toggle="0" aria-label="<?php esc_attr_e( 'Hide password', 'easy-digital-downloads' ); ?>">
				<span class="dashicons dashicons-hidden" aria-hidden="true"></span>
			</button>
			<div id="pass-strength-result" class="edd-has-js" aria-live="polite"><?php esc_html_e( 'Strength indicator' ); ?></div>
		</div>
	</div>
	<div class="edd-blocks-form__group edd-blocks-form__group-pw-weak pw-weak">
		<div class="edd-blocks-form__control">
			<input type="checkbox" name="pw_weak" id="pw-weak" class="pw-checkbox" />
			<label for="pw-weak"><?php esc_html_e( 'Confirm use of weak password' ); ?></label>
		</div>
	</div>

	<div class="edd-blocks-form__group edd-blocks-form__group-pass2 user-pass2-wrap">
		<label for="pass2"><?php esc_html_e( 'Confirm new password' ); ?></label>
		<div class="edd-blocks-form__control">
			<input type="password" name="pass2" id="pass2" class="input" size="20" value="" autocomplete="new-password" />
		</div>
	</div>

	<p class="description indicator-hint"><?php echo wp_kses_post( wp_get_password_hint() ); ?></p>
	<div class="edd-blocks-form__group edd-blocks-form__group-submit reset-pass-submit">
		<input type="hidden" name="rp_key" value="<?php echo esc_attr( $rp_key ); ?>" />
		<input type="hidden" name="edd_redirect" value="<?php echo esc_url( remove_query_arg( 'action', edd_get_current_page_url() ) ); ?>"/>
		<input type="hidden" name="edd_resetpassword_nonce" value="<?php echo esc_attr( wp_create_nonce( 'edd-reset-password-nonce' ) ); ?>"/>
		<input type="hidden" name="edd_action" value="user_reset_password"/>
		<button type="button" class="button wp-generate-pw edd-has-js edd-button-secondary"><?php esc_html_e( 'Generate Password' ); ?></button>
		<input type="submit" id="wp-submit" class="<?php echo esc_attr( implode( ' ', EDD\Blocks\Functions\get_button_classes() ) ); ?>" value="<?php esc_attr_e( 'Save Password' ); ?>" />
	</div>
</form>
