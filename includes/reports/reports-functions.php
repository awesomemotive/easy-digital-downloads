<?php
/**
 * Reports API - Functions
 *
 * @package     EDD
 * @subpackage  Reports
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Reports;

//
// Endpoint and report helpers.
//

/**
 * Registers a new endpoint to the master registry.
 *
 * @since 3.0
 *
 * @see \EDD\Reports\Data\Endpoint_Registry::register_endpoint()
 *
 * @param string $endpoint_id Reports data endpoint ID.
 * @param array  $attributes  {
 *     Endpoint attributes. All arguments are required unless otherwise noted.
 *
 *     @type string $label    Endpoint label.
 *     @type int    $priority Optional. Priority by which to retrieve the endpoint. Default 10.
 *     @type array  $views {
 *         Array of view handlers by type.
 *
 *         @type array $view_type {
 *             View type slug, with array beneath it.
 *
 *             @type callable $data_callback    Callback used to retrieve data for the view.
 *             @type callable $display_callback Callback used to render the view.
 *             @type array    $display_args     Optional. Array of arguments to pass to the
 *                                              display_callback (if any). Default empty array.
 *         }
 *     }
 * }
 * @return bool True if the endpoint was successfully registered, otherwise false.
 */
function register_endpoint( $endpoint_id, $attributes ) {

	/** @var Data\Endpoint_Registry|\WP_Error $registry */
	$registry = EDD()->utils->get_registry( 'reports:endpoints' );

	if ( empty( $registry ) || is_wp_error( $registry ) ) {
		return false;
	}

	try {
		$added = $registry->register_endpoint( $endpoint_id, $attributes );

	} catch ( \EDD_Exception $exception ) {
		edd_debug_log_exception( $exception );

		$added = false;
	}

	return $added;
}

/**
 * Retrieves and builds an endpoint object.
 *
 * @since 3.0
 *
 * @see \EDD\Reports\Data\Endpoint_Registry::build_endpoint()
 *
 * @param string $endpoint_id Endpoint ID.
 * @param string $view_type   View type to use when building the object.
 * @return Data\Endpoint|\WP_Error Endpoint object on success, otherwise a WP_Error object.
 */
function get_endpoint( $endpoint_id, $view_type ) {

	/** @var Data\Endpoint_Registry|\WP_Error $registry */
	$registry = EDD()->utils->get_registry( 'reports:endpoints' );

	if ( empty( $registry ) || is_wp_error( $registry ) ) {
		return $registry;
	}

	return $registry->build_endpoint( $endpoint_id, $view_type );
}

/**
 * Registers a new report.
 *
 * @since 3.0
 *
 * @see \EDD\Reports\Data\Report_Registry::add_report()
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
function add_report( $report_id, $attributes ) {

	/** @var Data\Report_Registry|\WP_Error $registry */
	$registry = EDD()->utils->get_registry( 'reports' );

	if ( empty( $registry ) || is_wp_error( $registry ) ) {
		return false;
	}

	try {
		$added = $registry->add_report( $report_id, $attributes );

	} catch ( \EDD_Exception $exception ) {
		edd_debug_log_exception( $exception );

		$added = false;
	}

	return $added;
}

/**
 * Retrieves and builds a report object.
 *
 * @since 3.0
 *
 * @see \EDD\Reports\Data\Report_Registry::build_report()
 *
 * @param string $report_id       Report ID.
 * @param bool   $build_endpoints Optional. Whether to build the endpoints (includes registering
 *                                any endpoint dependencies, such as registering meta boxes).
 *                                Default true.
 * @return Data\Report|\WP_Error Report object on success, otherwise a WP_Error object.
 */
function get_report( $report_id = false, $build_endpoints = true ) {

	/** @var Data\Report_Registry|\WP_Error $registry */
	$registry = EDD()->utils->get_registry( 'reports' );

	if ( empty( $registry ) || is_wp_error( $registry ) ) {
		return $registry;
	}

	return $registry->build_report( $report_id, $build_endpoints );
}

/** Sections ******************************************************************/

/**
 * Retrieves the list of slug/label report pairs.
 *
 * @since 3.0
 *
 * @return array List of reports, otherwise an empty array.
 */
function get_reports() {

	/** @var Data\Report_Registry|\WP_Error $registry */
	$registry = EDD()->utils->get_registry( 'reports' );

	if ( empty( $registry ) || is_wp_error( $registry ) ) {
		return array();
	} else {
		$reports = $registry->get_reports( 'priority', 'core' );
	}

	// Re-sort by priority.
	uasort( $reports, array( $registry, 'priority_sort' ) );

	/**
	 * Filters the list of report slug/label pairs.
	 *
	 * @since 3.0
	 *
	 * @param array $reports List of slug/label pairs as representative of reports.
	 */
	return apply_filters( 'edd_get_reports', $reports );
}

/**
 * Retrieves the slug for the active report.
 *
 * @since 3.0
 *
 * @return string The active report, or the 'overview' report if no view defined
 */
function get_current_report() {
	return isset( $_REQUEST['view'] )
		? sanitize_key( $_REQUEST['view'] )
		: 'overview'; // Hardcoded default
}

/** Endpoints *****************************************************************/

/**
 * Retrieves the list of supported endpoint view types and their attributes.
 *
 * @since 3.0
 *
 * @return array List of supported endpoint types.
 */
function get_endpoint_views() {
	if ( ! did_action( 'edd_reports_init' ) ) {
		_doing_it_wrong( __FUNCTION__, 'Endpoint views cannot be retrieved prior to the firing of the edd_reports_init hook.', 'EDD 3.0' );

		return array();
	}

	/** @var Data\Endpoint_View_Registry|\WP_Error $registry */
	$registry = EDD()->utils->get_registry( 'reports:endpoints:views' );

	if ( empty( $registry ) || is_wp_error( $registry ) ) {
		return array();
	} else {
		$views = $registry->get_endpoint_views();
	}

	return $views;
}

/**
 * Retrieves the name of the handler class for a given endpoint view.
 *
 * @since 3.0
 *
 * @param string $view Endpoint view.
 * @return string Handler class name if set and the view exists, otherwise an empty string.
 */
function get_endpoint_handler( $view ) {
	$views = get_endpoint_views();

	return isset( $views[ $view ]['handler'] )
		? $views[ $view ]['handler']
		: '';
}

/**
 * Retrieves the group display callback for a given endpoint view.
 *
 * @since 3.0
 *
 * @param string $view Endpoint view.
 * @return string Group callback if set, otherwise an empty string.
 */
function get_endpoint_group_callback( $view ) {
	$views = get_endpoint_views();

	return isset( $views[ $view ]['group_callback'] )
		? $views[ $view ]['group_callback']
		: '';
}

