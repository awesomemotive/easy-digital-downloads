<?php
if ( function_exists( 'EDD_CFM' ) ) {
	return;
}
?>
<fieldset id="edd_checkout_user_info" class="edd-blocks-form">
	<legend><?php esc_html_e( 'Personal Info', 'easy-digital-downloads' ); ?></legend>
	<?php if ( empty( $customer['email'] ) ) : ?>
		<div id="edd-email-wrap">
			<label class="edd-label" for="edd-email">
				<?php
				esc_html_e( 'Email address', 'easy-digital-downloads' );
				echo EDD()->html->show_required();
				?>
			</label>
			<input class="edd-input required" type="email" name="edd_email" placeholder="<?php esc_html_e( 'Email address', 'easy-digital-downloads' ); ?>" id="edd-email" value="<?php echo esc_attr( $customer['email'] ); ?>" aria-describedby="edd-email-description" maxlength="100" required/>
			<p class="edd-description" id="edd-email-description"><?php esc_html_e( 'We will send the purchase receipt to this address.', 'easy-digital-downloads' ); ?></p>
		</div>
	<?php endif; ?>
	<?php if ( empty( $customer['first_name'] ) ) : ?>
		<div id="edd-first-name-wrap">
			<label class="edd-label" for="edd-first">
				<?php
				esc_html_e( 'First name', 'easy-digital-downloads' );
				if ( edd_field_is_required( 'edd_first' ) ) {
					echo EDD()->html->show_required();
				}
				?>
			</label>
			<input class="edd-input required" type="text" name="edd_first" placeholder="<?php esc_html_e( 'First name', 'easy-digital-downloads' ); ?>" id="edd-first" value="<?php echo esc_attr( $customer['first_name'] ); ?>" aria-describedby="edd-first-description" <?php EDD\Blocks\Functions\mark_field_required( 'edd_first' ); ?>/>
			<p class="edd-description" id="edd-first-description"><?php esc_html_e( 'We will use this to personalize your account experience.', 'easy-digital-downloads' ); ?></p>
		</div>
	<?php endif; ?>
	<?php if ( empty( $customer['last_name'] ) ) : ?>
		<div id="edd-last-name-wrap">
			<label class="edd-label" for="edd-last">
				<?php
				esc_html_e( 'Last name', 'easy-digital-downloads' );
				if ( edd_field_is_required( 'edd_last' ) ) {
					echo EDD()->html->show_required();
				}
				?>
			</label>
			<input class="edd-input" type="text" name="edd_last" id="edd-last" placeholder="<?php esc_html_e( 'Last name', 'easy-digital-downloads' ); ?>" value="<?php echo esc_attr( $customer['last_name'] ); ?>" aria-describedby="edd-last-description" <?php EDD\Blocks\Functions\mark_field_required( 'edd_last' ); ?>/>
			<p class="edd-description" id="edd-last-description"><?php esc_html_e( 'We will use this as well to personalize your account experience.', 'easy-digital-downloads' ); ?></p>
		</div>
		<?php
	endif;
	/**
	 * Allow users to add additional fields to the checkout form.
	 *
	 * @param array $customer Customer information. Note that this parameter is not in the original shortcode hook.
	 */
	do_action( 'edd_purchase_form_user_info_fields', $customer );
	?>
</fieldset>
