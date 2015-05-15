<?php
/**
 * Easy Digital Downloads API
 *
 * This class provides a front-facing JSON/XML API that makes it possible to
 * query data from the shop.
 *
 * The primary purpose of this class is for external sales / earnings tracking
 * systems, such as mobile. This class is also used in the EDD iOS App.
 *
 * @package     EDD
 * @subpackage  Classes/API
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_API Class
 *
 * Renders API returns as a JSON/XML array
 *
 * @since  1.5
 */
class EDD_API {

	/**
	 * Latest API Version
	 */
	const VERSION = 1;

	/**
	 * Pretty Print?
	 *
	 * @var bool
	 * @access private
	 * @since 1.5
	 */
	private $pretty_print = false;

	/**
	 * Log API requests?
	 *
	 * @var bool
	 * @access private
	 * @since 1.5
	 */
	public $log_requests = true;

	/**
	 * Is this a valid request?
	 *
	 * @var bool
	 * @access private
	 * @since 1.5
	 */
	private $is_valid_request = false;

	/**
	 * User ID Performing the API Request
	 *
	 * @var int
	 * @access private
	 * @since 1.5.1
	 */
	public $user_id = 0;

	/**
	 * Instance of EDD Stats class
	 *
	 * @var object
	 * @access private
	 * @since 1.7
	 */
	private $stats;

	/**
	 * Response data to return
	 *
	 * @var array
	 * @access private
	 * @since 1.5.2
	 */
	private $data = array();

	/**
	 *
	 * @var bool
	 * @access private
	 * @since 1.7
	 */
	public $override = true;

	/**
	 * Version of the API queried
	 *
	 * @var string
	 * @access public
	 * @since 2.4
	 */
	private $queried_version;

	/**
	 * All versions of the API
	 *
	 * @var string
	 * @access public
	 * @since 2.4
	 */
	protected $versions = array();

	/**
	 * Queried endpoint
	 *
	 * @var string
	 * @access public
	 * @since 2.4
	 */
	private $endpoint;

	/**
	 * Endpoints routes
	 *
	 * @var object
	 * @access public
	 * @since 2.4
	 */
	private $routes;

	/**
	 * Setup the EDD API
	 *
	 * @author Daniel J Griffiths
	 * @since 1.5
	 */
	public function __construct() {

		$this->versions = array(
			'v1' => 'EDD_API_V1',
		);

		require_once EDD_PLUGIN_DIR . 'includes/api/class-edd-api-base.php';

		foreach( $this->versions as $version => $class ) {
			require_once EDD_PLUGIN_DIR . 'includes/api/class-edd-api-' . $version . '.php';
		}

		add_action( 'init',                     array( $this, 'add_endpoint'     ) );
		add_action( 'template_redirect',        array( $this, 'process_query'    ), -1 );
		add_filter( 'query_vars',               array( $this, 'query_vars'       ) );
		add_action( 'show_user_profile',        array( $this, 'user_key_field'   ) );
		add_action( 'edit_user_profile',        array( $this, 'user_key_field'   ) );
		add_action( 'personal_options_update',  array( $this, 'update_key'       ) );
		add_action( 'edit_user_profile_update', array( $this, 'update_key'       ) );
		add_action( 'edd_process_api_key',      array( $this, 'process_api_key'  ) );

		// Determine if JSON_PRETTY_PRINT is available
		$this->pretty_print = defined( 'JSON_PRETTY_PRINT' ) ? JSON_PRETTY_PRINT : null;

		// Allow API request logging to be turned off
		$this->log_requests = apply_filters( 'edd_api_log_requests', $this->log_requests );

		// Setup EDD_Stats instance
		$this->stats = new EDD_Payment_Stats;

	}

	/**
	 * Registers a new rewrite endpoint for accessing the API
	 *
	 * @access public
	 * @author Daniel J Griffiths
	 * @param array $rewrite_rules WordPress Rewrite Rules
	 * @since 1.5
	 */
	public function add_endpoint( $rewrite_rules ) {
		add_rewrite_endpoint( 'edd-api', EP_ALL );
	}

	/**
	 * Registers query vars for API access
	 *
	 * @access public
	 * @since 1.5
	 * @author Daniel J Griffiths
	 * @param array $vars Query vars
	 * @return string[] $vars New query vars
	 */
	public function query_vars( $vars ) {

		$vars[] = 'token';
		$vars[] = 'key';
		$vars[] = 'query';
		$vars[] = 'type';
		$vars[] = 'product';
		$vars[] = 'number';
		$vars[] = 'date';
		$vars[] = 'startdate';
		$vars[] = 'enddate';
		$vars[] = 'customer';
		$vars[] = 'discount';
		$vars[] = 'format';
		$vars[] = 'id';
		$vars[] = 'purchasekey';
		$vars[] = 'email';

		return $vars;
	}

