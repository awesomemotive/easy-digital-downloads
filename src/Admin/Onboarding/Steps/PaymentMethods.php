<?php
/**
 * Onboarding Wizard Payment Methods Step.
 *
 * @package     EDD
 * @subpackage  Onboarding
 * @copyright   Copyright (c) 2022, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.1.1
 */

namespace EDD\Admin\Onboarding\Steps;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class PaymentMethods extends Step {

	/**
	 * Get step view.
	 *
	 * @since 3.1.1
	 */
	public function step_html() {
		?>
		<div class="edd-onboarding__stripe-content-holder">
			<div class="edd-onboarding__stripe-content-logo">
				<img src="<?php echo esc_url( EDD_PLUGIN_URL . 'assets/images/onboarding/stripe-logo.svg' ); ?>" alt="">

				<span><?php echo esc_html_e( 'The world’s most powerful and easy to use payment gateway.', 'easy-digital-downloads' ); ?></span>
			</div>

			<strong class="edd-onboarding__stripe-features-title"><?php echo esc_html_e( 'Stripe Features we can add:', 'easy-digital-downloads' ); ?></strong>

			<ol class="edd-onboarding__stripe-features-listing">
				<li><?php echo esc_html_e( 'Secure checkout', 'easy-digital-downloads' ); ?></li>
				<li><?php echo esc_html_e( 'Accept all major credit cards', 'easy-digital-downloads' ); ?></li>
				<li><?php echo esc_html_e( 'Supports subscriptions', 'easy-digital-downloads' ); ?></li>
				<li><?php echo esc_html_e( 'Fraud prevention tools', 'easy-digital-downloads' ); ?></li>
				<li><?php echo esc_html_e( 'Apple Pay & Google Pay', 'easy-digital-downloads' ); ?></li>
				<li><?php echo esc_html_e( 'And more…', 'easy-digital-downloads' ); ?></li>
			</ol>

			<div class="edd-onboarding__button-stripe">
				<?php echo edds_stripe_connect_setting_field(); ?>
			</div>

			<div class="edd-onboarding__stripe-additional-text">
				<span><?php echo esc_html_e( 'Start accepting payments with Stripe by connecting your account. Stripe Connect helps ensure easier setup and improved security.', 'easy-digital-downloads' ); ?></span>
			</div>
		</div>
		<?php
	}
}
