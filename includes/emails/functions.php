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

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Retrieves the emails for which admin notifications are sent to (these can be
 * changed in the EDD Settings)
 *
 * @since 1.0
 * @param null|EDD\Orders\Order $order Optional. The order object. Default: null. Added in 3.2.3.
 * @return array $emails The emails to send admin notices to
 */
function edd_get_admin_notice_emails( $order = null ) {
	$emails = edd_get_option( 'admin_notice_emails', false );
	$emails = strlen( trim( $emails ) ) > 0 ? $emails : get_bloginfo( 'admin_email' );
	$emails = array_map( 'trim', explode( "\n", $emails ) );

	/**
	 * Filters the emails for which admin notifications are sent.
	 *
	 * @param array $emails The emails to send admin notices to.
	 * @param null|EDD\Orders\Order $order Optional. The order object. Default: null. Added in 3.2.3.
	 */
	return apply_filters( 'edd_admin_notice_emails', $emails, $order );
}

/**
 * Checks whether admin sale notices are disabled
 *
 * @since 1.5.2
 *
 * @param int $payment_id
 * @return mixed
 */
function edd_admin_notices_disabled( $payment_id = 0 ) {
	$ret = edd_get_option( 'disable_admin_notices', false );
	return (bool) apply_filters( 'edd_admin_notices_disabled', $ret, $payment_id );
}
