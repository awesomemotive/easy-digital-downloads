<?php
/**
 * Admin Pages
 *
 * @package     EDD
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Get the admin pages.
 *
 * This largely exists for back-compat in edd_is_admin_page(). Maybe eventually
 * we'll move away from globals for all of these, but who knows what add-ons are
 * doing, so we're keeping these around until we can formally deprecate them.
 *
 * @since 3.0
 *
 * @global $edd_discounts_page $edd_discounts_page
 * @global $edd_payments_page $edd_payments_page
 * @global $edd_settings_page $edd_settings_page
 * @global $edd_reports_page $edd_reports_page
 * @global type $edd_system_info_page
 * @global $edd_add_ons_page $edd_add_ons_page
 * @global $edd_settings_export $edd_settings_export
 * @global $edd_upgrades_screen $edd_upgrades_screen
 * @global $edd_customers_page $edd_customers_page
 * @global $edd_reports_page $edd_reports_page
 *
 * @return array
 */
function edd_get_admin_pages() {
	global  $edd_discounts_page,
			$edd_payments_page,
			$edd_settings_page,
			$edd_reports_page,
			$edd_system_info_page,
			$edd_add_ons_page,
			$edd_settings_export,
			$edd_upgrades_screen,
			$edd_customers_page,
			$edd_reports_page;

	// Filter & return
	return (array) apply_filters( 'edd_admin_pages', array(
		$edd_discounts_page,
		$edd_payments_page,
		$edd_settings_page,
		$edd_reports_page,
		$edd_system_info_page,
		$edd_add_ons_page,
		$edd_settings_export,
		$edd_upgrades_screen,
		$edd_customers_page,
		$edd_reports_page
	) );
}

/**
 * Creates the admin submenu pages under the Downloads menu and assigns their
 * links to global variables
 *
 * @since 1.0
 *
 * @global $edd_discounts_page
 * @global $edd_payments_page
 * @global $edd_customers_page
 * @global $edd_settings_page
 * @global $edd_reports_page
 * @global $edd_add_ons_page
 * @global $edd_settings_export
 * @global $edd_upgrades_screen
 */
function edd_add_options_link() {
	global $submenu, $edd_discounts_page, $edd_payments_page, $edd_settings_page, $edd_reports_page, $edd_upgrades_screen, $edd_tools_page, $edd_customers_page;

	// Filter the "View Customers" role
	$customer_view_role = apply_filters( 'edd_view_customers_role', 'view_shop_reports' );

	// Setup pages
	$edd_payments_page  = add_submenu_page( 'edit.php?post_type=download', __( 'Orders', 'easy-digital-downloads' ), __( 'Orders', 'easy-digital-downloads' ), 'edit_shop_payments', 'edd-payment-history', 'edd_payment_history_page' );
	$edd_customers_page = add_submenu_page( 'edit.php?post_type=download', __( 'Customers', 'easy-digital-downloads' ), __( 'Customers', 'easy-digital-downloads' ), $customer_view_role, 'edd-customers', 'edd_customers_page' );
	$edd_discounts_page = add_submenu_page( 'edit.php?post_type=download', __( 'Discounts', 'easy-digital-downloads' ), __( 'Discounts', 'easy-digital-downloads' ), 'manage_shop_discounts', 'edd-discounts', 'edd_discounts_page' );
	$edd_reports_page   = add_submenu_page( 'edit.php?post_type=download', __( 'Reports', 'easy-digital-downloads' ), __( 'Reports', 'easy-digital-downloads' ), 'view_shop_reports', 'edd-reports', 'edd_reports_page' );
	$edd_settings_page  = add_submenu_page( 'edit.php?post_type=download', __( 'EDD Settings', 'easy-digital-downloads' ), __( 'Settings', 'easy-digital-downloads' ), 'manage_shop_settings', 'edd-settings', 'edd_options_page' );
	$edd_tools_page     = add_submenu_page( 'edit.php?post_type=download', __( 'EDD Tools', 'easy-digital-downloads' ), __( 'Tools', 'easy-digital-downloads' ), 'manage_shop_settings', 'edd-tools', 'edd_tools_page' );

	// Setup hidden upgrades page
	$edd_upgrades_screen = add_submenu_page( '', __( 'EDD Upgrades', 'easy-digital-downloads' ), __( 'EDD Upgrades', 'easy-digital-downloads' ), 'manage_shop_settings', 'edd-upgrades', 'edd_upgrades_screen' );

	// Add our reports link in the main Dashboard menu.
	$submenu['index.php'][] = array( // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		__( 'Store Reports', 'easy-digital-downloads' ),
		'view_shop_reports',
		'edit.php?post_type=download&page=edd-reports',
	);
}
add_action( 'admin_menu', 'edd_add_options_link', 10 );

