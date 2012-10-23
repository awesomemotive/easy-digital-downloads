<?php if ( function_exists( 'has_post_thumbnail' ) && has_post_thumbnail( get_the_ID() ) ) : ?>
	<div class="edd_download_image">
		<?php echo get_the_post_thumbnail(  get_the_ID(), 'thumbnail' ); ?>
	</div>
<?php endif; ?>