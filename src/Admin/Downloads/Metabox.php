<?php
/**
 * Metabox class.
 *
 * @since 3.2.0
 * @package EDD\Admin\Downloads
 */

namespace EDD\Admin\Downloads;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;

/**
 * Metabox class.
 *
 * @since 3.2.0
 */
class Metabox implements SubscriberInterface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.2.0
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'wp_ajax_edd_swap_download_type' => 'swap_download_type',
			'add_meta_boxes'                 => array( 'add_meta_boxes', 9, 2 ),
			'wp_ajax_edd_clone_variation'    => 'clone_variation',
		);
	}

	/**
	 * Potentially swaps out the download files HTML when the product type changes.
	 *
	 * @since 3.2.0
	 */
	public function swap_download_type() {
		if ( ! current_user_can( 'edit_products' ) || ! edd_doing_ajax() ) {
			wp_send_json_error();
		}

		$download_id = ! empty( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : null;
		if ( is_null( $download_id ) || ! current_user_can( 'edit_product', $download_id ) ) {
			wp_send_json_error();
		}

		ob_start();
		do_action( 'edd_meta_box_files_fields', $download_id, $this->get_download_type() );

		wp_send_json_success(
			array(
				'html'  => ob_get_clean(),
				'label' => 'bundle' === $this->get_download_type() ? edd_get_label_plural() : __( 'Files', 'easy-digital-downloads' ),
			)
		);
	}

	/**
	 * Adds the metaboxes.
	 *
	 * @since 3.3.6
	 * @param string $post_type The post type.
	 * @param object $post      The post object.
	 */
	public function add_meta_boxes( $post_type, $post ) {

		if ( ! $post instanceof \WP_Post ) {
			return;
		}

		$download = edd_get_download( $post->ID );

		foreach ( $this->get_registered_metaboxes( $post->ID ) as $metabox ) {
			if ( ! $metabox instanceof Metaboxes\Metabox ) {
				continue;
			}

			$metabox->set_download( $download );
			$config = $metabox->get_config();

			add_meta_box(
				$config['id'],
				$config['title'],
				$config['callback'],
				$config['screen'],
				$config['context'],
				$config['priority'],
				$config['callback_args']
			);
		}
	}

	/**
	 * Clones a section via AJAX.
	 *
	 * @since 3.3.6
	 */
	public function clone_variation() {
		if ( ! current_user_can( 'edit_products' ) || ! edd_doing_ajax() ) {
			wp_send_json_error();
		}

		$token     = filter_input( INPUT_POST, 'token', FILTER_SANITIZE_SPECIAL_CHARS );
		$timestamp = filter_input( INPUT_POST, 'timestamp', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( ! \EDD\Utils\Tokenizer::is_token_valid( $token, $timestamp ) ) {
			wp_send_json_error();
		}
		$section_id = filter_input( INPUT_POST, 'section', FILTER_SANITIZE_NUMBER_INT );
		if ( empty( $section_id ) ) {
			wp_send_json_error();
		}
		$download_id = filter_input( INPUT_POST, 'download_id', FILTER_SANITIZE_NUMBER_INT );
		if ( empty( $download_id ) || ! current_user_can( 'edit_product', $download_id ) ) {
			wp_send_json_error();
		}

		$section = new Editor\VariablePrices();

		ob_start();
		$section->do_row(
			edd_get_download( $download_id ),
			array(
				'name'   => '',
				'amount' => '',
				'index'  => $section_id,
			),
			$section_id
		);

		wp_send_json_success( ob_get_clean() );
	}

	/**
	 * Gets the download type.
	 *
	 * @since 3.2.3
	 * @return string
	 */
	private function get_download_type() {
		return ( ! empty( $_POST['product_type'] ) && 'bundle' === $_POST['product_type'] ) ? 'bundle' : 'default';
	}

	/**
	 * Gets the registered metaboxes.
	 *
	 * @since 3.3.6
	 * @return array
	 */
	private function get_registered_metaboxes( $post_id ) {
		$metaboxes = array(
			new Metaboxes\Details(),
		);
		if ( current_user_can( 'view_product_stats', $post_id ) ) {
			$metaboxes[] = new Metaboxes\Stats();
		}

		return apply_filters( 'edd_download_editor_metaboxes', $metaboxes );
	}
}