/**
 * Whether the current admin area page is one that allows the insertion of a
 * button to make inserting Downloads easier.
 *
 * @since 3.0
 * @global $pagenow $pagenow
 * @global $typenow $typenow
 * @return boolean
 */
function edd_is_insertable_admin_page() {
	global $pagenow, $typenow;

	// Allowed pages
	$pages = array(
		'post.php',
		'page.php',
		'post-new.php',
		'post-edit.php'
	);

	// Allowed post types
	$types = get_post_types_by_support( 'edd_insert_download' );

	// Return if page and type are allowed
	return in_array( $pagenow, $pages, true ) && in_array( $typenow, $types, true );
}

/**
 * Determines whether the current admin page is a specific EDD admin page.
 *
 * Only works after the `wp_loaded` hook, & most effective
 * starting on `admin_menu` hook. Failure to pass in $view will match all views of $passed_page.
 * Failure to pass in $passed_page will return true if on any EDD page
 *
 * @since 1.9.6
 * @since 2.11.3 Added `$include_non_exclusive` parameter.
 *
 * @param string $passed_page           Optional. Main page's slug.
 * @param string $passed_view           Optional. Page view ( ex: `edit` or `delete` )
 * @param bool   $include_non_exclusive Optional. If we should consider pages not exclusive to EDD.
 *                                      Includes the main dashboard page and custom post types that
 *                                      support the "Insert Download" button via the TinyMCE editor.
 *
 * @return bool True if EDD admin page we're looking for or an EDD page or if $page is empty, any EDD page
 */
