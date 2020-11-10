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
	 * Sets the number of items to pull on each step.
	 *
	 * This is 50 in base, but we're cutting it in half here because we delete 25 from posts and 25 from comments
	 * on each step. Together combined that's 50.
	 *
	 * @since 3.0
	 * @var   int
	 */
	public $per_step = 25;

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
		// Delete meta on first step.
		if ( 1 === $this->step ) {
			$this->get_db()->query( $this->get_db()->prepare( "DELETE FROM {$this->get_db()->edd_customermeta} WHERE meta_key = %s", esc_sql( 'additional_email' ) ) );
			$this->get_db()->query( $this->get_db()->prepare( "DELETE FROM {$this->get_db()->usermeta} WHERE meta_key = %s", esc_sql( '_edd_user_address' ) ) );
		}

		// First delete custom post types.
		$results = $this->get_db()->get_col( $this->get_db()->prepare(
			"SELECT id
			 FROM {$this->get_db()->posts}
			 WHERE post_type IN(%s, %s, %s)
			 ORDER BY id ASC
			 LIMIT %d",
			esc_sql( 'edd_payment' ), esc_sql( 'edd_discount' ), esc_sql( 'edd_log' ), $this->per_step
		), 0 );

		$data_was_deleted = false;

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				wp_delete_post( $result, true );
			}

			$data_was_deleted = true;
		}

		// Then delete order notes, stored in comments.
		$results = $this->get_db()->get_col( $this->get_db()->prepare(
			"SELECT comment_ID
			FROM {$this->get_db()->comments}
			WHERE comment_type = %s
			ORDER BY comment_ID ASC
			LIMIT %d",
			'edd_payment_note', $this->per_step
		) );
		if ( ! empty( $results ) ) {
			foreach( $results as $result ) {
				wp_delete_comment( $result, true );
			}

			$data_was_deleted = true;
		}

		return $data_was_deleted;
	}

	/**
	 * Calculate the percentage completed.
	 *
	 * @since 3.0
	 *
	 * @return float Percentage.
	 */
	public function get_percentage_complete() {
		// Get post type total.
		$total = $this->get_db()->get_var( $this->get_db()->prepare(
			"SELECT COUNT(id) AS count
			 FROM {$this->get_db()->posts}
			 WHERE post_type IN(%s, %s, %s)",
			esc_sql( 'edd_payment' ), esc_sql( 'edd_discount' ), esc_sql( 'edd_log' )
		) );

		if ( empty( $total ) ) {
			$total = 0;
		}

		// Get order note total.
		$order_note_total = $this->get_db()->get_var( $this->get_db()->prepare(
			"SELECT COUNT(comment_ID) AS count
			FROM {$this->get_db()->comments}
			WHERE comment_type = %s",
			'edd_payment_note'
		) );

		if ( empty( $order_note_total ) ) {
			$order_note_total = 0;
		}

		// Combine the two.
		$total += $order_note_total;

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
