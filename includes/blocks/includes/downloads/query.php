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
	 * Constructor.
	 *
	 * @param array $block_attributes The block attributes.
	 */
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
		_edd_deprecated_function( __METHOD__, '3.5.1', 'EDD\Downloads\Query' );

		$query = new \EDD\Downloads\Query( $this->attributes );

		return $query->get_query();
	}
}
