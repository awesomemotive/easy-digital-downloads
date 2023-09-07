<?php
/**
 * Base Custom Database Table Date Query Class.
 *
 * @package     Database
 * @subpackage  Date
 * @copyright   Copyright (c) 2020
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace EDD\Database\Queries;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use EDD\Database\Base;

/**
 * Class for generating SQL clauses that filter a primary query according to date.
 *
 * Is heavily inspired by the WP_Date_Query class in WordPress, with changes to make
 * it more flexible for custom tables and their columns.
 *
 * Date is a helper that allows primary query classes, such as WP_Query, to filter
 * their results by date columns, by generating `WHERE` subclauses to be attached to the
 * primary SQL query string.
 *
 * Attempting to filter by an invalid date value (eg month=13) will generate SQL that will
 * return no results. In these cases, a _doing_it_wrong() error notice is also thrown.
 * See Date::validate_date_values().
 *
 * @link https://codex.wordpress.org/Function_Reference/WP_Query Codex page.
 *
 * @since 1.0.0
 */
class Date extends Base {

	/**
	 * Array of date queries.
	 *
	 * See Date::__construct() for information on date query arguments.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	public $queries = array();

	/**
	 * The default relation between top-level queries. Can be either 'AND' or 'OR'.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	public $relation = 'AND';

	/**
	 * The column to query against. Can be changed via the query arguments.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	public $column = 'date_created';

	/**
	 * The value comparison operator. Can be changed via the query arguments.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	public $compare = '=';

	/**
	 * Supported time-related parameter keys.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	public $time_keys = array(
		'after',
		'before',
		'value',
		'year',
		'month',
		'monthnum',
		'week',
		'w',
		'dayofyear',
		'day',
		'dayofweek',
		'dayofweek_iso',
		'hour',
		'minute',
		'second'
	);

	/**
	 * Supported comparison types
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	public $comparison_keys = array(
		'=',
		'!=',
		'>',
		'>=',
		'<',
		'<=',
		'IN',
		'NOT IN',
		'BETWEEN',
		'NOT BETWEEN',
		'IS NULL',
	);

	/**
	 * Supported multi-value comparison types
	 *
	 * @since 1.1.0
	 * @var   array
	 */
	public $multi_value_keys = array(
		'IN',
		'NOT IN',
		'BETWEEN',
		'NOT BETWEEN'
	);

	/**
	 * Supported relation types
	 *
	 * @since 1.1.0
	 * @var   array
	 */
	public $relation_keys = array(
		'OR',
		'AND'
	);

