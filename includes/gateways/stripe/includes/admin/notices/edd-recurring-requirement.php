<?php
/**
 * Notice: edd-recurring-requirement
 *
 * @package EDD_Stripe\Admin\Notices
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 2.8.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<p>
	<strong><?php esc_html_e( 'Credit card payments with Stripe are currently disabled.', 'easy-digital-downloads' ); ?></strong>
</p>

<p>
	<?php
		echo wp_kses(
			sprintf(
				/* translators: %1$s Opening strong tag, do not translate. %2$s Closing strong tag, do not translate. %3$s Opening code tag, do not translate. %4$s Closing code tag, do not translate. */
				__( 'To continue accepting credit card payments with Stripe please update %1$sEasy Digital Downloads - Recurring Payments%2$s to version %3$s2.10%4$s or higher.', 'easy-digital-downloads' ),
				'<strong>',
				'</strong>',
				'<code>',
				'</code>'
			),
			array(
				'code'   => true,
				'strong' => true,
			)
		);
	?>
</p>
