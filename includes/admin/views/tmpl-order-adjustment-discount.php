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
		'edd-action' => 'edit_discount'
	),
);
?>

<td></td>

<th class="column-name" colspan="{{ data.config.colspan }}">
	<# if ( true === data.state.isAdding ) { #>
	<button class="button-link delete" id="remove">
		<span class="dashicons dashicons-no"></span>
	</button>
	<# } #>

	<?php esc_html_e( 'Discount', 'easy-digital-downloads' ); ?>
	<br />
	<small>
		<a href="<?php echo esc_url( $view_url ); ?>&discount={{ data.typeId }}">{{ data.description }}</a>
	</small>
</th>

<td class="column-right">
	&ndash;{{ data.totalCurrency }}
</td>

<input type="hidden" value="{{ data.typeId }}" name="discounts[{{ data.cid }}][type_id]" />
<input type="hidden" value="{{ data.amount }}" name="discounts[{{ data.cid }}][amount]" />
<input type="hidden" value="{{ data.description }}" name="discounts[{{ data.cid }}][code]" />
