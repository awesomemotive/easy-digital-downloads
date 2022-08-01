<?php if ( has_post_thumbnail( get_the_ID() ) ) : ?>
	<div class="edd_download_image">
		<a href="<?php the_permalink(); ?>">
			<?php echo get_the_post_thumbnail( get_the_ID(), 'thumbnail' ); ?>
		</a>
	</div>
<?php endif; ?>
