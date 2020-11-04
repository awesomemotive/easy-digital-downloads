<?php
/**
 * Base Custom Database Table Compare Query Class.
 *
 * @package     Database
 * @subpackage  Compare
 * @copyright   Copyright (c) 2020
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace EDD\Database\Queries;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class used for generating SQL for compare clauses.
 *
 * This class is used to generate the SQL when a `compare` argument is passed to
 * the `Base` query class. It extends `Meta` so the `compare` key accepts
 * the same parameters as the ones passed to `Meta`.
 *
 * @since 1.0.0
 */
class Compare extends Meta {

	/**
	 * Generate SQL WHERE clauses for a first-order query clause.
	 *
	 * "First-order" means that it's an array with a 'key' or 'value'.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $clause       Query clause (passed by reference).
	 * @param array  $parent_query Parent query array.
	 * @param string $clause_key   Optional. The array key used to name the clause in the original `$meta_query`
	 *                             parameters. If not provided, a key will be generated automatically.
	 * @return array {
	 *     Array containing WHERE SQL clauses to append to a first-order query.
	 *
	 *     @type string $where SQL fragment to append to the main WHERE clause.
	 * }
	 */
	public function get_sql_for_clause( &$clause, $parent_query, $clause_key = '' ) {
		global $wpdb;

		$sql_chunks = array(
			'where' => array(),
			'join'  => array(),
		);

		if ( isset( $clause['compare'] ) ) {
			$clause['compare'] = strtoupper( $clause['compare'] );
		} else {
			$clause['compare'] = isset( $clause['value'] ) && is_array( $clause['value'] ) ? 'IN' : '=';
		}

		if ( ! in_array(
			$clause['compare'], array(
				'=',
				'!=',
				'>',
				'>=',
				'<',
				'<=',
				'LIKE',
				'NOT LIKE',
				'IN',
				'NOT IN',
				'BETWEEN',
				'NOT BETWEEN',
				'EXISTS',
				'NOT EXISTS',
				'REGEXP',
				'NOT REGEXP',
				'RLIKE',
			), true
		) ) {
			$clause['compare'] = '=';
		}

		if ( isset( $clause['compare_key'] ) && 'LIKE' === strtoupper( $clause['compare_key'] ) ) {
			$clause['compare_key'] = strtoupper( $clause['compare_key'] );
		} else {
			$clause['compare_key'] = '=';
		}

		$compare     = $clause['compare'];
		$compare_key = $clause['compare_key'];

		// Build the WHERE clause.

		// Column name and value.
		if ( array_key_exists( 'key', $clause ) && array_key_exists( 'value', $clause ) ) {
			$column = sanitize_key( $clause['key'] );
			$value  = $clause['value'];

			if ( in_array( $compare, array( 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' ), true ) ) {
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
					$where          = $wpdb->prepare( $compare_string, $value );
					break;

				case 'BETWEEN':
				case 'NOT BETWEEN':
					$value = array_slice( $value, 0, 2 );
					$where = $wpdb->prepare( '%s AND %s', $value );
					break;

				case 'LIKE':
				case 'NOT LIKE':
					$value = '%' . $wpdb->esc_like( $value ) . '%';
					$where = $wpdb->prepare( '%s', $value );
					break;

				// EXISTS with a value is interpreted as '='.
				case 'EXISTS':
					$compare = '=';
					$where   = $wpdb->prepare( '%s', $value );
					break;

				// 'value' is ignored for NOT EXISTS.
				case 'NOT EXISTS':
					$where = '';
					break;

				default:
					$where = $wpdb->prepare( '%s', $value );
					break;

			}

			if ( $where ) {
				$sql_chunks['where'][] = "{$column} {$compare} {$where}";
			}
		}

		/*
		 * Multiple WHERE clauses (for meta_key and meta_value) should
		 * be joined in parentheses.
		 */
		if ( 1 < count( $sql_chunks['where'] ) ) {
			$sql_chunks['where'] = array( '( ' . implode( ' AND ', $sql_chunks['where'] ) . ' )' );
		}

		return $sql_chunks;
	}
}
