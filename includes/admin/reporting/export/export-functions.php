<?php
/**
 * Exports Functions
 *
 * These are functions are used for exporting data from Easy Digital Downloads.
 *
 * @package     EDD
 * @subpackage  Admin/Export
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/class-export.php';
require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/export/export-actions.php';

/**
 * Process batch exports via AJAX.
 *
 * @since 2.4
 */
function edd_do_ajax_export() {
	require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/export/class-batch-export.php';

	parse_str( $_POST['form'], $form ); // WPCS: CSRF ok.

	$_REQUEST = $form;
	$form     = (array) $form;

	if ( ! wp_verify_nonce( $_REQUEST['edd_ajax_export'], 'edd_ajax_export' ) ) {
		die( '-2' );
	}

	do_action( 'edd_batch_export_class_include', $form['edd-export-class'] );

	$step  = absint( $_POST['step'] );
	$class = sanitize_text_field( $form['edd-export-class'] );

	/** @var \EDD_Batch_Export $export */
	$export = new $class( $step );

	if ( ! $export->can_export() ) {
		die( '-1' );
	}

	if ( ! $export->is_writable ) {
		echo wp_json_encode( array(
			'error'   => true,
			'message' => __( 'Export location or file not writable', 'easy-digital-downloads' ),
		));

		exit;
	}

	$export->set_properties( $_REQUEST );

	// Added in 2.5 to allow a bulk processor to pre-fetch some data to speed up the remaining steps and cache data.
	$export->pre_fetch();

	$ret = $export->process_step();

	$percentage = $export->get_percentage_complete();

	if ( $ret ) {
		$step++;

		echo wp_json_encode( array(
			'step'       => absint( $step ),
			'percentage' => esc_attr( $percentage ),
		) );

		exit;
	} elseif ( true === $export->is_empty ) {
		echo wp_json_encode( array(
			'error'   => true,
			'message' => __( 'No data found for export parameters', 'easy-digital-downloads' ),
		) );

		exit;
	} elseif ( true === $export->done && true === $export->is_void ) {
		$message = ! empty( $export->message )
			? $export->message
			: __( 'Batch Processing Complete', 'easy-digital-downloads' );

		echo wp_json_encode( array(
			'success' => true,
			'message' => $message,
			'data'    => $export->result_data,
		) );

		exit;
	} else {
		$args = array_merge( $_REQUEST, array(
			'step'       => absint( $step ),
			'class'      => urlencode( $class ),
			'nonce'      => wp_create_nonce( 'edd-batch-export' ),
			'edd_action' => 'download_batch_export',
		) );

		$download_url = add_query_arg( $args, admin_url() );

		echo wp_json_encode( array(
			'step' => 'done',
			'url'  => esc_url_raw( $download_url ),
		) );

		exit;
	}
}
add_action( 'wp_ajax_edd_do_ajax_export', 'edd_do_ajax_export' );
