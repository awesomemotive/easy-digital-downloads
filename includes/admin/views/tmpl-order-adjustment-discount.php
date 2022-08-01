<?php
/**
 * Order Overview: Adjustment Discount
 *
 * @package     EDD
 * @subpackage  Admin/Views
 * @copyright   Copyright (c) 2020, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

$view_url = edd_get_admin_url(
	array(
		'page'       => 'edd-discounts',
		'edd-action' => 'edit_discount',
	)
);
?>

<td></td>

<td class="column-primary" colspan="{{ data.config.colspan }}">
	<div class="removable">
		<# if ( true === data.state.isAdding ) { #>
		<button class="button-link delete">
			<span class="dashicons dashicons-no"></span>
			<span class="screen-reader-text"><?php printf( __( 'Remove discount', 'easy-digital-downloads' ) ); ?></span>
		</button>
		<# } #>

		<div>
			<a href="<?php echo esc_url( $view_url ); ?>&discount={{ data.typeId }}">{{ data.description }}</a>
			<br />
			<small>
				<?php esc_html_e( 'Discount', 'easy-digital-downloads' ); ?>
			</small>
		</div>
	</div>
</td>

<td class="column-right" data-colname="<?php esc_html_e( 'Amount', 'easy-digital-downloads' ); ?>">
	&ndash;{{ data.totalCurrency }}
</td>

<input type="hidden" value="{{ data.typeId }}" name="discounts[{{ data.id }}][type_id]" />
<input type="hidden" value="{{ data.description }}" name="discounts[{{ data.id }}][code]" />
<input type="hidden" value="{{ data.subtotal }}" name="discounts[{{ data.id }}][subtotal]" />
<input type="hidden" value="{{ data.total }}" name="discounts[{{ data.id }}][total]" />
