<?php
/**
 * 3.0 Data Migration - Discounts.
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
 * Discounts Class.
 *
 * @since 3.0
 */
class Discounts extends Base {

	/**
	 * Constructor.
	 *
	 * @param int $step Step.
	 */
	public function __construct( $step = 1 ) {
		parent::__construct( $step );

		$this->completed_message = __( 'Discounts migration completed successfully.', 'easy-digital-downloads' );
		$this->upgrade           = 'migrate_discounts';
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
			"SELECT *
			 FROM {$this->get_db()->posts}
			 WHERE post_type = %s
			 LIMIT %d, %d",
			esc_sql( 'edd_discount' ), $offset, $this->per_step
		) );

		if ( ! empty( $results ) ) {
			foreach ( $results as $old_discount ) {

				// Check if discount has already been migrated.
				if ( $old_discount->ID !== $this->remap_id( $old_discount->ID, static::DISCOUNTS ) ) {
					continue;
				}

				$old_discount = get_post( $old_discount->ID );

				$args            = array();
				$meta            = get_post_custom( $old_discount->ID );
				$meta_to_migrate = array();

				foreach ( $meta as $key => $value ) {
					if ( false === strpos( $key, '_edd_discount' ) ) {

						// This is custom meta from another plugin that needs to be migrated to the new meta table.
						$meta_to_migrate[ $key ] = maybe_unserialize( $value[0] );
						continue;
					}

					$value = maybe_unserialize( $value[0] );
					$args[ str_replace( '_edd_discount_', '', $key ) ] = $value;
				}

				// If the discount name was not stored in post_meta, use value from the WP_Post object.
				if ( ! isset( $args['name'] ) ) {
					$args['name'] = $old_discount->post_title;
				}

				$args['date_created']  = $old_discount->post_date_gmt;
				$args['date_modified'] = $old_discount->post_modified_gmt;

				// Use edd_store_discount() so any legacy data is handled correctly.
				$discount_id = edd_store_discount( $args );

				// Migrate any additional meta.
				if ( ! empty( $meta_to_migrate ) ) {
					foreach ( $meta_to_migrate as $key => $value ) {
						edd_add_adjustment_meta( $discount_id, $key, $value );
					}
				}

				// Store legacy discount ID.
				edd_add_adjustment_meta( $discount_id, 'legacy_discount_id', $old_discount->ID );
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
		$total = $this->get_db()->get_var( $this->get_db()->prepare( "SELECT COUNT(ID) AS count FROM {$this->get_db()->posts} WHERE post_type = %s", esc_sql( 'edd_discount' ) ) );

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