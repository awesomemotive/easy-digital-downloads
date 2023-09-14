<div class="postbox edd-export-sales-earnings">
	<h2 class="hndle"><span><?php esc_html_e( 'Export Sales and Earnings', 'easy-digital-downloads' ); ?></span></h2>
	<div class="inside">
		<p><?php esc_html_e( 'Download a CSV of all sales or earnings on a day-by-day basis.', 'easy-digital-downloads' ); ?></p>
		<form id="edd-export-sales-earnings" class="edd-export-form edd-import-export-form" method="post">
			<fieldset class="edd-from-to-wrapper">
			<legend class="screen-reader-text">
				<?php esc_html_e( 'Export Sales and Earnings Dates', 'easy-digital-downloads' ); ?>
			</legend>
				<label for="edd-order-export-start" class="screen-reader-text"><?php esc_html_e( 'Set start date', 'easy-digital-downloads' ); ?></label>
				<?php
				echo EDD()->html->date_field(
					array(
						'id'          => 'edd-order-export-start',
						'class'       => 'edd-export-start',
						'name'        => 'order-export-start',
						'placeholder' => _x( 'From', 'date filter', 'easy-digital-downloads' ),
					)
				);
				?>
				<label for="edd-order-export-end" class="screen-reader-text"><?php esc_html_e( 'Set end date', 'easy-digital-downloads' ); ?></label>
				<?php
				echo EDD()->html->date_field(
					array(
						'id'          => 'edd-order-export-end',
						'class'       => 'edd-export-end',
						'name'        => 'order-export-end',
						'placeholder' => _x( 'To', 'date filter', 'easy-digital-downloads' ),
					)
				);

				?>
			</fieldset>
			<label for="edd_orders_export_download" class="screen-reader-text"><?php esc_html_e( 'Select Download', 'easy-digital-downloads' ); ?></label>
			<?php
			echo EDD()->html->product_dropdown(
				array(
					'name'        => 'download_id',
					'id'          => 'edd_orders_export_download',
					'chosen'      => true,
					/* translators: the plural post type label */
					'placeholder' => sprintf( __( 'All %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
				)
			);
			?>
			<label for="edd_order_export_customer" class="screen-reader-text"><?php esc_html_e( 'Select Customer', 'easy-digital-downloads' ); ?></label>
			<?php
			echo EDD()->html->customer_dropdown(
				array(
					'name'          => 'customer_id',
					'id'            => 'edd_order_export_customer',
					'chosen'        => true,
					'none_selected' => '',
					'placeholder'   => __( 'All Customers', 'easy-digital-downloads' ),
				)
			);

			wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>

			<input type="hidden" name="edd-export-class" value="EDD_Batch_Sales_And_Earnings_Export"/>
			<button type="submit" class="button button-secondary"><?php esc_html_e( 'Export', 'easy-digital-downloads' ); ?></button>
		</form>
	</div>
</div>