	/**
	 * Constructor.
	 *
	 * Time-related parameters that normally require integer values ('year', 'month', 'week', 'dayofyear', 'day',
	 * 'dayofweek', 'dayofweek_iso', 'hour', 'minute', 'second') accept arrays of integers for some values of
	 * 'compare'. When 'compare' is 'IN' or 'NOT IN', arrays are accepted; when 'compare' is 'BETWEEN' or 'NOT
	 * BETWEEN', arrays of two valid values are required. See individual argument descriptions for accepted values.
	 *
	 * @since 1.0.0
	 *
	 * @param array $date_query {
	 *     Array of date query clauses.
	 *
	 *     @type array {
	 *         @type string $column   Optional. The column to query against. If undefined, inherits the value of
	 *                                'date_created'. Accepts 'date_created', 'date_created_gmt',
	 *                                'post_modified','post_modified_gmt', 'comment_date', 'comment_date_gmt'.
	 *                                Default 'date_created'.
	 *         @type string $compare  Optional. The comparison operator. Accepts '=', '!=', '>', '>=', '<', '<=',
	 *                                'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN'. Default '='.
	 *         @type string $relation Optional. The boolean relationship between the date queries. Accepts 'OR' or 'AND'.
	 *                                Default 'OR'.
	 *         @type array {
	 *             Optional. An array of first-order clause parameters, or another fully-formed date query.
	 *
	 *             @type string|array $before {
	 *                 Optional. Date to retrieve posts before. Accepts `strtotime()`-compatible string,
	 *                 or array of 'year', 'month', 'day' values.
	 *
	 *                 @type string $year  The four-digit year. Default empty. Accepts any four-digit year.
	 *                 @type string $month Optional when passing array.The month of the year.
	 *                                     Default (string:empty)|(array:1). Accepts numbers 1-12.
	 *                 @type string $day   Optional when passing array.The day of the month.
	 *                                     Default (string:empty)|(array:1). Accepts numbers 1-31.
	 *             }
	 *             @type string|array $after {
	 *                 Optional. Date to retrieve posts after. Accepts `strtotime()`-compatible string,
	 *                 or array of 'year', 'month', 'day' values.
	 *
	 *                 @type string $year  The four-digit year. Accepts any four-digit year. Default empty.
	 *                 @type string $month Optional when passing array. The month of the year. Accepts numbers 1-12.
	 *                                     Default (string:empty)|(array:12).
	 *                 @type string $day   Optional when passing array.The day of the month. Accepts numbers 1-31.
	 *                                     Default (string:empty)|(array:last day of month).
	 *             }
	 *             @type string       $column        Optional. Used to add a clause comparing a column other than the
	 *                                               column specified in the top-level `$column` parameter. Accepts
	 *                                               'date_created', 'date_created_gmt', 'post_modified', 'post_modified_gmt',
	 *                                               'comment_date', 'comment_date_gmt'. Default is the value of
	 *                                               top-level `$column`.
	 *             @type string       $compare       Optional. The comparison operator. Accepts '=', '!=', '>', '>=',
	 *                                               '<', '<=', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN'. 'IN',
	 *                                               'NOT IN', 'BETWEEN', and 'NOT BETWEEN'. Comparisons support
	 *                                               arrays in some time-related parameters. Default '='.
	 *             @type bool         $inclusive     Optional. Include results from dates specified in 'before' or
	 *                                               'after'. Default false.
	 *             @type int|array    $year          Optional. The four-digit year number. Accepts any four-digit year
	 *                                               or an array of years if `$compare` supports it. Default empty.
	 *             @type int|array    $month         Optional. The two-digit month number. Accepts numbers 1-12 or an
	 *                                               array of valid numbers if `$compare` supports it. Default empty.
	 *             @type int|array    $week          Optional. The week number of the year. Accepts numbers 0-53 or an
	 *                                               array of valid numbers if `$compare` supports it. Default empty.
	 *             @type int|array    $dayofyear     Optional. The day number of the year. Accepts numbers 1-366 or an
	 *                                               array of valid numbers if `$compare` supports it.
	 *             @type int|array    $day           Optional. The day of the month. Accepts numbers 1-31 or an array
	 *                                               of valid numbers if `$compare` supports it. Default empty.
	 *             @type int|array    $dayofweek     Optional. The day number of the week. Accepts numbers 1-7 (1 is
	 *                                               Sunday) or an array of valid numbers if `$compare` supports it.
	 *                                               Default empty.
	 *             @type int|array    $dayofweek_iso Optional. The day number of the week (ISO). Accepts numbers 1-7
	 *                                               (1 is Monday) or an array of valid numbers if `$compare` supports it.
	 *                                               Default empty.
	 *             @type int|array    $hour          Optional. The hour of the day. Accepts numbers 0-23 or an array
	 *                                               of valid numbers if `$compare` supports it. Default empty.
	 *             @type int|array    $minute        Optional. The minute of the hour. Accepts numbers 0-60 or an array
	 *                                               of valid numbers if `$compare` supports it. Default empty.
	 *             @type int|array    $second        Optional. The second of the minute. Accepts numbers 0-60 or an
	 *                                               array of valid numbers if `$compare` supports it. Default empty.
	 *         }
	 *     }
	 * }
	 */
	public function __construct( $date_query = array() ) {

		// Bail if not an array.
		if ( ! is_array( $date_query ) ) {
			return;
		}

		// Support for passing time-based keys in the top level of the array.
		if ( ! isset( $date_query[0] ) && ! empty( $date_query ) ) {
			$date_query = array( $date_query );
		}

		// Bail if empty.
		if ( empty( $date_query ) ) {
			return;
		}

		// Set column, compare, relation, and queries.
		$this->column   = $this->get_column( $date_query );
		$this->compare  = $this->get_compare( $date_query );
		$this->relation = $this->get_relation( $date_query );
		$this->queries  = $this->sanitize_query( $date_query );
	}

	/**
	 * Recursive-friendly query sanitizer.
	 *
	 * Ensures that each query-level clause has a 'relation' key, and that
	 * each first-order clause contains all the necessary keys from
	 * `$defaults`.
	 *
	 * @since 1.0.0
	 *
	 * @param array $queries
	 * @param array $parent_query
	 *
	 * @return array Sanitized queries.
	 */
	public function sanitize_query( $queries = array(), $parent_query = array() ) {
		// Default return value.
		$retval = array();

		// Setup defaults.
		$defaults = array(
			'column'   => $this->get_column(),
			'compare'  => $this->get_compare(),
			'relation' => $this->get_relation()
		);

		// Numeric keys should always have array values.
		foreach ( $queries as $qkey => $qvalue ) {
			if ( is_numeric( $qkey ) && ! is_array( $qvalue ) ) {
				unset( $queries[ $qkey ] );
			}
		}

		// Each query should have a value for each default key.
		// Inherit from the parent when possible.
		foreach ( $defaults as $dkey => $dvalue ) {
			// Skip if already set.
			if ( isset( $queries[ $dkey ] ) ) {
				continue;
			}

			// Set the query.
			if ( isset( $parent_query[ $dkey ] ) ) {
				$queries[ $dkey ] = $parent_query[ $dkey ];
			} else {
				$queries[ $dkey ] = $dvalue;
			}
		}

		// Validate the dates passed in the query.
		if ( $this->is_first_order_clause( $queries ) || $this->is_null_check( $queries ) ) {
			$this->validate_date_values( $queries );
		}

		// Add queries to return array.
		foreach ( $queries as $key => $q ) {

			if ( ! is_array( $q ) || in_array( $key, $this->time_keys, true ) ) { // This is a first-order query. Trust the values and sanitize when building SQL.
				$retval[ $key ] = $q;
			} elseif ( array_key_exists( 'compare', $q ) && 'IS NULL' === $q['compare'] ) { // If this isn't a time query, but is a compare for `IS NULL` we can trust the value.
				$retval[ $key ] = $q;
			} else { // Any array without a time key is another query, so we recurse.
				$retval[] = $this->sanitize_query( $q, $queries );
			}
		}

		// Return sanitized queries.
		return $retval;
	}

