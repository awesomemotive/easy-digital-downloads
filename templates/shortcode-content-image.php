<?php
defined( 'ABSPATH' ) OR exit;

if ( has_post_thumbnail() ) {
	$title = sprintf(
		_x( 'Permalink to: %s', '%s is the title', 'edd' ),
		get_the_title()
	);
	?>
	<a href="<?php the_permalink(); ?>" title="<?php echo $title; ?>">
		<?php the_post_thumbnail( 'product-image' ); ?>
	</a>
	<?php
}