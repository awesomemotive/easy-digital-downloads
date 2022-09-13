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
 * Is intended to be used when adding a customer directly with edd_add_customer function.
 *
 * Because the edd_add_customer function only directly interacts with the customers DB table, we may
 * need to do some additional actions like adding items to the customer email addresses table.
 *
 * @since 3.0.4
 *
 * @param int   $customer_id The customer ID that was added.
 * @param array $data        The data passed in to add the customer.
 */
function edd_process_customer_added( $customer_id, $data ) {
	// Make sure we add a new primary email address to the email addresses table.
	edd_add_customer_email_address(
		array(
			'customer_id' => $customer_id,
			'email'       => $data['email'],
			'type'        => 'primary',
		)
	);
}
add_action( 'edd_customer_added', 'edd_process_customer_added', 10, 2 );

/**
 * Is intended to be used when updating a customer directly with edd_add_customer function.
 *
 * Because the edd_update_customer function only directly interacts with the customers DB table, we may
 * need to do some additional actions like managing email addresses.
 *
 * @since 3.0.4
 *
 * @param int          $customer_id       The customer ID being updated.
 * @param array        $data              The data passed in to update the customer with.
 * @param EDD_Customer $prev_customer_obj The customer object, prior to these updates.
 */
function edd_process_customer_updated( $customer_id, $data, $prev_customer_obj ) {
	$customer      = edd_get_customer( $customer_id );
	$email_updated = false;

	// Process a User ID change.
	if ( intval( $customer->user_id ) !== intval( $prev_customer_obj->user_id ) ) {
		// Attach the User Email to the customer as well.
		$user = new WP_User( $customer->user_id );
		if ( $user instanceof WP_User ) {

			// Only update this if it doesn't match already.
			if ( $customer->email !== $user->user_email ) {
				$customers = new EDD\Database\Queries\Customer();
				$customers->update_item( $customer_id, array( 'email' => $user->user_email ) );
			}

			// Our transition hook for the type will handle demoting any other email addresses.
			edd_add_customer_email_address(
				array(
					'customer_id' => $customer->id,
					'email'       => $user->user_email,
					'type'        => 'primary',
				)
			);

			$email_updated = true;
		}

		// Remove the old user email from this account.
		$previous_user = new WP_User( $prev_customer_obj->user_id );
		if ( $previous_user instanceof WP_User ) {
			$existing_email_addresses = edd_get_customer_email_addresses(
				array(
					'customer_id' => $customer->id,
					'email'       => $previous_user->user_email,
				)
			);

			if ( ! empty( $existing_email_addresses ) ) {
				// Should only be one, but let's foreach to be safe.
				foreach ( $existing_email_addresses as $existing_address ) {
					edd_delete_customer_email_address( $existing_address->id );
				}
			}
		}

		// Update some payment meta if we need to.
		$order_ids = edd_get_orders( array( 'customer_id' => $customer->id, 'number' => 9999999 ) );

		foreach ( $order_ids as $order_id ) {
			edd_update_order( $order_id, array( 'user_id' => $customer->user_id ) );
		}
	}

	// If the email address changed, set the new one as primary.
	if ( false === $email_updated && $prev_customer_obj->email !== $customer->email ) {

		// Our transition hook for the type will handle demoting any other email addresses.
		edd_add_customer_email_address(
			array(
				'customer_id' => $customer->id,
				'email'       => $customer->email,
				'type'        => 'primary',
			)
		);
	}
}
add_action( 'edd_customer_updated', 'edd_process_customer_updated', 10, 3 );

/**
 * When a new primary email address is added to the database, any other primary email addresses should be demoted.
 *
 * @param string $old_value The previous value of `type`.
 * @param string $new_value The new value of `type`.
 * @param int    $item_id   The address ID in the edd_customer_email_addresses table.
 * @return void
 */
function edd_demote_customer_primary_email_addresses( $old_value, $new_value, $item_id ) {
	if ( ! $new_value ) {
		return;
	}

	// If we're not setting the `type` to `primary` we do not need to make any adjustments.
	if ( 'primary' !== $new_value ) {
		return;
	}

	$email_address                    = edd_get_customer_email_address( $item_id );
	$previous_primary_email_addresses = edd_get_customer_email_addresses(
		array(
			'id__not_in'  => array( $item_id ),
			'fields'      => 'ids',
			'customer_id' => $email_address->customer_id,
			'type'        => 'primary',
		)
	);

	if ( empty( $previous_primary_email_addresses ) ) {
		return;
	}

	foreach ( $previous_primary_email_addresses as $previous ) {
		edd_update_customer_email_address( $previous, array( 'type' => 'secondary' ) );
	}
}
add_action( 'edd_transition_customer_email_address_type', 'edd_demote_customer_primary_email_addresses', 10, 3 );

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
