<?php
/**
 * Reports API - Report Registry
 *
 * @package     EDD
 * @subpackage  Reports
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Reports\Data;

use EDD\Utils;
use EDD\Reports;

/**
 * Implements a singleton registry for registering reports.
 *
 * @since 3.0
 *
 * @see \EDD\Reports\Registry
 * @see \EDD\Utils\Static_Registry
 *
 * @method array get_report( string $report_id )
 * @method void  remove_report( string $report_id )
 */
class Report_Registry extends Reports\Registry implements Utils\Static_Registry {

	/**
	 * Item error label.
	 *
	 * @since 3.0
	 * @var   string
	 */
	public static $item_error_label = 'report';

	/**
	 * The one true Report registry instance.
	 *
	 * @since 3.0
	 * @var   Report_Registry
	 */
	private static $instance;

	/**
	 * Retrieves the one true Report registry instance.
	 *
	 * @since 3.0
	 *
	 * @return Report_Registry Report registry instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Report_Registry();
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
		$report_id_or_sort = isset( $arguments[0] )
			? $arguments[0]
			: '';

		switch ( $name ) {
			case 'get_report':
				return parent::get_item( $report_id_or_sort );

			case 'remove_report':
				return parent::remove_item( $report_id_or_sort );
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
	 *     @type array  $endpoints Optional. Endpoints to associate with the report.
	 * }
	 * @return bool True if the report was successfully registered, otherwise false.
	 */
	public function add_report( $report_id, $attributes ) {
		$error = false;

		$defaults = array(
			'label'      => '',
			'priority'   => 10,
			'group'      => 'core',
			'capability' => 'view_shop_reports',
			'filters'    => array(
				'dates',
				'taxes',
			)
		);

		$attributes['id'] = $report_id;
		$attributes = array_merge( $defaults, $attributes );

		try {
			// Filters can be empty.
			$this->validate_attributes( $attributes, $report_id, array( 'filters' ) );
		} catch ( \EDD_Exception $exception ) {
			$error = true;

			throw $exception;
		}

		if ( isset( $attributes['endpoints'] ) && is_array( $attributes['endpoints'] ) ) {
			foreach ( $attributes['endpoints'] as $view_group => $endpoints ) {
				foreach ( $endpoints as $index => $endpoint ) {
					if ( ! is_string( $endpoint ) && ! ( $endpoint instanceof \EDD\Reports\Data\Endpoint ) ) {
						unset( $attributes['endpoints'][ $view_group ][ $index ] );

						throw new Utils\Exception( sprintf( 'The \'%1$s\' report contains one or more invalidly defined endpoints.', $report_id ) );
					}
				}
			}
		}

		if ( isset( $attributes['filters'] ) && is_array( $attributes['filters'] ) ) {
			foreach ( $attributes['filters'] as $index => $filter ) {
				if ( ! Reports\validate_filter( $filter ) ) {
					$message = sprintf( 'The \'%1$s\' report contains one or more invalid filters.', $report_id );

					unset( $attributes['filters'][ $index ] );

					throw new Utils\Exception( $message );
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
	 * Retrieves registered reports.
	 *
	 * @since 3.0
	 *
	 * @param string $sort  Optional. How to sort the list of registered reports before retrieval.
	 *                      Accepts 'priority' or 'ID' (alphabetized by item ID), or empty (none).
	 *                      Default empty.
	 * @param string $group Optional. The reports group to retrieve reports for. Default 'core'.
	 * @return
	 */
	public function get_reports( $sort = '', $group = 'core' ) {
		$reports = $this->get_items_sorted( $sort );

		foreach ( $reports as $report_id => $atts ) {
			if ( $group !== $atts['group'] ) {
				unset( $reports[ $report_id ] );
			}
		}

		return $reports;
	}

	/**
	 * Registers a new data endpoint to the master endpoints registry.
	 *
	 * @since 3.0
	 *
	 * @throws \EDD_Exception if the `$label` or `$views` attributes are empty.
	 * @throws \EDD_Exception if any of the required `$views` sub-attributes are empty.
	 *
	 * @see \EDD\Reports\Data\Endpoint_Registry::register_endpoint()
	 *
	 * @param string $endpoint_id Reports data endpoint ID.
	 * @param array  $attributes  Attributes of the endpoint. See Endpoint_Registry::register_endpoint()
	 *                            for more information on expected arguments.
	 * @return bool True if the endpoint was successfully registered, otherwise false.
	 */
	public function register_endpoint( $endpoint_id, $attributes ) {
		/** @var \EDD\Reports\Data\Endpoint_Registry|\WP_Error $registry */
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
	 * @see \EDD\Reports\Data\Endpoint_Registry::unregister_endpoint()
	 *
	 * @param string $endpoint_id Endpoint ID.
	 */
	public function unregister_endpoint( $endpoint_id ) {
		/** @var \EDD\Reports\Data\Endpoint_Registry|\WP_Error $registry */
		$registry = EDD()->utils->get_registry( 'reports:endpoints' );

		if ( ! is_wp_error( $registry ) ) {
			$registry->unregister_endpoint( $endpoint_id );
		}
	}

	/**
	 * Registers an endpoint view to the master endpoint views registry.
	 *
	 * @since 3.0
	 *
	 * @throws \EDD_Exception if all expected attributes are not set.
	 *
	 * @see \EDD\Reports\Data\Endpoint_View_Registry::register_endpoint_view()
	 *
	 * @param string $view_id    View ID. Currently only core endpoint views can be added.
	 * @param array  $attributes Attributes of the endpoint view. See Endpoint_View_Registry::register_endpoint_view()
	 *                           for more information on expected/allowed arguments.
	 * @return bool True if the endpoint view was successfully registered, otherwise false.
	 */
	public function register_endpoint_view( $view_id, $attributes ) {
		/** @var \EDD\Reports\Data\Endpoint_View_Registry|\WP_Error $registry */
		$registry = EDD()->utils->get_registry( 'reports:endpoints:views' );

		if ( is_wp_error( $registry ) ) {
			return false;
		}

		return $registry->register_endpoint_view( $view_id, $attributes );
	}

	/**
	 * Builds and retrieves a Report object.
	 *
	 * @since 3.0
	 *
	 * @param string|Report $report          Report ID or object.
	 * @param bool          $build_endpoints Optional. Whether to build the endpoints (includes
	 *                                       registering any endpoint dependencies, such as
	 *                                       registering meta boxes). Default true.
	 * @return Report|\WP_Error Report object on success, otherwise a WP_Error object.
	 */
	public function build_report( $report, $build_endpoints = true ) {

		// If a report object was passed, just return it.
		if ( $report instanceof Report ) {
			return $report;
		}

		try {
			$_report = $this->get_report( $report );

		} catch( \EDD_Exception $exception ) {

			edd_debug_log_exception( $exception );

			return new \WP_Error( 'invalid_report', $exception->getMessage(), $report );
		}

		if ( ! empty( $_report ) ) {
			$_report = new Report( $_report );

			if ( true === $build_endpoints ) {
				$_report->build_endpoints();
			}
		}

		return $_report;
	}
}
