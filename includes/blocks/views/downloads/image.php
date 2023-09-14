<?php if ( $block_attributes['image_link'] ) : ?>
	<a
		href="<?php echo esc_url( get_the_permalink() ); ?>"
		class="edd-blocks__download-image-link"
		<?php
		if ( $block_attributes['title'] ) {
			?>
			aria-hidden="true"
			tabindex="-1"
			<?php
		}
		?>
	>
	<?php
endif;
the_post_thumbnail(
	$block_attributes['image_size'],
	array(
		'class' => "align{$block_attributes['image_alignment']} edd-blocks__download-image",
		'alt'   => '',
	)
);
if ( $block_attributes['image_link'] ) :
	?>
	</a>
	<?php
endif;
