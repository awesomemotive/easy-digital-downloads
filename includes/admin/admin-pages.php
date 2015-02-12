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
 * @global $edd_settings_page
 * @global $edd_reports_page
 * @global $edd_add_ons_page
 * @global $edd_settings_export
 * @global $edd_upgrades_screen
 * @return void
 */
function edd_add_options_link() {
	global $edd_discounts_page, $edd_payments_page, $edd_settings_page, $edd_reports_page, $edd_add_ons_page, $edd_settings_export, $edd_upgrades_screen, $edd_tools_page;

	$edd_payment            = get_post_type_object( 'edd_payment' );

	$edd_payments_page      = add_submenu_page( 'edit.php?post_type=download', $edd_payment->labels->name, $edd_payment->labels->menu_name, 'edit_shop_payments', 'edd-payment-history', 'edd_payment_history_page' );
	$edd_discounts_page     = add_submenu_page( 'edit.php?post_type=download', __( 'Discount Codes', 'edd' ), __( 'Discount Codes', 'edd' ), 'manage_shop_discounts', 'edd-discounts', 'edd_discounts_page' );
	$edd_reports_page 	    = add_submenu_page( 'edit.php?post_type=download', __( 'Earnings and Sales Reports', 'edd' ), __( 'Reports', 'edd' ), 'view_shop_reports', 'edd-reports', 'edd_reports_page' );
	$edd_settings_page 	    = add_submenu_page( 'edit.php?post_type=download', __( 'Easy Digital Download Settings', 'edd' ), __( 'Settings', 'edd' ), 'manage_shop_settings', 'edd-settings', 'edd_options_page' );
	$edd_tools_page         = add_submenu_page( 'edit.php?post_type=download', __( 'Easy Digital Download Info and Tools', 'edd' ), __( 'Tools', 'edd' ), 'install_plugins', 'edd-tools', 'edd_tools_page' );
	$edd_add_ons_page 	    = add_submenu_page( 'edit.php?post_type=download', __( 'Easy Digital Download Add Ons', 'edd' ), __( 'Add Ons', 'edd' ), 'install_plugins', 'edd-addons', 'edd_add_ons_page' );
	$edd_upgrades_screen    = add_submenu_page( null, __( 'EDD Upgrades', 'edd' ), __( 'EDD Upgrades', 'edd' ), 'manage_shop_settings', 'edd-upgrades', 'edd_upgrades_screen' );
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
function edd_is_admin_page( $page = '', $view = '' ) {
	
	global $pagenow, $typenow;
	
	$found = false;

	switch ( $page ){
		case 'download':
			switch ( $view ){
				case 'list-table':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' ){
						$found = true;
					}					
					break;
				case 'edit':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'post.php' ){
						$found = true;
					}				
					break;
				case 'new':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'post-new.php' ){
						$found = true;
					}				
					break;
				default:
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) || ( 'post-new.php' == $pagenow && isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ){
						$found = true;
					}
					break;
			}
			break;
		case 'categories':
			switch ( $view ){
				case 'list-table':
				case 'new':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit-tags.php' && !( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'edit' ) && ( isset( $_GET[ 'taxonomy' ] ) && $_GET[ 'taxonomy' ] == 'download_category' ) ){
						$found = true;
					}					
					break;
				case 'edit':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit-tags.php' && ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'edit' ) && ( isset( $_GET[ 'taxonomy' ] ) && $_GET[ 'taxonomy' ] == 'download_category' ) ){
						$found = true;
					}		
					break;
				default:
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit-tags.php' && ( isset( $_GET[ 'taxonomy' ] ) && $_GET[ 'taxonomy' ] == 'download_category' ) ){
						$found = true;
					}					
					break;
			}
			break;
		case 'tags':
			switch ( $view ){
				case 'list-table':
				case 'new':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit-tags.php' && !( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'edit' ) && ( isset( $_GET[ 'taxonomy' ] ) && $_GET[ 'taxonomy' ] == 'download_tag' ) ){
						$found = true;
					}					
					break;
				case 'edit':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit-tags.php' && ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'edit' ) && ( isset( $_GET[ 'taxonomy' ] ) && $_GET[ 'taxonomy' ] == 'download_tag' ) ){
						$found = true;
					}		
					break;
				default:
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit-tags.php' && ( isset( $_GET[ 'taxonomy' ] ) && $_GET[ 'taxonomy' ] == 'download_tag' ) ){
						$found = true;
					}					
					break;
			}
			break;
		case 'payments':
			switch ( $view ){
				case 'list-table':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-payment-history' ) && !isset( $_GET[ 'view' ] )  ){
						$found = true;
					}					
					break;
				case 'edit':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-payment-history' ) && ( isset( $_GET[ 'view' ] ) && $_GET[ 'view' ] == 'view-order-details' ) ){
						$found = true;
					}					
					break;
				default:
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-payment-history' ) ){
						$found = true;
					}				
					break;
			}
			break;
		case 'discounts':
			switch ( $view ){
				case 'list-table':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-discounts' ) && !isset( $_GET[ 'edd-action' ] )  ){
						$found = true;
					}					
					break;
				case 'edit':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-discounts' ) && ( isset( $_GET[ 'edd-action' ] ) && $_GET[ 'edd-action' ] == 'edit_discount' ) ){
						$found = true;
					}					
					break;
				case 'new':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-discounts' ) && ( isset( $_GET[ 'edd-action' ] ) && $_GET[ 'edd-action' ] == 'add_discount' ) ){
						$found = true;
					}					
					break;					
				default:
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-discounts' ) ){
						$found = true;
					}				
					break;
			}
			break;
		case 'reports':
			switch ( $view ){
				// If you want to do something like enqueue a script on a particular report's duration, look at $_GET[ 'range' ]
				case 'earnings':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-reports' ) && ( ( isset( $_GET[ 'view' ] ) && $_GET[ 'view' ] == 'earnings' ) || ( isset( $_GET[ 'view' ] ) && $_GET[ 'view' ] == '-1' ) || !isset( $_GET[ 'view' ] ) ) ){
						$found = true;
					}					
					break;
				case 'downloads':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-reports' ) && ( isset( $_GET[ 'view' ] ) && $_GET[ 'view' ] == 'downloads' ) ){
						$found = true;
					}					
					break;
				case 'customers':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-reports' ) && ( isset( $_GET[ 'view' ] ) && $_GET[ 'view' ] == 'customers' ) ){
						$found = true;
					}					
					break;
				case 'gateways':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-reports' ) && ( isset( $_GET[ 'view' ] ) && $_GET[ 'view' ] == 'gateways' ) ){
						$found = true;
					}					
					break;
				case 'taxes':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-reports' ) && ( isset( $_GET[ 'view' ] ) && $_GET[ 'view' ] == 'taxes' ) ){
						$found = true;
					}					
					break;
				case 'export':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-reports' ) && ( isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'export' ) ){
						$found = true;
					}					
					break;
				case 'logs':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-reports' ) && ( isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'logs' ) ){
						$found = true;
					}					
					break;		
				default:
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-reports' ) ){
						$found = true;
					}				
					break;
			}
			break;
		case 'settings':
			switch ( $view ){
				case 'general':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-settings' ) && ( ( isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'general' ) || !isset( $_GET[ 'tab' ] ) ) ){
						$found = true;
					}
					break;
				case 'gateways':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-settings' ) && ( isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'gateways' ) ){
						$found = true;
					}
					break;				
				case 'emails':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-settings' ) && ( isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'emails' ) ){
						$found = true;
					}				
					break;
				case 'styles':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-settings' ) && ( isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'styles' ) ){
						$found = true;
					}				
					break;
				case 'taxes':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-settings' ) && ( isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'taxes' ) ){
						$found = true;
					}				
					break;
				case 'extensions':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-settings' ) && ( isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'extensions' ) ){
						$found = true;
					}				
					break;
				case 'licenses':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-settings' ) && ( isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'licenses' ) ){
						$found = true;
					}				
					break;
				case 'misc':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-settings' ) && ( isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'misc' ) ){
						$found = true;
					}				
					break;
				default:
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-settings' ) ){
						$found = true;
					}				
					break;
			}
			break;
		case 'tools':
			switch ( $view ){
				case 'general':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-tools' ) && ( ( isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'general' ) || !isset( $_GET[ 'tab' ] ) ) ){
						$found = true;
					}
					break;
				case 'api_keys':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-tools' ) && ( isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'api_keys' ) ){
						$found = true;
					}
					break;
				case 'system_info':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-tools' ) && ( isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'system_info' ) ){
						$found = true;
					}
					break;
				case 'import_export':
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-tools' ) && ( isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'import_export' ) ){
						$found = true;
					}
					break;
				default:
					if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-tools' ) ){
						$found = true;
					}				
					break;
			}
			break;
		case 'addons':
			if ( ( 'download' == $typenow || ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'download' ) ) && $pagenow == 'edit.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'edd-addons' ) ){
				$found = true;
			}
			break;
		default:
			global $edd_discounts_page, $edd_payments_page, $edd_settings_page, $edd_reports_page, $edd_system_info_page, $edd_add_ons_page, $edd_settings_export, $edd_upgrades_screen;

			$admin_pages = apply_filters( 'edd_admin_pages', array( $edd_discounts_page, $edd_payments_page, $edd_settings_page, $edd_reports_page, $edd_system_info_page, $edd_add_ons_page, $edd_settings_export ) );

			if ( 'download' == $typenow || 'index.php' == $pagenow || 'post-new.php' == $pagenow || 'post.php' == $pagenow ) {

				$found = true;

				if( isset( $_GET[ 'page' ] ) && 'edd-upgrades' == $_GET[ 'page' ] ) {

					$found = false;

				}

			} elseif ( in_array( $pagenow, $admin_pages ) ) {

				$found = true;

			}

			break;
	}

	return (bool) apply_filters( 'edd_is_admin_page', $found, $page, $view );
}