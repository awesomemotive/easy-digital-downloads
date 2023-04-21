<div class="postbox edd-export-taxed-customers">
	<h2 class="hndle"><span><?php esc_html_e( 'Export Taxed Customers', 'easy-digital-downloads' ); ?></span></h2>
	<div class="inside">
		<p><?php esc_html_e( 'Download a CSV of all customers that were taxed.', 'easy-digital-downloads' ); ?></p>
		<form id="edd-export-taxed-customers" class="edd-export-form edd-import-export-form" method="post">
			<fieldset class="edd-from-to-wrapper">
				<legend class="screen-reader-text">
					<?php esc_html_e( 'Export Taxed Customer Dates', 'easy-digital-downloads' ); ?></legend>
				<label for="edd-taxed-customers-export-start" class="screen-reader-text"><?php esc_html_e( 'Set start date', 'easy-digital-downloads' ); ?></label>
				<?php
					echo EDD()->html->date_field(
						array(
							'id'          => 'edd-taxed-customers-export-start',
							'class'       => 'edd-export-start',
							'name'        => 'taxed-customers-export-start',
							'placeholder' => _x( 'From', 'date filter', 'easy-digital-downloads' )
						)
					);
				?>
				<label for="edd-taxed-customers-export-end" class="screen-reader-text"><?php esc_html_e( 'Set end date', 'easy-digital-downloads' ); ?></label>
				<?php
				echo EDD()->html->date_field(
					array(
						'id'          => 'edd-taxed-customers-export-end',
						'class'       => 'edd-export-end',
						'name'        => 'taxed-customers-export-end',
						'placeholder' => _x( 'To', 'date filter', 'easy-digital-downloads' )
					)
				);
				?>
			</fieldset>
			<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
			<input type="hidden" name="edd-export-class" value="EDD_Batch_Taxed_Customers_Export"/>
			<button type="submit" class="button button-secondary"><?php esc_html_e( 'Generate CSV', 'easy-digital-downloads' ); ?></button>
		</form>
	</div>
</div>
