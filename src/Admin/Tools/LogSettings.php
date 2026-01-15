<?php
/**
 * Log Settings class.
 *
 * @package     EDD\Admin\Tools
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.4
 */

namespace EDD\Admin\Tools;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;
use EDD\Admin\Menu\SubNav;
use EDD\Admin\Tools\Logs\LogTypesListTable;
use EDD\Admin\Tools\Logs\LogStorageCalculator;
use EDD\Cron\Events\SingleEvent;

/**
 * Log Settings tab.
 *
 * @since 3.6.4
 */
class LogSettings implements SubscriberInterface {
	use \EDD\Admin\Settings\Traits\AjaxToggle;

	/**
	 * Get the subscribed events.
	 *
	 * @since 3.6.4
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return array(
			'edd_logs_view_settings'                => 'render',
			'edd_logs_before_view'                  => 'render_navigation',
			'edd_toggle_setting_handlers'           => 'register_handler',
			'wp_ajax_edd_update_log_pruning_number' => 'ajax_update_number',
			'edd_flyout_docs_link'                  => 'filter_flyout_docs_link',
		);
	}

	/**
	 * Filter the flyout docs link when on the log settings page.
	 *
	 * @since 3.6.4
	 *
	 * @param string $url The default docs URL.
	 * @return string The filtered URL.
	 */
	public function filter_flyout_docs_link( string $url ): string {
		if ( ! $this->is_log_settings_page() ) {
			return $url;
		}

		return 'https://easydigitaldownloads.com/docs/reducing-database-size-with-log-retention-settings/';
	}

