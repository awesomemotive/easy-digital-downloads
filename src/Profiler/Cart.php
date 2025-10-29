<?php
/**
 * Cart Profiler
 *
 * Tracks cart calculation performance and method call frequency.
 *
 * @package     EDD\Profiler\Cart
 * @copyright   Copyright (c) 2025, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Profiler;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Cart Profiler Class
 *
 * @since 3.6.0
 */
class Cart extends Profiler {

	/**
	 * The ID of the profiler.
	 *
	 * @var string
	 */
	protected static $id = 'cart_profiler';

	/**
	 * Get the settings for the profiler.
	 *
	 * @since 3.6.0
	 * @return array The settings for the profiler.
	 */
	public static function get_settings(): array {
		return array(
			array(
				'id'    => self::get_id(),
				'name'  => __( 'Cart Performance Profiler', 'easy-digital-downloads' ),
				'check' => __( 'Enable performance profiling for cart operations. Data logs to a custom log file (below).', 'easy-digital-downloads' ),
				'type'  => 'checkbox_toggle',
				'data'  => array(
					'edd-requirement' => self::get_id(),
				),
			),
		);
	}

	/**
	 * Check if profiling is enabled.
	 *
	 * @since 3.6.0
	 * @return bool Whether profiling is enabled.
	 */
	public static function is_enabled(): bool {
		return edd_get_option( self::get_id() );
	}

	/**
	 * Methods to track.
	 *
	 * @var array
	 */
	protected $tracked_methods = array(
		'get_contents_details',
		'get_total',
		'get_subtotal',
		'get_tax',
		'get_discounted_amount',
		'get_contents',
	);

	/**
	 * Get the name of the profiler.
	 *
	 * @since 3.6.0
	 * @return string The name of the profiler
	 */
	protected function get_name(): string {
		return 'EDD Cart Profiler';
	}

	/**
	 * Check if caching is enabled.
	 *
	 * @since 3.6.0
	 * @return bool Whether caching is enabled.
	 */
	protected function is_cache_enabled(): bool {
		return edd_get_option( 'cart_caching' );
	}
}
