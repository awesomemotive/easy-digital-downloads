<?php if ( ! edd_has_variable_prices( get_the_ID() ) ) : ?>
	<div itemprop="price" class="edd_price">
		<?php edd_price( get_the_ID() ); ?>
	</div>
<?php endif; ?>