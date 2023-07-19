<?php
/**
 * Customers - Admin Functions.
 *
 * @package     EDD
 * @subpackage  Admin/Customers
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.3
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Register a view for the single customer view
 *
 * @since  2.3
 * @param  array $views An array of existing views
 * @return array        The altered list of views
 */
function edd_register_default_customer_views( $views ) {
	return array_merge( $views, array(
		'overview'  => 'edd_customers_view',
		'emails'    => 'edd_customers_emails_view',
		'addresses' => 'edd_customers_addresses_view',
		'delete'    => 'edd_customers_delete_view',
		'notes'     => 'edd_customer_notes_view',
		'tools'     => 'edd_customer_tools_view',
	) );
}
add_filter( 'edd_customer_views', 'edd_register_default_customer_views', 1, 1 );

/**
 * Register a tab for the single customer view
 *
 * @since  2.3
 * @param  array $tabs An array of existing tabs
 * @return array       The altered list of tabs
 */
function edd_register_default_customer_tabs( $tabs ) {
	return array_merge( $tabs, array(
		'overview'  => array( 'dashicon' => 'dashicons-admin-users',    'title' => _x( 'Profile', 'Customer Details tab title', 'easy-digital-downloads' ) ),
		'emails'    => array( 'dashicon' => 'dashicons-email', 'title' => _x( 'Emails', 'Customer Emails tab title', 'easy-digital-downloads' ) ),
		'addresses' => array( 'dashicon' => 'dashicons-admin-home', 'title' => _x( 'Addresses', 'Customer Addresses tab title', 'easy-digital-downloads' ) ),
		'notes'     => array( 'dashicon' => 'dashicons-admin-comments', 'title' => _x( 'Notes',   'Customer Notes tab title',   'easy-digital-downloads' ) ),
		'tools'     => array( 'dashicon' => 'dashicons-admin-tools',    'title' => _x( 'Tools',   'Customer Tools tab title',   'easy-digital-downloads' ) )
	) );
}
add_filter( 'edd_customer_tabs', 'edd_register_default_customer_tabs', 1, 1 );

/**
 * Register the Delete icon as late as possible so it's at the bottom
 *
 * @since  2.3.1
 * @param  array $tabs An array of existing tabs
 * @return array       The altered list of tabs, with 'delete' at the bottom
 */
function edd_register_delete_customer_tab( $tabs ) {

	$tabs['delete'] = array(
		'dashicon' => 'dashicons-trash',
		'title'    => _x( 'Delete', 'Delete Customer tab title', 'easy-digital-downloads' )
	);

	return $tabs;
}
add_filter( 'edd_customer_tabs', 'edd_register_delete_customer_tab', PHP_INT_MAX, 1 );

/**
 * Remove the admin bar edit profile link when the user is not verified
 *
 * @since  2.4.4
 * @return void
 */
function edd_maybe_remove_adminbar_profile_link() {

	if ( current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	if ( edd_user_pending_verification() ) {
		global $wp_admin_bar;
		$wp_admin_bar->remove_menu( 'edit-profile', 'user-actions' );
	}
}
add_action( 'wp_before_admin_bar_render', 'edd_maybe_remove_adminbar_profile_link' );

/**
 * Remove the admin menus and disable profile access for non-verified users
 *
 * @since  2.4.4
 * @return void
 */
function edd_maybe_remove_menu_profile_links() {

	if ( edd_doing_ajax() ) {
		return;
	}

	if ( current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	if ( edd_user_pending_verification() ) {

		if ( defined( 'IS_PROFILE_PAGE' ) && true === IS_PROFILE_PAGE ) {
			$url = esc_url( edd_get_user_verification_request_url() );
			/* translators: link to send an email */
			$message = sprintf( __( 'Your account is pending verification. Please click the link in your email to activate your account. No email? <a href="%s">Click here</a> to send a new activation code.', 'easy-digital-downloads' ), esc_url( $url ) );
			$title   = __( 'Account Pending Verification', 'easy-digital-downloads' );
			$args    = array(
				'response' => 403,
			);
			wp_die( $message, $title, $args );
		}

		remove_menu_page( 'profile.php' );
		remove_submenu_page( 'users.php', 'profile.php' );
	}
}
add_action( 'admin_init', 'edd_maybe_remove_menu_profile_links' );

/**
 * Add Customer column to Users list table.
 *
 * @since 3.0
 *
 * @param array $columns Existing columns.
 *
 * @return array $columns Columns with `Customer` added.
 */
function edd_add_customer_column_to_users_table( $columns ) {
	$columns['edd_customer'] = __( 'Customer', 'easy-digital-downloads' );
	return $columns;
}
add_filter( 'manage_users_columns', 'edd_add_customer_column_to_users_table' );

/**
 * Display customer details on Users list table.
 *
 * @since 3.0
 *
 * @param string $value       Existing value of the custom column.
 * @param string $column_name Column name.
 * @param int    $user_id     User ID.
 *
 * @return string URL to Customer page, existing value otherwise.
 */
function edd_render_customer_column( $value, $column_name, $user_id ) {
	if ( 'edd_customer' === $column_name ) {
		$customer = new EDD_Customer( $user_id, true );

		if ( $customer->id > 0 ) {
			$name     = '#' . $customer->id . ' ';
			$name    .= ! empty( $customer->name ) ? $customer->name : '<em>' . __( 'Unnamed Customer', 'easy-digital-downloads' ) . '</em>';
			$view_url = edd_get_admin_url(
				array(
					'page' => 'edd-customers',
					'view' => 'overview',
					'id'   => absint( $customer->id ),
				)
			);

			return '<a href="' . esc_url( $view_url ) . '">' . $name . '</a>';
		}
	}

	return $value;
}
add_action( 'manage_users_custom_column',  'edd_render_customer_column', 10, 3 );

/**
 * Renders the customer details header (gravatar/name).
 *
 * @since 3.0
 * @param \EDD_Customer $customer
 * @return void
 */
function edd_render_customer_details_header( \EDD_Customer $customer ) {
	?>
	<div class="edd-item-header-small">
		<?php echo get_avatar( $customer->email, 30 ); ?> <span><?php echo esc_html( $customer->name ); ?></span>
	</div>
	<?php
}
