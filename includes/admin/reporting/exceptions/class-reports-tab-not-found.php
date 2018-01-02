<?php
/**
 * Tab_Not_Found Exception
 *
 * @package     EDD
 * @subpackage  Admin/Reports/Exceptions
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Admin\Reports\Exceptions;

/**
 * Implements a Tab_Not_Found exception thrown when tab IDs
 * are checked in the Reports API.
 *
 * @since 3.0
 *
 * @see \OutOfBoundsException
 * @see \EDD_Exception
 */
class Tab_Not_Found extends \OutOfBoundsException implements \EDD_Exception {

	/**
	 * Retrieves an informed Tab_Not_Found instance via late-static binding.
	 *
	 * @since 3.0
	 *
	 * @param string     $tab_id   Tab ID resulting in the exception.
	 * @param int        $code     Optional. Exception code. Default null.
	 * @param \Exception $previous Optional. Previous exception (used for chaining).
	 *                             Default null.
	 * @return \EDD\Admin\Reports\Exceptions\Tab_Not_Found Exception instance.
	 */
	public static function from_tab( $tab_id, $code = null, $previous = null ) {
		$message = sprintf( "The '%1$s' reports tab does not exist.", $tab_id );

		return new static( $message, $code, $previous);
	}

}