	/**
	 * Sets the version of the API that was queried.
	 *
	 * Falls back to the default version if no version is specified
	 *
	 * @access private
	 * @since 2.4
	 */
	private function set_queried_version() {

		global $wp_query;

		$version = $wp_query->query_vars['edd-api'];

		if( strpos( $version, '/' ) ) {

			$version = explode( '/', $version );
			$version = strtolower( $version[0] );

			$wp_query->query_vars['edd-api'] = str_replace( $version . '/', '', $wp_query->query_vars['edd-api'] );

			if( array_key_exists( $version, $this->versions ) ) {

				$this->queried_version = $version;

			} else {

				$this->queried_version = $this->get_default_version();

			}

		} else {

			$this->queried_version = $this->get_default_version();

		}

	}

	/**
	 * Retrieves the default version of the API to use
	 *
	 * @access private
	 * @since 2.4
	 * @return string
	 */
	private function get_default_version() {

		$version = get_option( 'edd_default_api_version' );

		if( defined( 'EDD_API_VERSION' ) ) {
			$version = EDD_API_VERSION;
		} elseif( ! $version ) {
			$version = 'v1';
		}

		return $version;
	}

	/**
	 * Validate the API request
	 *
	 * Checks for the user's public key and token against the secret key
	 *
	 * @access private
	 * @global object $wp_query WordPress Query
	 * @uses EDD_API::get_user()
	 * @uses EDD_API::invalid_key()
	 * @uses EDD_API::invalid_auth()
	 * @since 1.5
	 * @return void
	 */
	private function validate_request() {
		global $wp_query;

		$this->override = false;

        // Make sure we have both user and api key
		if ( ! empty( $wp_query->query_vars['edd-api'] ) && ( $wp_query->query_vars['edd-api'] != 'products' || ! empty( $wp_query->query_vars['token'] ) ) ) {

			if ( empty( $wp_query->query_vars['token'] ) || empty( $wp_query->query_vars['key'] ) ) {
				$this->missing_auth();
			}

			// Retrieve the user by public API key and ensure they exist
			if ( ! ( $user = $this->get_user( $wp_query->query_vars['key'] ) ) ) {

				$this->invalid_key();

			} else {

				$token  = urldecode( $wp_query->query_vars['token'] );
				$secret = get_user_meta( $user, 'edd_user_secret_key', true );
				$public = urldecode( $wp_query->query_vars['key'] );

				if ( hash_equals( md5( $secret . $public ), $token ) ) {
					$this->is_valid_request = true;
				} else {
					$this->invalid_auth();
				}
			}
		} elseif ( ! empty( $wp_query->query_vars['edd-api'] ) && $wp_query->query_vars['edd-api'] == 'products' ) {
			$this->is_valid_request = true;
			$wp_query->set( 'key', 'public' );
		}
	}

	/**
	 * Retrieve the user ID based on the public key provided
	 *
	 * @access public
	 * @since 1.5.1
	 * @global object $wpdb Used to query the database using the WordPress
	 * Database API
	 *
	 * @param string $key Public Key
	 *
	 * @return bool if user ID is found, false otherwise
	 */
	public function get_user( $key = '' ) {
		global $wpdb, $wp_query;

		if( empty( $key ) ) {
			$key = urldecode( $wp_query->query_vars['key'] );
		}

		if ( empty( $key ) ) {
			return false;
		}

		$user = get_transient( md5( 'edd_api_user_' . $key ) );

		if ( false === $user ) {
			$user = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'edd_user_public_key' AND meta_value = %s LIMIT 1", $key ) );
			set_transient( md5( 'edd_api_user_' . $key ) , $user, DAY_IN_SECONDS );
		}

		if ( $user != NULL ) {
			$this->user_id = $user;
			return $user;
		}

		return false;
	}

	/**
	 * Displays a missing authentication error if all the parameters aren't
	 * provided
	 *
	 * @access private
	 * @author Daniel J Griffiths
	 * @uses EDD_API::output()
	 * @since 1.5
	 */
	private function missing_auth() {
		$error = array();
		$error['error'] = __( 'You must specify both a token and API key!', 'edd' );

		$this->data = $error;
		$this->output( 401 );
	}

