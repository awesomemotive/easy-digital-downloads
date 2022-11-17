<?php
/**
 * Onboarding Wizard Tools Step.
 *
 * @package     EDD
 * @subpackage  Onboarding
 * @copyright   Copyright (c) 2022, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.2
 */

namespace EDD\Onboarding\Steps\Tools;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Onboarding\Helpers;

/**
 * Initialize step.
 *
 * @since 3.2
 */
function initialize() {
	add_action( 'wp_ajax_edd_onboarding_telemetry_settings', __NAMESPACE__ . '\ajax_save_telemetry_settings' );
}

/**
 * Ajax callback for saving telemetry option.
 *
 * @since 3.2
 */
function ajax_save_telemetry_settings() {
	if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'edd_onboarding_wizard' ) ) {
		exit();
	}

	// @todo - Add correct permissions check!

	if ( isset( $_REQUEST['telemetry_toggle'] ) ) {
		edd_update_option( 'allow_tracking', filter_var( $_REQUEST['telemetry_toggle'], FILTER_VALIDATE_BOOLEAN ) );
	}

	update_option( 'edd_telemetry_email', filter_var( $_REQUEST['telemetry_email'], FILTER_VALIDATE_EMAIL ) );
	update_option( 'edd_tracking_notice', true );

	exit;
}

/**
 * Get step view.
 *
 * @since 3.2
 */
