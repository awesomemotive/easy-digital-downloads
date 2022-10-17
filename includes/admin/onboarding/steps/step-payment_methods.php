<?php
namespace EDD\Onboarding\Steps\PaymentMethods;

use EDD\Onboarding\Helpers;

function initialize() {
	return;
}

function save_handler() {
	exit;
}

function step_html() {
	ob_start();
	?>
	<div class="edd-onboarding__stripe-content-holder">
		<div class="edd-onboarding__stripe-content-logo">
			<img src="https://upload.wikimedia.org/wikipedia/commons/b/ba/Stripe_Logo%2C_revised_2016.svg" alt="#" title="#">

			<span>The world’s most powerful and easy to use payment gateway. </span>
		</div>

		<strong class="edd-onboarding__stripe-features-title">Stripe Features we can add:</strong>

		<div class="edd-onboarding__stripe-features-divider">
			<ol class="edd-onboarding__stripe-features-listing">
				<li>Secure checkout</li>
				<li>Accept all major credit cards</li>
				<li>Supports subscriptions</li>
			</ol>

			<ol class="edd-onboarding__stripe-features-listing">
				<li>Fraud prevention tools</li>
				<li>Apple Pay & Google Pay</li>
				<li>And more…</li>
			</ol>
		</div>

		<div class="edd-onboarding__button-stripe">
			<?php echo edds_stripe_connect_setting_field(); ?>
		</div>

		<div class="edd-onboarding__stripe-additional-text">
			<span>Start accepting payments with Stripe by connecting your account. Stripe Connect helps ensure easier setup and improved security.</span>
		</div>
	</div>
	<?php

	return ob_get_clean();
}
