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
