<?php
/**
 * exporters.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.0
 */

add_action( 'edd_export_init', function ( \EDD\Admin\Export\ExportRegistry $registry ) {
	try {
		$registry->register_exporter( 'earnings_report', array(
			'label'       => __( 'Earnings Report', 'easy-digital-downloads' ),
			'description' => __( 'Download a CSV giving a detailed look into earnings over time.', 'easy-digital-downloads' ),
			'class'       => EDD_Batch_Earnings_Report_Export::class,
			'class_path'  => EDD_PLUGIN_DIR . 'includes/admin/reporting/export/class-batch-export-earnings-report.php',
		) );
	} catch ( \Exception $e ) {
		error_log( $e->getMessage() );
	}
} );

add_action( 'edd_export_form', function ( $exporter_id ) {
	if ( 'earnings_report' === $exporter_id ) {
		?>
		<fieldset class="edd-to-and-from-container">
			<legend class="screen-reader-text">
				<?php esc_html_e( 'Export Earnings Start', 'easy-digital-downloads' ); ?>
			</legend>
			<label for="edd_export_earnings_start_month" class="screen-reader-text"><?php esc_html_e( 'Select start month', 'easy-digital-downloads' ); ?></label>
			<?php echo EDD()->html->month_dropdown( 'start_month', 0, 'edd_export_earnings', true ); ?>
			<label for="edd_export_earnings_start_year" class="screen-reader-text"><?php esc_html_e( 'Select start year', 'easy-digital-downloads' ); ?></label>
			<?php echo EDD()->html->year_dropdown( 'start_year', 0, 5, 0, 'edd_export_earnings' ); ?>
		</fieldset>

		<span class="edd-to-and-from--separator"><?php echo _x( '&mdash; to &mdash;', 'Date one to date two', 'easy-digital-downloads' ); ?></span>

		<fieldset class="edd-to-and-from-container">
			<legend class="screen-reader-text">
				<?php esc_html_e( 'Export Earnings End', 'easy-digital-downloads' ); ?>
			</legend>
			<label for="edd_export_earnings_end_month" class="screen-reader-text"><?php esc_html_e( 'Select end month', 'easy-digital-downloads' ); ?></label>
			<?php echo EDD()->html->month_dropdown( 'end_month', 0, 'edd_export_earnings', true ); ?>
			<label for="edd_export_earnings_end_year" class="screen-reader-text"><?php esc_html_e( 'Select end year', 'easy-digital-downloads' ); ?></label>
			<?php echo EDD()->html->year_dropdown( 'end_year', 0, 5, 0, 'edd_export_earnings' ); ?>
		</fieldset>
		<?php
	}
} );
