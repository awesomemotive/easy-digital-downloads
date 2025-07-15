<?php
/**
 * Base class for background upgrades.
 *
 * @package     EDD\Upgrades\Background
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.0
 */

namespace EDD\Upgrades\Background;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Cron\Events\SingleEvent;

/**
 * TaxRates upgrade class.
 *
 * @since 3.5.0
 */
class TaxRates extends Upgrade {

	/**
	 * Process the upgrade step.
	 *
	 * @since 3.5.0
	 * @return void
	 */
	public function process_step(): void {
		if ( ! $this->can_process_step() ) {
			return;
		}

		$items = $this->get_items();
		if ( empty( $items ) ) {
			$this->mark_complete();
			return;
		}

		if ( empty( $items ) ) {
			return;
		}

		foreach ( $items as $item ) {
			$item_data = array(
				'id'      => $item->id,
				'country' => $item->name,
				'state'   => $item->description,
				'amount'  => $item->amount,
				'scope'   => $item->scope,
				'status'  => $item->status,
			);

			$tax_query = new \EDD\Database\Queries\TaxRate();
			$new_id    = $tax_query->add_item( $item_data );
			if ( $new_id ) {
				edd_delete_adjustment( $item->id );
			}
		}

		$this->add_or_update_initial_notification();

		// Schedule the next step.
		SingleEvent::add(
			time() + MINUTE_IN_SECONDS,
			self::get_cron_action()
		);
	}

	/**
	 * Gets the name of the upgrade.
	 *
	 * @since 3.5.0
	 * @return string
	 */
	public static function get_upgrade_name(): string {
		return 'tax_rate_migration';
	}

	/**
	 * Get the items to process.
	 *
	 * @since 3.5.0
	 * @param bool $count Whether to return the count of items.
	 * @return array|bool
	 */
	public function get_items( $count = false ) {
		$args = array(
			'type'   => 'tax_rate',
			'number' => 50,
		);
		if ( $count ) {
			$args['count'] = true;
			unset( $args['number'] );
		}
		$query = new \EDD\Database\Queries\Adjustment( $args );
		if ( $count ) {
			return absint( $query->found_items );
		}

		return $query->query( $args );
	}

	/**
	 * Get the complete notification parameters.
	 *
	 * @since 3.5.0
	 * @return array
	 */
	public function get_complete_notification(): array {
		return array(
			'title'   => __( 'Tax Rate Migration Complete!', 'easy-digital-downloads' ),
			'content' => __( 'Easy Digital Downloads has finished updating your tax rates! Thank you for your patience.', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Get the in progress notification parameters.
	 *
	 * @since 3.5.0
	 * @return array
	 */
	public function get_in_progress_notification(): array {
		return array(
			/* translators: %d: Percentage complete */
			'title'   => __( 'Updating Tax Rates ( %d%% )', 'easy-digital-downloads' ),
			'content' => __( 'Easy Digital Downloads is updating your store\'s tax rates. We\'ll let you know when the process is complete.', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Get the CLI notification parameters.
	 *
	 * @since 3.5.0
	 * @return array
	 */
	public function get_cli_notification(): array {
		return array(
			'title'   => __( 'Database Update Required', 'easy-digital-downloads' ),
			'content' => sprintf(
				/* Translators: %s is the CLI command to run */
				__( 'Easy Digital Downloads needs to update tax rate information in the database, but due to the number of records, you should run this via CLI using <code>%s</code>.', 'easy-digital-downloads' ),
				'wp edd upgrade ' . self::$upgrade_name
			),
		);
	}
}
