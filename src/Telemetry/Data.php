<?php
/**
 * Telemetry Data.
 *
 * Gets the data to send to our telemetry server.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2023, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.1.1
 */

namespace EDD\Telemetry;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class Data
 *
 * @since 3.1.1
 */
class Data {
	use Traits\Anonymize;

	/**
	 * Gets all of the site data.
	 *
	 * @return false|array
	 */
	public function get() {
		$data = array(
			'id' => $this->get_id(),
		);

		$classes = array(
			'environment'  => new Environment(),
			'integrations' => new Integrations(),
			'licenses'     => new Licenses(),
			'sales'        => new Orders(),
			'refunds'      => new Orders( 'refund' ),
			'settings'     => new Settings(),
			'stats'        => new Stats(),
			'products'     => new Products(),
		);

		foreach ( $classes as $key => $class ) {
			$data[ $key ] = $class->get();
		}

		return $data;
	}
}
