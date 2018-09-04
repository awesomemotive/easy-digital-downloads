<?php
/**
 * 3.0 Data Migration - Remove Legacy Data.
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
 * Remove_Legacy_Data Class.
 *
 * @since 3.0
 */
class Remove_Legacy_Data extends Base {

	/**
	 * Constructor.
	 *
	 * @param int $step Step.
	 */
	public function __construct( $step = 1 ) {
		parent::__construct( $step );

		$this->completed_message = __( 'Legacy data removed successfully.', 'easy-digital-downloads' );
		$this->upgrade           = 'v30_data_migration';
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

		// Delete meta on first step.
		if ( 1 === $this->step ) {
			$this->get_db()->query( $this->get_db()->prepare( "DELETE FROM {$this->get_db()->edd_customermeta} WHERE meta_key = %s", esc_sql( 'additional_email' ) ) );
		}

		$results = $this->get_db()->get_col(
			$this->get_db()->prepare(
				"SELECT id
			 FROM {$this->get_db()->posts}
			 WHERE post_type = %s OR post_type = %s OR post_type = %s 
			 ORDER BY id ASC
			 LIMIT %d, %d",
				esc_sql( 'edd_payment' ),
				esc_sql( 'edd_discount' ),
				esc_sql( 'edd_log' ),
				$offset,
				$this->per_step
			),
			0
		);

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				wp_delete_post( $result, true );
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
		$total = $this->get_db()->get_var(
			$this->get_db()->prepare(
				"SELECT COUNT(id) AS count
			 FROM {$this->get_db()->posts}
			 WHERE post_type = %s OR post_type = %s OR post_type = %s",
				esc_sql( 'edd_payment' ),
				esc_sql( 'edd_discount' ),
				esc_sql( 'edd_log' )
			)
		);

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
