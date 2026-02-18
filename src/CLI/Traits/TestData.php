<?php
/**
 * Test Data Trait
 *
 * @package EDD\CLI\Traits
 * @copyright 2026, Sandhills Development, LLC
 * @license https://gnu.org/licenses/gpl-2.0.html
 * @since 3.6.5
 */

namespace EDD\CLI\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Test Data Trait
 *
 * @since 3.6.5
 */
trait TestData {

	/**
	 * Gets a random first name.
	 *
	 * @return string
	 */
	protected function get_fname() {
		$names = array(
			'Ilse',
			'Emelda',
			'Aurelio',
			'Chiquita',
			'Cheryl',
			'Norbert',
			'Neville',
			'Wendie',
			'Clint',
			'Synthia',
			'Tobi',
			'Nakita',
			'Marisa',
			'Maybelle',
			'Onie',
			'Donnette',
			'Henry',
			'Sheryll',
			'Leighann',
			'Wilson',
		);

		return $names[ rand( 0, ( count( $names ) - 1 ) ) ];
	}

	/**
	 * Gets a random last name.
	 *
	 * @return string
	 */
	protected function get_lname() {
		$names = array(
			'Warner',
			'Roush',
			'Lenahan',
			'Theiss',
			'Sack',
			'Troutt',
			'Vanderburg',
			'Lisi',
			'Lemons',
			'Christon',
			'Kogut',
			'Broad',
			'Wernick',
			'Horstmann',
			'Schoenfeld',
			'Dolloff',
			'Murph',
			'Shipp',
			'Hursey',
			'Jacobi',
		);

		return $names[ rand( 0, ( count( $names ) - 1 ) ) ];
	}

	/**
	 * Gets a random domain.
	 *
	 * @return string
	 */
	protected function get_domain() {
		$domains = array(
			'example',
			'edd',
			'rcp',
			'affwp',
		);

		return $domains[ rand( 0, ( count( $domains ) - 1 ) ) ];
	}

	/**
	 * Gets a random TLD.
	 *
	 * @return string
	 */
	protected function get_tld() {
		$tlds = array(
			'local',
			'test',
			'example',
			'localhost',
			'invalid',
		);

		return $tlds[ rand( 0, ( count( $tlds ) - 1 ) ) ];
	}

	/**
	 * Generate a random IP address.
	 *
	 * @since 3.6.5
	 * @return string Random IP address.
	 */
	private function generate_random_ip() {
		return sprintf(
			'%d.%d.%d.%d',
			wp_rand( 1, 255 ),
			wp_rand( 0, 255 ),
			wp_rand( 0, 255 ),
			wp_rand( 1, 255 )
		);
	}

	/**
	 * Generate a random user agent string.
	 *
	 * @since 3.6.5
	 * @return string Random user agent.
	 */
	private function generate_random_user_agent() {
		$user_agents = array(
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15',
			'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
			'Mozilla/5.0 (iPhone; CPU iPhone OS 17_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Mobile/15E148 Safari/604.1',
			'Mozilla/5.0 (iPad; CPU OS 17_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Mobile/15E148 Safari/604.1',
			'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.6099.43 Mobile Safari/537.36',
		);

		return $user_agents[ array_rand( $user_agents ) ];
	}

	/**
	 * Generate a random referrer URL.
	 *
	 * @since 3.6.5
	 * @return string Random referrer URL.
	 */
	private function generate_random_referrer() {
		$referrers = array(
			'https://google.com/',
			'https://facebook.com/',
			'https://twitter.com/',
			'https://linkedin.com/',
			'https://reddit.com/',
			'https://youtube.com/',
		);

		return $referrers[ array_rand( $referrers ) ];
	}

	/**
	 * Generate a random email address.
	 *
	 * @since 3.6.5
	 * @return string Random email address.
	 */
	private function generate_random_email() {
		$fname  = $this->get_fname();
		$lname  = $this->get_lname();
		$domain = $this->get_domain();
		$tld    = $this->get_tld();

		return $fname . '.' . $lname . '@' . $domain . '.' . $tld;
	}
}
