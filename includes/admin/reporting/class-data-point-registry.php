<?php
/**
 * Reports API Data Points Registry
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
 * Implements a singleton registry for registering reports data points.
 *
 * @since 3.0
 */
class Data_Point_Registry extends Utils\Registry implements Utils\Static_Registry {

	/**
	 * Registry type.
	 *
	 * @since 3.0
	 * @var   string
	 */
	public $type = 'data point';

	/**
	 * The one true Data_Point_Registry instance.
	 *
	 * @since 3.0
	 * @var   \EDD\Admin\Reports\Data_Point_Registry
	 */
	private static $instance;

	/**
	 * Retrieves the one true Data_Point_Registry instance.
	 *
	 * @since 3.0
	 *
	 * @return \EDD\Admin\Reports\Data_Point_Registry Registry instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Data_Point_Registry();
		}

		return self::$instance;
	}


}
