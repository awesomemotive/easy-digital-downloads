<?php
/**
 * Invalid_View exception class
 *
 * @package     EDD
 * @subpackage  Classes/Utilities
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Admin\Reports\Exceptions;

use EDD\Utils\Exceptions;

/**
 * Implements an Invalid_View exception thrown when a given
 * view is invalid.
 *
 * @since 3.0
 *
 * @see \InvalidArgumentException
 * @see \EDD_Exception
 */
class Invalid_View extends Exceptions\Invalid_Parameter implements \EDD_Exception {

	/**
	 * Builds the Invalid_View exception message.
	 *
	 * @since 3.0
	 *
	 * @param string     $argument_name Argument or parameter resulting in the exception.
	 * @param string     $method        Function or method name the argument or parameter was passed to.
	 * @return string Informed Invalid_Argument message.
	 */
	public static function build_message( $argument_name, $method, $context = null ) {
		self::$error_message = sprintf( 'The \'%1$s\' view for the \'%2$s\' item is invalid in \'%3$s\'.',
			$argument_name,
			$context,
			$method
		);
	}
}
