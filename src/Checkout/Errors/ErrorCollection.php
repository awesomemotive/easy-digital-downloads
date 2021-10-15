<?php
/**
 * ErrorCollection.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 */

namespace EDD\Checkout\Errors;

use EDD\Traits\Serializable;

class ErrorCollection {

	use Serializable;

	/**
	 * @var FormError[]
	 */
	private $errors = [];

	/**
	 * Adds a new form error.
	 *
	 * @param FormError $formError
	 */
	public function add( FormError $formError ) {
		$this->errors[] = $formError;
	}

	/**
	 * Whether or not we have any errors.
	 *
	 * @return bool
	 */
	public function hasErrors() {
		return ! empty( $this->errors );
	}

	/**
	 * @return FormError[]
	 */
	public function getErrors() {
		return $this->errors;
	}

	/**
	 * Whether the given error code is set.
	 *
	 * @param string $code
	 *
	 * @return bool
	 */
	public function hasErrorCode( $code ) {
		foreach ( $this->getErrors() as $error ) {
			if ( $error->error_code === $code ) {
				return true;
			}
		}

		return false;
	}

	public function toArray() {
		return $this->errors;
	}

}
