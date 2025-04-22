<label for="edd_download_export_download" class="screen-reader-text"><?php esc_html_e( 'Select Download', 'easy-digital-downloads' ); ?></label>
<?php
echo EDD()->html->product_dropdown(
	array(
		'name'        => 'download_id',
		'id'          => 'edd_download_export_download',
		'chosen'      => true,
		/* translators: %s: Download plural label */
		'placeholder' => sprintf( __( 'All %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
	)
);
