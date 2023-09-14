<?php use EDD\Blocks\Functions; ?>
<fieldset id="edd_cc_address" class="edd-blocks-form cc-address">
	<legend><?php esc_html_e( 'Billing Address', 'easy-digital-downloads' ); ?></legend>
	<?php do_action( 'edd_cc_billing_top' ); ?>
	<div id="edd-card-country-wrap" class="edd-blocks-form__group edd-blocks-form__group-country">
		<label for="billing_country" class="edd-label">
			<?php
			esc_html_e( 'Country', 'easy-digital-downloads' );
			if ( edd_field_is_required( 'billing_country' ) ) {
				echo EDD()->html->show_required();
			}
			?>
		</label>
		<select name="billing_country" id="billing_country" data-nonce="<?php echo esc_attr( wp_create_nonce( 'edd-country-field-nonce' ) ); ?>" class="<?php echo esc_attr( implode( ' ', Functions\get_input_classes( 'billing_country', array( 'edd-select' ) ) ) ); ?>"<?php Functions\mark_field_required( 'billing_country' ); ?>>
			<?php
			$selected_country = edd_get_shop_country();

			if ( ! empty( $customer['address']['country'] ) && '*' !== $customer['address']['country'] ) {
				$selected_country = $customer['address']['country'];
			}

			$countries = edd_get_country_list();
			foreach ( $countries as $country_code => $country ) {
				echo '<option value="' . esc_attr( $country_code ) . '"' . selected( $country_code, $selected_country, false ) . '>' . esc_html( $country ) . '</option>';
			}
			?>
		</select>
	</div>
	<div id="edd-card-address-wrap" class="edd-blocks-form__group edd-blocks-form__group-address">
		<label for="card_address" class="edd-label">
			<?php
			esc_html_e( 'Address', 'easy-digital-downloads' );
			if ( edd_field_is_required( 'card_address' ) ) {
				echo EDD()->html->show_required();
			}
			?>
		</label>
		<input type="text" id="card_address" name="card_address" class="<?php echo esc_attr( implode( ' ', Functions\get_input_classes( 'card_address', array( 'edd-input' ) ) ) ); ?>" placeholder="<?php esc_html_e( 'Address line 1', 'easy-digital-downloads' ); ?>" value="<?php echo esc_attr( $customer['address']['line1'] ); ?>"<?php Functions\mark_field_required( 'card_address' ); ?>/>
	</div>
	<div id="edd-card-address-2-wrap" class="edd-blocks-form__group edd-blocks-form__group-address2">
		<label for="card_address_2" class="edd-label">
			<?php
			esc_html_e( 'Address Line 2', 'easy-digital-downloads' );
			if ( edd_field_is_required( 'card_address_2' ) ) {
				echo EDD()->html->show_required();
			}
			?>
		</label>
		<input type="text" id="card_address_2" name="card_address_2" class="<?php echo esc_attr( implode( ' ', Functions\get_input_classes( 'card_address_2', array( 'edd-input' ) ) ) ); ?>" placeholder="<?php esc_html_e( 'Address line 2', 'easy-digital-downloads' ); ?>" value="<?php echo esc_attr( $customer['address']['line2'] ); ?>"<?php Functions\mark_field_required( 'card_address_2' ); ?>/>
	</div>
	<div id="edd-card-city-wrap" class="edd-blocks-form__group edd-blocks-form__group-city">
		<label for="card_city" class="edd-label">
			<?php
			esc_html_e( 'City', 'easy-digital-downloads' );
			if ( edd_field_is_required( 'card_city' ) ) {
				echo EDD()->html->show_required();
			}
			?>
		</label>
		<input type="text" id="card_city" name="card_city" class="<?php echo esc_attr( implode( ' ', Functions\get_input_classes( 'card_city', array( 'edd-input' ) ) ) ); ?>" placeholder="<?php esc_html_e( 'City', 'easy-digital-downloads' ); ?>" value="<?php echo esc_attr( $customer['address']['city'] ); ?>"<?php Functions\mark_field_required( 'card_city' ); ?>/>
	</div>
	<div class="edd-blocks-form__halves">
		<div id="edd-card-state-wrap" class="edd-blocks-form__group edd-blocks-form__group-state">
			<label for="card_state" class="edd-label">
				<?php
				esc_html_e( 'State/Province', 'easy-digital-downloads' );
				if ( edd_field_is_required( 'card_state' ) ) {
					echo EDD()->html->show_required();
				}
				?>
			</label>
			<?php
			$selected_state = edd_get_shop_state();
			$states         = edd_get_shop_states( $selected_country );

			if ( ! empty( $customer['address']['state'] ) ) {
				$selected_state = $customer['address']['state'];
			}

			if ( ! empty( $states ) ) :
				?>
				<select name="card_state" id="card_state" class="<?php echo esc_attr( implode( ' ', Functions\get_input_classes( 'card_state', array( 'edd-select' ) ) ) ); ?>"<?php Functions\mark_field_required( 'card_state' ); ?>>
					<?php
					foreach ( $states as $state_code => $state ) {
						echo '<option value="' . esc_attr( $state_code ) . '"' . selected( $state_code, $selected_state, false ) . '>' . esc_html( $state ) . '</option>';
					}
					?>
				</select>
				<?php
			else :
				$customer_state = ! empty( $customer['address']['state'] ) ? $customer['address']['state'] : '';
				?>
				<input type="text" name="card_state" id="card_state" class="<?php echo esc_attr( implode( ' ', Functions\get_input_classes( 'card_state', array( 'edd-input' ) ) ) ); ?>" value="<?php echo esc_attr( $customer_state ); ?>" placeholder="<?php esc_html_e( 'State/Province', 'easy-digital-downloads' ); ?>"<?php Functions\mark_field_required( 'card_state' ); ?>/>
			<?php endif; ?>
		</div>
		<div id="edd-card-zip-wrap" class="edd-blocks-form__group edd-blocks-form__group-zip">
			<label for="card_zip" class="edd-label">
				<?php
				esc_html_e( 'ZIP/Postal Code', 'easy-digital-downloads' );
				if ( edd_field_is_required( 'card_zip' ) ) {
					echo EDD()->html->show_required();
				}
				?>
			</label>
			<input type="text" id="card_zip" name="card_zip" class="<?php echo esc_attr( implode( ' ', Functions\get_input_classes( 'card_zip', array( 'edd-input' ) ) ) ); ?>" placeholder="<?php esc_html_e( 'Zip / Postal Code', 'easy-digital-downloads' ); ?>" value="<?php echo esc_attr( $customer['address']['zip'] ); ?>"<?php Functions\mark_field_required( 'card_zip' ); ?>/>
		</div>
	</div>
	<?php do_action( 'edd_cc_billing_bottom' ); ?>
	<?php wp_nonce_field( 'edd-checkout-address-fields', 'edd-checkout-address-fields-nonce', false, true ); ?>
</fieldset>
<?php
