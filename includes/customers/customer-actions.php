<?php
/**
 * Customer Actions.
 *
 * This file contains all of the first class functions for interacting with a
 * customer or its related meta data.
 *
 * @package     EDD
 * @subpackage  Actions
 * @copyright   Copyright (c) 2020, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

/**
 * When a new primary address is added to the database, any other primary addresses should be demoted.
 *
 * @param string $old_value The previous value of `is_primary`.
 * @param string $new_value The new value of `is_primary`.
 * @param int $item_id      The address ID in the edd_customer_addresses table.
 * @return void
 */
function edd_demote_customer_primary_addresses( $old_value, $new_value, $item_id ) {
	if ( ! $new_value ) {
		return;
	}
	$customer_addresses = new EDD\Database\Queries\Customer_Address();
	$address            = $customer_addresses->get_item( $item_id );
	if ( ! $address->is_primary ) {
		return;
	}
	$previous_primary_addresses = $customer_addresses->query(
		array(
			'id__not_in'  => array( $item_id ),
			'fields'      => 'ids',
			'customer_id' => $address->customer_id,
			'is_primary'  => true,
		)
	);
	if ( empty( $previous_primary_addresses ) ) {
		return;
	}
	foreach ( $previous_primary_addresses as $previous ) {
		edd_update_customer_address( $previous, array( 'is_primary' => false ) );
	}
}
add_action( 'edd_transition_customer_address_is_primary', 'edd_demote_customer_primary_addresses', 10, 3 );
