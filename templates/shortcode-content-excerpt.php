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
 * @version 3.0 Uses the new `edd_downlaod_shortcode_except() function`
 */

$item_prop = edd_add_schema_microdata() ? ' itemprop="description"' : '';
?>

<div<?php echo $item_prop; ?> class="edd_download_excerpt">
	<?php echo edd_download_shortcode_excerpt(); ?>
</div>
