<?php
/**
 * Labs tab.
 *
 * @package     EDD\Admin\Tools
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Admin\Tools;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;

/**
 * Labs tab.
 *
 * @since 3.6.0
 */
class Labs implements SubscriberInterface {
	use Traits\Profilers;

	/**
	 * Get the subscribed events.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return array(
			'edd_tools_tab_labs'              => 'render',
			'edd_submit_profiler_log'         => 'handle_profiler_log_action',
			'wp_ajax_edd_toggle_ajax_setting' => 'ajax_toggle_setting',
			'heartbeat_received'              => array( 'heartbeat_received', 10, 2 ),
		);
	}

	/**
	 * Get experimental feature settings displayed on Labs.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	public static function get_feature_settings(): array {
		/**
		 * Filter the feature settings.
		 *
		 * @since 3.6.0
		 * @param array $settings The feature settings.
		 * @return array The feature settings.
		 */
		return apply_filters(
			'edd_labs_feature_settings',
			array(
				'cart_caching' => array(
					'id'    => 'cart_caching',
					'name'  => __( 'Cart Caching (Experimental)', 'easy-digital-downloads' ),
					'check' => __( 'Enable experimental caching layer for cart operations. May improve performance on high-traffic sites.', 'easy-digital-downloads' ),
					'type'  => 'checkbox_toggle',
				),
			)
		);
	}

	/**
	 * Render the labs tab.
	 *
	 * @since 3.6.0
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		}

		$this->enqueue();
		?>

		<div class="edd-settings-content">
			<?php $this->render_feature_settings(); ?>
			<?php $this->render_profilers(); ?>
		</div>

		<?php
	}

	/**
	 * AJAX: Toggle a Labs/profiler setting.
	 *
	 * @since 3.6.0
	 * @return void
	 */
	public function ajax_toggle_setting(): void {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Unauthorized', 'easy-digital-downloads' ),
				),
				403
			);
		}

		check_ajax_referer( 'edd-labs-nonce', 'nonce' );

		$setting = isset( $_POST['setting'] ) ? sanitize_key( wp_unslash( $_POST['setting'] ) ) : '';
		$allowed = $this->get_allowed_setting_keys();
		if ( empty( $setting ) || ! in_array( $setting, $allowed, true ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid setting', 'easy-digital-downloads' ),
				),
				400
			);
		}

		$value = filter_input( INPUT_POST, 'value', FILTER_VALIDATE_BOOLEAN );
		if ( $value ) {
			if ( 'cookie' === $setting ) {
				// Return cookie data to be set client-side instead of server-side.
				$cookie_value = wp_hash( get_home_url() );
				$cookie_data  = array(
					'name'       => 'edd_profiler_enabled',
					'value'      => $cookie_value,
					'expiration' => time() + DAY_IN_SECONDS,
				);
			} else {
				edd_update_option( $setting, true );
			}
		} else {
			// Turning a setting off.
			edd_delete_option( $setting );
			if ( 'cookie' === $setting ) {
				// Return cookie deletion data to be cleared client-side.
				$cookie_data = array(
					'name'       => 'edd_profiler_enabled',
					'value'      => '',
					'expiration' => time() - 3600,
				);
			}

			// If turning off a profiler (e.g. cart_profiler), also disable dependents and clear its log.
			if ( preg_match( '/^(.*)_profiler$/', $setting, $matches ) ) {
				$profiler_id = $matches[1];
				$profilers   = self::get_profilers();
				if ( isset( $profilers[ $profiler_id ] ) ) {
					$profiler_class = $profilers[ $profiler_id ]['class'];
					// Clear log file for this profiler.
					$profiler_class::clear_log_file();
					// Disable dependent settings that start with "{profiler_id}_" but are not the main toggle.
					$settings = $profiler_class::get_settings();
					foreach ( (array) $settings as $profiler_setting ) {
						if ( empty( $profiler_setting['id'] ) ) {
							continue;
						}
						$dep_id = $profiler_setting['id'];
						if ( $dep_id !== $setting && false !== strpos( $dep_id, $profiler_id . '_' ) ) {
							edd_delete_option( $dep_id );
						}
					}
				}
			}
		}

		// Delete the cookie if all profilers are disabled.
		$all_disabled = true;
		foreach ( self::get_profilers() as $profiler ) {
			if ( $profiler['class']::is_enabled() ) {
				$all_disabled = false;
				break;
			}
		}
		if ( $all_disabled ) {
			$cookie_data = array(
				'name'       => 'edd_profiler_enabled',
				'value'      => '',
				'expiration' => time() - 3600,
			);
		}

		$response = array(
			'setting' => $setting,
			'value'   => $value,
		);

		// Include cookie data if applicable.
		if ( ! empty( $cookie_data ) ) {
			$response['cookie'] = $cookie_data;
		}

		wp_send_json_success( $response );
	}

	/**
	 * Enqueue the labs script.
	 *
	 * @since 3.6.0
	 * @return void
	 */
	private function enqueue(): void {
		$script_handle = 'edd-admin-tools-labs';
		$script_src    = EDD_PLUGIN_URL . 'assets/js/edd-admin-tools-labs.js';
		wp_register_script(
			$script_handle,
			$script_src,
			array( 'jquery', 'heartbeat' ),
			edd_admin_get_script_version(),
			true
		);

		// Localize the script with necessary data for AJAX calls.
		wp_localize_script(
			$script_handle,
			'eddSettings',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'edd-labs-nonce' ),
			)
		);

		wp_enqueue_script( $script_handle );
	}

	/**
	 * Render the experimental feature settings block.
	 *
	 * @since 3.6.0
	 * @return void
	 */
	private function render_feature_settings(): void {
		$features = self::get_feature_settings();
		if ( empty( $features ) ) {
			return;
		}
		?>
		<div class="postbox edd-labs__settings">
			<h3><span><?php esc_html_e( 'Experimental Features', 'easy-digital-downloads' ); ?></span></h3>
			<div class="inside">
				<?php
				foreach ( $features as $setting ) {
					$this->output_toggle_field( $setting );
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Output a single toggle field row.
	 *
	 * @since 3.6.0
	 * @param array $setting Setting descriptor (id, name, check, class, data, etc.).
	 * @return void
	 */
	private function output_toggle_field( array $setting ): void {
		if ( 'checkbox_toggle' !== $setting['type'] ) {
			return;
		}
		$group_classes = array( 'edd-form-group' );
		if ( ! empty( $setting['class'] ) ) {
			$group_classes[] = $setting['class'];
		}
		?>
		<div class="<?php echo esc_attr( implode( ' ', $group_classes ) ); ?>">
			<div class="edd-form-group__control">
				<?php
				$args                    = array(
					'label'   => isset( $setting['name'] ) ? $setting['name'] : '',
					'name'    => isset( $setting['id'] ) ? $setting['id'] : '',
					'current' => isset( $setting['current'] ) ? $setting['current'] : edd_get_option( $setting['id'] ),
					'data'    => isset( $setting['data'] ) ? $setting['data'] : array(),
				);
				$args['data']['setting'] = $setting['id'];
				$input                   = new \EDD\HTML\CheckboxToggle( $args );
				$input->output();
				?>
			</div>
			<?php if ( ! empty( $setting['check'] ) ) : ?>
				<p class="edd-form-group__description"><?php echo wp_kses_post( $setting['check'] ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Build allowlist of setting keys permitted to toggle via AJAX.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	private function get_allowed_setting_keys(): array {
		$keys     = array();
		$features = self::get_feature_settings();
		foreach ( $features as $feature ) {
			if ( ! empty( $feature['id'] ) ) {
				$keys[] = $feature['id'];
			}
		}
		$profilers = self::get_profilers();
		foreach ( $profilers as $key => $profiler ) {
			$settings = $profiler['class']::get_settings();
			foreach ( (array) $settings as $setting ) {
				if ( ! empty( $setting['id'] ) ) {
					$keys[] = $setting['id'];
				}
			}
			if ( array_key_first( $profilers ) === $key ) {
				$keys[] = 'cookie';
			}
		}

		return array_unique( $keys );
	}
}
