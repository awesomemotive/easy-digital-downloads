<?php
/**
 * Customer Actions.
 *
 * Hooks that are triggered when customer-based actions occur.
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
	$address                    = edd_fetch_customer_address( $item_id );
	$previous_primary_addresses = edd_get_customer_addresses(
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

/**
 * Updates the email address of a customer record when the email on a user is updated.
 *
 * @since 2.4.0
 *
 * @param int     $user_id       User ID.
 * @param WP_User $old_user_data Object containing user's data prior to update.
 *
 * @return void
 */
function edd_update_customer_email_on_user_update( $user_id, $old_user_data ) {
	$user = get_userdata( $user_id );

	// Bail if the email address didn't actually change just now.
	if ( empty( $user ) || $user->user_email === $old_user_data->user_email ) {
		return;
	}

	$customer = edd_get_customer_by( 'user_id', $user_id );

	if ( empty( $customer ) || $user->user_email === $customer->email ) {
		return;
	}

	// Bail if we have another customer with this email address already.
	if ( edd_get_customer_by( 'email', $user->user_email ) ) {
		return;
	}

	$success = edd_update_customer( $customer->id, array( 'email' => $user->user_email ) );

	if ( ! $success ) {
		return;
	}

	/**
	 * Triggers after the customer has been successfully updated.
	 *
	 * @param WP_User      $user
	 * @param EDD_Customer $customer
	 */
	do_action( 'edd_update_customer_email_on_user_update', $user, $customer );
}
add_action( 'profile_update', 'edd_update_customer_email_on_user_update', 10, 2 );
