<?php
/**
 * Admin Options Page
 *
 * @package     EDD
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Conditionally shows a notice on the Tax Rates screen if taxes are disabled, to inform users that while they are adding
 * tax rates, they will not be applied until taxes are enabled.
 *
 * @since 3.0
 */
function edd_tax_settings_display_tax_disabled_notice() {
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	if ( edd_use_taxes() ) {
		return;
	}

	?>
	<div class="notice-wrap" style="clear: both;">
		<div id="edd-tax-disabled-notice">
			<p>
				<?php _e( 'Taxes are currently disabled. Rates listed below will not be applied to purchases until taxes are enabled.', 'easy-digital-downloads' ); ?>
			</p>
		</div>
	</div>
	<?php
}
add_action( 'edd_settings_tab_top_taxes_rates', 'edd_tax_settings_display_tax_disabled_notice', 10 );

/**
 * Display help text at the top of the Licenses tab.
 *
 * @since 3.1.1.4
 * @return void
 */
function edd_license_settings_help_text() {
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	?>
	<div class="edd-licenses__description">
		<p>
			<?php esc_html_e( 'Manage extensions for Easy Digital Downloads which are not included with a pass. Having an active license for your extensions gives you access to updates when they\'re available.', 'easy-digital-downloads' ); ?>
		</p>
		<?php
		$pass_manager = new \EDD\Admin\Pass_Manager();
		if ( ! $pass_manager->highest_license_key ) :
			?>
			<p>
				<?php
				$url = edd_get_admin_url(
					array(
						'page' => 'edd-settings',
						'tab'  => 'general',
					)
				);
				printf(
					wp_kses_post(
						/* translators: 1: opening anchor tag, 2: closing anchor tag */
						__( 'Have a pass? You\'re ready to set up EDD (Pro). %1$sActivate Your Pass%2$s', 'easy-digital-downloads' )
					),
					'<a href="' . esc_url( $url ) . '" class="button button-primary">',
					'</a>'
				);
				?>
			</p>
		<?php endif; ?>
	</div>
	<?php
}
add_action( 'edd_settings_tab_top_licenses_main', 'edd_license_settings_help_text' );
