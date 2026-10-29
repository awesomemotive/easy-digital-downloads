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
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

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
		$this->update_shop_role_caps( 'add_cap' );
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
		$this->update_shop_role_caps( 'remove_cap' );
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

	/**
	 * Grants or revokes every shop capability in one write to the roles option.
	 *
	 * Also rebuilds the role objects and the current user's capabilities, so the
	 * change is effective in the request that made it.
	 *
	 * @since 3.7.3
	 *
	 * @param string $method The WP_Roles method to apply: 'add_cap' or 'remove_cap'.
	 * @return void
	 */
	private function update_shop_role_caps( $method ) {
		$wp_roles = wp_roles();
		$use_db   = $wp_roles->use_db;

		// WP_Roles writes the option once per capability unless use_db is off.
		$wp_roles->use_db = false;
		foreach ( $this->get_shop_role_caps() as $role => $caps ) {
			foreach ( $caps as $cap ) {
				$wp_roles->$method( $role, $cap );
			}
		}
		$wp_roles->use_db = $use_db;

		if ( $use_db ) {
			update_option( $wp_roles->role_key, $wp_roles->roles );
		}

		// WP_Roles::add_cap() and remove_cap() leave the role objects that WP_User reads untouched.
		$wp_roles->init_roles();

		if ( did_action( 'set_current_user' ) ) {
			wp_get_current_user()->get_role_caps();
		}
	}

	/**
	 * Gets the shop capabilities granted to each role.
	 *
	 * @since 3.7.3
	 *
	 * @return array Capability names, keyed by role.
	 */
	private function get_shop_role_caps() {
		$core_caps  = array_merge( ...array_values( $this->get_core_caps() ) );
		$store_caps = array(
			'view_shop_reports',
			'view_shop_sensitive_data',
			'export_shop_reports',
			'manage_shop_settings',
			'manage_shop_discounts',
		);

		return array(
			'shop_manager'    => array_merge( $store_caps, $core_caps ),
			'administrator'   => array_merge( $store_caps, $core_caps ),
			'shop_worker'     => $core_caps,
			'shop_accountant' => array(
				'edit_products',
				'read_private_products',
				'view_shop_reports',
				'export_shop_reports',
				'edit_shop_payments',
			),
			'shop_vendor'     => array(
				'edit_product',
				'edit_products',
				'delete_product',
				'delete_products',
				'publish_products',
				'edit_published_products',
				'upload_files',
				'assign_product_terms',
			),
		);
	}
}
