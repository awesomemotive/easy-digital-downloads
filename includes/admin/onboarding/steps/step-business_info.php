<?php
/**
 * Onboarding Wizard Business Info Step.
 *
 * @package     EDD
 * @subpackage  Onboarding
 * @copyright   Copyright (c) 2022, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.2
 */

namespace EDD\Onboarding\Steps\BusinessInfo;

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
	$onboarding_started       = get_option( 'edd_onboarding_started', false );
	$onboarding_initial_style = ( ! $onboarding_started ) ? ' style="display:none;"' : '';

	$sections = array(
		'edd_settings_general_main'     => array(
			'business_settings',
			'entity_name',
			'entity_type',
			'business_address',
			'business_address_2',
			'business_city',
			'business_postal_code',
		),
		'edd_settings_general_currency' => array(
			'currency_settings',
			'currency',
			'currency_position',
			'thousands_separator',
			'decimal_separator',
		),
	);
	ob_start();
	?>
	<div class="edd-onboarding__after-welcome-screen"<?php echo $onboarding_initial_style; ?>>
		<form method="post" action="options.php" class="edd-settings-form">
			<?php settings_fields( 'edd_settings' ); ?>
			<table class="form-table" role="presentation">
				<tbody>
					<?php echo Helpers\settings_html( Helpers\extract_settings_fields( $sections ) ); ?>
				</tbody>
			</table>
		</form>
	</div>
	<?php if ( ! $onboarding_started ) : ?>
		<div class="edd-onboarding__welcome-screen">
			<div class="edd-onboarding__welcome-screen-inner">
				<h1>👋 <?php echo esc_html( __( 'Welcome, and thanks for choosing us!', 'easy-digital-downloads' ) ); ?></h1>
				<p><?php echo esc_html( __( 'Easy Digital Downloads setup is fast and easy. Click below, and we\'ll walk you through the quick initial process. And don\'t worry. You can go back and change anything you do – at anytime. Nothing\'s permanent (unless you want it to be). So feel free to explore!', 'easy-digital-downloads' ) ); ?></p>
				<a href="" class="edd-onboarding__welcome-screen-get-started"><?php echo esc_html( __( 'GET STARTED', 'easy-digital-downloads' ) ); ?></a>
				<h2><?php echo esc_html( __( 'Creators ❤️ Easy Digital Downloads', 'easy-digital-downloads' ) ); ?></h2>
				<div class="edd-onboarding__testimonials-wrapper">
					<div class="edd-onboarding__testimonial">
						<p class="edd-onboarding__testimonial-content">The problem with many e-commerce platforms to sell online courses is they aren’t made with only digital goods in mind. <span class="big">EDD doesn’t have that problem, and as a result their platform is perfectly made for selling my online courses.</strong></p>
						<div class="edd-onboarding__testimonial-person">
							<img class="edd-onboarding__testimonial-avatar" src="<?php echo esc_url( EDD_PLUGIN_URL . '/assets/images/onboarding/joe.jpeg' ); ?>" />
							<p class="edd-onboarding__testimonial-info">
								<span class="testimonial-name">Joe Casabona</span>
								<span class="testimonial-company">How I Built It</span>
								<span class="testimonial-stars">
									<span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span>
								</span>
							</p>
						</div>
					</div>
					<div class="edd-onboarding__testimonial">
						<p class="edd-onboarding__testimonial-content">Before EDD's Recurring Payments was made available, we were only able to sell one-time subscriptions to our customers. Since implementing recurring payments, we've been able to offer quarterly and yearly subscriptions and subsequently <span class="big">increase our subscriptions revenue by 200%.</span></p>
						<div class="edd-onboarding__testimonial-person">
							<img class="edd-onboarding__testimonial-avatar" src="<?php echo esc_url( EDD_PLUGIN_URL . '/assets/images/onboarding/nicholas.jpeg' ); ?>" />
							<p class="edd-onboarding__testimonial-info">
								<span class="testimonial-name">Nicolas Martin</span>
								<span class="testimonial-company">Flea Market Insiders</span>
								<span class="testimonial-stars">
									<span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span>
								</span>
							</p>
						</div>
					</div>
					<div class="edd-onboarding__testimonial">
						<p class="edd-onboarding__testimonial-content">If anyone asks me what they should use for downloadable products on their WordPress site, <span class="big">it’s a no-brainer as far as EDD goes.</span></p>
						<div class="edd-onboarding__testimonial-person">
							<img class="edd-onboarding__testimonial-avatar" src="<?php echo esc_url( EDD_PLUGIN_URL . '/assets/images/onboarding/bob.jpeg' ); ?>" />
							<p class="edd-onboarding__testimonial-info">
								<span class="testimonial-name">Bob Dunn</span>
								<span class="testimonial-company">BobWP</span>
								<span class="testimonial-stars">
									<span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span>
								</span>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php

	return ob_get_clean();
}
