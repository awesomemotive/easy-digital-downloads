<?php
/**
 * This template is used to display the registration form with [edd_register]
 */
global $edd_register_redirect;

do_action( 'edd_print_errors' ); ?>

<?php if ( ! is_user_logged_in() ) : ?>

<form id="edd_register_form" class="edd_form" action="" method="post">
	<?php do_action( 'edd_register_form_fields_top' ); ?>

	<fieldset>
		<legend><?php _e( 'Register New Account', 'easy-digital-downloads' ); ?></legend>

		<?php do_action( 'edd_register_form_fields_before' ); ?>

		<p>
			<label for="edd-user-login"><?php _e( 'Username', 'easy-digital-downloads' ); ?></label>
			<input id="edd-user-login" class="required edd-input" type="text" name="edd_user_login" />
		</p>

		<p>
			<label for="edd-user-email"><?php _e( 'Email', 'easy-digital-downloads' ); ?></label>
			<input id="edd-user-email" class="required edd-input" type="email" name="edd_user_email" />
		</p>

		<p>
			<label for="edd-user-pass"><?php _e( 'Password', 'easy-digital-downloads' ); ?></label>
			<input id="edd-user-pass" class="password required edd-input" type="password" name="edd_user_pass" />
		</p>

		<p>
			<label for="edd-user-pass2"><?php _e( 'Confirm Password', 'easy-digital-downloads' ); ?></label>
			<input id="edd-user-pass2" class="password required edd-input" type="password" name="edd_user_pass2" />
		</p>


		<?php do_action( 'edd_register_form_fields_before_submit' ); ?>

		<p>
			<input type="hidden" name="edd_honeypot" value="" />
			<input type="hidden" name="edd_action" value="user_register" />
			<input type="hidden" name="edd_redirect" value="<?php echo esc_url( $edd_register_redirect ); ?>"/>
			<input class="edd-submit" name="edd_register_submit" type="submit" value="<?php esc_attr_e( 'Register', 'easy-digital-downloads' ); ?>" />
		</p>

		<?php do_action( 'edd_register_form_fields_after' ); ?>
	</fieldset>

	<?php do_action( 'edd_register_form_fields_bottom' ); ?>
</form>

<?php else : ?>

	<?php do_action( 'edd_register_form_logged_in' ); ?>

<?php endif; ?>
