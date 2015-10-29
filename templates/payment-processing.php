<div id="edd-payment-processing">
	<p><?php printf( __( 'Your purchase is processing. This page will reload automatically in 8 seconds. If it does not, click <a href="%s">here</a>.', 'easy-digital-downloads' ), edd_get_success_page_uri() ); ?>
	<span class="edd-cart-ajax"><i class="edd-icon-spinner edd-icon-spin"></i></span>
	<script type="text/javascript">setTimeout(function(){ window.location = '<?php echo edd_get_success_page_uri(); ?>'; }, 8000);</script>
</div>