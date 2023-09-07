<?php
/**
 * General admin functions for blocks.
 *
 * @package   edd-blocks
 * @copyright 2022 Easy Digital Downloads
 * @license   GPL2+
 * @since 2.0
 */

namespace EDD\Blocks\Admin\Functions;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

add_filter( 'display_post_states', __NAMESPACE__ . '\display_post_states', 15, 2 );
/**
 * Updates the post states array to show states specific to EDD.
 *
 * @since 2.0
 * @param array $post_states An array of post display states.
 * @param WP_Post $post Post object.
 * @return array
 */
function display_post_states( $post_states, $post ) {
	if ( intval( edd_get_option( 'login_page' ) ) === $post->ID ) {
		$post_states['edd_login_page'] = __( 'Login Page', 'easy-digital-downloads' );
	}

	if ( intval( edd_get_option( 'purchase_history_page' ) ) === $post->ID ) {
		$post_states['edd_purchase_history_page'] = __( 'Order History Page', 'easy-digital-downloads' );
	}

	if ( intval( edd_get_option( 'success_page' ) ) === $post->ID ) {
		$post_states['edd_success_page'] = __( 'Receipt Page', 'easy-digital-downloads' );
	}

	if ( intval( edd_get_option( 'confirmation_page' ) ) === $post->ID ) {
		$post_states['edd_confirmation_page'] = __( 'Confirmation Page', 'easy-digital-downloads' );
	}

	return $post_states;
}

add_filter( 'widget_types_to_hide_from_legacy_widget_block', __NAMESPACE__ . '\remove_legacy_widgets' );
/**
 * Removes legacy widgets if they're not being used on the site and have block equivalents.
 *
 * @since 2.0
 * @param array $widget_types
 * @return array
 */
function remove_legacy_widgets( $widget_types ) {
	$legacy_widgets = array(
		'edd_cart_widget',
		'edd_categories_tags_widget',
	);

	foreach ( $legacy_widgets as $widget ) {
		if ( ! is_active_widget( false, false, $widget ) ) {
			$widget_types[] = $widget;
		}
	}

	return $widget_types;
}
