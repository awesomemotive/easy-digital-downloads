<div class="edd-editor__status">
	<?php
	$status_tooltip = $email->get_status_tooltip();
	if ( ! empty( $status_tooltip ) ) {
		$tooltip = new EDD\HTML\Tooltip( $status_tooltip );
		$tooltip->output();
	}
	?>
	<div class="edd-form-group edd-toggle">
		<div class="edd-form-group__control">
			<input type="hidden" name="status" value="0">
			<input
				type="checkbox"
				id="edd-email-status"
				name="status"
				value="1"
				<?php checked( $email->status ); ?>
				<?php disabled( ! $email->can_edit( 'status' ) ); ?>
			>
			<label for="edd-email-status">
				<?php
				if ( $email->status ) {
					esc_html_e( 'Enabled', 'easy-digital-downloads' );
				} else {
					esc_html_e( 'Disabled', 'easy-digital-downloads' );
				}
				?>
			</label>
		</div>
	</div>
</div>
