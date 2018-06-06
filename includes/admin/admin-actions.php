<?php
/**
 * Admin Actions
 *
 * @package     EDD
 * @subpackage  Admin/Actions
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Processes all EDD actions sent via POST and GET by looking for the 'edd-action'
 * request and running do_action() to call the function
 *
 * @since 1.0
 * @return void
 */
function edd_process_actions() {
	if ( isset( $_POST['edd-action'] ) ) {
		do_action( 'edd_' . $_POST['edd-action'], $_POST );
	}

	if ( isset( $_GET['edd-action'] ) ) {
		do_action( 'edd_' . $_GET['edd-action'], $_GET );
	}
}
add_action( 'admin_init', 'edd_process_actions' );

/**
 * When the Download list table loads, call the function to view our tabs.
 *
 * @since 2.8.9
 * @param $views
 *
 * @return mixed
 */
function edd_products_tabs( $views ) {
	edd_display_product_tabs();

	return $views;
}
add_filter( 'views_edit-download', 'edd_products_tabs', 10, 1 );

/**
 * Displays the product tabs for 'Products' and 'Apps and Integrations'
 *
 * @since 2.8.9
 */
function edd_display_product_tabs() {
	?>
	<h2 class="nav-tab-wrapper">
		<?php
		$tabs = array(
			'products' => array(
				'name' => edd_get_label_plural(),
				'url'  => admin_url( 'edit.php?post_type=download' ),
			),
			'integrations' => array(
				'name' => __( 'Apps and Integrations', 'easy-digital-downloads' ),
				'url'  => admin_url( 'edit.php?post_type=download&page=edd-addons&view=integrations' ),
			),
		);

		$tabs       = apply_filters( 'edd_add_ons_tabs', $tabs );
		$active_tab = isset( $_GET['page'] ) && $_GET['page'] === 'edd-addons' ? 'integrations' : 'products';
		foreach( $tabs as $tab_id => $tab ) {

			$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

			echo '<a href="' . esc_url( $tab['url'] ) . '" class="nav-tab' . $active . '">';
			echo esc_html( $tab['name'] );
			echo '</a>';
		}
		?>

		<a href="<?php echo admin_url( 'post-new.php?post_type=download' ); ?>" class="page-title-action">
			<?php _e( 'Add New', 'easy-digital-downloads' ); // No text domain so it just follows what WP Core does ?>
		</a>
	</h2>
	<br />
	<?php
}