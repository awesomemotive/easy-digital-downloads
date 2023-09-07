<fieldset id="edd_login_fields" class="edd-blocks-form">
	<legend><?php esc_html_e( 'Log Into Your Account', 'easy-digital-downloads' ); ?></legend>
	<div class="edd-blocks-form__group edd-blocks-form__group-username">
		<label for="edd_user_login">
			<?php
			esc_html_e( 'Username or Email', 'easy-digital-downloads' );
			echo EDD()->html->show_required();
			?>
		</label>
		<div class="edd-blocks-form__control">
			<input name="edd_user_login" id="edd_user_login" class="edd-required edd-input" type="text" required/>
		</div>
	</div>
	<div class="edd-blocks-form__group edd-blocks-form__group-password">
		<label for="edd_user_pass">
			<?php
			esc_html_e( 'Password', 'easy-digital-downloads' );
			echo EDD()->html->show_required();
			?>
		</label>
		<div class="edd-blocks-form__control">
			<input name="edd_user_pass" id="edd_user_pass" class="edd-password edd-required edd-input" type="password" required/>
		</div>
	</div>
	<div id="edd-user-login-submit">
		<input type="submit" class="<?php echo esc_attr( implode( ' ', EDD\Blocks\Functions\get_button_classes() ) ); ?>" name="edd_login_submit" value="<?php esc_html_e( 'Log in', 'easy-digital-downloads' ); ?>"/>
		<?php wp_nonce_field( 'edd-login-form', 'edd_login_nonce', false, true ); ?>
</div>
</fieldset>
