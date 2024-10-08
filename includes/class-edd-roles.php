<?php
/**
 * Roles and Capabilities
 *
 * @package     EDD
 * @subpackage  Roles
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.4
*/

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Roles Class
 *
 * This class handles the role creation and assignment of capabilities for those
 * roles.
 *
 * These roles let us have Shop Accountants, Shop Workers, etc, each of whom
 * can do certain things within the EDD store.
 *
 * @since 1.4.4
 */
class EDD_Roles {

	/**
	 * Constructor.
	 *
	 * @since 1.4.4
	 */
	public function __construct() {
		add_filter( 'map_meta_cap', array( $this, 'meta_caps' ), 10, 4 );
		add_action( 'manage_users_extra_tablenav', array( $this, 'add_reset_tool' ) );
		add_action( 'edd_reset_capabilities', array( $this, 'reset_capabilities' ) );
	}

	/**
	 * Add new shop roles with default WordPress capabilities.
	 *
	 * @since 1.4.4
	 */
	public function add_roles() {
		add_role(
			'shop_manager',
			__( 'Shop Manager', 'easy-digital-downloads' ),
			array(
				'read'                   => true,
				'edit_posts'             => true,
				'delete_posts'           => true,
				'unfiltered_html'        => true,
				'upload_files'           => true,
				'export'                 => true,
				'import'                 => true,
				'delete_others_pages'    => true,
				'delete_others_posts'    => true,
				'delete_pages'           => true,
				'delete_private_pages'   => true,
				'delete_private_posts'   => true,
				'delete_published_pages' => true,
				'delete_published_posts' => true,
				'edit_others_pages'      => true,
				'edit_others_posts'      => true,
				'edit_pages'             => true,
				'edit_private_pages'     => true,
				'edit_private_posts'     => true,
				'edit_published_pages'   => true,
				'edit_published_posts'   => true,
				'manage_categories'      => true,
				'manage_links'           => true,
				'moderate_comments'      => true,
				'publish_pages'          => true,
				'publish_posts'          => true,
				'read_private_pages'     => true,
				'read_private_posts'     => true,
			)
		);

		add_role(
			'shop_accountant',
			__( 'Shop Accountant', 'easy-digital-downloads' ),
			array(
				'read'         => true,
				'edit_posts'   => false,
				'delete_posts' => false,
			)
		);

		add_role(
			'shop_worker',
			__( 'Shop Worker', 'easy-digital-downloads' ),
			array(
				'read'         => true,
				'edit_posts'   => false,
				'upload_files' => true,
				'delete_posts' => false,
			)
		);

		add_role(
			'shop_vendor',
			__( 'Shop Vendor', 'easy-digital-downloads' ),
			array(
				'read'         => true,
				'edit_posts'   => false,
				'upload_files' => true,
				'delete_posts' => false,
			)
		);
	}

