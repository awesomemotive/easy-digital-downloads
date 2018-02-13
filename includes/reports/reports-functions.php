<?php
/**
 * Reports API - Functions
 *
 * @package     EDD
 * @subpackage  Reports
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Reports;

/**
 * Retrieves the list of slug/label report tab pairs.
 *
 * @since 3.0
 *
 * @return array List of report tabs, otherwise an empty array.
 */
function get_tabs() {
	/** @var Data\Reports_Registry|\WP_Error $registry */
	$registry = EDD()->utils->get_registry( 'reports' );

	if ( is_wp_error( $registry ) ) {
		return array();
	} else {
		$registered_reports = $registry->get_reports( 'priority' );
	}

	$reports = array();

	foreach ( $registered_reports as $report_id => $attributes ) {
		$reports[ $report_id ] = $attributes['label'];
	}

	if ( has_filter( 'edd_report_views' ) ) {
		/**
		 * Filters legacy 'Reports' tab views.
		 *
		 * @since 1.4
		 * @deprecated 3.0 Use {@see 'edd_reports_get_tabs'}
		 * @see 'edd_reports_get_tabs'
		 *
		 * @param array $views 'Reports' tab views.
		 */
		$legacy_views = edd_apply_filters_deprecated( 'edd_report_views', array( array() ), '3.0', 'edd_reports_get_tabs' );

		$reports = array_merge( $reports, $legacy_views );
	}

	/**
	 * Filters the list of report tab slug/label pairs.
	 *
	 * @since 3.0
	 *
	 * @param array $reports List of slug/label pairs as representative of report tabs.
	 */
	return apply_filters( 'edd_reports_get_tabs', $reports );
}

/**
 * Retrieves the slug for the active report tab.
 *
 * @since 3.0
 *
 * @return string The active report tab, or the first tab if the 'tab' var is not defined.
 */
function get_active_tab() {

	$tabs = get_tabs();

	// If not set, default the active tab to the first one.
	return isset( $_REQUEST['tab'] ) ? sanitize_key( $_REQUEST['tab'] ) : key( $tabs );
}

/**
 * Retrieves the list of supported endpoint view types and their attributes.
 *
 * @since 3.0
 *
 * @return array List of supported endpoint types.
 */
function get_endpoint_views() {
	return array(
		'tile' => array(
			'group'          => 'tiles',
			'group_callback' => __NAMESPACE__ . '\\default_display_tiles_group',
			'handler'        => 'EDD\Reports\Data\Tile_Endpoint',
			'fields'         => array(
				'data_callback'    => '',
				'display_callback' => __NAMESPACE__ . '\\default_display_tile',
				'display_args'     => array(
					'type'             => '' ,
					'context'          => 'primary',
					'comparison_label' => __( 'All time', 'easy-digital-downloads' ),
				),
			),
		),
		'chart' => array(
			'group' => 'charts',
		),
		'table' => array(
			'group'          => 'tables',
			'group_callback' => __NAMESPACE__ . '\\default_display_tables_group',
			'handler'        => 'EDD\Reports\Data\Table_Endpoint',
			'fields'         => array(
				'display_callback' => 'display',
				'data_callback'    => 'prepare_items',
				'display_args'     => array(
					'class_name' => '',
					'class_file' => '',
				),
			),
		),
		'graph' => array(
			'group' => 'graphs',
		),
	);
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

	if ( isset( $views[ $view ]['handler'] ) ) {
		$handler = $views[ $view ]['handler'];
	} else {
		$handler = '';
	}

	return $handler;
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

	if ( isset( $views[ $view ]['group_callback'] ) ) {
		$callback = $views[ $view ]['group_callback'];
	} else {
		$callback = '';
	}

	return $callback;
}

/**
 * Determines whether an endpoint view is valid.
 *
 * @since 3.0
 *
 * @param string $view Endpoint view slug.
 * @return bool True if the view is valid, otherwise false.
 */
function validate_view( $view ) {
	return array_key_exists( $view, get_endpoint_views() );
}

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

	if ( is_wp_error( $registry ) ) {
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

	if ( is_wp_error( $registry ) ) {
		return $registry;
	}

	return $registry->build_endpoint( $endpoint_id, $view_type );
}