	/**
	 * Determine whether this is a first-order clause.
	 *
	 * Checks to see if the current clause has any time-related keys.
	 * If so, it's first-order.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $query Query clause.
	 *
	 * @return bool True if this is a first-order clause.
	 */
	protected function is_first_order_clause( $query = array() ) {
		$time_keys = array_intersect( $this->time_keys, array_keys( $query ) );

		return ! empty( $time_keys );
	}

	/**
	 * Determines whether this is a null check.
	 *
	 * This allows the `compare` of a date query to be `IS NULL`. Usually this wouldn't be something used in date columns,
	 * but in some tables the date is an optional value and this allows for querying for null values.
	 *
	 * @since 3.2.0
	 *
	 * @param array $queries A date query or a date subquery.
	 *
	 * @return bool True if this is a null check.
	 */
	protected function is_null_check( $queries = array() ) {
		return ( array_key_exists( 'compare', $queries ) && 'IS NULL' === $queries['compare'] );
	}

	/**
	 * Determines and validates what comparison operator to use.
	 *
	 * @since 1.0.0
	 *
	 * @param array $query A date query or a date subquery.
	 *
	 * @return string The comparison operator.
	 */
	public function get_column( $query = array() ) {

		// Use column if passed
		$retval = ! empty( $query['column'] )
			? esc_sql( $this->validate_column( $query['column'] ) )
			: $this->column;

		return $retval;
	}

	/**
	 * Determines and validates what comparison operator to use.
	 *
	 * @since 1.0.0
	 *
	 * @param array $query A date query or a date subquery.
	 *
	 * @return string The comparison operator.
	 */
	public function get_compare( $query = array() ) {

		// Compare must be in the allowed array
		$retval = ! empty( $query['compare'] ) && in_array( $query['compare'], $this->comparison_keys, true )
			? strtoupper( $query['compare'] )
			: $this->compare;

		return $retval;
	}

	/**
	 * Determines and validates what relation to use.
	 *
	 * @since 1.0.0
	 *
	 * @param array $query A date query or a date subquery.
	 * @return string The relation operator.
	 */
	public function get_relation( $query = array() ) {

		// Relation must be in the allowed array
		$retval = ! empty( $query['relation'] ) && in_array( $query['relation'], $this->relation_keys, true )
			? strtoupper( $query['relation'] )
			: $this->relation;

		return $retval;
	}

