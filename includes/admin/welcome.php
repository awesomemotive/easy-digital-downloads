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
		
		?>
		<style type="text/css" media="screen">
		/*<![CDATA[*/

		.about-wrap .edd-badge {
			position: absolute;
			top: 0;
			right: 0;
			width: 100px;
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
			<div class="edd-badge"><img src="<?php echo EDD_PLUGIN_URL . 'assets/images/edd-logo.svg'; ?>" alt="<?php _e( 'Easy Digital Downloads', 'edd' ); ?>" / ></div>

			<?php $this->tabs(); ?>

			<div class="changelog">
				<h3><?php _e( 'Amazon Payments', 'edd' );?></h3>

				<div class="feature-section">

					<p><?php _e( 'With Easy Digital Downloads version 2.4, you can now accept payments through Amazon\'s Login and Pay with the new built-in payment gateway.', 'edd' );?></p>

					<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/24-checkout.png'; ?>" class="edd-welcome-screenshots"/>

					<h4><?php _e( 'Secure Checkout', 'edd' );?></h4>
					<p><?php _e( 'When using Amazon Payments, credit / debit card details are entered on Amazon\'s secure servers and never pass through your own server, making the entire process dramatically more secure and reliable.', 'edd' );?></p>

					<h4><?php _e( 'Accept Credit and Debit Card Payments', 'edd' );?></h4>
					<p><?php _e( 'Amazon Payments allows your customers to easily pay with their debit or credit cards. During checkout, customers will be provided an option to use a stored card or enter a new one.', 'edd' );?></p>

					<h4><?php _e( 'Simple Customer Authentication', 'edd' );?></h4>
					<p><?php _e( 'Customers can log into their Amazon account from your checkout screen and have all of their billing details retrieved automatically from Amazon. With just a few clicks, customers can effortlessly complete their purchase.', 'edd' );?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Earnings / Sales By Category', 'edd' );?></h3>

				<div class="feature-section">

					<p><?php _e( 'Easy Digital Downloads version 2.4 introduces a new Report that displays earnings and sales for your product categories.', 'edd' );?></p>

					<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/24-category-earnings.png'; ?>" class="edd-welcome-screenshots"/>

					<h4><?php _e( 'Earnings and Sales Overview', 'edd' );?></h4>
					<p><?php _e( 'Quickly see how each of your categories has performed over the lifetime of your store. The total sales and earnings are displayed, as well as the average monthly sales and earnings for each category.', 'edd' );?></p>

					<h4><?php _e( 'Category Sales / Earnings Mix', 'edd' );?></h4>
					<p><?php _e( 'The report includes a visual break down of the sales / earnings mix for your categories. Quickly see which categories account for the highest (or lowest) percentage of your sales and earnings.', 'edd' );?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Improved Data Export', 'edd' );?></h3>

				<div class="feature-section">

					<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/24-export.png'; ?>" class="edd-welcome-screenshots"/>

					<h4><?php _e( 'Big Data Support', 'edd' );?></h4>
					<p><?php _e( 'With the new export processing in Easy Digital Downloads 2.4, you can easily export massive amounts of data. Need to export 20,000 payment records? No problem.', 'edd' );?></p>

					<h4><?php _e( 'Standardized Customer Export', 'edd' );?></h4>
					<p><?php _e( 'The Customer export has been standarized so it now produces the same data during export for all export options. It can also easily handle 20,000 or even 50,000 customer records in a single export.', 'edd' );?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Additional Updates', 'edd' );?></h3>

				<div class="feature-section col three-col">
					<div>

						<h4><?php _e( 'REST API Versioning', 'edd' );?></h4>
						<p><?php _e( 'The REST API now supports a version parameter that allows you to specify which version of the API you wish to use.', 'edd' );?></p>

						<h4><?php _e( 'Better Cart Tax Display', 'edd' );?></h4>
						<p><?php _e( 'Cart widgets now display estimated taxes for customers before reaching the checkout page.', 'edd' );?></p>

					</div>

					<div>

						<h4><?php _e( 'Customer > User Synchronization', 'edd' );?></h4>
						<p><?php _e( 'Customer email addresses are now updated when the associated user account\'s email is changed.' ,'edd' );?></p>

						<h4><?php _e( 'Better Test Mode Settings', 'edd' );?></h4>
						<p><?php _e( 'Test Mode has been improved by moving the option to the Payment Gateways screen. Sales / earnings stats are now incremented in test mode.', 'edd' );?></p>

					</div>

					<div class="last-feature">

						<h4><?php _e( 'Exclude Taxes from Reports', 'edd' );?></h4>
						<p><?php _e( 'Earnings and sales reports can now be shown exclusive of tax, allowing you to easily see how your store is performing after taxes.', 'edd' );?></p>

						<h4><?php _e( 'Default Gateway First', 'edd' );?></h4>
						<p><?php _e( 'The gateway selected as the default option will always be displayed first on checkout.' ,'edd' );?></p>

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
			<div class="edd-badge"><img src="<?php echo EDD_PLUGIN_URL . 'assets/images/edd-logo.svg'; ?>" alt="<?php _e( 'Easy Digital Downloads', 'edd' ); ?>" / ></div>

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
			<div class="edd-badge"><img src="<?php echo EDD_PLUGIN_URL . 'assets/images/edd-logo.svg'; ?>" alt="<?php _e( 'Easy Digital Downloads', 'edd' ); ?>" / ></div>

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
					<p><?php printf( __( 'The product grids can be customized in any way you wish and there is <a href="%s">extensive documentation</a> to assist you.', 'edd' ), 'http://docs.easydigitaldownloads.com/' ); ?></p>
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
					<p><?php _e( 'We do our best to provide the best support we can. If you encounter a problem or have a question, simply open a ticket using our <a href="https://easydigitaldownloads.com/support">support form</a>.', 'edd' );?></p>

					<h4><?php _e( 'Need Even Faster Support?', 'edd' );?></h4>
					<p><?php _e( 'Our <a href="https://easydigitaldownloads.com/support/pricing/">Priority Support</a> system is there for customers that need faster and/or more in-depth assistance.', 'edd' );?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Stay Up to Date', 'edd' );?></h3>

				<div class="feature-section">

					<h4><?php _e( 'Get Notified of Extension Releases','edd' );?></h4>
					<p><?php _e( 'New extensions that make Easy Digital Downloads even more powerful are released nearly every single week. Subscribe to the newsletter to stay up to date with our latest releases. <a href="http://eepurl.com/kaerz" target="_blank">Sign up now</a> to ensure you do not miss a release!', 'edd' );?></p>

					<h4><?php _e( 'Get Alerted About New Tutorials', 'edd' );?></h4>
					<p><?php _e( '<a href="http://eepurl.com/kaerz" target="_blank">Sign up now</a> to hear about the latest tutorial releases that explain how to take Easy Digital Downloads further.', 'edd' );?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Extensions for Everything', 'edd' );?></h3>

				<div class="feature-section">

					<h4><?php _e( 'Over 250 Extensions','edd' );?></h4>
					<p><?php _e( 'Add-on plugins are available that greatly extend the default functionality of Easy Digital Downloads. There are extensions for payment processors, such as Stripe and PayPal, extensions for newsletter integrations, and many, many more.', 'edd' );?></p>

					<h4><?php _e( 'Visit the Extension Store', 'edd' );?></h4>
					<p><?php _e( '<a href="https://easydigitaldownloads.com/downloads" target="_blank">The Extensions store</a> has a list of all available extensions, including convenient category filters so you can find exactly what you are looking for.', 'edd' );?></p>

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
			<div class="edd-badge"><img src="<?php echo EDD_PLUGIN_URL . 'assets/images/edd-logo.svg'; ?>" alt="<?php _e( 'Easy Digital Downloads', 'edd' ); ?>" / ></div>

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
