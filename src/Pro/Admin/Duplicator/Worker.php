<?php
/**
 * Duplicator functionality for Easy Digital Downloads.
 */
namespace EDD\Pro\Admin\Duplicator;

class Worker implements \EDD\EventManagement\SubscriberInterface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'admin_action_duplicate_product' => 'duplicate',
		);
	}

	/**
	 * Duplicates a product.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function duplicate() {

		// Get the original product
		$id = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );
		if ( empty( $id ) ) {
			$id = filter_input( INPUT_POST, 'post', FILTER_SANITIZE_NUMBER_INT );
		}

		$post = $this->get_product_to_duplicate( $id );

		// Exit silently if a post is found, but isn't a download.
		if ( ! empty( $post->post_type ) && 'download' !== $post->post_type ) {
			return;
		}

		check_admin_referer( "edd-duplicate-product_{$id}" );

		if ( ! current_user_can( 'edit_product', $id ) ) {
			wp_die( esc_html__( 'You do not have permission to duplicate this product.', 'easy-digital-downloads' ) );
		}

		if ( empty( $id ) || ( isset( $_REQUEST['action'] ) && 'duplicate_post_save_as_new_page' === $_REQUEST['action'] ) ) {
			wp_die( esc_html__( 'The product to duplicate is missing.', 'easy-digital-downloads' ) );
		}

		// Copy the product.
		if ( null !== $post ) {
			$new_id = $this->create_duplicate_from_product( $post );

			do_action( 'edd_duplicate_product', $new_id, $post );

			// Redirect to the edit screen for the new draft page
			edd_redirect(
				add_query_arg(
					array(
						'action' => 'edit',
						'post'   => absint( $new_id ),
					),
					admin_url( 'post.php' )
				)
			);
		}

		/* translators: the original product ID. */
		wp_die( sprintf( esc_html__( 'Product creation failed, could not find original product: %s', 'easy-digital-downloads' ), $id ) );
	}

	/**
	 * Gets a product from the database.
	 *
	 * @since 3.1.1
	 * @param int|string $id The ID of the download to duplicate.
	 * @return object|null Returns an object if a download is found; otherwise null.
	 */
	private function get_product_to_duplicate( $id ) {
		global $wpdb;

		$post = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID = %d", $id ) );
		if ( isset( $post->post_type ) && 'revision' === $post->post_type ) {
			$id   = $post->post_parent;
			$post = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID = %d", $id ) );
		}

		return $post;
	}

	/**
	 * Handles the actual product duplication.
	 *
	 * @since 3.1.1
	 * @param WP_Post $post        The original post/product.
	 * @param int     $parent      The original product parent.
	 * @param string  $post_status The original post status.
	 * @return int
	 */
	private function create_duplicate_from_product( $post ) {

		$new_post_author   = wp_get_current_user();
		$new_post_date     = current_time( 'mysql' );
		$new_post_date_gmt = get_gmt_from_date( $new_post_date );
		$post_status       = 'draft';
		$suffix            = ' ' . __( '(Copy)', 'easy-digital-downloads' );

		$new_post_type         = $post->post_type;
		$post_content          = str_replace( "'", "''", $post->post_content );
		$post_content_filtered = str_replace( "'", "''", $post->post_content_filtered );
		$post_excerpt          = str_replace( "'", "''", $post->post_excerpt );
		$post_title            = str_replace( "'", "''", $post->post_title ) . $suffix;
		$post_name             = str_replace( "'", "''", $post->post_name );
		$comment_status        = str_replace( "'", "''", $post->comment_status );
		$ping_status           = str_replace( "'", "''", $post->ping_status );

		// Insert the new product in the post table.
		global $wpdb;
		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$wpdb->posts}
			(post_author, post_date, post_date_gmt, post_content, post_content_filtered, post_title, post_excerpt,  post_status, post_type, comment_status, ping_status, post_password, to_ping, pinged, post_modified, post_modified_gmt, post_parent, menu_order, post_mime_type)
			VALUES
			(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
				$new_post_author->ID,
				$new_post_date,
				$new_post_date_gmt,
				$post_content,
				$post_content_filtered,
				$post_title,
				$post_excerpt,
				$post_status,
				$new_post_type,
				$comment_status,
				$ping_status,
				$post->post_password,
				$post->to_ping,
				$post->pinged,
				$new_post_date,
				$new_post_date_gmt,
				$post->post_parent,
				$post->menu_order,
				$post->post_mime_type
			)
		);

		$new_post_id = $wpdb->insert_id;

		// Copy the taxonomies.
		$this->duplicate_taxonomies( $post->ID, $new_post_id, $post->post_type );

		// Copy the meta information.
		$this->duplicate_post_meta( $post->ID, $new_post_id );

		// Clear Sales Data.
		update_post_meta( $new_post_id, '_edd_download_earnings', 0.00 );
		update_post_meta( $new_post_id, '_edd_download_sales', 0 );

		return $new_post_id;
	}

	/**
	 * Copy a post's taxonomies.
	 *
	 * @param int    $id        The original product ID.
	 * @param int    $new_id    The new product ID.
	 * @param string $post_type The post type.
	 * @return void
	 */
	private function duplicate_taxonomies( $id, $new_id, $post_type ) {
		$taxonomies = get_object_taxonomies( $post_type );
		if ( empty( $taxonomies ) ) {
			return;
		}
		foreach ( $taxonomies as $taxonomy ) {

			$post_terms = wp_get_object_terms( $id, $taxonomy );
			if ( empty( $post_terms ) || \is_wp_error( $post_terms ) ) {
				continue;
			}
			$post_terms_count = count( $post_terms );

			for ( $i = 0; $i < $post_terms_count; $i++ ) {
				wp_set_object_terms( $new_id, $post_terms[ $i ]->slug, $taxonomy, true );
			}
		}
	}

	/**
	 * Copy the meta information of a Product to another Product
	 *
	 * @since 3.1.1
	 * @param int $id     The original download ID.
	 * @param int $new_id The new download ID.
	 */
	private function duplicate_post_meta( $id, $new_id ) {
		global $wpdb;
		$post_meta_infos = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d", $id ) );

		if ( count( $post_meta_infos ) ) {
			$sql_query     = "INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) ";
			$sql_query_sel = array();
			foreach ( $post_meta_infos as $meta_info ) {
				$sql_query_sel[] = $wpdb->prepare(
					'SELECT %d, %s, %s',
					$new_id,
					$meta_info->meta_key,
					$meta_info->meta_value
				);
			}
			$sql_query .= implode( ' UNION ALL ', $sql_query_sel );
			$wpdb->query( $sql_query );
		}
	}
}
