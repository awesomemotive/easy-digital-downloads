<?php
/**
 * Admin Pages
 *
 * @package     EDD
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates the admin submenu pages under the Downloads menu and assigns their
 * links to global variables
 *
 * @since 1.0
 * @global $edd_discounts_page
 * @global $edd_payments_page
 * @global $edd_customers_page
 * @global $edd_settings_page
 * @global $edd_reports_page
 * @global $edd_add_ons_page
 * @global $edd_settings_export
 * @global $edd_upgrades_screen
 * @return void
 */
function edd_add_options_link() {
	global $edd_discounts_page, $edd_payments_page, $edd_settings_page, $edd_reports_page, $edd_add_ons_page, $edd_settings_export, $edd_upgrades_screen, $edd_tools_page, $edd_customers_page;

	$edd_payment            = get_post_type_object( 'edd_payment' );

	$customer_view_role     = apply_filters( 'edd_view_customers_role', 'view_shop_reports' );

	$edd_payments_page      = add_submenu_page( 'edit.php?post_type=download', $edd_payment->labels->name, $edd_payment->labels->menu_name, 'edit_shop_payments', 'edd-payment-history', 'edd_payment_history_page' );
	$edd_customers_page     = add_submenu_page( 'edit.php?post_type=download', __( 'Customers', 'easy-digital-downloads' ), __( 'Customers', 'easy-digital-downloads' ), $customer_view_role, 'edd-customers', 'edd_customers_page' );
	$edd_discounts_page     = add_submenu_page( 'edit.php?post_type=download', __( 'Discount Codes', 'easy-digital-downloads' ), __( 'Discount Codes', 'easy-digital-downloads' ), 'manage_shop_discounts', 'edd-discounts', 'edd_discounts_page' );
	$edd_reports_page       = add_submenu_page( 'edit.php?post_type=download', __( 'Earnings and Sales Reports', 'easy-digital-downloads' ), __( 'Reports', 'easy-digital-downloads' ), 'view_shop_reports', 'edd-reports', 'edd_reports_page' );
	$edd_settings_page      = add_submenu_page( 'edit.php?post_type=download', __( 'Easy Digital Downloads Settings', 'easy-digital-downloads' ), __( 'Settings', 'easy-digital-downloads' ), 'manage_shop_settings', 'edd-settings', 'edd_options_page' );
	$edd_tools_page         = add_submenu_page( 'edit.php?post_type=download', __( 'Easy Digital Downloads Info and Tools', 'easy-digital-downloads' ), __( 'Tools', 'easy-digital-downloads' ), 'manage_shop_settings', 'edd-tools', 'edd_tools_page' );
	$edd_add_ons_page       = add_submenu_page( 'edit.php?post_type=download', __( 'Easy Digital Downloads Extensions', 'easy-digital-downloads' ), __( 'Extensions', 'easy-digital-downloads' ), 'manage_shop_settings', 'edd-addons', 'edd_add_ons_page' );
	$edd_upgrades_screen    = add_submenu_page( null, __( 'EDD Upgrades', 'easy-digital-downloads' ), __( 'EDD Upgrades', 'easy-digital-downloads' ), 'manage_shop_settings', 'edd-upgrades', 'edd_upgrades_screen' );

}
add_action( 'admin_menu', 'edd_add_options_link', 10 );

/**
 *  Determines whether the current admin page is a specific EDD admin page.
 *
 *  Only works after the `wp_loaded` hook, & most effective
 *  starting on `admin_menu` hook. Failure to pass in $view will match all views of $main_page.
 *  Failure to pass in $main_page will return true if on any EDD page
 *
 *  @since 1.9.6
 *
 *  @param string $page Optional. Main page's slug
 *  @param string $view Optional. Page view ( ex: `edit` or `delete` )
 *  @return bool True if EDD admin page we're looking for or an EDD page or if $page is empty, any EDD page
 */
