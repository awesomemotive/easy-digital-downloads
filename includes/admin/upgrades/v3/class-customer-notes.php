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

use Carbon\Carbon;

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
	 * @since 3.0
	 *
	 * @return bool True if data was migrated, false otherwise.
	 */
	public function get_data() {
		$offset = ( $this->step - 1 ) * $this->per_step;

		$results = $this->get_db()->get_results( $this->get_db()->prepare(
			"SELECT *
			 FROM {$this->get_db()->edd_customers}
			 LIMIT %d, %d",
			$offset, $this->per_step
		) );

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$customer_id = absint( $result->id );

				if ( property_exists( $result, 'notes' ) && ! empty( $result->notes ) ) {
					$notes = array_reverse( array_filter( explode( "\n\n", $result->notes ) ) );

					$notes = array_map( function( $val ) {
						return explode( ' - ', $val );
					}, $notes );

					if ( ! empty( $notes ) ) {
						foreach ( $notes as $note ) {
							$date = isset( $note[0] )
								? Carbon::parse( $note[0], edd_get_timezone_id() )->setTimezone( 'UTC' )->toDateTimeString()
								: '';

							$note_content = isset( $note[1] )
								? $note[1]
								: '';

							edd_add_note( array(
								'user_id'       => 0,
								'object_id'     => $customer_id,
								'object_type'   => 'customer',
								'content'       => $note_content,
								'date_created'  => $date,
								'date_modified' => $date,
							) );
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
		$total = $this->get_db()->get_var( "SELECT COUNT(id) AS count FROM {$this->get_db()->edd_customers}" );

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