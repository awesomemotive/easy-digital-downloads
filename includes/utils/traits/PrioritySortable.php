<?php
/**
 * PrioritySortable.php
 *
 * For use in a registry. Supports sorting by the `priority` key of the registry items.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.1
 */

namespace EDD;

trait PrioritySortable {

	/**
	 * Sorts items by their priority.
	 *
	 * @return array
	 */
	public function get_items_by_priority() {
		parent::uasort( static function ( $a, $b ) {
			if ( $a['priority'] == $b['priority'] ) {
				return 0;
			}

			return ( $a['priority'] < $b['priority'] ) ? -1 : 1;
		} );

		return parent::get_items();
	}

}