	/**
	 * Add new shop-specific capabilities.
	 *
	 * @since  1.4.4
	 */
	public function add_caps() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles(); // WPCS: override ok.
			}
		}

		if ( is_object( $wp_roles ) ) {
			$wp_roles->add_cap( 'shop_manager', 'view_shop_reports' );
			$wp_roles->add_cap( 'shop_manager', 'view_shop_sensitive_data' );
			$wp_roles->add_cap( 'shop_manager', 'export_shop_reports' );
			$wp_roles->add_cap( 'shop_manager', 'manage_shop_settings' );
			$wp_roles->add_cap( 'shop_manager', 'manage_shop_discounts' );

			$wp_roles->add_cap( 'administrator', 'view_shop_reports' );
			$wp_roles->add_cap( 'administrator', 'view_shop_sensitive_data' );
			$wp_roles->add_cap( 'administrator', 'export_shop_reports' );
			$wp_roles->add_cap( 'administrator', 'manage_shop_discounts' );
			$wp_roles->add_cap( 'administrator', 'manage_shop_settings' );

			// Add the main post type capabilities.
			$capabilities = $this->get_core_caps();
			foreach ( $capabilities as $cap_group ) {
				foreach ( $cap_group as $cap ) {
					$wp_roles->add_cap( 'shop_manager', $cap );
					$wp_roles->add_cap( 'administrator', $cap );
					$wp_roles->add_cap( 'shop_worker', $cap );
				}
			}

			$wp_roles->add_cap( 'shop_accountant', 'edit_products' );
			$wp_roles->add_cap( 'shop_accountant', 'read_private_products' );
			$wp_roles->add_cap( 'shop_accountant', 'view_shop_reports' );
			$wp_roles->add_cap( 'shop_accountant', 'export_shop_reports' );
			$wp_roles->add_cap( 'shop_accountant', 'edit_shop_payments' );

			$wp_roles->add_cap( 'shop_vendor', 'edit_product' );
			$wp_roles->add_cap( 'shop_vendor', 'edit_products' );
			$wp_roles->add_cap( 'shop_vendor', 'delete_product' );
			$wp_roles->add_cap( 'shop_vendor', 'delete_products' );
			$wp_roles->add_cap( 'shop_vendor', 'publish_products' );
			$wp_roles->add_cap( 'shop_vendor', 'edit_published_products' );
			$wp_roles->add_cap( 'shop_vendor', 'upload_files' );
			$wp_roles->add_cap( 'shop_vendor', 'assign_product_terms' );
		}
	}

	/**
	 * Gets the core post type capabilities.
	 *
	 * @since 1.4.4
	 *
	 * @return array $capabilities Core post type capabilities.
	 */
	public function get_core_caps() {
		$capabilities = array();

		$capability_types = array( 'product', 'shop_payment', 'shop_discount' );

		foreach ( $capability_types as $capability_type ) {
			$capabilities[ $capability_type ] = array(
				// Post type
				"edit_{$capability_type}",
				"read_{$capability_type}",
				"delete_{$capability_type}",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"publish_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",

				// Terms
				"manage_{$capability_type}_terms",
				"edit_{$capability_type}_terms",
				"delete_{$capability_type}_terms",
				"assign_{$capability_type}_terms",

				// Custom
				"view_{$capability_type}_stats",
				"import_{$capability_type}s",
			);
		}

		return $capabilities;
	}

	/**
	 * Map meta caps to primitive caps.
	 *
	 * @since 2.0
	 *
	 * @param array  $caps    Capabilities for meta capability.
	 * @param string $cap     Capability name.
	 * @param int    $user_id User ID.
	 * @param mixed  $args    Arguments.
	 *
	 * @return array $caps
	 */
	public function meta_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {

		// Ensure user ID is a valid integer.
		$user_id = absint( $user_id );

		switch ( $cap ) {
			case 'view_product_stats':
				if ( empty( $args[0] ) ) {
					break;
				}

				$download = get_post( $args[0] );

				// Bail if download was not found.
				if ( empty( $download ) ) {
					break;
				}

				// No stats for auto-drafts.
				if ( 'auto-draft' === $download->post_status ) {
					$caps = array( 'do_not_allow' );
					break;
				}

				if ( user_can( $user_id, 'view_shop_reports' ) || absint( $download->post_author ) === $user_id ) {
					$caps = array();
				}

				break;
		}

		return $caps;
	}

	/**
	 * Remove core post type capabilities (called on uninstall).
	 *
	 * @since 1.5.2
	 */
	public function remove_caps() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles(); // WPCS: override ok.
			}
		}

		if ( is_object( $wp_roles ) ) {

			/** Shop Manager Capabilities */
			$wp_roles->remove_cap( 'shop_manager', 'view_shop_reports' );
			$wp_roles->remove_cap( 'shop_manager', 'view_shop_sensitive_data' );
			$wp_roles->remove_cap( 'shop_manager', 'export_shop_reports' );
			$wp_roles->remove_cap( 'shop_manager', 'manage_shop_discounts' );
			$wp_roles->remove_cap( 'shop_manager', 'manage_shop_settings' );

			/** Site Administrator Capabilities */
			$wp_roles->remove_cap( 'administrator', 'view_shop_reports' );
			$wp_roles->remove_cap( 'administrator', 'view_shop_sensitive_data' );
			$wp_roles->remove_cap( 'administrator', 'export_shop_reports' );
			$wp_roles->remove_cap( 'administrator', 'manage_shop_discounts' );
			$wp_roles->remove_cap( 'administrator', 'manage_shop_settings' );

			/** Remove the Main Post Type Capabilities */
			$capabilities = $this->get_core_caps();

			foreach ( $capabilities as $cap_group ) {
				foreach ( $cap_group as $cap ) {
					$wp_roles->remove_cap( 'shop_manager', $cap );
					$wp_roles->remove_cap( 'administrator', $cap );
					$wp_roles->remove_cap( 'shop_worker', $cap );
				}
			}

			/** Shop Accountant Capabilities */
			$wp_roles->remove_cap( 'shop_accountant', 'edit_products' );
			$wp_roles->remove_cap( 'shop_accountant', 'read_private_products' );
			$wp_roles->remove_cap( 'shop_accountant', 'view_shop_reports' );
			$wp_roles->remove_cap( 'shop_accountant', 'export_shop_reports' );

			/** Shop Vendor Capabilities */
			$wp_roles->remove_cap( 'shop_vendor', 'edit_product' );
			$wp_roles->remove_cap( 'shop_vendor', 'edit_products' );
			$wp_roles->remove_cap( 'shop_vendor', 'delete_product' );
			$wp_roles->remove_cap( 'shop_vendor', 'delete_products' );
			$wp_roles->remove_cap( 'shop_vendor', 'publish_products' );
			$wp_roles->remove_cap( 'shop_vendor', 'edit_published_products' );
			$wp_roles->remove_cap( 'shop_vendor', 'upload_files' );
		}
	}

	/**
	 * Adds a capabiilities reset tool to the users.php page.
	 *
	 * @since 3.3.4
	 * @param string $which
	 * @return void
	 */
	public function add_reset_tool( $which ) {
		if ( 'top' === $which ) {
			return;
		}
		if ( ! current_user_can( 'edit_users' ) ) {
			return;
		}
		$url = wp_nonce_url(
			add_query_arg(
				'edd-action',
				'reset_capabilities',
				admin_url( 'users.php' )
			),
			'edd-reset-capabilities'
		);
		?>
		<div class="alignleft actions">
			<a href="<?php echo esc_url( $url ); ?>" class="button"><?php esc_html_e( 'Reset EDD User Roles', 'easy-digital-downloads' ); ?></a>
		</div>
		<?php
	}

	/**
	 * Resets the capabilities for the shop roles.
	 *
	 * @since 3.3.4
	 * @param array $data
	 * @return void
	 */
	public function reset_capabilities( $data ) {
		if ( ! current_user_can( 'edit_users' ) ) {
			edd_redirect( admin_url( 'users.php' ) );
		}
		if ( empty( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'edd-reset-capabilities' ) ) {
			edd_redirect( admin_url( 'users.php' ) );
		}
		$this->add_roles();
		$this->add_caps();

		edd_redirect(
			add_query_arg(
				'edd-message',
				'capabilities_reset',
				admin_url( 'users.php' )
			)
		);
	}
}
