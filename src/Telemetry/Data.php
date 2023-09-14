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

class Data {

	/**
	 * The unique anonymized site ID.
	 *
	 * @var string
	 */
	private $id;

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

	/**
	 * Gets the unique site ID.
	 * This is generated from the home URL and two random pieces of data
	 * to create a hashed site ID that anonymizes the site data.
	 *
	 * @since 3.1.1
	 * @return string
	 */
	private function get_id() {
		$this->id = get_option( 'edd_telemetry_uuid' );
		if ( $this->id ) {
			return $this->id;
		}
		$home_url = get_home_url();
		$uuid     = wp_generate_uuid4();
		$today    = gmdate( 'now' );
		$this->id = md5( $home_url . $uuid . $today );

		update_option( 'edd_telemetry_uuid', $this->id, false );

		return $this->id;
	}
}
