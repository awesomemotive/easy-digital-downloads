<?php

class EDD_Roles {

	function __construct() {
		$this->add_roles();
		$this->add_caps();
	}


	public function add_roles() {

		add_role( 'shop_manager', __( 'Shop Manager', 'edd' ), array(
		    'read'                   => true,
		    'read_private_pages'     => true,
		    'read_private_posts'     => true,
		    'edit_users'             => true,
		    'edit_posts'             => false,
		    'delete_posts'           => false,
		    'manage_categories'      => true,
		    'manage_links'           => true,
		    'moderate_comments'      => true,
		    'unfiltered_html'        => true,
		    'upload_files'           => true,
		   	'export'                 => true,
			'import'                 => true
		) );

		add_role( 'shop_accountant', __( 'Shop Accountant', 'edd' ), array(
		    'read'                   => true,
		    'edit_posts'             => false,
		    'delete_posts'           => false
		) );

		add_role( 'shop_worker', __( 'Shop Worker', 'edd' ), array(
		    'read'                   => true,
		    'edit_posts'             => false,
		    'delete_posts'           => false
		) );

		add_role( 'shop_vendor', __( 'Shop Vendor', 'edd' ), array(
		    'read'                   => true,
		    'edit_posts'             => false,
		    'delete_posts'           => false
		) );

	}

	/**
	 * Add new capabilities
	 *
	 * @since  1.4.4
	 * @return void
	 */

	public function add_caps() {
		global $wp_roles;

		if ( class_exists('WP_Roles') )
			if ( ! isset( $wp_roles ) )
				$wp_roles = new WP_Roles();

		if ( is_object( $wp_roles ) ) {

			$wp_roles->add_cap( 'shop_manager', 'view_shop_reports' );
			$wp_roles->add_cap( 'shop_manager', 'export_shop_reports' );
			$wp_roles->add_cap( 'shop_manager', 'manage_shop_discounts' );
			$wp_roles->add_cap( 'shop_manager', 'manage_shop_settings' );

			$wp_roles->add_cap( 'administrator', 'view_shop_reports' );
			$wp_roles->add_cap( 'administrator', 'export_shop_reports' );
			$wp_roles->add_cap( 'administrator', 'manage_shop_discounts' );
			$wp_roles->add_cap( 'administrator', 'manage_shop_settings' );

			$wp_roles->add_cap( 'shop_accountant', 'view_shop_reports' );
			$wp_roles->add_cap( 'shop_accountant', 'export_shop_reports' );

		}

	}

}