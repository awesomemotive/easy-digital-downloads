<?php
/**
 * Order Overview: Adjustment
 *
 * @todo Use a separate view for different types to avoid so many conditionals?
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

	<# if ( 'discount' === data.type ) { #>
		<?php esc_html_e( 'Discount', 'easy-digital-downloads' ); ?>
		<br />
		<small>
			<a href="<?php echo esc_url( $view_url ); ?>&discount={{ data.typeId }}">{{ data.description }}</a>
		</small>
	<# } else { #>
		<# if ( 'credit' === data.type ) { #>
			<?php esc_html_e( 'Order Credit', 'easy-digital-downloads' ); ?>
		<# } else { #>
			<?php esc_html_e( 'Order Fee', 'easy-digital-downloads' ); ?>
		<# } #>

		<# if ( '' !== data.description ) { #>
			<br />
			<small>
				{{ data.description }}
			</small>
		<# } #>
	<# } #>
</th>

<td class="column-right">
	<# if ( 'discount' === data.type || 'credit' === data.type ) { #>&ndash;<# } #>{{ data.totalCurrency }}
</td>
