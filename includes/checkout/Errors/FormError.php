<?php
/**
 * FormError.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 */

namespace EDD\Checkout\Errors;

class FormError {

	/**
	 * @var string|null Name of the corresponding form field.
	 */
	public $field_name;

	/**
	 * @var string Error code.
	 */
	public $error_code;

	/**
	 * @var string Public-facing error message.
	 */
	public $message;

	/**
	 * @param string      $message
	 * @param string      $error_code
	 * @param string|null $field_name
	 */
	public function __construct( $message, $error_code = 'edd_error', $field_name = null ) {
		$this->field_name = $field_name;
		$this->message    = $message;
		$this->error_code = $error_code;
	}

}
