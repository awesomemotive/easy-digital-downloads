<?php
/**
 * 3.0 Data Migration - Customer Email Addresses.
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
 * Customer_Email_Addresses Class.
 *
 * @since 3.0
 */
class Customer_Email_Addresses extends Base {

	/**
	 * Constructor.
	 *
	 * @param int $step Step.
	 */
	public function __construct( $step = 1 ) {
		parent::__construct( $step );

		$this->completed_message = __( 'Customer email addresses migration completed successfully.', 'easy-digital-downloads' );
		$this->upgrade           = 'migrate_customer_email_addresses';
	}

	/**
	 * Retrieve the data pertaining to the current step and migrate as necessary.
	 *
	 * @since 3.0
	 *
	 * @return bool True if data was migrated, false otherwise.
	 */
	public function get_data() {
		$success = false;
		$offset  = ( $this->step - 1 ) * $this->per_step;

		$results = $this->get_db()->get_results(
			$this->get_db()->prepare(
				"SELECT *
			 FROM {$this->get_db()->edd_customermeta}
			 WHERE meta_key = %s
			 LIMIT %d, %d",
				esc_sql( 'additional_email' ),
				$offset,
				$this->per_step
			)
		);

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				// Check if email has already been migrated.
				if ( ! empty( $result->edd_customer_id ) && $result->meta_value ) {
					$number_results = edd_count_customer_email_addresses(
						array(
							'customer_id' => $result->edd_customer_id,
							'email'       => $result->meta_value,
						)
					);
					if ( $number_results > 0 ) {
						continue;
					}
				}

				Data_Migrator::customer_email_addresses( $result );
			}

			$success = true;
		}

		// Query customers without email address objects.
		$customers_without_emails = $this->get_db()->get_results(
			$this->get_db()->prepare(
				"SELECT *
				FROM {$this->get_db()->edd_customers}
				WHERE email != ''
				AND email NOT IN (
					SELECT email
					FROM {$this->get_db()->edd_customer_email_addresses}
				)
				LIMIT %d",
				$this->per_step
			)
		);

		if ( $customers_without_emails ) {
			foreach ( $customers_without_emails as $customer ) {
				$customer_has_primary = edd_count_customer_email_addresses(
					array(
						'customer_id' => $customer->id,
						'type'        => 'primary',
					)
				);
				edd_add_customer_email_address(
					array(
						'customer_id'  => $customer->id,
						'email'        => $customer->email,
						'date_created' => $customer->date_created,
						'type'         => $customer_has_primary ? 'secondary' : 'primary',
					)
				);
			}

			$success = true;
		}

		return $success;
	}

	/**
	 * Calculate the percentage completed.
	 *
	 * @since 3.0
	 *
	 * @return float Percentage.
	 */
	public function get_percentage_complete() {
		$total = $this->get_db()->get_var( $this->get_db()->prepare( "SELECT COUNT(meta_id) AS count FROM {$this->get_db()->edd_customermeta} WHERE meta_key = %s", esc_sql( 'additional_email' ) ) );

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
