<p>
	<strong><?php esc_html_e( 'Account Information', 'easy-digital-downloads' ); ?>:</strong>
	&nbsp;
	<?php
	printf(
		/* translators: 1. The current user's email address; 2. opening anchor tag, do not translate; 3. closing anchor tag, do not translate. */
		__( 'You are currently logged in as %1$s. (%2$slog out%3$s)', 'easy-digital-downloads' ),
		esc_html( $customer['email'] ),
		'<a href="' . esc_url( wp_logout_url( edd_get_current_page_url() ) ) . '">',
		'</a>'
	);
	?>
</p>
