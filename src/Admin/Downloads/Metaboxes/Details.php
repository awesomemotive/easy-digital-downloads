<?php
/**
 * Details metabox.
 *
 * @package   EDD\Admin\Downloads\Metaboxes
 * @copyright Copyright (c) 2024, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.3.6
 */

namespace EDD\Admin\Downloads\Metaboxes;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Admin\Downloads\Editor;

/**
 * Details metabox class.
 */
class Details extends Metabox {

	/**
	 * Metabox ID.
	 *
	 * @var string
	 */
	protected $id = 'edd_product_details';

	/**
	 * Context.
	 *
	 * @var string
	 */
	protected $context = 'normal';

	/**
	 * Priority.
	 *
	 * @var string
	 */
	protected $priority = 'high';

	/**
	 * Gets the metabox title.
	 *
	 * @since 3.3.6
	 * @return string
	 */
	public function get_title(): string {
		return sprintf(
			/* translators: %s: Download singular label */
			__( '%s Details', 'easy-digital-downloads' ),
			edd_get_label_singular(),
		);
	}

	/**
	 * Renders the metabox.
	 *
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	public function render( \WP_Post $post ) {

		$post_id = $post->ID;
		if ( is_numeric( $post_id ) && ! current_user_can( 'edit_product', $post_id ) ) {
			return;
		}

		if ( is_null( $post_id ) && ! current_user_can( 'edit_products' ) ) {
			return;
		}

		if ( ! $this->download ) {
			return;
		}

		$registered_sections = $this->get_registered_sections( $this->download );
		if ( empty( $registered_sections ) ) {
			return;
		}

		$metabox_sections = new Editor\Sections();
		$metabox_sections->set_sections( $registered_sections );
		$metabox_sections->set_item( $this->download );
		$metabox_sections->display();

		/**
		 * Output the download details metabox.
		 *
		 * @since 3.3.6
		 * @param \EDD_Download $download The download.
		 * @param \WP_Post      $post     The post object.
		 */
		do_action( 'edd_metabox_download_details', $this->download, $post );

		wp_nonce_field( 'edd_metabox_download_details', 'edd_download_meta_box_nonce' );
	}

	/**
	 * Get the sections.
	 *
	 * @since 3.3.6
	 * @return array
	 */
	private function get_registered_sections( $download ) {

		/**
		 * Allow developers to add, remove, or modify the sections in the download details metabox.
		 *
		 * @since 3.3.6
		 * @param array $sections Array of sections.
		 */
		$sections = apply_filters(
			'edd_download_details_sections',
			array(
				'details'          => Editor\Details::class,
				'variable-pricing' => Editor\VariablePrices::class,
				'files'            => Editor\Files::class,
				'notes'            => Editor\Notes::class,
				'settings'         => Editor\Settings::class,
			)
		);

		if ( empty( $sections ) ) {
			return array();
		}

		foreach ( $sections as $key => $section ) {
			$valid_section = $this->validate_section( $section, $key );
			if ( ! $valid_section ) {
				continue;
			}

			$registered_sections[] = $valid_section->get_config();
		}

		// Now sort the sections by priority.
		usort( $registered_sections, array( $this, 'sort_sections_by_priority' ) );

		return $registered_sections;
	}
}
