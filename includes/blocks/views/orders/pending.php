<?php
if ( ! empty( $_GET['edd-verify-request'] ) ) :
	?>
	<p class="edd-account-pending edd_success">
		<?php esc_html_e( 'An email with an activation link has been sent.', 'easy-digital-downloads' ); ?>
	</p>
<?php endif; ?>
<p class="edd-account-pending">
	<?php
	printf(
		wp_kses_post(
			/* translators: 1. Opening anchor tag. 2. Closing anchor tag. */
			__( 'Your account is pending verification. Please click the link in your email to activate your account. No email? %1$sSend a new activation code.%2$s', 'easy-digital-downloads' )
		),
		'<a href="' . esc_url( edd_get_user_verification_request_url() ) . '">',
		'</a>'
	);
	?>
</p>
