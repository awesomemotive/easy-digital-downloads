<?php
/**
 * Filters the WordPress debug_information array to add EDD's data.
 *
 * @since 3.1.2
 * @package EDD\Admin\SiteHealth
 */

namespace EDD\Admin\SiteHealth;

/**
 * Registers the EDD information for the Site Health.
 *
 * @since 3.1.2
 */
class Information implements \EDD\EventManagement\SubscriberInterface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.1.2
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'admin_head-site-health.php' => 'maybe_filter_debug',
		);
	}

	/**
	 * Adds the EDD filters to the debug information.
	 * Additionally, removes other filters on the information if using the
	 * EDD system info link.
	 *
	 * @since 3.1.2
	 * @return void
	 */
	public function maybe_filter_debug() {
		if ( ! empty( $_GET['edd'] ) && 'filter' === $_GET['edd'] ) {
			remove_all_filters( 'debug_information' );
		}
		add_filter( 'debug_information', array( $this, 'get_data' ) );
	}

	/**
	 * Gets the array of EDD sections for the Site Health.
	 *
	 * @since 3.1.2
	 * @param array $information The debug information.
	 * @return array
	 */
	public function get_data( $information ) {
		return array_merge( $information, $this->get_edd_data() );
	}

	/**
	 * Gets all of the EDD data for the Site Health.
	 *
	 * @since 3.1.4
	 * @return array
	 */
	private function get_edd_data() {
		$collectors = array(
			'edd_general'   => new General(),
			'edd_tables'    => new Tables(),
			'edd_pages'     => new Pages(),
			'edd_templates' => new Templates(),
			'edd_gateways'  => new Gateways(),
			'edd_taxes'     => new Taxes(),
			'edd_sessions'  => new Sessions(),
		);

		$information = array();
		foreach ( $collectors as $key => $class ) {
			$information[ $key ] = $class->get();
		}

		/**
		 * Allow extensions to add their own debug information that's specific to EDD.
		 *
		 * @since 3.1.4
		 * @param array $information The debug information.
		 */
		return apply_filters( 'edd_debug_information', $information );
	}
}
