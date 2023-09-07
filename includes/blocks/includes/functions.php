<?php
/**
 * General blocks functions.
 *
 * @package   edd-blocks
 * @copyright 2022 Easy Digital Downloads
 * @license   GPL2+
 * @since 2.0
 */
namespace EDD\Blocks\Functions;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

add_filter( 'block_categories_all', __NAMESPACE__ . '\register_edd_block_category', 10, 2 );
/**
 * Registers the EDD category for blocks.
 *
 * @since 2.0
 * @param array                    $block_categories
 * @param \WP_Block_Editor_Context $editor_context
 * @return array
 */
function register_edd_block_category( $block_categories, $editor_context ) {
	$block_categories[] = array(
		'slug'  => 'easy-digital-downloads',
		'title' => __( 'Easy Digital Downloads', 'easy-digital-downloads' ),
		'icon'  => 'download',
	);

	return $block_categories;
}

/**
 * Gets the array of classes for a block.
 *
 * @since 2.0
 * @param array $block_attributes
 * @param array $classes
 * @return array
 */
function get_block_classes( $block_attributes, $classes = array() ) {
	if ( ! empty( $block_attributes['columns'] ) && 1 < (int) $block_attributes['columns'] ) {
		$columns   = (int) $block_attributes['columns'];
		$classes[] = 'edd-blocks__columns';
		if ( 3 <= $columns ) {
			$classes[] = "edd-blocks__columns-{$columns}";
		}
	}
	if ( ! empty( $block_attributes['align'] ) ) {
		$classes[] = "align{$block_attributes['align']}";
	}
	if ( ! empty( $block_attributes['className'] ) ) {
		$additional_classes = explode( ' ', $block_attributes['className'] );
		if ( $additional_classes ) {
			$classes = array_merge( $classes, $additional_classes );
		}
	}

	return array_filter( array_unique( array_map( 'sanitize_html_class', $classes ) ) );
}

/**
 * Marks a field as required with the HTML5 attribute.
 *
 * @since 2.0
 * @param string $field
 * @return void
 */
function mark_field_required( $field ) {
	if ( edd_field_is_required( $field ) ) {
		echo 'required';
	}
}

/**
 * Gets an array of CSS classes for an edd-submit button.
 *
 * @since 2.0
 * @param array $classes Optional custom classes.
 * @return array
 */
function get_button_classes( $classes = array() ) {
	$button_classes = array_merge(
		$classes,
		array(
			'button',
			'edd-submit',
			edd_get_button_color_class(),
		)
	);

	return array_filter( $button_classes );
}

/**
 * Gets an array of classes for a given input field.
 *
 * @since 2.0
 * @param string $field
 * @param array $classes
 * @return array
 */
function get_input_classes( $field, $classes = array() ) {

	$classes = array_merge( array( $field ), $classes );
	if ( edd_field_is_required( $field ) ) {
		$classes[] = 'required';
	}

	return array_filter( array_unique( $classes ) );
}

/**
 * Checks whether we are viewing content in the block editor.
 *
 * @since 2.0
 * @param string $current_user_can Whether the current user needs to have a specific capability.
 * @return false|string
 */
function is_block_editor( $current_user_can = '' ) {
	$is_block_editor = ! empty( $_GET['edd_blocks_is_block_editor'] ) ? $_GET['edd_blocks_is_block_editor'] : false;

	// If not the block editor or custom capabilities are not required, return.
	if ( ! $is_block_editor || empty( $current_user_can ) ) {
		return $is_block_editor;
	}
	$user = wp_get_current_user();

	return hash_equals( md5( $user->user_email ), $is_block_editor ) && current_user_can( $current_user_can );
}

add_action( 'send_headers', __NAMESPACE__ . '\update_headers' );
/**
 * Prevents clickjacking by sending the X-Frame-Options header
 * when a page has either the checkout or login block.
 *
 * @since 2.0.5.1
 * @return void
 */
function update_headers() {
	if ( ! has_block( 'edd/checkout' ) && ! has_block( 'edd/login' ) && ! has_block( 'edd/register' ) ) {
		return;
	}
	header( 'X-Frame-Options: SAMEORIGIN' );
}
