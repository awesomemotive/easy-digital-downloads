<?php
/**
 * Tools
 *
 * These are functions used for displaying EDD tools such as the import/export system.
 *
 * @package     EDD
 * @subpackage  Admin/Tools
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Tools
 *
 * Shows the tools panel which contains EDD-specific tools including the
 * built-in import/export system.
 *
 * @since       1.8
 * @author      Daniel J Griffiths
 * @return      void
 */
function edd_tools_page() {
	$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'tools';
?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2 class="nav-tab-wrapper">
			<?php
			foreach( edd_get_tools_tabs() as $tab_id => $tab_name ) {

				$tab_url = add_query_arg( array(
					'tab' => $tab_id
				) );

				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';
				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">' . esc_html( $tab_name ) . '</a>';

			}
			?>
		</h2>
		<div class="metabox-holder">
			<?php
			do_action( 'edd_tools_tab_' . $active_tab );
			?>
		</div><!-- .metabox-holder -->
	</div><!-- .wrap -->
<?php
}


/**
 * Retrieve tools tabs
 *
 * @since       2.0
 * @return      array
 */
function edd_get_tools_tabs() {

	$tabs                  = array();
	$tabs['tools']         = __( 'Tools', 'edd' );
	$tabs['system_info']   = __( 'System Info', 'edd' );
	$tabs['import_export'] = __( 'Import/Export', 'edd' );

	return apply_filters( 'edd_tools_tabs', $tabs );
}
