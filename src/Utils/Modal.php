<?php
/**
 * Modal Utilities
 *
 * Provides reusable modal rendering and asset management.
 *
 * @package     EDD\Utils
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.3
 */

namespace EDD\Utils;

use EDD\EventManagement\SubscriberInterface;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Modal class.
 *
 * @since 3.5.3
 */
class Modal implements SubscriberInterface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.5.3
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'wp_enqueue_scripts' => 'enqueue',
		);
	}

	/**
	 * Enqueue modal assets.
	 *
	 * Only enqueues if a modal has been rendered on the page.
	 * Note: CSS is embedded in the Web Component's Shadow DOM, so only JS is needed.
	 *
	 * @since 3.5.3
	 * @return void
	 */
	public function enqueue() {

		// No CSS file needed - styles are embedded in the Web Component's Shadow DOM.
		wp_register_script(
			'edd-modal',
			EDD_PLUGIN_URL . 'assets/js/edd-modal.js',
			array(),
			EDD_VERSION,
			true
		);

		// Localize strings for the Web Component.
		wp_localize_script(
			'edd-modal',
			'eddModal',
			array_merge(
				\EDD\Utils\Colors::get_button_colors(),
				array(
					'close' => __( 'Close', 'easy-digital-downloads' ),
				)
			)
		);
	}

	/**
	 * Render a reusable modal container.
	 *
	 * Outputs the HTML structure for a generic EDD modal that can be used
	 * with the EDDModal JavaScript class. Modal HTML is deferred to wp_footer.
	 *
	 * Uses a custom <edd-modal> Web Component with Shadow DOM for style isolation.
	 *
	 * @since 3.5.3
	 *
	 * @param string $type Modal type identifier (e.g., 'verification', 'download').
	 * @param array  $args {
	 *     Optional. Array of arguments for customizing the modal.
	 *
	 *     @type string $modal_class Additional CSS classes for the custom element. Default empty.
	 *     @type string $title       Modal title for screen readers. Default empty.
	 * }
	 * @return void
	 */
	public static function render( $type, $args = array() ) {
		add_action(
			'wp_footer',
			function () use ( $type, $args ) {
				self::do_modal( $type, $args );
			},
			5
		);
	}

	/**
	 * Actually renders the modal HTML.
	 *
	 * Outputs a custom <edd-modal> Web Component element. The component uses Shadow DOM
	 * for complete style isolation from theme/plugin styles.
	 *
	 * @since 3.5.3
	 * @param string $type Modal type identifier.
	 * @param array  $args Modal arguments.
	 * @return void
	 */
	private static function do_modal( $type, $args = array() ) {
		if ( did_action( 'edd_modal_rendered' ) ) {
			return;
		}

		$defaults = array(
			'modal_class' => '',
			'title'       => '',
		);

		$args = wp_parse_args( $args, $defaults );

		// Generate ID based on type.
		$modal_id = 'edd-' . sanitize_html_class( $type ) . '-modal';

		// Build CSS classes for the custom element (optional, for light DOM styling if needed).
		$modal_classes = '';
		if ( ! empty( $args['modal_class'] ) ) {
			$modal_classes = ' class="' . esc_attr( $args['modal_class'] ) . '"';
		}

		// Set aria-label if title is provided.
		$aria_label = ! empty( $args['title'] ) ? ' aria-label="' . esc_attr( $args['title'] ) . '"' : '';

		wp_enqueue_script( 'edd-modal' );
		?>
		<edd-modal id="<?php echo esc_attr( $modal_id ); ?>"<?php echo $modal_classes; ?><?php echo $aria_label; ?>></edd-modal>
		<?php

		// Trigger action so we know a modal was rendered.
		do_action( 'edd_modal_rendered', $type, $args );
	}
}
