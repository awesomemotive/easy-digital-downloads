<?php
/**
 * Weclome Page Class
 *
 * @package     EDD
 * @subpackage  Admin/Welcome
 * @copyright   Copyright (c) 2015, Pippin Williamson
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
	 * @since 1.4
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

		// Changelog Page
		add_dashboard_page(
			__( 'Easy Digital Downloads Changelog', 'edd' ),
			__( 'Easy Digital Downloads Changelog', 'edd' ),
			$this->minimum_capability,
			'edd-changelog',
			array( $this, 'changelog_screen' )
		);

		// Getting Started Page
		add_dashboard_page(
			__( 'Getting started with Easy Digital Downloads', 'edd' ),
			__( 'Getting started with Easy Digital Downloads', 'edd' ),
			$this->minimum_capability,
			'edd-getting-started',
			array( $this, 'getting_started_screen' )
		);

		// Credits Page
		add_dashboard_page(
			__( 'The people that build Easy Digital Downloads', 'edd' ),
			__( 'The people that build Easy Digital Downloads', 'edd' ),
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
		remove_submenu_page( 'index.php', 'edd-changelog' );
		remove_submenu_page( 'index.php', 'edd-getting-started' );
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

		.edd-welcome-screenshots {
			float: right;
			margin-left: 10px!important;
		}

		.about-wrap .feature-section {
			margin-top: 20px;
		}

		/*]]>*/
		</style>
		<?php
	}

	/**
	 * Navigation tabs
	 *
	 * @access public
	 * @since 1.9
	 * @return void
	 */
	public function tabs() {
		$selected = isset( $_GET['page'] ) ? $_GET['page'] : 'edd-about';
		?>
		<h2 class="nav-tab-wrapper">
			<a class="nav-tab <?php echo $selected == 'edd-about' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-about' ), 'index.php' ) ) ); ?>">
				<?php _e( "What's New", 'edd' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'edd-getting-started' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-getting-started' ), 'index.php' ) ) ); ?>">
				<?php _e( 'Getting Started', 'edd' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'edd-credits' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-credits' ), 'index.php' ) ) ); ?>">
				<?php _e( 'Credits', 'edd' ); ?>
			</a>
		</h2>
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
			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! Easy Digital Downloads %s is ready to make your online store faster, safer, and better!', 'edd' ), $display_version ); ?></div>
			<div class="edd-badge"><?php printf( __( 'Version %s', 'edd' ), $display_version ); ?></div>

			<?php $this->tabs(); ?>

			<div class="changelog">
				<h3><?php _e( 'New Customer Management UI', 'edd' );?></h3>

				<div class="feature-section">

					<p><?php _e( 'Version 2.3 introduces a comprehensive customer management interface. Get detailed statistics on your customers, quickly make edits, and leave detailed notes.', 'edd' );?></p>

					<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/customer-ui.png'; ?>" class="edd-welcome-screenshots"/>

					<h4><?php _e( 'Better Customer Details on Payment', 'edd' );?></h4>
					<p><?php _e( 'The Customer Details section of the View Order Details screen has been updated to make it easier to move payment records between customers. A quick link to the customer\'s overview page has also been added, letting you easily see all purchases made by the customer.', 'edd' );?></p>

					<h4><?php _e( 'Create New Customers Quickly', 'edd' );?></h4>
					<p><?php _e( 'New customer records can now be created directly from the View Order Details screen, making it easier for you to manage change requests from your customers.', 'edd' );?></p>

					<h4><?php _e( 'More Accessible', 'edd' );?></h4>
					<p><?php _e( 'Your store\'s customer history has been moved to a dedicated submenu of the Downloads menu, making it significantly easier to locate. No longer is it hidden in an obscure section of the Reports page.', 'edd' );?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Signed URLs', 'edd' );?></h3>

				<div class="feature-section">

					<p><?php _e( 'We take security seriously and with Easy Digital Downloads 2.3, we have introduced a better, more secure file download mechanism that keeps your files safe and secure.', 'edd' );?></p>
					<p><?php _e( 'Download URLs provided to customers have been improved to be shorter and more secure, and now use tokens to verify the URL prior to allowing the download to start.', 'edd' );?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Improved Order Details', 'edd' );?></h3>

				<div class="feature-section">

					<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/22-purchased-downloads2.png'; ?>" class="edd-welcome-screenshots"/>

					<h4><?php _e( 'Focus on Purchased Downloads', 'edd' );?></h4>
					<p><?php _e( 'The Download products your customer has purchased is one of the most important parts of this screen, so we have put it up front and in the spotlight.', 'edd' );?></p>

					<h4><?php _e( 'More Accurate Statistics', 'edd' );?></h4>
					<p><?php _e( 'Adding or removing Downloads to and from a payment record now properly triggers an update routine to ensure the sales and earnings for the affected products are properly updated.', 'edd' );?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Additional Updates', 'edd' );?></h3>

				<div class="feature-section col three-col">
					<div>

						<h4><?php _e( 'PolyLang Support', 'edd' );?></h4>
						<p><?php _e( 'We\'ve improved support for the popular PolyLang Plugin in 2.3 making EDD more accessible in more languages.', 'edd' );?></p>

						<h4><?php _e( 'Customer API', 'edd' );?></h4>
						<p><?php _e( 'A new EDD_Customer class has been introduced that makes it easy for developers to interact with customer data.', 'edd' );?></p>

					</div>

					<div>

						<h4><?php _e( 'Schema Validation', 'edd' );?></h4>
						<p><?php _e( 'The Schema Markup has been improved and now properly includes prices for both single and multi-price option products.' ,'edd' );?></p>

						<h4><?php _e( 'Buy Now Button Improvements', 'edd' );?></h4>
						<p><?php _e( 'Buy Now buttons no longer create pending payment records when they are clicked. Buy Now buttons are now automatically deactivated if no supported payment gateway is activated.' ,'edd' );?></p>

					</div>

					<div class="last-feature">

						<h4><?php _e( 'Improved Upgrade Routine API', 'edd' );?></h4>
						<p><?php _e( 'The upgrade routine has been improved to be more robust and user friendly. It now supports multiple upgrades in a single release, logs which have been completed ,as well as allows incomplete upgrades to be resumed.', 'edd' );?></p>

					</div>

				</div>
			</div>

			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'post_type' => 'download', 'page' => 'edd-settings' ), 'edit.php' ) ) ); ?>"><?php _e( 'Go to Easy Digital Downloads Settings', 'edd' ); ?></a> &middot;
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-changelog' ), 'index.php' ) ) ); ?>"><?php _e( 'View the Full Changelog', 'edd' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Changelog Screen
	 *
	 * @access public
	 * @since 2.0.3
	 * @return void
	 */
	public function changelog_screen() {
		list( $display_version ) = explode( '-', EDD_VERSION );
		?>
		<div class="wrap about-wrap">
			<h1><?php _e( 'Easy Digital Downloads Changelog', 'edd' ); ?></h1>
			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! Easy Digital Downloads %s is ready to make your online store faster, safer, and better!', 'edd' ), $display_version ); ?></div>
			<div class="edd-badge"><?php printf( __( 'Version %s', 'edd' ), $display_version ); ?></div>

			<?php $this->tabs(); ?>

			<div class="changelog">
				<h3><?php _e( 'Full Changelog', 'edd' );?></h3>

				<div class="feature-section">
					<?php echo $this->parse_readme(); ?>
				</div>
			</div>

			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'post_type' => 'download', 'page' => 'edd-settings' ), 'edit.php' ) ) ); ?>"><?php _e( 'Go to Easy Digital Downloads Settings', 'edd' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Getting Started Screen
	 *
	 * @access public
	 * @since 1.9
	 * @return void
	 */
	public function getting_started_screen() {
		list( $display_version ) = explode( '-', EDD_VERSION );
		?>
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to Easy Digital Downloads %s', 'edd' ), $display_version ); ?></h1>
			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! Easy Digital Downloads %s is ready to make your online store faster, safer and better!', 'edd' ), $display_version ); ?></div>
			<div class="edd-badge"><?php printf( __( 'Version %s', 'edd' ), $display_version ); ?></div>

			<?php $this->tabs(); ?>

			<p class="about-description"><?php _e( 'Use the tips below to get started using Easy Digital Downloads. You will be up and running in no time!', 'edd' ); ?></p>

			<div class="changelog">
				<h3><?php _e( 'Creating Your First Download Product', 'edd' );?></h3>

				<div class="feature-section">

					<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/edit-download.png'; ?>" class="edd-welcome-screenshots"/>

					<h4><?php printf( __( '<a href="%s">%s &rarr; Add New</a>', 'edd' ), admin_url( 'post-new.php?post_type=download' ), edd_get_label_plural() ); ?></h4>
					<p><?php printf( __( 'The %s menu is your access point for all aspects of your Easy Digital Downloads product creation and setup. To create your first product, simply click Add New and then fill out the product details.', 'edd' ), edd_get_label_plural() ); ?></p>

					<h4><?php _e( 'Product Price', 'edd' );?></h4>
					<p><?php _e( 'Products can have simple prices or variable prices if you wish to have more than one price point for a product. For a single price, simply enter the price. For multiple price points, click <em>Enable variable pricing</em> and enter the options.', 'edd' );?></p>

					<h4><?php _e( 'Download Files', 'edd' );?></h4>
					<p><?php _e( 'Uploading the downloadable files is simple. Click <em>Upload File</em> in the Download Files section and choose your download file. To add more than one file, simply click the <em>Add New</em> button.', 'edd' );?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Display a Product Grid', 'edd' );?></h3>

				<div class="feature-section">

					<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/grid.png'; ?>" class="edd-welcome-screenshots"/>

					<h4><?php _e( 'Flexible Product Grids','edd' );?></h4>
					<p><?php _e( 'The [downloads] shortcode will display a product grid that works with any theme, no matter the size. It is even responsive!', 'edd' );?></p>

					<h4><?php _e( 'Change the Number of Columns', 'edd' );?></h4>
					<p><?php _e( 'You can easily change the number of columns by adding the columns="x" parameter:', 'edd' );?></p>
					<p><pre>[downloads columns="4"]</pre></p>

					<h4><?php _e( 'Additional Display Options', 'edd' ); ?></h4>
					<p><?php printf( __( 'The product grids can be customized in any way you wish and there is <a href="%s">extensive documentation</a> to assist you.', 'edd' ), 'http://easydigitaldownloads.com/documentation' ); ?></p>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Purchase Buttons Anywhere', 'edd' );?></h3>

				<div class="feature-section">

					<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/purchase-link.png'; ?>" class="edd-welcome-screenshots"/>

					<h4><?php _e( 'The <em>[purchase_link]</em> Shortcode','edd' );?></h4>
					<p><?php _e( 'With easily accessible shortcodes to display purchase buttons, you can add a Buy Now or Add to Cart button for any product anywhere on your site in seconds.', 'edd' );?></p>

					<h4><?php _e( 'Buy Now Buttons', 'edd' );?></h4>
					<p><?php _e( 'Purchase buttons can behave as either Add to Cart or Buy Now buttons. With Buy Now buttons customers are taken straight to PayPal, giving them the most frictionless purchasing experience possible.', 'edd' );?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Need Help?', 'edd' );?></h3>

				<div class="feature-section">

					<h4><?php _e( 'Phenomenal Support','edd' );?></h4>
					<p><?php _e( 'We do our best to provide the best support we can. If you encounter a problem or have a question, post a question in the <a href="https://easydigitaldownloads.com/support">support forums</a>.', 'edd' );?></p>

					<h4><?php _e( 'Need Even Faster Support?', 'edd' );?></h4>
					<p><?php _e( 'Our <a href="https://easydigitaldownloads.com/support/pricing/">Priority Support forums</a> are there for customers that need faster and/or more in-depth assistance.', 'edd' );?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Stay Up to Date', 'edd' );?></h3>

				<div class="feature-section">

					<h4><?php _e( 'Get Notified of Extension Releases','edd' );?></h4>
					<p><?php _e( 'New extensions that make Easy Digital Downloads even more powerful are released nearly every single week. Subscribe to the newsletter to stay up to date with our latest releases. <a href="http://eepurl.com/kaerz" target="_blank">Signup now</a> to ensure you do not miss a release!', 'edd' );?></p>

					<h4><?php _e( 'Get Alerted About New Tutorials', 'edd' );?></h4>
					<p><?php _e( '<a href="http://eepurl.com/kaerz" target="_blank">Signup now</a> to hear about the latest tutorial releases that explain how to take Easy Digital Downloads further.', 'edd' );?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Extensions for Everything', 'edd' );?></h3>

				<div class="feature-section">

					<h4><?php _e( 'Over 250 Extensions','edd' );?></h4>
					<p><?php _e( 'Add-on plugins are available that greatly extend the default functionality of Easy Digital Downloads. There are extensions for payment processors, such as Stripe and PayPal, extensions for newsletter integrations, and many, many more.', 'edd' );?></p>

					<h4><?php _e( 'Visit the Extension Store', 'edd' );?></h4>
					<p><?php _e( '<a href="https://easydigitaldownloads.com/extensions" target="_blank">The Extensions store</a> has a list of all available extensions, including convenient category filters so you can find exactly what you are looking for.', 'edd' );?></p>

				</div>
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

			<?php $this->tabs(); ?>

			<p class="about-description"><?php _e( 'Easy Digital Downloads is created by a worldwide team of developers who aim to provide the #1 eCommerce platform for selling digital goods through WordPress.', 'edd' ); ?></p>

			<?php echo $this->contributors(); ?>
		</div>
		<?php
	}


	/**
	 * Parse the EDD readme.txt file
	 *
	 * @since 2.0.3
	 * @return string $readme HTML formatted readme file
	 */
	public function parse_readme() {
		$file = file_exists( EDD_PLUGIN_DIR . 'readme.txt' ) ? EDD_PLUGIN_DIR . 'readme.txt' : null;

		if ( ! $file ) {
			$readme = '<p>' . __( 'No valid changlog was found.', 'edd' ) . '</p>';
		} else {
			$readme = file_get_contents( $file );
			$readme = nl2br( esc_html( $readme ) );
			$readme = explode( '== Changelog ==', $readme );
			$readme = end( $readme );

			$readme = preg_replace( '/`(.*?)`/', '<code>\\1</code>', $readme );
			$readme = preg_replace( '/[\040]\*\*(.*?)\*\*/', ' <strong>\\1</strong>', $readme );
			$readme = preg_replace( '/[\040]\*(.*?)\*/', ' <em>\\1</em>', $readme );
			$readme = preg_replace( '/= (.*?) =/', '<h4>\\1</h4>', $readme );
			$readme = preg_replace( '/\[(.*?)\]\((.*?)\)/', '<a href="\\2">\\1</a>', $readme );
		}

		return $readme;
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

		$response = wp_remote_get( 'https://api.github.com/repos/easydigitaldownloads/Easy-Digital-Downloads/contributors', array( 'sslverify' => false ) );

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
	 * @return void
	 */
	public function welcome() {
		// Bail if no activation redirect
		if ( ! get_transient( '_edd_activation_redirect' ) )
			return;

		// Delete the redirect transient
		delete_transient( '_edd_activation_redirect' );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) )
			return;

		$upgrade = get_option( 'edd_version_upgraded_from' );

		if( ! $upgrade ) { // First time install
			wp_safe_redirect( admin_url( 'index.php?page=edd-getting-started' ) ); exit;
		} else { // Update
			wp_safe_redirect( admin_url( 'index.php?page=edd-about' ) ); exit;
		}
	}
}
new EDD_Welcome();
