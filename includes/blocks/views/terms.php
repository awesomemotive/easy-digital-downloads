<?php
/**
 * Displays the download terms block.
 *
 * @var array         $block_attributes
 * @var WP_Term_Query $query
 * @var array         $classes
 */
?>
<div class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>">
	<?php
	foreach ( $query->terms as $term ) :
		$term_description = term_description( $term->term_id, $term->taxonomy );
		$attachment_id    = get_term_meta( $term->term_id, 'download_term_image', true );
		?>
		<div class="edd-blocks__term">
			<?php if ( $block_attributes['thumbnails'] && $attachment_id ) : ?>
				<div class="edd-blocks__image align<?php echo esc_attr( $block_attributes['image_alignment'] ); ?>">
					<a href="<?php echo esc_url( get_term_link( $term ) ); ?>">
						<?php
						echo wp_get_attachment_image(
							$attachment_id,
							$block_attributes['image_size']
						);
						?>
					</a>
				</div>
			<?php endif; ?>

			<?php if ( $block_attributes['title'] ) : ?>
				<div class="edd-blocks__term-title">
					<h3><a href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a></h3>
					<?php if ( $block_attributes['count'] ) : ?>
						<span class="edd-blocks__term-count">(<?php echo esc_html( $term->count ); ?>)</span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $block_attributes['description'] && $term_description ) : ?>
				<div class="edd-blocks__term-description">
					<?php echo wp_kses_post( $term_description ); ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>
