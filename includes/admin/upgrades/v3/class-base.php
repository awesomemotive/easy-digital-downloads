<?php
/**
 * 3.0 Data Migration - Base.
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
 * Base Class.
 *
 * @since 3.0
 */
class Base extends \EDD_Batch_Export {

	/**
	 * Orders.
	 *
	 * @since 3.0
	 * @var   string
	 */
	const ORDERS = 'orders';

	/**
	 * Discounts.
	 *
	 * @since 3.0
	 * @var   string
	 */
	const DISCOUNTS = 'discounts';

	/**
	 * Our export type. Used for export-type specific filters/actions.
	 *
	 * @since 3.0
	 * @var   string
	 */
	public $export_type = '';

	/**
	 * Allows for a non-download batch processing to be run.
	 *
	 * @since 3.0
	 * @var   bool
	 */
	public $is_void = true;

	/**
	 * Sets the number of items to pull on each step.
	 *
	 * @since 3.0
	 * @var   int
	 */
	public $per_step = 50;

	/**
	 * Is the upgrade done?
	 *
	 * @since 3.0
	 * @var   bool
	 */
	public $done;

	/**
	 * Message.
	 *
	 * @since 3.0
	 * @var   string
	 */
	public $message;

	/**
	 * Completed message.
	 *
	 * @since 3.0
	 * @var   string
	 */
	public $completed_message;

	/**
	 * Upgrade routine.
	 *
	 * @since 3.0
	 * @var   string
	 */
	public $upgrade;

	/**
	 * Retrieve the data pertaining to the current step and migrate as necessary.
	 *
	 * @since 3.0
	 *
	 * @return bool True if data was migrated, false otherwise.
	 */
	public function get_data() {
		return false;
	}

	/**
	 * Process a step.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function process_step() {
		if ( ! $this->can_export() ) {
			wp_die(
				esc_html__( 'You do not have permission to run this upgrade.', 'easy-digital-downloads' ),
				esc_html__( 'Error', 'easy-digital-downloads' ),
				array(
					'response' => 403,
				)
			);
		}

		$had_data = $this->get_data();

		if ( $had_data ) {
			$this->done = false;
			return true;
		} else {
			$this->done    = true;
			$this->message = $this->completed_message;
			edd_set_upgrade_complete( $this->upgrade );
			return false;
		}
	}

	/**
	 * Set the headers.
	 *
	 * @since 3.0
	 */
	public function headers() {
		edd_set_time_limit();
	}

	/**
	 * Perform the migration.
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	public function export() {

		// Set headers.
		$this->headers();

		edd_die();
	}

	/**
	 * Fetch a legacy ID when migrating.
	 *
	 * @since 3.0
	 *
	 * @param int    $legacy_id Legacy ID.
	 * @param string $type      Type.
	 *
	 * @return int|false New ID if found, false otherwise.
	 */
	protected function find_legacy_id( $legacy_id = 0, $type = '' ) {

		// Bail if nothing passed.
		if ( empty( $legacy_id ) || empty( $type ) ) {
			return $legacy_id;
		}

		$legacy_id = absint( $legacy_id );

		switch ( $type ) {
			case static::ORDERS:
				$id = $this->get_db()->get_var(
					$this->get_db()->prepare(
						"SELECT edd_order_id FROM {$this->get_db()->edd_ordermeta} WHERE meta_key = 'legacy_order_id' AND meta_value = %d",
						$legacy_id
					)
				);
				break;
			case static::DISCOUNTS:
				$id = $this->get_db()->get_var(
					$this->get_db()->prepare(
						"SELECT edd_discount_id FROM {$this->get_db()->edd_discountmeta} WHERE meta_key = 'legacy_discount_id' AND meta_value = %d",
						$legacy_id
					)
				);
				break;
		}

		return ! empty( $id )
			? absint( $id )
			: false;
	}

	/**
	 * Return the global database interface.
	 *
	 * @since 3.0
	 * @access protected
	 * @static
	 *
	 * @return \wpdb|\stdClass
	 */
	protected static function get_db() {
		return isset( $GLOBALS['wpdb'] )
			? $GLOBALS['wpdb']
			: new \stdClass();
	}

	/**
	 * Set properties specific to the export.
	 *
	 * @since 3.0
	 *
	 * @param array $request Form data passed into the batch processor.
	 */
	public function set_properties( $request ) {}

	/**
	 * Allow for pre-fetching of data for the remainder of the batch processor.
	 *
	 * @since 3.0
	 */
	public function pre_fetch() {}
}
