<div class="postbox edd-export-api-requests">
	<h2 class="hndle"><span><?php esc_html_e( 'Export API Request Logs', 'easy-digital-downloads' ); ?></span></h2>
	<div class="inside">
		<p><?php esc_html_e( 'Download a CSV of API request logs.', 'easy-digital-downloads' ); ?></p>
		<form id="edd-export-api-requests" class="edd-export-form edd-import-export-form" method="post">
			<fieldset class="edd-from-to-wrapper">
				<legend class="screen-reader-text">
					<?php esc_html_e( 'Export API Request Log Dates', 'easy-digital-downloads' ); ?>
				</legend>
				<label for="edd-api-requests-export-start" class="screen-reader-text"><?php esc_html_e( 'Set start date', 'easy-digital-downloads' ); ?></label>
				<?php
				echo EDD()->html->date_field(
					array(
						'id'          => 'edd-api-requests-export-start',
						'class'       => 'edd-export-start',
						'name'        => 'api-requests-export-start',
						'placeholder' => _x( 'From', 'date filter', 'easy-digital-downloads' )
					)
				);
				?>
				<label for="edd-api-requests-export-end" class="screen-reader-text"><?php esc_html_e( 'Set end date', 'easy-digital-downloads' ); ?></label>
				<?php
				echo EDD()->html->date_field(
					array(
						'id'          => 'edd-api-requests-export-end',
						'class'       => 'edd-export-end',
						'name'        => 'api-requests-export-end',
						'placeholder' => _x( 'To', 'date filter', 'easy-digital-downloads' )
					)
				);
				?>
			</fieldset>
			<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
			<input type="hidden" name="edd-export-class" value="EDD_Batch_API_Requests_Export"/>
			<button type="submit" class="button button-secondary"><?php esc_html_e( 'Generate CSV', 'easy-digital-downloads' ); ?></button>
		</form>
	</div>
</div>
