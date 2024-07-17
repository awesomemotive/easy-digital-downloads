<?php
/**
 * Email Summary Top Products
 */
?>
<div class="table-data-holder pull-down-25 " style="margin-top: 25px; ">
	<div class="table-top-icon align-c" style="text-align: center;">
		<img src="<?php echo esc_url( 'https://plugin.easydigitaldownloads.com/cdn/summaries/icon-top-products.png' ); ?>" alt="#" title="#" width="28" height="28">
	</div>

	<div class="table-top-title align-c" style="text-align: center; font-size: 14px; line-height: 18px; font-weight: 600; color: #1F2937; display: block; margin-top: 0px; margin-bottom: 12px;">
		<?php echo esc_html( __( 'Top 5 Products by Revenue', 'easy-digital-downloads' ) ); ?>
	</div>

	<table class="top-products" style="border-collapse: collapse; width: 100%; font-size: 12px; line-height: 15px; color: #4B5563;" width="100%">
		<tr>
			<th style="font-weight: 600; border-bottom: 1px solid #E5E7EB; text-align: left; border-right: none; padding: 10px 0px; font-size: 12px; line-height: 15px;" align="left"><?php echo esc_html( __( 'Product', 'easy-digital-downloads' ) ); ?></th>
			<th style="font-weight: 600; border-bottom: 1px solid #E5E7EB; border-right: none; padding: 10px 0px; font-size: 12px; line-height: 15px; text-align: right;" align="right"><?php echo esc_html( __( 'Gross Revenue', 'easy-digital-downloads' ) ); ?></th>
		</tr>
		<?php
		$counter = 1;
		foreach ( $dataset['top_selling_products'] as $product ) :
			if ( ! $product->object instanceof \EDD_Download ) {
				continue;
			}

			$title   = $product->object->post_title;
			$revenue = edd_currency_filter( edd_format_amount( $product->total ) );
			?>
			<tr>
				<td style="font-size: 12px; color: #4B5563; font-weight: 400; text-align: left; padding: 9px 0px; border-bottom: 1px solid #F0F1F4;" align="left"><?php echo esc_html( $counter ); ?>. <?php echo esc_html( $title ); ?></td>
				<td style="font-size: 12px; color: #4B5563; font-weight: 400; padding: 9px 0px; border-bottom: 1px solid #F0F1F4; text-align: right;" align="right"><?php echo esc_html( $revenue ); ?></td>
			</tr>
			<?php
			++$counter;
		endforeach;
		?>
	</table>
</div>
