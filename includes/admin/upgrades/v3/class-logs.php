<?php
/**
 * 3.0 Data Migration - Logs.
 *
 * @subpackage  Admin/Upgrades/v3
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Admin\Upgrades\v3;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Logs Class.
 *
 * @since 3.0
 */
class Logs extends Base {

	/**
	 * Constructor.
	 *
	 * @param int $step Step.
	 */
	public function __construct( $step = 1 ) {
		parent::__construct( $step );

		$this->completed_message = __( 'Logs migration completed successfully.', 'easy-digital-downloads' );
		$this->upgrade           = 'migrate_logs';
	}

	/**
	 * Retrieve the data pertaining to the current step and migrate as necessary.
	 *
	 * @since 3.0
	 *
	 * @return bool True if data was migrated, false otherwise.
	 */
	public function get_data() {
		$offset = ( $this->step - 1 ) * $this->per_step;

		$results = $this->get_db()->get_results( $this->get_db()->prepare(
			"SELECT p.*, t.slug
			 FROM {$this->get_db()->posts} AS p
			 LEFT JOIN {$this->get_db()->term_relationships} AS tr ON (p.ID = tr.object_id)
			 LEFT JOIN {$this->get_db()->term_taxonomy} AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
			 LEFT JOIN {$this->get_db()->terms} AS t ON (tt.term_id = t.term_id)
			 WHERE p.post_type = %s AND t.slug != %s 
			 GROUP BY p.ID
			 LIMIT %d, %d",
			esc_sql( 'edd_log' ), esc_sql( 'sale' ), $offset, $this->per_step
		) );

		if ( ! empty( $results ) ) {
			foreach ( $results as $old_log ) {
				if ( 'file_download' === $old_log->slug ) {
					$meta = $this->get_db()->get_results( $this->get_db()->prepare( "SELECT meta_key, meta_value FROM {$this->get_db()->postmeta} WHERE post_id = %d", absint( $old_log->ID ) ) );

					$post_meta = array();

					foreach ( $meta as $meta_item ) {
						$post_meta[ $meta_item->meta_key ] = maybe_unserialize( $meta_item->meta_value );
					}

					$log_data = array(
						'product_id'    => $old_log->post_parent,
						'file_id'       => $post_meta['_edd_log_file_id'],
						'order_id'      => $post_meta['_edd_log_payment_id'],
						'price_id'      => isset( $post_meta['_edd_log_price_id'] ) ? $post_meta['_edd_log_price_id'] : 0,
						'customer_id'   => isset( $post_meta['_edd_log_customer_id'] ) ? $post_meta['_edd_log_customer_id'] : 0,
						'ip'            => $post_meta['_edd_log_ip'],
						'date_created'  => $old_log->post_date_gmt,
						'date_modified' => $old_log->post_modified_gmt,
					);

					edd_add_file_download_log( $log_data );
				} elseif ( 'api_request' === $old_log->slug ) {
					$meta = $this->get_db()->get_results( $this->get_db()->prepare( "SELECT meta_key, meta_value FROM {$this->get_db()->postmeta} WHERE post_id = %d", absint( $old_log->ID ) ) );

					$post_meta = array();

					foreach ( $meta as $meta_item ) {
						$post_meta[ $meta_item->meta_key ] = maybe_unserialize( $meta_item->meta_value );
					}

					$post_meta = wp_parse_args( $post_meta, array(
						'_edd_log_request_ip' => '',
						'_edd_log_user'       => 0,
						'_edd_log_key'        => 'public',
						'_edd_log_token'      => 'public',
						'_edd_log_version'    => '',
						'_edd_log_time'       => '',
					) );

					$log_data = array(
						'ip'            => $post_meta['_edd_log_request_ip'],
						'user_id'       => $post_meta['_edd_log_user'],
						'api_key'       => $post_meta['_edd_log_key'],
						'token'         => $post_meta['_edd_log_token'],
						'version'       => $post_meta['_edd_log_version'],
						'time'          => $post_meta['_edd_log_time'],
						'request'       => $old_log->post_excerpt,
						'error'         => $old_log->post_content,
						'date_created'  => $old_log->post_date_gmt,
						'date_modified' => $old_log->post_modified_gmt,
					);

					edd_add_api_request_log( $log_data );
				} else {
					$post = \WP_Post::get_instance( $old_log->ID );

					$log_data = array(
						'object_id'     => $post->post_parent,
						'object_type'   => 'download',
						'type'          => $old_log->slug,
						'title'         => $old_log->post_title,
						'message'       => $old_log->post_content,
						'date_created'  => $old_log->post_date_gmt,
						'date_modified' => $old_log->post_modified_gmt,
					);

					$meta            = get_post_custom( $old_log->ID );
					$meta_to_migrate = array();

					foreach ( $meta as $key => $value ) {
						$meta_to_migrate[ $key ] = maybe_unserialize( $value[0] );
					}

					$new_log_id = edd_add_log( $log_data );

					if ( ! empty( $meta_to_migrate ) ) {
						foreach ( $meta_to_migrate as $key => $value ) {
							edd_add_log_meta( $new_log_id, $key, $value );
						}
					}
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Calculate the percentage completed.
	 *
	 * @since 3.0
	 *
	 * @return float Percentage.
	 */
	public function get_percentage_complete() {
		$total = $this->get_db()->get_var( $this->get_db()->prepare( "SELECT COUNT(ID) AS count FROM {$this->get_db()->posts} WHERE post_type = %s", esc_sql( 'edd_log' ) ) );

		if ( empty( $total ) ) {
			$total = 0;
		}

		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( $this->per_step * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}
}