	/**
	 * Displays an authentication failed error if the user failed to provide valid
	 * credentials
	 *
	 * @access private
	 * @since  1.5
	 * @uses EDD_API::output()
	 * @return void
	 */
	private function invalid_auth() {
		$error = array();
		$error['error'] = __( 'Your request could not be authenticated!', 'edd' );

		$this->data = $error;
		$this->output( 401 );
	}

	/**
	 * Displays an invalid API key error if the API key provided couldn't be
	 * validated
	 *
	 * @access private
	 * @author Daniel J Griffiths
	 * @since 1.5
	 * @uses EDD_API::output()
	 * @return void
	 */
	private function invalid_key() {
		$error = array();
		$error['error'] = __( 'Invalid API key!', 'edd' );

		$this->data = $error;
		$this->output( 401 );
	}


	/**
	 * Listens for the API and then processes the API requests
	 *
	 * @access public
	 * @author Daniel J Griffiths
	 * @global $wp_query
	 * @since 1.5
	 * @return void
	 */
	public function process_query() {

		global $wp_query;

		// Check for edd-api var. Get out if not present
		if ( ! isset( $wp_query->query_vars['edd-api'] ) ) {
			return;
		}

		// Determine which version was queried
		$this->set_queried_version();
//		echo $this->get_query_mode(); exit;

		// Determine the kind of query
		$this->set_query_mode();

		// Check for a valid user and set errors if necessary
		$this->validate_request();

		// Only proceed if no errors have been noted
		if( ! $this->is_valid_request ) {
			return;
		}

		if( ! defined( 'EDD_DOING_API' ) ) {
			define( 'EDD_DOING_API', true );
		}

		$data = array();
		$this->routes = new $this->versions[ $this->queried_version ];

		switch( $this->endpoint ) :

			case 'stats' :

				$data = $this->routes->get_stats( array(
					'type'      => isset( $wp_query->query_vars['type'] )      ? $wp_query->query_vars['type']      : null,
					'product'   => isset( $wp_query->query_vars['product'] )   ? $wp_query->query_vars['product']   : null,
					'date'      => isset( $wp_query->query_vars['date'] )      ? $wp_query->query_vars['date']      : null,
					'startdate' => isset( $wp_query->query_vars['startdate'] ) ? $wp_query->query_vars['startdate'] : null,
					'enddate'   => isset( $wp_query->query_vars['enddate'] )   ? $wp_query->query_vars['enddate']   : null
				) );

				break;

			case 'products' :

				$product = isset( $wp_query->query_vars['product'] )   ? $wp_query->query_vars['product']   : null;

				$data = $this->routes->get_products( $product );

				break;

			case 'customers' :

				$customer = isset( $wp_query->query_vars['customer'] ) ? $wp_query->query_vars['customer']  : null;

				$data = $this->routes->get_customers( $customer );

				break;

			case 'sales' :

				$data = $this->routes->get_recent_sales();

				break;

			case 'discounts' :

				$discount = isset( $wp_query->query_vars['discount'] ) ? $wp_query->query_vars['discount']  : null;

				$data = $this->routes->get_discounts( $discount );

				break;

		endswitch;

		// Allow extensions to setup their own return data
		$this->data = apply_filters( 'edd_api_output_data', $data, $this->endpoint, $this );

		// Log this API request, if enabled. We log it here because we have access to errors.
		$this->log_request( $this->data );

		// Send out data to the output function
		$this->output();
	}

	/**
	 * Returns the API endpoint requested
	 *
	 * @access private
	 * @since 1.5
	 * @return string $query Query mode
	 */
	public function get_query_mode() {

		return $this->endpoint;
	}

	/**
	 * Determines the kind of query requested and also ensure it is a valid query
	 *
	 * @access private
	 * @since 2.4
	 * @global $wp_query
	 */
	public function set_query_mode() {

		global $wp_query;

		// Whitelist our query options
		$accepted = apply_filters( 'edd_api_valid_query_modes', array(
			'stats',
			'products',
			'customers',
			'sales',
			'discounts'
		) );

		$query = isset( $wp_query->query_vars['edd-api'] ) ? $wp_query->query_vars['edd-api'] : null;
		$query = str_replace( $this->queried_version . '/', '', $query );

		$error = array();

		// Make sure our query is valid
		if ( ! in_array( $query, $accepted ) ) {
			$error['error'] = __( 'Invalid query!', 'edd' );

			$this->data = $error;
			$this->output();
		}

		$this->endpoint = $query;
	}


