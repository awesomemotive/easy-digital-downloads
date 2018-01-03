<?php
/**
 * Reports API Filters Registry
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Admin\Reports;

use EDD\Utils;

/**
 * Implements a singleton registry for registering reports filters.
 *
 * @since 3.0
 */
class Filters_Registry extends Utils\Registry implements Utils\Static_Registry {

	/**
	 * Registry type.
	 *
	 * @since 3.0
	 * @var   string
	 */
	public $type = 'filter';

	/**
	 * The one true Filters_Registry instance.
	 *
	 * @since 3.0
	 * @var   \EDD\Admin\Reports\Filters_Registry
	 */
	private static $instance;

	/**
	 * Retrieves the one true Filters_Registry instance.
	 *
	 * @since 3.0
	 *
	 * @return \EDD\Admin\Reports\Filters_Registry Registry instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Filters_Registry();
		}

		return self::$instance;
	}


}
