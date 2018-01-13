<?php
/**
 * Reports API - Reports Registry
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
 * Implements a singleton registry for registering reports.
 *
 * @since 3.0
 *
 * @see \EDD\Admin\Reports\Registry
 * @see \EDD\Utils\Static_Registry
 *
 * @method array get_report( string $report_id )
 * @method void  remove_report( string $report_id )
 * @method array get_reports( string $sort )
 */
class Reports_Registry extends Registry implements Utils\Static_Registry {

	/**
	 * Item error label.
	 *
	 * @since 3.0
	 * @var   string
	 */
	public static $item_error_label = 'report';

	/**
	 * The one true Reports registry instance.
	 *
	 * @since 3.0
	 * @var   Reports_Registry
	 */
	private static $instance;

	/**
	 * Retrieves the one true Reports registry instance.
	 *
	 * @since 3.0
	 *
	 * @return Reports_Registry Reports registry instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Reports_Registry();
		}

		return self::$instance;
	}

	/**
	 * Handles magic method calls for report manipulation.
	 *
	 * @since 3.0
	 *
	 * @throws \EDD_Exception in get_report() if the item does not exist.
	 *
	 * @param string $name      Method name.
	 * @param array  $arguments Method arguments (if any)
	 * @return mixed Results of the method call (if any).
	 */
	public function __call( $name, $arguments ) {

		$report_id_or_sort = isset( $arguments[0] ) ? $arguments[0] : '';

		switch( $name ) {
			case 'get_report':
				return parent::get_item( $report_id_or_sort );
				break;

			case 'remove_report':
				parent::remove_item( $report_id_or_sort );
				break;

			case 'get_reports':
				return $this->get_items_sorted( $report_id_or_sort );
				break;
		}
	}

	/**
	 * Adds a new report to the master registry.
	 *
	 * @since 3.0
	 *
	 * @throws \EDD_Exception if the 'label' or 'endpoints' attributes are empty.
	 * @throws \EDD_Exception if one or more endpoints are not of a valid specification.
	 *
	 * @param string $report_id   Report ID.
	 * @param array  $attributes {
	 *     Reports attributes. All arguments are required unless otherwise noted.
	 *
	 *     @type string $label     Report label.
	 *     @type int    $priority  Optional. Priority by which to register the report. Default 10.
	 *     @type array  $filters   Filters available to the report.
	 *     @type array  $endpoints Endpoints to associate with the report.
	 * }
	 * @return bool True if the report was successfully registered, otherwise false.
	 */
	public function add_report( $report_id, $attributes ) {
		$error = false;

		$defaults = array(
			'label'     => '',
			'priority'  => 10,
			'filters'   => array(),
			'endpoints' => array(),
		);

		$attributes = array_merge( $defaults, $attributes );

		try {

			$this->validate_attributes( $attributes, $report_id, array( 'filters' ) );

		} catch( \EDD_Exception $exception ) {

			throw $exception;

			$error = true;

		}

		foreach ( $attributes['endpoints'] as $view_group => $endpoints ) {

			foreach ( $endpoints as $index => $endpoint ) {

				if ( ! is_string( $endpoint ) && ! ( $endpoint instanceof \EDD\Admin\Reports\Data\Endpoint ) ) {
					throw new Utils\Exception( sprintf( 'The \'%1$s\' report contains one or more invalidly defined endpoints.', $report_id ) );

					unset( $attributes['endpoints'][ $view_group][ $index ] );
				}

			}
		}

		if ( true === $error ) {

			return false;

		} else {

			return parent::add_item( $report_id, $attributes );

		}
	}

	/**
	 * Registers a new data endpoint to the master endpoints registry.
	 *
	 * @since 3.0
	 *
	 * @throws \EDD_Exception if the `$label` or `$views` attributes are empty.
	 * @throws \EDD_Exception if any of the required `$views` sub-attributes are empty.
	 *
	 * @see \EDD\Admin\Reports\Data\Endpoint_Registry::register_endpoint()
	 *
	 * @param string $endpoint_id Reports data endpoint ID.
	 * @param array  $attributes  Attributes of the endpoint. See Endpoint_Registry::register_endpoint()
	 *                            for more information on expected arguments.
	 * @return bool True if the endpoint was successfully registered, otherwise false.
	 */
	public function register_endpoint( $endpoint_id, $attributes ) {
		/** @var \EDD\Admin\Reports\Data\Endpoint_Registry|\WP_Error $registry */
		$registry = EDD()->utils->get_registry( 'reports:endpoints' );

		if ( is_wp_error( $registry ) ) {
			return false;
		}

		return $registry->register_endpoint( $endpoint_id, $attributes );
	}

	/**
	 * Unregisters a data endpoint from the master endpoints registry.
	 *
	 * @since 3.0
	 *
	 * @see \EDD\Admin\Reports\Data\Endpoint_Registry::unregister_endpoint()
	 *
	 * @param string $endpoint_id Endpoint ID.
	 */
	public function unregister_endpoint( $endpoint_id ) {
		/** @var \EDD\Admin\Reports\Data\Endpoint_Registry|\WP_Error $registry */
		$registry = EDD()->utils->get_registry( 'reports:endpoints' );

		if ( ! is_wp_error( $registry ) ) {
			$registry->unregister_endpoint( $endpoint_id );
		}
	}

}
