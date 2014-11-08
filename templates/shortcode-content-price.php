<?php if ( ! edd_has_variable_prices( get_the_ID() ) ) : ?>
	<div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
		<div itemprop="price" class="edd_price">
			<?php edd_price( get_the_ID() ); ?>
		</div>
	</div>
<?php endif; ?>