	/**
	 * Retrieve the output format
	 *
	 * Determines whether results should be displayed in XML or JSON
	 *
	 * @since 1.5
	 *
	 * @return mixed|void
	 */
	public function get_output_format() {
		global $wp_query;

		$format = isset( $wp_query->query_vars['format'] ) ? $wp_query->query_vars['format'] : 'json';

		return apply_filters( 'edd_api_output_format', $format );
	}


	/**
	 * Log each API request, if enabled
	 *
	 * @access private
	 * @since  1.5
	 * @global $edd_logs
	 * @global $wp_query
	 * @param array $data
	 * @return void
	 */
	private function log_request( $data = array() ) {
		if ( ! $this->log_requests )
			return;

		global $edd_logs, $wp_query;

		$query = array(
			'edd-api'     => $wp_query->query_vars['edd-api'],
			'key'         => $wp_query->query_vars['key'],
			'token'       => $wp_query->query_vars['token'],
			'query'       => isset( $wp_query->query_vars['query'] )       ? $wp_query->query_vars['query']       : null,
			'type'        => isset( $wp_query->query_vars['type'] )        ? $wp_query->query_vars['type']        : null,
			'product'     => isset( $wp_query->query_vars['product'] )     ? $wp_query->query_vars['product']     : null,
			'customer'    => isset( $wp_query->query_vars['customer'] )    ? $wp_query->query_vars['customer']    : null,
			'date'        => isset( $wp_query->query_vars['date'] )        ? $wp_query->query_vars['date']        : null,
			'startdate'   => isset( $wp_query->query_vars['startdate'] )   ? $wp_query->query_vars['startdate']   : null,
			'enddate'     => isset( $wp_query->query_vars['enddate'] )     ? $wp_query->query_vars['enddate']     : null,
			'id'          => isset( $wp_query->query_vars['id'] )          ? $wp_query->query_vars['id']          : null,
			'purchasekey' => isset( $wp_query->query_vars['purchasekey'] ) ? $wp_query->query_vars['purchasekey'] : null,
			'email'       => isset( $wp_query->query_vars['email'] )       ? $wp_query->query_vars['email']       : null,
		);

		$log_data = array(
			'log_type'     => 'api_request',
			'post_excerpt' => http_build_query( $query ),
			'post_content' => ! empty( $data['error'] ) ? $data['error'] : '',
		);

		$log_meta = array(
			'request_ip' => edd_get_ip(),
			'user'       => $this->user_id,
			'key'        => $wp_query->query_vars['key'],
			'token'      => $wp_query->query_vars['token']
		);

		$edd_logs->insert_log( $log_data, $log_meta );
	}


	/**
	 * Retrieve the output data
	 *
	 * @access public
	 * @since 1.5.2
	 * @return array
	 */
	public function get_output() {
		return $this->data;
	}

	/**
	 * Output Query in either JSON/XML. The query data is outputted as JSON
	 * by default
	 *
	 * @author Daniel J Griffiths
	 * @since 1.5
	 * @global $wp_query
	 *
	 * @param int $status_code
	 */
	public function output( $status_code = 200 ) {
		global $wp_query;

		$format = $this->get_output_format();

		status_header( $status_code );

		do_action( 'edd_api_output_before', $this->data, $this, $format );

		switch ( $format ) :

			case 'xml' :

				require_once EDD_PLUGIN_DIR . 'includes/libraries/array2xml.php';
				$xml = Array2XML::createXML( 'edd', $this->data );
				echo $xml->saveXML();

				break;

			case 'json' :

				header( 'Content-Type: application/json' );
				if ( ! empty( $this->pretty_print ) )
					echo json_encode( $this->data, $this->pretty_print );
				else
					echo json_encode( $this->data );

				break;


			default :

				// Allow other formats to be added via extensions
				do_action( 'edd_api_output_' . $format, $this->data, $this );

				break;

		endswitch;

		do_action( 'edd_api_output_after', $this->data, $this, $format );

		edd_die();
	}

