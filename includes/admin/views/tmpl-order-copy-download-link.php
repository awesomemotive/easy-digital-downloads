<?php
/**
 * Order Overview: Copy Download Links
 *
 * @package     EDD
 * @subpackage  Admin/Views
 * @copyright   Copyright (c) 2020, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
?>

<div class="edd-order-overview-modal">
	<form class="edd-order-copy-download-link">

		<p>
			<label for="link">
				<?php echo esc_html( sprintf( __( '%s Links', 'easy-digital-downloads' ), edd_get_label_singular() ) ); ?>
			</label>

			<# if ( false === data.link ) { #>
				<span class="spinner is-active" style="float: none; margin: 0;"></span>
			<# } else if ( '' === data.link ) { #>
				<?php esc_html_e( 'No file links available', 'easy-digital-downloads' ); ?>
			<# } else { #>
				<textarea rows="10" id="link">{{ data.link }}</textarea>
			<# } #>
		</p>

		<p class="submit">
			<input
				id="close"
				type="submit"
				class="button button-primary edd-ml-auto"
				value="<?php esc_html_e( 'Close', 'easy-digital-downloads' ); ?>"
			/>
		</p>
	</form>
</div>
