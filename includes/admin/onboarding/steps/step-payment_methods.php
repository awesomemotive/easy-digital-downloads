<?php
/**
 * Onboarding Wizard Payment Methods Step.
 *
 * @package     EDD
 * @subpackage  Onboarding
 * @copyright   Copyright (c) 2022, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.2
 */

namespace EDD\Onboarding\Steps\PaymentMethods;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Onboarding\Helpers;

/**
 * Initialize step.
 *
 * @since 3.2
 */
function initialize() {}

/**
 * Get step view.
 *
 * @since 3.2
 */
function step_html() {
	ob_start();
	?>
	<div class="edd-onboarding__stripe-content-holder">
		<div class="edd-onboarding__stripe-content-logo">
			<img src="<?php echo esc_url( EDD_PLUGIN_URL . 'assets/images/onboarding/stripe-logo.svg' ); ?>" alt="">

			<span><?php echo esc_html( __( 'The world’s most powerful and easy to use payment gateway.', 'easy-digital-downloads' ) ); ?></span>
		</div>

		<strong class="edd-onboarding__stripe-features-title"><?php echo esc_html( __( 'Stripe Features we can add:', 'easy-digital-downloads' ) ); ?></strong>

		<div class="edd-onboarding__stripe-features-divider">
			<ol class="edd-onboarding__stripe-features-listing">
				<li><?php echo esc_html( __( 'Secure checkout', 'easy-digital-downloads' ) ); ?></li>
				<li><?php echo esc_html( __( 'Accept all major credit cards', 'easy-digital-downloads' ) ); ?></li>
				<li><?php echo esc_html( __( 'Supports subscriptions', 'easy-digital-downloads' ) ); ?></li>
			</ol>

			<ol class="edd-onboarding__stripe-features-listing">
				<li><?php echo esc_html( __( 'Fraud prevention tools', 'easy-digital-downloads' ) ); ?></li>
				<li><?php echo esc_html( __( 'Apple Pay & Google Pay', 'easy-digital-downloads' ) ); ?></li>
				<li><?php echo esc_html( __( 'And more…', 'easy-digital-downloads' ) ); ?></li>
			</ol>
		</div>

		<div class="edd-onboarding__button-stripe">
			<?php echo edds_stripe_connect_setting_field(); ?>
		</div>

		<div class="edd-onboarding__stripe-additional-text">
			<span><?php echo esc_html( __( 'Start accepting payments with Stripe by connecting your account. Stripe Connect helps ensure easier setup and improved security.', 'easy-digital-downloads' ) ); ?></span>
		</div>
	</div>
	<?php

	return ob_get_clean();
}
