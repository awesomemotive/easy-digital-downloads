<?php
/**
 * 3.0 Data Migration - Tax Rates.
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
 * Tax_Rates Class.
 *
 * @since 3.0
 */
class Tax_Rates extends Base {

	/**
	 * Constructor.
	 *
	 * @param int $step Step.
	 */
	public function __construct( $step = 1 ) {
		parent::__construct( $step );

		$this->completed_message = __( 'Tax rates migration completed successfully.', 'easy-digital-downloads' );
		$this->upgrade           = 'migrate_tax_rates';
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

		if ( 1 === $this->step ) {
			$default_tax_rate = edd_get_option( 'tax_rate', false );
			if ( ! empty( $default_tax_rate ) ) {
				edd_add_tax_rate(
					array(
						'scope'  => 'global',
						'amount' => floatval( $default_tax_rate ),
					)
				);
			}
		}

		$results = get_option( 'edd_tax_rates', array() );
		$results = array_slice( $results, $offset, $this->per_step, true );

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				Data_Migrator::tax_rates( $result );
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
		$total = count( get_option( 'edd_tax_rates', array() ) );

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
