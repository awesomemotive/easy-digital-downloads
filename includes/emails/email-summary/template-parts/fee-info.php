<?php
/**
 * Email Summary Fee Info
 */
if ( ! edd_is_gateway_active( 'stripe' ) ) {
	return;
}
$fees_status     = edd_stripe()->application_fee->has_application_fee();
$ignore_statuses = array( 'Not Connected', 'Not Supported' );

if ( false === $fees_status && in_array( edd_stripe()->application_fee->get_status(), $ignore_statuses, true ) ) {
	return;
}

if ( true === $fees_status && 'USD' !== edd_get_currency() ) {
	return;
}

$stats = new \EDD\Stats();

// Get the Stripe revenue so far this year, for the Stripe gateway.
$stripe_revenue = $stats->get_gateway_earnings(
	array(
		'range'   => 'this_year',
		'gateway' => 'stripe',
		'status'  => edd_get_gross_order_statuses(),
		'type'    => array( 'sale' ),
	)
);

$savings = edd_stripe()->application_fee->get_application_fee_amount( $stripe_revenue );

if ( true === $fees_status && $savings < 100 ) {
	return;
}

if ( false === $fees_status && empty( $savings ) ) {
	return;
}

?>
<div class="content-holder" style="padding: 12px 31px; border-radius: 5px;">
	<p class="stats-total-item-icon-wrapper" style="font-weight: 400; font-size: 14px; line-height: 18px; margin: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #809EB0; padding: 0; text-align: center; mso-line-height-rule: exactly; margin-bottom: 1px; height: 32px;">
		<img src="<?php echo esc_url( 'https://plugin.easydigitaldownloads.com/cdn/summaries/icon-net.png' ); ?>" alt="" width="28" height="28">
	</p>
	<p class="bigger pull-down-8" style="margin: 0px; font-weight: 400; color: #4B5563; margin-top: 8px; font-size: 12px; line-height: 18px; text-align: center;">
		<?php
		if ( true === $fees_status ) {
			$link_url = edd_link_helper(
				'https://easydigitaldownloads.com/pricing',
				array(
					'utm_medium'  => 'email-summaries',
					'utm_content' => 'stripe-fees',
				)
			);

			printf(
				/* translators: 1: amount that could have been saved, 2: opening anchor tag, 3: closing anchor tag */
				esc_html__( 'You could have saved %1$s in transaction fees this year by %2$supgrading to an Extended Pass%3$s.', 'easy-digital-downloads' ),
				edd_currency_filter( edd_format_amount( $savings ) ),
				'<a href="' . $link_url . '" style="font-weight: 600; color: #1da867; text-decoration: none;">',
				'</a>'
			);
		} else {
			printf(
				/* translators: 1: opening span tag, 2. the formatted currency amount, 3. the closing span tag */
				esc_html__( 'You have %1$ssaved %2$s in transaction fees%3$s this year with your active license.', 'easy-digital-downloads' ),
				'<span style="font-weight: 600; color: #1da867;">',
				edd_currency_filter( edd_format_amount( $savings ) ),
				'</span>'
			);
		}
		?>
	</p>
</div>

<hr style="border: 0.2px solid #E5E7EB; display: block;">
