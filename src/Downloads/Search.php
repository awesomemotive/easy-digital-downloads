<?php
/**
 * Search functionality for downloads.
 *
 * @package     EDD
 */

namespace EDD\Downloads;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Search class.
 */
class Search {

	/**
	 * Retrieve a downloads drop down
	 *
	 * @since 3.1.0.5 Copied from `edd_ajax_download_search`
	 *
	 * @return void
	 */
	public function ajax_search() {

		if ( ! edd_doing_ajax() ) {
			return;
		}

		echo wp_json_encode( $this->search() );

		edd_die();
	}

	/**
	 * Search for downloads.
	 *
	 * @since 3.3.6
	 * @return array
	 */
	public function search() {
		$search = $this->get_results();

		// Update the transient.
		set_transient( 'edd_download_search', $search, 30 );

		return $search['results'];
	}

	/**
	 * Get the search results.
	 *
	 * @since 3.3.6
	 * @return array
	 */
	private function get_results() {

		// We store the last search in a transient for 30 seconds. This _might_
		// result in a race condition if 2 users are looking at the exact same time,
		// but we'll worry about that later if that situation ever happens.
		$args = get_transient( 'edd_download_search' );

		// Parse args.
		$search = wp_parse_args(
			(array) $args,
			array(
				'text'    => '',
				'results' => array(),
			)
		);

		// Get the search string.
		$new_search = $this->get_search();

		// Bail early if the search text has not changed.
		if ( $search['text'] === $new_search ) {
			return $search;
		}

		// Set the local static search variable and clear the results.
		$search = array(
			'text'    => $new_search,
			'results' => array(),
		);

		// Default query arguments.
		$args = array(
			'orderby'          => 'title',
			'order'            => 'ASC',
			'post_type'        => 'download',
			'posts_per_page'   => 50,
			'post_status'      => $this->get_status(),
			'post__not_in'     => $this->get_exclusions(),
			's'                => $new_search,
			'suppress_filters' => false,
		);

		$items = $this->get_items( $args );

		if ( empty( $items ) ) {
			return $search;
		}

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

		return $search;
	}

	/**
	 * Gets the items.
	 *
	 * @since 3.2.8
	 * @param array $args The array of arguments for WP_Query.
	 * @return array
	 */
	private function get_items( $args ) {
		add_filter(
			'post_search_columns',
			function () {
				return array( 'post_title' );
			}
		);

		return get_posts( $args );
	}

	/**
	 * Gets the search string.
	 *
	 * @since 3.2.7
	 * @return string
	 */
	private function get_search() {
		return isset( $_GET['s'] )
			? sanitize_text_field( urldecode( $_GET['s'] ) )
			: '';
	}

	/**
	 * Gets the excluded downloads.
	 *
	 * @since 3.2.8
	 * @return array
	 */
	private function get_exclusions() {
		$excludes = ! empty( $_GET['current_id'] )
			? array_map( 'absint', (array) $_GET['current_id'] )
			: array();

		if ( ! empty( $_GET['exclusions'] ) ) {
			$excludes = array_merge( $excludes, array_map( 'absint', explode( ',', $_GET['exclusions'] ) ) );
		}

		return array_unique( array_filter( $excludes ) );
	}

	/**
	 * Get the download statuses to query.
	 *
	 * @since 3.3.6
	 * @return array
	 */
	private function get_status() {
		if ( ! current_user_can( 'edit_products' ) ) {
			return apply_filters( 'edd_product_dropdown_status_nopriv', array( 'publish' ) );
		}

		return apply_filters( 'edd_product_dropdown_status', array( 'publish', 'draft', 'private', 'future' ) );
	}

	/**
	 * Filters the WHERE SQL query for the edd_download_search.
	 * This searches the download titles only, not the excerpt/content.
	 *
	 * @since 3.1.0.2
	 * @since 3.1.0.5 Moved to EDD\Downloads\Ajax.
	 * @deprecated 3.3.6
	 * @param string $where The WHERE clause of the query.
	 * @return string
	 */
	public function filter_where( $where ) {
		return $where;
	}

	/**
	 * Parses the search terms to allow for a "fuzzy" search.
	 *
	 * @since 3.1.0.5
	 * @deprecated 3.3.6
	 * @param string $search The search string.
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