	/**
	 * Validates the given date_query values.
	 *
	 * Note that date queries with invalid date ranges are allowed to
	 * continue (though of course no items will be found for impossible dates).
	 * This method only generates debug notices for these cases.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $date_query The date_query array.
	 *
	 * @return bool  True if all values in the query are valid, false if one or more fail.
	 */
	public function validate_date_values( $date_query = array() ) {

		// Bail if empty.
		if ( empty( $date_query ) ) {
			return false;
		}

		$valid = true;

		// Allow IS NULL values.
		if ( array_key_exists( 'compare', $date_query ) && 'IS NULL' === $date_query['compare'] ) {
			return $valid;
		}

		/*
		 * Validate 'before' and 'after' up front, then let the
		 * validation routine continue to be sure that all invalid
		 * values generate errors too.
		 */
		if ( array_key_exists( 'before', $date_query ) && is_array( $date_query['before'] ) ) {
			$valid = $this->validate_date_values( $date_query['before'] );
		}

		if ( array_key_exists( 'after', $date_query ) && is_array( $date_query['after'] ) ) {
			$valid = $this->validate_date_values( $date_query['after'] );
		}

		// Values are passthroughs.
		if ( array_key_exists( 'value', $date_query ) ) {
			$valid = true;
		}

		// Array containing all min-max checks.
		$min_max_checks = array();

		// Days per year.
		if ( array_key_exists( 'year', $date_query ) ) {
			/*
			 * If a year exists in the date query, we can use it to get the days.
			 * If multiple years are provided (as in a BETWEEN), use the first one.
			 */
			if ( is_array( $date_query['year'] ) ) {
				$_year = reset( $date_query['year'] );
			} else {
				$_year = $date_query['year'];
			}

			$max_days_of_year = date( 'z', mktime( 0, 0, 0, 12, 31, $_year ) ) + 1;

		// Otherwise we use the max of 366 (leap-year)
		} else {
			$max_days_of_year = 366;
		}

		// Days of year.
		$min_max_checks['dayofyear'] = array(
			'min' => 1,
			'max' => $max_days_of_year,
		);

		// Days per week.
		$min_max_checks['dayofweek'] = array(
			'min' => 1,
			'max' => 7,
		);

		// Days per week.
		$min_max_checks['dayofweek_iso'] = array(
			'min' => 1,
			'max' => 7,
		);

		// Months per year.
		$min_max_checks['month'] = array(
			'min' => 1,
			'max' => 12,
		);

		// Weeks per year.
		if ( isset( $_year ) ) {
			/*
			 * If we have a specific year, use it to calculate number of weeks.
			 * Note: the number of weeks in a year is the date in which Dec 28 appears.
			 */
			$week_count = date( 'W', mktime( 0, 0, 0, 12, 28, $_year ) );

		// Otherwise set the week-count to a maximum of 53.
		} else {
			$week_count = 53;
		}

		// Weeks per year.
		$min_max_checks['week'] = array(
			'min' => 1,
			'max' => $week_count,
		);

		// Days per month.
		$min_max_checks['day'] = array(
			'min' => 1,
			'max' => 31,
		);

		// Hours per day.
		$min_max_checks['hour'] = array(
			'min' => 0,
			'max' => 23,
		);

		// Minutes per hour.
		$min_max_checks['minute'] = array(
			'min' => 0,
			'max' => 59,
		);

		// Seconds per minute.
		$min_max_checks['second'] = array(
			'min' => 0,
			'max' => 59,
		);

		// Loop through min/max checks.
		foreach ( $min_max_checks as $key => $check ) {

			// Skip if not in query.
			if ( ! array_key_exists( $key, $date_query ) ) {
				continue;
			}

			// Check for invalid values.
			foreach ( (array) $date_query[ $key ] as $_value ) {
				$is_between = ( $_value >= $check['min'] ) && ( $_value <= $check['max'] );

				if ( ! is_numeric( $_value ) || empty( $is_between ) ) {
					$valid = false;
				}
			}
		}

		// Bail if invalid query.
		if ( false === $valid ) {
			return $valid;
		}

		// Check what kinds of dates are being queried for.
		$day_exists   = array_key_exists( 'day',   $date_query ) && is_numeric( $date_query['day']   );
		$month_exists = array_key_exists( 'month', $date_query ) && is_numeric( $date_query['month'] );
		$year_exists  = array_key_exists( 'year',  $date_query ) && is_numeric( $date_query['year']  );

		// Checking at least day & month.
		if ( ! empty( $day_exists ) && ! empty( $month_exists ) ) {

			// Check for year query, or fallback to 2012 (for flexibility).
			$year = ! empty( $year_exists )
				? $date_query['year']
				: '2012';

			// Parse the date to check.
			$to_check = sprintf( '%s-%s-%s', $year, $date_query['month'], $date_query['day'] );

			// Check the date.
			if ( ! $this->checkdate( $date_query['month'], $date_query['day'], $year, $to_check ) ) {
				$valid = false;
			}
		}

		// Return if valid or not
		return $valid;
	}

	/**
	 * Validates a column name parameter.
	 *
	 * @since 1.0.0
	 *
	 * @param string $column The user-supplied column name.
	 *
	 * @return string A validated column name value.
	 */
	public function validate_column( $column = '' ) {
		return preg_replace( '/[^a-zA-Z0-9_$\.]/', '', $column );
	}

	/**
	 * Generate WHERE clause to be appended to a main query.
	 *
	 * @since 1.0.0
	 *
	 * @return string MySQL WHERE clauses.
	 */
	public function get_sql() {
		$sql = $this->get_sql_clauses();

		/**
		 * Filters the date query clauses.
		 *
		 * @since 1.0.0
		 *
		 * @param string $sql Clauses of the date query.
		 * @param Date   $this  The Date query instance.
		 */
		return apply_filters( 'get_date_sql', $sql, $this );
	}

	/**
	 * Generate SQL clauses to be appended to a main query.
	 *
	 * Called by the public Date::get_sql(), this method is abstracted
	 * out to maintain parity with the other Query classes.
	 *
	 * @since 1.0.0
	 *
	 * @return array {
	 *     Array containing JOIN and WHERE SQL clauses to append to the main query.
	 *
	 *     @type string $join  SQL fragment to append to the main JOIN clause.
	 *     @type string $where SQL fragment to append to the main WHERE clause.
	 * }
	 */
	protected function get_sql_clauses() {
		$sql = $this->get_sql_for_query( $this->queries );

		if ( ! empty( $sql['where'] ) ) {
			$sql['where'] = ' AND ' . $sql['where'];
		}

		return apply_filters( 'get_date_sql_clauses', $sql, $this );
	}

