<?php

/**
 * If we've run the 'edd_get_customer_notes' action, don't filter the comments
 *
 * @since  2.3
 * @return bool If we've asked for customer notes
 */
function edd_allow_customer_comments_query() {

	if ( did_action( 'edd_get_customer_notes' ) ) {
		return true;
	}

	return false;
}

/**
 * Exclude customer notes (comments) from showing in Recent
 * Comments widgets
 *
 * @since 2.3
 * @param obj $query WordPress Comment Query Object
 * @return void
 */
function edd_hide_customer_notes( $query ) {
	if ( edd_allow_customer_comments_query() ) {
		return $query;
	}

	global $wp_version;

	if( version_compare( floatval( $wp_version ), '4.1', '>=' ) ) {
		$types = isset( $query->query_vars['type__not_in'] ) ? $query->query_vars['type__not_in'] : array();
		if( ! is_array( $types ) ) {
			$types = array( $types );
		}
		$types[] = 'edd_customer_note';
		$query->query_vars['type__not_in'] = $types;
	}
}
add_action( 'pre_get_comments', 'edd_hide_customer_notes', 10 );

/**
 * Exclude customer notes (comments) from showing in Recent
 * Comments widgets
 *
 * @since 2.3
 * @param array $clauses Comment clauses for comment query
 * @param obj $wp_comment_query WordPress Comment Query Object
 * @return array $clauses Updated comment clauses
 */
function edd_hide_customer_notes_pre_41( $clauses, $wp_comment_query ) {
	if ( edd_allow_customer_comments_query() ) {
		return $clauses;
	}

	global $wpdb, $wp_version;

	if( version_compare( floatval( $wp_version ), '4.1', '<' ) ) {
		$clauses['where'] .= ' AND comment_type != "edd_customer_note"';
	}
	return $clauses;
}
add_filter( 'comments_clauses', 'edd_hide_customer_notes_pre_41', 10, 2 );


/**
 * Exclude customer notes (comments) from showing in comment feeds
 *
 * @since 2.3
 * @param array $where
 * @param obj $wp_comment_query WordPress Comment Query Object
 * @return array $where
 */
function edd_hide_customer_notes_from_feeds( $where, $wp_comment_query ) {
	if ( edd_allow_customer_comments_query() ) {
		return $where;
	}

	global $wpdb;

	$where .= $wpdb->prepare( " AND comment_type != %s", 'edd_customer_note' );
	return $where;
}
add_filter( 'comment_feed_where', 'edd_hide_customer_notes_from_feeds', 10, 2 );


/**
 * Remove customer notes (comments) from the wp_count_comments function
 *
 * @access public
 * @since 2.3
 * @param array $stats (empty from core filter)
 * @param int $post_id Post ID
 * @return array Array of comment counts
*/
function edd_remove_customer_notes_in_comment_counts( $stats, $post_id ) {
	if ( edd_allow_customer_comments_query() ) {
		return $stats;
	}

	global $wpdb, $pagenow;

	if( 'index.php' != $pagenow ) {
		return $stats;
	}

	$post_id = (int) $post_id;

	if ( apply_filters( 'edd_count_cusomter_notes_in_comments', false ) )
		return $stats;

	$stats = wp_cache_get( "comments-{$post_id}", 'counts' );

	if ( false !== $stats )
		return $stats;

	$where = 'WHERE comment_type != "edd_customer_note"';

	if ( $post_id > 0 )
		$where .= $wpdb->prepare( " AND comment_post_ID = %d", $post_id );

	$count = $wpdb->get_results( "SELECT comment_approved, COUNT( * ) AS num_comments FROM {$wpdb->comments} {$where} GROUP BY comment_approved", ARRAY_A );

	$total = 0;
	$approved = array( '0' => 'moderated', '1' => 'approved', 'spam' => 'spam', 'trash' => 'trash', 'post-trashed' => 'post-trashed' );
	foreach ( (array) $count as $row ) {
		// Don't count post-trashed toward totals
		if ( 'post-trashed' != $row['comment_approved'] && 'trash' != $row['comment_approved'] )
			$total += $row['num_comments'];
		if ( isset( $approved[$row['comment_approved']] ) )
			$stats[$approved[$row['comment_approved']]] = $row['num_comments'];
	}

	$stats['total_comments'] = $total;
	foreach ( $approved as $key ) {
		if ( empty($stats[$key]) )
			$stats[$key] = 0;
	}

	$stats = (object) $stats;
	wp_cache_set( "comments-{$post_id}", $stats, 'counts' );

	return $stats;
}
add_filter( 'wp_count_comments', 'edd_remove_customer_notes_in_comment_counts', 10, 2 );
