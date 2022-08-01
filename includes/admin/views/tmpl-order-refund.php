<?php
/**
 * Order Overview: Refunds
 *
 * @package     EDD
 * @subpackage  Admin/Views
 * @copyright   Copyright (c) 2020, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

$view_url = edd_get_admin_url( array(
	'page' => 'edd-payment-history',
	'view' => 'view-refund-details',
) );
?>

<td></td>

<td colspan="{{ data.config.colspan }}" class="column-primary">
	<?php
	echo wp_kses(
		sprintf(
			/* translators: %s Refund number, linked to Refund record. */
			__( 'Refund: %s', 'easy-digital-downloads' ),
			'<a href="' . esc_url( $view_url ) . '&id={{ data.id }}">{{ data.number }}</a>'
		),
		array(
			'a' => array(
				'href' => true,
			),
		)
	);
	?>
	<br />
	<small>{{ data.dateCreated }}</small>
</td>

<td class="column-right" data-colname="<?php esc_html_e( 'Total', 'easy-digital-downloads' ); ?>">
	{{ data.totalCurrency }}
</td>
