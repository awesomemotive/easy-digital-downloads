<?php
/**
 * v3 Upgrade Actions
 *
 * @package     EDD
 * @subpackage  Admin/Upgrades/v3
 * @copyright   Copyright (c) 2020, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Handles the 3.0 upgrade process.
 *
 * @since 3.0
 * @return void
 */
function edd_process_v3_upgrade() {
	require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/export/class-batch-export.php';

	check_ajax_referer( 'edd_process_v3_upgrade' );

	$all_upgrades = edd_get_v30_upgrades();

	$upgrade_key = ! empty( $_POST['upgrade_key'] ) && array_key_exists( $_POST['upgrade_key'], $all_upgrades )
		? $_POST['upgrade_key']
		: reset( $all_upgrades ); // First item in list.

	$step       = ! empty( $_POST['step'] ) ? absint( $_POST['step'] ) : 1;
	$class_name = $all_upgrades[ $upgrade_key ]['class'];

	// Load the required classes.
	require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/export/class-batch-export.php';
	do_action( 'edd_batch_export_class_include', $class_name );

	/** @var \EDD_Batch_Export $export */
	$export = new $class_name( $step );

	if ( ! $export->can_export() ) {
		wp_die( -1, 403, array( 'response' => 403 ) );
	}

	$was_processed       = $export->process_step();
	$percentage_complete = $export->get_percentage_complete();

	if ( $was_processed ) {
		wp_send_json_success( array(
			'upgrade_completed' => false,
			'next_step'         => $step + 1,
			'next_upgrade'      => $upgrade_key,
			'percentage'        => $percentage_complete
		) );
	} else {
		// Figure out which upgrades are still left to do.
		$upgrade_keys       = array_keys( $all_upgrades );
		$remaining_upgrades = array_slice( $upgrade_keys, array_search( $upgrade_key, $upgrade_keys ) + 1 );

		// Remove `v30_legacy_data_removed` because this is handled separately.
		unset( $remaining_upgrades['v30_legacy_data_removed'] );

		$next_upgrade = reset( $remaining_upgrades );

		wp_send_json_success( array(
			'upgrade_completed' => true,
			'next_step'         => 1,
			'next_upgrade'      => $next_upgrade,
			'percentage'        => 0
		) );
	}

}

add_action( 'wp_ajax_edd_process_v3_upgrade', 'edd_process_v3_upgrade' );
