<?php $excerpt_length = apply_filters( 'excerpt_length', 30 ); ?>

<?php if ( has_excerpt() ) : ?>
	<div itemprop="description" class="edd_download_excerpt">
		<?php echo apply_filters( 'edd_downloads_excerpt', wp_trim_words( get_post_field( 'post_excerpt', get_the_ID() ), $excerpt_length ) ); ?>
	</div>
<?php elseif ( get_the_content() ) : ?>
	<div itemprop="description" class="edd_download_excerpt">
		<?php echo apply_filters( 'edd_downloads_excerpt', wp_trim_words( get_post_field( 'post_content', get_the_ID() ), $excerpt_length ) ); ?>
	</div>
<?php endif; ?>