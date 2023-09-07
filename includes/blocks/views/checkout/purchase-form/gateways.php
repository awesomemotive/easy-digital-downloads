<div id="edd_payment_mode_select_wrap">
	<?php do_action( 'edd_payment_mode_top' ); ?>

	<?php if ( edd_is_ajax_disabled() ) { ?>
		<form id="edd_payment_mode" action="<?php echo esc_url( edd_get_current_page_url() ); ?>" method="GET">
	<?php } ?>

	<fieldset id="edd_payment_mode_select">
		<legend><?php esc_html_e( 'Select Payment Method', 'easy-digital-downloads' ); ?></legend>
		<?php do_action( 'edd_payment_mode_before_gateways_wrap' ); ?>
		<div id="edd-payment-mode-wrap">
			<?php
			do_action( 'edd_payment_mode_before_gateways' );

			$gateways      = edd_get_enabled_payment_gateways( true );
			$payment_icons = EDD\Blocks\Checkout\Gateways\get_payment_icons();
			$default       = edd_get_option( 'default_gateway' );
			foreach ( $gateways as $gateway_id => $gateway ) {
				$checked = $gateway_id === $default ? 'checked' : checked( $gateway_id, $payment_mode, false );
				$class   = 'edd-gateway-option';
				if ( $checked ) {
					$class .= ' edd-gateway-option-selected';
				}

				?>
				<label for="edd-gateway-<?php echo esc_attr( $gateway_id ); ?>" class="<?php echo esc_attr( $class ); ?>" id="edd-gateway-option-<?php echo esc_attr( $gateway_id ); ?>">
					<input type="radio" name="payment-mode" class="edd-gateway" id="edd-gateway-<?php echo esc_attr( $gateway_id ); ?>" value="<?php echo esc_attr( $gateway_id ); ?>" data-<?php echo esc_attr( $gateway_id ); ?>-nonce="<?php echo esc_attr( wp_create_nonce( 'edd-gateway-selected-' . esc_attr( $gateway_id ) ) ); ?>" autocomplete="off" <?php echo esc_attr( $checked ); ?>>
					<?php
					echo esc_html( apply_filters( 'edd_gateway_checkout_label_' . $gateway_id, $gateway['checkout_label'] ) );
					if ( $payment_icons ) {
						$gateway_icons = EDD\Blocks\Checkout\Gateways\get_gateway_icons( $gateway_id, $gateway );
						if ( $gateway_icons ) {
							EDD\Blocks\Checkout\Gateways\do_payment_icons( $payment_icons, $gateway_icons );
						}
					}
					?>
				</label>
				<?php
			}

			do_action( 'edd_payment_mode_after_gateways' );
			?>
		</div>

		<?php do_action( 'edd_payment_mode_after_gateways_wrap' ); ?>
	</fieldset>

	<div id="edd_payment_mode_submit" class="edd-no-js">
		<input type="hidden" name="edd_action" value="gateway_select" />
		<input type="hidden" name="page_id" value="<?php echo absint( edd_get_current_page_url() ); ?>"/>
		<input type="submit" name="gateway_submit" id="edd_next_button" class="edd-submit" value="<?php esc_html_e( 'Next', 'easy-digital-downloads' ); ?>"/>
	</div>

	<?php if ( edd_is_ajax_disabled() ) : ?>
		</form>
	<?php endif; ?>
</div>