	/**
	 * Check if we're on the log settings page.
	 *
	 * @since 3.6.4
	 * @return bool
	 */
	private function is_log_settings_page(): bool {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$screen = get_current_screen();
		if ( empty( $screen ) ) {
			return false;
		}

		// Check if we're on the tools page with the logs tab and settings view.
		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS );
		$tab  = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_SPECIAL_CHARS );
		$view = filter_input( INPUT_GET, 'view', FILTER_SANITIZE_SPECIAL_CHARS );

		return 'edd-tools' === $page && 'logs' === $tab && 'settings' === $view;
	}

	/**
	 * Get the list of settings that this handler allows to be toggled via AJAX.
	 *
	 * @since 3.6.4
	 * @return array
	 */
	public static function get_allowed_ajax_settings(): array {
		$allowed = array( 'log_pruning_enabled' );

		// Add all registered log types.
		$log_types = \EDD\Logs\Registry::get_types();
		foreach ( $log_types as $type_id => $type_config ) {
			if ( ! empty( $type_config['prunable'] ) ) {
				$allowed[] = 'log_pruning_' . $type_id . '_enabled';
			}
		}

		return $allowed;
	}

	/**
	 * Handle the AJAX toggle request for log pruning settings.
	 *
	 * Overrides the trait method to handle nested log type storage.
	 *
	 * @since 3.6.4
	 * @return void
	 */
	public static function ajax_toggle_setting(): void {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Unauthorized', 'easy-digital-downloads' ),
				),
				403
			);
		}

		check_ajax_referer( 'edd-toggle-nonce', 'nonce' );

		$setting = isset( $_POST['setting'] ) ? sanitize_key( wp_unslash( $_POST['setting'] ) ) : '';

		// Check if setting is explicitly allowed or matches the log type pattern.
		$is_allowed = in_array( $setting, static::get_allowed_ajax_settings(), true )
			|| preg_match( '/^log_pruning_.+_enabled$/', $setting );

		if ( empty( $setting ) || ! $is_allowed ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid setting', 'easy-digital-downloads' ),
				),
				400
			);
		}

		$value = filter_input( INPUT_POST, 'value', FILTER_VALIDATE_BOOLEAN );

		// Handle the main enabled toggle.
		if ( 'log_pruning_enabled' === $setting ) {
			if ( $value ) {
				edd_update_option( $setting, true );
			} else {
				edd_delete_option( $setting );
			}
		} elseif ( preg_match( '/^log_pruning_(.+)_enabled$/', $setting, $matches ) ) {
			// Handle per-log-type toggles - stored in nested array.
			$type_id  = $matches[1];
			$settings = edd_get_option( 'edd_log_pruning_settings', array() );

			if ( ! isset( $settings['log_types'] ) ) {
				$settings['log_types'] = array();
			}

			if ( ! isset( $settings['log_types'][ $type_id ] ) ) {
				// Get default days from registry if available.
				$log_types    = \EDD\Logs\Registry::get_types();
				$default_days = isset( $log_types[ $type_id ]['default_days'] ) ? $log_types[ $type_id ]['default_days'] : 90;

				$settings['log_types'][ $type_id ] = array(
					'enabled' => false,
					'days'    => $default_days,
				);
			}

			$settings['log_types'][ $type_id ]['enabled'] = $value;
			edd_update_option( 'edd_log_pruning_settings', $settings );
		}

		wp_send_json_success(
			array(
				'setting' => $setting,
				'value'   => $value,
			)
		);
	}

	/**
	 * Handle AJAX request to update number settings (batch_size and days).
	 *
	 * @since 3.6.4
	 * @return void
	 */
	public function ajax_update_number(): void {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Unauthorized', 'easy-digital-downloads' ),
				),
				403
			);
		}

		check_ajax_referer( 'edd-log-pruning-number-nonce', 'nonce' );

		$setting = isset( $_POST['setting'] ) ? sanitize_key( wp_unslash( $_POST['setting'] ) ) : '';
		$value   = isset( $_POST['value'] ) ? absint( $_POST['value'] ) : 0;

		if ( empty( $setting ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid setting', 'easy-digital-downloads' ),
				),
				400
			);
		}

		$settings = edd_get_option( 'edd_log_pruning_settings', array() );

		// Handle batch_size setting.
		if ( 'batch_size' === $setting ) {
			// Ensure batch_size is within valid range.
			$value = max( 50, min( 1000, $value ) );

			$settings['batch_size'] = $value;
			edd_update_option( 'edd_log_pruning_settings', $settings );

			wp_send_json_success(
				array(
					'setting' => $setting,
					'value'   => $value,
				)
			);
		}

		// Handle log type days settings (format: {type_id}_days).
		if ( preg_match( '/^(.+)_days$/', $setting, $matches ) ) {
			$type_id = $matches[1];

			// Ensure days is within valid range.
			$value = max( 1, min( 3650, $value ) );

			if ( ! isset( $settings['log_types'] ) ) {
				$settings['log_types'] = array();
			}

			if ( ! isset( $settings['log_types'][ $type_id ] ) ) {
				$settings['log_types'][ $type_id ] = array(
					'enabled' => false,
					'days'    => $value,
				);
			} else {
				$settings['log_types'][ $type_id ]['days'] = $value;
			}

			edd_update_option( 'edd_log_pruning_settings', $settings );

			wp_send_json_success(
				array(
					'setting' => $setting,
					'value'   => $value,
				)
			);
		}

		wp_send_json_error(
			array(
				'message' => __( 'Invalid setting', 'easy-digital-downloads' ),
			),
			400
		);
	}

	/**
	 * Render the secondary navigation for logs views.
	 *
	 * @since 3.6.4
	 *
	 * @param string $active_view The currently active view.
	 * @return void
	 */
	public function render_navigation( string $active_view = 'file_downloads' ): void {
		$this->render_sub_nav( $active_view );
	}

	/**
	 * Render the sub-navigation with correct styling.
	 *
	 * Uses the edd-sub-nav pattern to match the Settings page secondary navigation.
	 *
	 * @since 3.6.4
	 *
	 * @param string $active_view The currently active view.
	 * @return void
	 */
	private function render_sub_nav( string $active_view ): void {
		// Build tabs array.
		$tabs = Logs::get_default_views();

		// Add settings tab if user has capability.
		if ( current_user_can( 'manage_shop_settings' ) ) {
			$tabs['settings'] = __( 'Settings', 'easy-digital-downloads' );
		}

		$subnav = new SubNav(
			array(
				'tabs'          => $tabs,
				'current'       => $active_view,
				'url_args'      => array(
					'page' => 'edd-tools',
					'tab'  => 'logs',
				),
				'url_key'       => 'view',
				'wrapper_style' => 'margin-top: -4px;',
			)
		);
		$subnav->render();
	}

	/**
	 * Render the log pruning settings page.
	 *
	 * @since 3.6.4
	 * @return void
	 */
	public function render(): void {
		// Check capability.
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		}

		// Get current settings.
		$settings  = edd_get_option( 'edd_log_pruning_settings', array() );
		$enabled   = edd_get_option( 'log_pruning_enabled', false );
		$log_types = \EDD\Logs\Registry::get_types();

		// Enqueue inline JavaScript for AJAX handling.
		$this->enqueue_scripts();

		?>
		<style>.edd-log-days-input { width: 80px; }</style>
		<div class="postbox">
			<h3><span><?php esc_html_e( 'Log Retention Settings', 'easy-digital-downloads' ); ?></span></h3>
			<div class="inside">
				<p class="description">
					<?php esc_html_e( 'Configure automatic log retention management to keep your database clean. Logs older than the specified number of days will be automatically deleted.', 'easy-digital-downloads' ); ?>
				</p>

				<table class="form-table">
						<tr>
							<th scope="row">
								<?php esc_html_e( 'Enable Automatic Pruning', 'easy-digital-downloads' ); ?>
								<?php
								$tooltip = new \EDD\HTML\Tooltip(
									array(
										'content' => __( 'Pruning runs at a random time each day to avoid server resource issues during peak times.', 'easy-digital-downloads' ),
									)
								);
								$tooltip->output();
								?>
							</th>
							<td>
								<?php
								$toggle = new \EDD\HTML\CheckboxToggle(
									array(
										'name'    => 'log_pruning_enabled',
										'current' => $enabled,
										'label'   => __( 'Enable automatic daily pruning', 'easy-digital-downloads' ),
										'data'    => array(
											'setting'         => 'log_pruning_enabled',
											'edd-requirement' => 'log_pruning_enabled',
											'nonce'           => wp_create_nonce( 'edd-toggle-nonce' ),
										),
									)
								);
								$toggle->output();
								?>
								<p class="description">
									<?php esc_html_e( 'Pruning will run once daily.', 'easy-digital-downloads' ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="edd_log_pruning_batch_size">
									<?php esc_html_e( 'Batch Size', 'easy-digital-downloads' ); ?>
								</label>
								<?php
								$tooltip = new \EDD\HTML\Tooltip(
									array(
										'content' => __( 'Maximum number of log entries to delete per run. Lower values reduce server load but may take longer to fully prune large log tables.', 'easy-digital-downloads' ),
									)
								);
								$tooltip->output();
								?>
							</th>
							<td>
								<?php
								$batch_size = new \EDD\HTML\Number(
									array(
										'name'  => 'edd_log_pruning[batch_size]',
										'id'    => 'edd_log_pruning_batch_size',
										'value' => isset( $settings['batch_size'] ) ? absint( $settings['batch_size'] ) : 250,
										'min'   => 50,
										'max'   => 1000,
										'step'  => 50,
										'class' => 'small-text edd-log-pruning-number',
										'desc'  => __( 'Default: 250', 'easy-digital-downloads' ),
										'data'  => array(
											'setting' => 'batch_size',
											'nonce'   => wp_create_nonce( 'edd-log-pruning-number-nonce' ),
										),
									)
								);
								$batch_size->output();
								?>
							</td>
						</tr>
					</table>

					<h3><?php esc_html_e( 'Registered Log Types', 'easy-digital-downloads' ); ?></h3>
					<p class="description">
						<?php esc_html_e( 'Configure retention settings for each registered log type. You can also manually prune logs immediately using the "Prune Now" button.', 'easy-digital-downloads' ); ?>
					</p>

					<?php
					// Prepare data for the registered log types table.
					$registered_items = $this->prepare_log_types_data( $log_types, $settings, $enabled );

					$registered_table = new LogTypesListTable(
						array(
							'log_types'       => $registered_items,
							'settings'        => $settings,
							'pruning_enabled' => $enabled,
							'is_additional'   => false,
						)
					);
					$registered_table->prepare_items();
					$registered_table->display();
					?>

					<?php
					// Get additional (unregistered) log types from the database.
					$additional_types = \EDD\Logs\Registry::get_additional_log_types( true, false );
					if ( ! empty( $additional_types ) ) :
						?>
						<h3 style="margin-top: 30px;"><?php esc_html_e( 'Additional Log Types', 'easy-digital-downloads' ); ?></h3>
						<p class="description">
							<?php esc_html_e( 'These log types were found in the database but are not officially registered. You can still manage their retention settings.', 'easy-digital-downloads' ); ?>
							<strong><?php esc_html_e( 'Warning:', 'easy-digital-downloads' ); ?></strong>
							<?php esc_html_e( 'Since these are unregistered, they may be created by extensions or custom code. Pruning them could have unintended consequences if that code expects the logs to exist.', 'easy-digital-downloads' ); ?>
						</p>

						<?php
						// Prepare data for the additional log types table.
						$additional_items = $this->prepare_additional_log_types_data( $additional_types, $settings, $enabled );

						$additional_table = new LogTypesListTable(
							array(
								'log_types'       => $additional_items,
								'settings'        => $settings,
								'pruning_enabled' => $enabled,
								'is_additional'   => true,
							)
						);
						$additional_table->prepare_items();
						$additional_table->display();
						?>
					<?php endif; ?>

			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue JavaScript for log pruning.
	 *
	 * @since 3.6.4
	 * @return void
	 */
	private function enqueue_scripts(): void {
		$script_handle = 'edd-admin-tools-log-settings';
		$script_src    = edd_get_assets_url( 'js/admin' ) . 'tools-log-settings.js';

		wp_register_script(
			$script_handle,
			$script_src,
			array( 'wp-api-fetch' ),
			edd_admin_get_script_version(),
			true
		);

		wp_localize_script(
			$script_handle,
			'eddLogSettings',
			array(
				'i18n' => array(
					'deleting'      => __( 'Deleting logs...', 'easy-digital-downloads' ),
					'deletedSoFar'  => __( 'deleted so far.', 'easy-digital-downloads' ),
					'oneDeleted'    => __( '1 log entry deleted.', 'easy-digital-downloads' ),
					'manyDeleted'   => __( '%d log entries deleted.', 'easy-digital-downloads' ),
					'noLogsFound'   => __( 'No logs found to delete.', 'easy-digital-downloads' ),
					'errorOccurred' => __( 'An error occurred.', 'easy-digital-downloads' ),
					'errorTryAgain' => __( 'An error occurred. Please try again.', 'easy-digital-downloads' ),
				),
			)
		);

		wp_enqueue_script( $script_handle );

		// Inline styles for the number inputs.
		?>
		<style>
			.edd-log-pruning-number.updating {
				opacity: 0.6;
			}
			.edd-log-pruning-number.edd-saved {
				border-color: #46b450;
				box-shadow: 0 0 2px rgba(70, 180, 80, 0.8);
			}
		</style>
		<?php
	}

	/**
	 * Get the record count for a log type.
	 *
	 * @since 3.6.4
	 *
	 * @param array $type_config Log type configuration.
	 * @return int Record count.
	 */
	private function get_log_type_count( array $type_config ): int {
		if ( empty( $type_config['query_class'] ) ) {
			return 0;
		}

		$query_class = $type_config['query_class'];

		// Ensure the query class exists.
		if ( ! class_exists( $query_class ) ) {
			return 0;
		}

		try {
			// Build query arguments.
			$query_args = array(
				'count' => true,
			);

			// Add type-specific query arguments (e.g., type => 'gateway_error' for edd_logs).
			if ( ! empty( $type_config['query_args'] ) && is_array( $type_config['query_args'] ) ) {
				$query_args = array_merge( $query_args, $type_config['query_args'] );
			}

			$query = new $query_class( $query_args );
			return absint( $query->found_items );
		} catch ( \Exception $e ) {
			return 0;
		}
	}

	/**
	 * Prepare data for the registered log types table.
	 *
	 * @since 3.6.4
	 *
	 * @param array $log_types Registered log types from the registry.
	 * @param array $settings  The pruning settings array.
	 * @param bool  $enabled   Whether pruning is enabled globally.
	 * @return array Prepared items for the list table.
	 */
	private function prepare_log_types_data( array $log_types, array $settings, bool $enabled ): array {
		$items = array();

		foreach ( $log_types as $type_id => $type_config ) {
			$type_enabled = ! empty( $settings['log_types'][ $type_id ]['enabled'] );
			$type_days    = isset( $settings['log_types'][ $type_id ]['days'] ) ? absint( $settings['log_types'][ $type_id ]['days'] ) : $type_config['default_days'];
			$is_prunable  = ! empty( $type_config['prunable'] );
			$has_warning  = ! empty( $type_config['has_warning'] );
			$is_legacy    = ! empty( $type_config['legacy'] );

			$items[] = array(
				'type_id'         => $type_id,
				'label'           => $type_config['label'],
				'description'     => $type_config['description'],
				'record_count'    => $this->get_log_type_count( $type_config ),
				'storage'         => LogStorageCalculator::get_formatted_storage( $type_id, $type_config ),
				'type_enabled'    => $type_enabled,
				'type_days'       => $type_days,
				'is_prunable'     => $is_prunable,
				'has_warning'     => $has_warning,
				'legacy'          => $is_legacy,
				'next_prune_text' => $this->get_next_pruning_text( $enabled, $type_enabled, $type_id ),
			);
		}

		return $items;
	}

	/**
	 * Prepare data for the additional (unregistered) log types table.
	 *
	 * @since 3.6.4
	 *
	 * @param array $additional_types Additional log types from the database.
	 * @param array $settings         The pruning settings array.
	 * @param bool  $enabled          Whether pruning is enabled globally.
	 * @return array Prepared items for the list table.
	 */
	private function prepare_additional_log_types_data( array $additional_types, array $settings, bool $enabled ): array {
		$items = array();

		foreach ( $additional_types as $type_id => $type_config ) {
			$type_enabled = ! empty( $settings['log_types'][ $type_id ]['enabled'] );
			$type_days    = isset( $settings['log_types'][ $type_id ]['days'] ) ? absint( $settings['log_types'][ $type_id ]['days'] ) : 90;

			// Build storage config for additional types (all use edd_logs table with type filter).
			$storage_config = array(
				'table'      => 'edd_logs',
				'query_args' => array( 'type' => $type_id ),
			);

			$items[] = array(
				'type_id'         => $type_id,
				'label'           => $type_config['label'],
				'description'     => $type_config['description'],
				'record_count'    => $type_config['count'],
				'storage'         => LogStorageCalculator::get_formatted_storage( $type_id, $storage_config ),
				'type_enabled'    => $type_enabled,
				'type_days'       => $type_days,
				'is_prunable'     => true,
				'has_warning'     => false,
				'next_prune_text' => $this->get_next_pruning_text( $enabled, $type_enabled, $type_id ),
			);
		}

		return $items;
	}

	/**
	 * Get the next pruning text for a log type.
	 *
	 * @since 3.6.4
	 *
	 * @param bool   $enabled      Whether pruning is enabled globally.
	 * @param bool   $type_enabled Whether pruning is enabled for this log type.
	 * @param string $type_id      The log type ID.
	 * @return string Next pruning text.
	 */
	private function get_next_pruning_text( bool $enabled, bool $type_enabled, string $type_id ): string {
		// If pruning is not enabled globally or for this type.
		if ( ! $enabled || ! $type_enabled ) {
			return __( 'Not scheduled', 'easy-digital-downloads' );
		}

		// Get the next scheduled time for this specific log type's hook.
		$hook           = "edd_prune_logs_{$type_id}";
		$next_scheduled = SingleEvent::next_scheduled( $hook );

		// If no scheduled event.
		if ( false === $next_scheduled ) {
			return __( 'Not scheduled', 'easy-digital-downloads' );
		}

		// Calculate time difference.
		$time_diff = $next_scheduled - time();

		// If it's in the past (shouldn't happen, but just in case).
		if ( $time_diff < 0 ) {
			return __( 'Overdue', 'easy-digital-downloads' );
		}

		// Format the time difference.
		if ( $time_diff < HOUR_IN_SECONDS ) {
			$minutes = ceil( $time_diff / MINUTE_IN_SECONDS );
			return sprintf(
				/* translators: %d: number of minutes */
				_n( 'In %d minute', 'In %d minutes', $minutes, 'easy-digital-downloads' ),
				$minutes
			);
		} elseif ( $time_diff < DAY_IN_SECONDS ) {
			$hours = ceil( $time_diff / HOUR_IN_SECONDS );
			return sprintf(
				/* translators: %d: number of hours */
				_n( 'In %d hour', 'In %d hours', $hours, 'easy-digital-downloads' ),
				$hours
			);
		} else {
			$days = ceil( $time_diff / DAY_IN_SECONDS );
			return sprintf(
				/* translators: %d: number of days */
				_n( 'In %d day', 'In %d days', $days, 'easy-digital-downloads' ),
				$days
			);
		}
	}
}
