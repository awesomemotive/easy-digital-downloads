<?php
$download_files = edd_get_download_files( $item->product_id, $item->price_id );
if ( $block_attributes['hide_empty'] && empty( $download_files ) ) {
	return;
}
$order   = edd_get_order( $item->order_id );
$classes = array(
	'edd-blocks__row',
	'edd-order-item__product',
);
if ( $block_attributes['search'] && edd_is_pro() ) {
	$classes[] = 'edd-pro-search__product';
}

$registered_columns = EDD\Blocks\Orders\get_user_downloads_block_columns();
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<?php
	foreach ( $registered_columns as $column_id => $column ) {
		$row = $column['row'];

		$classes = array(
			'edd-blocks__row-column',
			'edd-blocks__row-column--' . $column_id,
		);

		if ( ! empty( $row['classes'] ) ) {
			$classes = array_merge( $classes, $row['classes'] );
		}

		?>
		<div class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>">
			<?php

			$action_args = array(
				'name'             => $name,
				'order_item'       => $item,
				'order'            => $order,
				'block_attributes' => $block_attributes,
				'download_files'   => $download_files,
			);
			/**
			 * Renders a column in the user downloads block.
			 *
			 * To add a new column, use the `edd_blocks_user_download_columns` filter.
			 *
			 * @since 2.0.6
			 * @param array $action_args    The arguments to pass to the hook.
			 *     @param string                $name             The name of the product.
			 *     @param EDD\Orders\Order_Item $item             The order item.
			 *     @param EDD\Orders\Order      $order            The order object.
			 *     @param array                 $block_attributes The block attributes.
			 *     @param array                 $download_files   The download files.
			 */
			do_action( 'edd_blocks_user_downloads_block_column_' . $column_id, $action_args );
			?>
		</div>
	<?php } ?>
</div>
