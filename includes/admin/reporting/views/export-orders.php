<div class="postbox edd-export-orders">
	<h2 class="hndle"><span><?php esc_html_e( 'Export Orders', 'easy-digital-downloads' ); ?></span></h2>
	<div class="inside">
		<p><?php esc_html_e( 'Download a CSV of all orders.', 'easy-digital-downloads' ); ?></p>
		<form id="edd-export-orders" class="edd-export-form edd-import-export-form" method="post">
			<fieldset class="edd-from-to-wrapper">
			<legend class="screen-reader-text">
				<?php esc_html_e( 'Export Order Dates', 'easy-digital-downloads' ); ?>
			</legend>
				<label for="edd-orders-export-start" class="screen-reader-text"><?php esc_html_e( 'Set start date', 'easy-digital-downloads' ); ?></label>
					<?php
					echo EDD()->html->date_field(
						array(
							'id'          => 'edd-orders-export-start',
							'class'       => 'edd-export-start',
							'name'        => 'orders-export-start',
							'placeholder' => _x( 'From', 'date filter', 'easy-digital-downloads' ),
						)
					);
					?>
				<label for="edd-orders-export-end" class="screen-reader-text"><?php esc_html_e( 'Set end date', 'easy-digital-downloads' ); ?></label>
					<?php
					echo EDD()->html->date_field(
						array(
							'id'          => 'edd-orders-export-end',
							'class'       => 'edd-export-end',
							'name'        => 'orders-export-end',
							'placeholder' => _x( 'To', 'date filter', 'easy-digital-downloads' ),
						)
					);
					?>
			</fieldset>
			<label for="edd_orders_export_status" class="screen-reader-text"><?php esc_html_e( 'Select Status', 'easy-digital-downloads' ); ?></label>
				<?php
					echo EDD()->html->select(
						array(
							'id'               => 'edd_orders_export_status',
							'name'             => 'status',
							'show_option_all'  => __( 'All Statuses', 'easy-digital-downloads' ),
							'show_option_none' => false,
							'selected'         => false,
							'options'          => edd_get_payment_statuses(),
						)
					);

				wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' );
				?>
			<input type="hidden" name="edd-export-class" value="EDD_Batch_Payments_Export"/>
			<button type="submit" class="button button-secondary"><?php esc_html_e( 'Generate CSV', 'easy-digital-downloads' ); ?></button>
		</form>
	</div>
</div>
