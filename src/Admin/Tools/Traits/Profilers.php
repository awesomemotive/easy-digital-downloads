<?php
/**
 * Profilers trait for the Labs class.
 *
 * @package EDD\Admin\Tools\Traits
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Admin\Tools\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Profilers trait.
 *
 * @since 3.6.0
 */
trait Profilers {

	/**
	 * The profilers.
	 *
	 * @var array
	 */
	private static $profilers;

	/**
	 * Handle profiler log actions (download, copy, clear).
	 *
	 * @since 3.6.0
	 * @return void
	 */
	public function handle_profiler_log_action(): void {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		}

		check_admin_referer( 'edd-profiler-log-action' );

		$profiler_id = isset( $_REQUEST['profiler_id'] ) ? sanitize_text_field( $_REQUEST['profiler_id'] ) : '';
		if ( empty( $profiler_id ) ) {
			return;
		}

		// Get profilers and find the matching one.
		$profilers = self::get_profilers();
		if ( empty( $profilers ) || ! isset( $profilers[ $profiler_id ] ) ) {
			return;
		}

		$profiler = $profilers[ $profiler_id ];
		if ( isset( $_REQUEST['edd-download-profiler-log'] ) ) {
			$this->download_profiler_log( $profiler_id );
		} elseif ( isset( $_REQUEST['edd-clear-profiler-log'] ) ) {
			$this->clear_profiler_log( $profiler['class'] );
		}
	}

	/**
	 * Provide profiler logs over the WP Heartbeat API.
	 *
	 * @since 3.6.0
	 * @param array $response  The response to send back to the browser.
	 * @param array $data      The $_POST data sent.
	 * @return array
	 */
	public function heartbeat_received( $response, $data ) {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return $response;
		}

		if ( empty( $data['eddLabsLogs'] ) || empty( $data['eddLabsLogs']['ids'] ) || ! is_array( $data['eddLabsLogs']['ids'] ) ) {
			return $response;
		}

		$requested = array_map( 'sanitize_key', $data['eddLabsLogs']['ids'] );
		$profilers = self::get_profilers();
		$payload   = array();

		foreach ( $requested as $profiler_id ) {
			$class                   = $profilers[ $profiler_id ]['class'];
			$tail                    = $class::get_file_tail();
			$payload[ $profiler_id ] = array(
				'contents'  => $tail['contents'],
				'truncated' => $tail['truncated'],
				'path'      => wp_normalize_path( $class::get_file_path() ),
				'updated'   => edd_date_i18n( 'now', get_option( 'time_format' ) ),
			);
		}

		if ( ! empty( $payload ) ) {
			$response['eddLabsLogs'] = $payload;
		}

		return $response;
	}

	/**
	 * Get the profilers.
	 *
	 * @since 3.6.0
	 * @return array The profilers.
	 */
	public static function get_profilers(): array {
		if ( ! is_null( self::$profilers ) ) {
			return self::$profilers;
		}

		/**
		 * Filter the profilers.
		 *
		 * @since 3.6.0
		 * @param array $profilers The profilers.
		 * @return array The profilers.
		 */
		self::$profilers = apply_filters(
			'edd_profilers',
			array(
				'cart' => array(
					'class' => \EDD\Profiler\Cart::class,
					'name'  => __( 'Cart Profiler', 'easy-digital-downloads' ),
				),
			)
		);

		foreach ( self::$profilers as $profiler ) {
			if ( ! self::is_profiler( $profiler ) ) {
				unset( self::$profilers[ $profiler['id'] ] );
			}
		}

		return self::$profilers;
	}

	/**
	 * Check if the profiler is valid.
	 *
	 * @since 3.6.0
	 * @param string|array $profiler The profiler class or array.
	 * @return bool Whether the profiler is valid.
	 */
	private static function is_profiler( $profiler ): bool {
		if ( is_array( $profiler ) ) {
			if ( empty( $profiler['class'] ) ) {
				return false;
			}

			$profiler = $profiler['class'];
		}

		return is_subclass_of( $profiler, '\\EDD\\Profiler\\Profiler' );
	}

	/**
	 * Render the profiler log section.
	 *
	 * @since 3.6.0
	 * @return void
	 */
	private function render_profilers(): void {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		}

		$profilers = self::get_profilers();
		if ( empty( $profilers ) ) {
			return;
		}

		foreach ( $profilers as $profiler_id => $profiler ) {
			$path        = $profiler['class']::get_file_path();
			$log         = $profiler['class']::get_file_contents();
			$path_output = ! empty( $path ) ? wp_normalize_path( $path ) : esc_html__( 'No File', 'easy-digital-downloads' );
			$log_output  = ! empty( $log ) ? wp_normalize_path( $log ) : esc_html__( 'Log is Empty', 'easy-digital-downloads' );
			?>

			<div class="postbox">
				<h3><span><?php echo esc_html( $profiler['name'] ); ?></span></h3>
				<div class="inside">
					<?php
					// Profiler-specific settings toggles.
					$settings = $profiler['class']::get_settings();
					// Show the cookie setting for the first profiler.
					if ( array_key_first( $profilers ) === $profiler_id ) {
						$settings['cookie'] = $profiler['class']::get_cookie_setting();
					}
					if ( ! empty( $settings ) ) {
						foreach ( $settings as $setting ) {
							if ( 'checkbox_toggle' !== $setting['type'] ) {
								continue;
							}
							$this->output_toggle_field( $setting );
						}
					}
					$log_classes = array( 'edd-profiler-log', 'edd-requires', "edd-requires__{$profiler_id}_profiler" );
					if ( ! edd_get_option( "{$profiler_id}_profiler", false ) ) {
						$log_classes[] = 'edd-hidden';
					}
					?>
					<div class="<?php echo esc_attr( implode( ' ', $log_classes ) ); ?>" data-requires="<?php echo esc_attr( "{$profiler_id}_profiler" ); ?>">
						<form id="edd-profiler-log-<?php echo esc_attr( $profiler_id ); ?>" method="post">
							<h4 class="edd-profiler-log__title">
							<?php esc_html_e( 'Profiler Log', 'easy-digital-downloads' ); ?>
							</h4>
							<textarea
								readonly="readonly"
								class="edd-tools-textarea"
								rows="15"
								name="edd-profiler-log-contents"><?php echo esc_textarea( $log_output ); ?></textarea>
							<div class="edd-profiler-log__timestamp edd-hidden">
								<?php esc_html_e( 'Last updated:', 'easy-digital-downloads' ); ?>
								<span class="edd-profiler-log__time" data-profiler="<?php echo esc_attr( $profiler_id ); ?>">—</span>
							</div>
							<div class="edd-log__actions edd-log__actions--profiler">
								<input type="hidden" name="edd-action" value="submit_profiler_log"/>
								<input type="hidden" name="profiler_id" value="<?php echo esc_attr( $profiler_id ); ?>"/>
								<?php
								if ( ! empty( $log ) ) {
									submit_button(
										__( 'Download Profiler Log File', 'easy-digital-downloads' ),
										'primary',
										'edd-download-profiler-log',
										false
									);
									submit_button(
										__( 'Copy to Clipboard', 'easy-digital-downloads' ),
										'secondary edd-inline-button',
										'edd-copy-profiler-log',
										false,
										array(
											'onclick' => "this.form['edd-profiler-log-contents'].focus();this.form['edd-profiler-log-contents'].select();document.execCommand('copy');return false;",
										)
									);
									submit_button(
										__( 'Clear Log', 'easy-digital-downloads' ),
										'secondary edd-inline-button',
										'edd-clear-profiler-log',
										false
									);
								}

								?>
							</div>
							<?php wp_nonce_field( 'edd-profiler-log-action' ); ?>
						</form>

						<p>
							<?php esc_html_e( 'Log file', 'easy-digital-downloads' ); ?>:
							<code><?php echo esc_html( $path_output ); ?></code>
						</p>
					</div>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Download the profiler log file.
	 *
	 * @since 3.6.0
	 * @param string $profiler_id The profiler ID.
	 * @return void
	 */
	private function download_profiler_log( $profiler_id = '' ): void {
		nocache_headers();

		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment; filename="edd-profiler-log-' . sanitize_file_name( $profiler_id ) . '.txt"' );

		echo wp_strip_all_tags( $_REQUEST['edd-profiler-log-contents'] );
		exit;
	}

	/**
	 * Clear the profiler log.
	 *
	 * @since 3.6.0
	 * @param string $profiler The profiler class.
	 * @return void
	 */
	private function clear_profiler_log( $profiler ): void {
		if ( ! self::is_profiler( $profiler ) ) {
			return;
		}

		$profiler::clear_log_file();

		edd_redirect(
			edd_get_admin_url(
				array(
					'page' => 'edd-tools',
					'tab'  => 'labs',
				)
			)
		);
	}
}
