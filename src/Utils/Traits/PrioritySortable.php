<?php
/**
 * PrioritySortable.php
 *
 * For use in a registry. Supports sorting by the `priority` key of the registry items.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2024, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.3.8
 */

namespace EDD\Utils\Traits;

trait PrioritySortable {

	/**
	 * Sorts items by their priority.
	 *
	 * @return array
	 */
	public function get_items_by_priority() {
		parent::uasort(
			static function ( $a, $b ) {
				if ( (int) $a['priority'] === (int) $b['priority'] ) {
					return 0;
				}

				return ( $a['priority'] < $b['priority'] ) ? -1 : 1;
			}
		);

		return parent::get_items();
	}
}
