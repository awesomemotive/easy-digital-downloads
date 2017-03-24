<?php if ( ! edd_has_variable_prices( get_the_ID() ) ) : ?>
	<?php $item_props = edd_add_schema_microdata() ? ' itemprop="offers" itemscope itemtype="http://schema.org/Offer"' : ''; ?>
	<div<?php echo $item_props; ?>>
		<div itemprop="price" class="edd_price">
			<?php edd_price( get_the_ID() ); ?>
		</div>
	</div>
<?php endif; ?>
