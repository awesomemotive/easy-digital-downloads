<?php
/**
 * ValidationException.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 */

namespace EDD\Checkout\Exceptions;

use EDD\Checkout\Errors\ErrorCollection;

class ValidationException extends \Exception implements \EDD_Exception {

	private $errorCollection;

	public function __construct( ErrorCollection $errorCollection, $message = '', $code = 0, $previous = null ) {
		$this->errorCollection = $errorCollection;

		parent::__construct( $message, $code, $previous );
	}

	public function getErrorCollection() {
		return $this->errorCollection;
	}

}
