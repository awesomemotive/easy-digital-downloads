<?php
/**
 * class-sample-upgrade-notice.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     2.10.5
 */

namespace EDD\Admin\Promos\Notices;

class Sample_Upgrade_Notice extends Notice {

	const DISMISS_DURATION = MINUTE_IN_SECONDS;

	/**
	 * @inheritDoc
	 */
	protected function _display() {
		esc_html_e( 'Test content.', 'easy-digital-downloads' );
	}
}
