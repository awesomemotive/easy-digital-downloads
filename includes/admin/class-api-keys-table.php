<?php
/**
 * API Key Table Class
 *
 * @package     EDD
 * @subpackage  Admin/Tools/APIKeys
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * EDD_API_Keys_Table Class
 *
 * Renders the API Keys table
 *
 * @since 2.0
 */
class EDD_API_Keys_Table extends WP_List_Table {

	/**
	 * @var int Number of items per page
	 * @since 2.0
	 */
	public $per_page = 30;

	/**
	 * @var object Query results
	 * @since 2.0
	 */
	private $keys;

	/**
	 * Get things started
	 *
	 * @since 1.5
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular'  => __( 'API Key', 'easy-digital-downloads' ),
			'plural'    => __( 'API Keys', 'easy-digital-downloads' ),
			'ajax'      => false,
		) );

		$this->query();
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 2.5
	 * @access protected
	 *
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'user';
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @since 2.0
	 *
	 * @param array $item Contains all the data of the keys
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * Displays the public key rows
	 *
	 * @since 2.4
	 *
	 * @param array $item Contains all the data of the keys
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_key( $item ) {
		return '<input readonly="readonly" type="text" class="large-text" value="' . esc_attr( $item[ 'key' ] ) . '"/>';
	}

	/**
	 * Displays the token rows
	 *
	 * @since 2.4
	 *
	 * @param array $item Contains all the data of the keys
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_token( $item ) {
		return '<input readonly="readonly" type="text" class="large-text" value="' . esc_attr( $item[ 'token' ] ) . '"/>';
	}

	/**
	 * Displays the secret key rows
	 *
	 * @since 2.4
	 *
	 * @param array $item Contains all the data of the keys
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_secret( $item ) {
		return '<input readonly="readonly" type="text" class="large-text" value="' . esc_attr( $item[ 'secret' ] ) . '"/>';
	}

	/**
	 * Renders the column for the user field
	 *
	 * @since 2.0
	 * @return void
	 */
	public function column_user( $item ) {

		$actions = array();

		if( apply_filters( 'edd_api_log_requests', true ) ) {
			$actions['view'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( add_query_arg( array( 'view' => 'api_requests', 'post_type' => 'download', 'page' => 'edd-reports', 'tab' => 'logs', 's' => $item['email'] ), 'edit.php' ) ),
				__( 'View API Log', 'easy-digital-downloads' )
			);
		}

		$actions['reissue'] = sprintf(
			'<a href="%s" class="edd-regenerate-api-key">%s</a>',
			esc_url( wp_nonce_url( add_query_arg( array( 'user_id' => $item['id'], 'edd_action' => 'process_api_key', 'edd_api_process' => 'regenerate' ) ), 'edd-api-nonce' ) ),
			__( 'Reissue', 'easy-digital-downloads' )
		);
		$actions['revoke'] = sprintf(
			'<a href="%s" class="edd-revoke-api-key edd-delete">%s</a>',
			esc_url( wp_nonce_url( add_query_arg( array( 'user_id' => $item['id'], 'edd_action' => 'process_api_key', 'edd_api_process' => 'revoke' ) ), 'edd-api-nonce' ) ),
			__( 'Revoke', 'easy-digital-downloads' )
		);

		$actions = apply_filters( 'edd_api_row_actions', array_filter( $actions ) );

		return sprintf('%1$s %2$s', $item['user'], $this->row_actions( $actions ) );
	}

	/**
	 * Retrieve the table columns
	 *
	 * @since 2.0
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'user'   => __( 'Username', 'easy-digital-downloads' ),
			'key'    => __( 'Public Key', 'easy-digital-downloads' ),
			'token'  => __( 'Token', 'easy-digital-downloads' ),
			'secret' => __( 'Secret Key', 'easy-digital-downloads' ),
		);

		return $columns;
	}

	/**
	 * Display the key generation form
	 *
	 * @since 1.5
	 * @return void
	 */
	public function bulk_actions( $which = '' ) {
		// These aren't really bulk actions but this outputs the markup in the right place
		static $edd_api_is_bottom;

		if( $edd_api_is_bottom ) {
			return;
		}
		?>
		<form id="api-key-generate-form" method="post" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-tools&tab=api_keys' ); ?>">
			<input type="hidden" name="edd_action" value="process_api_key" />
			<input type="hidden" name="edd_api_process" value="generate" />
			<?php wp_nonce_field( 'edd-api-nonce' ); ?>
			<?php echo EDD()->html->ajax_user_search(); ?>
			<?php submit_button( __( 'Generate New API Keys', 'easy-digital-downloads' ), 'secondary', 'submit', false ); ?>
		</form>
		<?php
		$edd_api_is_bottom = true;
	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 * @since 3.1.0
	 * @access protected
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {
		if ( 'top' === $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		}
?>
	<div class="tablenav <?php echo esc_attr( $which ); ?>">

		<div class="alignleft actions bulkactions">
			<?php $this->bulk_actions( $which ); ?>
		</div>
<?php
		$this->extra_tablenav( $which );
		$this->pagination( $which );
?>

		<br class="clear" />
	</div>
<?php
	}

	/**
	 * Retrieve the current page number
	 *
	 * @since 2.0
	 * @return int Current page number
	 */
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}

	/**
	 * Performs the key query
	 *
	 * @since 2.0
	 * @return void
	 */
	public function query() {
		$users    = get_users( array(
			'meta_value' => 'edd_user_secret_key',
			'number'     => $this->per_page,
			'offset'     => $this->per_page * ( $this->get_paged() - 1 ),
		) );
		$keys     = array();

		foreach( $users as $user ) {
			$keys[$user->ID]['id']     = $user->ID;
			$keys[$user->ID]['email']  = $user->user_email;
			$keys[$user->ID]['user']   = '<a href="' . add_query_arg( 'user_id', $user->ID, 'user-edit.php' ) . '"><strong>' . $user->user_login . '</strong></a>';

			$keys[$user->ID]['key']    = EDD()->api->get_user_public_key( $user->ID );
			$keys[$user->ID]['secret'] = EDD()->api->get_user_secret_key( $user->ID );
			$keys[$user->ID]['token']  = EDD()->api->get_token( $user->ID );
		}

		return $keys;
	}



	/**
	 * Retrieve count of total users with keys
	 *
	 * @since 2.0
	 * @return int
	 */
	public function total_items() {
		global $wpdb;

		if( ! get_transient( 'edd_total_api_keys' ) ) {
			$total_items = $wpdb->get_var( "SELECT count(user_id) FROM $wpdb->usermeta WHERE meta_value='edd_user_secret_key'" );

			set_transient( 'edd_total_api_keys', $total_items, 60 * 60 );
		}

		return get_transient( 'edd_total_api_keys' );
	}

	/**
	 * Setup the final data for the table
	 *
	 * @since 2.0
	 * @return void
	 */
	public function prepare_items() {
		$columns = $this->get_columns();

		$hidden = array(); // No hidden columns
		$sortable = array(); // Not sortable... for now

		$this->_column_headers = array( $columns, $hidden, $sortable, 'user' );

		$data = $this->query();

		$total_items = $this->total_items();

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page ),
			)
		);
	}
}
