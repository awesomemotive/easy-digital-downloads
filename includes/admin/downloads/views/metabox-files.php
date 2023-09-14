<?php
/**
 * Download Files Metabox
 *
 * @var int $post_id
 */
$files = edd_get_download_files( $post_id );
?>

<div id="edd_download_files">
	<div id="edd_file_fields_default" class="edd_meta_table_wrap">
		<div class="widefat edd_repeatable_table">

			<div class="edd-file-fields edd-repeatables-wrap">
				<?php

				if ( ! empty( $files ) && is_array( $files ) ) :
					foreach ( $files as $key => $value ) :
						$index          = isset( $value['index'] ) ? $value['index'] : $key;
						$name           = isset( $value['name'] ) ? $value['name'] : '';
						$file           = isset( $value['file'] ) ? $value['file'] : '';
						$condition      = isset( $value['condition'] ) ? $value['condition'] : false;
						$thumbnail_size = isset( $value['thumbnail_size'] ) ? $value['thumbnail_size'] : '';
						$attachment_id  = isset( $value['attachment_id'] ) ? absint( $value['attachment_id'] ) : false;

						$args = apply_filters( 'edd_file_row_args', compact( 'name', 'file', 'condition', 'attachment_id', 'thumbnail_size' ), $value ); ?>

						<div class="edd_repeatable_upload_wrapper edd_repeatable_row" data-key="<?php echo esc_attr( $key ); ?>">
							<?php do_action( 'edd_render_file_row', $key, $args, $post_id, $index ); ?>
						</div>

						<?php
					endforeach;
				else : ?>

					<div class="edd_repeatable_upload_wrapper edd_repeatable_row">
						<?php do_action( 'edd_render_file_row', 1, array(), $post_id, 0 ); ?>
					</div>

				<?php endif; ?>

			</div>

			<div class="edd-add-repeatable-row">
				<button class="button-secondary edd_add_repeatable"><?php esc_html_e( 'Add New File', 'easy-digital-downloads' ); ?></button>
			</div>
		</div>
	</div>
</div>
