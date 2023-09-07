<?php
/**
 * Query building for EDD downloads blocks.
 *
 * @package   edd-blocks
 * @copyright 2022 Easy Digital Downloads
 * @license   GPL2+
 * @since 2.0
 */

namespace EDD\Blocks\Downloads;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


class Query {

	/**
	 * The block attributes.
	 *
	 * @var array
	 */
	protected $attributes;

	public function __construct( $block_attributes ) {
		$this->attributes = $block_attributes;
	}

	/**
	 * Gets the downloads query parameters from the block attributes.
	 *
	 * @since 2.0
	 * @return array
	 */
	public function get_query() {
		$query = array(
			'post_type' => 'download',
			'order'     => $this->attributes['order'],
		);
		$query = $this->parse_pagination( $query, $this->attributes );
		$query = $this->parse_orderby( $query, $this->attributes );
		$query = $this->parse_tax_query( $query, $this->attributes );

		if ( ! empty( $this->attributes['ids'] ) ) {
			$query['post__in'] = explode( ',', $this->attributes['ids'] );
		}

		$query['paged'] = (int) $this->get_paged();

		/**
		 * Allow extensions to filter the downloads query.
		 * This is the same filter used in EDD core for the downloads shortcode query.
		 *
		 * @since 2.0
		 * @param array $query            The array of query parameters.
		 * @param array $this->attributes The block attributes.
		 */
		return apply_filters( 'edd_downloads_query', $query, $this->attributes );
	}

	/**
	 * Parses the pagination parameters for the downloads query.
	 *
	 * @since 2.0
	 * @param array $query Download query args.
	 * @return array
	 */
	private function parse_pagination( $query ) {
		if ( $this->attributes['pagination'] || ( ! $this->attributes['pagination'] && $this->attributes['number'] ) ) {

			$query['posts_per_page'] = (int) $this->attributes['number'];

			if ( $query['posts_per_page'] < 0 ) {
				$query['posts_per_page'] = abs( $query['posts_per_page'] );
			}
		} else {
			$query['nopaging'] = true;
		}

		return $query;
	}

	/**
	 * Parses the orderby parameters for the downloads query.
	 *
	 * @since 2.0
	 * @param array $query Download query args.
	 * @return array
	 */
	private function parse_orderby( $query ) {
		switch ( $this->attributes['orderby'] ) {
			case 'price':
				$query['meta_key'] = 'edd_price';
				$query['orderby']  = 'meta_value_num';
				break;

			case 'sales':
				$query['meta_key'] = '_edd_download_sales';
				$query['orderby']  = 'meta_value_num';
				break;

			case 'earnings':
				$query['meta_key'] = '_edd_download_earnings';
				$query['orderby']  = 'meta_value_num';
				break;

			default:
				$query['orderby'] = $this->attributes['orderby'];
				break;
		}

		return $query;
	}

	/**
	 * Parses the taxonomy parameters for the downloads query.
	 *
	 * @since 2.0
	 * @param array $query Download query args.
	 * @return array
	 */
	private function parse_tax_query( $query ) {
		if ( empty( $this->attributes['tag'] ) && empty( $this->attributes['category'] ) && empty( $this->attributes['exclude_category'] ) && empty( $this->attributes['exclude_tags'] ) ) {
			return $query;
		}

		$query['tax_query'] = array(
			'relation' => ! empty( $this->attributes['relation'] ) ? $this->attributes['relation'] : 'AND',
		);

		$query = $this->parse_tags( $query, $this->attributes );
		$query = $this->parse_categories( $query, $this->attributes );
		$query = $this->parse_exclude_category( $query, $this->attributes );
		$query = $this->parse_exclude_tags( $query, $this->attributes );
		$query = $this->parse_author( $query, $this->attributes );

		if ( ! empty( $this->attributes['exclude_tags'] ) || ! empty( $this->attributes['exclude_category'] ) ) {
			$query['tax_query']['relation'] = 'AND';
		}

		return $query;
	}

	/**
	 * Parses the tag parameters for the downloads query.
	 *
	 * @since 2.0
	 * @param array $query Download query args.
	 * @return array
	 */
	private function parse_tags( $query ) {
		if ( empty( $this->attributes['tag'] ) ) {
			return $query;
		}

		$term_ids = $this->get_term_ids( array_filter( (array) $this->attributes['tag'] ), 'download_tag' );
		if ( ! empty( $term_ids ) ) {
			$query['tax_query'][] = array(
				'taxonomy' => 'download_tag',
				'field'    => 'term_id',
				'terms'    => $term_ids,
			);
		}

		return $query;
	}

