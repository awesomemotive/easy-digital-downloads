<?php
/**
 * Serializable.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 */

namespace EDD\Traits;

trait Serializable {

	public function toArray() {
		return get_object_vars( $this );
	}

}
