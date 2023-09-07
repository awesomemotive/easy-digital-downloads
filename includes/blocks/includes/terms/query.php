<?php
/**
 * Query building for EDD terms blocks.
 *
 * @package   edd-blocks
 * @copyright 2022 Easy Digital Downloads
 * @license   GPL2+
 * @since 2.0.1
 */

namespace EDD\Blocks\Terms;

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
			'taxonomy'   => $this->attributes['taxonomy'],
			'orderby'    => $this->attributes['orderby'],
			'order'      => $this->attributes['order'],
			'hide_empty' => false === $this->attributes['show_empty'],
		);

		// Hide child download categories by default.
		if ( 'download_category' === $this->attributes['taxonomy'] ) {
			$query['parent'] = 0;
		}

		/**
		 * Allow extensions to filter the terms query.
		 *
		 * @since 2.0.1
		 * @param array $query            The array of query parameters.
		 * @param array $this->attributes The block attributes.
		 */
		return apply_filters( 'edd_blocks_terms_query', $query, $this->attributes );
	}
}
