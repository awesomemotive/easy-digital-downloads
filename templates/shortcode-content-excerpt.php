<div itemprop="description" class="edd_download_excerpt">
	<?php
		$excerpt_length = apply_filters('excerpt_length', 30);
		echo apply_filters( 'edd_downloads_excerpt', wp_trim_words( get_post_field( 'post_content', get_the_ID() ), $excerpt_length ) );
	?>
</div>