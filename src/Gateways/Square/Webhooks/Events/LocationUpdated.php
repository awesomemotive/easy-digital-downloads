<?php
/**
 * The Square Location Updated event.
 *
 * @package     EDD\Gateways\Square\Webhooks\Events
 * @copyright   Copyright (c) 2025, Easy Digital Downloads
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square\Webhooks\Events;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Gateways\Square\Helpers\Setting;
use EDD\Gateways\Square\Helpers\Api;

/**
 * The Square Location Updated event.
 */
class LocationUpdated extends Event {

	/**
	 * Process the event.
	 *
	 * @since 3.4.0
	 * @return void
	 * @throws \Exception If the location cannot be retrieved.
	 */
	public function process() {
		edd_debug_log( sprintf( 'Square webhook - Location Updated: %s', $this->data['id'] ) );

		// Retrieve the location from Square, as it doesn't send the name in.
		$location = Api::client()->getLocationsApi()->retrieveLocation( $this->data['id'] );

		if ( ! $location->isSuccess() ) {
			edd_debug_log( sprintf( 'Square webhook - Error retrieving location: %s', $location->getErrors()[0]->getDetail() ) );
			throw new \Exception( $location->getErrors()[0]->getDetail() );
		}

		$location = $location->getResult()->getLocation();

		// Update the locations option.
		$locations                      = Setting::get( 'locations' );
		$locations[ $this->data['id'] ] = $location->getName();
		Setting::set( 'locations', $locations );
	}
}
