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
use EDD\Utils\Exception as EDD_Exception;

/**
 * Implements a singleton registry for registering reports tiles.
 *
 * @since 3.0
 *
 * @see \EDD\Utils\Registry
 */
class Tiles_Registry extends Registry {

	/**
	 * The one true Tiles_Registry instance.
	 *
	 * @since 3.0
	 * @var   \EDD\Admin\Reports\Tiles_Registry
	 */
	private static $instance;

	/**
	 * Retrieves the one true Reports Tabs Registry instance.
	 *
	 * @since 3.0
	 *
	 * @return \EDD\Admin\Reports\Tiles_Registry Reports tiles registry instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Tiles_Registry;
		}

		return self::$instance;
	}

	/**
	 * Adds a new reports tile to the registry.
	 *
	 * @since 3.0
	 *
	 * @param string $tab_id      Reports tab ID for the tile.
	 * @param string $tile_id     Reports tile ID.
	 * @param array  $attributes {
	 *     Attributes of the reports tile.
	 * }
	 * @return bool True if the tile was successfully added, otherwise false.
	 */
	public function add_tile( $tab_id, $tile_id, $attributes ) {
		$result = false;

		if ( $this->tab_exists( $tab_id ) ) {

			try {

				$result = parent::add_item( "{$tab_id}:{$tile_id}", $attributes );

			} catch( EDD_Exception $exception ) {

				$exception->log();

			}

		} else {

			$message = sprintf( 'The %1$s tab does not exist when attempting to add tile %2$s.', $tab_id, $tile_id );

			throw new EDD_Exception( $message );

		}

		return $result;
	}

	/**
	 * Removes a reports tile by ID from the master registry.
	 *
	 * @since 3.0
	 *
	 * @param string $tab_id  Reports tab ID.
	 * @param string $tile_id Reports tile ID.
	 */
	public function remove_tile( $tab_id, $tile_id ) {
		if ( $this->tab_exists( $tab_id ) ) {

			parent::remove_item( "{$tab_id}:{$tile_id}" );

		} else {

			$message = sprintf( "The '%1$s' tab does not exist when attempting to remove '%2$s' tile.", $tab_id, $tile_id );

			throw new EDD_Exception( $message );

		}
	}

	/**
	 * Retrieves a specific reports tile by ID from the master registry.
	 *
	 * @since 3.0
	 *
	 * @param string $tab_id  ID of the reports tab to retrieve the tile for.
	 * @param string $tile_id ID of the reports tile to retrieve.
	 * @return array The tile's attributes if it exists, otherwise an empty array.
	 */
	public function get_tile( $tab_id, $tile_id ) {
		$tile = array();

		if ( $this->tab_exists( $tab_id ) ) {

			try {

				$tile = parent::get_item( "{$tab_id}:{$tile_id}" );

			} catch( EDD_Exception $exception ) {

				$exception->log();

			}

		} else {

			$message = sprintf( "The '%1$s' tab does not exist when attempting to remove '%2$s' tile.", $tab_id, $tile_id );

			throw new EDD_Exception( $message );

		}

		return $tile;
	}

	/**
	 * Retrieves all of the registered reports tile records.
	 *
	 * @since 3.0
	 *
	 * @return array All registered reports tiles.
	 */
	public function get_tiles() {
		return parent::get_items();
	}

	/**
	 * Determines whether the given reports tab exists.
	 *
	 * @since 3.0
	 *
	 * @param string $tab_id Reports tab ID.
	 * @return bool True if the tab is registered and exists, otherwise false.
	 */
	public function tab_exists( $tab_id ) {
		$tabs = edd_get_registry( 'reports:tabs' );

		return $tabs->offsetExists( $tab_id );
	}

}
