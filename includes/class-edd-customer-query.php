<?php
/**
 * This file only exists to maintain backwards compatibility for anyone who may
 * have included it directly.
 *
 * If you are reading this, and are including this file directly in any of your
 * code, please consider not doing so, and interfacing with customer data via
 * the REST API or some other way.
 *
 * @package     EDD
 * @subpackage  Classes/Customer Query
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.8
 * @deprecated  3.0.0
 */

// Exit if accessed directly
if ( ! defined( 'EDD_PLUGIN_DIR' ) ) exit;

/**
 * EDD_Customer_Query.
 *
 * This class remains for backwards compatibility. Please use edd_get_customers() instead.
 *
 * @deprecated 3.0.0
 */
class EDD_Customer_Query extends EDD\Database\Queries\Customer {

	/**
	 * Constructor.
	 *
	 * Sets up the customer query defaults and optionally runs a query.
	 *
	 * @since 2.8
	 *
	 * @deprecated 3.0.0
	 *
	 * @param string|array $query {
	 *     Optional. Array or query string of customer query parameters. Default empty.
	 *
	 *     @type int          $number         Maximum number of customers to retrieve. Default 20.
	 *     @type int          $offset         Number of customers to offset the query. Default 0.
	 *     @type string|array $orderby        Customer status or array of statuses. To use 'meta_value'
	 *                                        or 'meta_value_num', `$meta_key` must also be provided.
	 *                                        To sort by a specific `$meta_query` clause, use that
	 *                                        clause's array key. Accepts 'id', 'user_id', 'name',
	 *                                        'email', 'purchase_value', 'purchase_count',
	 *                                        'notes', 'date_created', 'meta_value', 'meta_value_num',
	 *                                        the value of `$meta_key`, and the array keys of `$meta_query`.
	 *                                        Also accepts false, an empty array, or 'none' to disable the
	 *                                        `ORDER BY` clause. Default 'id'.
	 *     @type string       $order          How to order retrieved customers. Accepts 'ASC', 'DESC'.
	 *                                        Default 'DESC'.
	 *     @type string|array $include        String or array of customer IDs to include. Default empty.
	 *     @type string|array $exclude        String or array of customer IDs to exclude. Default empty.
	 *     @type string|array $users_include  String or array of customer user IDs to include. Default
	 *                                        empty.
	 *     @type string|array $users_exclude  String or array of customer user IDs to exclude. Default
	 *                                        empty.
	 *     @type string|array $email          Limit results to those customers affiliated with one of
	 *                                        the given emails. Default empty.
	 *     @type string       $search         Search term(s) to retrieve matching customers for. Searches
	 *                                        through customer names. Default empty.
	 *     @type string|array $search_columns Columns to search using the value of `$search`. Default 'name'.
	 *     @type string       $meta_key       Include customers with a matching customer meta key.
	 *                                        Default empty.
	 *     @type string       $meta_value     Include customers with a matching customer meta value.
	 *                                        Requires `$meta_key` to be set. Default empty.
	 *     @type array        $meta_query     Meta query clauses to limit retrieved customers by.
	 *                                        See `WP_Meta_Query`. Default empty.
	 *     @type array        $date_query     Date query clauses to limit retrieved customers by.
	 *                                        See `WP_Date_Query`. Default empty.
	 *     @type bool         $count          Whether to return a count (true) instead of an array of
	 *                                        customer objects. Default false.
	 *     @type bool         $no_found_rows  Whether to disable the `SQL_CALC_FOUND_ROWS` query.
	 *                                        Default true.
	 * }
	 */
	public function __construct( $query = array() ) {
		if ( isset( $query['include'] ) ) {
			$query['id__in'] = $query['include'];
			unset( $query['include'] );
		}

		if ( isset( $query['exclude'] ) ) {
			$query['id__not_in'] = $query['exclude'];
			unset( $query['exclude'] );
		}

		if ( isset( $query['users_include'] ) ) {
			$query['user_id__in'] = $query['users_include'];
			unset( $query['users_include'] );
		}

		if ( isset( $query['users_exclude'] ) ) {
			$query['user_id__not_in'] = $query['users_exclude'];
			unset( $query['users_exclude'] );
		}

		parent::__construct( $query );
	}
}
