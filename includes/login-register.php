<?php
/**
 * Login / Register Functions
 *
 * @package     EDD
 * @subpackage  Functions/Login
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Login Form
 *
 * @since 1.0
 * @global $post
 * @param string $redirect Redirect page URL
 * @return string Login form
*/
function edd_login_form( $redirect = '' ) {
	global $edd_login_redirect;

	if ( empty( $redirect ) ) {
		$redirect = edd_get_current_page_url();
	}

	$edd_login_redirect = $redirect;

	ob_start();

	edd_get_template_part( 'shortcode', 'login' );

	return apply_filters( 'edd_login_form', ob_get_clean() );
}

/**
 * Registration Form
 *
 * @since 2.0
 * @global $post
 * @param string $redirect Redirect page URL
 * @return string Register form
*/
function edd_register_form( $redirect = '' ) {
	global $edd_register_redirect;

	if ( empty( $redirect ) ) {
		$redirect = edd_get_current_page_url();
	}

	$edd_register_redirect = $redirect;

	ob_start();

	edd_get_template_part( 'shortcode', 'register' );

	return apply_filters( 'edd_register_form', ob_get_clean() );
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
		if ( ! $user_data ) {
			$user_data = get_user_by( 'email', $data['edd_user_login'] );
		}
		do_action( 'edd_process_login_form' );
		if ( $user_data ) {
			$user_ID = $user_data->ID;
			$user_email = $user_data->user_email;

			if ( wp_check_password( $data['edd_user_pass'], $user_data->user_pass, $user_data->ID ) ) {

				if ( isset( $data['rememberme'] ) ) {
					$data['rememberme'] = true;
				} else {
					$data['rememberme'] = false;
				}
				$errors = edd_get_errors();
				if ( ! $errors ) {
					edd_log_user_in( $user_data->ID, $data['edd_user_login'], $data['edd_user_pass'], $data['rememberme'] );
				}
			} else {
				edd_set_error( 'password_incorrect', __( 'The password you entered is incorrect', 'easy-digital-downloads' ) );
			}
		} else {
			edd_set_error( 'username_incorrect', __( 'The username you entered does not exist', 'easy-digital-downloads' ) );
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
 * @param boolean $remember Remember me
 * @return void
*/
function edd_log_user_in( $user_id, $user_login, $user_pass, $remember = false ) {
	if ( $user_id < 1 )
		return;

	wp_set_auth_cookie( $user_id, $remember );
	wp_set_current_user( $user_id, $user_login );
	do_action( 'wp_login', $user_login, get_userdata( $user_id ) );
	do_action( 'edd_log_user_in', $user_id, $user_login, $user_pass );
}


/**
 * Process Register Form
 *
 * @since 2.0
 * @param array $data Data sent from the register form
 * @return void
*/
function edd_process_register_form( $data ) {

	if( is_user_logged_in() ) {
		return;
	}

	if( empty( $_POST['edd_register_submit'] ) ) {
		return;
	}

	do_action( 'edd_pre_process_register_form' );

	if( empty( $data['edd_user_login'] ) ) {
		edd_set_error( 'empty_username', __( 'Invalid username', 'easy-digital-downloads' ) );
	}

	if( username_exists( $data['edd_user_login'] ) ) {
		edd_set_error( 'username_unavailable', __( 'Username already taken', 'easy-digital-downloads' ) );
	}

	if( ! validate_username( $data['edd_user_login'] ) ) {
		edd_set_error( 'username_invalid', __( 'Invalid username', 'easy-digital-downloads' ) );
	}

	if( email_exists( $data['edd_user_email'] ) ) {
		edd_set_error( 'email_unavailable', __( 'Email address already taken', 'easy-digital-downloads' ) );
	}

	if( empty( $data['edd_user_email'] ) || ! is_email( $data['edd_user_email'] ) ) {
		edd_set_error( 'email_invalid', __( 'Invalid email', 'easy-digital-downloads' ) );
	}

	if( ! empty( $data['edd_payment_email'] ) && $data['edd_payment_email'] != $data['edd_user_email'] && ! is_email( $data['edd_payment_email'] ) ) {
		edd_set_error( 'payment_email_invalid', __( 'Invalid payment email', 'easy-digital-downloads' ) );
	}

	if( empty( $_POST['edd_user_pass'] ) ) {
		edd_set_error( 'empty_password', __( 'Please enter a password', 'easy-digital-downloads' ) );
	}

	if( ( ! empty( $_POST['edd_user_pass'] ) && empty( $_POST['edd_user_pass2'] ) ) || ( $_POST['edd_user_pass'] !== $_POST['edd_user_pass2'] ) ) {
		edd_set_error( 'password_mismatch', __( 'Passwords do not match', 'easy-digital-downloads' ) );
	}

	do_action( 'edd_process_register_form' );

	// Check for errors and redirect if none present
	$errors = edd_get_errors();

	if (  empty( $errors ) ) {

		$redirect = apply_filters( 'edd_register_redirect', $data['edd_redirect'] );

		edd_register_and_login_new_user( array(
			'user_login'      => $data['edd_user_login'],
			'user_pass'       => $data['edd_user_pass'],
			'user_email'      => $data['edd_user_email'],
			'user_registered' => date( 'Y-m-d H:i:s' ),
			'role'            => get_option( 'default_role' )
		) );

		wp_redirect( $redirect );
		edd_die();
	}
}
add_action( 'edd_user_register', 'edd_process_register_form' );

/**
 * Print reCAPTCHA on Login and Register Form
 *
 * @since 2.9.17
 * @return string
*/
function edd_login_register_form_fields_before_submit(){
		
		$public_key = edd_get_option( 'edd-recaptcha-public-key' );
		$prefix   = is_ssl() ? "https" : "http";

		$url      = $prefix . '://www.google.com/recaptcha/api.js';
		 ?>

		<div class="efm-fields">

			<?php wp_enqueue_script( 'recaptcha', $url ); ?>

			<div class="g-recaptcha" data-sitekey="<?php echo $public_key; ?>"></div>
			<noscript>
			  <div style="width: 302px; height: 422px;">
				<div style="width: 302px; height: 422px; position: relative;">
				  <div style="width: 302px; height: 422px; position: absolute;">
					<iframe src="https://www.google.com/recaptcha/api/fallback?k=<?php echo $public_key; ?>"
							frameborder="0" scrolling="no"
							style="width: 302px; height:422px; border-style: none;">
					</iframe>
				  </div>
				  <div style="width: 300px; height: 60px; border-style: none;
							  bottom: 12px; left: 25px; margin: 0px; padding: 0px; right: 25px;
							  background: #f9f9f9; border: 1px solid #c1c1c1; border-radius: 3px;">
					<textarea id="g-recaptcha-response" name="g-recaptcha-response"
							  class="g-recaptcha-response"
							  style="width: 250px; height: 40px; border: 1px solid #c1c1c1;
									 margin: 10px 25px; padding: 0px; resize: none;" >
					</textarea>
				  </div>
				</div>
			  </div>
			</noscript>
		</div>
		<?php
}

$is_edd_login_captcha = edd_get_option( 'edd-login-captcha' );
$is_edd_register_captcha = edd_get_option( 'edd-register-captcha' );

if($is_edd_login_captcha){
	add_action('edd_login_fields_before_submit', 'edd_login_register_form_fields_before_submit');	
}
if($is_edd_register_captcha){
	add_action('edd_register_form_fields_before_submit', 'edd_login_register_form_fields_before_submit');	
}




/**
 * Validating reCAPTCHA on Login and Register Form
 *
 * @since 2.9.17
 * @return void
*/
function edd_validate_recaptcha_login_register_form(){
	
	if( empty( $_POST['g-recaptcha-response'] ) ) {
		edd_set_error( 'empty_recaptcha', __( 'Please fill out recaptcha', 'easy-digital-downloads' ) );
	}else{
		$recap_challenge = isset( $_POST[ 'g-recaptcha-response' ] ) ? $_POST[ 'g-recaptcha-response' ] : '';

		
		$private_key = edd_get_option( 'edd-recaptcha-private-key' );

		try {

			$url      = 'https://www.google.com/recaptcha/api/siteverify';

			$data     = array( 'secret' => $private_key, 'response' => $recap_challenge, 'remoteip' => $_SERVER['REMOTE_ADDR'] );

			$options  = array( 'http' => array( 'header' => "Content-type: application/x-www-form-urlencoded\r\n", 'method' => 'POST', 'content' => http_build_query( $data ) ) );

			$context  = stream_context_create( $options );

			$result   = file_get_contents( $url, false, $context );

			if ( json_decode( $result )->success == false ) {
				edd_set_error( 'invalid_recaptcha', __( 'reCAPTCHA validation failed', 'easy-digital-downloads' ) );
			}

		}

		catch ( Exception $e ) {

			edd_set_error( 'invalid_recaptcha', __( 'reCAPTCHA validation failed', 'easy-digital-downloads' ) );

		}
	}
}

add_action('edd_process_register_form', 'edd_validate_recaptcha_login_register_form');
add_action('edd_process_login_form', 'edd_validate_recaptcha_login_register_form');
