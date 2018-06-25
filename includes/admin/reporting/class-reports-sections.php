<?php
/**
 * Reports Sections Class.
 *
 * @package     EDD
 * @subpackage  Admin
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

namespace EDD\Admin;

use EDD\Reports as Reports;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Main class for creating a vertically tabbed UI
 *
 * @since 3.0
 */
class Reports_Sections extends Sections {

	/**
	 * Output the contents
	 *
	 * @since 3.0
	 */
	public function display() {
		$use_js = ! empty( $this->use_js )
			? ' use-js'
			: '';

		$report = Reports\get_report( $this->current_section );

		if ( ! is_wp_error( $report ) ) {
			Reports\display_filters( $report );
		}

		ob_start(); ?>

		<div class="edd-sections-wrap">
			<div class="edd-vertical-sections<?php echo $use_js; ?>">
				<ul class="section-nav">
					<?php echo $this->get_all_section_links(); ?>
				</ul>

				<div class="section-wrap">
					<?php echo $this->get_all_section_contents(); ?>
				</div>
				<br class="clear" />
			</div>
			<?php $this->nonce_field();

			if ( ! empty( $this->item ) ) : ?>

				<input type="hidden" name="edd-item-id" value="<?php echo esc_attr( $this->item->id ); ?>" />

			<?php endif; ?>
		</div>

		<?php

		// Output current buffer
		echo ob_get_clean();
	}
}
