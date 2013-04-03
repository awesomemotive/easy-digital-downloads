<?php
/**
 * Weclome Page Class
 *
 * @package     EDD
 * @subpackage  Admin/Welcome
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Welcome Class
 *
 * A general class for About and Credits page.
 *
 * @since 1.4
 */
class EDD_Welcome {
	/**
	 * @var string The capability users should have to view the page
	 */
	public $minimum_capability = 'manage_options';

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since 1.4
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menus') );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_init', array( $this, 'welcome'    ) );
	}

	/**
	 * Register the Dashboard Pages which are later hidden but these pages
	 * are used to render the Welcome and Credits pages.
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function admin_menus() {
		// About Page
		add_dashboard_page(
			__( 'Welcome to Easy Digital Downloads', 'edd' ),
			__( 'Welcome to Easy Digital Downloads', 'edd' ),
			$this->minimum_capability,
			'edd-about',
			array( $this, 'about_screen' )
		);

		// Credits Page
		add_dashboard_page(
			__( 'Welcome to Easy Digital Downloads', 'edd' ),
			__( 'Welcome to Easy Digital Downloads', 'edd' ),
			$this->minimum_capability,
			'edd-credits',
			array( $this, 'credits_screen' )
		);
	}

	/**
	 * Hide Individual Dashboard Pages
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function admin_head() {
		remove_submenu_page( 'index.php', 'edd-about' );
		remove_submenu_page( 'index.php', 'edd-credits' );

		// Badge for welcome page
		$badge_url = EDD_PLUGIN_URL . 'assets/images/edd-badge.png';
		?>
		<style type="text/css" media="screen">
		/*<![CDATA[*/
		.edd-badge {
			padding-top: 150px;
			height: 52px;
			width: 185px;
			color: #666;
			font-weight: bold;
			font-size: 14px;
			text-align: center;
			text-shadow: 0 1px 0 rgba(255, 255, 255, 0.8);
			margin: 0 -5px;
			background: url('<?php echo $badge_url; ?>') no-repeat;
		}

		.about-wrap .edd-badge {
			position: absolute;
			top: 0;
			right: 0;
		}
		/*]]>*/
		</style>
		<?php
	}

	/**
	 * Render About Screen
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function about_screen() {
		list( $display_version ) = explode( '-', EDD_VERSION );
		?>
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to Easy Digital Downloads %s', 'edd' ), $display_version ); ?></h1>
			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! Easy Digital Downloads %s is ready to make your online store faster, safer and better!', 'edd' ), $display_version ); ?></div>
			<div class="edd-badge"><?php printf( __( 'Version %s', 'edd' ), $display_version ); ?></div>

			<h2 class="nav-tab-wrapper">
				<a class="nav-tab nav-tab-active" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-about' ), 'index.php' ) ) ); ?>">
					<?php _e( "What's New", 'edd' ); ?>
				</a><a class="nav-tab" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-credits' ), 'index.php' ) ) ); ?>">
					<?php _e( 'Credits', 'edd' ); ?>
				</a>
			</h2>

			<div class="changelog">
				<h3><?php _e( 'Improved Checkout User Experience', 'edd' ); ?></h3>

				<div class="feature-section">

					<h4><?php _e( 'Preload Default Payment Method', 'edd' ); ?></h4>
					<p><?php _e( 'You can now define the default payment method for customers that will be loaded immediately when accessing the checkout page. This saves the customer a click and results in more conversions for you.', 'edd' ); ?></p>

					<h4><?php _e( 'Better Payment Method Select', 'edd' ); ?></h4>
					<p><?php _e( 'Payment methods are now displayed as radio buttons, making the options more accessible and easier to see / understand for customers.', 'edd' ); ?></p>

					<h4><?php _e( 'Field Descriptions', 'edd' ); ?></h4>
					<p><?php _e( 'It has been proven by countless studies that descriptive text by every field helps customers complete the purchase process, so we have added description text for each field.', 'edd' ); ?></p>

					<h4><?php _e( 'Reworked Field Order', 'edd' ); ?></h4>
					<p><?php _e( 'The order that the fields are displayed on the checkout has been updated to reflect the findings of many UX studies to help ensure customers have a simple, enjoyable experiencing purchasing through your store.', 'edd' ); ?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Greatly Improved Discount Codes', 'edd' ); ?></h3>

				<div class="feature-section">

					<h4><?php _e( 'Per-Product Discounts', 'edd' ); ?></h4>
					<p><?php _e( 'Discount codes can now be restricted to individual (or several) products, giving you greater control over your marketing.', 'edd' ); ?></p>

					<h4><?php _e( 'Once-Per-Customer Discounts', 'edd' ); ?></h4>
					<p><?php _e( 'You can now specify on a per-discount basis whether customers should be able to use a discount more than once.', 'edd' ); ?></p>


				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Improved Tax Options', 'edd' ); ?></h3>

				<div class="feature-section">

					<h4><?php _e( 'Checkout Display Options', 'edd' ); ?></h4>
					<p><?php _e( 'New options to better control how taxes are displayed on checkout have been added.', 'edd' );  ?></p>

					<h4><?php _e( 'Better Tax Calculation', 'edd' ); ?></h4>
					<p><?php _e( 'Taxes are now more accurately calculated and there is an option to set product prices as inclusive or exclusive of tax.', 'edd' );  ?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Improved Reports and Data Export', 'edd' ); ?></h3>

				<div class="feature-section">

					<h4><?php _e( 'Per-Product Customer Export', 'edd' ); ?></h4>
					<p><?php printf( __( 'You can now export all customers that have purchased a particular product from the %sExport%s screen.', 'edd' ), '<a href="' . admin_url( 'edit.php?post_type=download&page=edd-reports&tab=export' ) . '">', '</a>' );  ?></p>

					<h4><?php _e( 'Export Payment History By Status', 'edd' ); ?></h4>
					<p><?php _e( 'The Payment History export now includes an option to only export payments of a particular status. Want to export all of your failed payments? Now you can.', 'edd' );  ?></p>

					<h4><?php _e( 'Estimated Monthly Stats', 'edd' ); ?></h4>
					<p><?php _e( 'Monthly estimates for sales and earnings are now displayed below the graphs in the Reports page.', 'edd' );  ?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'RESTful API', 'edd' ); ?></h3>

				<div class="feature-section">
					<h4><?php _e( 'Creation of Third Party Stat Tracking Apps Now Possible', 'edd' ); ?></h4>
					<p><?php _e( 'The new RESTful API available in Easy Digital Downloads makes it possible to create 3rd party apps (iOS, Android, etc) for tracking your store sales and earnings.', 'edd' );  ?></p>

					<p><?php printf( __( 'The API is %sfully documented%s and ready for developers to have fun with.', 'edd' ), '<a href="https://easydigitaldownloads.com/docs/edd-api-reference/" target="_blank">', '</a>' ); ?></p>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Under the Hood', 'edd' ); ?></h3>

				<div class="feature-section col three-col">
					<div>
						<h4><?php _e( 'EDD_Fees Class', 'edd' ); ?></h4>
						<p><?php printf( __( 'The new %sEDD_Fees class%s makes it possible to create arbitrary fees (or discounts) that are applied to the shopping cart contents.', 'edd' ), '<a href="https://github.com/pippinsplugins/Easy-Digital-Downloads/issues/418" target="_blank">', '</a>' ); ?></p>

						<h4><?php _e( 'Better Session Management', 'edd' ); ?></h4>
						<p><?php printf( __( 'We have replaced usage of the standard PHP $_SESSION with the phenomenal %sWP_Session%s system developed by Eric Mann. This will provide a more stable experience and greater support for more hosts.', 'edd' ), '<a href="http://eamann.com/tech/introducing-wp_session/" target="_blank">', '</a>' ); ?></p>
					</div>

					<div>
						<h4><?php _e( 'More Template Files', 'edd' ); ?></h4>
						<p><?php printf( __( 'Additional %stemplate files%s that can be modified via your theme have been added for the cart widget and shopping cart short code.', 'edd' ), '<a href="https://easydigitaldownloads.com/videos/template-files/" target="_blank">', '</a>' ); ?></p>

						<h4><?php _e( 'Better AJAX Functionality', 'edd' ); ?></h4>
						<p><?php _e( 'The ajaxed functions, such as payment gateway loading, have been significantly improved to make them faster.', 'edd' ); ?></p>
					</div>

					<div class="last-feature">
						<h4><?php _e( 'New Product Microdata', 'edd' ); ?></h4>
						<p><?php _e( 'Microdata defined by Schema.org/Product has been added to all download products to improve product appearance in search engines.', 'edd' ); ?></p>

						<h4><?php _e( 'Improved Performance', 'edd' ); ?></h4>
						<p><?php _e( 'Memory usage and general performance of the plugin was investigated and dramatically improved in several key areas of the admin interfaces.', 'edd' ); ?></p>
					</div>
				</div>
			</div>

			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'post_type' => 'download', 'page' => 'edd-settings' ), 'edit.php' ) ) ); ?>"><?php _e( 'Go to Easy Digital Downloads Settings', 'edd' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Credits Screen
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function credits_screen() {
		list( $display_version ) = explode( '-', EDD_VERSION );
		?>
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to Easy Digital Downloads %s', 'edd' ), $display_version ); ?></h1>
			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! Easy Digital Downloads %s is ready to make your online store faster, safer and better!', 'edd' ), $display_version ); ?></div>
			<div class="edd-badge"><?php printf( __( 'Version %s', 'edd' ), $display_version ); ?></div>

			<h2 class="nav-tab-wrapper">
				<a class="nav-tab" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-about' ), 'index.php' ) ) ); ?>">
					<?php _e( "What's New", 'edd' ); ?>
				</a><a class="nav-tab nav-tab-active" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-credits' ), 'index.php' ) ) ); ?>">
					<?php _e( 'Credits', 'edd' ); ?>
				</a>
			</h2>

			<p class="about-description"><?php _e( 'Easy Digital Downloads is created by a worldwide team of developers who aim to provide the #1 eCommerce platform for selling digital goods through WordPress.', 'edd' ); ?></p>

			<?php echo $this->contributors(); ?>
		</div>
		<?php
	}


	/**
	 * Render Contributors List
	 *
	 * @since 1.4
	 * @uses EDD_Welcome::get_contributors()
	 * @return string $contributor_list HTML formatted list of all the contributors for EDD
	 */
	public function contributors() {
		$contributors = $this->get_contributors();

		if ( empty( $contributors ) )
			return '';

		$contributor_list = '<ul class="wp-people-group">';

		foreach ( $contributors as $contributor ) {
			$contributor_list .= '<li class="wp-person">';
			$contributor_list .= sprintf( '<a href="%s" title="%s">',
				esc_url( 'https://github.com/' . $contributor->login ),
				esc_html( sprintf( __( 'View %s', 'edd' ), $contributor->login ) )
			);
			$contributor_list .= sprintf( '<img src="%s" width="64" height="64" class="gravatar" alt="%s" />', esc_url( $contributor->avatar_url ), esc_html( $contributor->login ) );
			$contributor_list .= '</a>';
			$contributor_list .= sprintf( '<a class="web" href="%s">%s</a>', esc_url( 'https://github.com/' . $contributor->login ), esc_html( $contributor->login ) );
			$contributor_list .= '</a>';
			$contributor_list .= '</li>';
		}

		$contributor_list .= '</ul>';

		return $contributor_list;
	}

	/**
	 * Retreive list of contributors from GitHub.
	 *
	 * @access public
	 * @since 1.4
	 * @return array $contributors List of contributors
	 */
	public function get_contributors() {
		$contributors = get_transient( 'edd_contributors' );

		if ( false !== $contributors )
			return $contributors;

		$response = wp_remote_get( 'https://api.github.com/repos/pippinsplugins/Easy-Digital-Downloads/contributors', array( 'sslverify' => false ) );

		if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) )
			return array();

		$contributors = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! is_array( $contributors ) )
			return array();

		set_transient( 'edd_contributors', $contributors, 3600 );

		return $contributors;
	}

	/**
	 * Sends user to the Welcome page on first activation of EDD as well as each
	 * time EDD is upgraded to a new version
	 *
	 * @access public
	 * @since 1.4
	 * @global $edd_options Array of all the EDD Options
	 * @return void
	 */
	public function welcome() {
		global $edd_options;

		// Bail if no activation redirect
		if ( ! get_transient( '_edd_activation_redirect' ) )
			return;

		// Delete the redirect transient
		delete_transient( '_edd_activation_redirect' );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) )
			return;

		wp_safe_redirect( admin_url( 'index.php?page=edd-about' ) ); exit;
	}
}
new EDD_Welcome();