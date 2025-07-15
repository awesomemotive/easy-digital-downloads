<?php
/**
 * Template for the VAT check result.
 *
 * @var array $args The template arguments.
 *
 * @package     EDD\Templates\Checkout
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args,
	array(
		'vat_details'  => null,
		'result_text'  => '',
		'result_class' => '',
	)
);

if ( ! $args['vat_details'] ) {
	return;
}

$css_classes = array(
	'edd-alert',
	$args['result_class'],
);
if ( 'edd-vat__check--error' === $args['result_class'] ) {
	$css_classes[] = 'edd-alert-error';
} else {
	$css_classes[] = 'edd-alert-success';
	$css_classes[] = 'edd_success';
}

?>
<div id="edd-vat-check-result" class="<?php echo esc_attr( implode( ' ', $css_classes ) ); ?>" data-valid="<?php echo esc_attr( $args['vat_details']->is_valid() ); ?>" data-country="<?php echo esc_attr( $args['vat_details']->country_code ); ?>">
	<?php echo esc_html( $args['result_text'] ); ?>
</div>
