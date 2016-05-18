<?php if( ! empty( $_GET['edd-verify-request'] ) ) : ?>
<p class="edd-account-pending edd_success">
	<?php _e( 'An email with an activation link has been sent.', 'easy-digital-downloads' ); ?>
</p>
<?php endif; ?>
<p class="edd-account-pending">
	<?php $url = esc_url( edd_get_user_verification_request_url() ); ?>
	<?php printf( __( 'Your account is pending verification. Please click the link in your email to activate your account. No email? <a href="%s">Click here</a> to send a new activation code.', 'easy-digital-downloads' ), $url ); ?>
</p>