	/**
	 * Generate SQL clauses for a single query array.
	 *
	 * If nested subqueries are found, this method recurses the tree to
	 * produce the properly nested SQL.
	 *
	 * @since 1.0.0
	 *
	 * @param array $query Query to parse.
	 * @param int   $depth Optional. Number of tree levels deep we currently are.
	 *                     Used to calculate indentation. Default 0.
	 * @return array {
	 *     Array containing JOIN and WHERE SQL clauses to append to a single query array.
	 *
	 *     @type string $join  SQL fragment to append to the main JOIN clause.
	 *     @type string $where SQL fragment to append to the main WHERE clause.
	 * }
	 */
	protected function get_sql_for_query( $query = array(), $depth = 0 ) {
		$sql_chunks = array(
			'join'  => array(),
			'where' => array(),
		);

		$sql = array(
			'join'  => '',
			'where' => '',
		);

		$indent = '';
		for ( $i = 0; $i < $depth; $i++ ) {
			$indent .= '  ';
		}

		foreach ( $query as $key => $clause ) {

			if ( 'relation' === $key ) {
				$relation = $query['relation'];

			} elseif ( is_array( $clause ) ) {
				// This is a first-order clause.
				if ( $this->is_first_order_clause( $clause ) || $this->is_null_check( $clause ) ) {
					// Get clauses & where count
					$clause_sql  = $this->get_sql_for_clause( $clause, $query );
					$where_count = count( $clause_sql['where'] );

					if ( ! $where_count ) {
						$sql_chunks['where'][] = '';

					} elseif ( 1 === $where_count ) {
						$sql_chunks['where'][] = $clause_sql['where'][0];

					} else {
						$sql_chunks['where'][] = '( ' . implode( ' AND ', $clause_sql['where'] ) . ' )';
					}

					$sql_chunks['join'] = array_merge( $sql_chunks['join'], $clause_sql['join'] );

				// This is a subquery, so we recurse.
				} else {
					$clause_sql = $this->get_sql_for_query( $clause, $depth + 1 );

					$sql_chunks['where'][] = $clause_sql['where'];
					$sql_chunks['join'][]  = $clause_sql['join'];
				}
			}
		}

		// Filter to remove empties.
		$sql_chunks['join']  = array_filter( $sql_chunks['join'] );
		$sql_chunks['where'] = array_filter( $sql_chunks['where'] );

		if ( empty( $relation ) ) {
			$relation = 'AND';
		}

		// Filter duplicate JOIN clauses and combine into a single string.
		if ( ! empty( $sql_chunks['join'] ) ) {
			$sql['join'] = implode( ' ', array_unique( $sql_chunks['join'] ) );
		}

		// Generate a single WHERE clause with proper brackets and indentation.
		if ( ! empty( $sql_chunks['where'] ) ) {
			$sql['where'] = '( ' . "\n  " . $indent . implode( ' ' . "\n  " . $indent . $relation . ' ' . "\n  " . $indent, $sql_chunks['where'] ) . "\n" . $indent . ')';
		}

		// Filter and return
		return apply_filters( 'get_date_sql_for_query', $sql, $query, $depth, $this );
	}

