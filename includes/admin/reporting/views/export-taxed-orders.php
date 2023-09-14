<div class="postbox edd-export-taxed-orders">
	<h2 class="hndle"><span><?php esc_html_e( 'Export Taxed Orders', 'easy-digital-downloads' ); ?></span></h2>
	<div class="inside">
		<p><?php esc_html_e( 'Download a CSV of all orders, taxed by Country and/or Region.', 'easy-digital-downloads' ); ?></p>
		<form id="edd-export-taxed-orders" class="edd-export-form edd-import-export-form" method="post">
			<fieldset class="edd-from-to-wrapper">
				<legend class="screen-reader-text">
					<?php esc_html_e( 'Export Taxed Order Dates', 'easy-digital-downloads' ); ?>
				</legend>
				<label for="edd-taxed-orders-export-start" class="screen-reader-text"><?php esc_html_e( 'Set start date', 'easy-digital-downloads' ); ?></label>
				<?php
				echo EDD()->html->date_field(
					array(
						'id'          => 'edd-taxed-orders-export-start',
						'class'       => 'edd-export-start',
						'name'        => 'taxed-orders-export-start',
						'placeholder' => _x( 'From', 'date filter', 'easy-digital-downloads' )
					)
				);
				?>
				<label for="edd-taxed-orders-export-end" class="screen-reader-text"><?php esc_html_e( 'Set end date', 'easy-digital-downloads' ); ?></label>
				<?php
				echo EDD()->html->date_field(
					array(
						'id'          => 'edd-taxed-orders-export-end',
						'class'       => 'edd-export-end',
						'name'        => 'taxed-orders-export-end',
						'placeholder' => _x( 'To', 'date filter', 'easy-digital-downloads' )
					)
				);
				?>
			</fieldset>
			<label for="edd_taxed_orders_export_status" class="screen-reader-text"><?php esc_html_e( 'Select Status', 'easy-digital-downloads' ); ?></label>
			<?php
			echo EDD()->html->select(
				array(
					'id'               => 'edd_taxed_orders_export_status',
					'name'             => 'status',
					'show_option_all'  => __( 'All Statuses', 'easy-digital-downloads' ),
					'show_option_none' => false,
					'selected'         => false,
					'options'          => edd_get_payment_statuses(),
				)
			);
			?>
			<label for="edd_reports_filter_taxed_countries" class="screen-reader-text"><?php esc_html_e( 'Select Country', 'easy-digital-downloads' ); ?></label>
			<?php
			echo EDD()->html->country_select(
				array(
					'name'            => 'country',
					'id'              => 'edd_reports_filter_taxed_countries',
					'selected'        => false,
					'show_option_all' => false,
				)
			);
			?>
			<label for="edd_reports_filter_regions" class="screen-reader-text"><?php esc_html_e( 'Select Region', 'easy-digital-downloads' ); ?></label>
			<?php
			echo EDD()->html->region_select(
				array(
					'id'          => 'edd_reports_filter_regions',
					'placeholder' => __( 'All Regions', 'easy-digital-downloads' ),
				)
			);

			wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' );
			?>
			<input type="hidden" name="edd-export-class" value="EDD_Batch_Taxed_Orders_Export"/>
			<button type="submit" class="button button-secondary"><?php esc_html_e( 'Generate CSV', 'easy-digital-downloads' ); ?></button>
		</form>
	</div>
</div>
