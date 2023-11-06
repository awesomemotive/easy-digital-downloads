<?php
/**
 * Order Stats class.
 *
 * @package     EDD
 * @subpackage  Orders
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD;

use EDD\Reports as Reports;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Stats Class.
 *
 * @since 3.0
 */
class Stats {

	/**
	 * Parsed query vars.
	 *
	 * @since 3.0
	 * @access protected
	 * @var array
	 */
	protected $query_vars = array();

	/**
	 * Query var originals. These hold query vars passed to the constructor.
	 *
	 * @since 3.0
	 * @access protected
	 * @var array
	 */
	protected $query_var_originals = array();

	/**
	 * Date ranges.
	 *
	 * @since 3.0
	 * @access protected
	 * @var array
	 */
	protected $date_ranges = array();

	/**
	 * Date ranges used when calculating percentage difference.
	 *
	 * @since 3.0
	 * @access protected
	 * @var array
	 */
	protected $relative_date_ranges = array();

	/**
	 * Query vars defaults.
	 *
	 * @since 3.0
	 * @access protected
	 * @var array
	 */
	protected $query_vars_defaults = array();

	/**
	 * Constructor.
	 *
	 * @since 3.0
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class. Some methods will not allow parameters to be overridden as it could lead to inaccurate calculations.
	 *
	 *     @type string $start         Start day and time (based on the beginning of the given day).
	 *     @type string $end           End day and time (based on the end of the given day).
	 *     @type string $range         Date range. If a range is passed, this will override and `start` and `end`
	 *                                 values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type bool   $exclude_taxes If taxes should be excluded from calculations. Default `false`.
	 *     @type string $function      SQL function. Certain methods will only accept certain functions. See each method for
	 *                                 a list of accepted SQL functions.
	 *     @type string $where_sql     Reserved for internal use. Allows for additional WHERE clauses to be appended to the
	 *                                 query.
	 *     @type string $output        The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 */
	public function __construct( $query = array() ) {

		// Start the Reports API.
		new Reports\Init();

		// Set date ranges.
		$this->set_date_ranges();

		// Maybe parse query.
		if ( ! empty( $query ) ) {
			$this->parse_query( $query );

			$this->query_var_originals = $this->query_vars;

		// Set defaults.
		} else {
			$this->query_var_originals = $this->query_vars = array(
				'start'             => '',
				'end'               => '',
				'range'             => '',
				'exclude_taxes'     => false,
				'currency'          => false,
				'currency_sql'      => '',
				'status'            => array(),
				'status_sql'        => '',
				'type'              => array(),
				'type_sql'          => '',
				'where_sql'         => '',
				'date_query_sql'    => '',
				'date_query_column' => '',
				'column'            => '',
				'table'             => '',
				'function'          => 'SUM',
				'output'            => 'raw',
				'relative'          => false,
				'relative_start'    => '',
				'relative_end'      => '',
				'grouped'           => false,
				'product_id'        => '',
				'price_id'          => null,
				'revenue_type'      => 'gross',
			);
		}

	}

