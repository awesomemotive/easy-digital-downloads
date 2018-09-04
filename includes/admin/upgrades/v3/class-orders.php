<?php
/**
 * 3.0 Data Migration - Orders.
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
 * Orders Class.
 *
 * @since 3.0
 */
class Orders extends Base {

	/**
	 * Constructor.
	 *
	 * @param int $step Step.
	 */
	public function __construct( $step = 1 ) {
		parent::__construct( $step );

		$this->completed_message = __( 'Orders migration completed successfully.', 'easy-digital-downloads' );
		$this->upgrade           = 'migrate_orders';
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

		$results = $this->get_db()->get_results(
			$this->get_db()->prepare(
				"SELECT *
			 FROM {$this->get_db()->posts}
			 WHERE post_type = %s
			 ORDER BY ID ASC
			 LIMIT %d, %d",
				esc_sql( 'edd_payment' ),
				$offset,
				$this->per_step
			)
		);

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {

				// Check if order has already been migrated.
				if ( $this->find_legacy_id( $result->ID, static::ORDERS ) ) {
					continue;
				}

				Data_Migrator::orders( $result );
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
		$total = $this->get_db()->get_var( $this->get_db()->prepare( "SELECT COUNT(id) AS count FROM {$this->get_db()->posts} WHERE post_type = %s", esc_sql( 'edd_payment' ) ) );

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
