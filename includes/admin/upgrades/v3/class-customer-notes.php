<?php
/**
 * 3.0 Data Migration - Customer Notes.
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
 * Customer_Notes Class.
 *
 * @since 3.0
 */
class Customer_Notes extends Base {

	/**
	 * Constructor.
	 *
	 * @param int $step Step.
	 */
	public function __construct( $step = 1 ) {
		parent::__construct( $step );

		$this->completed_message = __( 'Customer notes migration completed successfully.', 'easy-digital-downloads' );
		$this->upgrade           = 'migrate_customer_notes';
	}

	/**
	 * Retrieve the data pertaining to the current step and migrate as necessary.
	 *
	 * @internal This batch processor migrates all the data in one step as the customer notes were previously stored as one
	 *           large block of text making it impossible to split the data with the query.
	 *
	 * @since 3.0
	 *
	 * @return bool True if data was migrated, false otherwise.
	 */
	public function get_data() {
		$offset = ( $this->step - 1 ) * $this->per_step;

		$results = $this->get_db()->get_results( $this->get_db()->prepare(
			"SELECT *
			 FROM {$this->get_db()->edd_customermeta}
			 WHERE meta_key = %s
			 LIMIT %d, %d",
			esc_sql( 'additional_email' ), $offset, $this->per_step
		) );

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$customer_id = absint( $result->edd_customer_id );

				edd_add_customer_email_address( array(
					'customer_id' => $customer_id,
					'email'       => $result->meta_value,
				) );
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

		// Return 100 as this migration is done in one step.
		return 100;
	}
}