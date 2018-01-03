<?php
/**
 * Attribute_Not_Found exception class
 *
 * @package     EDD
 * @subpackage  Classes/Utilities
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Utils\Exceptions;

/**
 * Implements an Attribute_Not_Found exception thrown when a given
 * attribute is not found.
 *
 * @since 3.0
 *
 * @see \OutOfBoundsException
 * @see \EDD_Exception
 */
class Attribute_Not_Found extends \OutOfBoundsException implements \EDD_Exception {

	/**
	 * Retrieves an informed Tab_Not_Found instance via late-static binding.
	 *
	 * @since 3.0
	 *
	 * @param string     $attribute_name Attribute resulting in the exception.
	 * @param int        $code           Optional. Exception code. Default null.
	 * @param \Exception $previous       Optional. Previous exception (used for chaining).
	 *                                   Default null.
	 * @return \EDD\Utils\Exceptions\Attribute_Not_Found Exception instance.
	 */
	public static function from_attr( $attribute_name, $code = null, $previous = null ) {
		$message = sprintf( 'The \'%1$s\' attribute does not exist.', $attribute_name );

		return new static( $message, $code, $previous);
	}

}
