<div class="postbox edd-export-downloads">
	<h2 class="hndle"><span><?php esc_html_e(
		/* translators: the singular post type label */
		sprintf( __( 'Export %s Products', 'easy-digital-downloads' ), edd_get_label_singular() ) ); ?></span></h2>
	<div class="inside">
		<p><?php esc_html_e(
			/* translators: the plural post type label */
			sprintf( __( 'Download a CSV of product %1$s.', 'easy-digital-downloads' ), edd_get_label_plural( true ) ) ); ?></p>
		<form id="edd-export-downloads" class="edd-export-form edd-import-export-form" method="post">
			<label for="edd_download_export_download" class="screen-reader-text"><?php esc_html_e( 'Select Download', 'easy-digital-downloads' ); ?></label>
			<?php echo EDD()->html->product_dropdown(
				array(
					'name'        => 'download_id',
					'id'          => 'edd_download_export_download',
					'chosen'      => true,
					/* translators: the plural post type label */
					'placeholder' => sprintf( __( 'All %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
				)
			);
			?>
			<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
			<input type="hidden" name="edd-export-class" value="EDD_Batch_Downloads_Export"/>
			<button type="submit" class="button button-secondary"><?php esc_html_e( 'Generate CSV', 'easy-digital-downloads' ); ?></button>
		</form>
	</div>
</div>
