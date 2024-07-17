<?php
/**
 * Email Functions
 *
 * @package     EDD
 * @subpackage  Emails
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Database\Queries\Email as Query;

/**
 * Retrieves the email template registry.
 *
 * @since 3.3.0
 * @return EDD\Emails\Templates\Registry $emails The email templates
 */
function edd_get_email_registry() {
	return EDD\Emails\Templates\Registry::get_instance();
}

/**
 * Retrieves the emails for which admin notifications are sent to (these can be
 * changed in the EDD Settings)
 *
 * @since 1.0
 * @param null|EDD\Orders\Order $email Optional. The email object. Default: null. Added in 3.2.3.
 * @return array $emails The emails to send admin notices to
 */
function edd_get_admin_notice_emails( $email = null ) {
	$emails = edd_get_option( 'admin_notice_emails', false );
	$emails = strlen( trim( $emails ) ) > 0 ? $emails : get_bloginfo( 'admin_email' );
	$emails = array_map( 'trim', explode( "\n", $emails ) );

	/**
	 * Filters the emails for which admin notifications are sent.
	 *
	 * @param array $emails The emails to send admin notices to.
	 * @param null|EDD\Orders\Order $email Optional. The email object. Default: null. Added in 3.2.3.
	 */
	return apply_filters( 'edd_admin_notice_emails', $emails, $email );
}

/**
 * Checks whether admin sale notices are disabled
 *
 * @since 1.5.2
 *
 * @param int $payment_id The payment ID
 * @return mixed
 */
function edd_admin_notices_disabled( $payment_id = 0 ) {
	$email  = edd_get_email( 'admin_order_notice' );
	$status = $email && $email->is_enabled();

	return (bool) apply_filters( 'edd_admin_notices_disabled', empty( $status ), $payment_id );
}

/**
 * Get an email.
 *
 * @since 3.3.0
 *
 * @param int|string $email_id The email ID. This can also be the email_id because that's really how we identify emails.
 * @return \EDD\Emails\Email|false
 */
function edd_get_email( $email_id = 0 ) {
	$query = new Query();
	if ( ! is_numeric( $email_id ) ) {
		return $query->get_item_by( 'email_id', $email_id );
	}

	return $query->get_item( $email_id );
}

/**
 * Adds an email to the database.
 *
 * @since 3.3.0
 * @param array $args The query arguments.
 * @return int|false
 */
function edd_add_email( $args = array() ) {
	$query = new Query();

	return $query->add_item( $args );
}

/**
 * Updates an email in the database.
 *
 * @since 3.3.0
 * @param int   $email_id The email ID.
 * @param array $args     The query arguments.
 * @return bool
 */
function edd_update_email( $email_id = 0, $args = array() ) {
	$query = new Query();

	return $query->update_item( $email_id, $args );
}

/**
 * Deletes an email from the database.
 *
 * @since 3.3.0
 * @param int $email_id The email ID.
 * @return bool
 */
function edd_delete_email( $email_id = 0 ) {
	$query = new Query();

	return $query->delete_item( $email_id );
}

/**
 * Gets an email by a field.
 *
 * @since 3.3.0
 * @param string $field The field to query by.
 * @param string $value The value to query by.
 * @return \EDD\Emails\Email
 */
function edd_get_email_by( $field = '', $value = '' ) {
	$query = new Query();

	return $query->get_item_by( $field, $value );
}

/**
 * Gets emails.
 *
 * @since 3.3.0
 * @param array $args The query arguments.
 * @return \EDD\Emails\Email[]
 */
function edd_get_emails( $args = array() ) {

	$r     = wp_parse_args(
		$args,
		array(
			'number' => 300,
		)
	);
	$query = new Query();

	return $query->query( $r );
}

/**
 * Add meta data field to an email.
 *
 * @since 3.3.0
 *
 * @param int    $email_id   Order ID.
 * @param string $meta_key   Meta data name.
 * @param mixed  $meta_value Meta data value. Must be serializable if non-scalar.
 * @param bool   $unique     Optional. Whether the same key should not be added. Default false.
 *
 * @return int|false Meta ID on success, false on failure.
 */
function edd_add_email_meta( $email_id, $meta_key, $meta_value, $unique = false ) {
	return add_metadata( 'edd_email', $email_id, $meta_key, $meta_value, $unique );
}

/**
 * Remove meta data matching criteria from an email.
 *
 * You can match based on the key, or key and value. Removing based on key and value, will keep from removing duplicate
 * meta data with the same key. It also allows removing all meta data matching key, if needed.
 *
 * @since 3.3.0
 *
 * @param int    $email_id   Order ID.
 * @param string $meta_key   Meta data name.
 * @param mixed  $meta_value Optional. Meta data value. Must be serializable if non-scalar. Default empty.
 *
 * @return bool True on success, false on failure.
 */
function edd_delete_email_meta( $email_id, $meta_key, $meta_value = '' ) {
	return delete_metadata( 'edd_email', $email_id, $meta_key, $meta_value );
}

/**
 * Retrieve email meta field for an email.
 *
 * @since 3.3.0
 *
 * @param int    $email_id  Order ID.
 * @param string $key       Optional. The meta key to retrieve. By default, returns data for all keys. Default empty.
 * @param bool   $single    Optional, default is false. If true, return only the first value of the specified meta_key.
 *                          This parameter has no effect if meta_key is not specified.
 *
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
 */
function edd_get_email_meta( $email_id, $key = '', $single = false ) {
	return get_metadata( 'edd_email', $email_id, $key, $single );
}

/**
 * Update email meta field based on email ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and email ID.
 *
 * If the meta field for the email does not exist, it will be added.
 *
 * @since 3.3.0
 *
 * @param int    $email_id   Email ID.
 * @param string $meta_key   Meta data key.
 * @param mixed  $meta_value Meta data value. Must be serializable if non-scalar.
 * @param mixed  $prev_value Optional. Previous value to check before removing. Default empty.
 *
 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
 */
function edd_update_email_meta( $email_id, $meta_key, $meta_value, $prev_value = '' ) {
	return update_metadata( 'edd_email', $email_id, $meta_key, $meta_value, $prev_value );
}

/**
 * Delete everything from email meta matching meta key.
 *
 * @since 3.3.0
 * @param string $meta_key Key to search for when deleting.
 * @return bool Whether the email meta key was deleted from the database.
 */
function edd_delete_email_meta_by_key( $meta_key ) {
	return delete_metadata( 'edd_email', null, $meta_key, '', true );
}
