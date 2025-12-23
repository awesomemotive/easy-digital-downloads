<?php
/**
 * Easy Digital Downloads Marketing Settings
 *
 * @package     EDD
 * @subpackage  Settings
 * @copyright   Copyright (c) 2023, Easy Digital Downloads
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.1.4
 */

namespace EDD\Admin\Settings\Tabs;

defined( 'ABSPATH' ) || exit;

/**
 * Marketing settings tab class.
 *
 * @since 3.1.4
 */
class Marketing extends Tab {

	/**
	 * Get the ID for this tab.
	 *
	 * @since 3.1.4
	 *
	 * @var string
	 */
	protected $id = 'marketing';

	/**
	 * Register the settings for this tab.
	 *
	 * @since 3.1.4
	 * @return array
	 */
	protected function register() {
		return array(
			'main' => array(
				'recapture'                => array(
					'id'   => 'recapture',
					'name' => __( 'Abandoned Cart Recovery', 'easy-digital-downloads' ),
					'desc' => '',
					'type' => 'recapture',
				),
				'campaign_tracker'         => array(
					'id'      => 'campaign_tracker',
					'name'    => __( 'Campaign Tracking', 'easy-digital-downloads' ),
					'check'   => __( 'Track Google Analytics UTM parameters on orders.', 'easy-digital-downloads' ),
					'type'    => 'checkbox_toggle',
					'options' => array(
						'disabled' => ! edd_is_pro() || edd_is_inactive_pro(),
					),
					'desc'    => $this->get_campaign_tracker_description(),
				),
				'allow_multiple_discounts' => array(
					'id'    => 'allow_multiple_discounts',
					'name'  => __( 'Multiple Discounts', 'easy-digital-downloads' ),
					'check' => __( 'Allow customers to use multiple discounts on the same purchase?', 'easy-digital-downloads' ),
					'type'  => 'checkbox_toggle',
				),
			),
		);
	}

	/**
	 * Get the description for the campaign tracker setting.
	 *
	 * @since 3.6.3
	 * @return string
	 */
	private function get_campaign_tracker_description(): string {
		if ( ! $this->is_admin_page( 'settings', $this->id ) ) {
			return '';
		}

		if ( edd_is_pro() && ! edd_is_inactive_pro() ) {
			return '';
		}

		return sprintf(
			/* translators: 1: opening button tag, 2: closing button tag */
			__( 'Automatically capture UTM campaign data and see which campaigns drive sales when you %1$sUpgrade to Pro%2$s.', 'easy-digital-downloads' ),
			'<button class="edd-pro-upgrade button-link edd-promo-notice__trigger" data-id="campaigntracker">',
			'</button>'
		);
	}
}
