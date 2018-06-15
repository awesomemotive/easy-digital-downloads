<?php
/**
 * Invalid_Argument exception class
 *
 * @package     EDD
 * @subpackage  Classes/Utilities
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Utils\Exceptions;

/**
 * Implements an Invalid_Argument exception thrown when a given
 * argument or parameter is invalid.
 *
 * @since 3.0
 *
 * @see \InvalidArgumentException
 * @see \EDD_Exception
 */
class Invalid_Argument extends \InvalidArgumentException implements \EDD_Exception {

	/**
	 * Type of value.
	 *
	 * @since 3.0
	 * @var   string
	 */
	public static $type = 'argument';

	/**
	 * Exception message.
	 *
	 * @since 3.0
	 * @var   string|null
	 */
	public static $error_message;

	/**
	 * Retrieves an informed Invalid_Argument instance via late-static binding.
	 *
	 * @since 3.0
	 *
	 * @param string     $argument_name Argument or parameter resulting in the exception.
	 * @param string     $method        Function or method name the argument or parameter was passed to.
	 * @param string     $context       Further context under which to build the exception message. To be
	 *                                  used by sub-classes when overriding build_message(). Default null.
	 * @param int        $code          Optional. Exception code. Default null.
	 * @param \Exception $previous      Optional. Previous exception (used for chaining).
	 *                                  Default null.
	 * @return \EDD\Utils\Exceptions\Invalid_Argument Exception instance.
	 */
	public static function from( $argument_name, $method, $context = null ) {
		static::build_message( $argument_name, $method, $context );

		return new static( static::$error_message );
	}

	/**
	 * Builds the Invalid_Argument exception message.
	 *
	 * Abstracted to allow for completely overriding the exception message in a subclass.
	 *
	 * @since 3.0
	 *
	 * @param string     $argument_name Argument or parameter resulting in the exception.
	 * @param string     $method        Function or method name the argument or parameter was passed to.
	 * @param string     $context       Further context under which to build the exception message. To be
	 *                                  used by sub-classes when overriding build_message(). Default null.
	 * @return string Informed Invalid_Argument message.
	 */
	public static function build_message( $argument_name, $method, $context = null ) {
		if ( ! isset( static::$error_message ) ) {

			if ( ! isset( self::$type ) ) {
				self::$type = 'argument';
			}

			self::$error_message = sprintf( 'The \'%1$s\' %2$s is missing or invalid for \'%3$s\'.',
				$argument_name,
				static::$type,
				$method
			);
		}
	}

}
