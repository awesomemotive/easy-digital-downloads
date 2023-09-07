<div class="edd-blocks__download-content">
	<?php
	if ( ! empty( $block_attributes['content'] ) ) {
		if ( 'content' === $block_attributes['content'] ) {
			the_content();
		} else {
			the_excerpt();
		}
	}
	if ( $block_attributes['price'] ) :
		?>
		<div class="edd-blocks__download-price">
			<?php
			if ( edd_is_free_download( get_the_ID() ) ) {
				printf(
					'<span class="edd_price" id="edd_price_%s">%s</span>',
					absint( get_the_ID() ),
					esc_html__( 'Free', 'easy-digital-downloads' )
				);
			} elseif ( edd_has_variable_prices( get_the_ID() ) ) {
				echo wp_kses_post( edd_price_range( get_the_ID() ) );
			} else {
				edd_price( get_the_ID(), true );
			}
			?>
		</div>
		<?php
	endif;
	?>
</div>
