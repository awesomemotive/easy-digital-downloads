<?php
/**
 * This template is used to display the login form with [edd_login]
 */
global $edd_login_redirect;
if ( ! is_user_logged_in() ) :

	// Show any error messages after form submission
	edd_print_errors(); ?>
	<form id="edd_login_form" class="edd_form" action="" method="post">
		<fieldset>
			<span><legend><?php _e( 'Log into Your Account', 'edd' ); ?></legend></span>
			<?php do_action( 'edd_login_fields_before' ); ?>
			<p>
				<label for="edd_user_Login"><?php _e( 'Username', 'edd' ); ?></label>
				<input name="edd_user_login" id="edd_user_login" class="required edd-input" type="text" title="<?php _e( 'Username', 'edd' ); ?>"/>
			</p>
			<p>
				<label for="edd_user_pass"><?php _e( 'Password', 'edd' ); ?></label>
				<input name="edd_user_pass" id="edd_user_pass" class="password required edd-input" type="password"/>
			</p>
			<p>
				<input type="hidden" name="edd_redirect" value="<?php echo esc_url( $edd_login_redirect ); ?>"/>
				<input type="hidden" name="edd_login_nonce" value="<?php echo wp_create_nonce( 'edd-login-nonce' ); ?>"/>
				<input type="hidden" name="edd_action" value="user_login"/>
				<input id="edd_login_submit" type="submit" class="edd_submit" value="<?php _e( 'Log In', 'edd' ); ?>"/>
			</p>
			<p class="edd-lost-password">
				<a href="<?php echo wp_lostpassword_url(); ?>" title="<?php _e( 'Lost Password', 'edd' ); ?>">
					<?php _e( 'Lost Password?', 'edd' ); ?>
				</a>
			</p>
			<?php do_action( 'edd_login_fields_after' ); ?>
		</fieldset>
	</form>
<?php else : ?>
	<p class="edd-logged-in"><?php _e( 'You are already logged in', 'edd' ); ?></p>
<?php endif; ?>