function edd_is_admin_page( $passed_page = '', $passed_view = '', $include_non_exclusive = true ) {
	global $pagenow, $typenow;

	$found      = false;
	$post_type  = isset( $_GET['post_type'] )  ? strtolower( $_GET['post_type'] )  : false;
	$action     = isset( $_GET['action'] )     ? strtolower( $_GET['action'] )     : false;
	$taxonomy   = isset( $_GET['taxonomy'] )   ? strtolower( $_GET['taxonomy'] )   : false;
	$page       = isset( $_GET['page'] )       ? strtolower( $_GET['page'] )       : false;
	$view       = isset( $_GET['view'] )       ? strtolower( $_GET['view'] )       : false;
	$edd_action = isset( $_GET['edd-action'] ) ? strtolower( $_GET['edd-action'] ) : false;
	$tab        = isset( $_GET['tab'] )        ? strtolower( $_GET['tab'] )        : false;

	switch ( $passed_page ) {
		case 'download':
			switch ( $passed_view ) {
				case 'list-table':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'post.php' ) {
						$found = true;
					}
					break;
				case 'new':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'post-new.php' ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'download' === $typenow || 'download' === $post_type ) || 'download' === $post_type || ( 'post-new.php' === $pagenow && 'download' === $post_type ) ) {
						$found = true;
					}
					break;
			}
			break;
		case 'categories':
			switch ( $passed_view ) {
				case 'list-table':
				case 'new':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit-tags.php' && 'edit' !== $action && 'download_category' === $taxonomy ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit-tags.php' && 'edit' === $action && 'download_category' === $taxonomy ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit-tags.php' && 'download_category' === $taxonomy ) {
						$found = true;
					}
					break;
			}
			break;
		case 'tags':
			switch ( $passed_view ) {
				case 'list-table':
				case 'new':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit-tags.php' && 'edit' !== $action && 'download_tag' === $taxonomy ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit-tags.php' && 'edit' === $action && 'download_tag' === $taxonomy ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit-tags.php' && 'download_tag' === $taxonomy ) {
						$found = true;
					}
					break;
			}
			break;
		case 'payments':
			switch ( $passed_view ) {
				case 'list-table':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-payment-history' === $page && false === $view  ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-payment-history' === $page && 'view-order-details' === $view ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-payment-history' === $page ) {
						$found = true;
					}
					break;
			}
			break;
		case 'discounts':
			switch ( $passed_view ) {
				case 'list-table':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-discounts' === $page && false === $edd_action ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-discounts' === $page && 'edit_discount' === $edd_action ) {
						$found = true;
					}
					break;
				case 'new':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-discounts' === $page && 'add_discount' === $edd_action ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-discounts' === $page ) {
						$found = true;
					}
					break;
			}
			break;
		case 'reports':
			switch ( $passed_view ) {
				// If you want to do something like enqueue a script on a particular report's duration, look at $_GET[ 'range' ]
				case 'earnings':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-reports' === $page && ( 'earnings' === $view || '-1' === $view || false === $view ) ) {
						$found = true;
					}
					break;
				case 'downloads':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-reports' === $page && 'downloads' === $view ) {
						$found = true;
					}
					break;
				case 'customers':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-reports' === $page && 'customers' === $view ) {
						$found = true;
					}
					break;
				case 'gateways':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-reports' === $page && 'gateways' === $view ) {
						$found = true;
					}
					break;
				case 'taxes':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-reports' === $page && 'taxes' === $view ) {
						$found = true;
					}
					break;
				case 'export':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-reports' === $page && 'export' === $view ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-reports' === $page ) {
						$found = true;
					}
					break;
			}
			break;
		case 'settings':
			switch ( $passed_view ) {
				case 'general':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-settings' === $page && ( 'general' === $tab || false === $tab ) ) {
						$found = true;
					}
					break;
				case 'gateways':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-settings' === $page && 'gateways' === $tab ) {
						$found = true;
					}
					break;
				case 'emails':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-settings' === $page && 'emails' === $tab ) {
						$found = true;
					}
					break;
				case 'styles':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-settings' === $page && 'styles' === $tab ) {
						$found = true;
					}
					break;
				case 'taxes':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-settings' === $page && 'taxes' === $tab ) {
						$found = true;
					}
					break;
				case 'extensions':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-settings' === $page && 'extensions' === $tab ) {
						$found = true;
					}
					break;
				case 'licenses':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-settings' === $page && 'licenses' === $tab ) {
						$found = true;
					}
					break;
				case 'misc':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-settings' === $page && 'misc' === $tab ) {
						$found = true;
					}
					break;
				case 'marketing':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-settings' === $page && 'marketing' === $tab ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-settings' === $page ) {
						$found = true;
					}
					break;
			}
			break;
		case 'tools':
			switch ( $passed_view ) {
				case 'general':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-tools' === $page && ( 'general' === $tab || false === $tab ) ) {
						$found = true;
					}
					break;
				case 'api_keys':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-tools' === $page && 'api_keys' === $tab ) {
						$found = true;
					}
					break;
				case 'system_info':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-tools' === $page && 'system_info' === $tab ) {
						$found = true;
					}
					break;
				case 'logs':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-tools' === $page && 'logs' === $tab ) {
						$found = true;
					}
					break;
				case 'import_export':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-tools' === $page && 'import_export' === $tab ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-tools' === $page ) {
						$found = true;
					}
					break;
			}
			break;
		case 'addons':
			if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-addons' === $page ) {
				$found = true;
			}
			break;
		case 'customers':
			switch ( $passed_view ) {
				case 'list-table':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-customers' === $page && false === $view ) {
						$found = true;
					}
					break;
				case 'overview':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-customers' === $page && 'overview' === $view ) {
						$found = true;
					}
					break;
				case 'notes':
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-customers' === $page && 'notes' === $view ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-customers' === $page ) {
						$found = true;
					}
					break;
			}
			break;
		case 'reports':
			if ( ( 'download' === $typenow || 'download' === $post_type ) && $pagenow === 'edit.php' && 'edd-reports' === $page ) {
				$found = true;
			}
			break;
		case 'index.php' :
			if ( 'index.php' === $pagenow ) {
				$found = true;
			}
			break;

		default:
			$admin_pages = edd_get_admin_pages();

			// Downloads sub-page or Dashboard page
			if ( ( 'download' === $typenow ) || ( $include_non_exclusive && 'index.php' === $pagenow ) ) {
				$found = true;

			// Registered global pages
			} elseif ( in_array( $pagenow, $admin_pages, true ) ) {
				$found = true;

			// Supported post types
			} elseif ( $include_non_exclusive && edd_is_insertable_admin_page() ) {
				$found = true;

			// The EDD settings screen (fallback if mislinked)
			} elseif ( 'edd-settings' === $page ) {
				$found = true;
			}
			break;
	}

	return (bool) apply_filters( 'edd_is_admin_page', $found, $page, $view, $passed_page, $passed_view );
}

/**
 * Forces the Cache-Control header on our admin pages to send the no-store header
 * which prevents the back-forward cache (bfcache) from storing a copy of this page in local
 * cache. This helps make sure that page elements modified via AJAX and DOM manipulations aren't
 * incorrectly shown as if they never changed.
 *
 * @since 3.0
 * @param array $headers An array of nocache headers.
 *
 * @return array
 */
function _edd_bfcache_buster( $headers ) {
	if ( ! is_admin() & ! edd_is_admin_page() ) {
		return $headers;
	}

	$headers['Cache-Control'] = 'no-cache, must-revalidate, max-age=0, no-store';

	return $headers;
}
add_filter( 'nocache_headers', '_edd_bfcache_buster', 10, 1 );