/**
 * Registers a new report.
 *
 * @since 3.0
 *
 * @see \EDD\Reports\Data\Reports_Registry::add_report()
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
	/** @var Data\Reports_Registry|\WP_Error $registry */
	$registry = EDD()->utils->get_registry( 'reports' );

	if ( is_wp_error( $registry ) ) {
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
 * @see \EDD\Reports\Data\Reports_Registry::build_report()
 *
 * @param string $report_id       Report ID.
 * @param bool   $build_endpoints Optional. Whether to build the endpoints (includes registering
 *                                any endpoint dependencies, such as registering meta boxes).
 *                                Default true.
 * @return Data\Report|\WP_Error Report object on success, otherwise a WP_Error object.
 */
function get_report( $report_id, $build_endpoints = true ) {
	/** @var Data\Reports_Registry|\WP_Error $registry */
	$registry = EDD()->utils->get_registry( 'reports' );

	if ( is_wp_error( $registry ) ) {
		return $registry;
	}

	return $registry->build_report( $report_id, $build_endpoints );
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

/**
 * Displays the default content for a tile endpoint.
 *
 * @since 3.0
 *
 * @param \EDD\Reports\Data\Report $report Report object the tile endpoint is being rendered in.
 *                                               Not always set.
 * @param array                          $args   Tile display arguments.
 * @return void Meta box display callbacks only echo output.
 */
function default_display_tile( $object, $tile ) {
	if ( ! isset( $tile['args'] ) ) {
		return;
	}

	if ( empty( $tile['args']['data'] ) ) {
		echo '<span class="tile-no-data tile-value">' . __( 'No data for the current date range.', 'easy-digital-downloads' ) . '</span>';
	} else {
		switch( $tile['args']['display_args']['type'] ) {
			case 'number':
				echo '<span class="tile-number tile-value">' . edd_format_amount( $tile['args']['data'] ) . '</span>';
				break;

			case 'split-number':
				printf( '<span class="tile-amount tile-value">%1$d / %2$d</span>',
					edd_format_amount( $tile['args']['data']['first_value'] ),
					edd_format_amount( $tile['args']['data']['second_value'] )
				);
				break;

			case 'split-amount':
				printf( '<span class="tile-amount tile-value">%1$d / %2$d</span>',
					edd_currency_filter( edd_format_amount( $tile['args']['data']['first_value'] ) ),
					edd_currency_filter( edd_format_amount( $tile['args']['data']['second_value'] ) )
				);
				break;

			case 'amount':
				echo '<span class="tile-amount tile-value">' . edd_currency_filter( edd_format_amount( $tile['args']['data'] ) ) . '</span>';
				break;

			case 'url':
				echo '<span class="tile-url tile-value">' . esc_url( $tile['args']['data'] ) . '</span>';
				break;

			default:
				echo '<span class="tile-value">' . esc_html( $tile['args']['data'] ) . '</span>';
				break;
		}
	}

	if ( ! empty( $tile['args']['display_args']['comparison_label'] ) ) {
		echo '<span class="tile-compare">' . $tile['args']['display_args']['comparison_label'] . '</span>';
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
	if ( $report->has_endpoints( 'tiles' ) ) : ?>

		<div id="edd-reports-tiles-wrap">
			<h3><?php _e( 'Quick Stats', 'easy-digital-downloads' ); ?></h3>

			<div id="dashboard-widgets" class="metabox-holder">

				<div class="postbox-container">
					<?php do_meta_boxes( 'download_page_edd-reports', 'primary', $report ); ?>
				</div>

				<div class="postbox-container">
					<?php do_meta_boxes( 'download_page_edd-reports', 'secondary', $report ); ?>
				</div>

				<div class="postbox-container">
					<?php do_meta_boxes( 'download_page_edd-reports', 'tertiary', $report ); ?>
				</div>

			</div>
		</div>
	<?php endif; // Has endpoints.
}

/**
 * Handles default display of all table endpoints registered against a report.
 *
 * @since 3.0
 *
 * @param Data\Report $report Report object.
 */
function default_display_tables_group( $report ) {
	if ( $report->has_endpoints( 'tables' ) ) :
		$tables = $report->get_endpoints( 'tables' );
		?>
		<div id="edd-reports-tables-wrap">

			<?php foreach ( $tables as $endpoint_id => $table ) : ?>
				<h3><?php echo esc_html( $table->get_label() ); ?></h3>

				<?php $table->display(); ?>
			<?php endforeach; ?>

		</div>
	<?php endif; // Has endpoints.
}

/**
 * Retrieves the list of supported endpoint filters and their attributes.
 *
 * @since 3.0
 *
 * @return array List of supported endpoint filters.
 */
function get_endpoint_filters() {
	return array(
		'dates'    => array(
			'label'            => __( 'Date', 'easy-digital-downloads' ),
			'session_var'      => 'reports:date',
			'display_callback' => __NAMESPACE__ . '\\display_dates_filter',
		),
		'products' => array(
			'label'            => __( 'Products', 'easy-digital-downloads' ),
			'session_var'      => 'reports:products',
			'display_callback' => __NAMESPACE__ . '\\display_products_filter',
		),
		'taxes'    => array(
			'label'            => __( 'Exclude Taxes', 'easy-digital-downloads' ),
			'session_var'      => 'reports:taxes',
			'display_callback' => __NAMESPACE__ . '\\display_taxes_filter',
		),
	);
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
	return array_key_exists( $filter, get_endpoint_filters() );
}

/**
 * Retrieves the value of an endpoint filter for the current session and tab.
 *
 * @since 3.0
 *
 * @param string $filter    Filter key to retrieve the value for.
 * @param string $report_id Report ID to retrieve the filter value for.
 * @return mixed|string Value of the filter if it exists, otherwise an empty string.
 */
function get_filter_value( $filter, $report_id ) {
	$value = '';

	if ( validate_filter( $filter ) ) {

		$filter_value = EDD()->session->get( "{$report_id}:{$filter}" );

		if ( false !== $filter_value ) {
			$value = $filter_value;
		}
	}

	return $value;
}

/**
 * Retrieves key/label pairs of date filter options for use in a drop-down.
 *
 * @since 3.0
 *
 * @return array Key/label pairs of date filter options.
 */
function get_dates_filter_options() {
	$date_options = array(
		'today'        => __( 'Today', 'easy-digital-downloads' ),
		'yesterday'    => __( 'Yesterday', 'easy-digital-downloads' ),
		'this_week'    => __( 'This Week', 'easy-digital-downloads' ),
		'last_week'    => __( 'Last Week', 'easy-digital-downloads' ),
		'last_30_days' => __( 'Last 30 Days', 'easy-digital-downloads' ),
		'this_month'   => __( 'This Month', 'easy-digital-downloads' ),
		'last_month'   => __( 'Last Month', 'easy-digital-downloads' ),
		'this_quarter' => __( 'This Quarter', 'easy-digital-downloads' ),
		'last_quarter' => __( 'Last Quarter', 'easy-digital-downloads' ),
		'this_year'    => __( 'This Year', 'easy-digital-downloads' ),
		'last_year'    => __( 'Last Year', 'easy-digital-downloads' ),
		'other'        => __( 'Custom', 'easy-digital-downloads' )
	);

	/**
	 * Filters the list of key/label pairs of date filter options.
	 *
	 * @since 1.3
	 *
	 * @param array $date_options Date filter options.
	 */
	return apply_filters( 'edd_report_date_options', $date_options );
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
function get_dates_filter( $values = 'strings', $timezone = '' ) {
	$date  = EDD()->utils->date( 'now', $timezone );
	$dates = parse_dates_for_range( $date );

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
	 *                                         If `$values` is 'objects', a Carbon object, otherwise a date
	 *                                         time string.
	 *     @type string|\EDD\Utils\Date $end   End day and time (based on the end of the given day). If `$values`
	 *                                         is 'objects', a Carbon object, otherwise a date time string.
	 * }
	 */
	return apply_filters( 'edd_get_dates_filter', $dates );
}

/**
 * Parses start and end dates for the given range.
 *
 * @since 3.0
 *
 * @param \EDD\Utils\Date $date  Date object.
 * @param string          $range Optional. Range value to generate start and end dates for against `$date`.
 *                               Default is the current range as derived from the session.
 * @return \EDD\Utils\Date[] Array of start and end date objects.
 */
function parse_dates_for_range( $date, $range = null ) {

	if ( null === $range ) {
		$range = get_dates_filter_range();
	}

	switch( $range ) {

		case 'this_month':
			$dates = array(
				'start' => $date->copy()->startOfMonth(),
				'end'   => $date->copy()->endOfMonth(),
			);
			break;

		case 'last_month':
			$dates = array(
				'start' => $date->copy()->subMonth( 1 )->startOfMonth(),
				'end'   => $date->copy()->subMonth( 1 )->endOfMonth(),
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
				'end'   => $date->copy()->endOfWeek(),
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
				'end'   => $date->copy()->endOfQuarter(),
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
				'end'   => $date->copy()->endOfYear(),
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
			$filter_dates = get_dates_filter_values( true );

			$dates = array(
				'start' => EDD()->utils->date( $filter_dates['start'] )->startOfDay(),
				'end'   => EDD()->utils->date( $filter_dates['end'] )->endOfDay(),
			);
			break;
	}

	return $dates;
}

/**
 * Retrieves values of the filter_from and filter_to request variables.
 *
 * @since 3.0
 *
 * @param bool $now Optional. Whether to default to 'now' when retrieving empty values. Default false.
 * @return array {
 *     Query date range for the current date filter request.
 *
 *     @type string $start Start day and time string based on the WP timezone.
 *     @type string $end   End day and time string based on the WP timezone.
 * }
 */
function get_dates_filter_values( $now = false ) {
	if ( true === $now ) {
		$default = 'now';
	} else {
		$default = '';
	}
	$values = array(
		'start' => empty( $_REQUEST['filter_from'] ) ? $default : $_REQUEST['filter_from'],
		'end'   => empty( $_REQUEST['filter_to'] )   ? $default : $_REQUEST['filter_to']
	);
	/**
	 * Filters the start and end filter date values for a Graph API request.
	 *
	 * @since 3.0
	 *
	 * @param array {
	 *     Query date range for the current date filter request.
	 *
	 *     @type string $start Start day and time string based on the WP timezone.
	 *     @type string $end   End day and time string based on the WP timezone.
	 * }
	 * @param string $default The fallback value if 'filter_from' and/or 'filter_to' `$_REQUEST`
	 *                        values are empty. If `$now` is true, will be 'now', otherwise empty.
	 */
	return apply_filters( 'edd_get_dates_filter_values', $values, $default );
}

/**
 * Retrieves the date filter range.
 *
 * @since 3.0
 *
 * @return string Date filter range.
 */
function get_dates_filter_range() {
	if ( isset( $_REQUEST['range'] ) ) {
		$range = sanitize_key( $_REQUEST['range'] );
	} else {
		$range = 'last_30_days';
	}

	/**
	 * Filters the report dates default range.
	 *
	 * @since 1.3
	 *
	 * @param string $range Date range as derived from the 'range' request var.
	 *                      Default 'last_30_days'
	 */
	return apply_filters( 'edd_get_report_dates_default_range', $range );
}

/**
 * Handles display of the 'Date' filter for reports.
 *
 * @since 3.0
 *
 * @param Data\Report $report Report object.
 * @return void
 */
function display_dates_filter( $report ) {
	$options = get_dates_filter_options();
	$dates   = get_filter_value( 'dates', $report->get_id() );
	$range   = get_dates_filter_range();
	$class   = $range === 'other' ? '' : 'screen-reader-text';
	?>
	<select id="edd-graphs-date-options" name="range">
		<?php foreach ( $options as $key => $label ) : ?>
			<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $key, $range ); ?>><?php echo esc_html( $label ); ?></option>
		<?php endforeach; ?>
	</select>

	<div id="edd-date-range-options" class="<?php echo esc_attr( $class ); ?>">
		<span class="edd-search-date">
			<?php
			// From.
			echo EDD()->html->date_field( array(
				'id'          => 'filter_from',
				'name'        => 'filter_from',
				'value'       => empty( $dates['from'] ) ? '' : $dates['from'],
				'label'       => _x( 'From', 'date filter', 'easy-digital-downloads' ),
				'placeholder' => __( 'mm/dd/yyyy', 'easy-digital-downloads' ),
			) );

			// To.
			echo EDD()->html->date_field( array(
				'id'          => 'filter_to',
				'name'        => 'filter_to',
				'value'       => empty( $dates['to'] ) ? '' : $dates['to'],
				'label'       => _x( 'To', 'date filter', 'easy-digital-downloads' ),
				'placeholder' => __( 'mm/dd/yyyy', 'easy-digital-downloads' ),
			) );
			?>
	</div>
	<?php
}

/**
 * Handles display of the 'Products' filter for reports.
 *
 * @since 3.0
 *
 * @param Data\Report $report Report object.
 * @return void
 */
function display_products_filter( $report ) {

}

/**
 * Handles display of the 'Exclude Taxes' filter for reports.
 *
 * @since 3.0
 *
 * @param Data\Report $report Report object.
 * @return void
 */
function display_taxes_filter( $report ) {
	$taxes = get_filter_value( 'taxes', $report->get_id() );
	?>
	<div class="edd-graph-filter-options graph-option-section">
		<input type="checkbox" id="exclude_taxes" <?php checked( false, $taxes, true ); ?> value="1" name="exclude_taxes" />
		<label for="exclude_taxes"><?php _e( 'Exclude Taxes', 'easy-digital-downloads' ); ?></label>
	</div>
	<?php
}

/**
 * Handles display of a report.
 *
 * @since 3.0
 *
 * @param Data\Report $report Report object.
 */
function default_display_report( $report ) {

	if ( ! is_wp_error( $report ) ) :

		display_filters( $report );

		$report->display_endpoint_group( 'tiles' );

		$report->display_endpoint_group( 'tables' );

	endif; // WP_Error.

	// Back-compat.
	$active_tab = get_active_tab();

	if ( has_action( "edd_reports_tab_{$active_tab}" ) ) {

		/**
		 * Legacy: Fires inside the content area of the currently active Reports tab.
		 *
		 * The dynamic portion of the hook name, `$active_tab` refers to the slug of
		 * the current reports tab.
		 *
		 * @since 1.0
		 * @deprecated 3.0 Use the new Reports API to register new tabs.
		 * @see \EDD\Reports\add_report()
		 *
		 * @param \EDD\Reports\Data\Report $report Current report object.
		 */
		edd_do_action_deprecated( "edd_reports_tab_{$active_tab}", array( $report ), '3.0', '\EDD\Reports\add_report' );

	} elseif ( has_action( "edd_reports_view_{$active_tab}" ) ) {

		/**
		 * Legacy: Fires inside the content area of the currently active Reports tab
		 * (formerly reviewed to as a 'view' inside the global 'Reports' tab).
		 *
		 * The dynamic portion of the hook name, `$active_tab` refers to the slug of
		 * the current reports tab.
		 *
		 * @since 1.0
		 * @deprecated 3.0 Use the new Reports API to register new tabs.
		 * @see \EDD\Reports\add_report()
		 *
		 * @param \EDD\Reports\Data\Report $report Current report object.
		 */
		edd_do_action_deprecated( "edd_reports_view_{$active_tab}", array( $report ), '3.0', '\EDD\Reports\add_report' );

	}
}

/**
 * Displays the filters UI for a report.
 *
 * @since 3.0
 *
 * @param Data\Report $report Report object.
 */
function display_filters( $report ) {
	$filters  = $report->get_filters();
	$manifest = get_endpoint_filters();

	if ( ! empty( $filters ) ) : ?>
		<style type="text/css">
			#edd-item-card-wrapper > div {
				overflow: auto;
				margin-bottom: 20px;
			}
		</style>
		<div id="edd-reports-filters-wrap">
			<h3><?php _e( 'Filters', 'easy-digital-downloads' ); ?></h3>

			<?php foreach ( $filters as $filter ) : ?>
				<?php
				if ( ! empty( $manifest[ $filter ]['display_callback'] ) ) :
					$callback = $manifest[ $filter ]['display_callback'];

					if ( is_callable( $callback ) ) :
						call_user_func( $callback, $report );
					endif;
				endif;
				?>
			<?php endforeach; ?>
		</div>
	<?php endif;
}
