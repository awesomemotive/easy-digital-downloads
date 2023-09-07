<?php
/**
 * Functionality for terms blocks and images.
 *
 * @package   edd-blocks
 * @copyright 2022 Easy Digital Downloads
 * @license   GPL2+
 * @since 2.0
 */
namespace EDD\Blocks\Terms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Blocks\Functions;

add_action( 'init', __NAMESPACE__ . '\register' );
/**
 * Registers all of the EDD core blocks.
 *
 * @since 2.0
 * @return void
 */
function register() {
	$blocks = array(
		'terms' => array(
			'render_callback' => __NAMESPACE__ . '\terms',
		),
	);

	foreach ( $blocks as $block => $args ) {
		register_block_type( EDD_BLOCKS_DIR . 'build/' . $block, $args );
	}
}

add_action( 'admin_init', __NAMESPACE__ . '\meta' );
/**
 * Adds functionality to set featured images for download terms.
 *
 * @since 2.0
 * @return void
 */
function meta() {
	// If the original EDD Blocks are active, defer to that for setting term images.
	if ( class_exists( 'EDD_Term_Images' ) ) {
		return;
	}
	require_once EDD_BLOCKS_DIR . 'includes/terms/images.php';
	new \EDD\Blocks\Terms\Images();
}

/**
 * Renders the terms block.
 *
 * @since 2.0
 * @param array  $block_attributes The block attributes.
 * @return string
 */
function terms( $block_attributes = array() ) {
	// Set up defaults.
	$defaults = array(
		'thumbnails'      => true,
		'title'           => true,
		'description'     => true,
		'show_empty'      => false,
		'columns'         => 3,
		'count'           => true,
		'orderby'         => 'count',
		'order'           => 'DESC',
		'taxonomy'        => 'download_category',
		'image_size'      => 'large',
		'image_alignment' => 'center',
		'align'           => '',
	);

	$block_attributes = wp_parse_args( $block_attributes, $defaults );

	// Taxonomy must be specified.
	if ( empty( $block_attributes['taxonomy'] ) ) {
		return;
	}

	require_once EDD_BLOCKS_DIR . 'includes/terms/query.php';

	$query      = new Query( $block_attributes );
	$query_args = $query->get_query();
	$query      = new \WP_Term_Query( $query_args );
	if ( empty( $query->terms ) ) {
		return '';
	}

	// Set up classes.
	$classes = array(
		'wp-block-edd-terms',
		'edd-blocks__terms',
	);
	$classes = Functions\get_block_classes( $block_attributes, $classes );

	ob_start();
	include EDD_BLOCKS_DIR . 'views/terms.php';

	return ob_get_clean();
}