/**
 * Determines whether an endpoint view is valid.
 *
 * @since 3.0
 *
 * @param string $view Endpoint view slug.
 * @return bool True if the view is valid, otherwise false.
 */
function validate_endpoint_view( $view ) {
	return array_key_exists( $view, get_endpoint_views() );
}

/**
 * Parses views for an incoming endpoint.
 *
 * @since 3.0
 *
 * @see get_endpoint_views()
 *
 * @param array  $views View slugs and attributes as dictated by get_endpoint_views().
 *
 * @return array (Maybe) adjusted views slugs and attributes array.
 */
function parse_endpoint_views( $views ) {
	$valid_views = get_endpoint_views();

	foreach ( $views as $view => $attributes ) {
		if ( ! empty( $valid_views[ $view ]['fields'] ) ) {
			$fields = $valid_views[ $view ]['fields'];

			// Merge the incoming args with the field defaults.
			$view_args = wp_parse_args( $attributes, $fields );

			// Overwrite the view attributes, keeping only the valid fields.
			$views[ $view ] = array_intersect_key( $view_args, $fields );

			if ( $views[ $view ]['display_callback'] === $fields['display_callback'] ) {
				$views[ $view ]['display_args'] = wp_parse_args( $views[ $view ]['display_args'], $fields['display_args'] );
			}
		}
	}

	return $views;
}

/** Filters *******************************************************************/

/**
 * Retrieves the list of registered reports filters and their attributes.
 *
 * @since 3.0
 *
 * @return array List of supported endpoint filters.
 */
function get_filters() {
	$filters = array(
		'dates'              => array(
			'label'            => __( 'Date', 'easy-digital-downloads' ),
			'display_callback' => __NAMESPACE__ . '\\display_dates_filter'
		),
		'products'           => array(
			'label'            => __( 'Products', 'easy-digital-downloads' ),
			'display_callback' => __NAMESPACE__ . '\\display_products_filter'
		),
		'product_categories' => array(
			'label'            => __( 'Product Categories', 'easy-digital-downloads' ),
			'display_callback' => __NAMESPACE__ . '\\display_product_categories_filter'
		),
		'taxes'              => array(
			'label'            => __( 'Exclude Taxes', 'easy-digital-downloads' ),
			'display_callback' => __NAMESPACE__ . '\\display_taxes_filter'
		),
		'gateways'           => array(
			'label'            => __( 'Gateways', 'easy-digital-downloads' ),
			'display_callback' => __NAMESPACE__ . '\\display_gateways_filter'
		),
		'discounts'          => array(
			'label'            => __( 'Discounts', 'easy-digital-downloads' ),
			'display_callback' => __NAMESPACE__ . '\\display_discounts_filter'
		),
		'regions'            => array(
			'label'            => __( 'Regions', 'easy-digital-downloads' ),
			'display_callback' => __NAMESPACE__ . '\\display_region_filter'
		),
		'countries'          => array(
			'label'            => __( 'Countries', 'easy-digital-downloads' ),
			'display_callback' => __NAMESPACE__ . '\\display_country_filter'
		),
		'currencies'          => array(
			'label'            => __( 'Currencies', 'easy-digital-downloads' ),
			'display_callback' => __NAMESPACE__ . '\\display_currency_filter'
		)
	);

	/**
	 * Filters the list of available report filters.
	 *
	 * @since 3.0
	 *
	 * @param array[] $filters
	 */
	return apply_filters( 'edd_report_filters', $filters );
}

/**
 * Determines whether the given filter is valid.
 *
 * @since 3.0
 *
 * @param string $filter Filter key.
 * @return bool True if the filter is valid, otherwise false.
 */
function validate_filter( $filter ) {
	return array_key_exists( $filter, get_filters() );
}

/**
 * Retrieves the value of an endpoint filter for the current session and report.
 *
 * @since 3.0
 *
 * @param string $filter Filter key to retrieve the value for.
 * @return mixed|string Value of the filter if it exists, otherwise an empty string.
 */
function get_filter_value( $filter ) {
	$value = '';

	// Bail if filter does not validate
	if ( ! validate_filter( $filter ) ) {
		return $value;
	}

	switch ( $filter ) {
		// Handle dates.
		case 'dates':
			$default_range          = 'this_month';
			$default_relative_range = 'previous_period';

			if ( ! isset( $_GET['range'] ) ) {
				$dates   = parse_dates_for_range( $default_range );
				$value   = array(
					'range'          => $default_range,
					'relative_range' => $default_relative_range,
					'from'           => $dates['start']->format( 'Y-m-d' ),
					'to'             => $dates['end']->format( 'Y-m-d' ),
				);
			} else {
				$value = array(
					'range' => isset( $_GET['range'] )
						? sanitize_text_field( $_GET['range'] )
						: $default_range,
					'relative_range' => isset( $_GET['relative_range'] )
						? sanitize_text_field( $_GET['relative_range'] )
						: $default_relative_range,
					'from' => isset( $_GET['filter_from'] )
						? sanitize_text_field( $_GET['filter_from'] )
						: '',
					'to'   => isset( $_GET['filter_to'] )
						? sanitize_text_field( $_GET['filter_to'] )
						: ''
				);
			}

			break;

		// Handle taxes.
		case 'taxes':
			$value = array();

			if ( isset( $_GET['exclude_taxes'] ) ) {
				$value['exclude_taxes'] = true;
			}

			break;

		// Handle default (direct from URL).
		default:
			$value = isset( $_GET[ $filter ] )
				? sanitize_text_field( $_GET[ $filter ] )
				: '';

			/**
			 * Filters the value of a report filter.
			 *
			 * @since 3.0
			 *
			 * @param string $value Report filter value.
			 * @param string $filter Report filter.
			 */
			$value = apply_filters( 'edd_reports_get_filter_value', $value, $filter );
	}

	return $value;
}

/**
 * Returns a list of registered report filters that should be persisted across views.
 *
 * @since 3.0
 *
 * @return array
 */
function get_persisted_filters() {
	$filters = array(
		'range',
		'relative_range',
		'filter_from',
		'filter_to',
		'exclude_taxes',
	);

	/**
	 * Filters registered report filters that should be persisted across views.
	 *
	 * @since 3.0
	 *
	 * @param array $filters List of registered filters to persist.
	 */
	$filters = apply_filters( 'edd_reports_get_persisted_filters', $filters );

	return $filters;
}

/**
 * Retrieves key/label pairs of date filter options for use in a drop-down.
 *
 * @since 3.0
 *
 * @return array Key/label pairs of date filter options.
 */
