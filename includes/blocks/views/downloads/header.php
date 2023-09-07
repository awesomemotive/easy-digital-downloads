
<header class="edd-blocks__download-header">
	<?php
	do_action( 'edd_blocks_downloads_before_entry_title', $block_attributes );
	if ( $block_attributes['title'] ) {
		printf(
			'<h3><a href="%s">%s</a></h3>',
			esc_url( get_the_permalink() ),
			esc_html( get_the_title() )
		);
	}
	do_action( 'edd_blocks_downloads_after_entry_title', $block_attributes );
	?>
</header>
