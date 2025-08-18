<?php
/**
 * Displays the download terms block.
 *
 * @package EDD\Blocks
 * @var array    $block_attributes
 * @var WP_Query $downloads
 * @var array    $classes
 */

?>
<div class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>">
	<?php
	while ( $downloads->have_posts() ) {
		$downloads->the_post();
		$classes = array( 'edd-blocks__download' );
		if ( ! empty( get_post_meta( get_the_ID(), 'edd_feature_download', true ) ) ) {
			$classes[] = 'edd-blocks__download--featured';
		}
		$classes = apply_filters( 'edd_blocks_download_classes', $classes, get_the_ID(), $block_attributes );
		?>
		<article class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>">
			<?php
			if ( ! empty( $block_attributes['title'] ) || ! empty( $block_attributes['image_location'] ) ) {
				include 'header.php';
			}
			if ( ! empty( $block_attributes['content'] ) || ! empty( $block_attributes['price'] ) ) {
				include 'content.php';
			}
			if ( $block_attributes['purchase_link'] ) {
				include 'footer.php';
			}
			?>
		</article>
		<?php
	}
	?>
</div>
<?php
if ( $block_attributes['pagination'] ) {
	include 'pagination.php';
}
