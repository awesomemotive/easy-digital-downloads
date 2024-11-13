<?php
/**
 * This template is shown to customers who use a Stripe Payment method that requires offsite processing/confirmation.
 *
 * @package EDD\Templates
 * @since 3.3.5
 */

?>
<div class="edds-checkout-confirmation">
	<p>
		<?php
		esc_html_e( 'Thank you for your purchase. Your order is being processed and you will receive an email with your download link shortly.', 'easy-digital-downloads' );
		?>
	</p>
</div>
