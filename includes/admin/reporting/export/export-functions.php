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

	\EDD\Admin\Export\ExportLoader::bootstrap();

	try {
		if ( ! empty( $form['exporter_id'] ) ) {
			$exporter = \EDD\Admin\Export\ExportRegistry::instance()->get_item( $form['exporter_id'] );

			if ( ! empty( $exporter['class_path'] ) ) {
				require_once $exporter['class_path'];
			}

			$class = $exporter['class'];
		} else {
			$class = $form['edd-export-class'];

			_doing_it_wrong( __FUNCTION__, sprintf( 'Register your %s exporter using ExportRegistry.', $class ), '3.1' );

			do_action( 'edd_batch_export_class_include', $class );
		}

		if ( ! class_exists( $class ) ) {
			throw new \Exception( __( 'Exporter not available.', 'easy-digital-downloads' ) );
		}
	} catch ( \Exception $e ) {
		wp_send_json( array(
			'error'   => true,
			'message' => $e->getMessage(),
		), 500 );
	}

	$step  = absint( $_POST['step'] );

	/** @var \EDD_Batch_Export $export */
	$export = new $class( $step );

	if ( ! $export->can_export() ) {
		die( '-1' );
	}

	if ( ! $export->is_writable ) {
		wp_send_json( array(
			'error'   => true,
			'message' => __( 'Export location or file not writable', 'easy-digital-downloads' ),
		));
	}

	$export->set_properties( $_REQUEST );

	// Added in 2.5 to allow a bulk processor to pre-fetch some data to speed up the remaining steps and cache data.
	$export->pre_fetch();

	$ret = $export->process_step();

	$percentage = $export->get_percentage_complete();

	if ( $ret ) {
		$step++;

		wp_send_json( array(
			'step'       => $step,
			'percentage' => $percentage,
		) );
	} elseif ( true === $export->is_empty ) {
		wp_send_json( array(
			'error'   => true,
			'message' => __( 'No data found for export parameters', 'easy-digital-downloads' ),
		) );
	} elseif ( true === $export->done && true === $export->is_void ) {
		$message = ! empty( $export->message )
			? $export->message
			: __( 'Batch Processing Complete', 'easy-digital-downloads' );

		wp_send_json( array(
			'success' => true,
			'message' => $message,
			'data'    => $export->result_data,
		) );
	} else {
		$args = array_merge( $_REQUEST, array(
			'step'       => $step,
			'class'      => $class,
			'nonce'      => wp_create_nonce( 'edd-batch-export' ),
			'edd_action' => 'download_batch_export',
		) );

		$download_url = add_query_arg( $args, admin_url() );

		wp_send_json( array(
			'step' => 'done',
			'url'  => $download_url,
		) );
	}
}
add_action( 'wp_ajax_edd_do_ajax_export', 'edd_do_ajax_export' );
