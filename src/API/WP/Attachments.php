<?php
/**
 * Compatibility layer for the WP REST API Attachments Controller.
 *
 * @package EDD\API\WP
 * @since 3.3.5
 */

namespace EDD\API\WP;

use EDD\EventManagement\SubscriberInterface;
use EDD\Utils\FileSystem;

/**
 * Attachments Controller integration.
 *
 * @since 3.3.5
 */
class Attachments implements SubscriberInterface {

	/**
	 * Cache group for the attachments query args.
	 *
	 * @since 3.3.5
	 * @var string
	 */
	private const CACHE_GROUP = 'edd_wp_rest_attachments_query_args';

	/**
	 * {@inheritdoc}
	 */
	public static function get_subscribed_events() {
		return array(
			'rest_attachment_query' => array( 'filter_attachment_query', 10, 2 ),
			'post_updated'       => array( 'clear_cache', 10, 3 ),
		);
	}

	/**
	 * Filters the attachment query.
	 *
	 * @since 3.3.5
	 *
	 * @param array           $args Query arguments.
	 * @param WP_REST_Request $request The REST request.
	 */
	public function filter_attachment_query( $args, $request ) {
		if ( current_user_can( 'edit_products' ) ) {
			return $args;
		}

		$download_ids                = $this->get_download_ids();
		$args['post_parent__not_in'] = isset( $args['post_parent__not_in'] ) ? array_merge( $args['post_parent__not_in'], $download_ids ) : $download_ids;

		$attachment_ids       = $this->get_attachment_ids_by_guid();
		$args['post__not_in'] = isset( $args['post__not_in'] ) ? array_merge( $args['post__not_in'], $attachment_ids ) : $attachment_ids;

		$detached_file_ids    = $this->get_detached_files_ids();
		$args['post__not_in'] = isset( $args['post__not_in'] ) ? array_merge( $args['post__not_in'], $detached_file_ids ) : $detached_file_ids;

		$args['post_parent__not_in'] = array_unique( $args['post_parent__not_in'] );
		$args['post__not_in']        = array_unique( $args['post__not_in'] );

		return $args;
	}

	/**
	 * Gets the download IDs.
	 *
	 * Collects and stores all downloads IDs.
	 *
	 * @since 3.3.5
	 *
	 * @return array
	 */
	private function get_download_ids() {
		$download_ids = wp_cache_get( 'edd_download_ids', self::CACHE_GROUP );

		if ( false === $download_ids ) {
			$download_ids = get_posts(
				array(
					'post_type' => 'download',
					'fields'    => 'ids',
				)
			);

			wp_cache_set( 'edd_download_ids', $download_ids, self::CACHE_GROUP, DAY_IN_SECONDS );
		}

		return $download_ids;
	}

	/**
	 * Gets the attachment IDs by GUID.
	 *
	 * Collects all attachment Post IDs where the GUID contains the EDD upload base directory.
	 *
	 * @since 3.3.5
	 *
	 * @return array
	 */
	private function get_attachment_ids_by_guid() {
		$file_ids_in_path = wp_cache_get( 'edd_attachments_by_path', self::CACHE_GROUP );

		if ( false === $file_ids_in_path ) {
			global $wpdb;

			$edd_upload_dir     = edd_get_upload_dir();
			$edd_upload_basedir = str_replace( ABSPATH, '', trailingslashit( $edd_upload_dir ) );

			$file_ids_in_path = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT ID FROM $wpdb->posts
					WHERE post_type = 'attachment'
					AND guid LIKE %s",
					'%' . $wpdb->esc_like( $edd_upload_basedir ) . '%'
				)
			);

			wp_cache_set( 'edd_attachments_by_path', $file_ids_in_path, self::CACHE_GROUP, DAY_IN_SECONDS );
		}

		return $file_ids_in_path;
	}

	/**
	 * Get attachment IDs that are not attached to a download or are not in our uploads directory.
	 *
	 * @since 3.3.5
	 *
	 * @return array
	 */
	private function get_detached_files_ids() {
		$detached_file_ids = wp_cache_get( 'edd_detached_file_ids', self::CACHE_GROUP );

		if ( false === $detached_file_ids ) {
			global $wpdb;

			$download_files = $wpdb->get_results(
				"SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = 'edd_download_files'"
			);

			$found_file_ids = array();

			// Loop through the results and collect the file IDs.
			foreach ( $download_files as $download_file ) {
				$file_data = maybe_unserialize( $download_file->meta_value );

				foreach ( $file_data as $file ) {
					// If we got an attachment ID, add it to the list and move to the next item.
					if ( ! empty( $file['attachment_id'] ) ) {
						$found_file_ids[] = absint( $file['attachment_id'] );
						continue;
					}

					$file_path = FileSystem::sanitize_file_path( $file['file'] );
					$file_id   = $wpdb->get_var(
						$wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid = %s", $file_path )
					);

					if ( $file_id ) {
						$found_file_ids[] = absint( $file_id );
					}
				}
			}

			$detached_file_ids = array_unique( $found_file_ids );

			wp_cache_set( 'edd_detached_file_ids', $detached_file_ids, self::CACHE_GROUP, DAY_IN_SECONDS );
		}

		return $detached_file_ids;
	}

	/**
	 * Clears the cache when a download is updated.
	 *
	 * @since 3.3.5
	 * @return void
	 */
	public function clear_cache( $post_id, $post, $update ) {
		if ( 'download' === $post->post_type ) {
			wp_cache_delete_multiple(
				array(
					'edd_download_ids',
					'edd_attachments_by_path',
					'edd_detached_file_ids',
				),
				self::CACHE_GROUP
			);
		}
	}
}
