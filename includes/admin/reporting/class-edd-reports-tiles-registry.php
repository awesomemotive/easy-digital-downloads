<?php
/**
 * Reports API Tab Tiles Registry
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Admin\Reports;

use EDD\Utils\Registry;
use EDD\Utils\Exceptions\Exception;

/**
 * Implements a registry for registering reports tiles.
 *
 * @since 3.0
 *
 * @see \EDD\Utils\Registry
 */
class Tiles_Registry extends Registry {

	/**
	 * Constructs the Tiles_Registry instance.
	 *
	 * @since 3.0
	 *
	 * @throws \EDD\Admin\Reports\Exception if a non-empty value is passed to $input.
	 *
	 * @param array|object $input          Unused.
	 * @param int          $flags          Optional. Flags to control the behaviour of the ArrayObject
	 *                                     object. Default 0.
	 * @param string       $iterator_class Optional. The class that will be used for iteration of the
	 *                                     ArrayObjectobject. Default 'ArrayIterator'.
	 */
	public function __construct( $input = array(), $flags = 0, $iterator_class = "ArrayIterator" ) {
		if ( ! empty( $input ) ) {
			throw new Exception( 'Tiles_Registry items must be registered through CRUD methods.' );

			$input = array();
		}

		parent::__construct( $input, $flags, $iterator_class );
	}

	/**
	 * Adds a new reports tile to the registry.
	 *
	 * @since 3.0
	 *
	 * @throws \EDD\Admin\Reports\Exception if the tab does not exist.
	 *
	 * @param string $tab_id      Reports tab ID for the tile.
	 * @param string $tile_id     Reports tile ID.
	 * @param array  $attributes {
	 *     Attributes of the reports tile.
	 *
	 *     @type string   $label            Tile label. Default 'Meta Box'.
	 *     @type string   $context          Tile context. Maps to the corresponding meta box `$context` value.
	 *                                      Accepts 'primary', 'secondary', and 'tertiary'. Default 'primary'.
	 *     @type string   $type             Tile type (used for formatting purposes). Accepts 'number', 'amount',
	 *                                      or empty. Default 'number'.
	 *     @type mixed    $data             The data value to supply to the tile. Default empty.
	 *     @type mixed    $comparison_data  Comparison data to pair with `$data`. Default empty.
	 *     @type callable $display_callback Display callback to use for the tile. Default is 'default_tile',
	 *                                      which leverages `$type`.
	 * }
	 * @return bool True if the tile was successfully added, otherwise false.
	 */
	public function add_tile( $tile_id, $attributes ) {
		$result = false;

		try {

			$result = parent::add_item( $tile_id, $attributes );

		} catch( \EDD_Exception $exception ) {

			edd_debug_log_exception( $exception );

			throw $exception;

		}

		return $result;
	}

	/**
	 * Removes a reports tile by ID from the registry.
	 *
	 * @since 3.0
	 *
	 * @param string $tile_id Reports tile ID.
	 */
	public function remove_tile( $tile_id ) {
		parent::remove_item( $tile_id );
	}

	/**
	 * Attempts to retrieve a given tile from the registry.
	 *
	 * @since 3.0
	 *
	 * @param string $tile_id Tile ID.
	 * @return array Array of tile attributes if it exists, otherwise an empty array.
	 */
	public function get_tile( $tile_id ) {
		$tile = array();

		try {

			$tile = parent::get_item( $tile_id );

		} catch( \EDD_Exception $exception ) {

			edd_debug_log_exception( $exception );

			throw Exceptions\Tile_Not_Found::from_tile( $tile_id, null, $exception );

		}

		return $tile;
	}

	/**
	 * Retrieves the list of tiles for the current registry instance.
	 *
	 * @since 3.0
	 *
	 * @return array Array of tile records in the current registry.
	 */
	public function get_tiles() {
		return parent::get_items();
	}

}
