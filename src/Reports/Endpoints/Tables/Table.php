<?php
/**
 * Base class for Simple EDD Reports Tables
 *
 * This abstract class provides common functionality for building table data
 * from database queries or Stats API calls, making it easy to create new table implementations.
 *
 * @package     EDD\Reports\Endpoints\Tables
 * @copyright   Copyright (c) 2025, Easy Digital Downloads, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.1
 */

namespace EDD\Reports\Endpoints\Tables;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Reports\Endpoints\Endpoint;
use EDD\Reports;

/**
 * Abstract base class for simple EDD Reports table builders.
 *
 * Provides common functionality for table data building patterns,
 * particularly for displaying single values or statistics.
 *
 * @since 3.5.1
 */
abstract class Table extends Endpoint {

	/**
	 * Additional display arguments for the tile.
	 *
	 * @since 3.5.1
	 * @var array
	 */
	protected $display_args = array();

	/**
	 * Gets the class name for the table.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	abstract protected function get_class_name(): string;

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
					'table' => array(
						'display_args' => $this->get_display_args(),
					),
				),
			)
		);
	}

	/**
	 * Gets table data formatted for the callback system.
	 *
	 * @since 3.5.1
	 * @return mixed
	 */
	public function get_data_for_callback() {
		return $this->get_data();
	}

	/**
	 * Gets the data for the table.
	 * This is intentionally empty because tables register their own classes.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	protected function get_data() {
		return array();
	}

	/**
	 * Gets the display arguments for the tile.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	protected function get_display_args(): array {
		return array_merge(
			array(
				'class_name' => $this->get_class_name(),
			),
			$this->display_args
		);
	}
}
