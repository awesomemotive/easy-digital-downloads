<?php
/**
 * 3.0 Data Migration - Order Notes.
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
 * Order_Notes Class.
 *
 * @since 3.0
 */
class Order_Notes extends Base {

	/**
	 * Constructor.
	 *
	 * @param int $step Step.
	 */
	public function __construct( $step = 1 ) {
		parent::__construct( $step );

		$this->completed_message = __( 'Order notes migration completed successfully.', 'easy-digital-downloads' );
		$this->upgrade           = 'migrate_order_notes';
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
			 FROM {$this->get_db()->comments}
			 WHERE comment_type = %s
			 ORDER BY comment_id ASC
			 LIMIT %d, %d",
			esc_sql( 'edd_payment_note' ), $offset, $this->per_step
		) );

		if ( ! empty( $results ) ) {
			foreach ( $results as $old_note ) {
				$note_data = array(
					'object_id'     => $this->remap_id( $old_note->comment_post_ID, 'orders' ),
					'object_type'   => 'order',
					'date_created'  => $old_note->comment_date_gmt,
					'date_modified' => $old_note->comment_date_gmt,
					'content'       => $old_note->comment_content,
					'user_id'       => $old_note->user_id,
				);

				$id = edd_add_note( $note_data );

				$meta = get_comment_meta( $old_note->comment_ID );
				if ( ! empty( $meta ) ) {
					foreach ( $meta as $key => $value ) {
						edd_add_note_meta( $id, $key, $value );
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
		$total = $this->get_db()->get_var( $this->get_db()->prepare( "SELECT COUNT(comment_ID) AS count FROM {$this->get_db()->comments} WHERE comment_type = %s", esc_sql( 'edd_payment_note' ) ) );

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