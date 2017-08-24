<?php $item_prop = edd_add_schema_microdata() ? ' itemprop="description"' : ''; ?>
<div<?php echo $item_prop; ?> class="edd_download_full_content">
	<?php echo apply_filters( 'edd_downloads_content', get_post_field( 'post_content', get_the_ID() ) ); ?>
</div>
