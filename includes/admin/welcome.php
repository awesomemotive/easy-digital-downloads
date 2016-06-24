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
		add_action( 'admin_init', array( $this, 'welcome'    ), 11 );
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
			__( 'Welcome to Easy Digital Downloads', 'easy-digital-downloads' ),
			__( 'Welcome to Easy Digital Downloads', 'easy-digital-downloads' ),
			$this->minimum_capability,
			'edd-about',
			array( $this, 'about_screen' )
		);

		// Changelog Page
		add_dashboard_page(
			__( 'Easy Digital Downloads Changelog', 'easy-digital-downloads' ),
			__( 'Easy Digital Downloads Changelog', 'easy-digital-downloads' ),
			$this->minimum_capability,
			'edd-changelog',
			array( $this, 'changelog_screen' )
		);

		// Getting Started Page
		add_dashboard_page(
			__( 'Getting started with Easy Digital Downloads', 'easy-digital-downloads' ),
			__( 'Getting started with Easy Digital Downloads', 'easy-digital-downloads' ),
			$this->minimum_capability,
			'edd-getting-started',
			array( $this, 'getting_started_screen' )
		);

		// Credits Page
		add_dashboard_page(
			__( 'The people that build Easy Digital Downloads', 'easy-digital-downloads' ),
			__( 'The people that build Easy Digital Downloads', 'easy-digital-downloads' ),
			$this->minimum_capability,
			'edd-credits',
			array( $this, 'credits_screen' )
		);

		// Now remove them from the menus so plugins that allow customizing the admin menu don't show them
		remove_submenu_page( 'index.php', 'edd-about' );
		remove_submenu_page( 'index.php', 'edd-changelog' );
		remove_submenu_page( 'index.php', 'edd-getting-started' );
		remove_submenu_page( 'index.php', 'edd-credits' );
	}

	/**
	 * Hide Individual Dashboard Pages
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function admin_head() {
		?>
		<style type="text/css" media="screen">
			/*<![CDATA[*/
			.edd-about-wrap .edd-badge { float: right; border-radius: 4px; margin: 0 0 15px 15px; max-width: 100px; }
			.edd-about-wrap #edd-header { margin-bottom: 15px; }
			.edd-about-wrap #edd-header h1 { margin-bottom: 15px !important; }
			.edd-about-wrap .about-text { margin: 0 0 15px; max-width: 670px; }
			.edd-about-wrap .feature-section { margin-top: 20px; }
			.edd-about-wrap .feature-section-content,
			.edd-about-wrap .feature-section-media { width: 50%; box-sizing: border-box; }
			.edd-about-wrap .feature-section-content { float: left; padding-right: 50px; }
			.edd-about-wrap .feature-section-content h4 { margin: 0 0 1em; }
			.edd-about-wrap .feature-section-media { float: right; text-align: right; margin-bottom: 20px; }
			.edd-about-wrap .feature-section-media img { border: 1px solid #ddd; }
			.edd-about-wrap .feature-section:not(.under-the-hood) .col { margin-top: 0; }
			/* responsive */
			@media all and ( max-width: 782px ) {
				.edd-about-wrap .feature-section-content,
				.edd-about-wrap .feature-section-media { float: none; padding-right: 0; width: 100%; text-align: left; }
				.edd-about-wrap .feature-section-media img { float: none; margin: 0 0 20px; }
			}
			/*]]>*/
		</style>
		<?php
	}

	/**
	 * Welcome message
	 *
	 * @access public
	 * @since 2.5
	 * @return void
	 */
	public function welcome_message() {
		list( $display_version ) = explode( '-', EDD_VERSION );
		?>
		<div id="edd-header">
			<img class="edd-badge" src="<?php echo EDD_PLUGIN_URL . 'assets/images/edd-logo.svg'; ?>" alt="<?php _e( 'Easy Digital Downloads', 'easy-digital-downloads' ); ?>" / >
			<h1><?php printf( __( 'Welcome to Easy Digital Downloads %s', 'easy-digital-downloads' ), $display_version ); ?></h1>
			<p class="about-text">
				<?php printf( __( 'Thank you for updating to the latest version! Easy Digital Downloads %s is ready to make your online store faster, safer, and better!', 'easy-digital-downloads' ), $display_version ); ?>
			</p>
		</div>
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
		<h1 class="nav-tab-wrapper">
			<a class="nav-tab <?php echo $selected == 'edd-about' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-about' ), 'index.php' ) ) ); ?>">
				<?php _e( "What's New", 'easy-digital-downloads' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'edd-getting-started' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-getting-started' ), 'index.php' ) ) ); ?>">
				<?php _e( 'Getting Started', 'easy-digital-downloads' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'edd-credits' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-credits' ), 'index.php' ) ) ); ?>">
				<?php _e( 'Credits', 'easy-digital-downloads' ); ?>
			</a>
		</h1>
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
		?>
		<div class="wrap about-wrap edd-about-wrap">
			<?php
				// load welcome message and content tabs
				$this->welcome_message();
				$this->tabs();
			?>
			<div class="changelog">
				<h3><?php _e( 'Additional Customer Emails', 'easy-digital-downloads' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media">
						<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/26-customer.png'; ?>"/>
					</div>
					<div class="feature-section-content">
						<p><?php _e( 'To help keep track of customers that have multiple email addresses, Easy Digital Downloads now supports storing additional emails on customers. During checkout, customers can use any email address assigned to their account to complete their purchase.', 'easy-digital-downloads' );?></p>

						<p><?php _e( 'Email addresses can be easily added by site administrators at anytime and will also be automatically registered when a customer makes a purchase with an additional email address.', 'easy-digital-downloads' );?></p>

						<h4><?php _e( 'Improved Help Text', 'easy-digital-downloads' );?></h4>
						<p><?php _e( 'While we strive to make Easy Digital Downloads live up to its name, there are always times when certain things are not quite clear. To help alleviate any uncertainty, we have introduced improved descriptions and help texts throughout the plugin. Along with the improved descriptions, we have also added tooltips in many places that offer verbose definitions of options.', 'easy-digital-downloads' );?></p>

						<h4><?php _e( 'Better Mobile Checkout', 'easy-digital-downloads' );?></h4>
						<p><?php _e( 'When purchasing with a debit or credit card from a mobile phone, the card number input field will now properly set the phone’s keyboard to a numerical keyboard.', 'easy-digital-downloads' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Native Import Options', 'easy-digital-downloads' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media">
						<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/26-import.png'; ?>"/>
					</div>
					<div class="feature-section-content">
						<p><?php _e( 'We believe you should own your data. We also believe that it should be easy to get data out of <em>and</em> into Easy Digital Downloads. 2.6 introduces native import options for payments and download products.', 'easy-digital-downloads' );?></p>

						<h4><?php _e( 'Product Import', 'easy-digital-downloads' );?></h4>
						<p><?php _e( 'With the new import options, Easy Digital Downloads now makes it easy to import products from a CSV file into your store. Whether you wish to import five products or 50,000, Easy Digital Downloads can now effortlessly handle the import for you.', 'easy-digital-downloads' );?></p>

						<h4><?php _e( 'Payment Import', 'easy-digital-downloads' );?></h4>
						<p><?php _e( 'Sometimes it is necessary to move purchase records from one location to another. Perhaps you are transitioning from another eCommerce system, or from a separate Easy Digital Downloads store; whatever the reason, Easy Digital Downloads now allows you to easily import purchase records from a CSV file.', 'easy-digital-downloads' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Better Refunds', 'easy-digital-downloads' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media">
						<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/26-refund.png'; ?>" class="edd-welcome-screenshots alignleft"/>
					</div>
					<div class="feature-section-content">
						<h4><?php _e( 'Refund Processing for PayPal Standard', 'easy-digital-downloads' );?></h4>
						<p><?php _e( 'While not usually something store administrators take great pleasure in handling, refunds are a very real part of running an eCommerce store. As much as we would love to, we can’t make the actual refund more enjoyable, but we can make refunds easier to process.', 'easy-digital-downloads' );?></p>
						<p><?php _e( 'In Easy Digital Downloads 2.6, we have added support for processing refunds directly from the View Order Details screen for purchases made through PayPal Standard.', 'easy-digital-downloads' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Additional Updates', 'easy-digital-downloads' );?></h3>
				<div class="feature-section three-col">
					<div class="col">
						<h4><?php _e( 'REST API Version 2', 'easy-digital-downloads' );?></h4>
						<p><?php _e( 'Version 2 of the REST API offers several improved endpoint options and better data standardization.', 'easy-digital-downloads' );?></p>
					</div>
					<div class="col">
						<h4><?php _e( 'Prices on oEmbed', 'easy-digital-downloads' );?></h4>
						<p><?php _e( 'When embedding a download product on another site, using WordPress core’s oEmbed feature, the product prices are now shown.', 'easy-digital-downloads' );?></p>
					</div>
					<div class="col">
						<h4><?php _e( 'Customer Meta', 'easy-digital-downloads' );?></h4>
						<p><?php _e( 'The customer database now includes a complete metadata API for storing additional information on customer records.' ,'easy-digital-downloads' );?></p>
					</div>
					<div class="clear">
						<div class="col">
							<h4><?php _e( 'Improved Accessibility', 'easy-digital-downloads' );?></h4>
							<p><?php _e( 'Easy Digital Downloads is now more accessible to more users thanks to a member of the WordPress accessibility team who helped resolve accessibility issues throughout the administrative interfaces.', 'easy-digital-downloads' );?></p>
						</div>
						<div class="col">
							<h4><?php _e( 'Resolved Schema Problems', 'easy-digital-downloads' );?></h4>
							<p><?php _e( 'Invalid and missing schema microdata has been resolved.', 'easy-digital-downloads' );?></p>
						</div>
						<div class="col">
							<h4><?php _e( 'More Actions and Filters', 'easy-digital-downloads' );?></h4>
							<p><?php _e( 'Numerous new actions and filters have been added to help make Easy Digital Downloads more extensible for developers.' ,'easy-digital-downloads' );?></p>
						</div>
					</div>
				</div>
			</div>

			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'post_type' => 'download', 'page' => 'edd-settings' ), 'edit.php' ) ) ); ?>"><?php _e( 'Go to Easy Digital Downloads Settings', 'easy-digital-downloads' ); ?></a> &middot;
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-changelog' ), 'index.php' ) ) ); ?>"><?php _e( 'View the Full Changelog', 'easy-digital-downloads' ); ?></a>
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
		?>
		<div class="wrap about-wrap edd-about-wrap">
			<?php
				// load welcome message and content tabs
				$this->welcome_message();
				$this->tabs();
			?>
			<div class="changelog">
				<h3><?php _e( 'Full Changelog', 'easy-digital-downloads' );?></h3>

				<div class="feature-section">
					<?php echo $this->parse_readme(); ?>
				</div>
			</div>

			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'post_type' => 'download', 'page' => 'edd-settings' ), 'edit.php' ) ) ); ?>"><?php _e( 'Go to Easy Digital Downloads Settings', 'easy-digital-downloads' ); ?></a>
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
		?>
		<div class="wrap about-wrap edd-about-wrap">
			<?php
				// load welcome message and content tabs
				$this->welcome_message();
				$this->tabs();
			?>
			<p class="about-description"><?php _e( 'Use the tips below to get started using Easy Digital Downloads. You will be up and running in no time!', 'easy-digital-downloads' ); ?></p>

			<div class="changelog">
				<h3><?php _e( 'Creating Your First Download Product', 'easy-digital-downloads' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media">
						<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/edit-download.png'; ?>" class="edd-welcome-screenshots"/>
					</div>
					<div class="feature-section-content">
						<h4><a href="<?php echo admin_url( 'post-new.php?post_type=download' ) ?>"><?php printf( __( '%s &rarr; Add New', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></a></h4>
						<p><?php printf( __( 'The %s menu is your access point for all aspects of your Easy Digital Downloads product creation and setup. To create your first product, simply click Add New and then fill out the product details.', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></p>


						<h4><?php _e( 'Download Files', 'easy-digital-downloads' );?></h4>
						<p><?php _e( 'Uploading the downloadable files is simple. Click <em>Upload File</em> in the Download Files section and choose your download file. To add more than one file, simply click the <em>Add New</em> button.', 'easy-digital-downloads' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Display a Product Grid', 'easy-digital-downloads' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media">
						<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/grid.png'; ?>"/>
					</div>
					<div class="feature-section-content">
						<h4><?php _e( 'Flexible Product Grids','easy-digital-downloads' );?></h4>
						<p><?php _e( 'The [downloads] shortcode will display a product grid that works with any theme, no matter the size. It is even responsive!', 'easy-digital-downloads' );?></p>

						<h4><?php _e( 'Change the Number of Columns', 'easy-digital-downloads' );?></h4>
						<p><?php _e( 'You can easily change the number of columns by adding the columns="x" parameter:', 'easy-digital-downloads' );?></p>
						<p><pre>[downloads columns="4"]</pre></p>

						<h4><?php _e( 'Additional Display Options', 'easy-digital-downloads' ); ?></h4>
						<p><?php printf( __( 'The product grids can be customized in any way you wish and there is <a href="%s">extensive documentation</a> to assist you.', 'easy-digital-downloads' ), 'http://docs.easydigitaldownloads.com/' ); ?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Purchase Buttons Anywhere', 'easy-digital-downloads' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media">
						<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/purchase-link.png'; ?>"/>
					</div>
					<div class="feature-section-content">
						<h4><?php _e( 'The <em>[purchase_link]</em> Shortcode','easy-digital-downloads' );?></h4>
						<p><?php _e( 'With easily accessible shortcodes to display purchase buttons, you can add a Buy Now or Add to Cart button for any product anywhere on your site in seconds.', 'easy-digital-downloads' );?></p>

						<h4><?php _e( 'Buy Now Buttons', 'easy-digital-downloads' );?></h4>
						<p><?php _e( 'Purchase buttons can behave as either Add to Cart or Buy Now buttons. With Buy Now buttons customers are taken straight to PayPal, giving them the most frictionless purchasing experience possible.', 'easy-digital-downloads' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Need Help?', 'easy-digital-downloads' );?></h3>
				<div class="feature-section two-col">
					<div class="col">
						<h4><?php _e( 'Phenomenal Support','easy-digital-downloads' );?></h4>
						<p><?php _e( 'We do our best to provide the best support we can. If you encounter a problem or have a question, simply open a ticket using our <a href="https://easydigitaldownloads.com/support/?utm_source=plugin-welcome-page&utm_medium=support-link&utm_term=support&utm_campaign=EDDWelcomeSupport">support form</a>.', 'easy-digital-downloads' );?></p>
					</div>
					<div class="col">
						<h4><?php _e( 'Need Even Faster Support?', 'easy-digital-downloads' );?></h4>
						<p><?php _e( 'Our <a href="https://easydigitaldownloads.com/support/pricing/?utm_source=plugin-welcome-page&utm_medium=support-link&utm_term=priority-support&utm_campaign=EDDWelcomeSupport">Priority Support</a> system is there for customers that need faster and/or more in-depth assistance.', 'easy-digital-downloads' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Stay Up to Date', 'easy-digital-downloads' );?></h3>
				<div class="feature-section two-col">
					<div class="col">
						<h4><?php _e( 'Get Notified of Extension Releases','easy-digital-downloads' );?></h4>
						<p><?php _e( 'New extensions that make Easy Digital Downloads even more powerful are released nearly every single week. Subscribe to the newsletter to stay up to date with our latest releases. <a href="https://easydigitaldownloads.com/subscribe" target="_blank">Sign up now</a> to ensure you do not miss a release!', 'easy-digital-downloads' );?></p>
					</div>
					<div class="col">
						<h4><?php _e( 'Get Alerted About New Tutorials', 'easy-digital-downloads' );?></h4>
						<p><?php _e( '<a href="https://easydigitaldownloads.com/subscribe" target="_blank">Sign up now</a> to hear about the latest tutorial releases that explain how to take Easy Digital Downloads further.', 'easy-digital-downloads' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Extensions for Everything', 'easy-digital-downloads' );?></h3>
				<div class="feature-section two-col">
					<div class="col">
						<h4><?php _e( 'Over 250 Extensions','easy-digital-downloads' );?></h4>
						<p><?php _e( 'Add-on plugins are available that greatly extend the default functionality of Easy Digital Downloads. There are extensions for payment processors, such as Stripe and PayPal, extensions for newsletter integrations, and many, many more.', 'easy-digital-downloads' );?></p>
					</div>
					<div class="col">
						<h4><?php _e( 'Visit the Extension Store', 'easy-digital-downloads' );?></h4>
						<p><?php _e( '<a href="https://easydigitaldownloads.com/downloads/?utm_source=plugin-welcome-page&utm_medium=extensions-link&utm_term=extensions&utm_campaign=EDDWelcomeExtensions" target="_blank">The Extensions store</a> has a list of all available extensions, including convenient category filters so you can find exactly what you are looking for.', 'easy-digital-downloads' );?></p>
					</div>
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
		?>
		<div class="wrap about-wrap edd-about-wrap">
			<?php
				// load welcome message and content tabs
				$this->welcome_message();
				$this->tabs();
			?>
			<p class="about-description"><?php _e( 'Easy Digital Downloads is created by a worldwide team of developers who aim to provide the #1 eCommerce platform for selling digital goods through WordPress.', 'easy-digital-downloads' ); ?></p>

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
			$readme = '<p>' . __( 'No valid changelog was found.', 'easy-digital-downloads' ) . '</p>';
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
			$contributor_list .= sprintf( '<a href="%s">',
				esc_url( 'https://github.com/' . $contributor->login ),
				esc_html( sprintf( __( 'View %s', 'easy-digital-downloads' ), $contributor->login ) )
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

		$response = wp_remote_get( 'https://api.github.com/repos/easydigitaldownloads/Easy-Digital-Downloads/contributors?per_page=999', array( 'sslverify' => false ) );

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