function edd_is_admin_page( $passed_page = '', $passed_view = '' ) {

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
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'post.php' ) {
						$found = true;
					}
					break;
				case 'new':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'post-new.php' ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'download' == $typenow || 'download' === $post_type ) || 'download' === $post_type || ( 'post-new.php' == $pagenow && 'download' === $post_type ) ) {
						$found = true;
					}
					break;
			}
			break;
		case 'categories':
			switch ( $passed_view ) {
				case 'list-table':
				case 'new':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit-tags.php' && 'edit' !== $action && 'download_category' === $taxonomy ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit-tags.php' && 'edit' === $action && 'download_category' === $taxonomy ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit-tags.php' && 'download_category' === $taxonomy ) {
						$found = true;
					}
					break;
			}
			break;
		case 'tags':
			switch ( $passed_view ) {
				case 'list-table':
				case 'new':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit-tags.php' && 'edit' !== $action && 'download_tax' === $taxonomy ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit-tags.php' && 'edit' === $action && 'download_tax' === $taxonomy ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit-tags.php' && 'download_tax' === $taxonomy ) {
						$found = true;
					}
					break;
			}
			break;
		case 'payments':
			switch ( $passed_view ) {
				case 'list-table':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-payment-history' === $page && false === $view  ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-payment-history' === $page && 'view-order-details' === $view ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-payment-history' === $page ) {
						$found = true;
					}
					break;
			}
			break;
		case 'discounts':
			switch ( $passed_view ) {
				case 'list-table':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-discounts' === $page && false === $edd_action ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-discounts' === $page && 'edit_discount' === $edd_action ) {
						$found = true;
					}
					break;
				case 'new':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-discounts' === $page && 'add_discount' === $edd_action ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-discounts' === $page ) {
						$found = true;
					}
					break;
			}
			break;
		case 'reports':
			switch ( $passed_view ) {
				// If you want to do something like enqueue a script on a particular report's duration, look at $_GET[ 'range' ]
				case 'earnings':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-reports' === $page && ( 'earnings' === $view || '-1' === $view || false === $view ) ) {
						$found = true;
					}
					break;
				case 'downloads':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-reports' === $page && 'downloads' === $view ) {
						$found = true;
					}
					break;
				case 'customers':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-reports' === $page && 'customers' === $view ) {
						$found = true;
					}
					break;
				case 'gateways':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-reports' === $page && 'gateways' === $view ) {
						$found = true;
					}
					break;
				case 'taxes':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-reports' === $page && 'taxes' === $view ) {
						$found = true;
					}
					break;
				case 'export':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-reports' === $page && 'export' === $view ) {
						$found = true;
					}
					break;
				case 'logs':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-reports' === $page && 'logs' === $view ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-reports' === $page ) {
						$found = true;
					}
					break;
			}
			break;
		case 'settings':
			switch ( $passed_view ) {
				case 'general':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-settings' === $page && ( 'genera' === $tab || false === $tab ) ) {
						$found = true;
					}
					break;
				case 'gateways':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-settings' === $page && 'gateways' === $tab ) {
						$found = true;
					}
					break;
				case 'emails':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-settings' === $page && 'emails' === $tab ) {
						$found = true;
					}
					break;
				case 'styles':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-settings' === $page && 'styles' === $tab ) {
						$found = true;
					}
					break;
				case 'taxes':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-settings' === $page && 'taxes' === $tab ) {
						$found = true;
					}
					break;
				case 'extensions':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-settings' === $page && 'extensions' === $tab ) {
						$found = true;
					}
					break;
				case 'licenses':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-settings' === $page && 'licenses' === $tab ) {
						$found = true;
					}
					break;
				case 'misc':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-settings' === $page && 'misc' === $tab ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-settings' === $page ) {
						$found = true;
					}
					break;
			}
			break;
		case 'tools':
			switch ( $passed_view ) {
				case 'general':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-tools' === $page && ( 'general' === $tab || false === $tab ) ) {
						$found = true;
					}
					break;
				case 'api_keys':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-tools' === $page && 'api_keys' === $tab ) {
						$found = true;
					}
					break;
				case 'system_info':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-tools' === $page && 'system_info' === $tab ) {
						$found = true;
					}
					break;
				case 'import_export':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-tools' === $page && 'import_export' === $tab ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-tools' === $page ) {
						$found = true;
					}
					break;
			}
			break;
		case 'addons':
			if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-addons' === $page ) {
				$found = true;
			}
			break;
		case 'customers':
			switch ( $passed_view ) {
				case 'list-table':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-customers' === $page && false === $view ) {
						$found = true;
					}
					break;
				case 'overview':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-customers' === $page && 'overview' === $view ) {
						$found = true;
					}
					break;
				case 'notes':
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-customers' === $page && 'notes' === $view ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-customers' === $page ) {
						$found = true;
					}
					break;
			}
			break;
		case 'reports':
			if ( ( 'download' == $typenow || 'download' === $post_type ) && $pagenow == 'edit.php' && 'edd-reports' === $page ) {
				$found = true;
			}
			break;
		default:
			global $edd_discounts_page, $edd_payments_page, $edd_settings_page, $edd_reports_page, $edd_system_info_page, $edd_add_ons_page, $edd_settings_export, $edd_upgrades_screen, $edd_customers_page, $edd_reports_page;
			$admin_pages = apply_filters( 'edd_admin_pages', array( $edd_discounts_page, $edd_payments_page, $edd_settings_page, $edd_reports_page, $edd_system_info_page, $edd_add_ons_page, $edd_settings_export, $edd_customers_page, $edd_reports_page ) );
			if ( 'download' == $typenow || 'index.php' == $pagenow || 'post-new.php' == $pagenow || 'post.php' == $pagenow ) {
				$found = true;
				if( 'edd-upgrades' === $page ) {
					$found = false;
				}
			} elseif ( in_array( $pagenow, $admin_pages ) ) {
				$found = true;
			}
			break;
	}

	return (bool) apply_filters( 'edd_is_admin_page', $found, $page, $view, $passed_page, $passed_view );
}