	/**
	 * Turns a first-order date query into SQL for a WHERE clause.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $query        Date query clause.
	 * @param  array $parent_query Parent query of the current date query.
	 *
	 * @return array {
	 *     Array containing JOIN and WHERE SQL clauses to append to the main query.
	 *
	 *     @type string $join  SQL fragment to append to the main JOIN clause.
	 *     @type string $where SQL fragment to append to the main WHERE clause.
	 * }
	 */
	protected function get_sql_for_clause( $query = array(), $parent_query = array() ) {

		// The sub-parts of a $where part.
		$where_parts = array();

		$column    = $this->get_column( $query );
		$compare   = $this->get_compare( $query );
		$inclusive = ! empty( $query['inclusive'] );

		// Assign greater-than and less-than values.
		$lt = '<';
		$gt = '>';

		if ( $inclusive ) {
			$lt .= '=';
			$gt .= '=';
		}

		// NULL values.
		if ( isset( $query['compare'] ) && 'IS NULL' === $query['compare'] ) {
			$where_parts[] = "{$column} IS NULL";
		}

		// Range queries.
		if ( ! empty( $query['after'] ) ) {
			$where_parts[] = $this->get_db()->prepare( "{$column} {$gt} %s", $this->build_mysql_datetime( $query['after'], ! $inclusive ) );
		}

		if ( ! empty( $query['before'] ) ) {
			$where_parts[] = $this->get_db()->prepare( "{$column} {$lt} %s", $this->build_mysql_datetime( $query['before'], $inclusive ) );
		}

		// Specific value queries.
		if ( isset( $query['year'] ) && $value = $this->build_numeric_value( $compare, $query['year'] ) ) {
			$where_parts[] = "YEAR( {$column} ) {$compare} {$value}";
		}

		if ( isset( $query['month'] ) && $value = $this->build_numeric_value( $compare, $query['month'] ) ) {
			$where_parts[] = "MONTH( {$column} ) {$compare} {$value}";
		} elseif ( isset( $query['monthnum'] ) && $value = $this->build_numeric_value( $compare, $query['monthnum'] ) ) {
			$where_parts[] = "MONTH( {$column} ) {$compare} {$value}";
		}

		if ( isset( $query['week'] ) && false !== ( $value = $this->build_numeric_value( $compare, $query['week'] ) ) ) {
			$where_parts[] = _wp_mysql_week( $column ) . " {$compare} {$value}";
		} elseif ( isset( $query['w'] ) && false !== ( $value = $this->build_numeric_value( $compare, $query['w'] ) ) ) {
			$where_parts[] = _wp_mysql_week( $column ) . " {$compare} {$value}";
		}

		if ( isset( $query['dayofyear'] ) && $value = $this->build_numeric_value( $compare, $query['dayofyear'] ) ) {
			$where_parts[] = "DAYOFYEAR( {$column} ) {$compare} {$value}";
		}

		if ( isset( $query['day'] ) && $value = $this->build_numeric_value( $compare, $query['day'] ) ) {
			$where_parts[] = "DAYOFMONTH( {$column} ) {$compare} {$value}";
		}

		if ( isset( $query['dayofweek'] ) && $value = $this->build_numeric_value( $compare, $query['dayofweek'] ) ) {
			$where_parts[] = "DAYOFWEEK( {$column} ) {$compare} {$value}";
		}

		if ( isset( $query['dayofweek_iso'] ) && $value = $this->build_numeric_value( $compare, $query['dayofweek_iso'] ) ) {
			$where_parts[] = "WEEKDAY( {$column} ) + 1 {$compare} {$value}";
		}

		// Straight value compare
		if ( isset( $query['value'] ) ) {
			$value         = $this->build_value( $compare, $query['value'] );
			$where_parts[] = "{$column} {$compare} $value";
		}

		// Hour/Minute/Second
		if ( isset( $query['hour'] ) || isset( $query['minute'] ) || isset( $query['second'] ) ) {

			// Avoid notices.
			foreach ( array( 'hour', 'minute', 'second' ) as $unit ) {
				if ( ! isset( $query[ $unit ] ) ) {
					$query[ $unit ] = null;
				}
			}

			$time_query = $this->build_time_query( $column, $compare, $query['hour'], $query['minute'], $query['second'] );

			if ( ! empty( $time_query ) ) {
				$where_parts[] = $time_query;
			}
		}

		/*
		 * Return an array of 'join' and 'where' for compatibility
		 * with other query classes.
		 */
		return array(
			'where' => $where_parts,
			'join'  => array(),
		);
	}

	/**
	 * Builds and validates a value string based on the comparison operator.
	 *
	 * @since 1.0.0
	 *
	 * @param string $compare The compare operator to use
	 * @param string|array $value The value
	 *
	 * @return string|false|int The value to be used in SQL or false on error.
	 */
	public function build_numeric_value( $compare = '=', $value = null ) {

		// Bail if null value
		if ( is_null( $value ) ) {
			return false;
		}

		switch ( $compare ) {
			case 'IN':
			case 'NOT IN':
				$value = (array) $value;

				// Remove non-numeric values.
				$value = array_filter( $value, 'is_numeric' );

				if ( empty( $value ) ) {
					return false;
				}

				return '(' . implode( ',', array_map( 'intval', $value ) ) . ')';

			case 'BETWEEN':
			case 'NOT BETWEEN':
				if ( ! is_array( $value ) || 2 != count( $value ) ) {
					$value = array( $value, $value );
				} else {
					$value = array_values( $value );
				}

				// If either value is non-numeric, bail.
				foreach ( $value as $v ) {
					if ( ! is_numeric( $v ) ) {
						return false;
					}
				}

				$value = array_map( 'intval', $value );

				return $value[0] . ' AND ' . $value[1];

			case 'IS NULL':
				return '';

			default:
				if ( ! is_numeric( $value ) ) {
					return false;
				}

				return (int) $value;
		}
	}

	/**
	 * Builds and validates a value string based on the comparison operator.
	 *
	 * @since 1.0.0
	 *
	 * @param string $compare The compare operator to use
	 * @param string|array $value The value
	 *
	 * @return string|false|int The value to be used in SQL or false on error.
	 */
	public function build_value( $compare = '=', $value = null ) {

		if ( in_array( $compare, $this->multi_value_keys, true ) ) {
			if ( ! is_array( $value ) ) {
				$value = preg_split( '/[,\s]+/', $value );
			}
		} else {
			$value = trim( $value );
		}

		switch ( $compare ) {
			case 'IN':
			case 'NOT IN':
				$compare_string = '(' . substr( str_repeat( ',%s', count( $value ) ), 1 ) . ')';
				$where          = $this->get_db()->prepare( $compare_string, $value );
				break;

			case 'BETWEEN':
			case 'NOT BETWEEN':
				$value = array_slice( $value, 0, 2 );
				$where = $this->get_db()->prepare( '%s AND %s', $value );
				break;

			case 'LIKE':
			case 'NOT LIKE':
				$value = '%' . $this->get_db()->esc_like( $value ) . '%';
				$where = $this->get_db()->prepare( '%s', $value );
				break;

			// EXISTS with a value is interpreted as '='.
			case 'EXISTS':
				$compare = '=';
				$where   = $this->get_db()->prepare( '%s', $value );
				break;

			// 'value' is ignored for NOT EXISTS and IS NULL.
			case 'NOT EXISTS':
			case 'IS NULL':
				$where = '';
				break;

			default:
				$where = $this->get_db()->prepare( '%s', $value );
				break;
		}

		return $where;
	}

