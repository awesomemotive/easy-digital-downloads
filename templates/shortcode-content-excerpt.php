<?php
/**
 * Shortcode "Excerpt" template.
 *
 * @since 1.2
 *
 * @package EDD
 * @category Template
 * @author Easy Digital Downloads
 * @version 2.0.0
 */

$item_prop = edd_add_schema_microdata() ? ' itemprop="description"' : '';
?>

<div<?php echo $item_prop; ?> class="edd_download_excerpt">
	<?php echo edd_download_shortcode_excerpt(); ?>
</div>
