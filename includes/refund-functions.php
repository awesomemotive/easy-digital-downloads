<?php
/**
 * Refund Functions
 *
 * This file contains functions related to refunds.
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Return array of refundability types.
 *
 * @since 3.0
 *
 * @return array
 */
function edd_get_refundability_types() {
	return (array) apply_filters( 'edd_get_refund_types', array(
		'refundable'    => __( 'Refundable',     'easy-digital-downloads' ),
		'nonrefundable' => __( 'Non-Refundable', 'easy-digital-downloads' )
	) );
}
