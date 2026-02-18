<?php
/**
 * Logs class.
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

/**
 * Logs class.
 *
 * @since 3.6.4
 */
class Logs implements SubscriberInterface {

	/**
	 * Get the subscribed events.
	 *
	 * @since 3.6.4
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return array(
			'edd_logs_view_file_downloads' => 'render_file_downloads',
			'edd_logs_view_gateway_errors' => 'render_gateway_errors',
			'edd_logs_view_api_requests'   => 'render_api_requests',
		);
	}

	/**
	 * Setup the logs view.
	 *
	 * @since 3.6.4
	 *
	 * @param string $type Log type.
	 * @return bool True if setup successful, false otherwise.
	 */
	public static function setup( $type = '' ): bool {
		// Bail if cannot view.
		if ( ! current_user_can( 'view_shop_reports' ) ) {
			return false;
		}

		// Includes.
		require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/class-base-logs-list-table.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/class-' . sanitize_key( $type ) . '-logs-list-table.php';

		// Done!
		return true;
	}

	/**
	 * Output the log page.
	 *
	 * @since 3.6.4
	 *
	 * @param \EDD_Base_Log_List_Table $logs_table List table class to work with.
	 * @param string                   $tag        Type of log to view.
	 * @return void
	 */
	public static function render_page( $logs_table, string $tag = '' ): void {
		$tag = sanitize_key( $tag );
		$logs_table->prepare_items();
		?>

		<div class="wrap">
			<?php
			/**
			 * Fires at the top of the logs view.
			 *
			 * @since 3.0
			 */
			do_action( "edd_logs_{$tag}_top" );
			$form_action_url = edd_get_admin_url(
				array(
					'page' => 'edd-tools',
					'tab'  => sanitize_key( $tag ),
				)
			);
			?>

			<form id="edd-logs-filter" method="get" action="<?php echo esc_url( $form_action_url ); ?>">
				<input type="hidden" name="post_type" value="download" />
				<input type="hidden" name="page" value="edd-tools" />
				<input type="hidden" name="tab" value="logs" />
				<input type="hidden" name="view" value="<?php echo esc_attr( $tag ); ?>" />
				<?php
				wp_nonce_field( -1, 'edd_filter', false );
				$logs_table->views();
				$logs_table->advanced_filters();
				?>
			</form>
			<?php
			$logs_table->display();
			?>

			<?php
			/**
			 * Fires at the bottom of the logs view.
			 *
			 * @since 3.0
			 */
			do_action( "edd_logs_{$tag}_bottom" );
			?>

		</div>
		<?php
	}

	/**
	 * Get default log views.
	 *
	 * @since 3.6.4
	 * @return array Log views.
	 */
	public static function get_default_views(): array {
		/**
		 * Filters the default logs views.
		 *
		 * @since 1.4
		 * @since 3.0 Removed sales log.
		 *
		 * @param array $views Logs views. Each key/value pair represents the view slug
		 *                     and label, respectively.
		 */
		return apply_filters(
			'edd_log_views',
			array(
				'file_downloads' => __( 'File Downloads', 'easy-digital-downloads' ),
				'gateway_errors' => __( 'Payment Errors', 'easy-digital-downloads' ),
				'api_requests'   => __( 'API Requests', 'easy-digital-downloads' ),
			)
		);
	}

	/**
	 * Render file downloads log view.
	 *
	 * @since 3.6.4
	 * @return void
	 */
	public function render_file_downloads(): void {
		// Setup or bail.
		if ( ! self::setup( 'file-downloads' ) ) {
			return;
		}

		$logs_table = new \EDD_File_Downloads_Log_Table();

		self::render_page( $logs_table, 'file_downloads' );
	}

	/**
	 * Render gateway errors log view.
	 *
	 * @since 3.6.4
	 * @return void
	 */
	public function render_gateway_errors(): void {
		// Setup or bail.
		if ( ! self::setup( 'gateway-error' ) ) {
			return;
		}

		$logs_table = new \EDD_Gateway_Error_Log_Table();

		self::render_page( $logs_table, 'gateway_errors' );
	}

	/**
	 * Render API requests log view.
	 *
	 * @since 3.6.4
	 * @return void
	 */
	public function render_api_requests(): void {
		// Setup or bail.
		if ( ! self::setup( 'api-requests' ) ) {
			return;
		}

		$logs_table = new \EDD_API_Request_Log_Table();

		self::render_page( $logs_table, 'api_requests' );
	}
}
