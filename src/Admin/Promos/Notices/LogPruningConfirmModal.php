<?php
/**
 * Confirmation modal for log pruning.
 *
 * @package EDD\Admin\Promos\Notices
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.6.4
 */

namespace EDD\Admin\Promos\Notices;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Log Pruning Confirmation Modal.
 *
 * @since 3.6.4
 */
class LogPruningConfirmModal extends Notice {

	/**
	 * Action hook for displaying the notice.
	 */
	const DISPLAY_HOOK = 'admin_print_footer_scripts-download_page_edd-tools';

	/**
	 * Type of promotional notice.
	 */
	const TYPE = 'overlay';

	/**
	 * Capability required to dismiss the notice.
	 */
	const CAPABILITY = 'manage_shop_settings';

	/**
	 * Duration (in seconds) that the notice is dismissed for.
	 * `0` means it's dismissed permanently.
	 *
	 * @since 3.6.4
	 *
	 * @return int
	 */
	public static function dismiss_duration() {
		return 1;
	}

	/**
	 * Gets the border color for the modal.
	 * Returns warning yellow color.
	 *
	 * @since 3.6.4
	 * @return string
	 */
	public function get_border_color() {
		return '#dba617';
	}

	/**
	 * Gets the notice content for AJAX requests.
	 *
	 * @since 3.6.4
	 *
	 * @return string
	 */
	public function get_ajax_content() {
		if ( 'logpruningconfirmmodal' !== ( isset( $_GET['notice_id'] ) ? sanitize_key( wp_unslash( $_GET['notice_id'] ) ) : '' ) ) {
			return '';
		}

		// Verify user has permission to manage settings.
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return '';
		}

		ob_start();

		// Get parameters from EDD's supported data attributes.
		$log_type = isset( $_GET['product_id'] ) ? sanitize_key( wp_unslash( $_GET['product_id'] ) ) : '';
		$days     = filter_input( INPUT_GET, 'value', FILTER_VALIDATE_INT );

		$log_types      = \EDD\Logs\Registry::get_types();
		$log_type_label = '';
		$count          = 0;

		if ( ! empty( $log_type ) && isset( $log_types[ $log_type ] ) && $days > 0 ) {
			$type_config    = $log_types[ $log_type ];
			$log_type_label = $type_config['label'];
			$count          = \EDD\Cron\Components\LogPruning::get_prune_count( $type_config, $days );
		}
		?>
		<style>
			.edd-log-pruning-modal__title {
				text-align: center;
				margin-top: 0;
			}
			.edd-log-pruning-modal__text {
				font-size: 14px;
			}
			.edd-log-pruning-modal__warning {
				font-size: 14px;
				margin-top: 15px;
				text-align: center;
			}
			.edd-log-pruning-warning-icon {
				display: flex;
				align-items: center;
				justify-content: center;
				margin-bottom: 15px;
			}
			.edd-log-pruning-warning-icon .dashicons {
				font-size: 48px;
				width: 48px;
				height: 48px;
				color: #dba617;
			}
		</style>
		<div class="edd-promo-notice__content">
			<div class="edd-log-pruning-warning-icon">
				<span class="dashicons dashicons-warning"></span>
			</div>
			<h2 class="edd-log-pruning-modal__title">
				<?php esc_html_e( 'Confirm Log Pruning', 'easy-digital-downloads' ); ?>
			</h2>
			<p class="edd-log-pruning-modal__warning">
				<strong><?php esc_html_e( 'Warning: This action cannot be undone!', 'easy-digital-downloads' ); ?></strong>
			</p>
			<p class="edd-log-pruning-modal__text" id="edd-prune-logs-count-message">
				<?php
				if ( $count > 0 ) {
					printf(
						/* translators: 1: number of records, 2: log type label, 3: number of days */
						_n(
							'You are about to permanently delete <strong>%1$s %2$s log entry</strong> older than <strong>%3$d day</strong>.',
							'You are about to permanently delete <strong>%1$s %2$s log entries</strong> older than <strong>%3$d days</strong>.',
							$count,
							'easy-digital-downloads'
						),
						number_format_i18n( $count ),
						esc_html( $log_type_label ),
						absint( $days )
					);
				} else {
					printf(
						/* translators: 1: log type label, 2: number of days */
						esc_html__( 'No %1$s log entries found older than %2$d days.', 'easy-digital-downloads' ),
						esc_html( $log_type_label ),
						absint( $days )
					);
				}
				?>
			</p>
			<?php if ( $count > 0 ) : ?>
				<p class="edd-log-pruning-modal__text">
					<?php esc_html_e( 'Are you sure you want to proceed?', 'easy-digital-downloads' ); ?>
				</p>
			<?php endif; ?>
		</div>
		<div class="edd-promo-notice__actions">
			<?php if ( $count > 0 ) : ?>
				<button
					type="button"
					id="edd-confirm-prune-logs"
					class="button button-primary"
					data-log-type="<?php echo esc_attr( $log_type ); ?>"
					data-days="<?php echo esc_attr( $days ); ?>"
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'edd_prune_logs_manual' ) ); ?>"
					data-default-text="<?php esc_attr_e( 'Yes, Delete These Logs', 'easy-digital-downloads' ); ?>"
					data-updating-text="<?php esc_attr_e( 'Deleting...', 'easy-digital-downloads' ); ?>"
				>
					<?php esc_html_e( 'Yes, Delete These Logs', 'easy-digital-downloads' ); ?>
				</button>
			<?php endif; ?>
			<button class="button button-secondary edd-promo-notice-dismiss">
				<?php echo $count > 0 ? esc_html__( 'Cancel', 'easy-digital-downloads' ) : esc_html__( 'Close', 'easy-digital-downloads' ); ?>
			</button>
		</div>
		<div class="edd-promo-notice__info">
			<p id="edd-prune-logs-result" class="info edd-hidden"></p>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Renders the dismiss button for the notice.
	 * This is intentionally left blank as the dismiss button is rendered in the AJAX content.
	 *
	 * @since 3.6.4
	 * @return void
	 */
	public function dismiss_button() {}

	/**
	 * Displays the notice content.
	 * This is intentionally left blank as the content is rendered in the AJAX content.
	 *
	 * @return void
	 */
	protected function _display() {}

	/**
	 * Determines if the notice should be displayed on render.
	 *
	 * @since 3.6.4
	 * @return bool
	 */
	protected function _should_display(): bool {
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
		$view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : '';

		return 'edd-tools' === $page && 'logs' === $tab && 'settings' === $view;
	}
}
