<fieldset id="edd_cc_fields" class="edd-do-validate edd-blocks-form">
	<legend><?php esc_html_e( 'Credit Card Info', 'easy-digital-downloads' ); ?></legend>
	<?php if ( is_ssl() ) : ?>
		<div id="edd_secure_site_wrapper">
			<?php
				echo edd_get_payment_icon(
					array(
						'icon'    => 'lock',
						'width'   => 16,
						'height'  => 16,
						'title'   => __( 'Secure SSL encrypted payment', 'easy-digital-downloads' ),
						'classes' => array( 'edd-icon', 'edd-icon-lock' ),
					)
				);
			?>
			<span><?php esc_html_e( 'This is a secure SSL encrypted payment.', 'easy-digital-downloads' ); ?></span>
		</div>
	<?php endif; ?>
	<div id="edd-card-name-wrap" class="edd-blocks-form__group edd-blocks-form__group-card--name">
		<label for="card_name" class="edd-label">
			<?php
			esc_html_e( 'Name on the Card', 'easy-digital-downloads' );
			echo EDD()->html->show_required();
			?>
		</label>
		<input type="text" autocomplete="off" name="card_name" id="card_name" class="card-name edd-input required" placeholder="<?php _e( 'Card name', 'easy-digital-downloads' ); ?>" />
	</div>
	<div id="edd-card-number-wrap" class="edd-blocks-form__group edd-blocks-form__group-card--number">
		<label for="card_number" class="edd-label">
			<?php
			esc_html_e( 'Card Number', 'easy-digital-downloads' );
			echo EDD()->html->show_required();
			?>
			<span class="card-type"></span>
		</label>
		<input type="tel" pattern="^[0-9!@#$%^&* ]*$" autocomplete="off" name="card_number" id="card_number" class="card-number edd-input required" placeholder="<?php esc_html_e( 'Card number', 'easy-digital-downloads' ); ?>" />
	</div>
	<div class="edd-blocks-form__halves">
		<div id="edd-card-cvc-wrap" class="edd-blocks-form__group edd-blocks-form__group-card--cvc">
			<label for="card_cvc" class="edd-label">
				<?php
				esc_html_e( 'CVC', 'easy-digital-downloads' );
				echo EDD()->html->show_required();
				?>
			</label>
			<input type="tel" pattern="[0-9]{3,4}" size="4" maxlength="4" autocomplete="off" name="card_cvc" id="card_cvc" class="card-cvc edd-input required" placeholder="<?php _e( 'Security code', 'easy-digital-downloads' ); ?>" />
		</div>
		<?php do_action( 'edd_before_cc_expiration' ); ?>
		<div class="card-expiration" class="edd-blocks-form__group edd-blocks-form__group-card--expiration">
			<label for="card_exp_month" class="edd-label">
				<?php
				esc_html_e( 'Expiration (MM/YY)', 'easy-digital-downloads' );
				echo EDD()->html->show_required();
				?>
			</label>
			<select id="card_exp_month" name="card_exp_month" class="card-expiry-month edd-select edd-select-small required">
				<?php
				for ( $i = 1; $i <= 12; $i++ ) {
					echo '<option value="' . absint( $i ) . '">' . sprintf( '%02d', absint( $i ) ) . '</option>';
				}
				?>
			</select>
			<span class="exp-divider">/</span>
			<select id="card_exp_year" name="card_exp_year" class="card-expiry-year edd-select edd-select-small required">
				<?php
				$year = gmdate( 'Y' );
				for ( $i = $year; $i <= $year + 30; $i++ ) {
					echo '<option value="' . absint( $i ) . '">' . esc_html( substr( $i, 2 ) ) . '</option>';
				}
				?>
			</select>
		</div>
		<?php do_action( 'edd_after_cc_expiration' ); ?>
	</div>

</fieldset>
