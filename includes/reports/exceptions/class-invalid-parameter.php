<?php
/**
 * Invalid_Parameter exception class
 *
 * @package     EDD
 * @subpackage  Classes/Utilities
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Reports\Exceptions;

use EDD\Utils\Exceptions;

/**
 * Implements an Invalid_Argument exception thrown when a given
 * argument or parameter is invalid.
 *
 * @since 3.0
 *
 * @see \InvalidArgumentException
 * @see \EDD_Exception
 */
class Invalid_Parameter extends Exceptions\Invalid_Parameter implements \EDD_Exception {

	/**
	 * Builds the Invalid_Parameter exception message.
	 *
	 * @since 3.0
	 *
	 * @param string     $argument_name Argument or parameter resulting in the exception.
	 * @param string     $method        Function or method name the argument or parameter was passed to.
	 * @return string Informed Invalid_Argument message.
	 */
	public static function build_message( $argument_name, $method, $context = null ) {
		self::$error_message = sprintf( 'The \'%1$s\' %2$s for the \'%3$s\' item is missing or invalid in \'%4$s\'.',
			$argument_name,
			static::$type,
			$context,
			$method
		);
	}
}
