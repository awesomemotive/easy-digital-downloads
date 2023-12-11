<?php
/**
 * Bundled Products Metabox
 *
 * @var int          $post_id
 * @var EDD_Download $download
 */
$products         = $download->get_bundled_downloads();
$variable_pricing = $download->has_variable_prices();
$variable_display = $variable_pricing ? '' : 'display:none;';
$variable_class   = $variable_pricing ? ' has-variable-pricing' : '';
$prices           = $download->get_prices();
$bundle_options   = EDD()->html->get_products(
	array(
		'bundles' => false,
	)
);
?>

<div id="edd_products">
	<div id="edd_file_fields_bundle" class="edd_meta_table_wrap">
		<div class="widefat edd_repeatable_table">

			<?php do_action( 'edd_download_products_table_head', $post_id ); ?>

			<div class="edd-bundled-product-select edd-repeatables-wrap">

				<?php if ( $products ) : ?>

					<div class="edd-bundle-products-header">
						<span class="edd-bundle-products-title"><?php printf( __( 'Bundled %s', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></span>
					</div>

					<?php $index = 1; ?>
					<?php foreach ( $products as $key => $product ) : ?>
						<div class="edd_repeatable_product_wrapper edd_repeatable_row" data-key="<?php echo esc_attr( $index ); ?>">
							<div class="edd-bundled-product-row<?php echo esc_attr( $variable_class ); ?>">
								<div class="edd-bundled-product-item-reorder">
									<span class="edd-product-file-reorder edd-draghandle-anchor dashicons dashicons-move"  title="<?php printf( __( 'Click and drag to re-order bundled %s', 'easy-digital-downloads' ), edd_get_label_plural() ); ?>"></span>
									<input type="hidden" name="edd_bundled_products[<?php echo esc_attr( $index ); ?>][index]" class="edd_repeatable_index" value="<?php echo esc_attr( $index ); ?>"/>
								</div>
								<div class="edd-form-group edd-bundled-product-item">
									<label for="edd_bundled_products_<?php echo esc_attr( $index ); ?>" class="edd-form-group__label edd-repeatable-row-setting-label"><?php printf( esc_html__( 'Select %s:', 'easy-digital-downloads' ), edd_get_label_singular() ); ?></label>
									<div class="edd-form-group__control">
									<?php
									echo EDD()->html->product_dropdown(
										array(
											'name'                 => '_edd_bundled_products[' . $index . ']',
											'id'                   => 'edd_bundled_products_' . esc_attr( $index ),
											'selected'             => $product,
											'multiple'             => false,
											'chosen'               => true,
											'products'             => $bundle_options,
											'variations'           => true,
											'show_variations_only' => false,
											'class'                => 'edd-form-group__input',
											'bundles'              => false,
											'exclude_current'      => true,
										)
									);
									?>
									</div>
								</div>
								<div class="edd-form-group edd-bundled-product-price-assignment pricing" style="<?php echo $variable_display; ?>">
									<label class="edd-form-group__label edd-repeatable-row-setting-label" for="edd_bundled_products_conditions_<?php echo esc_attr( $index ); ?>"><?php esc_html_e( 'Price assignment:', 'easy-digital-downloads' ); ?></label>
									<div class="edd-form-group__control">
									<?php
										$options = array();

										if ( $prices ) {
											foreach ( $prices as $price_key => $price ) {
												$options[ $price_key ] = $prices[ $price_key ]['name'];
											}
										}

										$price_assignments = edd_get_bundle_pricing_variations( $post_id );
										if ( ! empty( $price_assignments[0] ) ) {
											$price_assignments = $price_assignments[0];
										}

										$selected = isset( $price_assignments[ $index ] ) ? $price_assignments[ $index ] : null;

										echo EDD()->html->select( array(
											'name'             => '_edd_bundled_products_conditions[' . $index . ']',
											'id'               => 'edd_bundled_products_conditions_'. esc_attr( $index ),
											'class'            => 'edd_repeatable_condition_field',
											'options'          => $options,
											'show_option_none' => false,
											'selected'         => $selected
										) );
									?>
									</div>
								</div>
								<div class="edd-bundled-product-actions">
									<a class="edd-remove-row edd-delete" data-type="file"><?php esc_html_e( 'Remove', 'easy-digital-downloads' ); ?><span class="screen-reader-text"><?php printf( esc_html__( 'Remove bundle option %s', 'easy-digital-downloads' ), esc_html( $index ) ); ?></span></a>
								</div>
								<?php do_action( 'edd_download_products_table_row', $post_id ); ?>
							</div>
						</div>
						<?php $index++; ?>
					<?php endforeach; ?>

				<?php else: ?>

					<div class="edd-bundle-products-header">
						<span class="edd-bundle-products-title"><?php printf( __( 'Bundled %s:', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></span>
					</div>
					<div class="edd_repeatable_product_wrapper edd_repeatable_row" data-key="1">
						<div class="edd-bundled-product-row<?php echo $variable_class; ?>">

							<div class="edd-bundled-product-item-reorder">
								<span class="edd-product-file-reorder edd-draghandle-anchor dashicons dashicons-move" title="<?php printf( __( 'Click and drag to re-order bundled %s', 'easy-digital-downloads' ), edd_get_label_plural() ); ?>"></span>
								<input type="hidden" name="edd_bundled_products[1][index]" class="edd_repeatable_index" value="1"/>
							</div>
							<div class="edd-form-group edd-bundled-product-item">
								<label class="edd-form-group__label edd-repeatable-row-setting-label" for="edd_bundled_products_1"><?php printf( esc_html__( 'Select %s:', 'easy-digital-downloads' ), edd_get_label_singular() ); ?></label>
								<div class="edd-form-group__control">
								<?php
								echo EDD()->html->product_dropdown( array(
									'name'                 => '_edd_bundled_products[1]',
									'id'                   => 'edd_bundled_products_1',
									'multiple'             => false,
									'chosen'               => true,
									'products'             => $bundle_options,
									'variations'           => true,
									'show_variations_only' => false,
									'bundles'              => false,
									'exclude_current'      => true,
								) );
								?>
								</div>
							</div>
							<div class="edd-form-group edd-bundled-product-price-assignment pricing" style="<?php echo $variable_display; ?>">
								<label class="edd-form-group__label edd-repeatable-row-setting-label" for="edd_bundled_products_conditions_1"><?php esc_html_e( 'Price assignment:', 'easy-digital-downloads' ); ?></label>
								<div class="edd-form-group__control">
								<?php
									$options = array();

									if ( $prices ) {
										foreach ( $prices as $price_key => $price ) {
											$options[ $price_key ] = $prices[ $price_key ]['name'];
										}
									}

									$price_assignments = edd_get_bundle_pricing_variations( $post_id );

									echo EDD()->html->select( array(
										'name'             => '_edd_bundled_products_conditions[1]',
										'id'               => 'edd_bundled_products_conditions_1',
										'class'            => 'edd-form-group__input edd_repeatable_condition_field',
										'options'          => $options,
										'show_option_none' => false,
										'selected'         => null,
									) );
								?>
								</div>
							</div>
							<div class="edd-bundled-product-actions">
								<a class="edd-remove-row edd-delete" data-type="file" ><?php esc_html_e( 'Remove', 'easy-digital-downloads' ); ?><span class="screen-reader-text"><?php esc_html_e( 'Remove bundle option 1', 'easy-digital-downloads' ); ?></span></a>
							</div>
							<?php do_action( 'edd_download_products_table_row', $post_id ); ?>
						</div>
					</div>

				<?php endif; ?>

			</div>

			<div class="edd-add-repeatable-row">
				<button class="button-secondary edd_add_repeatable"><?php esc_html_e( 'Add New File', 'easy-digital-downloads' ); ?></button>
			</div>
		</div>
	</div>
</div>