	/**
	 * Builds a MySQL format date/time based on some query parameters.
	 *
	 * You can pass an array of values (year, month, etc.) with missing parameter values being defaulted to
	 * either the maximum or minimum values (controlled by the $default_to parameter). Alternatively you can
	 * pass a string that will be run through strtotime().
	 *
	 * @since 1.0.0
	 *
	 * @param string|array $datetime       An array of parameters or a strtotime() string
	 * @param bool         $default_to_max Whether to round up incomplete dates. Supported by values
	 *                                     of $datetime that are arrays, or string values that are a
	 *                                     subset of MySQL date format ('Y', 'Y-m', 'Y-m-d', 'Y-m-d H:i').
	 *                                     Default: false.
	 *
	 * @return string|false A MySQL format date/time or false on failure
	 */
	public function build_mysql_datetime( $datetime = '', $default_to_max = false ) {

		// Get current time
		$now = time();

		// Datetime is string
		if ( is_string( $datetime ) ) {

			// Define matches so linters don't complain
			$matches = array();

			/*
			 * Try to parse some common date formats, so we can detect
			 * the level of precision and support the 'inclusive' parameter.
			 */

			// Y
			if ( preg_match( '/^(\d{4})$/', $datetime, $matches ) ) {
				$datetime = array(
					'year' => intval( $matches[1] ),
				);

			// Y-m
			} elseif ( preg_match( '/^(\d{4})\-(\d{2})$/', $datetime, $matches ) ) {
				$datetime = array(
					'year'  => intval( $matches[1] ),
					'month' => intval( $matches[2] ),
				);

			// Y-m-d
			} elseif ( preg_match( '/^(\d{4})\-(\d{2})\-(\d{2})$/', $datetime, $matches ) ) {
				$datetime = array(
					'year'  => intval( $matches[1] ),
					'month' => intval( $matches[2] ),
					'day'   => intval( $matches[3] ),
				);

			// Y-m-d H:i
			} elseif ( preg_match( '/^(\d{4})\-(\d{2})\-(\d{2}) (\d{2}):(\d{2})$/', $datetime, $matches ) ) {
				$datetime = array(
					'year'   => intval( $matches[1] ),
					'month'  => intval( $matches[2] ),
					'day'    => intval( $matches[3] ),
					'hour'   => intval( $matches[4] ),
					'minute' => intval( $matches[5] ),
				);
			}

			// If no match is found, we don't support default_to_max.
			if ( ! is_array( $datetime ) ) {
				return gmdate( 'Y-m-d H:i:s', strtotime( $datetime, $now ) );
			}
		}

		// Map to ints
		$datetime = array_map( 'absint', $datetime );

		// Year
		if ( ! isset( $datetime['year'] ) ) {
			$datetime['year'] = date( 'Y', $now );
		}

		// Month
		if ( ! isset( $datetime['month'] ) ) {
			$datetime['month'] = ! empty( $default_to_max )
				? 12
				: 1;
		}

		// Day
		if ( ! isset( $datetime['day'] ) ) {
			$datetime['day'] = ! empty( $default_to_max )
				? (int) date( 't', mktime( 0, 0, 0, $datetime['month'], 1, $datetime['year'] ) )
				: 1;
		}

		// Hour
		if ( ! isset( $datetime['hour'] ) ) {
			$datetime['hour'] = ! empty( $default_to_max )
				? 23
				: 0;
		}

		// Minute
		if ( ! isset( $datetime['minute'] ) ) {
			$datetime['minute'] = ! empty( $default_to_max )
				? 59
				: 0;
		}

		// Second
		if ( ! isset( $datetime['second'] ) ) {
			$datetime['second'] = ! empty( $default_to_max )
				? 59
				: 0;
		}

		// Combine and return
		return sprintf(
			'%04d-%02d-%02d %02d:%02d:%02d',
			$datetime['year'],
			$datetime['month'],
			$datetime['day'],
			$datetime['hour'],
			$datetime['minute'],
			$datetime['second']
		);
	}

