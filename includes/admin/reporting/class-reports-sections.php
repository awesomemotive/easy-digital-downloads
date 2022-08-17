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

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class for creating a vertically tabbed UI for reports.
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

		$role           = $this->use_js ? 'tablist' : 'menu';
		$range          = \EDD\Reports\get_dates_filter_range();
		$date_format    = get_option('date_format');
		$dates          = \EDD\Reports\parse_dates_for_range( $range );
		$relative_dates = \EDD\Reports\parse_relative_dates_for_range( $range );
		?>
		<div class="edd-sections-wrap edd-reports-wrapper">

			<div class="edd-vertical-sections<?php echo $use_js; ?>">
				<span class="edd-reports-section-label">
					<strong><?php echo esc_html( $dates['start']->format( $date_format ) );?> - <?php echo esc_html( $dates['end']->format( $date_format ) );?></strong>
					<?php echo esc_html__( 'compared to', 'easy-digital-downloads' );?>
					<strong><?php echo esc_html( $relative_dates['start']->format( $date_format ) );?> - <?php echo esc_html( $relative_dates['end']->format( $date_format ) );?></strong>
				</span>

				<ul class="section-nav" role="<?php echo esc_attr( $role ); ?>">
					<?php echo $this->get_all_section_links(); ?>
				</ul>

				<div class="section-wrap">

					<?php echo $this->get_all_section_contents(); ?>
				</div>
				<br class="clear">
			</div>
		</div>
		<?php
	}
}
