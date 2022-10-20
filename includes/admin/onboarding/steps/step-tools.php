<?php
namespace EDD\Onboarding\Steps\Tools;

use EDD\Onboarding\Helpers;

function initialize() {
	add_action( 'wp_ajax_edd_onboarding_telemetry_settings', __NAMESPACE__ . '\ajax_save_telemetry_settings' );
}

function ajax_save_telemetry_settings() {
	// @todo - PERMISSIONS AND NONCE!
	if ( isset( $_REQUEST['telemetry_toggle'] ) ) {
		edd_update_option( 'allow_tracking', filter_var( $_REQUEST['telemetry_toggle'], FILTER_VALIDATE_BOOLEAN ) );
	}

	exit;
}

function step_html() {
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
			'name'        => __( 'Conversion Tools', 'easy-digital-downloads' ),
			'description' => __( 'Get the #1 conversion optimization plugin to convert your website traffic into subscribers, leads, and sales.', 'easy-digital-downloads' ),
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

		// Is plugin already installed?
		if ( $extension_manager->is_plugin_installed( $plugin['plugin_file'] ) ) {
			$available_plugins[ $key ]['action'] = 'activate';
		}

		// Is plugin already activated?
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
				<label for="get-suggestion-email">
					Your Email Address:
				</label>
			</div>

			<div class="edd-onboarding__get-suggestions-section_input">
				<input type="email" id="edd-onboarding__telemery-email">
			</div>

			<label class="edd-toggle"><input type="checkbox" id="edd-onboarding__telemery-toggle" name="telemetry" value="1" checked> Help make EDD better for everyone <span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="Explanation what we are doing here."></span></input></label>
		</div>

		<div class="edd-onboarding__selected-plugins">
			<p>The following plugins will be installed: <span class="edd-onboarding__selected-plugins-text"></span></p>
		</div>
	</div>
	<div class="edd-onboarding__install-failed" style="display: none;">
		<h3>Some features were not able to be installed!</h3>
		<p>Don't worry, everything will still work without them! You can install "<span class="edd-onboarding__failed-plugins-text"></span>" later by going to the Plugins > Add New section of your admin</p>

		<a href="#" class="button button-primary edd-onboarding__button-skip-step"><?php echo esc_html( __( 'Continue', 'easy-digital-downloads' ) ); ?></a>
	</div>
	<?php

	return ob_get_clean();
}
