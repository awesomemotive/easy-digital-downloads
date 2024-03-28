<?php
/**
 * Style functions for blocks.
 *
 * @since 2.0
 * @package   edd-blocks
 * @copyright 2022 Easy Digital Downloads
 * @license   GPL2+
 */

namespace EDD\Blocks\Styles;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Adds our custom EDD button colors to the edd-blocks stylesheet.
 *
 * @since 2.0
 * @since 2.0.8 Enqueues the edd-blocks stylesheet.
 * @return void
 */
function add_to_global_styles() {
	wp_enqueue_style( 'edd-blocks', EDD_BLOCKS_URL . 'assets/css/edd-blocks.css', array(), EDD_VERSION );
	$styles = array();
	$rules  = array();
	$colors = edd_get_option( 'button_colors' );
	if ( ! empty( $colors ) ) {
		foreach ( $colors as $setting => $value ) {
			if ( empty( $value ) ) {
				continue;
			}
			$styles[] = "--edd-blocks-button-{$setting}:{$value };";
			if ( 'text' === $setting ) {
				$rules[] = '.edd-submit,.has-edd-button-text-color{color: var(--edd-blocks-button-text) !important;}';
			} elseif ( 'background' === $setting ) {
				$rules[] = '.edd-submit,.has-edd-button-background-color{background-color: var(--edd-blocks-button-background) !important;}';
				$rules[] = '.has-edd-button-background-text-color{color: var(--edd-blocks-button-background) !important;}';
			}
		}
	}
	if ( empty( $styles ) ) {
		return;
	}
	$inline_style = 'body{' . implode( ' ', $styles ) . '}';
	if ( ! empty( $rules ) ) {
		$inline_style .= implode( ' ', $rules );
	}
	wp_add_inline_style( 'edd-blocks', $inline_style );
}
add_action( 'enqueue_block_assets', __NAMESPACE__ . '\add_to_global_styles' );

add_filter( 'edd_button_color_class', __NAMESPACE__ . '\update_button_color_class' );
/**
 * Update the EDD button color class from the new color settings.
 *
 * @since 2.0
 * @param string $class
 * @return string
 */
function update_button_color_class( $class ) {
	$classes       = array();
	$color_options = edd_get_option( 'button_colors' );
	if ( ! empty( $color_options['background'] ) ) {
		$classes[] = 'has-edd-button-background-color';
	}
	if ( ! empty( $color_options['text'] ) ) {
		$classes[] = 'has-edd-button-text-color';
	}

	return ! empty( $classes ) ? implode( ' ', $classes ) : $class;
}

/**
 * Remove the default block styles from EDD blocks
 * if the theme is not a block theme.
 *
 * We unset the style property from the block type metadata
 * and manually enqueue the block style on demand.
 *
 * @since 2.0.8
 * @param array $metadata
 * @return array
 */
function filter_block_type_metadata( $metadata ) {
	if ( should_let_wp_manage_styles() ) {
		return $metadata;
	}
	$edd_blocks = get_edd_blocks();
	if ( in_array( $metadata['name'], $edd_blocks, true ) ) {
		unset( $metadata['style'] );
	}

	return $metadata;
}
add_filter( 'block_type_metadata', __NAMESPACE__ . '\filter_block_type_metadata' );

/**
 * Enqueue the block style on demand.
 *
 * @since 2.0.8
 * @param string $content
 * @param array  $block
 * @return string
 */
function enqueue_on_render( $content, $block ) {
	if ( should_let_wp_manage_styles() ) {
		return $content;
	}

	// Ignore non-EDD blocks.
	if ( empty( $block['blockName'] ) || false === strpos( $block['blockName'], 'edd/' ) ) {
		return $content;
	}

	// Ignore blocks that are not registered.
	$registry = \WP_Block_Type_Registry::get_instance();
	if ( ! $registry->is_registered( $block['blockName'] ) ) {
		return $content;
	}

	enqueue_base_styles( $block['blockName'] );

	return $content;
}
add_filter( 'render_block', __NAMESPACE__ . '\enqueue_on_render', 10, 2 );

/**
 * Enqueue the base styles for a block.
 *
 * @since 2.0.8
 * @param string $block_name
 * @return void
 */
function enqueue_base_styles( $block_name ) {
	$block_name = str_replace( 'edd/', '', $block_name );
	$style_uri  = apply_filters(
		'edd_block_style_uri',
		EDD_BLOCKS_URL . "build/{$block_name}/style-index.css",
		$block_name
	);

	wp_enqueue_style(
		"edd-{$block_name}-style",
		$style_uri,
		array( 'edd-blocks' ),
		EDD_VERSION
	);
}

/**
 * Whether EDD should manage block styles.
 * The wp_is_block_theme() function is only available in WP 5.9+.
 *
 * @since 2.0.8
 * @return bool
 */
function should_let_wp_manage_styles() {
	if ( is_admin() || ( function_exists( 'get_current_screen' ) && ! empty( get_current_screen()->is_block_editor ) ) ) {
		return true;
	}

	return function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();
}

/**
 * Get all registered EDD blocks.
 *
 * @since 3.2.10
 * @return array
 */
function get_edd_blocks() {
	$cached_blocks = wp_cache_get( 'edd-registered-blocks', 'edd-blocks' );
	if ( false !== $cached_blocks ) {
		return $cached_blocks;
	}

	$blocks     = glob( EDD_BLOCKS_DIR . 'build/**', GLOB_ONLYDIR | GLOB_NOSORT );
	$edd_blocks = array();
	foreach ( $blocks as $block_path ) {
		$block_name = explode( '/', $block_path );
		$block_name = end( $block_name );
		if ( 'pro' === $block_name ) {
			continue;
		}
		$edd_blocks[] = "edd/{$block_name}";
	}

	$blocks = apply_filters( 'edd_registered_blocks', $edd_blocks );
	wp_cache_set( 'edd-registered-blocks', $blocks, 'edd-blocks', 5 * MINUTE_IN_SECONDS );

	return $blocks;
}
