<div itemprop="description" class="edd_download_excerpt">
	<?php
	$content = has_excerpt( get_the_ID() )
		? 'excerpt'
		: 'content'
	;
	echo apply_filters(
		'edd_downloads_excerpt',
		wp_trim_words(
			get_post_field( "post_{$content}", get_the_ID() ),
			apply_filters( 'excerpt_length', 30 )
		)
	);
	?>
</div>