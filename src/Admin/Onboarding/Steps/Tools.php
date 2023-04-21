<?php
/**
 * Onboarding Wizard Tools Step.
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

class Tools extends Step {

	/**
	 * Get step view.
	 *
	 * @since 3.1.1
	 */
	public function step_html() {
		$can_install_plugins = current_user_can( 'install_plugins' );
		$available_plugins   = $this->get_plugins();

		?>
		<div class="edd-onboarding__install-plugins">
			<div class="edd-onboarding__plugins-list">
				<?php
				foreach ( $available_plugins as $plugin ) :
					$checked  = '';
					$disabled = '';
					$readonly = '';
					$id       = str_replace( ' ', '-', strtolower( $plugin['plugin_name'] ) );
					if ( isset( $plugin['prechecked'] ) && $plugin['prechecked'] ) {
						$checked = ' checked';
					}
					if ( isset( $plugin['disabled'] ) && $plugin['disabled'] ) {
						$disabled = ' disabled';
					}
					if ( isset( $plugin['readonly'] ) && $plugin['readonly'] ) {
						$readonly = ' onClick="return false;"';
					}
					?>
					<div class="edd-onboarding__plugins-plugin">
						<h3><?php echo esc_html( $plugin['name'] ); ?></h3>
						<div class="edd-onboarding__plugins-details">
							<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $plugin['description'] ); ?>
								<div class="edd-onboarding__plugins-control">
									<?php if ( ! $can_install_plugins && ! empty( $plugin['plugin_url'] ) ) : ?>
										<a href="<?php echo esc_url( $plugin['plugin_url'] ); ?>" class="edd-onboarding__plugins-external-link" target="_blank"><span class="dashicons dashicons-external"></span></a>
									<?php else : ?>
										<div class="checkbox-control checkbox-control--checkbox">
											<input id="<?php echo esc_attr( $id ); ?>" class="edd-onboarding__plugin-install" data-plugin-name="<?php echo esc_attr( $plugin['plugin_name'] ); ?>" data-action="<?php echo esc_attr( $plugin['action'] ); ?>" data-plugin-file="<?php echo esc_attr( $plugin['plugin_file'] ); ?>" value="<?php echo esc_attr( $plugin['plugin_zip'] ); ?>" type="checkbox"<?php echo $checked.$disabled.$readonly;?>/>
											<div class="checkbox-control__indicator"></div>
										</div>
									<?php endif; ?>

								</div>
							</label>
							<?php if ( ! empty( $plugin['active'] ) ) : ?>
								<p>
									<em>
										<?php
										/* translators: the plugin name. */
										printf( esc_html__( '%s is already active.', 'easy-digital-downloads' ), esc_html( $plugin['plugin_name'] ) );
										?>
									</em>
								</p>
							<?php elseif ( ! empty( $plugin['has_feature'] ) ) : ?>
								<p>
									<em><?php esc_html_e( 'You already have a solution installed for this feature.', 'easy-digital-downloads' ); ?></em>
								</p>
							<?php endif; ?>
						</div>
					</div>
					<?php
				endforeach;
				?>
			</div>

			<?php $this->telemetry(); ?>

			<div class="edd-onboarding__selected-plugins">
				<p><?php esc_html_e( 'Based on your selection above, the following plugins will be installed:', 'easy-digital-downloads' ); ?> <span class="edd-onboarding__selected-plugins-text"></span></p>
			</div>
		</div>
		<div class="edd-onboarding__install-failed" style="display: none;">
			<h3><?php esc_html_e( 'Some features were not able to be installed!', 'easy-digital-downloads' ); ?></h3>
			<p>
				<?php
					wp_kses(
						/* translators: list of plugins that were not able to be installed or activated */
						printf( __( 'Don\'t worry, everything will still work without them! You can install %s later by going to Plugins > Add New.', 'easy-digital-downloads' ), '<span class="edd-onboarding__failed-plugins-text"></span>' ),
						array( 'span' )
					);
				?>
			</p>
			<button class="button button-primary button-hero edd-onboarding__button-skip-step"><?php esc_html_e( 'Continue', 'easy-digital-downloads' ); ?></button>
		</div>

		<div class="edd-onboarding__install-success-wrapper" style="display: none;">
			<div  class="edd-onboarding__install-success">
				<span class="emoji">🥳</span>
				<span><?php esc_html_e( 'Plugins were successfully installed!', 'easy-digital-downloads' ); ?></span>
			</div>
		</div>
		<?php
	}

	/**
	 * Gets the plugins to install/activate.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	private function get_plugins() {
		$extension_manager = new \EDD\Admin\Extensions\Extension_Manager();
		$available_plugins = array(
			array(
				'name'        => __( 'Essential eCommerce Features', 'easy-digital-downloads' ),
				'description' => __( 'Get all the essential eCommerce features to sell digital products with WordPress.', 'easy-digital-downloads' ),
				'prechecked'  => true,
				'readonly'    => true,
				'disabled'    => true,
				'plugin_name' => __( 'Easy Digital Downloads', 'easy-digital-downloads' ),
				'plugin_file' => '',
				'plugin_zip'  => '',
				'plugin_url'  => '',
				'action'      => '',
			),
			array(
				'name'        => __( 'Optimize Checkout', 'easy-digital-downloads' ),
				'description' => __( 'Improve the checkout experience by auto-creating user accounts for new customers.', 'easy-digital-downloads' ),
				'prechecked'  => true,
				'plugin_name' => 'Auto Register',
				'plugin_file' => 'edd-auto-register/edd-auto-register.php',
				'plugin_zip'  => 'https://downloads.wordpress.org/plugin/edd-auto-register.zip',
				'plugin_url'  => 'https://wordpress.org/plugins/edd-auto-register',
				'action'      => 'install',
			),
			array(
				'name'        => __( 'Reliable Email Delivery', 'easy-digital-downloads' ),
				'description' => __( 'Email deliverability is one of the most important services for an eCommerce store. Don’t leave your customers in the dark.', 'easy-digital-downloads' ),
				'prechecked'  => true,
				'plugin_name' => 'WP Mail SMTP',
				'plugin_file' => 'wp-mail-smtp/wp_mail_smtp.php',
				'plugin_zip'  => 'https://downloads.wordpress.org/plugin/wp-mail-smtp.zip',
				'plugin_url'  => 'https://wordpress.org/plugins/wp-mail-smtp/',
				'action'      => 'install',
				'conflicts'   => array(
					'wp-mail-smtp-pro/wp_mail_smtp.php',
				),
			),
			array(
				'name'        => __( 'Analytics Tools', 'easy-digital-downloads' ),
				'description' => __( 'Get the #1 analytics plugin to see useful information about your visitors right inside your WordPress dashboard.', 'easy-digital-downloads' ),
				'prechecked'  => true,
				'plugin_name' => 'MonsterInsights',
				'plugin_file' => 'google-analytics-for-wordpress/googleanalytics.php',
				'plugin_zip'  => 'https://downloads.wordpress.org/plugin/google-analytics-for-wordpress.zip',
				'plugin_url'  => 'https://wordpress.org/plugins/google-analytics-for-wordpress/',
				'action'      => 'install',
				'conflicts'   => array(
					'google-analytics-premium/googleanalytics-premium.php',
					'google-analytics-dashboard-for-wp/gadwp.php',
					'exactmetrics-premium/exactmetrics-premium.php',
					'wp-analytify/wp-analytify.php',
					'ga-google-analytics/ga-google-analytics.php',
				),
			),
			array(
				'name'        => __( 'SEO', 'easy-digital-downloads' ),
				'description' => __( 'Get the tools used by millions of smart business owners to analyze and optimize their store’s traffic with SEO.', 'easy-digital-downloads' ),
				'prechecked'  => true,
				'plugin_name' => 'All In One SEO Pack',
				'plugin_file' => 'all-in-one-seo-pack/all_in_one_seo_pack.php',
				'plugin_zip'  => 'https://downloads.wordpress.org/plugin/all-in-one-seo-pack.zip',
				'plugin_url'  => 'https://wordpress.org/plugins/all-in-one-seo-pack/',
				'action'      => 'install',
				'conflicts'   => array(
					'all-in-one-seo-pack-pro/all_in_one_seo_pack.php',
					'wordpress-seo/wp-seo.php',
					'wordpress-seo-premium/wp-seo-premium.php',
				),
			),
		);

		// Check the state of the plugins in the current environment.
		foreach ( $available_plugins as $key => $plugin ) {

			// If the plugin has a conflict with another plugin, remove it from the list.
			if ( ! empty( $plugin['conflicts'] ) ) {
				foreach ( $plugin['conflicts'] as $conflicting_slug ) {
					if ( is_plugin_active( $conflicting_slug ) ) {
						$available_plugins[ $key ]['disabled']    = true;
						$available_plugins[ $key ]['prechecked']  = true;
						$available_plugins[ $key ]['readonly']    = true;
						$available_plugins[ $key ]['has_feature'] = true;
						break;
					}
				}
			}

			if ( isset( $plugin['disabled'] ) && $plugin['disabled'] ) {
				continue;
			}

			// If plugin is already installed, set the action to activate.
			if ( $extension_manager->is_plugin_installed( $plugin['plugin_file'] ) ) {
				$available_plugins[ $key ]['action'] = 'activate';
			}

			// If this plugin is activated, disable the checkbox on the front.
			if ( is_plugin_active( $plugin['plugin_file'] ) ) {
				$available_plugins[ $key ]['prechecked'] = true;
				$available_plugins[ $key ]['disabled']   = true;
				$available_plugins[ $key ]['action']     = '';
				$available_plugins[ $key ]['active']     = true;
			}
		}

		return $available_plugins;
	}

	/**
	 * Outputs the telemetry checkbox.
	 *
	 * @since 3.1.1.3
	 * @return void
	 */
	private function telemetry() {
		if ( edd_is_pro() ) {
			return;
		}
		?>
		<div class="edd-onboarding__get-suggestions-section">
			<h3>
				<?php esc_html_e( 'Join the EDD Community', 'easy-digital-downloads' ); ?>
			</h3>

			<label for="edd-onboarding__telemery-toggle" class="edd-onboarding__get-suggestions-section_label">
				<?php esc_html_e( 'Help us provide a better experience and faster fixes by sharing some anonymous data about how you use Easy Digital Downloads.', 'easy-digital-downloads' ); ?>
			</label>
			<div class="edd-toggle">
				<input type="checkbox" id="edd-onboarding__telemery-toggle" class="edd-onboarding__get-suggestions-section_input" name="telemetry" value="1" checked>
			</div>
		</div>
		<?php
	}
}
