<?php
/**
 * Duplicator functionality for Easy Digital Downloads.
 */
namespace EDD\Pro\Admin\Duplicator;

class Controls implements \EDD\EventManagement\SubscriberInterface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'post_row_actions'            => array( 'link_row', 10, 2 ),
			'enqueue_block_editor_assets' => 'enqueue',
			'post_submitbox_start'        => 'classic_editor_duplicate_control',
		);
	}

	/**
	 * Enqueues the script needed to add the duplicator button to the editor.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function enqueue() {

		global $post;

		$screen = get_current_screen();

		if ( ! is_null( $screen ) && 'download' !== $screen->id && 'edit' !== $screen->parent_base ) {
			return;
		}

		if ( ! $this->is_duplicate_allowed( $post->ID ) ) {
			return;
		}

		wp_enqueue_script( 'edd-duplicator', EDD_PLUGIN_URL . 'assets/pro/js/duplicator.js', array( 'wp-element', 'wp-edit-post', 'wp-plugins' ), EDD_VERSION, true );
		wp_localize_script(
			'edd-duplicator',
			'eddDuplicator',
			array(
				'label' => __( 'Duplicate', 'easy-digital-downloads' ),
				'url'   => $this->get_duplicate_url( $post->ID ),
			)
		);
		wp_add_inline_style( 'wp-edit-post', '.edd-duplicator .components-button {justify-content:center;width:100%;}' );
	}

	/**
	 * Adds a duplicate link to the post row actions.
	 *
	 * @since 3.1.1
	 * @param array   $actions The array of actions.
	 * @param WP_Post $post    The post object.
	 * @return array
	 */
	public function link_row( $actions, $post ) {
		if ( ! $this->is_duplicate_allowed( $post->ID ) ) {
			return $actions;
		}

		$actions['duplicate'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $this->get_duplicate_url( $post->ID ) ),
			__( 'Duplicate', 'easy-digital-downloads' )
		);

		return $actions;
	}

	/**
	 * Duplicate download button for classic editor
	 *
	 * @since 3.1.3
	 *
	 * @param WP_Post $post
	 */
	public function classic_editor_duplicate_control( $post ) {
		if ( ! $this->is_duplicate_allowed( $post->ID ) ) {
			return;
		}

		echo sprintf(
			'<div id="edd-duplicate-action"><a class="duplication" href="%s">%s</a></div>',
			esc_url( $this->get_duplicate_url( $post->ID ) ),
			esc_html__( 'Duplicate', 'easy-digital-downloads' )
		);
	}

	/**
	 * Gets the URL to duplicate a download.
	 *
	 * @param int $post_id
	 * @return string
	 */
	private function get_duplicate_url( $post_id ) {

		$post_id = (int) $post_id;

		return wp_nonce_url(
			add_query_arg(
				array(
					'action' => 'duplicate_product',
					'post'   => absint( $post_id ),
				),
				admin_url( 'admin.php' )
			),
			"edd-duplicate-product_{$post_id}"
		);
	}

	/**
	 * Checks if the current user can duplicate a download.
	 *
	 * @since 3.1.3
	 *
	 * @param int $post_id
	 * @return boolean
	 */
	private function is_duplicate_allowed( $post_id ) {
		return 'download' === get_post_type( $post_id ) && current_user_can( 'edit_product', $post_id ) && 'auto-draft' !== get_post_status( $post_id );
	}
}
