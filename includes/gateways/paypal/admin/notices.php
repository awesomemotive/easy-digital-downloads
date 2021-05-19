<?php
/**
 * PayPal Admin Notices
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     2.11
 */

namespace EDD\PayPal\Admin;

add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Bail if this notice has already been dismissed.
	if ( get_user_meta( get_current_user_id(), '_edd_paypal_commerce_dismissed' ) ) {
		return;
	}

	$enabled_gateways = array_keys( edd_get_enabled_payment_gateways() );
	$enabled_gateways = array_diff( $enabled_gateways, array( 'paypal_commerce' ) );

	// Show a notice if any PayPal gateway is enabled, other than PayPal Commerce.
	$paypal_gateways = array_filter( $enabled_gateways, function( $gateway ) {
		return false !== strpos( strtolower( $gateway ), 'paypal' );
	} );

	if ( ! $paypal_gateways ) {
		return;
	}

	$dismiss_url = add_query_arg( array( 'edd_action' => 'dismiss_notices', 'edd_notice' => 'paypal_commerce' ) );
	$setup_url = add_query_arg( array(
		'post_type' => 'download',
		'page'      => 'edd-settings',
		'tab'       => 'gateways',
		'section'   => 'paypal_commerce'
	), admin_url( 'edit.php' ) );

	?>
	<div class="notice notice-info">
		<h2><?php esc_html_e( 'Enable the new PayPal gateway for Easy Digital Downloads' ); ?></h2>
		<p>
			<?php
			printf(
				/* Translators: %1$s opening anchor tag; %2$s closing anchor tag */
				esc_html__( 'A new, improved PayPal experience is now available in Easy Digital Downloads. %1$sClick here to activate it.%2$s', 'easy-digital-downloads' ),
				'<a href="' . esc_url( $setup_url ) . '">',
				'</a>'
			)
			?>
		</p>
		<p><a href="<?php echo esc_url( $dismiss_url ); ?>"><?php esc_html_e( 'Dismiss Notice', 'easy-digital-downloads' ); ?></a></p>
	</div>
	<?php
} );
