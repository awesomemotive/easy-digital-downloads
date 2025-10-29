<?php
/**
 * Checkout Elementor Widget
 *
 * @package     EDD\Elementor\Widgets
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Elementor\Widgets;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Elementor\Utils\Page;
use EDD\Elementor\Widgets\Config\Checkout as Config;

/**
 * EDD Checkout Widget for Elementor
 *
 * @since 3.6.0
 */
class Checkout extends Base {

	use Traits\ConfigurableControls;

	/**
	 * Enqueue the checkout style.
	 *
	 * @since 3.6.0
	 */
	public static function enqueue_style() {
		if ( wp_script_is( 'edd-checkout-style', 'enqueued' ) ) {
			return;
		}

		wp_enqueue_style(
			'edd-checkout-style',
			EDD_BLOCKS_URL . 'build/checkout/style-index.css',
			array(),
			EDD_VERSION
		);
	}

	/**
	 * Get widget name
	 *
	 * @since 3.6.0
	 * @return string Widget name
	 */
	public function get_name(): string {
		return 'edd-checkout';
	}

	/**
	 * Get widget title
	 *
	 * @since 3.6.0
	 * @return string Widget title
	 */
	public function get_title(): string {
		return __( 'EDD Checkout', 'easy-digital-downloads' );
	}

	/**
	 * Get widget icon
	 *
	 * @since 3.6.0
	 * @return string Widget icon
	 */
	public function get_icon(): string {
		return 'dashicons dashicons-download';
	}

	/**
	 * Get widget keywords
	 *
	 * @since 3.6.0
	 * @return array Widget keywords
	 */
	public function get_keywords(): array {
		return array( 'edd', 'checkout', 'purchase', 'buy', 'payment' );
	}

	/**
	 * Get the style dependencies for the widget.
	 *
	 * @since 3.6.0
	 * @return array The style dependencies for the widget.
	 */
	public function get_style_depends() {
		return array( 'edd-checkout-style' );
	}

	/**
	 * Get the script dependencies for the widget.
	 *
	 * @since 3.6.0
	 * @return array The script dependencies for the widget.
	 */
	public function get_script_depends() {
		if ( Page::is_edit_mode() ) {
			return array();
		}

		return array( 'edd-checkout-global', 'edd-ajax' );
	}

	/**
	 * Get the element raw data.
	 *
	 * Override the parent method to exclude preview settings from being saved.
	 *
	 * @since 3.6.0
	 *
	 * @param bool $with_html_content Optional. Whether to return the data with
	 *                                HTML content or without. Used for caching.
	 *                                Default is false, without HTML.
	 *
	 * @return array Element raw data.
	 */
	public function get_raw_data( $with_html_content = false ) {
		$data = parent::get_raw_data( $with_html_content );

		// Remove preview settings from being saved to database.
		if ( isset( $data['settings']['preview_cart_item'] ) ) {
			unset( $data['settings']['preview_cart_item'] );
		}

		return $data;
	}

	/**
	 * Register widget controls
	 *
	 * @since 3.6.0
	 */
	protected function register_controls() {
		$this->register_controls_from_config( Config::get_all_controls() );
	}

	/**
	 * Render the widget output on the frontend
	 *
	 * @since 3.6.0
	 */
	protected function render() {
		$this->before_render_hooks();
		echo \EDD\Blocks\Checkout\checkout( $this->get_attributes() );
		$this->after_render_hooks();
	}

	/**
	 * Render widget plain content.
	 *
	 * Override the default behavior to save block code instead of rendered HTML.
	 * This method is called when Elementor saves the page content to post_content.
	 *
	 * @since 3.6.0
	 */
	public function render_plain_content() {
		echo '<!-- wp:edd/checkout ' . wp_json_encode( $this->get_attributes() ) . ' /-->';
	}

	/**
	 * Get the selector prefix for this widget.
	 *
	 * @since 3.6.0
	 * @return string
	 */
	protected function get_selector_prefix(): string {
		return '{{WRAPPER}} #edd_checkout_form_wrap';
	}

	/**
	 * Get the selector mappings for this widget.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	protected function get_selector_mappings(): array {
		return array(
			'form fieldset' => 'form fieldset:not(#edd_purchase_submit)',
		);
	}

	/**
	 * Before widget render hooks.
	 *
	 * @since 3.6.0
	 */
	private function before_render_hooks() {
		$this->setup_preview_settings();
		self::enqueue_style();
	}

	/**
	 * Setup preview settings.
	 *
	 * @since 3.6.0
	 */
	private function setup_preview_settings() {
		if ( ! Page::is_edit_mode() ) {
			return;
		}

		$user                               = wp_get_current_user();
		$_GET['edd_blocks_is_block_editor'] = md5( $user->user_email );

		// Set preview parameter for guest preview functionality.
		if ( ! empty( $this->get_settings( 'preview_as_guest' ) ) ) {
			add_filter( 'edd_blocks_doing_guest_preview', '__return_true' );
		}

		$cart_item = $this->get_settings( 'preview_cart_item' );
		if ( ! empty( $cart_item ) ) {
			$_GET['cart_item'] = $cart_item;
		}
	}

	/**
	 * After widget render hooks.
	 *
	 * @since 3.6.0
	 */
	private function after_render_hooks() {}

	/**
	 * Get the attributes for the widget.
	 *
	 * @since 3.6.0
	 * @return array The attributes for the widget.
	 */
	private function get_attributes() {
		return array(
			'layout'             => sanitize_text_field( $this->get_settings( 'layout' ) ),
			'show_discount_form' => filter_var( $this->get_settings( 'show_discount_form' ), FILTER_VALIDATE_BOOLEAN ),
			'thumbnail_width'    => $this->get_thumbnail_width(),
		);
	}

	/**
	 * Get the thumbnail width.
	 *
	 * @since 3.6.0
	 * @return int The thumbnail width.
	 */
	private function get_thumbnail_width() {
		$thumbnail_width = $this->get_settings( 'thumbnail_width' );
		if ( ! empty( $thumbnail_width['size'] ) ) {
			return (int) max( 10, min( 100, $thumbnail_width['size'] ) );
		}

		return 25;
	}

	/**
	 * Get the selectors for the widget.
	 *
	 * @since 3.6.0
	 *
	 * @param string|array $selector The selector to get.
	 * @return string|array The selectors for the widget.
	 */
	private function selectors( $selector ) {
		$prefix = '{{WRAPPER}} #edd_checkout_form_wrap';

		if ( is_array( $selector ) ) {
			$new_selectors = array();
			foreach ( $selector as $_selector => $style ) {
				$new_selectors[ "{$prefix} {$this->mapped_selectors( $_selector )}" ] = $style;
			}
			return $new_selectors;
		}

		return "{$prefix} {$this->mapped_selectors( $selector )}";
	}

	/**
	 * Get the mapped selectors for the widget.
	 *
	 * @since 3.6.0
	 * @param string $selector The selector to get.
	 * @return string The mapped selectors for the widget.
	 */
	private function mapped_selectors( $selector ) {
		$mapped_selectors = array(
			'form fieldset' => 'form fieldset:not(#edd_purchase_submit)',
		);

		return str_replace( array_keys( $mapped_selectors ), array_values( $mapped_selectors ), $selector );
	}
}
