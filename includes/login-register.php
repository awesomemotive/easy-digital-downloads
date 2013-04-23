<?php
/**
 * Login / Register Functions
 *
 * @package     EDD
 * @subpackage  Functions/Login
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Login Form
 *
 * @since 1.0
 * @global $edd_options
 * @global $post
 * @param string $redirect Redirect page URL
 * @return string Login form
*/
function edd_login_form( $redirect = '' ) {
	global $edd_options, $post;

	if ( $redirect == '' ) {
		$redirect = edd_get_current_page_url();
	}

	ob_start();

	if ( ! is_user_logged_in() ) { ?>
		<?php
		// Show any error messages after form submission
		edd_print_errors(); ?>
		<form id="edd_login_form"  class="edd_form" action="" method="post">
			<fieldset>
				<legend><?php _e( 'Log into Your Account', 'edd' ); ?></legend>
				<?php do_action('edd_checkout_login_fields_before');?>
				<p>
					<label for="edd_user_Login"><?php _e( 'Username', 'edd' ); ?></label>
					<input name="edd_user_login" id="edd_user_login" class="required" type="text" title="<?php _e( 'Username', 'edd' ); ?>"/>
				</p>
				<p>
					<label for="edd_user_pass"><?php _e( 'Password', 'edd' ); ?></label>
					<input name="edd_user_pass" id="edd_user_pass" class="password required" type="password"/>
				</p>
				<p>
					<input type="hidden" name="edd_redirect" value="<?php echo $redirect; ?>"/>
					<input type="hidden" name="edd_login_nonce" value="<?php echo wp_create_nonce( 'edd-login-nonce' ); ?>"/>
					<input type="hidden" name="edd_action" value="user_login"/>
					<input id="edd_login_submit" type="submit" class="edd_submit" value="<?php _e( 'Login', 'edd' ); ?>"/>
				</p>
				<p class="edd-lost-password">
					<a href="<?php echo wp_lostpassword_url(); ?>" title="<?php _e( 'Lost Password', 'edd' ); ?>">
						<?php _e( 'Lost Password?', 'edd' ); ?>
					</a>
				</p>
				<?php do_action('edd_checkout_login_fields_after');?>
			</fieldset>
		</form>
	<?php
	} else {
		echo '<p class="edd-logged-in">' . __('You are already logged in', 'edd' ) . '</p>';
	}
	return ob_get_clean();
}

/**
 * Process Login Form
 *
 * @since 1.0
 * @param array $data Data sent from the login form
 * @return void
*/
function edd_process_login_form( $data ) {
	if ( wp_verify_nonce( $data['edd_login_nonce'], 'edd-login-nonce' ) ) {
		$user_data = get_user_by( 'login', $data['edd_user_login'] );
		if ( $user_data ) {
			$user_ID = $user_data->ID;
			$user_email = $user_data->user_email;
			if ( wp_check_password( $data['edd_user_pass'], $user_data->user_pass, $user_data->ID ) ) {
				edd_log_user_in( $user_data->ID, $data['edd_user_login'], $data['edd_user_pass'] );
			} else {
				edd_set_error( 'password_incorrect', __('The password you entered is incorrect', 'edd' ) );
			}
		} else {
			edd_set_error( 'username_incorrect', __('The username you entered does not exist', 'edd' ) );
		}
		// Check for errors and redirect if none present
		$errors = edd_get_errors();
		if ( ! $errors ) {
			$redirect = apply_filters( 'edd_login_redirect', $data['edd_redirect'], $user_ID );
			wp_redirect( $redirect );
			edd_die();
		}
	}
}
add_action( 'edd_user_login', 'edd_process_login_form' );

/**
 * Log User In
 *
 * @since 1.0
 * @param int $user_id User ID
 * @param string $user_login Username
 * @param string $user_pass Password
 * @return void
*/
function edd_log_user_in( $user_id, $user_login, $user_pass ) {
	if ( $user_id < 1 )
		return;

	wp_set_auth_cookie( $user_id );
	wp_set_current_user( $user_id, $user_login );
	do_action( 'wp_login', $user_login, get_userdata( $user_id ) );
	do_action( 'edd_log_user_in', $user_id, $user_login, $user_pass );
}
