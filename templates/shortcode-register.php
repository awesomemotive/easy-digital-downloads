<?php
global $edd_register_redirect;

edd_print_errors(); ?>

<form id="edd_register_form" class="edd_form" action="" method="post">
	<?php do_action( 'edd_register_form_fields_top' ); ?>

	<fieldset>
		<legend><?php _e( 'Register New Account', 'edd' ); ?></legend>

		<?php do_action( 'edd_register_form_fields_before' ); ?>

		<p>
			<label for="edd-user-login"><?php _e( 'Username', 'edd' ); ?></label>
			<input id="edd-user-login" class="required edd-input" type="text" name="edd_user_login" title="<?php esc_attr_e( 'Username', 'edd' ); ?>" />
		</p>

		<p>
			<label for="edd-user-email"><?php _e( 'Email', 'edd' ); ?></label>
			<input id="edd-user-email" class="required edd-input" type="email" name="edd_user_email" title="<?php esc_attr_e( 'Email Address', 'edd' ); ?>" />
		</p>

		<p>
			<label for="edd-user-pass"><?php _e( 'Password', 'edd' ); ?></label>
			<input id="edd-user-pass" class="password required edd-input" type="password" name="edd_user_pass" />
		</p>

		<p>
			<label for="edd-user-pass2"><?php _e( 'Confirm Password', 'edd' ); ?></label>
			<input id="edd-user-pass2" class="password required edd-input" type="password" name="edd_user_pass2" />
		</p>


		<?php do_action( 'edd_register_form_fields_before_submit' ); ?>

		<p>
			<input type="hidden" name="edd_honeypot" value="" />
			<input type="hidden" name="edd_action" value="user_register" />
			<input type="hidden" name="edd_redirect" value="<?php echo esc_url( $edd_register_redirect ); ?>"/>
			<input class="button" name="edd_register_submit" type="submit" value="<?php esc_attr_e( 'Register', 'edd' ); ?>" />
		</p>

		<?php do_action( 'edd_register_form_fields_after' ); ?>
	</fieldset>

	<?php do_action( 'edd_register_form_fields_bottom' ); ?>
</form>
