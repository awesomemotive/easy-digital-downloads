<?php
/**
 * Query building for EDD downloads blocks/shortcodes.
 *
 * @package     EDD\Downloads
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-3.5.1.php GNU Public License
 * @since       3.5.1
 */

namespace EDD\Downloads;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Downloads query class.
 */
class Query {

	/**
	 * The block attributes.
	 *
	 * @var array
	 */
	protected $attributes;

	/**
	 * The query arguments.
	 *
	 * @var array
	 */
	protected $query = array(
		'post_type' => 'download',
	);

	/**
	 * Constructor.
	 *
	 * @param array $attributes The block or shortcode attributes.
	 */
	public function __construct( $attributes ) {
		$this->attributes = wp_parse_args( $attributes, $this->get_defaults() );
	}

	/**
	 * Gets the downloads query parameters from the block attributes.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	public function get_query(): array {
		$this->query['order'] = $this->attributes['order'];
		$this->parse_pagination();
		$this->parse_orderby();
		$this->parse_tax_query();
		$this->parse_author();

		if ( ! empty( $this->attributes['ids'] ) ) {
			$this->query['post__in'] = explode( ',', $this->attributes['ids'] );
		}

		if ( ! empty( $this->attributes['featured'] ) && 'yes' === $this->attributes['featured'] ) {
			$this->query['meta_query'] = array(
				array(
					'key'     => 'edd_feature_download',
					'value'   => '1',
					'compare' => '=',
				),
			);
		}

		$this->query['paged'] = (int) $this->get_paged();

		/**
		 * Allow extensions to filter the downloads query.
		 * This is the same filter used in EDD core for the downloads shortcode query.
		 *
		 * @since 3.5.1
		 * @param array $query            The array of query parameters.
		 * @param array $this->attributes The block attributes.
		 */
		return apply_filters( 'edd_downloads_query', $this->query, $this->attributes );
	}

	/**
	 * Parses the pagination parameters for the downloads query.
	 *
	 * @since 3.5.1
	 */
	private function parse_pagination() {
		if ( $this->attributes['pagination'] || ( ! $this->attributes['pagination'] && $this->attributes['number'] ) ) {
			$this->query['posts_per_page'] = (int) $this->attributes['number'];

			if ( $this->query['posts_per_page'] < 0 ) {
				$this->query['posts_per_page'] = abs( $this->query['posts_per_page'] );
			}
		} else {
			$this->query['nopaging'] = true;
		}
	}

	/**
	 * Parses the orderby parameters for the downloads query.
	 *
	 * @since 3.5.1
	 */
	private function parse_orderby() {
		switch ( $this->attributes['orderby'] ) {
			case 'price':
				$this->query['meta_key'] = 'edd_price';
				$this->query['orderby']  = 'meta_value_num';
				break;

			case 'sales':
				$this->query['meta_key'] = '_edd_download_sales';
				$this->query['orderby']  = 'meta_value_num';
				break;

			case 'earnings':
				$this->query['meta_key'] = '_edd_download_earnings';
				$this->query['orderby']  = 'meta_value_num';
				break;

			default:
				$this->query['orderby'] = $this->attributes['orderby'];
				break;
		}

		if ( ! empty( $this->attributes['featured'] ) && 'orderby' === $this->attributes['featured'] ) {
			$this->query['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key'     => 'edd_feature_download',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'edd_feature_download',
					'compare' => 'EXISTS',
				),
			);
			$this->query['orderby']    = 'meta_value_num ' . $this->query['orderby'];
		}
	}

	/**
	 * Parses the taxonomy parameters for the downloads query.
	 *
	 * @since 3.5.1
	 */
	private function parse_tax_query() {
		if ( empty( $this->attributes['tag'] ) && empty( $this->attributes['category'] ) && empty( $this->attributes['exclude_category'] ) && empty( $this->attributes['exclude_tags'] ) ) {
			return;
		}

		$this->query['tax_query'] = array(
			'relation' => $this->attributes['relation'],
		);

		$this->parse_tags();
		$this->parse_categories();
		$this->parse_exclude_category();
		$this->parse_exclude_tags();

		if ( ! empty( $this->attributes['exclude_tags'] ) || ! empty( $this->attributes['exclude_category'] ) ) {
			$this->query['tax_query']['relation'] = 'AND';
		}
	}

	/**
	 * Parses the tag parameters for the downloads query.
	 *
	 * @since 3.5.1
	 */
	private function parse_tags() {
		if ( empty( $this->attributes['tag'] ) ) {
			return;
		}

		$term_ids = $this->get_term_ids( array_filter( (array) $this->attributes['tag'] ), 'download_tag' );
		if ( ! empty( $term_ids ) ) {
			$this->query['tax_query'][] = array(
				'taxonomy' => 'download_tag',
				'field'    => 'term_id',
				'terms'    => $term_ids,
			);
		}
	}

	/**
	 * Parses the category parameters for the downloads query.
	 *
	 * @since 3.5.1
	 */
	private function parse_categories() {
		if ( empty( $this->attributes['category'] ) ) {
			return;
		}

		$term_ids = $this->get_term_ids( array_filter( (array) $this->attributes['category'] ) );
		if ( ! empty( $term_ids ) ) {
			$this->query['tax_query'][] = array(
				'taxonomy' => 'download_category',
				'field'    => 'term_id',
				'terms'    => $term_ids,
			);
		}
	}

	/**
	 * Parses the excluded category parameters for the downloads query.
	 *
	 * @since 3.5.1
	 */
	private function parse_exclude_category() {
		if ( empty( $this->attributes['exclude_category'] ) ) {
			return;
		}

		$term_ids = $this->get_term_ids( $this->attributes['exclude_category'] );
		if ( ! empty( $term_ids ) ) {
			$this->query['tax_query'][] = array(
				'taxonomy' => 'download_category',
				'field'    => 'term_id',
				'terms'    => $term_ids,
				'operator' => 'NOT IN',
			);
		}
	}

	/**
	 * Parses the excluded tags parameters for the downloads query.
	 *
	 * @since 3.5.1
	 */
	private function parse_exclude_tags() {
		if ( empty( $this->attributes['exclude_tags'] ) ) {
			return;
		}

		$term_ids = $this->get_term_ids( $this->attributes['exclude_tags'], 'download_tag' );
		if ( ! empty( $term_ids ) ) {
			$this->query['tax_query'][] = array(
				'taxonomy' => 'download_tag',
				'field'    => 'term_id',
				'terms'    => $term_ids,
				'operator' => 'NOT IN',
			);
		}
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
	 * @since 3.5.1
	 */
	private function parse_author() {
		if ( empty( $this->attributes['author'] ) ) {
			return;
		}

		$authors = explode( ',', $this->attributes['author'] );
		if ( empty( $authors ) ) {
			return;
		}
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
			$author_ids            = array_unique( array_map( 'absint', $author_ids ) );
			$this->query['author'] = implode( ',', $author_ids );
		}
	}

	/**
	 * Parses the paged parameters for the downloads query.
	 *
	 * @since 3.5.1
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

	/**
	 * Gets the default attributes for the downloads query.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	private function get_defaults(): array {
		return array(
			'orderby'          => 'post_date',
			'order'            => 'DESC',
			'number'           => 6,
			'pagination'       => true,
			'category'         => array(),
			'tag'              => array(),
			'author'           => false,
			'featured'         => '',
			'ids'              => '',
			'exclude_category' => '',
			'exclude_tags'     => '',
			'relation'         => 'AND',
		);
	}
}