	/**
	 * Modify User Profile
	 *
	 * Modifies the output of profile.php to add key generation/revocation
	 *
	 * @access public
	 * @author Daniel J Griffiths
	 * @since 1.5
	 * @param object $user Current user info
	 * @return void
	 */
	function user_key_field( $user ) {
		if ( ( edd_get_option( 'api_allow_user_keys', false ) || current_user_can( 'manage_shop_settings' ) ) && current_user_can( 'edit_user', $user->ID ) ) {
			$user = get_userdata( $user->ID );
			?>
			<table class="form-table">
				<tbody>
					<tr>
						<th>
							<label for="edd_set_api_key"><?php _e( 'Easy Digital Downloads API Keys', 'edd' ); ?></label>
						</th>
						<td>
							<?php if ( empty( $user->edd_user_public_key ) ) { ?>
								<input name="edd_set_api_key" type="checkbox" id="edd_set_api_key" value="0" />
								<span class="description"><?php _e( 'Generate API Key', 'edd' ); ?></span>
							<?php } else { ?>
								<strong><?php _e( 'Public key:', 'edd' ); ?>&nbsp;</strong><span id="publickey"><?php echo $user->edd_user_public_key; ?></span><br/>
								<strong><?php _e( 'Secret key:', 'edd' ); ?>&nbsp;</strong><span id="privatekey"><?php echo $user->edd_user_secret_key; ?></span><br/>
								<strong><?php _e( 'Token:', 'edd' ); ?>&nbsp;</strong><span id="token"><?php echo $this->get_token( $user->ID ); ?></span><br/>
								<input name="edd_set_api_key" type="checkbox" id="edd_set_api_key" value="0" />
								<span class="description"><?php _e( 'Revoke API Keys', 'edd' ); ?></span>
							<?php } ?>
						</td>
					</tr>
				</tbody>
			</table>
		<?php }
	}

	/**
	 * Process an API key generation/revocation
	 *
	 * @access public
	 * @since 2.0.0
	 * @param array $args
	 * @return void
	 */
	public function process_api_key( $args ) {

		if( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'edd-api-nonce' ) ) {

			wp_die( __( 'Nonce verification failed', 'edd' ), __( 'Error', 'edd' ), array( 'response' => 403 ) );

		}

		if( is_numeric( $args['user_id'] ) ) {
			$user_id    = isset( $args['user_id'] ) ? absint( $args['user_id'] ) : get_current_user_id();
		} else {
			$userdata   = get_user_by( 'login', $args['user_id'] );
			$user_id    = $userdata->ID;
		}
		$process    = isset( $args['edd_api_process'] ) ? strtolower( $args['edd_api_process'] ) : false;

		if( $user_id == get_current_user_id() && ! edd_get_option( 'allow_user_api_keys' ) && ! current_user_can( 'manage_shop_settings' ) ) {
			wp_die( sprintf( __( 'You do not have permission to %s API keys for this user', 'edd' ), $process ), __( 'Error', 'edd' ), array( 'response' => 403 ) );
		} elseif( ! current_user_can( 'manage_shop_settings' ) ) {
			wp_die( sprintf( __( 'You do not have permission to %s API keys for this user', 'edd' ), $process ), __( 'Error', 'edd' ), array( 'response' => 403 ) );
		}