	/**
	 * Builds a fully qualified amount column and function, given the currency settings,
	 * tax settings, and accepted functions.
	 *
	 * @param array $args              {
	 *                                 Optional arguments.
	 *
	 * @type string $column_prefix     Column prefix (table alias or name).
	 * @type array  $accepted_function Accepted functions for this query.
	 *                    }
	 *
	 * @return string Example: `SUM( total / rate )`
	 * @throws \InvalidArgumentException
	 */
	private function get_amount_column_and_function( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'column_prefix'      => '',
			'accepted_functions' => array(),
			'requested_function' => false,
			'rate'               => true,
		) );

		$column = $this->query_vars['column'];
		$column_prefix = '';

		if ( ! empty( $args['column_prefix'] ) ) {
			$column_prefix = $args['column_prefix'] . '.';
		}

		if ( empty( $column ) ) {
			$column = true === $this->query_vars['exclude_taxes'] ?  "{$column_prefix}total - {$column_prefix}tax" : $column_prefix . 'total';
		} else {
			$column = $column_prefix . $column;
		}

		$default_function = is_array( $args['accepted_functions'] ) && isset( $args['accepted_functions'][0] ) ? $args['accepted_functions'][0] : false;
		$function = ! empty( $this->query_vars['function'] ) ? $this->query_vars['function'] : $default_function;

		if ( ! empty( $args['requested_function'] ) ) {
			$function = $args['requested_function'];
		}

		if ( empty( $function ) ) {
			throw new \InvalidArgumentException( 'Missing select function.' );
		}

		if ( ! empty( $args['accepted_functions'] ) && ! in_array( strtoupper( $function ), $args['accepted_functions'], true ) ) {
			if ( ! empty( $default_function ) ) {
				$function = $default_function;
			} else {
				throw new \InvalidArgumentException( sprintf( 'Invalid function "%s". Must be one of: %s', $this->query_vars['function'], json_encode( $args['accepted_functions'] ) ) );
			}
		}

		$function = strtoupper( $function );

		// Multiply by rate if currency conversion is enabled.
		if (
			! empty( $args['rate'] ) &&
			in_array( $function, array( 'SUM', 'AVG' ), true ) &&
			( empty( $this->query_vars['currency'] ) || 'convert' === $this->query_vars['currency'] ) &&
			( false !== strpos( $column, 'total' ) || false !== strpos( $column, 'tax' ) )
		) {
			$column = sprintf( '(%s) / %s', $column, $column_prefix . 'rate' );
		}

		return sprintf( '%s(%s)', $function, $column );
	}

	/** Calculation Methods ***************************************************/

	/** Orders ***************************************************************/

	/**
	 * Calculate order earnings.
	 *
	 * @since 3.0
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class.
	 *
	 *     @type string $start         Start day and time (based on the beginning of the given day).
	 *     @type string $end           End day and time (based on the end of the given day).
	 *     @type string $range         Date range. If a range is passed, this will override and `start` and `end`
	 *                                 values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type bool   $exclude_taxes If taxes should be excluded from calculations. Default `false`.
	 *     @type string $function      SQL function. Accepts `SUM` and `AVG`. Default `SUM`.
	 *     @type string $where_sql     Reserved for internal use. Allows for additional WHERE clauses to be appended to the
	 *                                 query.
	 *     @type string $output        The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 *
	 * @return string Formatted order earnings.
	 */
	public function get_order_earnings( $query = array() ) {
		$this->parse_query( $query );

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_orders;
		$this->query_vars['date_query_column'] = 'date_created';

		if ( empty( $this->query_vars['function'] ) ) {
			$this->query_vars['function'] = 'SUM';
		}

		/*
		 * By default we're checking sales only and excluding refunds. This gives us gross order earnings.
		 * This may be overridden in $query parameters that get passed through.
		 */
		$this->query_vars['type']   = $this->get_revenue_type_order_types();
		$this->query_vars['status'] = edd_get_gross_order_statuses();

		/**
		 * Filters Order statuses that should be included when calculating stats.
		 *
		 * @since 2.7
		 *
		 * @param array $statuses Order statuses to include when generating stats.
		 */
		$this->query_vars['status'] = array_unique( apply_filters( 'edd_payment_stats_post_statuses', $this->query_vars['status'] ) );

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$function = $this->get_amount_column_and_function( array(
			'accepted_functions' => array( 'SUM', 'AVG' )
		) );

		$initial_query = "SELECT {$function} AS total
			FROM {$this->query_vars['table']}
			WHERE 1=1
			{$this->query_vars['status_sql']}
			{$this->query_vars['type_sql']}
			{$this->query_vars['currency_sql']}
			{$this->query_vars['where_sql']}
			{$this->query_vars['date_query_sql']}";

		$initial_result = $this->get_db()->get_row( $initial_query );

		if ( true === $this->query_vars['relative'] ) {

			$relative_date_query_sql = $this->generate_relative_date_query_sql();

			$relative_query = "SELECT {$function} AS total
				FROM {$this->query_vars['table']}
				WHERE 1=1
				{$this->query_vars['status_sql']}
				{$this->query_vars['type_sql']}
				{$this->query_vars['currency_sql']}
				{$this->query_vars['where_sql']}
				{$relative_date_query_sql}";


			$relative_result = $this->get_db()->get_row( $relative_query );
		}

		$total = null === $initial_result->total
			? 0.00
			: (float) $initial_result->total;

		if ( 'array' === $this->query_vars['output'] ) {
			$output = array(
				'value'         => $total,
				'relative_data' => ( true === $this->query_vars['relative'] ) ? $this->generate_relative_data( floatval( $total ), floatval( $relative_result->total ) ) : array(),
			);
		} else {
			if ( true === $this->query_vars['relative'] ) {
				$output = $this->generate_relative_markup( floatval( $total ), floatval( $relative_result->total ) );
			} else {
				$output = $this->maybe_format( $total );
			}
		}

		// Reset query vars.
		$this->post_query();

		return $output;
	}

	/**
	 * Calculate the number of orders.
	 *
	 * @since 3.0
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class.
	 *
	 *     @type string $start     Start day and time (based on the beginning of the given day).
	 *     @type string $end       End day and time (based on the end of the given day).
	 *     @type string $range     Date range. If a range is passed, this will override and `start` and `end`
	 *                             values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type string $function  SQL function. Accepts `COUNT` and `AVG`. Default `COUNT`.
	 *     @type string $where_sql Reserved for internal use. Allows for additional WHERE clauses to be appended to the
	 *                             query.
	 *     @type string $output    The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 *
	 * @return int Number of orders.
	 */
	public function get_order_count( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_orders;
		$this->query_vars['column']            = 'id';
		$this->query_vars['date_query_column'] = 'date_created';

		/*
		 * By default we're checking sales only and excluding refunds. This gives us gross order counts.
		 * This may be overridden in $query parameters that get passed through.
		 */
		$this->query_vars['type']   = 'sale';
		$this->query_vars['status'] = $this->get_revenue_type_statuses();

		/**
		 * Filters Order statuses that should be included when calculating stats.
		 *
		 * @since 2.7
		 *
		 * @param array $statuses Order statuses to include when generating stats.
		 */
		$this->query_vars['status'] = apply_filters( 'edd_payment_stats_post_statuses', $this->query_vars['status'] );

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$function = $this->get_amount_column_and_function( array(
			'accepted_functions' => array( 'COUNT', 'AVG' )
		) );

		// First get the 'current' date filter's results.
		$initial_query = "SELECT COUNT(id) AS total
			FROM {$this->query_vars['table']}
			WHERE 1=1
			{$this->query_vars['status_sql']}
			{$this->query_vars['type_sql']}
			{$this->query_vars['currency_sql']}
			{$this->query_vars['where_sql']}
			{$this->query_vars['date_query_sql']}";

		$initial_result = $this->get_db()->get_row( $initial_query );

		if ( true === $this->query_vars['relative'] ) {

			// Now get the relative data.
			$relative_date_query_sql = $this->generate_relative_date_query_sql();

			$relative_query = "SELECT COUNT(id) AS total
				FROM {$this->query_vars['table']}
				WHERE 1=1
				{$this->query_vars['status_sql']}
				{$this->query_vars['type_sql']}
				{$this->query_vars['currency_sql']}
				{$this->query_vars['where_sql']}
				{$relative_date_query_sql}";

			$relative_result = $this->get_db()->get_row( $relative_query );
		}

		$total = null === $initial_result
			? 0
			: absint( $initial_result->total );

		if ( true === $this->query_vars['relative'] ) {
			$total = $this->generate_relative_markup( absint( $total ), absint( $relative_result->total ) );
		}

		// Reset query vars.
		$this->post_query();

		return $total;
	}

	/**
	 * Calculate the busiest day of the week for stores.
	 *
	 * @since 3.0
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class.
	 *
	 *     @type string $start     Start day and time (based on the beginning of the given day).
	 *     @type string $end       End day and time (based on the end of the given day).
	 *     @type string $range     Date range. If a range is passed, this will override and `start` and `end`
	 *                             values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type string $function  This method does not allow any SQL functions to be passed.
	 *     @type string $where_sql Reserved for internal use. Allows for additional WHERE clauses to be appended to the
	 *                             query.
	 *     @type string $output    The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 *
	 * @return string Busiest day of the week.
	 */
	public function get_busiest_day( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_orders;
		$this->query_vars['column']            = 'id';
		$this->query_vars['date_query_column'] = 'date_created';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$sql = "SELECT DAYOFWEEK(date_created) AS day, COUNT({$this->query_vars['column']}) as total
				FROM {$this->query_vars['table']}
				WHERE 1=1 {$this->query_vars['status_sql']} {$this->query_vars['currency_sql']} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}
				GROUP BY day
				ORDER BY total DESC
				LIMIT 1";

		$result = $this->get_db()->get_row( $sql );

		$days = array(
			__( 'Sunday', 'easy-digital-downloads' ),
			__( 'Monday', 'easy-digital-downloads' ),
			__( 'Tuesday', 'easy-digital-downloads' ),
			__( 'Wednesday', 'easy-digital-downloads' ),
			__( 'Thursday', 'easy-digital-downloads' ),
			__( 'Friday', 'easy-digital-downloads' ),
			__( 'Saturday', 'easy-digital-downloads' ),
		);

		$day = null === $result
			? ''
			: $days[ $result->day - 1 ];

		// Reset query vars.
		$this->post_query();

		return $day;
	}

	/**
	 * Calculate number of refunded orders.
	 *
	 * @since 3.0
	 *
	 * @see \EDD\Stats::get_order_count()
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class.
	 *
	 *     @type string $start     Start day and time (based on the beginning of the given day).
	 *     @type string $end       End day and time (based on the end of the given day).
	 *     @type string $range     Date range. If a range is passed, this will override and `start` and `end`
	 *                             values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type string $function  SQL function. Accepts `COUNT` and `AVG`. Default `COUNT`.
	 *     @type string $where_sql Reserved for internal use. Allows for additional WHERE clauses to be appended to the
	 *                             query.
	 *     @type string $output    The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 *
	 * @return int Number of refunded orders.
	 */
	public function get_order_refund_count( $query = array() ) {
		$query['status'] = isset( $query['status'] )
			? $query['status']
			: array( 'complete' );

		if ( ! array( $query['status'] ) ) {
			$query['status'] = array( $query['status'] );
		}

		$query['type'] = array( 'refund' );

		return $this->get_order_count( $query );
	}

	/**
	 * Calculate number of refunded order items.
	 *
	 * @since 3.0
	 *
	 * @see \EDD\Stats::get_order_item_count()
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class.
	 *
	 *     @type string $start     Start day and time (based on the beginning of the given day).
	 *     @type string $end       End day and time (based on the end of the given day).
	 *     @type string $range     Date range. If a range is passed, this will override and `start` and `end`
	 *                             values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type string $function  SQL function. Accepts `COUNT` and `AVG`. Default `COUNT`.
	 *     @type string $where_sql Reserved for internal use. Allows for additional WHERE clauses to be appended to the
	 *                             query.
	 *     @type string $output    The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 *
	 * @return int Number of refunded orders.
	 */
	public function get_order_item_refund_count( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_order_items;
		$this->query_vars['column']            = 'id';
		$this->query_vars['date_query_column'] = 'date_created';

		if ( empty( $this->query_vars['function'] ) ) {
			$this->query_vars['function'] = 'COUNT';
		}

		// Base value for status.
		$query['status'] = isset( $query['status'] )
			? $query['status']
			: array( 'refunded' );

		/*
		 * The type should be `sale` because we're querying for fully refunded order items only.
		 * That means we look in `type` = `sale` and `status` = `refunded`.
		 */
		$this->query_vars['where_sql'] .= " AND {$this->get_db()->edd_orders}.type = 'sale' ";

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$function = $this->get_amount_column_and_function( array(
			'column_prefix'      => $this->query_vars['table'],
			'accepted_functions' => array( 'COUNT', 'AVG' )
		) );

		$product_id = ! empty( $this->query_vars['product_id'] )
			? $this->get_db()->prepare( 'AND product_id = %d', absint( $this->query_vars['product_id'] ) )
			: '';

		$price_id = $this->generate_price_id_query_sql();

		$currency_sql = str_replace( $this->get_db()->edd_order_items, $this->get_db()->edd_orders, $this->query_vars['currency_sql'] );

		// Calculating an average requires a subquery.
		if ( 'AVG' === $this->query_vars['function'] ) {
			$sql = "SELECT AVG(id) AS total
					FROM (
						SELECT COUNT({$this->query_vars['table']}.id) AS id
						FROM {$this->query_vars['table']}
						INNER JOIN {$this->get_db()->edd_orders} ON( {$this->get_db()->edd_orders}.id = {$this->query_vars['table']}.order_id )
						WHERE 1=1 {$product_id} {$price_id} {$this->query_vars['status_sql']} {$currency_sql} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}
						GROUP BY order_id
					) AS counts";
		} elseif ( true === $this->query_vars['grouped'] ) {
			$sql = "SELECT {$this->query_vars['table']}.product_id, {$this->query_vars['table']}.price_id, {$function} AS total
					FROM {$this->query_vars['table']}
					INNER JOIN {$this->get_db()->edd_orders} ON( {$this->get_db()->edd_orders}.id = {$this->query_vars['table']}.order_id )
					WHERE 1=1 {$product_id} {$price_id} {$this->query_vars['status_sql']} {$currency_sql} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}
					GROUP BY product_id, price_id
					ORDER BY total DESC";
		} else {
			$sql = "SELECT {$function} AS total
					FROM {$this->query_vars['table']}
					INNER JOIN {$this->get_db()->edd_orders} ON( {$this->get_db()->edd_orders}.id = {$this->query_vars['table']}.order_id )
					WHERE 1=1 {$product_id} {$price_id} {$this->query_vars['status_sql']} {$currency_sql} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}";
		}

		$result = $this->get_db()->get_results( $sql );

		if ( true === $this->query_vars['grouped'] ) {
			array_walk( $result, function ( &$value ) {

				// Format resultant object.
				$value->product_id = absint( $value->product_id );
				$value->price_id   = is_numeric( $value->price_id ) ? absint( $value->price_id ) : null;
				$value->total      = absint( $value->total );

				// Add instance of EDD_Download to resultant object.
				$value->object = edd_get_download( $value->product_id );
			} );
		} else {
			$result = null === $result[0]->total
				? 0.00
				: absint( $result[0]->total );
		}

		// Reset query vars.
		$this->post_query();

		return $result;

		return $this->get_order_item_count( $query );
	}

	/**
	 * Calculate total amount for refunded orders.
	 *
	 * @since 3.0
	 *
	 * @see \EDD\Stats::get_order_earnings()
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class.
	 *
	 *     @type string $start         Start day and time (based on the beginning of the given day).
	 *     @type string $end           End day and time (based on the end of the given day).
	 *     @type string $range         Date range. If a range is passed, this will override and `start` and `end`
	 *                                 values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type bool   $exclude_taxes If taxes should be excluded from calculations. Default `false`.
	 *     @type string $function      SQL function. Default `SUM`.
	 *     @type string $where_sql     Reserved for internal use. Allows for additional WHERE clauses to be appended to the
	 *                                 query.
	 *     @type string $output        The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 *
	 * @return string Formatted amount from refunded orders.
	 */
	public function get_order_refund_amount( $query = array() ) {
		$this->parse_query( $query );

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_orders;
		$this->query_vars['date_query_column'] = 'date_created';

		if ( empty( $this->query_vars['function'] ) ) {
			$this->query_vars['function'] = 'SUM';
		}

		/*
		 * By default we're checking refunds only and excluding any other types. This gives us gross refund amounts.
		 * This may be overridden in $query parameters that get passed through.
		 */
		$this->query_vars['type']   = 'refund';
		$this->query_vars['status'] = array( 'complete' );

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$function = $this->get_amount_column_and_function( array(
			'accepted_functions' => array( 'SUM', 'AVG' )
		) );

		$initial_query = "SELECT {$function} AS total
			FROM {$this->query_vars['table']}
			WHERE 1=1
			{$this->query_vars['status_sql']}
			{$this->query_vars['type_sql']}
			{$this->query_vars['currency_sql']}
			{$this->query_vars['where_sql']}
			{$this->query_vars['date_query_sql']}";

		$initial_result = $this->get_db()->get_row( $initial_query );

		if ( true === $this->query_vars['relative'] ) {

			$relative_date_query_sql = $this->generate_relative_date_query_sql();

			$relative_query = "SELECT {$function} AS total
				FROM {$this->query_vars['table']}
				WHERE 1=1
				{$this->query_vars['status_sql']}
				{$this->query_vars['type_sql']}
				{$this->query_vars['currency_sql']}
				{$this->query_vars['where_sql']}
				{$relative_date_query_sql}";

			$relative_result = $this->get_db()->get_row( $relative_query );
		}

		$total = null === $initial_result->total
			? 0.00
			: (float) $initial_result->total;

		if ( true === $this->query_vars['relative'] ) {
			$total    = -( floatval( $initial_result->total ) );
			$relative = -( floatval( $relative_result->total ) );
			$total    = $this->generate_relative_markup( $total, $relative, true );
		} else {
			$total = $this->maybe_format( -( $total ) );
		}

		// Reset query vars.
		$this->post_query();

		return $total;
	}

	/**
	 * Calculate average time for an order to be refunded.
	 *
	 * @since 3.0
	 *
	 * @see \EDD\Stats::get_order_earnings()
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class.
	 *
	 *     @type string $start     Start day and time (based on the beginning of the given day).
	 *     @type string $end       End day and time (based on the end of the given day).
	 *     @type string $range     Date range. If a range is passed, this will override and `start` and `end`
	 *                             values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type string $function  SQL function. Accepts `AVG` only. Default `AVG`.
	 *     @type string $where_sql Reserved for internal use. Allows for additional WHERE clauses to be appended to the
	 *                             query.
	 *     @type string $output    The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 *
	 * @return string Average time for an order to be refunded in human readable format.
	 */
	public function get_average_refund_time( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_orders;
		$this->query_vars['column']            = 'date_completed';
		$this->query_vars['date_query_column'] = 'date_created';

		$type_sql = $this->get_db()->prepare( 'AND o2.type = %s', esc_sql( 'refund' ) );

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$sql = "SELECT AVG( TIMESTAMPDIFF( SECOND, {$this->query_vars['table']}.{$this->query_vars['column']}, o2.date_created ) ) AS time_to_refund
				FROM {$this->query_vars['table']}
				INNER JOIN {$this->query_vars['table']} o2 ON {$this->query_vars['table']}.id = o2.parent
				WHERE 1=1 {$type_sql} {$this->query_vars['currency_sql']} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}";

		$result = $this->get_db()->get_var( $sql );

		$time_to_refund = null === $result
			? ''
			: $result;

		// Beginning of time.
		$base = strtotime( '1970-01-01 00:00:00' );

		if ( ! empty( $time_to_refund ) ) {
			$time_to_refund = absint( $time_to_refund );

			$intervals = array( 'year', 'month', 'day', 'hour', 'minute', 'second' );
			$diffs     = array();

			foreach ( $intervals as $interval ) {
				$time = strtotime( '+1 ' . $interval, $base );

				$add    = 1;
				$looped = 0;

				while ( $time_to_refund >= $time ) {
					$add++;
					$time = strtotime( '+' . $add . ' ' . $interval, $base );
					$looped++;
				}

				$base               = strtotime( '+' . $looped . ' ' . $interval, $base );
				$diffs[ $interval ] = $looped;
			}

			$count = 0;
			$times = array();

			foreach ( $diffs as $interval => $value ) {

				// Keep precision to 2.
				if ( $count >= 2 ) {
					break;
				}

				// Add value and interval if value is bigger than 0.
				if ( $value > 0 ) {
					$interval = substr( $interval, 0, 1 );

					// Add value and interval to times array.
					$times[] = $value . $interval;
					$count ++;
				}
			}
		}

		// Reset query vars.
		$this->post_query();

		return empty( $time_to_refund )
			? ''
			: implode( ' ', $times );
	}

	/**
	 * Calculate refund rate.
	 *
	 * @since 3.0
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class.
	 *
	 *     @type string $start         Start day and time (based on the beginning of the given day).
	 *     @type string $end           End day and time (based on the end of the given day).
	 *     @type string $range         Date range. If a range is passed, this will override and `start` and `end`
	 *     @type bool   $exclude_taxes If taxes should be excluded from calculations. Default `false`.
	 *                                 values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type string $function      This method does not allow any SQL functions to be passed.
	 *     @type string $where_sql     Reserved for internal use. Allows for additional WHERE clauses to be appended to the
	 *                                 query.
	 *     @type string $output        The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 *
	 * @return float|int Rate of refunded orders.
	 */
	public function get_refund_rate( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_orders;
		$this->query_vars['column']            = 'id';
		$this->query_vars['date_query_column'] = 'date_created';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$status_sql = $this->get_db()->prepare( "AND status = %s AND type = '%s'", esc_sql( 'complete' ), esc_sql( 'refund' ) );

		$ignore_free = $this->get_db()->prepare( "AND {$this->query_vars['table']}.total > %d", 0 );

		$sql = "SELECT COUNT(id ) / o.number_orders * 100 AS `refund_rate`
				FROM {$this->query_vars['table']}
				CROSS JOIN (
					SELECT COUNT(id) AS number_orders
					FROM {$this->query_vars['table']}
					WHERE 1=1 {$this->query_vars['status_sql']} {$this->query_vars['currency_sql']} {$ignore_free} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}
				) o
				WHERE 1=1 {$status_sql} {$this->query_vars['currency_sql']} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}";

		$result = $this->get_db()->get_var( $sql );

		$total = null === $result
			? 0
			: round( $result, 2 );

		if ( 'formatted' === $this->query_vars['output'] ) {
			$total .= '%';
			$total  = esc_html( $total );
		}

		// Reset query vars.
		$this->post_query();

		return $total;
	}

	/** Order Items **********************************************************/

	/**
	 * Calculate order item earnings.
	 *
	 * @since 3.0
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class.
	 *
	 *     @type string $start         Start day and time (based on the beginning of the given day).
	 *     @type string $end           End day and time (based on the end of the given day).
	 *     @type string $range         Date range. If a range is passed, this will override and `start` and `end`
	 *                                 values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type bool   $exclude_taxes If taxes should be excluded from calculations. Default `false`.
	 *     @type string $function      SQL function. Default `SUM`.
	 *     @type string $where_sql     Reserved for internal use. Allows for additional WHERE clauses to be appended to the
	 *                                 query.
	 *     @type int    $product_id    Product ID. If empty, an aggregation of the values in the `total` column in the
	 *                                 `edd_order_items` table will be returned.
	 *     @type string $output        The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 *
	 * @return array|float|int Formatted order item earnings.
	 */
	public function get_order_item_earnings( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_order_items;
		$this->query_vars['column']            = true === $this->query_vars['exclude_taxes'] ? 'total - tax' : 'total';
		$this->query_vars['date_query_column'] = 'date_created';
		$this->query_vars['status']            = edd_get_gross_order_statuses();

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$product_id = ! empty( $this->query_vars['product_id'] )
			? $this->get_db()->prepare( "AND {$this->query_vars['table']}.product_id = %d", absint( $this->query_vars['product_id'] ) )
			: '';

		$price_id = $this->generate_price_id_query_sql();

		$region = ! empty( $this->query_vars['region'] )
			? $this->get_db()->prepare( 'AND edd_oa.region = %s', esc_sql( $this->query_vars['region'] ) )
			: '';

		$country = ! empty( $this->query_vars['country'] )
			? $this->get_db()->prepare( 'AND edd_oa.country = %s', esc_sql( $this->query_vars['country'] ) )
			: '';

		$status = ! empty( $this->query_vars['status'] )
			? " AND {$this->query_vars['table']}.status IN ('" . implode( "', '", $this->query_vars['status'] ) . "')"
			: '';

		$join = $currency = '';
		if ( ! empty( $country ) || ! empty( $region ) ) {
			$join .= " INNER JOIN {$this->get_db()->edd_order_addresses} edd_oa ON {$this->query_vars['table']}.order_id = edd_oa.order_id ";
		}

		$join .= " INNER JOIN {$this->get_db()->edd_orders} edd_o ON ({$this->query_vars['table']}.order_id = edd_o.id) AND edd_o.status IN ('" . implode( "', '", $this->query_vars['status'] ) . "') ";

		if ( ! empty( $this->query_vars['currency'] ) && array_key_exists( strtoupper( $this->query_vars['currency'] ), edd_get_currencies() ) ) {
			$currency = $this->get_db()->prepare( "AND edd_o.currency = %s", strtoupper( $this->query_vars['currency'] ) );
		}

		/**
		 * The adjustments query needs a different order status check than the order items. This is due to the fact that
		 * adjustments refunded would end up being double counted, and therefore create an inaccurate revenue report.
		 */
		$adjustments_join = " INNER JOIN {$this->get_db()->edd_orders} edd_o ON ({$this->query_vars['table']}.order_id = edd_o.id) AND edd_o.type = 'sale' AND edd_o.status IN ('" . implode( "', '", edd_get_net_order_statuses() ) . "') ";

		/**
		 * With the addition of including fees into the calcualtion, the order_items
		 * and order_adjustments for the order items needs to be a SUM and then the final function
		 * (SUM or AVG) needs to be run on the final UNION Query.
		 */
		$order_item_function = $this->get_amount_column_and_function( array(
			'column_prefix'      => $this->query_vars['table'],
			'accepted_functions' => array( 'SUM', 'AVG' ),
			'requested_function' => 'SUM',
		) );

		$order_adjustment_function = $this->get_amount_column_and_function( array(
			'column_prefix'      => 'oadj',
			'accepted_functions' => array( 'SUM', 'AVG' ),
			'requested_function' => 'SUM',
		) );

		$union_function = $this->get_amount_column_and_function( array(
			'column_prefix'      => '',
			'accepted_functions' => array( 'SUM', 'AVG' ),
			'rate'               => false,
		) );

		if ( true === $this->query_vars['grouped'] ) {
			$order_items = "SELECT
				{$this->query_vars['table']}.product_id,
				{$this->query_vars['table']}.price_id,
				{$order_item_function} AS total
				FROM {$this->query_vars['table']}
				{$join}
				WHERE 1=1
				{$product_id}
				{$price_id}
				{$region}
				{$country}
				{$currency}
				{$this->query_vars['where_sql']}
				{$this->query_vars['date_query_sql']}
				GROUP BY {$this->query_vars['table']}.product_id, {$this->query_vars['table']}.price_id";

			$order_adjustments = "SELECT
				{$this->query_vars['table']}.product_id as product_id,
				{$this->query_vars['table']}.price_id as price_id,
				{$order_adjustment_function} as total
				FROM {$this->get_db()->edd_order_adjustments} oadj
				INNER JOIN {$this->query_vars['table']} ON
					({$this->query_vars['table']}.id = oadj.object_id)
					{$product_id}
					{$price_id}
					{$region}
					{$country}
					{$currency}
				{$adjustments_join}
				WHERE oadj.object_type = 'order_item'
				AND oadj.type != 'discount'
				{$this->query_vars['date_query_sql']}
				GROUP BY {$this->query_vars['table']}.product_id, {$this->query_vars['table']}.price_id";

			$sql = "SELECT product_id, price_id, {$union_function} AS total
				FROM ({$order_items} UNION {$order_adjustments})a
				GROUP BY product_id, price_id
				ORDER BY total DESC";
		} else {
			$order_items = "SELECT
				{$order_item_function} AS total
				FROM {$this->query_vars['table']}
				{$join}
				WHERE 1=1
				{$product_id}
				{$price_id}
				{$region}
				{$country}
				{$currency}
				{$this->query_vars['where_sql']}
				{$this->query_vars['date_query_sql']}";

			$order_adjustments = "SELECT
				{$order_adjustment_function} as total
				FROM {$this->get_db()->edd_order_adjustments} oadj
				INNER JOIN {$this->query_vars['table']} ON
					({$this->query_vars['table']}.id = oadj.object_id)
					{$product_id}
					{$price_id}
					{$region}
					{$country}
					{$currency}
				{$adjustments_join}
				WHERE oadj.object_type = 'order_item'
				AND oadj.type != 'discount'
				{$this->query_vars['date_query_sql']}";

			$sql = "SELECT {$union_function} AS total FROM ({$order_items} UNION {$order_adjustments})a";
		}

		$result = $this->get_db()->get_results( $sql );

		if ( true === $this->query_vars['grouped'] ) {
			array_walk( $result, function ( &$value ) {

				// Format resultant object.
				$value->product_id = absint( $value->product_id );
				$value->price_id   = is_numeric( $value->price_id ) ? absint( $value->price_id ) : null;
				$value->total      = $this->maybe_format( $value->total );

				// Add instance of EDD_Download to resultant object.
				$value->object = edd_get_download( $value->product_id );
			} );
		} else {
			$result = null === $result[0]->total
				? $this->maybe_format( 0.00 )
				: $this->maybe_format( floatval( $result[0]->total ) );
		}

		// Reset query vars.
		$this->post_query();

		return $result;
	}

	/**
	 * Calculate the number of times a specific item has been purchased.
	 *
	 * @since 3.0
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class.
	 *
	 *     @type string $start      Start day and time (based on the beginning of the given day).
	 *     @type string $end        End day and time (based on the end of the given day).
	 *     @type string $range      Date range. If a range is passed, this will override and `start` and `end`
	 *                              values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type string $function   SQL function. Accepts `COUNT` and `AVG`. Default `COUNT`.
	 *     @type string $where_sql  Reserved for internal use. Allows for additional WHERE clauses to be appended to the
	 *                              query.
	 *     @type int    $product_id Product ID. If empty, an aggregation of the values in the `total` column in the
	 *                              `edd_order_items` table will be returned.
	 *     @type string $output     The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 *
	 * @return array|int Number of times a specific item has been purchased.
	 */
	public function get_order_item_count( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_order_items;
		$this->query_vars['column']            = 'id';
		$this->query_vars['date_query_column'] = 'date_created';
		$this->query_vars['status']            = array( 'complete', 'partially_refunded' );

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$function = $this->get_amount_column_and_function( array(
			'column_prefix'      => $this->query_vars['table'],
			'accepted_functions' => array( 'COUNT', 'AVG' ),
		) );

		$product_id = ! empty( $this->query_vars['product_id'] )
			? $this->get_db()->prepare( 'AND product_id = %d', absint( $this->query_vars['product_id'] ) )
			: '';

		$price_id = $this->generate_price_id_query_sql();

		$region = ! empty( $this->query_vars['region'] )
			? $this->get_db()->prepare( 'AND edd_oa.region = %s', esc_sql( $this->query_vars['region'] ) )
			: '';

		$country = ! empty( $this->query_vars['country'] )
			? $this->get_db()->prepare( 'AND edd_oa.country = %s', esc_sql( $this->query_vars['country'] ) )
			: '';

		$statuses      = edd_get_net_order_statuses();
		$status_string = $this->get_placeholder_string( $statuses );

		$join = $this->get_db()->prepare(
			"INNER JOIN {$this->get_db()->edd_orders} edd_o ON ({$this->query_vars['table']}.order_id = edd_o.id) AND edd_o.status IN({$status_string}) AND edd_o.type = 'sale' ",
			...$statuses
		);

		$currency = '';
		if ( ! empty( $country ) || ! empty( $region ) ) {
			$join .= " INNER JOIN {$this->get_db()->edd_order_addresses} edd_oa ON {$this->query_vars['table']}.order_id = edd_oa.order_id ";
		}
		if ( ! empty( $this->query_vars['currency'] ) && array_key_exists( strtoupper( $this->query_vars['currency'] ), edd_get_currencies() ) ) {
			$currency = $this->get_db()->prepare( "AND edd_o.currency = %s", strtoupper( $this->query_vars['currency'] ) );
		}

		// Calculating an average requires a subquery.
		if ( 'AVG' === $this->query_vars['function'] ) {
			$sql = "SELECT AVG(id) AS total
					FROM (
						SELECT COUNT({$this->query_vars['table']}.id) AS id
						FROM {$this->query_vars['table']}
						{$join}
						WHERE 1=1 {$product_id} {$price_id} {$region} {$country} {$currency} {$this->query_vars['status_sql']} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}
						GROUP BY order_id
					) AS counts";
		} elseif ( true === $this->query_vars['grouped'] ) {
			$sql = "SELECT product_id, price_id, {$function} AS total
					FROM {$this->query_vars['table']}
					{$join}
					WHERE 1=1 {$product_id} {$price_id} {$region} {$country} {$currency} {$this->query_vars['status_sql']} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}
					GROUP BY product_id, price_id
					ORDER BY total DESC";
		} else {
			$sql = "SELECT {$function} AS total
					FROM {$this->query_vars['table']}
					{$join}
					WHERE 1=1 {$product_id} {$price_id} {$region} {$country} {$currency} {$this->query_vars['status_sql']} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}";
		}

		$result = $this->get_db()->get_results( $sql );

		if ( true === $this->query_vars['grouped'] ) {
			array_walk( $result, function ( &$value ) {

				// Format resultant object.
				$value->product_id = absint( $value->product_id );
				$value->price_id   = is_numeric( $value->price_id ) ? absint( $value->price_id ) : null;
				$value->total      = absint( $value->total );

				// Add instance of EDD_Download to resultant object.
				$value->object = edd_get_download( $value->product_id );
			} );
		} else {
			$result = null === $result[0]->total
				? 0
				: absint( $result[0]->total );
		}

		// Reset query vars.
		$this->post_query();

		return $result;
	}

	/**
	 * Calculate most valuable order items.
	 *
	 * @since 3.0
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class.
	 *
	 *     @type string $start         Start day and time (based on the beginning of the given day).
	 *     @type string $end           End day and time (based on the end of the given day).
	 *     @type string $range         Date range. If a range is passed, this will override and `start` and `end`
	 *                                 values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type bool   $exclude_taxes If taxes should be excluded from calculations. Default `false`.
	 *     @type string $function      This method does not allow any SQL functions to be passed.
	 *     @type string $where_sql     Reserved for internal use. Allows for additional WHERE clauses to be appended to the
	 *                                 query.
	 *     @type int    $number        Number of order items to fetch. Default 1.
	 *     @type string $output        The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 *
	 * @return array Array of objects with most valuable order items. Each object has the product ID, total earnings,
	 *               and an instance of EDD_Download.
	 */
	public function get_most_valuable_order_items( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_order_items;
		$this->query_vars['date_query_column'] = 'date_created';
		$this->query_vars['exclude_taxes']     = true;

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		// By default, the most valuable customer is returned.
		$number = isset( $this->query_vars['number'] )
			? absint( $this->query_vars['number'] )
			: 1;

		$function = $this->get_amount_column_and_function( array(
			'column_prefix'      => $this->query_vars['table'],
			'accepted_functions' => array( 'SUM' )
		) );

		$statuses      = edd_get_net_order_statuses();
		$status_string = $this->get_placeholder_string( $statuses );

		$where = $this->get_db()->prepare(
			"AND {$this->get_db()->edd_order_items}.status IN('complete','partially_refunded')
			 AND {$this->get_db()->edd_orders}.status IN({$status_string}) ",
			 ...$statuses
		);
		if ( ! empty( $this->query_vars['currency'] ) && array_key_exists( strtoupper( $this->query_vars['currency'] ), edd_get_currencies() ) ) {
			$where .= $this->get_db()->prepare(
				" AND {$this->get_db()->edd_orders}.currency = %s ",
				strtoupper( $this->query_vars['currency'] )
			);
		}

		$sql = "SELECT product_id, price_id, {$function} AS total
				FROM {$this->query_vars['table']}
				INNER JOIN {$this->get_db()->edd_orders} ON({$this->get_db()->edd_orders}.id = {$this->query_vars['table']}.order_id)
				WHERE 1=1 {$where} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}
				GROUP BY product_id, price_id
				ORDER BY total DESC
				LIMIT {$number}";

		$result = $this->get_db()->get_results( $sql );

		array_walk( $result, function ( &$value ) {

			// Format resultant object.
			$value->product_id = absint( $value->product_id );
			$value->price_id   = is_numeric( $value->price_id ) ? absint( $value->price_id ) : null;
			$download_model    = new \EDD\Models\Download(
				$value->product_id,
				$value->price_id,
				array(
					'start' => $this->query_vars['start'],
					'end'   => $this->query_vars['end'],
				)
			);

			$value->sales      = absint( $download_model->get_net_sales() );
			$value->total      = $this->maybe_format($download_model->get_net_earnings() );

			// Add instance of EDD_Download to resultant object.
			$value->object = edd_get_download( $value->product_id );
		} );

		// Reset query vars.
		$this->post_query();

		return $result;
	}

	/** Discounts ************************************************************/

	/**
	 * Calculate the usage count of discount codes.
	 *
	 * @since 3.0
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class.
	 *
	 *     @type string $start         Start day and time (based on the beginning of the given day).
	 *     @type string $end           End day and time (based on the end of the given day).
	 *     @type string $range         Date range. If a range is passed, this will override and `start` and `end`
	 *                                 values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type string $function      This method does not allow any SQL functions to be passed.
	 *     @type string $where_sql     Reserved for internal use. Allows for additional WHERE clauses to be appended
	 *                                 to the query.
	 *     @type string $discount_code Discount code to fetch the usage count for.
	 *     @type string $output        The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 *
	 * @return int Number of times a discount code has been used.
	 */
	public function get_discount_usage_count( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_order_adjustments;
		$this->query_vars['column']            = 'id';
		$this->query_vars['date_query_column'] = 'date_created';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$discount_code = isset( $this->query_vars['discount_code'] )
			? $this->get_db()->prepare( 'AND type = %s AND description = %s', 'discount', sanitize_text_field( $this->query_vars['discount_code'] ) )
			: $this->get_db()->prepare( 'AND type = %s', 'discount' );

		$sql = "SELECT COUNT({$this->query_vars['column']})
				FROM {$this->query_vars['table']}
				WHERE 1=1 {$discount_code} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}";

		$result = $this->get_db()->get_var( $sql );

		$total = null === $result
			? 0
			: absint( $result );

		// Reset query vars.
		$this->post_query();

		return $total;
	}

	/**
	 * Retrieve the most popular discount code.
	 *
	 * @since 3.0
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class.
	 *
	 *     @type string $start         Start day and time (based on the beginning of the given day).
	 *     @type string $end           End day and time (based on the end of the given day).
	 *     @type string $range         Date range. If a range is passed, this will override and `start` and `end`
	 *                                 values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type string $function      This method does not allow any SQL functions to be passed.
	 *     @type string $where_sql     Reserved for internal use. Allows for additional WHERE clauses to be appended
	 *                                 to the query.
	 *     @type string $discount_code Discount code to fetch the usage count for.
	 *     @type string $output        The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 *
	 * @return array Most popular discounts with usage count.
	 */
	public function get_most_popular_discounts( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_order_adjustments;
		$this->query_vars['column']            = 'id';
		$this->query_vars['date_query_column'] = 'date_created';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		// By default, the most valuable discount is returned.
		$number = isset( $this->query_vars['number'] )
			? absint( $this->query_vars['number'] )
			: 1;

		$discount = $this->get_db()->prepare( 'AND type = %s', 'discount' );

		$sql = "SELECT description AS code, COUNT({$this->query_vars['column']}) AS count
				FROM {$this->query_vars['table']}
				WHERE 1=1 {$discount} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}
				GROUP BY description
				ORDER BY count DESC
				LIMIT {$number}";

		$result = $this->get_db()->get_results( $sql );

		array_walk( $result, function ( &$value ) {

			// Add instance of EDD_Discount to resultant object.
			$value->object = edd_get_discount_by_code( $value->code );

			// Format resultant object.
			if ( ! empty( $value->object ) ) {
				$value->discount_id = absint( $value->object->id );
				$value->count       = absint( $value->count );
			} else {
				$value->discount_id = 0;
				$value->count       = '&mdash;';
			}
		} );

		// Reset query vars.
		$this->post_query();

		return $result;
	}

	/**
	 * Calculate the savings from using a discount code.
	 *
	 * @since 3.0
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class.
	 *
	 *     @type string $start         Start day and time (based on the beginning of the given day).
	 *     @type string $end           End day and time (based on the end of the given day).
	 *     @type string $range         Date range. If a range is passed, this will override and `start` and `end`
	 *                                 values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type bool   $exclude_taxes If taxes should be excluded from calculations. Default `false`.
	 *     @type string $function      This method does not allow any SQL functions to be passed.
	 *     @type string $where_sql     Reserved for internal use. Allows for additional WHERE clauses to be appended
	 *                                 to the query.
	 *     @type string $discount_code Discount code to fetch the savings amount for. Default empty. If empty, the amount
	 *                                 saved from using any discount will be returned.
	 *     @type string $output        The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 *
	 * @return float Savings from using a discount code.
	 */
	public function get_discount_savings( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_order_adjustments;
		$this->query_vars['column']            = true === $this->query_vars['exclude_taxes'] ? 'total - tax' : 'total';
		$this->query_vars['date_query_column'] = 'date_created';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$function = $this->get_amount_column_and_function( array(
			'accepted_functions' => array( 'SUM' )
		) );

		$discount_code = ! empty( $this->query_vars['discount_code'] )
			? $this->get_db()->prepare( 'AND type = %s AND description = %s', 'discount', sanitize_text_field( $this->query_vars['discount_code'] ) )
			: $this->get_db()->prepare( 'AND type = %s', 'discount' );

		$sql = "SELECT {$function}
				FROM {$this->query_vars['table']}
				WHERE 1=1 {$discount_code} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}";

		$result = $this->get_db()->get_var( $sql );

		$total = null === $result
			? 0.00
			: floatval( $result );

		$total = $this->maybe_format( $total );

		// Reset query vars.
		$this->post_query();

		return $total;
	}

	/**
	 * Calculate the average discount amount applied to an order.
	 *
	 * @since 3.0
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class.
	 *
	 *     @type string $start         Start day and time (based on the beginning of the given day).
	 *     @type string $end           End day and time (based on the end of the given day).
	 *     @type string $range         Date range. If a range is passed, this will override and `start` and `end`
	 *                                 values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type bool   $exclude_taxes If taxes should be excluded from calculations. Default `false`.
	 *     @type string $function      This method does not allow any SQL functions to be passed.
	 *     @type string $where_sql     Reserved for internal use. Allows for additional WHERE clauses to be appended
	 *                                 to the query.
	 *     @type string $output        The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 *
	 * @return float Average discount amount applied to an order.
	 */
	public function get_average_discount_amount( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_order_adjustments;
		$this->query_vars['column']            = 'total';
		$this->query_vars['date_query_column'] = 'date_created';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$function = $this->get_amount_column_and_function( array(
			'accepted_functions' => array( 'AVG' )
		) );

		$type_discount = $this->get_db()->prepare( 'AND type = %s', 'discount' );

		$sql = "SELECT {$function}
				FROM {$this->query_vars['table']}
				WHERE 1=1 {$type_discount} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}";

		$result = $this->get_db()->get_var( $sql );

		$total = null === $result
			? 0.00
			: floatval( $result );

		$total = $this->maybe_format( $total );

		// Reset query vars.
		$this->post_query();

		return $total;
	}

	/**
	 * Calculate the ratio of discounted to non-discounted orders.
	 *
	 * @since 3.0
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class.
	 *
	 *     @type string $start     Start day and time (based on the beginning of the given day).
	 *     @type string $end       End day and time (based on the end of the given day).
	 *     @type string $range     Date range. If a range is passed, this will override and `start` and `end`
	 *                             values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type string $function  This method does not allow any SQL functions to be passed.
	 *     @type string $where_sql Reserved for internal use. Allows for additional WHERE clauses to be appended
	 *                             to the query.
	 *     @type string $output    The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 *
	 * @return string Ratio of discounted to non-discounted orders. Format is A:B where A and B are integers.
	 */
	public function get_ratio_of_discounted_orders( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_orders;
		$this->query_vars['column']            = 'id';
		$this->query_vars['date_query_column'] = 'date_created';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$sql = "SELECT COUNT(id) AS total, o.discounted_orders
				FROM {$this->query_vars['table']}
				CROSS JOIN (
					SELECT COUNT(id) AS discounted_orders
					FROM {$this->query_vars['table']}
					WHERE 1=1 {$this->query_vars['status_sql']} AND discount > 0 {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}
				) o
				WHERE 1=1 {$this->query_vars['status_sql']} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}";

		$result = $this->get_db()->get_row( $sql );

		// No need to calculate the ratio if there are no orders.
		if ( 0 === (int) $result->discounted_orders || 0 === (int) $result->total ) {
			return 0;
		}

		// Calculate GCD.
		$result->total             = absint( $result->total );
		$result->discounted_orders = absint( $result->discounted_orders );

		$original_result = clone $result;

		while ( 0 !== $result->total ) {
			$remainder                 = $result->discounted_orders % $result->total;
			$result->discounted_orders = $result->total;
			$result->total             = $remainder;
		}

		$ratio = absint( $result->discounted_orders );

		// Reset query vars.
		$this->post_query();

		// Return the formatted ratio.
		return ( $original_result->discounted_orders / $ratio ) . ':' . ( $original_result->total / $ratio );
	}

	/** Gateways *************************************************************/

	/**
	 * Perform gateway calculations based on data passed.
	 *
	 * @internal This method must remain `private`, it exists to reduce duplicated code.
	 *
	 * @since 3.0
	 * @access private
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class.
	 *
	 *     @type string $start         Start day and time (based on the beginning of the given day).
	 *     @type string $end           End day and time (based on the end of the given day).
	 *     @type string $range         Date range. If a range is passed, this will override and `start` and `end`
	 *     @type bool   $exclude_taxes If taxes should be excluded from calculations. Default `false`.
	 *                                 values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type string $function      SQL function. Accepts `COUNT`, `AVG`, and `SUM`. Default `COUNT`.
	 *     @type string $where_sql     Reserved for internal use. Allows for additional WHERE clauses to be appended
	 *                                 to the query.
	 *     @type string $gateway       Gateway name. This is checked against a list of registered payment gateways.
	 *                                 If a gateway is not passed, a list of objects are returned for each gateway and the
	 *                                 number of orders processed with that gateway.
	 *     @type string $output        The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 *
	 * @return mixed array|int|float Either a list of payment gateways and counts or just a single value.
	 */
	private function get_gateway_data( $query = array() ) {
		$query = wp_parse_args( $query, array(
			'type'   => 'sale',
			'status' => edd_get_gross_order_statuses(),
		) );

		$this->parse_query( $query );

		// Set up default values.
		$gateways = edd_get_payment_gateways();
		$defaults = array();

		// Set up an object for each gateway.
		foreach ( $gateways as $id => $data ) {
			$object          = new \stdClass();
			$object->gateway = $id;
			$object->total   = 0;

			$defaults[] = $object;
		}

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_orders;
		$this->query_vars['date_query_column'] = 'date_created';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$function = $this->get_amount_column_and_function( array(
			'accepted_functions' => array( 'COUNT', 'AVG', 'SUM' )
		) );

		$gateway = ! empty( $this->query_vars['gateway'] )
			? $this->get_db()->prepare( 'AND gateway = %s', sanitize_text_field( $this->query_vars['gateway'] ) )
			: '';

		$sql = "SELECT gateway, {$function} AS total
			FROM {$this->query_vars['table']}
			WHERE 1=1 {$this->query_vars['type_sql']} {$this->query_vars['status_sql']} {$this->query_vars['currency_sql']} {$gateway} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}
			GROUP BY gateway";

		$result = $this->get_db()->get_results( $sql );

		// Ensure count values are always valid integers if counting sales.
		if ( 'COUNT' === $this->query_vars['function'] ) {
			array_walk( $result, function ( &$value ) {
				$value->total = absint( $value->total );
			} );
		} elseif ( 'SUM' === $this->query_vars['function'] || 'AVG' === $this->query_vars['function'] ) {
			array_walk( $result, function ( &$value ) {
				$value->total = floatval( abs( $value->total ) );
			} );
		}

		if ( empty( $gateway ) && true === $this->query_vars['grouped'] ) {
			$results = array();

			// Merge defaults with values returned from the database.
			foreach ( $defaults as $key => $value ) {

				// Filter based on gateway.
				$filter = wp_filter_object_list( $result, array( 'gateway' => $value->gateway ) );

				$filter = ! empty( $filter )
					? array_values( $filter )
					: array();

				if ( ! empty( $filter ) ) {
					$results[] = $filter[0];
				} else {
					$results[] = $defaults[ $key ];
				}
			}
		} elseif ( false === $this->query_vars['grouped'] ) {
			$total = 0;

			array_walk( $result, function( $value ) use ( &$total ) {
				$total += $value->total;
			} );

			$results = 'COUNT' === $this->query_vars['function']
				? absint( $total )
				: $this->maybe_format( $total );
		}

		if ( ! empty( $gateway ) && true === $this->query_vars['grouped'] ) {

			// Filter based on gateway if passed.
			$filter = wp_filter_object_list( $result, array( 'gateway' => $this->query_vars['gateway'] ) );

			$results = 'COUNT' === $this->query_vars['function']
				? absint( $filter[0]->total )
				: $this->maybe_format( $filter[0]->total );
		}

		// Reset query vars.
		$this->post_query();

		// Return array of objects with gateway name and count.
		return $results;
	}

	/**
	 * Calculate the number of processed by a gateway.
	 *
	 * @since 3.0
	 *
	 * @see \EDD\Stats::get_gateway_data()
	 *
	 * @param array $query See \EDD\Stats::get_gateway_data().
	 *
	 * @return int|array List of objects containing the number of sales processed either for every gateway or the gateway
	 *               passed as a query parameter.
	 */
	public function get_gateway_sales( $query = array() ) {

		$query['column']   = 'id';
		$query['function'] = 'COUNT';

		// Dispatch to \EDD\Stats::get_gateway_data().
		return $this->get_gateway_data( $query );
	}

	/**
	 * Calculate the total order amount of processed by a gateway.
	 *
	 * @since 3.0
	 *
	 * @see \EDD\Stats::get_gateway_data()
	 *
	 * @param array $query See \EDD\Stats::get_gateway_data().
	 *
	 * @return array List of objects containing the amount processed either for every gateway or the gateway
	 *               passed as a query parameter.
	 */
	public function get_gateway_earnings( $query = array() ) {

		// Summation is required as we are returning earnings.
		$query['function'] = isset( $query['function'] )
			? $query['function']
			: 'SUM';

		// Dispatch to \EDD\Stats::get_gateway_data().
		$result = $this->get_gateway_data( $query );

		// Rename object var.
		if ( is_array( $result ) ) {
			array_walk( $result, function ( &$value ) {
				$value->earnings = $value->total;
				$value->earnings = $this->maybe_format( $value->earnings );
				unset( $value->total );
			} );
		} else {
			$result = $this->maybe_format( $result );
		}

		// Reset query vars.
		$this->post_query();

		// Return array of objects with gateway name and earnings.
		return $result;
	}

	/**
	 * Calculate the amount for refunded orders processed by a gateway.
	 *
	 * @since 3.0
	 *
	 * @see \EDD\Stats::get_gateway_earnings()
	 *
	 * @param array $query See \EDD\Stats::get_gateway_earnings().
	 *
	 * @return array List of objects containing the amount for refunded orders processed either for every
	 *               gateway or the gateway passed as a query parameter.
	 */
	public function get_gateway_refund_amount( $query = array() ) {

		// Ensure orders are refunded.
		$this->query_vars['where_sql'] = $this->get_db()->prepare( 'AND status = %s', 'refunded' );

		// Dispatch to \EDD\Stats::get_gateway_data().
		$result = $this->get_gateway_earnings( $query );

		// Reset query vars.
		$this->post_query();

		// Return array of objects with gateway name and amount from refunded orders.
		return $result;
	}

	/**
	 * Calculate the average order amount of processed by a gateway.
	 *
	 * @since 3.0
	 *
	 * @see \EDD\Stats::get_gateway_data()
	 *
	 * @param array $query See \EDD\Stats::get_gateway_data().
	 *
	 * @return array List of objects containing the average order value processed either for every gateway
	 *               pr the gateway passed as a query parameter.
	 */
	public function get_gateway_average_value( $query = array() ) {

		// Function needs to be `AVG`.
		$query['function'] = 'AVG';

		// Dispatch to \EDD\Stats::get_gateway_data().
		$result = $this->get_gateway_data( $query );

		// Rename object var.
		array_walk( $result, function( &$value ) {
			$value->earnings = $value->count;
			$value->earnings = $this->maybe_format( $value->earnings );
			unset( $value->count );
		} );

		// Reset query vars.
		$this->post_query();

		// Return array of objects with gateway name and earnings.
		return $result;
	}

	/** Tax ******************************************************************/

	/**
	 * Calculate total tax collected.
	 *
	 * @since 3.0
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class.
	 *
	 *     @type string $start     Start day and time (based on the beginning of the given day).
	 *     @type string $end       End day and time (based on the end of the given day).
	 *     @type string $range     Date range. If a range is passed, this will override and `start` and `end`
	 *                             values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type string $function  SQL function. Accepts `SUM` and `AVG`. Default `SUM`.
	 *     @type string $where_sql Reserved for internal use. Allows for additional WHERE clauses to be appended
	 *                             to the query.
	 *     @type string $output    The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 *
	 * @return string Formatted amount of total tax collected.
	 */
	public function get_tax( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_orders;
		$this->query_vars['column']            = 'tax';
		$this->query_vars['date_query_column'] = 'date_created';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$function = $this->get_amount_column_and_function( array(
			'accepted_functions' => array( 'SUM', 'AVG' )
		) );

		$product_id = ! empty( $this->query_vars['download_id'] )
			? $this->get_db()->prepare( 'AND product_id = %d', absint( $this->query_vars['download_id'] ) )
			: '';

		$price_id = $this->generate_price_id_query_sql();

		if ( true === $this->query_vars['relative'] ) {
			$relative_date_query_sql = $this->generate_relative_date_query_sql();

			$sql = "SELECT IFNULL({$function}, 0) AS total, IFNULL(relative, 0) AS relative
					FROM {$this->query_vars['table']}
					CROSS JOIN (
						SELECT IFNULL({$function}, 0) AS relative
						FROM {$this->query_vars['table']}
						WHERE 1=1 {$this->query_vars['status_sql']} {$this->query_vars['currency_sql']} {$this->query_vars['where_sql']} {$relative_date_query_sql}
					) o
					WHERE 1=1 {$this->query_vars['status_sql']} {$this->query_vars['currency_sql']} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}";
		} elseif ( ! empty( $product_id ) || ! empty( $price_id ) ) {

			// Regenerate SQL clauses due to alias.
			$table = $this->query_vars['table'];
			$this->query_vars['table'] = 'o';
			$this->pre_query( $query );
			$this->query_vars['table'] = $table;

			$function = $this->get_amount_column_and_function( array(
				'column_prefix'      => 'oi',
				'accepted_functions' => array( 'SUM', 'AVG' )
			) );

			$sql = "SELECT {$function} AS total
					FROM {$this->query_vars['table']} o
					INNER JOIN {$this->get_db()->edd_order_items} oi ON o.id = oi.order_id
					WHERE 1=1 {$product_id} {$price_id} {$this->query_vars['status_sql']} {$this->query_vars['currency_sql']} {$this->query_vars['date_query_sql']}";

			$this->pre_query( $query );
		} else {
			$sql = "SELECT {$function} AS total
					FROM {$this->query_vars['table']}
					WHERE 1=1 {$this->query_vars['status_sql']} {$this->query_vars['currency_sql']} {$this->query_vars['date_query_sql']}";
		}

		$result = $this->get_db()->get_row( $sql );

		$total = null === $result->total
			? 0.00
			: (float) $result->total;

		if ( true === $this->query_vars['relative'] ) {
			$total    = floatval( $result->total );
			$relative = floatval( $result->relative );
			$total    = $this->generate_relative_markup( $total, $relative );
		} else {
			$total = $this->maybe_format( $total );
		}

		// Reset query vars.
		$this->post_query();

		return $total;
	}

	/**
	 * Calculate total tax collected for country and state passed.
	 *
	 * @since 3.0
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class.
	 *
	 *     @type string $start     Start day and time (based on the beginning of the given day).
	 *     @type string $end       End day and time (based on the end of the given day).
	 *     @type string $range     Date range. If a range is passed, this will override and `start` and `end`
	 *                             values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type string $function  SQL function. Default `COUNT`.
	 *     @type string $where_sql Reserved for internal use. Allows for additional WHERE clauses to be appended
	 *                             to the query.
	 *     @type string $country   Country name. Defaults to store's base country.
	 *     @type string $region    Region name. Defaults to store's base state.
	 *     @type string $output    The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 *
	 * @return string Formatted amount of total tax collected for country and state passed.
	 */
	public function get_tax_by_location( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_orders;
		$this->query_vars['column']            = 'tax';
		$this->query_vars['date_query_column'] = 'date_created';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$function = $this->get_amount_column_and_function( array(
			'column_prefix'      => $this->query_vars['table'],
			'accepted_functions' => array( 'SUM', 'AVG' )
		) );

		$region = ! empty( $this->query_vars['region'] )
			? $this->get_db()->prepare( 'AND oa.region = %s', esc_sql( $this->query_vars['region'] ) )
			: '';

		$country = ! empty( $this->query_vars['country'] )
			? $this->get_db()->prepare( 'AND oa.country = %s', esc_sql( $this->query_vars['country'] ) )
			: '';

		$product_id = ! empty( $this->query_vars['download_id'] )
			? $this->get_db()->prepare( 'AND oi.product_id = %d', absint( $this->query_vars['download_id'] ) )
			: '';

		$price_id = ! is_null( $this->query_vars['price_id'] ) && is_numeric( $this->query_vars['price_id'] )
			? $this->get_db()->prepare( 'AND oi.price_id = %d', absint( $this->query_vars['price_id'] ) )
			: '';

		$join = ! empty( $product_id )
			? "INNER JOIN {$this->get_db()->edd_order_items} oi ON {$this->query_vars['table']}.id = oi.order_id"
			: '';

		// Re-parse function to fetch tax from the order items table.
		if ( ! empty( $product_id ) && 'tax' === $this->query_vars['column'] ) {
			$function = $this->get_amount_column_and_function( array(
				'column_prefix'      => 'oi',
				'accepted_functions' => array( 'SUM', 'AVG' )
			) );
		}

		$sql = "SELECT {$function} AS total
				FROM {$this->query_vars['table']}
				INNER JOIN {$this->get_db()->edd_order_addresses} oa ON {$this->query_vars['table']}.id = oa.order_id
				{$join}
				WHERE 1=1 {$region} {$country} {$product_id} {$price_id} {$this->query_vars['status_sql']} {$this->query_vars['currency_sql']} {$this->query_vars['date_query_sql']}";

		$result = $this->get_db()->get_row( $sql );

		$total = null === $result->total
			? 0.00
			: (float) $result->total;

		$total = $this->maybe_format( $total );

		// Reset query vars.
		$this->post_query();

		return $total;
	}

	/** Customers ************************************************************/

	/**
	 * Calculate the number of customers.
	 *
	 * @since 3.0
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class.
	 *
	 *     @type string $start     Start day and time (based on the beginning of the given day).
	 *     @type string $end       End day and time (based on the end of the given day).
	 *     @type string $range     Date range. If a range is passed, this will override and `start` and `end`
	 *                             values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type string $function  This method does not allow any SQL functions to be passed.
	 *     @type string $where_sql Reserved for internal use. Allows for additional WHERE clauses to be appended
	 *                             to the query.
	 *     @type string $output    The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 *
	 * @return int Number of customers.
	 */
	public function get_customer_count( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_customers;
		$this->query_vars['column']            = 'id';
		$this->query_vars['date_query_column'] = 'date_created';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$where = $this->query_vars['where_sql'];
		// Allow `purchase_count` to be set to `true` to query only customers with orders.
		if ( isset( $query['purchase_count'] ) && true === $query['purchase_count'] ) {
			$where .= " AND {$this->query_vars['table']}.purchase_count > 0";
		}

		if ( true === $this->query_vars['relative'] ) {
			$relative_date_query_sql = $this->generate_relative_date_query_sql();

			$sql = "SELECT IFNULL(COUNT(id), 0) AS total, IFNULL(relative, 0) AS relative
					FROM {$this->query_vars['table']}
					CROSS JOIN (
						SELECT IFNULL(COUNT(id), 0) AS relative
						FROM {$this->query_vars['table']}
						WHERE 1=1 {$where} {$relative_date_query_sql}
					) o
					WHERE 1=1 {$where} {$this->query_vars['date_query_sql']}";
		} else {
			$sql = "SELECT COUNT(id) AS total
					FROM {$this->query_vars['table']}
					WHERE 1=1 {$where} {$this->query_vars['date_query_sql']}";
		}

		$result = $this->get_db()->get_row( $sql );

		$total = null === $result->total
			? 0
			: absint( $result->total );

		if ( 'array' === $this->query_vars['output'] ) {
			$output = array(
				'value'         => $total,
				'relative_data' => ( true === $this->query_vars['relative'] ) ? $this->generate_relative_data( absint( $result->total ), absint( $result->relative ) ) : array(),
			);
		} else {
			if ( true === $this->query_vars['relative'] ) {
				$output = $this->generate_relative_markup( absint( $result->total ), absint( $result->relative ) );
			} else {
				$output = $this->maybe_format( $total );
			}
		}

		// Reset query vars.
		$this->post_query();

		return $output;
	}

	/**
	 * Calculate the lifetime value of a customer.
	 *
	 * @since 3.0
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class.
	 *
	 *     @type string $start         Start day and time (based on the beginning of the given day).
	 *     @type string $end           End day and time (based on the end of the given day).
	 *     @type string $range         Date range. If a range is passed, this will override and `start` and `end`
	 *                                 values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type bool   $exclude_taxes If taxes should be excluded from calculations. Default `false`.
	 *     @type string $function      SQL function. Accepts `AVG` and `SUM`. Default `SUM`.
	 *     @type string $where_sql     Reserved for internal use. Allows for additional WHERE clauses to be appended
	 *                                 to the query.
	 *     @type int    $customer_id   Customer ID. Default empty.
	 *     @type int    $user_id       User ID. Default empty.
	 *     @type string $email         Email address.
	 *     @type string $output        The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 *
	 * @return string Formatted lifetime value of a customer.
	 */
	public function get_customer_lifetime_value( $query = array() ) {
		$this->parse_query( $query );

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_orders;
		$this->query_vars['column']            = 'total';
		$this->query_vars['date_query_column'] = 'date_created';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$function = $this->get_amount_column_and_function( array(
			'accepted_functions' => array( 'SUM', 'AVG' )
		) );

		$user = isset( $this->query_vars['user_id'] )
			? $this->get_db()->prepare( 'AND user_id = %d', absint( $this->query_vars['user_id'] ) )
			: '';

		$customer = isset( $this->query_vars['customer'] )
			? $this->get_db()->prepare( 'AND customer_id = %d', absint( $this->query_vars['customer'] ) )
			: '';

		$email    = isset( $this->query_vars['email'] )
			? $this->get_db()->prepare( 'AND email = %s', absint( $this->query_vars['email'] ) )
			: '';

		$function = $this->get_amount_column_and_function( array(
			'accepted_functions' => array( 'SUM', 'AVG' ),
			'rate'               => false
		) );

		$inner_function = $this->get_amount_column_and_function( array(
			'accepted_functions' => array( 'SUM' )
		) );

		$sql = "SELECT {$function} AS total
				FROM (
					SELECT {$inner_function} AS total
					FROM {$this->query_vars['table']}
					WHERE 1=1 {$this->query_vars['status_sql']} {$this->query_vars['currency_sql']} {$user} {$customer} {$email} {$this->query_vars['date_query_sql']}
					GROUP BY customer_id
				) o";

		$result = $this->get_db()->get_row( $sql );

		$total = null === $result->total
			? 0.00
			: (float) $result->total;

		$total = $this->maybe_format( $total );

		// Reset query vars.
		$this->post_query();

		return $total;
	}

	/**
	 * Calculate the number of orders made by a customer.
	 *
	 * @since 3.0
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class.
	 *
	 *     @type string $start       Start day and time (based on the beginning of the given day).
	 *     @type string $end         End day and time (based on the end of the given day).
	 *     @type string $range       Date range. If a range is passed, this will override and `start` and `end`
	 *                               values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type string $function    SQL function. Accepts `AVG` and `SUM`. Default `SUM`.
	 *     @type string $where_sql   Reserved for internal use. Allows for additional WHERE clauses to be appended
	 *                               to the query.
	 *     @type int    $customer_id Customer ID. Default empty.
	 *     @type int    $user_id     User ID. Default empty.
	 *     @type string $email       Email address.
	 *     @type string $output      The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 *
	 * @return int Number of orders made by a customer.
	 */
	public function get_customer_order_count( $query = array() ) {
		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_orders;
		$this->query_vars['column']            = 'id';
		$this->query_vars['date_query_column'] = 'date_created';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$function = $this->get_amount_column_and_function( array(
			'accepted_functions' => array( 'COUNT', 'AVG' )
		) );

		$user = isset( $this->query_vars['user_id'] )
			? $this->get_db()->prepare( 'AND user_id = %d', absint( $this->query_vars['user_id'] ) )
			: '';

		$customer = isset( $this->query_vars['customer'] )
			? $this->get_db()->prepare( 'AND customer_id = %d', absint( $this->query_vars['customer'] ) )
			: '';

		$email = isset( $this->query_vars['email'] )
			? $this->get_db()->prepare( 'AND email = %s', sanitize_email( $this->query_vars['email'] ) )
			: '';

		if ( true === $this->query_vars['relative'] ) {
			$relative_date_query_sql = $this->generate_relative_date_query_sql();

			if ( 'AVG(id)' === $function ) {
				$sql = "SELECT COUNT(id) / COUNT(DISTINCT customer_id) AS total, IFNULL(relative, 0) AS relative
						FROM {$this->query_vars['table']}
						CROSS JOIN (
							SELECT COUNT(id) / COUNT(DISTINCT customer_id) AS relative
							FROM {$this->query_vars['table']}
							WHERE 1=1 {$this->query_vars['status_sql']} {$user} {$customer} {$email} {$this->query_vars['where_sql']} {$relative_date_query_sql}
						) o
						WHERE 1=1 {$this->query_vars['status_sql']} {$user} {$customer} {$email} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}";
			} else {
				$sql = "SELECT COUNT(id) AS total, IFNULL(relative, 0) AS relative
						FROM {$this->query_vars['table']}
						CROSS JOIN (
							SELECT COUNT(id), IFNULL(relative, 0) AS relative
							FROM {$this->query_vars['table']}
							WHERE 1=1 {$this->query_vars['status_sql']} {$user} {$customer} {$email} {$this->query_vars['where_sql']} {$relative_date_query_sql}
						) o
						WHERE 1=1 {$this->query_vars['status_sql']} {$user} {$customer} {$email} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}";
			}
		} else {
			if ( 'AVG(id)' === $function ) {
				$sql = "SELECT COUNT(id) / COUNT(DISTINCT customer_id) AS total
						FROM {$this->query_vars['table']}
						WHERE 1=1 {$this->query_vars['status_sql']} {$user} {$customer} {$email} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}";
			} else {
				$sql = "SELECT COUNT(id) as total
						FROM {$this->query_vars['table']}
						WHERE 1=1 {$this->query_vars['status_sql']} {$user} {$customer} {$email} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}";
			}
		}
		$result = $this->get_db()->get_row( $sql );

		$total = null === $result
			? 0
			: absint( $result->total );

		if ( true === $this->query_vars['relative'] ) {
			$total    = absint( $result->total );
			$relative = absint( $result->relative );
			$total    = $this->generate_relative_markup( $total, $relative );
		} else {
			$total = $this->maybe_format( $total );
		}

		// Reset query vars.
		$this->post_query();
		return $total;
	}

	/**
	 * Calculate the average age of a customer.
	 *
	 * @since 3.0
	 *
	 * @see \EDD\Stats::get_order_count()
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class.
	 *
	 *     @type string $start       Start day and time (based on the beginning of the given day).
	 *     @type string $end         End day and time (based on the end of the given day).
	 *     @type string $range       Date range. If a range is passed, this will override and `start` and `end`
	 *                               values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type string $function    This method does not allow any SQL functions to be passed.
	 *     @type string $where_sql   Reserved for internal use. Allows for additional WHERE clauses to be appended
	 *                               to the query.
	 *     @type string $output      The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 *
	 * @return int|float Average age of a customer.
	 */
	public function get_customer_age( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_customers;
		$this->query_vars['column']            = 'id';
		$this->query_vars['date_query_column'] = 'date_created';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$sql = "SELECT AVG(DATEDIFF(NOW(), date_created))
				FROM {$this->query_vars['table']}
				WHERE 1=1 {$this->query_vars['date_query_sql']}";

		$result = $this->get_db()->get_var( $sql );

		// Reset query vars.
		$this->post_query();

		return null === $result
			? 0
			: round( $result, 2 );
	}

	/**
	 * Calculate the most valuable customers.
	 *
	 * @since 3.0
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class.
	 *
	 *     @type string $start         Start day and time (based on the beginning of the given day).
	 *     @type string $end           End day and time (based on the end of the given day).
	 *     @type string $range         Date range. If a range is passed, this will override and `start` and `end`
	 *                                 values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type bool   $exclude_taxes If taxes should be excluded from calculations. Default `false`.
	 *     @type string $function      This method does not allow any SQL functions to be passed.
	 *     @type string $where_sql     Reserved for internal use. Allows for additional WHERE clauses to be appended
	 *                                 to the query.
	 *     @type int    $number        Number of customers to fetch. Default 1.
	 *     @type string $output        The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 *
	 * @return array Array of objects with most valuable customers. Each object has the customer ID, total amount spent
	 *               by that customer and an instance of EDD_Customer.
	 */
	public function get_most_valuable_customers( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_orders;
		$this->query_vars['column']            = 'id';
		$this->query_vars['date_query_column'] = 'date_created';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		// By default, the most valuable customer is returned.
		$number = isset( $this->query_vars['number'] )
			? absint( $this->query_vars['number'] )
			: 1;

		$column = true === $this->query_vars['exclude_taxes']
			? 'total - tax'
			: 'total';

		$sql = "SELECT customer_id, SUM({$column}) AS total
				FROM {$this->query_vars['table']}
				WHERE 1=1 {$this->query_vars['status_sql']} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}
				GROUP BY customer_id
				ORDER BY total DESC
				LIMIT {$number}";

		$result = $this->get_db()->get_results( $sql );

		array_walk( $result, function ( &$value ) {

			// Format resultant object.
			$value->customer_id = absint( $value->customer_id );
			$value->total       = $this->maybe_format( $value->total );

			// Add instance of EDD_Download to resultant object.
			$value->object = edd_get_customer( $value->customer_id );
		} );

		// Reset query vars.
		$this->post_query();

		return $result;
	}

	/** File Downloads *******************************************************/

	/**
	 * Calculate the number of file downloads.
	 *
	 * @since 3.0
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class.
	 *
	 *     @type string $start     Start day and time (based on the beginning of the given day).
	 *     @type string $end       End day and time (based on the end of the given day).
	 *     @type string $range     Date range. If a range is passed, this will override and `start` and `end`
	 *                             values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type string $function  SQL function. Accepts `COUNT` and `AVG`. Default `COUNT`.
	 *     @type string $where_sql Reserved for internal use. Allows for additional WHERE clauses to be appended
	 *                             to the query.
	 *     @type string $output    The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 *
	 * @return int Number of file downloads.
	 */
	public function get_file_download_count( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_logs_file_downloads;
		$this->query_vars['column']            = 'id';
		$this->query_vars['date_query_column'] = 'date_created';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		// Only `COUNT` and `AVG` are accepted by this method.
		$accepted_functions = array( 'COUNT', 'AVG' );

		$function = isset( $this->query_vars['function'] ) && in_array( strtoupper( $this->query_vars['function'] ), $accepted_functions, true )
			? $this->query_vars['function'] . "({$this->query_vars['column']})"
			: 'COUNT(id)';

		$product_id = ! empty( $this->query_vars['download_id'] )
			? $this->get_db()->prepare( 'AND product_id = %d', absint( $this->query_vars['download_id'] ) )
			: '';

		$price_id = $this->generate_price_id_query_sql();

		if ( true === $this->query_vars['relative'] ) {
			$relative_date_query_sql = $this->generate_relative_date_query_sql();

			$sql = "SELECT IFNULL({$function}, 0) AS total, IFNULL(relative, 0) AS relative
					FROM {$this->query_vars['table']}
					CROSS JOIN (
						SELECT IFNULL({$function}, 0) AS relative
						FROM {$this->query_vars['table']}
						WHERE 1=1 {$this->query_vars['where_sql']} {$relative_date_query_sql}
					) o
					WHERE 1=1 {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}";
		} else {
			$sql = "SELECT {$function} AS total
					FROM {$this->query_vars['table']}
					WHERE 1=1 {$product_id} {$price_id} {$this->query_vars['date_query_sql']}";
		}

		$result = $this->get_db()->get_row( $sql );

		$total = null === $result->total
			? 0
			: absint( $result->total );

		if ( true === $this->query_vars['relative'] ) {
			$total    = absint( $result->total );
			$relative = absint( $result->relative );
			$total    = $this->generate_relative_markup( $total, $relative );
		} else {
			$total = $this->maybe_format( $total );
		}

		// Reset query vars.
		$this->post_query();

		return $total;
	}

	/**
	 * Calculate most downloaded products.
	 *
	 * @since 3.0
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class.
	 *
	 *     @type string $start     Start day and time (based on the beginning of the given day).
	 *     @type string $end       End day and time (based on the end of the given day).
	 *     @type string $range     Date range. If a range is passed, this will override and `start` and `end`
	 *                             values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type string $function  SQL function. Accepts `COUNT` and `AVG`. Default `COUNT`.
	 *     @type string $where_sql Reserved for internal use. Allows for additional WHERE clauses to be appended
	 *                             to the query.
	 *     @type string $output    The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 *
	 * @return array Array of objects with most valuable order items. Each object has the product ID, number of downloads,
	 *               and an instance of EDD_Download.
	 */
	public function get_most_downloaded_products( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_logs_file_downloads;
		$this->query_vars['column']            = 'id';
		$this->query_vars['date_query_column'] = 'date_created';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		// By default, the most valuable customer is returned.
		$number = isset( $this->query_vars['number'] )
			? absint( $this->query_vars['number'] )
			: 1;

		$sql = "SELECT product_id, file_id, COUNT(id) AS total
				FROM {$this->query_vars['table']}
				WHERE 1=1 {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}
				GROUP BY product_id
				ORDER BY total DESC
				LIMIT {$number}";

		$result = $this->get_db()->get_results( $sql );

		array_walk( $result, function ( &$value ) {

			// Format resultant object.
			$value->product_id = absint( $value->product_id );
			$value->file_id    = absint( $value->file_id );
			$value->total      = absint( $value->total );

			// Add instance of EDD_Download to resultant object.
			$value->object = edd_get_download( $value->product_id );
		} );

		// Reset query vars.
		$this->post_query();

		return $result;
	}

	/**
	 * Calculate average number of file downloads.
	 *
	 * @since 3.0
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class.
	 *
	 *     @type string $start     Start day and time (based on the beginning of the given day).
	 *     @type string $end       End day and time (based on the end of the given day).
	 *     @type string $range     Date range. If a range is passed, this will override and `start` and `end`
	 *                             values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 *     @type string $function  SQL function. Accepts `COUNT` and `AVG`. Default `COUNT`.
	 *     @type string $where_sql Reserved for internal use. Allows for additional WHERE clauses to be appended
	 *                             to the query.
	 *     @type string $output    The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 *
	 * @return int Average file downloads.
	 */
	public function get_average_file_download_count( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_logs_file_downloads;
		$this->query_vars['column']            = 'customer_id';
		$this->query_vars['date_query_column'] = 'date_created';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$product_id = ! empty( $this->query_vars['download_id'] )
			? $this->get_db()->prepare( 'AND product_id = %d', absint( $this->query_vars['download_id'] ) )
			: '';

		$price_id = $this->generate_price_id_query_sql();

		$file_id = ! empty( $this->query_vars['file_id'] )
			? $this->get_db()->prepare( 'AND file_id = %d', absint( $this->query_vars['file_id'] ) )
			: '';

		$sql = "SELECT AVG(total) AS total
				FROM (
					SELECT {$this->query_vars['column']}, COUNT(id) AS total
					FROM {$this->query_vars['table']}
					WHERE 1=1 {$product_id} {$price_id} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}
					GROUP BY {$this->query_vars['column']}
				) o";

		$result = $this->get_db()->get_var( $sql );

		$result = null === $result
			? 0
			: absint( $result );

		// Reset query vars.
		$this->post_query();

		return $result;
	}

	/** Private Methods ******************************************************/

	/**
	 * Parse query vars to be passed to the calculation methods.
	 *
	 * @since 3.0
	 * @access private
	 *
	 * @see \EDD\Stats::__construct()
	 *
	 * @param array $query Array of arguments. See \EDD\Stats::__construct().
	 */
	private function parse_query( $query = array() ) {
		$query_var_defaults = array(
			'start'             => '',
			'end'               => '',
			'range'             => '',
			'exclude_taxes'     => false,
			'currency'          => false,
			'currency_sql'      => '',
			'status'            => array(),
			'status_sql'        => '',
			'type'              => array(),
			'type_sql'          => '',
			'where_sql'         => '',
			'date_query_sql'    => '',
			'date_query_column' => '',
			'column'            => '',
			'table'             => '',
			'function'          => 'SUM',
			'output'            => 'raw',
			'relative'          => false,
			'relative_start'    => '',
			'relative_end'      => '',
			'grouped'           => false,
			'product_id'        => '',
			'price_id'          => null,
			'revenue_type'      => 'gross',
			'country'           => '',
			'region'            => '',
		);

		if ( empty( $this->query_vars ) ) {
			$this->query_vars_defaults = $this->query_vars = wp_parse_args( $query, $query_var_defaults );
		} else {
			$this->query_vars = wp_parse_args( $query, $this->query_vars );
		}

		// Use Carbon to set up start and end date based on range passed.
		if ( ! empty( $this->query_vars['range'] ) && isset( $this->date_ranges[ $this->query_vars['range'] ] ) ) {

			if ( ! empty( $this->date_ranges[ $this->query_vars['range'] ]['start'] ) ) {
				$this->query_vars['start'] = $this->date_ranges[ $this->query_vars['range'] ]['start']->format( 'mysql' );
			}

			if ( ! empty( $this->date_ranges[ $this->query_vars['range'] ]['end'] ) ) {
				$this->query_vars['end'] = $this->date_ranges[ $this->query_vars['range'] ]['end']->format( 'mysql' );
			}
		}

		// Use Carbon to set up start and end date based on range passed.
		if ( true === $this->query_vars['relative'] && ! empty( $this->query_vars['range'] ) && isset( $this->relative_date_ranges[ $this->query_vars['range'] ] ) ) {

			if ( ! empty( $this->relative_date_ranges[ $this->query_vars['range'] ]['start'] ) ) {
				$this->query_vars['relative_start'] = $this->relative_date_ranges[ $this->query_vars['range'] ]['start']->format( 'mysql' );
			}

			if ( ! empty( $this->relative_date_ranges[ $this->query_vars['range'] ]['end'] ) ) {
				$this->query_vars['relative_end'] = $this->relative_date_ranges[ $this->query_vars['range'] ]['end']->format( 'mysql' );
			}
		}

		// Validate currency.
		if ( empty( $this->query_vars['currency'] ) ) {
			$this->query_vars['currency'] = false;
		} elseif ( array_key_exists( strtoupper( $this->query_vars['currency'] ), edd_get_currencies() ) ) {
			$this->query_vars['currency'] = strtoupper( $this->query_vars['currency'] );
		} else {
			$this->query_vars['currency'] = 'convert';
		}

		// Correctly format functions and column names.
		if ( ! empty( $this->query_vars['function'] ) ) {
			$this->query_vars['function'] = strtoupper( $this->query_vars['function'] );
		}

		if ( ! empty( $this->query_vars['column'] ) ) {
			$this->query_vars['column'] = strtolower( $this->query_vars['column'] );
		}

		/** Parse country ****************************************************/
		$country = isset( $this->query_vars['country'] )
			? sanitize_text_field( $this->query_vars['country'] )
			: '';

		if ( $country ) {
			$country_list = array_filter( edd_get_country_list() );

			// Maybe convert country code to country name.
			$country = in_array( $country, array_flip( $country_list ), true )
				? $country_list[ $country ]
				: $country;

			// Ensure a valid county has been passed.
			$country = in_array( $country, $country_list, true )
				? $country
				: null;

			// Convert back to country code for SQL query.
			$country_list                = array_flip( $country_list );
			$this->query_vars['country'] = is_null( $country )
				? ''
				: $country_list[ $country ];
		}

		/** Parse state ******************************************************/

		$state = isset( $this->query_vars['region'] )
			? sanitize_text_field( $this->query_vars['region'] )
			: '';

		// Only parse state if one was passed.
		if ( $state ) {
			$state_list = array_filter( edd_get_shop_states( $this->query_vars['country'] ) );

			// Maybe convert state code to state name.
			$state = in_array( $state, array_flip( $state_list ), true )
				? $state_list[ $state ]
				: $state;

			// Ensure a valid state has been passed.
			$state = in_array( $state, $state_list, true )
				? $state
				: null;

			// Convert back to state code for SQL query.
			$state_codes                = array_flip( $state_list );
			$this->query_vars['region'] = is_null( $state )
				? ''
				: $state_codes[ $state ];
		}

		/**
		 * Fires after the item query vars have been parsed.
		 *
		 * @since 3.0
		 *
		 * @param \EDD\Stats &$this The \EDD\Stats (passed by reference).
		 */
		do_action_ref_array( 'edd_order_stats_parse_query', array( &$this ) );
	}

	/**
	 * Ensures arguments exist before going ahead and calculating statistics.
	 *
	 * @since 3.0
	 * @access private
	 *
	 * @param array $query
	 */
	private function pre_query( $query = array() ) {

		// Maybe parse query.
		if ( ! empty( $query ) ) {
			$this->parse_query( $query );
		}

		// Generate date query SQL if dates have been set.
		if ( ! empty( $this->query_vars['start'] ) || ! empty( $this->query_vars['end'] ) ) {
			$date_query_sql = ' AND ';

			if ( ! empty( $this->query_vars['start'] ) ) {
				$start_date      = EDD()->utils->date( $this->query_vars['start'], edd_get_timezone_id(), false )->format( 'mysql' );
				$date_query_sql .= "{$this->query_vars['table']}.{$this->query_vars['date_query_column']} ";
				$date_query_sql .= $this->get_db()->prepare( '>= %s', $start_date );
			}

			// Join dates with `AND` if start and end date set.
			if ( ! empty( $this->query_vars['start'] ) && ! empty( $this->query_vars['end'] ) ) {
				$date_query_sql .= ' AND ';
			}

			if ( ! empty( $this->query_vars['end'] ) ) {
				$end_date        = EDD()->utils->date( $this->query_vars['end'], edd_get_timezone_id(), false )->format( 'mysql' );
				$date_query_sql .= $this->get_db()->prepare( "{$this->query_vars['table']}.{$this->query_vars['date_query_column']} <= %s", $end_date );
			}

			$this->query_vars['date_query_sql'] = $date_query_sql;
		}

		// Generate status SQL if statuses have been set.
		if ( ! empty( $this->query_vars['status'] ) ) {
			if ( 'any' === $this->query_vars['status'] ) {
				$this->query_vars['status_sql'] = '';
			} else {
				$this->query_vars['status'] = array_map( 'sanitize_text_field', $this->query_vars['status'] );

				$placeholders = $this->get_placeholder_string( $this->query_vars['status'] );

				$this->query_vars['status_sql'] = $this->get_db()->prepare( "AND {$this->query_vars['table']}.status IN ({$placeholders})", $this->query_vars['status'] );
			}
		}

		if ( ! empty( $this->query_vars['type'] ) ) {

			// We always want to format this as an array, so account for a possible string.
			if ( ! is_array( $this->query_vars['type'] ) ) {
				$this->query_vars['type'] = array( $this->query_vars['type'] );
			}

			$this->query_vars['type'] = array_map( 'sanitize_text_field', $this->query_vars['type'] );

			$placeholders = $this->get_placeholder_string( $this->query_vars['type'] );

			$this->query_vars['type_sql'] = $this->get_db()->prepare( "AND {$this->query_vars['table']}.type IN ({$placeholders})", $this->query_vars['type'] );
		}

		if ( ! empty( $this->query_vars['currency'] ) && 'convert' !== strtolower( $this->query_vars['currency'] ) ) {
			$this->query_vars['currency_sql'] = $this->get_db()->prepare( "AND {$this->query_vars['table']}.currency = %s", $this->query_vars['currency'] );
		}
	}

	/**
	 * Runs after a query. Resets query vars back to the originals passed in via the constructor.
	 *
	 * @since 3.0
	 * @access private
	 */
	private function post_query() {
		$this->query_vars = $this->query_var_originals;
	}

	/**
	 * Format the data if requested via the query parameter.
	 *
	 * @since 3.0
	 * @access private
	 *
	 * @param mixed $data Data to format.
	 *
	 * @return mixed Raw or formatted data depending on query parameter.
	 */
	private function maybe_format( $data = null ) {

		// Bail if nothing was passed.
		if ( null === $data ) {
			return $data;
		}

		$allowed_output_formats = array( 'raw', 'typed', 'formatted' );

		// Output format. Default raw.
		$output = isset( $this->query_vars['output'] ) && in_array( $this->query_vars['output'], $allowed_output_formats, true )
			? $this->query_vars['output']
			: 'raw';

		// Return data as is if the format is raw.
		if ( 'raw' === $output ) {
			return $data;
		}

		$currency = $this->query_vars['currency'];
		if ( empty( $currency ) || 'convert' === strtolower( $currency ) ) {
			$currency = edd_get_currency();
		}

		if ( is_object( $data ) ) {
			foreach ( array_keys( get_object_vars( $data ) ) as $field ) {
				if ( is_numeric( $data->{$field} ) ) {
					$data->{$field} = edd_format_amount( $data->{$field}, true, $currency, $output );

					if ( 'formatted' === $output ) {
						$data->{$field} = edd_currency_filter( $data->{$field}, $currency );
					}
				}
			}
		} elseif ( is_array( $data ) ) {
			foreach ( array_keys( $data ) as $field ) {
				if ( is_numeric( $data[ $field ] ) ) {
					$data[ $field ] = edd_format_amount( $data[ $field ], true, $currency, $output );

					if ( 'formatted' === $output ) {
						$data[ $field ] = edd_currency_filter( $data[ $field ], $currency );
					}
				}
			}
		} else {
			if ( is_numeric( $data ) ) {
				$data = edd_format_amount( $data, true, $currency, $output );

				if ( 'formatted' === $output ) {
					$data = edd_currency_filter( $data, $currency );
				}
			}
		}

		return $data;
	}

	/**
	 * Generate date query SQL for relative time periods.
	 *
	 * @since 3.0
	 * @access protected
	 *
	 * @return string Date query SQL.
	 */
	private function generate_relative_date_query_sql() {

		// Bail if relative calculation not requested.
		if ( false === $this->query_vars['relative'] ) {
			return '';
		}

		// Generate date query SQL if dates have been set.
		if ( ! empty( $this->query_vars['relative_start'] ) || ! empty( $this->query_vars['relative_end'] ) ) {
			$date_query_sql = "AND {$this->query_vars['table']}.{$this->query_vars['date_query_column']} ";

			if ( ! empty( $this->query_vars['relative_start'] ) ) {
				$date_query_sql .= $this->get_db()->prepare( '>= %s', $this->query_vars['relative_start'] );
			}

			// Join dates with `AND` if start and end date set.
			if ( ! empty( $this->query_vars['relative_start'] ) && ! empty( $this->query_vars['relative_end'] ) ) {
				$date_query_sql .= ' AND ';
			}

			if ( ! empty( $this->query_vars['relative_end'] ) ) {
				$date_query_sql .= $this->get_db()->prepare( "{$this->query_vars['table']}.{$this->query_vars['date_query_column']} <= %s", $this->query_vars['relative_end'] );
			}

			return $date_query_sql;
		}
	}

	/**
	 * Generates price ID query SQL.
	 *
	 * @since 3.0
	 * @return string
	 */
	private function generate_price_id_query_sql() {
		return ! is_null( $this->query_vars['price_id'] ) && is_numeric( $this->query_vars['price_id'] )
			? $this->get_db()->prepare( "AND {$this->query_vars['table']}.price_id = %d", absint( $this->query_vars['price_id'] ) )
			: '';
	}

	/** Private Getters *******************************************************/

	/**
	 * Return the global database interface.
	 *
	 * @since 3.0
	 * @access private
	 * @static
	 *
	 * @return \wpdb|\stdClass
	 */
	private static function get_db() {
		return isset( $GLOBALS['wpdb'] )
			? $GLOBALS['wpdb']
			: new \stdClass();
	}

	/** Private Setters ******************************************************/

	/**
	 * Set up the date ranges available.
	 *
	 * @since 3.0
	 * @access private
	 */
	private function set_date_ranges() {

		// Retrieve the time in UTC for the date ranges to be correctly parsed.
		$date = EDD()->utils->date( 'now', edd_get_timezone_id(), false );

		$date_filters = Reports\get_dates_filter_options();
		$filter       = Reports\get_filter_value( 'dates' );

		foreach ( $date_filters as $range => $label ) {
			$this->date_ranges[ $range ]          = Reports\parse_dates_for_range( $range );
			$this->relative_date_ranges[ $range ] = Reports\parse_relative_dates_for_range( $range );
		}

	}

	/**
	 * Based on the query_vars['revenue_type'], use gross or net statuses.
	 *
	 * @since 3.0
	 *
	 * @return array The statuses of orders to use for the stats generation.
	 */
	private function get_revenue_type_statuses() {
		if ( 'net' === $this->query_vars['revenue_type'] ) {
			return edd_get_net_order_statuses();
		}

		return edd_get_gross_order_statuses();
	}

	/**
	 * Based on the query_vars['revenue_type'], use just sale or also include refunds.
	 *
	 * @since 3.0
	 *
	 * @return array The order types to use when generating stats.
	 */
	private function get_revenue_type_order_types() {
		$order_types = array( 'sale' );
		if ( 'net' === $this->query_vars['revenue_type'] ) {
			$order_types[] = 'refund';
		}

		return $order_types;
	}

	/**
	 * Calculates the relative change between two datasets
	 * and outputs an array of details about comparison.
	 *
	 * @since 3.1
	 *
	 * @param int|float $total     The primary value result for the stat.
	 * @param int|float $relative  The value relative to the previous date range.
	 * @param bool      $reverse   If the stat being displayed is a 'reverse' state, where lower is better.
	 *
	 * @return array Details about the relative change between two datasets.
	 */
	public function generate_relative_data( $total = 0, $relative = 0, $reverse = false ) {
		$output = array(
			'comparable'                  => true,
			'no_change'                   => false,
			'percentage_change'           => false,
			'formatted_percentage_change' => false,
			'positive_change'             => false,
			'total'                       => $total,
			'relative'                    => $relative,
			'reverse'                     => $reverse,
		);

		if ( ( floatval( 0 ) === floatval( $total ) && floatval( 0 ) === floatval( $relative ) ) || ( $total === $relative ) ) {
			// There is no change between datasets.
			$output['no_change'] = true;
		} else if ( floatval( 0 ) !== floatval( $relative ) ) {
			// There is a calculatable difference between datasets.
			$percentage_change           = ( $total - $relative ) / $relative * 100;
			$formatted_percentage_change = absint( $percentage_change );
			$positive_change             = false;

			if ( absint( $percentage_change ) < 100 ) {
				$formatted_percentage_change = number_format( $percentage_change, 2 );
				$formatted_percentage_change = $formatted_percentage_change < 1 ? $formatted_percentage_change * -1 : $formatted_percentage_change;
			}

			// Check if stat is in a 'reverse' state, where lower is better.
			$positive_change = (bool) ! $reverse;
			if ( 0 > $percentage_change ) {
				$positive_change = (bool) $reverse;
			}

			$output['percentage_change']           = $percentage_change;
			$output['formatted_percentage_change'] = $formatted_percentage_change;
			$output['positive_change']             = $positive_change;
		} else {
			// There is no data to compare.
			$output['comparable'] = false;
		}

		return $output;
	}

	/**
	 * Generates output for the report tiles when a relative % change is requested.
	 *
	 * @since 3.0
	 *
	 * @param int|float $total     The primary value result for the stat.
	 * @param int|float $relative  The value relative to the previous date range.
	 * @param bool      $reverse   If the stat being displayed is a 'reverse' state, where lower is better.
	 */
	private function generate_relative_markup( $total = 0, $relative = 0, $reverse = false ) {

		$relative_data   = $this->generate_relative_data( $total, $relative, $reverse );
		$total_output    = $this->maybe_format( $relative_data['total'] );
		$relative_markup = '';

		if ( $relative_data['no_change'] ) {
			$relative_output = esc_html__( 'No Change', 'easy-digital-downloads' );
		} else if ( $relative_data['comparable'] ) {
			if ( 0 < $relative_data['percentage_change'] ) {
				$direction       = $relative_data['reverse'] ? 'up reverse' : 'up';
				$relative_output = '<span class="dashicons dashicons-arrow-' . esc_attr( $direction ) . '"></span> ' . $relative_data['formatted_percentage_change'] . '%';
			} else {
				$direction       = $relative_data['reverse'] ? 'down reverse' : 'down';
				$relative_output = '<span class="dashicons dashicons-arrow-' . esc_attr( $direction ) . '"></span> ' . $relative_data['formatted_percentage_change'] . '%';
			}
		} else {
			$relative_output = '<span aria-hidden="true">&mdash;</span><span class="screen-reader-text">' . __( 'No data to compare', 'easy-digital-downloads' ) . '</span>';
		}

		$relative_markup = $total_output;
		if ( ! empty( $relative_output ) ) {
			$relative_markup .= '<div class="tile-relative">' . $relative_output . '</div>';
		}

		return $relative_markup;
	}

	/**
	 * Gets a placeholder string from an array.
	 *
	 * @since 3.1
	 * @param array $array
	 * @return string
	 */
	private function get_placeholder_string( $array ) {
		return implode( ', ', array_fill( 0, count( $array ), '%s' ) );
	}
}
