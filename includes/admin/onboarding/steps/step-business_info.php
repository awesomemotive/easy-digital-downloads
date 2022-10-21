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
				<span><?php echo esc_html( __( '👋 Say hello to the easiest way to...', 'easy-digital-downloads' ) ); ?></span>
				<h1><?php echo esc_html( __( 'Sell Digital Products With WordPress', 'easy-digital-downloads' ) ); ?></h1>
				<p><?php echo esc_html( __( 'From eBooks, to WordPress plugins, to PDF files and more, we make selling digital products a breeze. Easy Digital Downloads is trusted by over 50,000 smart website owners.', 'easy-digital-downloads' ) ); ?></p>
				<a href="" class="edd-onboarding__welcome-screen-get-started"><?php echo esc_html( __( 'GET STARTED', 'easy-digital-downloads' ) ); ?></a>
			</div>
		</div>
	<?php endif; ?>
	<?php

	return ob_get_clean();
}
