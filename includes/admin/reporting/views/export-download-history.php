<div class="postbox edd-export-download-history">
	<h2 class="hndle"><span><?php esc_html_e( 'Export File Download Logs', 'easy-digital-downloads' ); ?></span></h2>
	<div class="inside">
		<p><?php esc_html_e( 'Download a CSV of file download logs.', 'easy-digital-downloads' ); ?></p>
		<form id="edd-export-download-history" class="edd-export-form edd-import-export-form" method="post">
			<label for="edd_file_download_export_download" class="screen-reader-text"><?php esc_html_e( 'Select Download', 'easy-digital-downloads' ); ?></label>
			<?php echo EDD()->html->product_dropdown(
				array(
					'name'        => 'download_id',
					'id'          => 'edd_file_download_export_download',
					'chosen'      => true,
					/* translators: the plural post type label */
					'placeholder' => sprintf( __( 'All %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
				)
			);
			?>
			<fieldset class="edd-from-to-wrapper">
				<legend class="screen-reader-text">
					<?php esc_html_e( 'Export File Download Log Dates', 'easy-digital-downloads' ); ?>
				</legend>
				<label for="edd-file-download-export-start" class="screen-reader-text"><?php esc_html_e( 'Set start date', 'easy-digital-downloads' ); ?></label>
				<?php
				echo EDD()->html->date_field(
					array(
						'id'          => 'edd-file-download-export-start',
						'class'       => 'edd-export-start',
						'name'        => 'file-download-export-start',
						'placeholder' => _x( 'From', 'date filter', 'easy-digital-downloads' )
					)
				);
				?>
				<label for="edd-file-download-export-end" class="screen-reader-text"><?php esc_html_e( 'Set end date', 'easy-digital-downloads' ); ?></label>
				<?php
				echo EDD()->html->date_field(
					array(
						'id'          => 'edd-file-download-export-end',
						'class'       => 'edd-export-end',
						'name'        => 'file-download-export-end',
						'placeholder' => _x( 'To', 'date filter', 'easy-digital-downloads' )
					)
				);
				?>
			</fieldset>
			<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
			<input type="hidden" name="edd-export-class" value="EDD_Batch_File_Downloads_Export"/>
			<button type="submit" class="button button-secondary"><?php esc_html_e( 'Generate CSV', 'easy-digital-downloads' ); ?></button>
		</form>
	</div>
</div>
