<?php
/**
 * Admin Actions
 *
 * @package     EDD
 * @subpackage  Admin/Actions
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

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
 * When the Download list table loads, call the function to view our tabs.
 *
 * @since 3.0
 *
 * @return void
 */
function edd_taxonomies_tabs() {

	// Bail if not viewing a taxonomy
	if ( empty( $_GET['taxonomy'] ) ) {
		return;
	}

	// Get taxonomies
	$taxonomy   = sanitize_key( $_GET['taxonomy'] );
	$taxonomies = get_object_taxonomies( 'download' );

	// Bail if current taxonomy is not a download taxonomy
	if ( ! in_array( $taxonomy, $taxonomies, true ) ) {
		return;
	}

	// Output the tabs
	?><div class="wrap edd-tab-wrap"><?php
	edd_display_product_tabs();
	?></div><?php
}
add_action( 'admin_notices', 'edd_taxonomies_tabs', 10, 1 );

/**
 * Remove the top level taxonomy submenus.
 *
 * Since 3.0, these links were moved to horizontal tabs.
 *
 * @since 3.0
 */
function edd_admin_adjust_submenus() {

	// Get taxonomies
	$taxonomies = get_object_taxonomies( 'download' );

	// Bail if no taxonomies
	if ( empty( $taxonomies ) ) {
		return;
	}

	// Loop through each taxonomy and remove the menu
	foreach ( $taxonomies as $taxonomy ) {
		remove_submenu_page( 'edit.php?post_type=download', 'edit-tags.php?taxonomy=' . $taxonomy . '&amp;post_type=download' );
	}

	// Remove the "Add New" link for downloads
	remove_submenu_page( 'edit.php?post_type=download', 'post-new.php?post_type=download' );
}
add_action( 'admin_menu', 'edd_admin_adjust_submenus', 999 );

/**
 * This tells WordPress to highlight the Downloads > Downloads submenu,
 * regardless of which actual Downloads Taxonomy screen we are on.
 *
 * The conditional prevents the override when the user is viewing settings or
 * any third-party plugins.
 *
 * @since 3.0.0
 *
 * @global string $submenu_file
 */
function edd_taxonomies_modify_menu_highlight() {
	global $submenu_file;

	// Bail if not viewing a taxonomy
	if ( empty( $_GET['taxonomy'] ) ) {
		return;
	}

	// Get taxonomies
	$taxonomy   = sanitize_key( $_GET['taxonomy'] );
	$taxonomies = get_object_taxonomies( 'download' );

	// Bail if current taxonomy is not a download taxonomy
	if ( ! in_array( $taxonomy, $taxonomies, true ) ) {
		return;
	}

	// Force the submenu file
	$submenu_file = 'edit.php?post_type=download';
}
add_filter( 'admin_head', 'edd_taxonomies_modify_menu_highlight', 9999 );

/**
 * This tells WordPress to highlight the Downloads > Downloads submenu when
 * adding a new product.
 *
 * @since 3.0.0
 *
 * @global string $submenu_file
 */
function edd_add_new_modify_menu_highlight() {
	global $submenu_file, $pagenow;

	// Bail if not viewing the right page or post type
	if ( empty( $_GET['post_type'] ) || ( 'post-new.php' !== $pagenow ) ) {
		return;
	}

	// Get post_type
	$post_type = sanitize_key( $_GET['post_type'] );

	// Bail if current post type is not download
	if ( 'download' !== $post_type ) {
		return;
	}

	// Force the submenu file
	$submenu_file = 'edit.php?post_type=download';
}
add_filter( 'admin_head', 'edd_add_new_modify_menu_highlight', 9999 );

/**
 * Displays the product tabs for Products, Categories, and Tags
 *
 * @since 2.8.9
 */
function edd_display_product_tabs() {

	// Initial tabs
	$tabs = array(
		'products' => array(
			'name' => edd_get_label_plural(),
			'url'  => admin_url( 'edit.php?post_type=download' ),
		)
	);

	// Get taxonomies
	$taxonomies = get_object_taxonomies( 'download', 'objects' );
	foreach ( $taxonomies as $tax => $details ) {
		$tabs[ $tax ] = array(
			'name' => $details->labels->menu_name,
			'url'  => add_query_arg( array(
				'taxonomy'  => $tax,
				'post_type' => 'download'
			), admin_url( 'edit-tags.php' ) )
		);
	}

	// Filter the tabs
	$tabs = apply_filters( 'edd_add_ons_tabs', $tabs );

	// Taxonomies
	if ( isset( $_GET['taxonomy'] ) && in_array( $_GET['taxonomy'], array_keys( $taxonomies ), true ) ) {
		$active_tab = $_GET['taxonomy'];

	// Default to Products
	} else {
		$active_tab = 'products';
	}

	// Start a buffer
	ob_start() ?>

	<div class="clear"></div>
	<h2 class="nav-tab-wrapper edd-nav-tab-wrapper edd-tab-clear">
		<?php

		foreach ( $tabs as $tab_id => $tab ) {
			$active = ( $active_tab === $tab_id )
				? ' nav-tab-active'
				: '';

			echo '<a href="' . esc_url( $tab['url'] ) . '" class="nav-tab' . esc_attr( $active ) . '">';
			echo esc_html( $tab['name'] );
			echo '</a>';
		} ?>

		<a href="<?php echo admin_url( 'post-new.php?post_type=download' ); ?>" class="page-title-action">
			<?php _e( 'Add New', 'easy-digital-downloads' ); ?>
		</a>
	</h2>
	<br />

	<?php

	// Output the current buffer
	echo ob_get_clean();
}

/**
 * Return array of query arguments that should be removed from URLs.
 *
 * @since 3.0
 *
 * @return array
 */
function edd_admin_removable_query_args() {
	return apply_filters( 'edd_admin_removable_query_args', array(
		'edd-action',
		'edd-notice',
		'edd-message',
		'edd-redirect'
	) );
}

/**
 * Output payment icons into the admin footer.
 *
 * Specifically on the "General" tab of the "Payment Gateways" admin page.
 *
 * @since 3.0
 */
function edd_admin_print_payment_icons() {

	// Bail if not the gateways page
	if ( ! edd_is_admin_page( 'settings', 'gateways' ) ) {
		return;
	}

	// Output the SVG icons
	edd_print_payment_icons( array(
		'mastercard',
		'visa',
		'americanexpress',
		'discover',
		'paypal',
		'amazon'
	) );
}
add_action( 'admin_footer', 'edd_admin_print_payment_icons', 9999 );