function step_html() {
	$telemetry_email   = get_option( 'edd_telemetry_email' );
	$extension_manager = new \EDD\Admin\Extensions\Extension_Manager();

	$available_plugins = array(
		array(
			'name'        => __( 'Essential eCommerce Features', 'easy-digital-downloads' ),
			'description' => __( 'Get all the essential eCommerce features to sell digital products with WordPress.', 'easy-digital-downloads' ),
			'prechecked'  => true,
			'disabled'    => true,
			'plugin_name' => '',
			'plugin_file' => '',
			'plugin_url'  => '',
			'action'      => '',
		),
		array(
			'name'        => __( 'Optimize Checkout', 'easy-digital-downloads' ),
			'description' => __( 'Improve the checkout experience by auto-creating user accounts for new customers.', 'easy-digital-downloads' ),
			'prechecked'  => true,
			'plugin_name' => 'Auto Register',
			'plugin_file' => 'edd-auto-register/edd-auto-register.php',
			'plugin_url'  => 'https://downloads.wordpress.org/plugin/edd-auto-register.zip',
			'action'      => 'install',
		),
		array(
			'name'        => __( 'Reliable Email Delivery', 'easy-digital-downloads' ),
			'description' => __( 'Email deliverability is one of the most important services for an eCommerce store. Don’t leave your customers in the dark.', 'easy-digital-downloads' ),
			'prechecked'  => true,
			'plugin_name' => 'WP Mail SMTP',
			'plugin_file' => 'wp-mail-smtp/wp_mail_smtp.php',
			'plugin_url'  => 'https://downloads.wordpress.org/plugin/wp-mail-smtp.zip',
			'action'      => 'install',
		),
		array(
			'name'        => __( 'SEO', 'easy-digital-downloads' ),
			'description' => __( 'Get the tools used by millions of smart business owners to analyze and optimize their store’s traffic with SEO.', 'easy-digital-downloads' ),
			'prechecked'  => true,
			'plugin_name' => 'All In One Seo',
			'plugin_file' => 'all-in-one-seo-pack/all_in_one_seo_pack.php',
			'plugin_url'  => 'https://downloads.wordpress.org/plugin/all-in-one-seo-pack.zip',
			'action'      => 'install',
		),
		array(
			'name'        => __( 'Analytics Tools', 'easy-digital-downloads' ),
			'description' => __( 'Get the #1 analytics plugin to see useful information about your visitors right inside your WordPress dashboard.', 'easy-digital-downloads' ),
			'prechecked'  => true,
			'plugin_name' => 'MonsterInsights',
			'plugin_file' => 'google-analytics-for-wordpress/googleanalytics.php',
			'plugin_url'  => 'https://downloads.wordpress.org/plugin/google-analytics-for-wordpress.zip',
			'action'      => 'install',
		),
	);

	// Check the state of the plugins in the current environment.
	foreach ( $available_plugins as $key => $plugin ) {
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
		}
	}

	ob_start();
	?>
	<div class="edd-onboarding__install-plugins">
		<div class="edd-onboarding__plugins-list">
			<?php
			foreach ( $available_plugins as $plugin ) :
				$checked  = '';
				$disabled = '';
				if ( isset( $plugin['prechecked'] ) && $plugin['prechecked'] ) {
					$checked = ' checked';
				}
				if ( isset( $plugin['disabled'] ) && $plugin['disabled'] ) {
					$disabled = ' disabled';
				}
				?>
				<div class="edd-onboarding__plugins-plugin">
					<div class="edd-onboarding__plugins-details">
						<h3><?php echo esc_html( $plugin['name'] ); ?></h3>
						<p><?php echo esc_html( $plugin['description'] ); ?></p>
					</div>
					<div class="edd-onboarding__plugins-control">

					<label class="checkbox-control checkbox-control--checkbox">
						<input class="edd-onboarding__plugin-install" data-plugin-name="<?php echo esc_attr( $plugin['plugin_name'] ); ?>" data-action="<?php echo esc_attr( $plugin['action'] ); ?>" data-plugin-file="<?php echo esc_attr( $plugin['plugin_file'] ); ?>" value="<?php echo esc_attr( $plugin['plugin_url'] ); ?>" type="checkbox"<?php echo $checked.$disabled;?>/>
						<div class="checkbox-control__indicator"></div>
					</label>

					</div>
				</div>
				<?php
			endforeach;
			?>
		</div>

		<div class="edd-onboarding__get-suggestions-section">
			<h3>Get helpful suggestions from Easy Digital Downloads on how to supercharge your EDD powered store, so you can improve conversions and earn more money.</h3>

			<div class="edd-onboarding__get-suggestions-section_label">
				<label for="edd-onboarding__telemetry-email">
					<?php echo esc_html( __( 'Your Email Address:', 'easy-digital-downloads' ) ); ?>
				</label>
			</div>

			<div class="edd-onboarding__get-suggestions-section_input">
				<input type="email" name="edd_telemetry_email" value="<?php echo esc_attr( $telemetry_email ); ?>" id="edd-onboarding__telemetry-email">
			</div>

			<label class="edd-toggle">
				<input type="checkbox" id="edd-onboarding__telemery-toggle" name="telemetry" value="1" checked> <?php echo esc_html( __( 'Help make EDD better for everyone', 'easy-digital-downloads' ) ); ?>
			</label>
			<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php echo esc_attr( __( 'Explanation for the telemetry.', 'easy-digital-downloads' ) ); ?>"></span></input>
		</div>

		<div class="edd-onboarding__selected-plugins">
			<p><?php echo esc_html( __( 'The following plugins will be installed:', 'easy-digital-downloads' ) ); ?> <span class="edd-onboarding__selected-plugins-text"></span></p>
		</div>
	</div>
	<div class="edd-onboarding__install-failed" style="display: none;">
		<h3><?php echo esc_html( __( 'Some features were not able to be installed!', 'easy-digital-downloads' ) ); ?></h3>
		<p>
			<?php
				/* Translators: list of plugins that were not able to be installed or activated */
				wp_kses(
					printf( __( 'Don\'t worry, everything will still work without them! You can install %s later by going to Plugins > Add New.', 'easy-digital-downloads' ), '<span class="edd-onboarding__failed-plugins-text"></span>' ),
					array( 'span' )
				);
			?>
		</p>
		<?php echo esc_html( __( 'Your Email Address:', 'easy-digital-downloads' ) ); ?>
		<a href="#" class="button button-primary edd-onboarding__button-skip-step"><?php echo esc_html( __( 'Continue', 'easy-digital-downloads' ) ); ?></a>
	</div>
	<?php

	return ob_get_clean();
}
