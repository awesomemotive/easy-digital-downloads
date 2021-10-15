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

	/**
	 * @var ErrorCollection
	 */
	private $errorCollection;

	public function __construct( ErrorCollection $errorCollection, $message = '', $code = 400, $previous = null ) {
		$this->errorCollection = $errorCollection;

		parent::__construct( $message, $code, $previous );
	}

	/**
	 * @return ErrorCollection
	 */
	public function getErrorCollection() {
		return $this->errorCollection;
	}

}
