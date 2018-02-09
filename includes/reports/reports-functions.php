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
	/** @var \EDD\Reports\Data\Reports_Registry|\WP_Error $registry */
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
	return isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : key( $tabs );
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
	$handler = '';

	$views = get_endpoint_views();

	if ( isset( $views[ $view ]['handler'] ) ) {
		$handler = $views[ $view ]['handler'];
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
	$callback = '';

	$views = get_endpoint_views();

	if ( isset( $views[ $view ]['group_callback'] ) ) {
		$callback = $views[ $view ]['group_callback'];
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
function is_view_valid( $view ) {
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
	/** @var \EDD\Reports\Data\Endpoint_Registry|\WP_Error $registry */
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
 * @return \EDD\Reports\Data\Endpoint|\WP_Error Endpoint object on success, otherwise a WP_Error object.
 */
function get_endpoint( $endpoint_id, $view_type ) {
	/** @var \EDD\Reports\Data\Endpoint_Registry|\WP_Error $registry */
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
	/** @var \EDD\Reports\Data\Reports_Registry|\WP_Error $registry */
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
 * @return \EDD\Reports\Data\Report|\WP_Error Report object on success, otherwise a WP_Error object.
 */
function get_report( $report_id, $build_endpoints = true ) {
	/** @var \EDD\Reports\Data\Reports_Registry|\WP_Error $registry */
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
 * @param \EDD\Reports\Data\Report $report Report object.
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
 * @param \EDD\Reports\Data\Report $report Report object.
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
 * Handles display of a report.
 *
 * @since 3.0
 *
 * @param Data\Report $report Report object.
 */
function default_display_report( $report ) {

	if ( ! is_wp_error( $report ) ) :

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
