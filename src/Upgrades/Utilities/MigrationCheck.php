<?php
/**
 * Migration Check.
 *
 * @package EDD
 * @subpackage Upgrades
 * @since 3.2.2
 */
namespace EDD\Upgrades\Utilities;

defined( 'ABSPATH' ) || exit;

/**
 * Migration Check Class.
 *
 * @since 3.2.2
 */
class MigrationCheck {

	/**
	 * Whether the base EDD v3.0 migration has completed.
	 * This does not include the legacy data removal check.
	 *
	 * @since 3.2.2
	 * @var   bool
	 */
	public static function is_v30_migration_complete() {
		$upgrades = edd_get_v30_upgrades();
		unset( $upgrades['v30_legacy_data_removed'] );
		$upgrades = array_keys( $upgrades );
		foreach ( $upgrades as $upgrade ) {
			// If any migration has not completed, return false.
			if ( ! edd_has_upgrade_completed( $upgrade ) ) {
				return false;
			}
		}

		return true;
	}
}