	/**
	 * Return a MySQL expression for selecting the week number based on the
	 * day that the week starts.
	 *
	 * Uses the WordPress site option, if set.
	 *
	 * @since 1.0.0
	 *
	 * @param string $column        Database column.
	 * @param int    $start_of_week Day that week starts on. 0 = Sunday.
	 *
	 * @return string SQL clause.
	 */
	public function build_mysql_week( $column = '', $start_of_week = 0 ) {

		// Start of week option
		$start_of_week = (int) get_option( 'start_of_week', $start_of_week );

		// When does the week start?
		switch ( $start_of_week ) {

			// Monday
			case 1:
				$retval = "WEEK( {$column}, 1 )";
				break;

			// Tuesday - Saturday
			case 2:
			case 3:
			case 4:
			case 5:
			case 6:
				$retval = "WEEK( DATE_SUB( {$column}, INTERVAL {$start_of_week} DAY ), 0 )";
				break;

			// Sunday
			case 0:
			default:
				$retval = "WEEK( {$column}, 0 )";
				break;
		}

		// Return SQL
		return $retval;
	}

	/**
	 * Builds a query string for comparing time values (hour, minute, second).
	 *
	 * If just hour, minute, or second is set than a normal comparison will be done.
	 * However if multiple values are passed, a pseudo-decimal time will be created
	 * in order to be able to accurately compare against.
	 *
	 * @since 1.0.0
	 *
	 * @param string   $column  The column to query against. Needs to be pre-validated!
	 * @param string   $compare The comparison operator. Needs to be pre-validated!
	 * @param int|null $hour    Optional. An hour value (0-23).
	 * @param int|null $minute  Optional. A minute value (0-59).
	 * @param int|null $second  Optional. A second value (0-59).
	 *
	 * @return string|false A query part or false on failure.
	 */
	public function build_time_query( $column, $compare, $hour = null, $minute = null, $second = null ) {

		// Have to have at least one
		if ( ! isset( $hour ) && ! isset( $minute ) && ! isset( $second ) ) {
			return false;
		}

		// Complex combined queries aren't supported for multi-value queries
		if ( in_array( $compare, $this->multi_value_keys, true ) ) {
			$retval = array();

			// Hour
			if ( isset( $hour ) && false !== ( $value = $this->build_numeric_value( $compare, $hour ) ) ) {
				$retval[] = "HOUR( {$column} ) {$compare} {$value}";
			}

			// Minute
			if ( isset( $minute ) && false !== ( $value = $this->build_numeric_value( $compare, $minute ) ) ) {
				$retval[] = "MINUTE( {$column} ) {$compare} {$value}";
			}

			// Second
			if ( isset( $second ) && false !== ( $value = $this->build_numeric_value( $compare, $second ) ) ) {
				$retval[] = "SECOND( {$column} ) {$compare} {$value}";
			}

			return implode( ' AND ', $retval );
		}

		// Cases where just one unit is set

		// Hour
		if ( isset( $hour ) && ! isset( $minute ) && ! isset( $second ) && false !== ( $value = $this->build_numeric_value( $compare, $hour ) ) ) {
			return "HOUR( {$column} ) {$compare} {$value}";

		// Minute
		} elseif ( ! isset( $hour ) && isset( $minute ) && ! isset( $second ) && false !== ( $value = $this->build_numeric_value( $compare, $minute ) ) ) {
			return "MINUTE( {$column} ) {$compare} {$value}";

		// Second
		} elseif ( ! isset( $hour ) && ! isset( $minute ) && isset( $second ) && false !== ( $value = $this->build_numeric_value( $compare, $second ) ) ) {
			return "SECOND( {$column} ) {$compare} {$value}";
		}

		// Single units were already handled. Since hour & second isn't allowed,
		// minute must to be set.
		if ( ! isset( $minute ) ) {
			return false;
		}

		// Defaults
		$format = $time = '';

		// Hour
		if ( null !== $hour ) {
			$format .= '%H.';
			$time   .= sprintf( '%02d', $hour ) . '.';
		} else {
			$format .= '0.';
			$time   .= '0.';
		}

		// Minute
		$format .= '%i';
		$time   .= sprintf( '%02d', $minute );

		// Second
		if ( isset( $second ) ) {
			$format .= '%s';
			$time   .= sprintf( '%02d', $second );
		}

		// Build the SQL
		$query = "DATE_FORMAT( {$column}, %s ) {$compare} %f";

		// Return the prepared SQL
		return $this->get_db()->prepare( $query, $format, $time );
	}

	/**
	 * Test if the supplied date is valid for the Gregorian calendar.
	 *
	 * @since 1.0.0
	 *
	 * @link https://www.php.net/manual/en/function.checkdate.php
	 *
	 * @param int    $month       Month number.
	 * @param int    $day         Day number.
	 * @param int    $year        Year number.
	 * @param string $source_date The date to filter.
	 *
	 * @return bool True if valid date, false if not valid date.
	 */
	public function checkdate( $month = 0, $day = 0, $year = 0, $source_date = '' ) {

		// Check the date
		$retval = checkdate( $month, $day, $year );

		/**
		 * Filters whether the given date is valid for the Gregorian calendar.
		 *
		 * @since 1.0.0
		 *
		 * @param bool   $checkdate   Whether the given date is valid.
		 * @param string $source_date Date to check.
		 */
		return (bool) apply_filters( 'wp_checkdate', $retval, $source_date );
	}
}
