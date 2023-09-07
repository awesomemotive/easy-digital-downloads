<?php
/**
 * Search functionality for downloads.
 */
namespace EDD\Downloads;

class Search {

	/**
	 * Retrieve a downloads drop down
	 *
	 * @since 3.1.0.5 Copied from `edd_ajax_download_search`
	 *
	 * @return void
	 */
	public function ajax_search() {

		// We store the last search in a transient for 30 seconds. This _might_
		// result in a race condition if 2 users are looking at the exact same time,
		// but we'll worry about that later if that situation ever happens.
		$args   = get_transient( 'edd_download_search' );

		// Parse args.
		$search = wp_parse_args(
			(array) $args,
			array(
				'text'    => '',
				'results' => array(),
			)
		);

		// Get the search string.
		$new_search = isset( $_GET['s'] )
			? sanitize_text_field( $_GET['s'] )
			: '';

		// Limit to only alphanumeric characters, including unicode and spaces.
		$new_search = preg_replace( '/[^\pL^\pN\pZ]/', ' ', $new_search );

		// Bail early if the search text has not changed.
		if ( $search['text'] === $new_search ) {
			echo wp_json_encode( $search['results'] );
			edd_die();
		}

		// Set the local static search variable.
		$search['text'] = $new_search;

		// Are we excluding the current ID?
		$excludes = ! empty( $_GET['current_id'] )
			? array_unique( array_map( 'absint', (array) $_GET['current_id'] ) )
			: array();

		// Are we excluding bundles?
		$no_bundles = isset( $_GET['no_bundles'] )
			? filter_var( $_GET['no_bundles'], FILTER_VALIDATE_BOOLEAN )
			: false;

		// Are we including variations?
		$variations = isset( $_GET['variations'] )
			? filter_var( $_GET['variations'], FILTER_VALIDATE_BOOLEAN )
			: false;

		$variations_only = isset( $_GET['variations_only'] )
			? filter_var( $_GET['variations_only'], FILTER_VALIDATE_BOOLEAN )
			: false;

		// Are we including all statuses, or only public ones?
		$status = ! current_user_can( 'edit_products' )
			? apply_filters( 'edd_product_dropdown_status_nopriv', array( 'publish' ) )
			: apply_filters( 'edd_product_dropdown_status', array( 'publish', 'draft', 'private', 'future' ) );

		// Default query arguments.
		$args = array(
			'orderby'          => 'title',
			'order'            => 'ASC',
			'post_type'        => 'download',
			'posts_per_page'   => 50,
			'post_status'      => implode( ',', $status ), // String.
			'post__not_in'     => $excludes,               // Array.
			'edd_search'       => $new_search,             // String.
			'suppress_filters' => false,
		);

		add_filter( 'posts_where', array( $this, 'filter_where' ), 10, 2 );
		// Get downloads.
		$items = get_posts( $args );
		remove_filter( 'posts_where', array( $this, 'filter_where' ), 10, 2 );

		// Pluck title & ID.
		if ( ! empty( $items ) ) {
			$items = wp_list_pluck( $items, 'post_title', 'ID' );

			// Loop through all items...
			foreach ( $items as $post_id => $title ) {
				// Skip bundles if we're excluding them.
				if ( true === $no_bundles && 'bundle' === edd_get_download_type( $post_id ) ) {
					continue;
				}
				$product_title = $title;

				// Look for variable pricing.
				$prices = edd_get_variable_prices( $post_id );

				if ( ! empty( $prices ) && ( false === $variations || ! $variations_only ) ) {
					$title .= ' (' . __( 'All Price Options', 'easy-digital-downloads' ) . ')';
				}

				if ( empty( $prices ) || ! $variations_only ) {
					// Add item to results array.
					$search['results'][] = array(
						'id'   => $post_id,
						'name' => $title,
					);
				}

				// Maybe include variable pricing.
				if ( ! empty( $variations ) && ! empty( $prices ) ) {
					foreach ( $prices as $key => $value ) {
						$name = ! empty( $value['name'] ) ? $value['name'] : '';

						if ( ! empty( $name ) ) {
							$search['results'][] = array(
								'id'   => $post_id . '_' . $key,
								'name' => esc_html( $product_title . ': ' . $name ),
							);
						}
					}
				}
			}
		} else {
			// Empty the results array.
			$search['results'] = array();
		}

		// Update the transient.
		set_transient( 'edd_download_search', $search, 30 );

		// Output the results.
		echo wp_json_encode( $search['results'] );

		// Done!
		edd_die();
	}

	/**
	 * Filters the WHERE SQL query for the edd_download_search.
	 * This searches the download titles only, not the excerpt/content.
	 *
	 * @since 3.1.0.2
	 * @since 3.1.0.5 Moved to EDD\Downloads\Ajax.
	 * @param string $where
	 * @param WP_Query $wp_query
	 * @return string
	 */
	public function filter_where( $where, $wp_query ) {
		$search = $wp_query->get( 'edd_search' );
		if ( ! $search ) {
			return $where;
		}

		$terms = $this->parse_search_terms( $search );
		if ( empty( $terms ) ) {
			return $where;
		}

		global $wpdb;
		$query = '';
		foreach ( $terms as $term ) {
			$operator = empty( $query ) ? '' : ' AND ';
			$term     = $wpdb->esc_like( $term );
			$query   .= "{$operator}{$wpdb->posts}.post_title LIKE '%{$term}%'";
		}
		if ( $query ) {
			$where .= " AND ({$query})";
		}

		return $where;
	}

	/**
	 * Parses the search terms to allow for a "fuzzy" search.
	 *
	 * @since 3.1.0.5
	 * @param string $search
	 * @return array
	 */
	protected function parse_search_terms( $search ) {
		$terms      = explode( ' ', $search );
		$strtolower = function_exists( 'mb_strtolower' ) ? 'mb_strtolower' : 'strtolower';
		$checked    = array();

		foreach ( $terms as $term ) {
			// Keep before/after spaces when term is for exact match.
			if ( preg_match( '/^".+"$/', $term ) ) {
				$term = trim( $term, "\"'" );
			} else {
				$term = trim( $term, "\"' " );
			}

			// Avoid single A-Z and single dashes.
			if ( ! $term || ( 1 === strlen( $term ) && preg_match( '/^[a-z\-]$/i', $term ) ) ) {
				continue;
			}

			$checked[] = $term;
		}

		return $checked;
	}
}