function get_dates_filter_options() {
	static $options = null;

	if ( is_null( $options ) ) {
		$options = array(
			'other'        => __( 'Custom', 'easy-digital-downloads' ),
			'today'        => __( 'Today', 'easy-digital-downloads' ),
			'yesterday'    => __( 'Yesterday', 'easy-digital-downloads' ),
			'this_week'    => __( 'This Week', 'easy-digital-downloads' ),
			'last_week'    => __( 'Last Week', 'easy-digital-downloads' ),
			'last_30_days' => __( 'Last 30 Days', 'easy-digital-downloads' ),
			'this_month'   => __( 'Month to Date', 'easy-digital-downloads' ),
			'last_month'   => __( 'Last Month', 'easy-digital-downloads' ),
			'this_quarter' => __( 'Quarter to Date', 'easy-digital-downloads' ),
			'last_quarter' => __( 'Last Quarter', 'easy-digital-downloads' ),
			'this_year'    => __( 'Year to Date', 'easy-digital-downloads' ),
			'last_year'    => __( 'Last Year', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Filters the list of key/label pairs of date filter options.
	 *
	 * @since 1.3
	 *
	 * @param array $date_options Date filter options.
	 */
	return apply_filters( 'edd_report_date_options', $options );
}


/**
 * Retrieves the default relative range key for a specific range.
 *
 * @since 3.1
 *
 * @return string Relative date range key.
 */
function get_default_relative_range( $range ) {

	switch ( $range ) {
		case 'this_month':
		case 'last_month':
			$relative_range = 'previous_month';
			break;

		case 'this_quarter':
		case 'last_quarter':
			$relative_range = 'previous_quarter';
			break;

		case 'this_year':
		case 'last_year':
			$relative_range = 'previous_year';
			break;

		default:
			$relative_range = 'previous_period';
			break;
	}

	return $relative_range;
}


/**
 * Retrieves key/label pairs of relative date filter options for use in a drop-down.
 *
 * @since 3.1
 *
 * @return array Key/label pairs of relative date filter options.
 */
function get_relative_dates_filter_options() {
	static $options = null;

	if ( is_null( $options ) ) {
		$options = array(
			'previous_period'  => __( 'Previous period', 'easy-digital-downloads' ),
			'previous_month'   => __( 'Previous month', 'easy-digital-downloads' ),
			'previous_quarter' => __( 'Previous quarter', 'easy-digital-downloads' ),
			'previous_year'    => __( 'Previous year', 'easy-digital-downloads' ),
		);
	}

	return $options;
}

/**
 * Retrieves the start and end date filters for use with the Reports API.
 *
 * @since 3.0
 *
 * @param string $values   Optional. What format to retrieve dates in the resulting array in.
 *                         Accepts 'strings' or 'objects'. Default 'strings'.
 * @param string $timezone Optional. Timezone to force for filter dates. Primarily used for
 *                         legacy testing purposes. Default empty.
 * @return array|\EDD\Utils\Date[] {
 *     Query date range for the current graph filter request.
 *
 *     @type string|\EDD\Utils\Date $start Start day and time (based on the beginning of the given day).
 *                                         If `$values` is 'objects', a Carbon object, otherwise a date
 *                                         time string.
 *     @type string|\EDD\Utils\Date $end   End day and time (based on the end of the given day). If `$values`
 *                                         is 'objects', a Carbon object, otherwise a date time string.
 * }
 */
function get_dates_filter( $values = 'strings', $timezone = null ) {
	$dates = parse_dates_for_range();

	if ( 'strings' === $values ) {
		if ( ! empty( $dates['start'] ) ) {
			$dates['start'] = $dates['start']->toDateTimeString();
		}
		if ( ! empty( $dates['end'] ) ) {
			$dates['end'] = $dates['end']->toDateTimeString();
		}
	}

	/**
	 * Filters the start and end date filters for use with the Graphs API.
	 *
	 * @since 3.0
	 *
	 * @param array|\EDD\Utils\Date[] $dates {
	 *     Query date range for the current graph filter request.
	 *
	 *     @type string|\EDD\Utils\Date $start Start day and time (based on the beginning of the given day).
	 *                                         If `$values` is 'objects', a Date object, otherwise a date
	 *                                         time string.
	 *     @type string|\EDD\Utils\Date $end   End day and time (based on the end of the given day). If `$values`
	 *                                         is 'objects', a Date object, otherwise a date time string.
	 * }
	 */
	return apply_filters( 'edd_get_dates_filter', $dates );
}

/**
 * Parses start and end dates for the given range.
 *
 * @since 3.0
 *
 * @param string          $range          Optional. Range value to generate start and end dates for against `$date`.
 *                                        Default is the current range as derived from the session.
 * @param string          $date           Date string converted to `\EDD\Utils\Date` to anchor calculations to.
 * @param bool            $convert_to_utc Optional. If we should convert the results to UTC for Database Queries
 * @return \EDD\Utils\Date[] Array of start and end date objects.
 */
function parse_dates_for_range( $range = null, $date = 'now', $convert_to_utc = true ) {

	// Set the time ranges in the user's timezone, so they ultimately see them in their own timezone.
	$date = EDD()->utils->date( $date, null, true );

	if ( null === $range || ! array_key_exists( $range, get_dates_filter_options() ) ) {
		$range = get_dates_filter_range();
	}

	switch ( $range ) {

		case 'this_month':
			$dates = array(
				'start' => $date->copy()->startOfMonth(),
				'end'   => $date->copy()->endOfDay(),
			);
			break;

		case 'last_month':
			$dates = array(
				'start' => $date->copy()->subMonthNoOverflow( 1 )->startOfMonth(),
				'end'   => $date->copy()->subMonthNoOverflow( 1 )->endOfMonth(),
			);
			break;

		case 'today':
			$dates = array(
				'start' => $date->copy()->startOfDay(),
				'end'   => $date->copy()->endOfDay(),
			);
			break;

		case 'yesterday':
			$dates = array(
				'start' => $date->copy()->subDay( 1 )->startOfDay(),
				'end'   => $date->copy()->subDay( 1 )->endOfDay(),
			);
			break;

		case 'this_week':
			$dates = array(
				'start' => $date->copy()->startOfWeek(),
				'end'   => $date->copy()->endOfDay(),
			);
			break;

		case 'last_week':
			$dates = array(
				'start' => $date->copy()->subWeek( 1 )->startOfWeek(),
				'end'   => $date->copy()->subWeek( 1 )->endOfWeek(),
			);
			break;

		case 'last_30_days':
			$dates = array(
				'start' => $date->copy()->subDay( 30 )->startOfDay(),
				'end'   => $date->copy()->endOfDay(),
			);
			break;

		case 'this_quarter':
			$dates = array(
				'start' => $date->copy()->startOfQuarter(),
				'end'   => $date->copy()->endOfDay(),
			);
			break;

		case 'last_quarter':
			$dates = array(
				'start' => $date->copy()->subQuarter( 1 )->startOfQuarter(),
				'end'   => $date->copy()->subQuarter( 1 )->endOfQuarter(),
			);
			break;

		case 'this_year':
			$dates = array(
				'start' => $date->copy()->startOfYear(),
				'end'   => $date->copy()->endOfDay(),
			);
			break;

		case 'last_year':
			$dates = array(
				'start' => $date->copy()->subYear( 1 )->startOfYear(),
				'end'   => $date->copy()->subYear( 1 )->endOfYear(),
			);
			break;

		case 'other':
		default:
			$dates_from_report = get_filter_value( 'dates' );

			if ( ! empty( $dates_from_report ) ) {
				$start = $dates_from_report['from'];
				$end   = $dates_from_report['to'];
			} else {
				$start = $end = 'now';
			}

			$dates = array(
				'start' => EDD()->utils->date( $start )->startOfDay(),
				'end'   => EDD()->utils->date( $end )->endOfDay(),
			);
			break;
	}

	if ( $convert_to_utc ) {
		// Convert the values to the UTC equivalent so that we can query the database using UTC.
		$dates['start'] = edd_get_utc_equivalent_date( $dates['start'] );
		$dates['end']   = edd_get_utc_equivalent_date( $dates['end'] );
	}

	$dates['range'] = $range;

	return $dates;
}

/**
 * Parses relative start and end dates for the given range.
 *
 * @since 3.1
 *
 * @param string          $range          Optional. Range value to generate start and end dates for against `$date`.
 * @param string          $relative_range Optional. Range value to generate relative start and end dates for against `$date`.
 *                                        Default is the current range as derived from the session.
 * @param string          $date           Date string converted to `\EDD\Utils\Date` to anchor calculations to.
 * @param bool            $convert_to_utc Optional. If we should convert the results to UTC for Database Queries
 * @return \EDD\Utils\Date[] Array of start and end date objects.
 */
function parse_relative_dates_for_range( $range = null, $relative_range = null, $date = 'now', $convert_to_utc = true ) {


	if ( null === $range || ! array_key_exists( $range, get_dates_filter_options() ) ) {
		$range = get_dates_filter_range();
	}

	if ( null === $relative_range || ! array_key_exists( $relative_range, get_relative_dates_filter_options() ) ) {
		$relative_range = get_relative_dates_filter_range();
	}

	$dates = parse_dates_for_range( $range, $date, false );

	switch ( $relative_range ) {
		case 'previous_period':
			$days_diff = $dates['start']->copy()->diffInDays( $dates['end'], true ) + 1;
			$dates     = array(
				'start' => $dates['start']->copy()->subDays( $days_diff ),
				'end'   => $dates['end']->copy()->subDays( $days_diff ),
			);
			break;
		case 'previous_month':
			$dates = array(
				'start' => $dates['start']->copy()->subMonth( 1 ),
				'end'   => $dates['end']->copy()->subMonthNoOverflow( 1 ),
			);
			break;
		case 'previous_quarter':
			$dates = array(
				'start' => $dates['start']->copy()->subQuarter( 1 ),
				'end'   => $dates['end']->copy()->subQuarter( 1 ),
			);
			break;
		case 'previous_year':
			$dates = array(
				'start' => $dates['start']->copy()->subYear( 1 ),
				'end'   => $dates['end']->copy()->subYear( 1 ),
			);
			break;
	}

	if ( $convert_to_utc ) {
		// Convert the values to the UTC equivalent so that we can query the database using UTC.
		$dates['start'] = edd_get_utc_equivalent_date( $dates['start'] );
		$dates['end']   = edd_get_utc_equivalent_date( $dates['end'] );
	}

	$dates['range'] = $range;

	return $dates;
}

/**
 * Retrieves the date filter range.
 *
 * @since 3.0
 *
 * @return string Date filter range.
 */
function get_dates_filter_range() {

	$dates = get_filter_value( 'dates' );

	if ( isset( $dates['range'] ) ) {
		$range = sanitize_key( $dates['range'] );

	} else {

		/**
		 * Filters the report dates default range.
		 *
		 * @since 1.3
		 *
		 * @param string $range Date range as derived from the session. Default 'last_30_days'
		 * @param array  $dates Dates filter data array.
		 */
		$range = apply_filters( 'edd_get_report_dates_default_range', 'this_month', $dates );
	}

	/**
	 * Filters the dates filter range.
	 *
	 * @since 3.0
	 *
	 * @param string $range Dates filter range.
	 * @param array  $dates Dates filter data array.
	 */
	return apply_filters( 'edd_get_dates_filter_range', $range, $dates );
}


/**
 * Retrieves the date filter for relative range.
 *
 * @since 3.1
 *
 * @return string Date filter range.
 */
function get_relative_dates_filter_range() {

	$dates = get_filter_value( 'dates' );

	if ( isset( $dates['relative_range'] ) ) {
		$relative_range = sanitize_key( $dates['relative_range'] );
	} else {

		/**
		 * Filters the report dates default range.
		 *
		 * @since 3.1
		 *
		 * @param string $range Relative daate range as derived from the session. Default 'previous_period'
		 * @param array  $dates Dates filter data array.
		 */
		$relative_range = apply_filters( 'edd_get_report_dates_default_relative_range', 'previous_period', $dates );
	}

	/**
	 * Filters the dates filter range.
	 *
	 * @since 3.1
	 *
	 * @param string $range Dates filter relative range.
	 * @param array  $dates Dates filter data array.
	 */
	return apply_filters( 'edd_get_dates_filter_relative_range', $relative_range, $dates );
}

/**
 * Determines whether results should be displayed hour by hour, or not.
 *
 * @since 3.0
 *
 * @return bool True if results should use hour by hour, otherwise false.
 */
function get_dates_filter_hour_by_hour() {
	$hour_by_hour = false;

	// Retrieve the queried dates.
	$dates = get_dates_filter( 'objects' );

	// Determine graph options.
	switch ( $dates['range'] ) {
		case 'today':
		case 'yesterday':
			$hour_by_hour = true;
			break;
		case 'this_week':
		case 'this_month':
		case 'this_quarter':
		case 'this_year':
		case 'other':
			$difference = ( $dates['end']->getTimestamp() - $dates['start']->getTimestamp() );
			if ( $difference <= ( DAY_IN_SECONDS * 2 ) ) {
				$hour_by_hour = true;
			}
			break;
		default:
			$hour_by_hour = false;
			break;
	}

	return $hour_by_hour;
}

/**
 * Determines whether results should be displayed day by day or not.
 *
 * @since 3.0
 *
 * @return bool True if results should use day by day, otherwise false.
 */
function get_dates_filter_day_by_day() {
	// Retrieve the queried dates
	$dates = get_dates_filter( 'objects' );

	// Determine graph options
	switch ( $dates['range'] ) {
		case 'today':
		case 'yesterday':
		case 'this_year':
		case 'last_year':
			$day_by_day = false;
			break;
		case 'other':
			$difference = ( $dates['end']->getTimestamp() - $dates['start']->getTimestamp() );

			if ( $difference >= ( YEAR_IN_SECONDS / 4 ) ) {
				$day_by_day = false;
			} else {
				$day_by_day = true;
			}
			break;
		default:
			$day_by_day = true;
			break;
	}

	return $day_by_day;
}

/**
 * Gets the period for a graph.
 *
 * @since 3.1.1.4
 * @return string
 */
function get_graph_period() {
	if ( get_dates_filter_hour_by_hour() ) {
		return 'hour';
	}
	if ( get_dates_filter_day_by_day() ) {
		return 'day';
	}

	return 'month';
}

/**
 * Gets the SQL clauses.
 * The result of this function should be run through $wpdb->prepare().
 *
 * @since 3.1.1.4
 * @param string $period The period for the query.
 * @param string $column The column to query.
 * @return array
 */
function get_sql_clauses( $period, $column = 'date_created' ) {

	// Get the date for the query.
	$converted_date = get_column_conversion( $column );

	switch ( $period ) {
		case 'hour':
			$date_format = '%%Y-%%m-%%d %%H:00:00';
			break;
		case 'day':
			$date_format = '%%Y-%%m-%%d';
			break;
		default:
			$date_format = '%%Y-%%m';
			break;
	}

	return array(
		'select'  => "DATE_FORMAT({$converted_date}, \"{$date_format}\") AS date",
		'where'   => '',
		'groupby' => 'date',
		'orderby' => 'date',
	);
}

/**
 * Given a function and column, make a timezone converted groupby query.
 *
 * @since 3.0
 * @since 3.0.4 If MONTH is passed as the function, always add YEAR and MONTH
 *              to avoid issues with spanning multiple years.
 * @since 3.1.1.4 This function isn't needed anymore due to using DATE_FORMAT in the select clause.
 *
 * @param string $function The function to run the value through, like DATE, HOUR, MONTH.
 * @param string $column   The column to group by.
 *
 * @return string
 */
function get_groupby_date_string( $function = 'DATE', $column = 'date_created' ) {
	/**
	 * If there is no offset, the default column will be returned.
	 * Otherwise, the column will be converted to the timezone offset.
	 */
	$column_conversion = get_column_conversion( $column );

	$function = strtoupper( $function );
	switch ( $function ) {
		case 'HOUR':
			$group_by_string = "DAY({$column_conversion}), HOUR({$column_conversion})";
			break;
		case 'MONTH':
			$group_by_string = "YEAR({$column_conversion}), MONTH({$column_conversion})";
			break;
		default:
			$group_by_string = "{$function}({$column_conversion})";
			break;
	}

	return $group_by_string;
}

/**
 * Get the time zone converted dates for the query.
 *
 * @since 3.1.1.4
 * @param string $column
 * @return string
 */
function get_column_conversion( $column = 'date_created' ) {
	$date       = EDD()->utils->date( 'now', edd_get_timezone_id(), false );
	$gmt_offset = $date->getOffset();
	if ( empty( $gmt_offset ) ) {
		return $column;
	}

	// Output the offset in the proper format.
	$hours   = abs( floor( $gmt_offset / HOUR_IN_SECONDS ) );
	$minutes = abs( floor( ( $gmt_offset / MINUTE_IN_SECONDS ) % MINUTE_IN_SECONDS ) );
	$math    = ( $gmt_offset >= 0 ) ? '+' : '-';

	$formatted_offset = ! empty( $minutes ) ? "{$hours}:{$minutes}" : $hours . ':00';

	/**
	 * There is a limitation here that we cannot get past due to MySQL not having timezone information.
	 *
	 * When a requested date group spans the DST change. For instance, a 6 month graph will have slightly
	 * different results for each month than if you pulled each of those 6 months individually. This is because
	 * our 'grouping' can only convert the timezone based on the current offset and that can change if the
	 * range spans the DST break, which would have some dates be in a +/- 1 hour state.
	 *
	 * @see https://github.com/awesomemotive/easy-digital-downloads/pull/9449
	 */
	return "CONVERT_TZ({$column}, '+00:00', '{$math}{$formatted_offset}')";
}

/**
 * Retrieves the tax exclusion filter.
 *
 * @since 3.0
 *
 * @return bool True if taxes should be excluded from calculations.
 */
function get_taxes_excluded_filter() {
	$taxes = get_filter_value( 'taxes' );

	if ( ! isset( $taxes['exclude_taxes'] ) ) {
		return false;
	}

	return (bool) $taxes['exclude_taxes'];
}

/** Display *******************************************************************/

/**
 * Handles display of a report.
 *
 * @since 3.0
 *
 * @param Data\Report $report Report object.
 */
function default_display_report( $report ) {

	// Bail if erroneous report
	if ( empty( $report ) || is_wp_error( $report ) ) {
		return;
	}

	// Try to output: tiles, tables, and charts
	$report->display_endpoint_group( 'tiles'  );
	$report->display_endpoint_group( 'tables' );
	$report->display_endpoint_group( 'charts' );
}

/**
 * Displays the default content for a tile endpoint.
 *
 * @since 3.0
 *
 * @param Data\Report $report Report object the tile endpoint is being rendered in.
 *                            Not always set.
 * @param array       $tile   {
 *     Tile display arguments.
 *
 *     @type Data\Tile_Endpoint $endpoint     Endpoint object.
 *     @type mixed|array        $data         Date for display. By default, will be an array,
 *                                            but can be of other types.
 *     @type array              $display_args Array of any display arguments.
 * }
 * @return void Meta box display callbacks only echo output.
 */
function default_display_tile( $endpoint, $data, $args ) {
	echo '<div class="tile-label">' . esc_html( $endpoint->get_label() ) . '</div>';

	if ( empty( $data ) ) {
		echo '<div class="tile-no-data tile-value">&mdash;</div>';
	} else {
		switch ( $args['type'] ) {
			case 'number':
				echo '<div class="tile-number tile-value">' . edd_format_amount( $data ) . '</div>';
				break;

			case 'split-number':
				printf( '<div class="tile-amount tile-value">%1$d / %2$d</div>',
					edd_format_amount( $data['first_value'] ),
					edd_format_amount( $data['second_value'] )
				);
				break;

			case 'split-amount':
				printf( '<div class="tile-amount tile-value">%1$d / %2$d</div>',
					edd_currency_filter( edd_format_amount( $data['first_value'] ) ),
					edd_currency_filter( edd_format_amount( $data['second_value'] ) )
				);
				break;

			case 'relative':
				$direction = ( ! empty( $data['direction'] ) && in_array( $data['direction'], array( 'up', 'down' ), true ) )
					? '-' . sanitize_key( $data['direction'] )
					: '';
				echo '<div class="tile-change' . esc_attr( $direction ) . ' tile-value">' . edd_format_amount( $data['value'] ) . '</div>';
				break;

			case 'amount':
				echo '<div class="tile-amount tile-value">' . edd_currency_filter( edd_format_amount( $data ) ) . '</div>';
				break;

			case 'url':
				echo '<div class="tile-url tile-value">' . esc_url( $data ) . '</div>';
				break;

			default:
				$tags = wp_kses_allowed_html( 'post' );
				echo '<div class="tile-value tile-default">' . wp_kses( $data, $tags ) . '</div>';
				break;
		}
	}

	if ( ! empty( $args['comparison_label'] ) ) {
		echo '<div class="tile-compare">' . esc_attr( $args['comparison_label'] ) . '</div>';
	}
}

/**
 * Handles default display of all tile endpoints registered against a report.
 *
 * @since 3.0
 *
 * @param Data\Report $report Report object.
 */
function default_display_tiles_group( $report ) {
	if ( ! $report->has_endpoints( 'tiles' ) ) {
		return;
	}

	$tiles = $report->get_endpoints( 'tiles' );
?>

	<div id="edd-reports-tiles-wrap" class="edd-report-wrap">
		<?php
		foreach ( $tiles as $endpoint_id => $tile ) :
			$tile->display();
		endforeach;
		?>
	</div>

	<?php
}

/**
 * Handles default display of all table endpoints registered against a report.
 *
 * @since 3.0
 *
 * @param Data\Report $report Report object.
 */
function default_display_tables_group( $report ) {
	if ( ! $report->has_endpoints( 'tables' ) ) {
		return;
	}

	$tables = $report->get_endpoints( 'tables' ); ?>

	<div id="edd-reports-tables-wrap" class="edd-report-wrap"><?php

		foreach ( $tables as $endpoint_id => $table ) :

			?><div class="edd-reports-table" id="edd-reports-table-<?php echo esc_attr( $endpoint_id ); ?>">
				<h3><?php echo esc_html( $table->get_label() ); ?></h3><?php

				$table->display();

			?></div><?php

		endforeach;

	?><div class="clear"></div></div><?php
}

/**
 * Handles default display of all chart endpoints registered against a report.
 *
 * @since 3.0
 *
 * @param Data\Report $report Report object.
 */
function default_display_charts_group( $report ) {
	if ( ! $report->has_endpoints( 'charts' ) ) {
		return;
	}

	?>
	<div id="edd-reports-charts-wrap" class="edd-report-wrap">
	<?php

	$charts = $report->get_endpoints( 'charts' );

	foreach ( $charts as $endpoint_id => $chart ) {
		?>
		<div class="edd-reports-chart edd-reports-chart-<?php echo esc_attr( $chart->get_type() ); ?>" id="edd-reports-table-<?php echo esc_attr( $endpoint_id ); ?>">
			<h3><?php echo esc_html( $chart->get_label() ); ?></h3>

			<?php $chart->display(); ?>
		</div>
		<?php
	}
	?>
		<div class="chart-timezone">
			<?php printf( esc_html__( 'Chart time zone: %s', 'easy-digital-downloads' ), esc_html( edd_get_timezone_id() ) ); ?>
		</div>
	</div>
	<?php
}

/**
 * Handles display of the 'Date' filter for reports.
 *
 * @since 3.0
 */
function display_dates_filter() {
	$date_format            = get_option( 'date_format' );
	$range_options          = get_dates_filter_options();
	$relative_range_options = get_relative_dates_filter_options();
	$dates                  = get_filter_value( 'dates' );
	$selected_range         = isset( $dates['range'] )
		? $dates['range']
		: get_dates_filter_range();

	$selected_relative_range = isset( $dates['relative_range'] )
		? $dates['relative_range']
		: get_relative_dates_filter_range();

	$class = ( 'other' !== $selected_range )
		? ' screen-reader-text'
		: '';

	$range_select = EDD()->html->select(
		array(
			'name'             => 'range',
			'class'            => 'edd-graphs-date-options',
			'options'          => $range_options,
			'variations'       => false,
			'show_option_all'  => false,
			'show_option_none' => false,
			'selected'         => $selected_range,
		)
	);

	$relative_range_select = EDD()->html->select(
		array(
			'name'             => 'relative_range',
			'class'            => 'edd-graphs-relative-date-options',
			'options'          => $relative_range_options,
			'variations'       => false,
			'show_option_all'  => false,
			'show_option_none' => false,
			'selected'         => $selected_relative_range,
		)
	);

	// From.
	$from = EDD()->html->date_field(
		array(
			'id'          => 'filter_from',
			'name'        => 'filter_from',
			'value'       => ( empty( $dates['from'] ) || ( 'other' !== $dates['range'] ) ) ? '' : $dates['from'],
			'placeholder' => _x( 'From', 'date filter', 'easy-digital-downloads' ),
		)
	);

	// To.
	$to = EDD()->html->date_field(
		array(
			'id'          => 'filter_to',
			'name'        => 'filter_to',
			'value'       => ( empty( $dates['to'] ) || ( 'other' !== $dates['range'] ) ) ? '' : $dates['to'],
			'placeholder' => _x( 'To', 'date filter', 'easy-digital-downloads' ),
		)
	);

	// Output fields
	?>
	<div class="edd-date-range-picker graph-option-section" data-range="<?php echo esc_attr( $selected_range ); ?>">
		<?php echo $range_select; ?>
		<!-- DATE RANGES -->
		<div class="edd-date-range-dates">
			<span class="dashicons dashicons-calendar edd-date-main-icon"></span>
			<div class="edd-date-range-selected-date">
				<?php
				foreach ( $range_options as $range_key => $range_name ) :
					$range_dates          = \EDD\Reports\parse_dates_for_range( $range_key );
					$selected_range_class = ( $selected_range !== $range_key ) ? 'hidden' : '';
					$start_date           = edd_get_edd_timezone_equivalent_date_from_utc( $range_dates['start'] )->format( $date_format );
					$end_date             = edd_get_edd_timezone_equivalent_date_from_utc( $range_dates['end'] )->format( $date_format );
					$label                = $start_date;
					if ( $start_date !== $end_date ) {
						$label = $start_date . ' - ' . $end_date;
					}
					?>
					<span class="<?php echo esc_attr( $selected_range_class ); ?>" data-range="<?php echo esc_attr( $range_key ); ?>" data-default-relative-range="<?php echo \EDD\Reports\get_default_relative_range( $range_key ); ?>"><?php echo esc_html( $label ); ?></span>
					<?php
				endforeach;
				?>
			</div>
		</div>
		<!-- RELATIVE DATE RANGES -->
		<div class="edd-date-range-relative-dates">
			<div class="hidden"><?php echo $relative_range_select; ?></div>

			<?php echo esc_html__( 'compared to', 'easy-digital-downloads' ); ?>

			<div class="edd-date-range-selected-relative-date">
				<span class="edd-date-range-selected-relative-range-name"><?php echo esc_html( $relative_range_options[ $selected_relative_range ] ); ?></span>
				<img src="<?php echo esc_url( EDD_PLUGIN_URL . '/assets/images/icons/icon-chevron-down.svg' ); ?>" class="arrow-down">

				<!-- RELATIVE DATE RANGES DROPDOWN -->
				<div class="edd-date-range-relative-dropdown">
					<?php echo \EDD\Reports\display_relative_dates_dropdown_options( $selected_range, $selected_relative_range ); ?>
				</div>
			</div>
		</div>
	</div>
	<span class="edd-date-range-options graph-option-section edd-from-to-wrapper<?php echo esc_attr( $class ); ?>">
		<?php echo $from . $to; ?>
	</span>
	<?php
}
/**
 * Handles display of the relative dates dropdown options.
 *
 * @since 3.1
 */
function display_relative_dates_dropdown_options( $range, $selected_relative_range ) {
	$date_format            = get_option( 'date_format' );
	$relative_range_options = get_relative_dates_filter_options();
	?>
	<ul data-range="<?php echo esc_attr( $range ); ?>">
		<?php
		foreach ( $relative_range_options as $relative_range_key => $relative_range_name ) :
			$relative_range_dates = \EDD\Reports\parse_relative_dates_for_range( $range, $relative_range_key );
			$selected_range_class = ( $selected_relative_range === $relative_range_key ) ? 'active' : '';
			?>
			<li class="<?php echo esc_attr( $selected_range_class ); ?>" data-range="<?php echo esc_attr( $relative_range_key ); ?>">
				<span class="date-range-name"><?php echo esc_html( $relative_range_options[ $relative_range_key ] ); ?></span>
				<span class="date-range-dates"><?php echo esc_html( edd_get_edd_timezone_equivalent_date_from_utc( $relative_range_dates['start'] )->format( $date_format ) ); ?> - <?php echo esc_html( edd_get_edd_timezone_equivalent_date_from_utc( $relative_range_dates['end'] )->format( $date_format ) ); ?></span>
			</li>
			<?php
		endforeach;
		?>
	</ul>
	<?php
}

/**
 * Handles display of the 'Products' filter for reports.
 *
 * @since 3.0
 */
function display_products_filter() {
	$products = get_filter_value( 'products' );

	$select   = EDD()->html->product_dropdown( array(
		'chosen'           => true,
		'variations'       => true,
		'selected'         => empty( $products ) ? 0 : $products,
		'show_option_none' => false,
		'show_option_all'  => sprintf( __( 'All %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
	) ); ?>

	<span class="edd-graph-filter-options graph-option-section"><?php
		echo $select;
	?></span><?php
}

/**
 * Handles display of the 'Products Dropdown' filter for reports.
 *
 * @since 3.0
 */
function display_product_categories_filter() {
	?>
	<span class="edd-graph-filter-options graph-option-selection">
		<?php echo EDD()->html->category_dropdown( 'product_categories', get_filter_value( 'product_categories' ) ); ?>
	</span>
	<?php
}

/**
 * Handles display of the 'Exclude Taxes' filter for reports.
 *
 * @since 3.0
 */
function display_taxes_filter() {
	if ( false === edd_use_taxes() ) {
		return;
	}

	$taxes         = get_filter_value( 'taxes' );
	$exclude_taxes = isset( $taxes['exclude_taxes'] ) && true == $taxes['exclude_taxes'];
?>
	<span class="edd-graph-filter-options graph-option-section">
		<label for="exclude_taxes">
			<input type="checkbox" id="exclude_taxes" <?php checked( true, $exclude_taxes, true ); ?> value="1" name="exclude_taxes"/>
			<?php esc_html_e( 'Exclude Taxes', 'easy-digital-downloads' ); ?>
		</label>
	</span>
<?php
}

/**
 * Handles display of the 'Discounts' filter for reports.
 *
 * @since 3.0
 */
function display_discounts_filter() {
	$discount = get_filter_value( 'discounts' );

	$d = edd_get_discounts( array(
		'fields' => array( 'code', 'name' ),
		'number' => 100,
		'status' => array( 'active', 'inactive', 'expired', 'archived' ),
	) );

	$discounts = array();

	foreach ( $d as $discount_data ) {
		$discounts[ $discount_data->code ] = esc_html( $discount_data->name );
	}

	// Get the select
	$select = EDD()->html->discount_dropdown( array(
		'name'     => 'discounts',
		'chosen'   => true,
		'selected' => empty( $discount ) ? 0 : $discount,
	) ); ?>

    <span class="edd-graph-filter-options graph-option-section"><?php
		echo $select;
	?></span><?php
}

/**
 * Handles display of the 'Gateways' filter for reports.
 *
 * @since 3.0
 */
function display_gateways_filter() {
	$gateway = get_filter_value( 'gateways' );

	$known_gateways = edd_get_payment_gateways();

	$gateways = array();

	foreach ( $known_gateways as $id => $data ) {
		$gateways[ $id ] = esc_html( $data['admin_label'] );
	}

	// Get the select
	$select = EDD()->html->select( array(
		'name'             => 'gateways',
		'options'          => $gateways,
		'selected'         => empty( $gateway ) ? 0 : $gateway,
		'show_option_none' => false,
	) ); ?>

    <span class="edd-graph-filter-options graph-option-section"><?php
		echo $select;
	?></span><?php
}

/**
 * Handles display of the 'Country' filter for reports.
 *
 * @since 3.0
 */
function display_region_filter() {
	$region  = get_filter_value( 'regions' );
	$country = get_filter_value( 'countries' );

	if ( empty( $region ) ) {
		$region = '';
	}
	if ( empty( $country ) ) {
		$country = '';
	}

	$regions = edd_get_shop_states( $country );

	// Remove empty values.
	$regions = array_filter( $regions );

	// Get the select
	$select = EDD()->html->region_select(
		array(
			'name'    => 'regions',
			'id'      => 'edd_reports_filter_regions',
			'options' => $regions,
		),
		$country,
		$region
	);
	?>

	<span class="edd-graph-filter-options graph-option-section"><?php
	echo $select;
	?></span><?php
}

/**
 * Handles display of the 'Country' filter for reports.
 *
 * @since 3.0
 */
function display_country_filter() {
	$country = get_filter_value( 'countries' );
	if ( empty( $country ) ) {
		$country = '';
	}

	$countries = edd_get_country_list();

	// Remove empty values.
	$countries = array_filter( $countries );

	// Get the select
	$select = EDD()->html->country_select(
		array(
			'name'    => 'countries',
			'id'      => 'edd_reports_filter_countries',
			'options' => $countries,
		),
		$country
	);
	?>

	<span class="edd-graph-filter-options graph-option-section"><?php
	echo $select;
	?></span><?php
}

/**
 * Handles the display of the 'Currency' filter for reports.
 *
 * @since 3.0
 */
function display_currency_filter() {
	$currency = get_filter_value( 'currencies' );
	if ( empty( $currency ) ) {
		$currency = 'all';
	}

	$order_currencies = get_transient( 'edd_distinct_order_currencies' );
	if ( false === $order_currencies ) {
		global $wpdb;

		$order_currencies = $wpdb->get_col(
			"SELECT distinct currency FROM {$wpdb->edd_orders}"
		);

		if ( is_array( $order_currencies ) ) {
			$order_currencies = array_filter( $order_currencies );
		}

		set_transient( 'edd_distinct_order_currencies', $order_currencies, 3 * HOUR_IN_SECONDS );
	}

	if ( ! is_array( $order_currencies ) || count( $order_currencies ) <= 1 ) {
		return;
	}

	$all_currencies = array_intersect_key( edd_get_currencies(), array_flip( $order_currencies ) );
	if ( array_key_exists( edd_get_currency(), $all_currencies ) ) {
		$all_currencies = array_merge( array(
			'convert' => sprintf( __( '%s - Converted', 'easy-digital-downloads' ), $all_currencies[ edd_get_currency() ] )
		), $all_currencies );
	}
	?>
	<span class="edd-graph-filter-options graph-option-section">
		<?php
		echo EDD()->html->select( array(
			'name'             => 'currencies',
			'id'               => 'edd_reports_filter_currencies',
			'options'          => $all_currencies,
			'selected'         => $currency,
			'show_option_all'  => false,
			'show_option_none' => false
		) );
		?>
	</span>
	<?php
}

/**
 * Displays the filters UI for a report.
 *
 * @since 3.0
 *
 * @param Data\Report $report Report object.
 */
function display_filters( $report ) {
	$action = edd_get_admin_url( array(
		'page' => 'edd-reports',
	) );
	?>

	<form action="<?php echo esc_url( $action ); ?>" method="GET">
		<?php edd_admin_filter_bar( 'reports', $report ); ?>
	</form>

	<?php
}

/**
 * Output filter items
 *
 * @since 3.0
 *
 * @param object $report
 */
function filter_items( $report = false ) {

	// Get the report ID
	$report_id = $report->get_id();

	// Bail if no report
	if ( empty( $report_id ) ) {
		return;
	}

	$redirect_url = edd_get_admin_url( array(
		'page' => 'edd-reports',
		'view' => sanitize_key( $report_id ),
	) );

	// Bail if no filters
	$filters  = $report->get_filters();
	if ( empty( $filters ) ) {
		return;
	}

	// Bail if no manifest
	$manifest = get_filters();
	if ( empty( $manifest ) ) {
		return;
	}

	// Setup callables
	$callables = array();

	// Loop through filters and find the callables
	foreach ( $filters as $filter ) {

		// Skip if empty
		if ( empty( $manifest[ $filter ]['display_callback'] ) ) {
			continue;
		}

		// Skip if not callable
		$callback = $manifest[ $filter ]['display_callback'];
		if ( ! is_callable( $callback ) ) {
			continue;
		}

		// Add callable to callables
		$callables[] = $callback;
	}

	// Bail if no callables
	if ( empty( $callables ) ) {
		return;
	}

	// Start an output buffer
	ob_start();

	// Call the callables in the buffer
	foreach ( $callables as $to_call ) {
		call_user_func( $to_call, $report );
	} ?>

	<span class="edd-graph-filter-submit graph-option-section">
		<input type="submit" class="button button-secondary" value="<?php esc_html_e( 'Filter', 'easy-digital-downloads' ); ?>"/>
		<input type="hidden" name="edd_action" value="filter_reports">
		<input type="hidden" name="edd_redirect" value="<?php echo esc_url_raw( $redirect_url ); ?>">
	</span>

	<?php

	// Output the current buffer
	echo ob_get_clean();
}
add_action( 'edd_admin_filter_bar_reports', 'EDD\Reports\filter_items' );

/**
 * Renders the mobile link at the bottom of the payment history page
 *
 * @since 1.8.4
 * @since 3.0 Updated filter to display link next to the reports filters.
*/
function mobile_link() {
	$url = edd_link_helper(
		'https://easydigitaldownloads.com/downloads/ios-app/',
		array(
			'utm_medium'  => 'reports',
			'utm_content' => 'ios-app',
		)
	);
	?>
	<span class="edd-mobile-link">
		<a href="<?php echo $url; ?>" target="_blank">
			<?php esc_html_e( 'Try the Sales/Earnings iOS App!', 'easy-digital-downloads' ); ?>
		</a>
	</span>
	<?php
}
add_action( 'edd_after_admin_filter_bar_reports', 'EDD\Reports\mobile_link', 100 );

/** Compat ********************************************************************/

/**
 * Private: Injects the value of $_REQUEST['range'] into the Reports\get_dates_filter_range() if set.
 *
 * To be used only for backward-compatibility with anything relying on the `$_REQUEST['range']` value.
 *
 * @since 3.0
 * @access private
 *
 * @param string $range Currently resolved dates range.
 * @return string (Maybe) modified range based on the value of `$_REQUEST['range']`.
 */
function compat_filter_date_range( $range ) {
	return isset( $_REQUEST['range'] )
		? sanitize_key( $_REQUEST['range'] )
		: $range;
}

/**
 * Gets a download label from a download data array.
 *
 * @since 3.2.8
 * @param array $download_data
 * @return string
 */
function get_download_label( $download_data = array() ) {
	if ( empty( $download_data ) ) {
		return '';
	}
	$download = edd_get_download( $download_data['download_id'] );
	if ( ! $download ) {
		return '';
	}

	if ( isset( $download_data['price_id'] ) && is_numeric( $download_data['price_id'] ) ) {
		$args       = array( 'price_id' => $download_data['price_id'] );
		$price_name = edd_get_price_name( $download->ID, $args );
		if ( $price_name ) {
			$download->post_title .= ': ' . $price_name;
		}
	}

	return esc_html( ' (' . $download->post_title . ')' );
}
