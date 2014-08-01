<?php
/**
 * Admin Options Page
 *
 * @package     EDD
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Options Page
 *
 * Renders the options page contents.
 *
 * @since 1.0
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_options_page() {
	global $edd_options;

	$active_tab = isset( $_GET[ 'tab' ] ) && array_key_exists( $_GET['tab'], edd_get_settings_tabs() ) ? $_GET[ 'tab' ] : 'general';

	ob_start();
	?>
	<div class="wrap">
		<h2 class="nav-tab-wrapper">
			<?php
			foreach( edd_get_settings_tabs() as $tab_id => $tab_name ) {

				$tab_url = add_query_arg( array(
					'settings-updated' => false,
					'tab' => $tab_id
				) );

				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">';
					echo esc_html( $tab_name );
				echo '</a>';
			}
			?>
		</h2>
		<div id="tab_container">
			<?php $sections = edd_get_settings_tabs_sections( $active_tab ); ?>
			<?php if( ! empty( $sections ) ) { ?>
				<?php $active_section = isset( $_GET['section'] ) && array_key_exists( $_GET['section'], $sections ) ? $_GET['section'] : 'general'; ?>
				<div class="nav-section-wrapper">
					<?php
					foreach( $sections as $section_id => $section_name ) {
						$section_url = add_query_arg( array(
							'settings-updated' => false,
							'section' => $section_id
						) );

						$active = $active_section == $section_id ? ' nav-section-active' : '';

						echo '<li>';
						echo '<a href="' . esc_url( $section_url ) . '" title="' . esc_attr( $section_name ) . '" class="nav-section' . $active . '">';
						echo esc_html( $section_name );
						echo '</a>';
						echo '</li>';
					}
					?>
				</div>
			<?php } ?>
			<form method="post" action="options.php">
				<table class="form-table">
				<?php
				settings_fields( 'edd_settings' );
				if( ! empty( $sections ) ) {
					$settings = edd_get_registered_settings();
					do_settings_fields( 'edd_settings_' . $active_tab, 'edd_settings_' . $active_tab . '_' . $active_section );
				}
				do_settings_fields( 'edd_settings_' . $active_tab, 'edd_settings_' . $active_tab );
				?>
				</table>
				<?php submit_button(); ?>
			</form>
		</div><!-- #tab_container-->
	</div><!-- .wrap -->
	<?php
	echo ob_get_clean();
}
