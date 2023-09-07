<?php require 'personal-info.php'; ?>
<fieldset class="edd-blocks-form">
	<legend><?php esc_html_e( 'Register For a New Account', 'easy-digital-downloads' ); ?></legend>
	<div class="edd-blocks-form__group edd-blocks-form__group-username">
		<label for="edd_user_register">
			<?php
			esc_html_e( 'Username', 'easy-digital-downloads' );
			echo EDD()->html->show_required();
			?>
		</label>
		<div class="edd-blocks-form__control">
			<input name="edd_user_login" id="edd_user_register" class="edd-required edd-input" type="text" required/>
		</div>
	</div>
	<div class="edd-blocks-form__group edd-blocks-form__group-password">
		<label for="edd-user-pass">
			<?php
			esc_html_e( 'Password', 'easy-digital-downloads' );
			echo EDD()->html->show_required();
			?>
		</label>
		<div class="edd-blocks-form__control">
			<input id="edd-user-pass" class="password required edd-input" type="password" name="edd_user_pass" required />
		</div>
	</div>
	<div class="edd-blocks-form__group edd-blocks-form__group-password-confirm">
		<label for="edd-user-pass2">
			<?php
			esc_html_e( 'Confirm Password', 'easy-digital-downloads' );
			echo EDD()->html->show_required();
			?>
		</label>
		<div class="edd-blocks-form__control">
			<input id="edd-user-pass2" class="password required edd-input" type="password" name="edd_user_pass_confirm" required />
		</div>
		<input type="hidden" name="edd-purchase-var" value="needs-to-register"/>
	</div>
</fieldset>
