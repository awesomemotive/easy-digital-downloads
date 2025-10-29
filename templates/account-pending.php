<?php if ( ! empty( $_GET['edd-verify-request'] ) ) : ?>
	<p class="edd-account-pending edd_success">
		<?php esc_html_e( 'An email with an activation link has been sent.', 'easy-digital-downloads' ); ?>
	</p>
<?php endif; ?>
<p class="edd-account-pending">
	<?php
	/* translators: Context: Message displayed when user account is pending verification */
	esc_html_e( 'Your account is pending verification. Please click the link in your email to activate your account.', 'easy-digital-downloads' );
	?>
</p>

<?php
\EDD\Users\Verification::render();
