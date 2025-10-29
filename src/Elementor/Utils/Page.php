<?php
/**
 * Elementor Page Utility
 *
 * @package     EDD\Elementor\Utils
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Elementor\Utils;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use Elementor\Plugin as ElementorPlugin;

/**
 * Page utility class.
 *
 * @since 3.6.0
 */
class Page {

	/**
	 * Get the page data.
	 *
	 * @since 3.6.0
	 *
	 * @param int|null $current_page Current page ID.
	 * @return array|false
	 */
	public static function get_page_data( $current_page = null ) {

		if ( is_null( $current_page ) ) {
			$current_page = self::get_id();
		}

		$post = get_post( $current_page );
		if ( ! $post ) {
			return false;
		}

		$document = \Elementor\Plugin::$instance->documents->get( $post->ID );
		if ( ! $document || ! $document->is_built_with_elementor() ) {
			return false;
		}

		return $document->get_elements_data();
	}

	/**
	 * Check if the page has a widget.
	 *
	 * @since 3.6.0
	 *
	 * @param string $widget_type Widget type.
	 * @param array  $elements Page elements data.
	 * @return bool
	 */
	public static function has_widget( string $widget_type, $elements = null ): bool {
		return ! empty( self::get_widget_data( $widget_type, $elements ) );
	}

	/**
	 * Get the widget data.
	 *
	 * @since 3.6.0
	 *
	 * @param string $widget_type Widget type.
	 * @param array  $elements Page elements data.
	 * @param int    $occurrence  Occurrence number.
	 * @return array
	 */
	public static function get_widget_data( string $widget_type, $elements = null, int $occurrence = 1 ): array {
		$elements = $elements ?? self::get_page_data();

		if ( ! is_array( $elements ) ) {
			return array();
		}

		$found = 0;
		foreach ( $elements as $element ) {
			if ( isset( $element['widgetType'] ) && $widget_type === $element['widgetType'] ) {
				++$found;
				if ( $found === $occurrence ) {
					return $element;
				}
			}

			if ( isset( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$data = self::get_widget_data( $widget_type, $element['elements'] );
				if ( ! empty( $data ) ) {
					return $data;
				}
			}
		}
		return array();
	}

	/**
	 * Check if the page is in edit mode.
	 *
	 * @since 3.6.0
	 *
	 * @return bool
	 */
	public static function is_edit_mode(): bool {
		$editor = ElementorPlugin::$instance->editor;
		if ( $editor && $editor->is_edit_mode() ) {
			return true;
		}

		$request_uri = $_SERVER['REQUEST_URI'];
		if ( false !== strpos( $request_uri, 'elementor-preview' ) ) {
			return true;
		}

		if ( edd_doing_ajax() && strpos( wp_get_referer(), 'elementor-preview' ) !== false ) {
			return true;
		}

		return false;
	}

	/**
	 * Get the current page ID.
	 *
	 * @since 3.6.0
	 * @return int The page ID, or 0 if not found.
	 */
	private static function get_id(): int {
		$id = self::get_current_page_by_elementor_request();
		if ( $id > 0 ) {
			return $id;
		}

		if ( edd_doing_ajax() && ! empty( $_POST['current_page'] ) ) {
			return absint( $_POST['current_page'] );
		}

		return 0;
	}

	/**
	 * Get the current page ID from Elementor request parameters.
	 *
	 * @since 3.6.0
	 * @return int The page ID, or 0 if not found.
	 */
	private static function get_current_page_by_elementor_request(): int {
		// Check for elementor-preview parameter.
		$preview_id = $_REQUEST['elementor-preview'] ?? null;
		if ( is_numeric( $preview_id ) && (int) $preview_id > 0 ) {
			return absint( $preview_id );
		}

		// Check for elementor action with post parameter.
		$action  = $_REQUEST['action'] ?? '';
		$post_id = $_REQUEST['post'] ?? null;

		if ( 'elementor' === $action && $post_id && is_numeric( $post_id ) && (int) $post_id > 0 ) {
			return (int) $post_id;
		}

		return 0;
	}
}
