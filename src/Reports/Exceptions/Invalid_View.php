<?php
/**
 * Invalid_View exception class
 *
 * @package     EDD\Reports\Exceptions
 * @copyright   Copyright (c) 2018, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

namespace EDD\Reports\Exceptions;

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
class Invalid_View extends Invalid_Parameter implements \EDD_Exception {

	/**
	 * Type of value.
	 *
	 * @since 3.0
	 * @var   string
	 */
	public static $type = 'view';
}
