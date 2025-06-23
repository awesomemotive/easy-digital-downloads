<?php
/**
 * Bundled Products Metabox
 *
 * @package EDD\Admin\Downloads\Views
 * @var int          $post_id
 * @var EDD_Download $download
 */

$products = $download->get_bundled_downloads();
if ( ! $products ) {
	$products = array(
		0 => '',
	);
}
$variable_pricing = $download->has_variable_prices();
if ( edd_doing_ajax() ) {
	$variable_pricing = filter_input( INPUT_POST, 'has_variable_pricing', FILTER_VALIDATE_BOOLEAN );
}
$row_classes = array(
	'edd-bundled-product-row',
);
if ( $variable_pricing ) {
	$row_classes[] = 'has-variable-pricing';
}
$prices         = $download->get_prices();
$bundle_options = EDD()->html->get_products(
	array(
		'bundles' => false,
	)
);
?>

<div id="edd_bundled_products">
	<div id="edd_file_fields_bundle" class="edd_meta_table_wrap">
		<div class="widefat edd_repeatable_table">

			<?php do_action( 'edd_download_products_table_head', $post_id ); ?>

			<div class="edd-bundled-product-select edd-repeatables-wrap edd-handle-actions__group">
				<?php
				$index = 1;
				foreach ( $products as $product ) :
					$product_select_args = array(
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
						'show_option_empty'    => __( 'Select a product', 'easy-digital-downloads' ),
						'show_option_all'      => false,
					);
					?>
					<div class="edd_repeatable_product_wrapper edd_repeatable_row edd-has-handle-actions" data-key="<?php echo esc_attr( $index ); ?>">
						<div class="<?php echo esc_attr( implode( ' ', $row_classes ) ); ?>">
							<div class="edd-form-group edd-bundled-product-item">
								<?php /* translators: %s: Download singular label */ ?>
								<label for="edd_bundled_products_<?php echo esc_attr( $index ); ?>" class="edd-form-group__label edd-repeatable-row-setting-label"><?php printf( esc_html_x( 'Select %s:', 'Noun: The singular label for the download post type', 'easy-digital-downloads' ), edd_get_label_singular() ); ?></label>
								<div class="edd-form-group__control">
									<?php
									$dropdown = new EDD\HTML\ProductSelect( $product_select_args );
									$dropdown->output();
									?>
								</div>
							</div>
							<?php
							$conditions_classes = array(
								'edd-form-group',
								'edd-bundled-product-price-assignment',
								'pricing',
							);
							if ( ! $variable_pricing ) {
								$conditions_classes[] = 'edd-hidden';
							}
							?>
							<div class="<?php echo esc_attr( implode( ' ', $conditions_classes ) ); ?>" data-edd-requires-variable-pricing="true">
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

								$select = new EDD\HTML\Select(
									array(
										'name'             => '_edd_bundled_products_conditions[' . $index . ']',
										'id'               => 'edd_bundled_products_conditions_' . esc_attr( $index ),
										'class'            => 'edd_repeatable_condition_field',
										'options'          => $options,
										'show_option_none' => false,
										'selected'         => $selected,
									)
								);
								$select->output();
								?>
								</div>
							</div>
						</div>
						<div class="edd-bundled-product-actions edd-repeatable-row-actions">
							<div class="edd__handle-actions-order hide-if-no-js">
								<button type="button" class="edd__handle-actions edd__handle-actions-order--higher" aria-disabled="false" aria-describedby="edd-bundled-product-<?php echo esc_attr( $index ); ?>-edd__handle-actions-order--higher-description">
									<span class="screen-reader-text"><?php esc_html_e( 'Move up', 'easy-digital-downloads' ); ?></span>
									<span class="dashicons dashicons-arrow-up-alt2" aria-hidden="true"></span>
								</button>
								<span class="hidden" id="edd-bundled-product-<?php echo esc_attr( $index ); ?>-edd__handle-actions-order--higher-description">
									<?php
									/* translators: %s: Download singular label */
									printf( esc_html__( 'Move %s up', 'easy-digital-downloads' ), edd_get_label_singular() );
									?>
								</span>
								<button type="button" class="edd__handle-actions edd__handle-actions-order--lower" aria-disabled="false" aria-describedby="edd-bundled-product-<?php echo esc_attr( $index ); ?>-edd__handle-actions-order--lower-description">
									<span class="screen-reader-text"><?php esc_html_e( 'Move down', 'easy-digital-downloads' ); ?></span>
									<span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
								</button>
								<span class="hidden" id="edd-bundled-product-<?php echo esc_attr( $index ); ?>-edd__handle-actions-order--lower-description">
									<?php
									/* translators: %s: Download singular label */
									printf( esc_html__( 'Move %s down', 'easy-digital-downloads' ), edd_get_label_singular() );
									?>
								</span>
							</div>
							<?php /* translators: %s: The bundle product index number. */ ?>
							<button type="button" class="edd-remove-row button button-secondary edd-delete" data-type="file"><?php esc_html_e( 'Remove', 'easy-digital-downloads' ); ?><span class="screen-reader-text"><?php printf( esc_html__( 'Remove bundle option %s', 'easy-digital-downloads' ), esc_html( $index ) ); ?></span></button>
						</div>
						<?php do_action( 'edd_download_products_table_row', $post_id ); ?>
					</div>
					<?php ++$index; ?>
				<?php endforeach; ?>
			</div>

			<div class="edd-add-repeatable-row">
				<button class="button-secondary edd_add_repeatable">
					<?php
					/* translators: %s: Download singular label */
					echo esc_html( sprintf( __( 'Add %s', 'easy-digital-downloads' ), edd_get_label_singular() ) );
					?>
				</button>
			</div>
		</div>
	</div>
</div>
