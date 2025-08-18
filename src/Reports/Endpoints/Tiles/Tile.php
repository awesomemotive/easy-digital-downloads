<?php
/**
 * Base class for Simple EDD Reports Tiles
 *
 * This abstract class provides common functionality for building tile data
 * from database queries or Stats API calls, making it easy to create new tile implementations.
 *
 * @package     EDD\Reports\Endpoints\Tiles
 * @copyright   Copyright (c) 2025, Easy Digital Downloads, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.1
 */

namespace EDD\Reports\Endpoints\Tiles;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Reports\Endpoints\Endpoint;
use EDD\Reports;

/**
 * Abstract base class for simple EDD Reports Tile builders.
 *
 * Provides common functionality for tile data building patterns,
 * particularly for displaying single values or statistics.
 *
 * @since 3.5.1
 */
abstract class Tile extends Endpoint {

	/**
	 * The tile context (primary or secondary).
	 *
	 * @since 3.5.1
	 * @var string
	 */
	protected $context = 'primary';

	/**
	 * The comparison label for the tile.
	 *
	 * @since 3.5.1
	 * @var string
	 */
	protected $comparison_label;

	/**
	 * Additional display arguments for the tile.
	 *
	 * @since 3.5.1
	 * @var array
	 */
	protected $display_args = array();

	/**
	 * Registers this tile with the reports system.
	 *
	 * @since 3.5.1
	 */
	protected function register(): void {
		$this->reports->register_endpoint(
			$this->get_id(),
			array(
				'label' => $this->get_label(),
				'views' => array(
					'tile' => array(
						'data_callback' => array( $this, 'get_data_for_callback' ),
						'display_args'  => $this->get_display_args(),
					),
				),
			)
		);
	}

	/**
	 * Gets tile data formatted for the callback system.
	 *
	 * @since 3.5.1
	 * @return mixed
	 */
	public function get_data_for_callback() {
		try {
			return $this->format_output( $this->get_data() );
		} catch ( \Exception $e ) {
			edd_debug_log( $e->getMessage(), true );
			return '';
		}
	}

	/**
	 * Gets the display arguments for the tile.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	protected function get_display_args(): array {
		$args = array_merge(
			array(
				'comparison_label' => $this->get_chart_label(),
			),
			$this->display_args
		);

		// Only add context if it's not primary (default).
		if ( 'secondary' === $this->context ) {
			$args['context'] = $this->context;
		}

		return $args;
	}

	/**
	 * Creates a Stats instance with common filters applied.
	 *
	 * @since 3.5.1
	 * @param array $additional_args Additional arguments to pass to the Stats constructor.
	 * @return \EDD\Stats
	 */
	protected function get_stats( array $additional_args = array() ): \EDD\Stats {
		$default_args = array(
			'range'         => $this->dates['range'],
			'exclude_taxes' => $this->exclude_taxes,
			'currency'      => $this->currency,
		);

		$args = array_merge( $default_args, $additional_args );

		return new \EDD\Stats( $args );
	}

	/**
	 * Escapes and formats the output for display.
	 *
	 * @since 3.5.1
	 * @param mixed $value The value to format.
	 * @return string
	 */
	protected function format_output( $value ): string {
		// If it's already formatted (contains HTML or currency symbols), return as-is.
		if ( is_string( $value ) && ( strpos( $value, '<' ) !== false || strpos( $value, '$' ) !== false ) ) {
			return wp_kses_post( $value );
		}

		// Otherwise, escape it.
		return esc_html( $value );
	}
}
