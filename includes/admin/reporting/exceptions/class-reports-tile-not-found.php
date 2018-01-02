<?php
/**
 * Tile_Not_Found Exception
 *
 * @package     EDD
 * @subpackage  Admin/Reports/Exceptions
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Admin\Reports\Exceptions;

/**
 * Implements a Tile_Not_Found exception thrown when a tile ID
 * is checked in the Reports API.
 *
 * @since 3.0
 *
 * @see \OutOfBoundsException
 * @see \EDD_Exception
 */
class Tile_Not_Found extends \OutOfBoundsException implements \EDD_Exception {

	/**
	 * Retrieves an informed Tile_Not_Found instance via late-static binding.
	 *
	 * @since 3.0
	 *
	 * @param string     $tile_id  Tile ID resulting in the exception.
	 * @param int        $code     Optional. Exception code. Default null.
	 * @param \Exception $previous Optional. Previous exception (used for chaining).
	 *                             Default null.
	 * @return \EDD\Admin\Reports\Exceptions\Tile_Not_Found Exception instance.
	 */
	public static function from_tile( $tile_id, $code = null, $previous = null ) {
		$message = sprintf( "The '%1$s' reports tile does not exist.", $tile_id );

		return new static( $message, $code, $previous);
	}

}
