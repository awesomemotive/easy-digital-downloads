<?php
/**
 * Integrations: All Access Pass
 *
 * @package EDD_Stripe
 * @since   2.8.0
 */

/**
 * Disables Payment Request Button output if Download has been unlocked with a pass.
 *
 * @since 2.8.0
 *
 * @param bool  $enabled If the Payment Request Button is enabled.
 * @param int   $download_id Current Download ID.
 * @return bool
 */
function edds_all_access_prb_purchase_link_enabled( $enabled, $download_id ) {
	$all_access = edd_all_access_check(
		array(
			'download_id' => $download_id,
		)
	);

	return false === $all_access['success'];
}
add_filter( 'edds_prb_purchase_link_enabled', 'edds_all_access_prb_purchase_link_enabled', 10, 2 );
