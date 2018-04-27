<?php
/**
 * Privacy Functions
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.9.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Given a string, mask it with the * character.
 *
 * First and last character will remain with the filling characters being changed to *. One Character will
 * be left in tact as is. Two character strings will has the first character remain and the second be a *.
 *
 * @since 2.9.2
 * @param string $string
 *
 * @return string
 */
function edd_mask_string( $string = '' ) {

	if ( empty( $string ) ) {
		return '';
	}

	$first_char  = substr( $string, 0, 1 );
	$last_char   = substr( $string, -1, 1 );

	$masked_string = $string;

	if ( strlen( $string ) > 2 ) {

		$total_stars = strlen( $string ) - 2;
		$masked_string = $first_char . str_repeat( '*', $total_stars ) . $last_char;

	} elseif ( strlen( $string ) === 2 ) {

		$masked_string = $first_char . '*';

	}



	return $masked_string;

}

/**
 * Given a domain, mask it with the * character.
 *
 * TLD parts will remain in tact (.com, .co.uk, etc). All subdomains will be masked t**t.e*****e.co.uk.
 *
 * @since 2.9.2
 * @param string $domain
 *
 * @return string
 */
function edd_mask_domain( $domain = '' ) {

	if ( empty( $domain ) ) {
		return '';
	}

	$domain_parts = explode( '.', $domain );

	if ( count( $domain_parts ) === 2 ) {

		// We have a single entry tld like .org or .com
		$domain_parts[0] = edd_mask_string( $domain_parts[0] );

	} else {

		$part_count     = count( $domain_parts );
		$possible_cctld = strlen( $domain_parts[ $part_count - 2 ] ) <= 3 ? true : false;

		$mask_parts = $possible_cctld ? array_slice( $domain_parts, 0, $part_count - 2 ) : array_slice( $domain_parts, 0, $part_count - 1 );

		$i = 0;
		while ( $i < count( $mask_parts ) ) {
			$domain_parts[ $i ] = edd_mask_string( $domain_parts[ $i ]);
			$i++;
		}

	}

	return implode( '.', $domain_parts );
}

/**
 * Given an email address, mask the name and domain according to domain and string masking functions.
 *
 * Will result in an email address like a***n@e*****e.org for admin@example.org.
 *
 * @since 2.9.2
 * @param $email_address
 *
 * @return string
 */
function edd_pseudo_mask_email( $email_address ) {
	if ( ! is_email( $email_address ) ) {
		return $email_address;
	}

	$email_parts = explode( '@', $email_address );
	$name        = edd_mask_string( $email_parts[0] );
	$domain      = edd_mask_domain( $email_parts[1] );

	$email_address = $name . '@' . $domain;


	return $email_address;
}