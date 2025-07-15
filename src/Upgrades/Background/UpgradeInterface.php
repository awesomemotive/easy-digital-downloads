<?php
/**
 * Interface for background upgrades.
 *
 * @package     EDD\Upgrades\Background
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.0
 */

namespace EDD\Upgrades\Background;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

interface UpgradeInterface {

	/**
	 * Gets the name of the upgrade.
	 *
	 * @since 3.5.0
	 * @return string
	 */
	public static function get_upgrade_name(): string;

	/**
	 * Gets the items to process.
	 *
	 * @since 3.5.0
	 * @param bool $count If we should return the count of items.
	 * @return array|int|false
	 */
	public function get_items( $count = false );

	/**
	 * Processes the upgrade step.
	 *
	 * @since 3.5.0
	 * @return void
	 */
	public function process_step(): void;

	/**
	 * Gets the array of parameters for the complete notification.
	 *
	 * @since 3.5.0
	 * @return array
	 */
	public function get_complete_notification(): array;

	/**
	 * Gets the array of parameters for the in progress notification.
	 *
	 * @since 3.5.0
	 * @return array
	 */
	public function get_in_progress_notification(): array;

	/**
	 * Gets the array of parameters for the in progress notification.
	 *
	 * @since 3.5.0
	 * @return array
	 */
	public function get_cli_notification(): array;
}
