<?php
/**
 * Tax Rate Row Class.
 *
 * @package     EDD\Taxes
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.0
 */

namespace EDD\Taxes;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Database\Row;

/**
 * Class Rate
 *
 * @since 3.5.0
 * @package EDD\Taxes
 */
class Rate extends Row {

	/**
	 * Magic getter for immutability.
	 *
	 * @since 3.5.0
	 * @param string $key Key to retrieve.
	 * @return mixed
	 */
	public function __get( $key = '' ) {

		if ( 'name' === $key ) {
			return $this->country;
		}

		if ( 'description' === $key ) {
			return $this->state;
		}

		return parent::__get( $key );
	}
}
