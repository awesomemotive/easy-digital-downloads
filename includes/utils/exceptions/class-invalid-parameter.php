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
class Invalid_Parameter extends Invalid_Argument implements \EDD_Exception {

	/**
	 * Type of value.
	 *
	 * @since 3.0
	 * @var   string
	 */
	public static $type = 'parameter';

}