	/**
	 * Parses the category parameters for the downloads query.
	 *
	 * @since 2.0
	 * @param array $query Download query args.
	 * @return array
	 */
	private function parse_categories( $query ) {
		if ( empty( $this->attributes['category'] ) ) {
			return $query;
		}

		$term_ids = $this->get_term_ids( array_filter( (array) $this->attributes['category'] ) );
		if ( ! empty( $term_ids ) ) {
			$query['tax_query'][] = array(
				'taxonomy' => 'download_category',
				'field'    => 'term_id',
				'terms'    => $term_ids,
			);
		}

		return $query;
	}

	/**
	 * Parses the excluded category parameters for the downloads query.
	 *
	 * @since 2.0
	 * @param array $query Download query args.
	 * @return array
	 */
	private function parse_exclude_category( $query ) {
		if ( empty( $this->attributes['exclude_category'] ) ) {
			return $query;
		}

		$term_ids = $this->get_term_ids( $this->attributes['exclude_category'] );
		if ( ! empty( $term_ids ) ) {
			$query['tax_query'][] = array(
				'taxonomy' => 'download_category',
				'field'    => 'term_id',
				'terms'    => $term_ids,
				'operator' => 'NOT IN',
			);
		}

		return $query;
	}

	/**
	 * Parses the excluded tags parameters for the downloads query.
	 *
	 * @since 2.0
	 * @param array $query Download query args.
	 * @return array
	 */
	private function parse_exclude_tags( $query ) {
		if ( empty( $this->attributes['exclude_tags'] ) ) {
			return $query;
		}

		$term_ids = $this->get_term_ids( $this->attributes['exclude_tags'], 'download_tag' );
		if ( ! empty( $term_ids ) ) {
			$query['tax_query'][] = array(
				'taxonomy' => 'download_tag',
				'field'    => 'term_id',
				'terms'    => $term_ids,
				'operator' => 'NOT IN',
			);
		}

		return $query;
	}

	/**
	 * Gets an array of term ids for a parameter.
	 *
	 * @param array|string $list     The term IDs to evaluate.
	 * @param string       $taxonomy The taxonomy to check.
	 * @return array
	 */
	private function get_term_ids( $list, $taxonomy = 'download_category' ) {
		$term_list = is_array( $list ) ? $list : explode( ',', $list );
		$term_ids  = array();

		foreach ( $term_list as $term ) {

			$t_id  = (int) $term;
			$is_id = is_int( $t_id ) && ! empty( $t_id );

			if ( $is_id ) {
				$term_ids[] = $t_id;
				continue;
			}

			$term_object = get_term_by( 'slug', $term, $taxonomy );
			if ( ! empty( $term_object->term_id ) ) {
				$term_ids[] = $term_object->term_id;
			}
		}

		return $term_ids;
	}

	/**
	 * Parses the author parameters for the downloads query.
	 *
	 * @since 2.0
	 * @param array $query
	 * @return array
	 */
	private function parse_author( $query ) {
		if ( empty( $this->attributes['author'] ) ) {
			return $query;
		}

		$authors = explode( ',', $this->attributes['author'] );
		if ( ! empty( $authors ) ) {
			$author_ids   = array();
			$author_names = array();

			foreach ( $authors as $author ) {
				if ( is_numeric( $author ) ) {
					$author_ids[] = $author;
				} else {
					$user = get_user_by( 'login', $author );
					if ( $user ) {
						$author_ids[] = $user->ID;
					}
				}
			}

			if ( ! empty( $author_ids ) ) {
				$author_ids      = array_unique( array_map( 'absint', $author_ids ) );
				$query['author'] = implode( ',', $author_ids );
			}
		}

		return $query;
	}

	/**
	 * Parses the paged parameters for the downloads query.
	 *
	 * @since 2.0
	 * @return int
	 */
	private function get_paged() {
		if ( get_query_var( 'paged' ) ) {
			return get_query_var( 'paged' );
		}

		if ( get_query_var( 'page' ) ) {
			return get_query_var( 'page' );
		}

		return 1;
	}
}
