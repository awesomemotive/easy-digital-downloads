<div class="postbox edd-export-payment-history">
	<h2 class="hndle"><?php esc_html_e( 'Export Product Sales', 'easy-digital-downloads' ); ?></h2>
	<div class="inside">
		<p><?php esc_html_e( 'Download a CSV file containing a record of each sale of a product along with the customer information.', 'easy-digital-downloads' ); ?></p>
		<form id="edd-export-sales" class="edd-export-form edd-import-export-form" method="post">
			<fieldset class="edd-from-to-wrapper">
				<legend class="screen-reader-text">
					<?php esc_html_e( 'Export Sales Dates', 'easy-digital-downloads' ); ?>
				</legend>
				<label for="edd-sales-export-start" class="screen-reader-text"><?php esc_html_e( 'Set start date', 'easy-digital-downloads' ); ?></label>
				<?php
				echo EDD()->html->date_field(
					array(
						'id'          => 'edd-sales-export-start',
						'class'       => 'edd-sales-export-start',
						'name'        => 'sales-export-start',
						'placeholder' => _x( 'From', 'date filter', 'easy-digital-downloads' ),
					)
				);
				?>
				<label for="edd-sales-export-end" class="screen-reader-text"><?php esc_html_e( 'Set end date', 'easy-digital-downloads' ); ?></label>
				<?php
				echo EDD()->html->date_field(
					array(
						'id'          => 'edd-sales-export-end',
						'class'       => 'edd-export-end',
						'name'        => 'sales-export-end',
						'placeholder' => _x( 'To', 'date filter', 'easy-digital-downloads' ),
					)
				);

				?>
			</fieldset>
			<label for="edd_sales_export_download" class="screen-reader-text"><?php esc_html_e( 'Select Download', 'easy-digital-downloads' ); ?></label>
			<?php
			echo EDD()->html->product_dropdown(
				array(
					'name'   => 'download_id',
					'id'     => 'edd_sales_export_download',
					'chosen' => true,
					)
				);
			?>
			<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
			<input type="hidden" name="edd-export-class" value="EDD_Batch_Sales_Export"/>
			<button type="submit" class="button button-secondary"><?php esc_html_e( 'Generate CSV', 'easy-digital-downloads' ); ?></button>
		</form>
	</div>
</div>
