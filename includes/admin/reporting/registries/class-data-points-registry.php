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
 *
 * @see \EDD\Utils\Registry
 * @see \EDD\Utils\Static_Registry
 *
 * @method array get_data_point( string $item_id )
 * @method void  remove_data_point( string $item_id )
 * @method array get_data_points()
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

	/**
	 * Handles magic method calls for data point manipulation.
	 *
	 * @since 3.0
	 *
	 * @param string $name      Method name.
	 * @param array  $arguments Method arguments (if any)
	 * @return mixed Results of the method call (if any).
	 */
	public function __call( $name, $arguments ) {
		switch( $name ) {
			case 'get_data_point':
				return parent::get_item( $name );
				break;

			case 'remove_data_point':
				parent::remove_item( $name );
				break;

			case 'get_data_points':
				return parent::get_items();
				break;

		}
	}

}
