<?php
/**
 * Reports API Tabs Registry
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
use EDD\Admin\Reports\Exception as Reports_Exception;

/**
 * Implements a singleton registry for registering reports tabs.
 *
 * @since 3.0
 *
 * @see \EDD\Utils\Registry
 */
class Tabs_Registry extends Registry {

	/**
	 * The one true EDD_Reports_Tabs_Registry instance.
	 *
	 * @since 3.0
	 * @var   \EDD\Admin\Reports\Tabs_Registry
	 */
	private static $instance;

	/**
	 * Retrieves the one true Reports Tabs Registry instance.
	 *
	 * @since 3.0
	 *
	 * @return \EDD\Admin\Reports\Tabs_Registry Reports tabs registry instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Tabs_Registry;
		}

		return self::$instance;
	}

	/**
	 * Adds a new reports tab to the master registry.
	 *
	 * @since 3.0
	 *
	 * @param string $tab_id     Reports tab ID.
	 * @param array  $attributes {
	 *     Attributes of the reports tab.
	 *
	 *     @type string $label    Tab label.
	 *     @type int    $priority Priority by which to register the tab.
	 *     @type array  $filters  Registered filters to expose for the tab.
	 *     @type string $graph    Class to instantiate for building the graph.
	 * }
	 * @return bool True if the tab was successfully registered, otherwise false.
	 */
	public function add_tab( $tab_id, $attributes ) {
		$result = false;

		if ( ! is_array( $attributes ) ) {
			throw new Reports_Exception( 'New tab attributes must take the form of an array.' );

			return $result;
		} else {
			$attributes['tiles'] = new Tiles_Registry();
		}

		try {

			$result = parent::add_item( $tab_id, $attributes );

		} catch( EDD_Exception $exception ) {

			$exception->log();

		}

		return $result;
	}

	/**
	 * Removes a reports tab by ID from the master registry.
	 *
	 * @since 3.0
	 *
	 * @param string $tab_id Reports tab ID.
	 */
	public function remove_tab( $tab_id ) {
		parent::remove_item( $tab_id );
	}

	/**
	 * Retrieves a specific reports tab by ID from the master registry.
	 *
	 * @since 3.0
	 *
	 * @param string $tab_id        Name of the reports tab to retrieve.
	 * @param bool   $extract_tiles Optional. Whether to extract registered tiles as an array.
	 *                              Default true.
	 * @return array The tab's attributes if it exists, otherwise an empty array.
	 */
	public function get_tab( $tab_id, $extract_tiles = true ) {
		$tab = array();

		try {

			$tab = parent::get_item( $tab_id );

			if ( true === $extract_tiles ) {
				// Reduce tile registry to an array.
				if ( ! empty( $tab['tiles'] ) ) {
					$tab['tiles'] = $tab['tiles']->getArrayCopy();
				}
			}

		} catch( EDD_Exception $exception ) {

			$exception->log();

		}

		return $tab;
	}

	/**
	 * Retrieves all of the registered reports tab records.
	 *
	 * @since 3.0
	 *
	 * @param bool $extract_tiles Optional. Whether to extract registered tiles as arrays.
	 *                            Default true.
	 * @return array All registered reports tabs.
	 */
	public function get_tabs( $extract_tiles = true ) {
		/** @var \ArrayObject[] $tabs */
		$tabs = parent::get_items();

		if ( true === $extract_tiles ) {
			foreach ( $tabs as $tab_id => $attributes ) {
				if ( ! empty( $attributes['tiles'] ) ) {
					$tabs[ $tab_id ]['tiles'] = $attributes['tiles']->getArrayCopy();
				}
			}
		}

		return $tabs;
	}

	/**
	 * Registers a new Reports tab tile.
	 *
	 * @since 3.0
	 *
	 * @param string $tile_id    New tab tile ID.
	 * @param string $tab_id     Reports tab ID.
	 * @param array  $attributes Tile attributes.
	 * @return bool True if the tile was added, otherwise false.
	 */
	public function add_tile( $tile_id, $tab_id, $attributes ) {

		$added = false;

		if ( $this->offsetExists( $tab_id ) ) {

			$tab = $this->get_tab( $tab_id, false );

			try {

				$tab['tiles']->add_tile( $tile_id, $attributes );

				$added = true;

			} catch( Reports_Exception $exception ) {

				$exception->log();

			}
		}

		return $added;
	}

	/**
	 * Removes a tile from a tab.
	 *
	 * @since 3.0
	 *
	 * @param string $tile_id Reports tab tile ID.
	 * @param string $tab_id  Reports tab ID.
	 */
	public function remove_tile( $tile_id, $tab_id ) {

		if ( $this->offsetExists( $tab_id ) ) {

			$tab = $this->get_tab( $tab_id, false );

			$tab['tiles']->remove_tile( $tile_id );

		} else {

			$message = sprintf( "Tile '%1$s' cannot be removed from the nonexistent '%2$s' tab.", $tile_id, $tab_id );

			throw new Reports_Exception( $message );
		}

	}

	/**
	 * Retrieves a specific reports tile by ID from the master registry.
	 *
	 * @since 3.0
	 *
	 * @throws \EDD\Admin\Reports\Exception if the tab does not exist.
	 *
	 * @param string $tile_id ID of the reports tile to retrieve.
	 * @param string $tab_id  ID of the reports tab to retrieve the tile for.
	 * @return array The tile's attributes if it exists, otherwise an empty array.
	 */
	public function get_tile( $tile_id, $tab_id ) {
		$tile = array();

		if ( $this->tab_exists( $tab_id ) ) {

			$tab = $this->get_tab( $tab_id, false );

			try {

				$tile = $tab['tiles']->get_item( $tile_id );

			} catch( EDD_Exception $exception ) {

				$exception->log();

			}

		} else {

			$message = sprintf( "The '%1$s' tile could not be added because the '%2$s' tab does not exist.", $tile_id, $tab_id );

			throw new Reports_Exception( $message );

		}

		return $tile;
	}

	/**
	 * Retrieves all of the tile records for a given tab.
	 *
	 * @since 3.0
	 *
	 * @throws \EDD\Admin\Reports\Exception if the tab doesn't exist.
	 *
	 * @param string $tab_id Tab to retrieve tiles for.
	 * @return array All registered reports tiles.
	 */
	public function get_tiles( $tab_id ) {
		$tiles = array();

		if ( $this->tab_exists( $tab_id ) ) {

			$tab   = $this->get_tab( $tab_id );
			$tiles = $tab['tiles']->getArrayCopy();

		} else {

			$message = sprintf( "Tiles cannot be retrieve for the nonexistent '%1$s' tab.", $tab_id );

			throw new Reports_Exception( $message );

		}

		return $tiles;
	}

}
