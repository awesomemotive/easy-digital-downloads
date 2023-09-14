<?php
/**
 * EDD downloads blocks.
 *
 * @package   edd-blocks
 * @copyright 2022 Easy Digital Downloads
 * @license   GPL2+
 * @since 2.0
 */

namespace EDD\Blocks\Downloads;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Blocks\Functions;

if ( ! function_exists( 'edd_blocks_api_products_product' ) ) {
	require_once EDD_BLOCKS_DIR . 'includes/downloads/rest.php';
}

add_action( 'init', __NAMESPACE__ . '\register' );
/**
 * Registers all of the EDD core blocks.
 *
 * @since 2.0
 * @return void
 */
function register() {
	$blocks = array(
		'downloads'  => array(
			'render_callback' => __NAMESPACE__ . '\downloads',
		),
		'buy-button' => array(
			'render_callback' => __NAMESPACE__ . '\buy_button',
		),
	);

	foreach ( $blocks as $block => $args ) {
		register_block_type( EDD_BLOCKS_DIR . 'build/' . $block, $args );
	}
}

/**
 * Renders the order history block.
 *
 * @since 2.0
 * @param array $block_attributes The block attributes.
 * @return string Order history HTML.
 */
function downloads( $block_attributes = array() ) {
	// Set up defaults.
	$defaults = array(
		'image_location'      => 'before_entry_header',
		'image_size'          => 'large',
		'image_alignment'     => 'center',
		'title'               => true,
		'content'             => 'excerpt',
		'columns'             => 3,
		'orderby'             => 'post_date',
		'order'               => 'DESC',
		'pagination'          => true,
		'buy_button'          => true,
		'category'            => array(),
		'pagination'          => true,
		'number'              => 6,
		'price'               => true,
		'image_link'          => true,
		'purchase_link_align' => 'none',
		'tag'                 => array(),
		'show_price'          => true,
		'all_access'          => false,
		'author'              => false,
	);

	$block_attributes = wp_parse_args( $block_attributes, $defaults );
	if ( 'rand' === $block_attributes['orderby'] ) {
		$block_attributes['pagination'] = false;
	}
	if ( ! empty( $block_attributes['all_access'] ) ) {
		$block_attributes['all_access_customer_downloads_only'] = true;
	}

	require_once EDD_BLOCKS_DIR . 'includes/downloads/query.php';

	$query      = new Query( $block_attributes );
	$query_args = $query->get_query();
	$downloads  = new \WP_Query( $query_args );
	if ( ! $downloads->have_posts() ) {
		/* translators: the plurals downloads name. */
		return sprintf( _x( 'No %s found.', 'download post type name', 'easy-digital-downloads' ), edd_get_label_plural() );
	}

	// Set up classes.
	$classes = array(
		'wp-block-edd-downloads',
		'edd-blocks__downloads',
	);
	$classes = Functions\get_block_classes( $block_attributes, $classes );

	if ( ! empty( $block_attributes['image_location'] ) ) {
		add_action( "edd_blocks_downloads_{$block_attributes['image_location']}", __NAMESPACE__ . '\image' );
	}

	// Always disable the Stripe express checkout buttons in the block editor.
	if ( \EDD\Blocks\Functions\is_block_editor() ) {
		add_filter( 'edds_prb_purchase_link_enabled', '__return_false' );
	}

	ob_start();
	include EDD_BLOCKS_DIR . 'views/downloads/downloads.php';

	wp_reset_postdata();
	if ( ! empty( $block_attributes['image_location'] ) ) {
		remove_action( "edd_blocks_downloads_{$block_attributes['image_location']}", __NAMESPACE__ . '\image' );
	}

	return ob_get_clean();
}

/**
 * Renders the EDD buy button block.
 *
 * @since 2.0
 * @param array $block_attributes The block attributes.
 * @return string Buy button HTML.
 */
function buy_button( $block_attributes = array() ) {
	$block_attributes = wp_parse_args(
		$block_attributes,
		array(
			'download_id' => get_the_ID(),
			'show_price'  => true,
			'align'       => '',
			'direct'      => false,
			'text'        => edd_get_option( 'add_to_cart_text', __( 'Purchase', 'easy-digital-downloads' ) ),
		)
	);
	if ( empty( $block_attributes['download_id'] ) || 'download' !== get_post_type( $block_attributes['download_id'] ) ) {
		return '';
	}
	$block_attributes_for_classes = $block_attributes;
	unset( $block_attributes_for_classes['align'] );
	$classes = array(
		'wp-block-edd-buy-button',
		'edd-blocks__buy-button',
	);
	$classes = Functions\get_block_classes( $block_attributes_for_classes, $classes );
	remove_filter( 'edd_purchase_link_args', __NAMESPACE__ . '\maybe_update_purchase_links', 100 );
	if ( wp_style_is( 'edd-styles', 'registered' ) ) {
		wp_enqueue_style( 'edd-styles' );
	}

	$args = array(
		'class'       => implode( ' ', get_purchase_link_classes( $block_attributes ) ),
		'download_id' => absint( $block_attributes['download_id'] ),
		'price'       => (bool) $block_attributes['show_price'],
		'text'        => $block_attributes['text'],
	);
	if ( $block_attributes['direct'] && edd_shop_supports_buy_now() ) {
		$args['direct'] = true;
		$args['text']   = edd_get_option( 'buy_now_text', __( 'Buy Now', 'easy-digital-downloads' ) );
	}

	$output  = sprintf( '<div class="%s">', esc_attr( implode( ' ', $classes ) ) );
	$output .= edd_get_purchase_link( $args );
	$output .= '</div>';

	add_filter( 'edd_purchase_link_args', __NAMESPACE__ . '\maybe_update_purchase_links', 100 );

	return $output;
}

/**
 * Gets the array of classes for the purchase link buttons from the block attributes.
 *
 * @since 2.0
 * @param array $block_attributes The block attributes.
 * @return array
 */
function get_purchase_link_classes( $block_attributes = array() ) {
	$classes = array(
		'edd-submit',
	);
	if ( ! empty( $block_attributes['align'] ) ) {
		$classes[] = "align{$block_attributes['align']}";
	}

	return $classes;
}

add_filter( 'edd_purchase_link_args', __NAMESPACE__ . '\maybe_update_purchase_links', 100 );
/**
 * If the blocks button colors have been defined, update all purchase links everywhere.
 *
 * @since 2.0
 * @param array $args
 * @return array
 */
function maybe_update_purchase_links( $args ) {
	$classes       = get_purchase_link_classes();
	$current_class = explode( ' ', $args['class'] );
	$classes       = array_merge( $classes, $current_class );
	$args['class'] = implode( ' ', array_unique( $classes ) );

	return $args;
}

/**
 * Renders a featured image if one is set.
 *
 * @since 2.0
 * @param array $block_attributes
 * @return void
 */
function image( $block_attributes ) {
	if ( ! \has_post_thumbnail() ) {
		return;
	}
	include EDD_BLOCKS_DIR . 'views/downloads/image.php';
}
