<?php
/**
 * Static_Registry interface
 *
 * @package     EDD
 * @subpackage  Interfaces/Utilities
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Utils;

/**
 * Defines the contract for a static (singleton) registry object.
 *
 * @since 3.0
 */
interface Static_Registry {

	/**
	 * Retrieves the one true registry instance.
	 *
	 * @since 3.0
	 *
	 * @return \EDD\Utils\Static_Registry Registry instance.
	 */
	public static function instance();

}