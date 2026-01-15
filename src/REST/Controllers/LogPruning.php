<?php
/**
 * Log Pruning REST Controller
 *
 * Handles log pruning REST API requests.
 *
 * @package     EDD\REST\Controllers
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.4
 */

namespace EDD\REST\Controllers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * LogPruning Controller class
 *
 * @since 3.6.4
 */
class LogPruning {

	/**
	 * Prune logs of a specific type.
	 *
	 * @since 3.6.4
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function prune( $request ) {
		$log_type = $request->get_param( 'log_type' );
		$days     = $request->get_param( 'days' );

		$log_types = \EDD\Logs\Registry::get_types();

		// Check if this is a registered type.
		if ( isset( $log_types[ $log_type ] ) ) {
			$type_config = $log_types[ $log_type ];
		} elseif ( strpos( $log_type, 'unregistered_' ) === 0 ) {
			// Build config for unregistered type (requires 'unregistered_' prefix).
			$type_config = \EDD\Logs\Registry::get_unregistered_type_config( $log_type );

			if ( null === $type_config ) {
				return new \WP_Error(
					'invalid_log_type',
					__( 'Invalid log type.', 'easy-digital-downloads' ),
					array( 'status' => 400 )
				);
			}
		} else {
			return new \WP_Error(
				'invalid_log_type',
				__( 'Invalid log type.', 'easy-digital-downloads' ),
				array( 'status' => 400 )
			);
		}

		// Check if prunable.
		if ( empty( $type_config['prunable'] ) ) {
			return new \WP_Error(
				'not_prunable',
				__( 'This log type cannot be pruned.', 'easy-digital-downloads' ),
				array( 'status' => 400 )
			);
		}

		// Get batch size from settings.
		$settings   = edd_get_option( 'edd_log_pruning_settings', array() );
		$batch_size = isset( $settings['batch_size'] ) ? absint( $settings['batch_size'] ) : 250;

		// Process one batch.
		$deleted_count = \EDD\Cron\Components\LogPruning::prune_log_type( $log_type, $type_config, $days, $batch_size );

		return new \WP_REST_Response(
			array(
				'success' => true,
				'count'   => $deleted_count,
				'message' => sprintf(
					/* translators: %d: number of logs deleted */
					_n(
						'%d log entry deleted.',
						'%d log entries deleted.',
						$deleted_count,
						'easy-digital-downloads'
					),
					$deleted_count
				),
			),
			200
		);
	}
}