		switch( $process ) {
			case 'generate':
				if( $this->generate_api_key( $user_id ) ) {
					delete_transient( 'edd-total-api-keys' );
					wp_redirect( add_query_arg( 'edd-message', 'api-key-generated', 'edit.php?post_type=download&page=edd-tools&tab=api_keys' ) ); exit();
				} else {
					wp_redirect( add_query_arg( 'edd-message', 'api-key-failed', 'edit.php?post_type=download&page=edd-tools&tab=api_keys' ) ); exit();
				}
				break;
			case 'regenerate':
				$this->generate_api_key( $user_id, true );
				delete_transient( 'edd-total-api-keys' );
				wp_redirect( add_query_arg( 'edd-message', 'api-key-regenerated', 'edit.php?post_type=download&page=edd-tools&tab=api_keys' ) ); exit();
				break;
			case 'revoke':
				$this->revoke_api_key( $user_id );
				delete_transient( 'edd-total-api-keys' );
				wp_redirect( add_query_arg( 'edd-message', 'api-key-revoked', 'edit.php?post_type=download&page=edd-tools&tab=api_keys' ) ); exit();
				break;
			default;
				break;
		}
	}

	/**
	 * Generate new API keys for a user
	 *
	 * @access public
	 * @since 2.0.0
	 * @param int $user_id User ID the key is being generated for
	 * @param boolean $regenerate Regenerate the key for the user
	 * @return boolean True if (re)generated succesfully, false otherwise.
	 */
	public function generate_api_key( $user_id = 0, $regenerate = false ) {

		if( empty( $user_id ) ) {
			return false;
		}

		$user = get_userdata( $user_id );

		if( ! $user ) {
			return false;
		}

		if ( empty( $user->edd_user_public_key ) ) {
			update_user_meta( $user_id, 'edd_user_public_key', $this->generate_public_key( $user->user_email ) );
			update_user_meta( $user_id, 'edd_user_secret_key', $this->generate_private_key( $user->ID ) );
		} elseif( $regenerate == true ) {
			$this->revoke_api_key( $user->ID );
			update_user_meta( $user_id, 'edd_user_public_key', $this->generate_public_key( $user->user_email ) );
			update_user_meta( $user_id, 'edd_user_secret_key', $this->generate_private_key( $user->ID ) );
		} else {
			return false;
		}

		return true;
	}

	/**
	 * Revoke a users API keys
	 *
	 * @access public
	 * @since 2.0.0
	 * @param int $user_id User ID of user to revoke key for
	 * @return string
	 */
	public function revoke_api_key( $user_id = 0 ) {

		if( empty( $user_id ) ) {
			return false;
		}

		$user = get_userdata( $user_id );

		if( ! $user ) {
			return false;
		}

		if ( ! empty( $user->edd_user_public_key ) ) {
			delete_transient( md5( 'edd_api_user_' . $user->edd_user_public_key ) );
			delete_user_meta( $user_id, 'edd_user_public_key' );
			delete_user_meta( $user_id, 'edd_user_secret_key' );
		} else {
			return false;
		}

		return true;
	}


	/**
	 * Generate and Save API key
	 *
	 * Generates the key requested by user_key_field and stores it in the database
	 *
	 * @access public
	 * @author Daniel J Griffiths
	 * @since 1.5
	 * @param int $user_id
	 * @return void
	 */
	public function update_key( $user_id ) {
		if ( current_user_can( 'edit_user', $user_id ) && isset( $_POST['edd_set_api_key'] ) ) {

			$user = get_userdata( $user_id );

			if ( empty( $user->edd_user_public_key ) ) {
				update_user_meta( $user_id, 'edd_user_public_key', $this->generate_public_key( $user->user_email ) );
				update_user_meta( $user_id, 'edd_user_secret_key', $this->generate_private_key( $user->ID ) );
			} else {
				$this->revoke_api_key( $user_id );
			}
		}
	}

	/**
	 * Generate the public key for a user
	 *
	 * @access private
	 * @since 1.9.9
	 * @param string $user_email
	 * @return string
	 */
	private function generate_public_key( $user_email = '' ) {
		$auth_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
		$public   = hash( 'md5', $user_email . $auth_key . date( 'U' ) );
		return $public;
	}

	/**
	 * Generate the secret key for a user
	 *
	 * @access private
	 * @since 1.9.9
	 * @param int $user_id
	 * @return string
	 */
	private function generate_private_key( $user_id = 0 ) {
		$auth_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
		$secret   = hash( 'md5', $user_id . $auth_key . date( 'U' ) );
		return $secret;
	}

	/**
	 * Retrieve the user's token
	 *
	 * @access private
	 * @since 1.9.9
	 * @param int $user_id
	 * @return string
	 */
	private function get_token( $user_id = 0 ) {
		$user = get_userdata( $user_id );
		return hash( 'md5', $user->edd_user_secret_key . $user->edd_user_public_key );
	}

	/**
	 * Generate the default sales stats returned by the 'stats' endpoint
	 *
	 * @access private
	 * @since 1.5.3
	 * @return array default sales statistics
	 */
	private function get_default_sales_stats() {

		// Default sales return
		$sales = array();
		$sales['sales']['today']         = $this->stats->get_sales( 0, 'today' );
		$sales['sales']['current_month'] = $this->stats->get_sales( 0, 'this_month' );
		$sales['sales']['last_month']    = $this->stats->get_sales( 0, 'last_month' );
		$sales['sales']['totals']        = edd_get_total_sales();

		return $sales;
	}

	/**
	 * Generate the default earnings stats returned by the 'stats' endpoint
	 *
	 * @access private
	 * @since 1.5.3
	 * @return array default earnings statistics
	 */
	private function get_default_earnings_stats() {

		// Default earnings return
		$earnings = array();
		$earnings['earnings']['today']         = $this->stats->get_earnings( 0, 'today' );
		$earnings['earnings']['current_month'] = $this->stats->get_earnings( 0, 'this_month' );
		$earnings['earnings']['last_month']    = $this->stats->get_earnings( 0, 'last_month' );
		$earnings['earnings']['totals']        = edd_get_total_earnings();

		return $earnings;
	}
}
