<div id="edd-payment-processing">
	<p>
		<?php
		/* translators: %s - success page URL */
		printf( wp_kses_post( __( 'Your purchase is processing. This page will reload automatically in 8 seconds. If it does not, click <a href="%s">here</a>.', 'easy-digital-downloads' ) ), esc_url( edd_get_success_page_uri() ) );
		?>
	</p>
	<span class="edd-cart-ajax"><span class="edd-icon-spinner edd-icon-spin"></span></span>
	<script type="text/javascript">setTimeout(function(){ window.location = '<?php echo esc_url( edd_get_success_page_uri() ); ?>'; }, 8000);</script>
</div>
