<?php
/**
 * Adds the About Us page to the EDD Admin
 *
 * @package     EDD
 * @subpackage  Admin/Promos
 *
 * @since 3.2.4
 */

namespace EDD\Admin\Promos;

use EDD\EventManagement\SubscriberInterface;
use EDD\Admin\Extensions\Extension_Manager;

/**
 * The About Us page class.
 *
 * @since 3.2.4
 */
class About implements SubscriberInterface {

	/**
	 * The default tab to show.
	 *
	 * @var string
	 */
	private $default_tab = 'general';

	/**
	 * The tabs to show.
	 *
	 * @var array
	 */
	private $tabs = array();

	/**
	 * The Extension Manager
	 *
	 * @since 3.2.4
	 *
	 * @var \EDD\Admin\Extensions\Extension_Manager
	 */
	private $manager;

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 */
	public static function get_subscribed_events() {
		return array(
			'admin_body_class'        => 'add_body_class',
			'edd_settings_page_title' => 'page_title',
			'admin_menu'              => array( 'register_page', 99 ),
		);
	}

	/**
	 * Adds the "edd-about" body class to the EDD About Us page.
	 *
	 * @since 3.2.4
	 *
	 * @param string $classes The current body classes.
	 * @return string
	 */
	public function add_body_class( $classes ) {
		if ( $this->is_about_us_page() ) {
			$classes .= ' edd-about';
		}

		return $classes;
	}

	/**
	 * Changes the page title for the EDD About Us page and the Getting Started Page
	 *
	 * @since 3.2.4
	 *
	 * @param string $title The current page title.
	 * @return string
	 */
	public function page_title( $title ) {
		if ( $this->is_about_us_page() ) {
			$tabs = $this->setup_tabs();

			$title = $tabs[ $this->get_current_tab() ];
		}

		return $title;
	}

	/**
	 * Register the About Us page.
	 *
	 * @since 3.2.4
	 */
	public function register_page() {
		add_submenu_page(
			'edit.php?post_type=download',
			__( 'About Easy Digital Downloads', 'easy-digital-downloads' ),
			__( 'About Us', 'easy-digital-downloads' ),
			'manage_shop_settings',
			'edd-about',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Render the About Us page.
	 *
	 * @since 3.2.4
	 */
	public function render_page() {
		$this->manager = new Extension_Manager();
		$this->manager->enqueue();

		$this->setup_tabs();
		?>
		<div id="edd-admin-about" class="wrap edd-admin-wrap">
		<?php
			$this->render_tabs();

			$this->render_tab( $this->get_current_tab() );
		?>
		</div>
		<?php
	}

	/**
	 * Setup the tabs.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	private function setup_tabs() {
		if ( ! empty( $this->tabs ) ) {
			return $this->tabs;
		}

		$this->tabs = array(
			'general'         => __( 'About Us', 'easy-digital-downloads' ),
			'getting_started' => __( 'Getting Started', 'easy-digital-downloads' ),
		);

		return $this->tabs;
	}

	/**
	 * Check if we are on the About Us page.
	 *
	 * @since 3.2.4
	 *
	 * @return bool
	 */
	private function is_about_us_page() {
		$screen = get_current_screen();
		if ( 'download_page_edd-about' === $screen->id ) {
			return true;
		}

		return false;
	}

	/**
	 * Render the tabs.
	 *
	 * @since 3.2.4
	 */
	private function render_tabs() {
		?>
		<nav class="edd-about-nav" aria-label="<?php esc_attr_e( 'Secondary menu', 'easy-digital-downloads' ); ?>">
			<?php

			foreach ( $this->tabs as $tab_slug => $tab_name ) {
				$tab_url = edd_get_admin_url(
					array(
						'page' => 'edd-about',
						'tab'  => sanitize_key( $tab_slug ),
					)
				);

				$class = 'tab';
				if ( $this->get_current_tab() === $tab_slug ) {
					$class .= ' active';
				}

				// Link.
				echo '<a href="' . esc_url( $tab_url ) . '" class="' . esc_attr( $class ) . '">';
				echo esc_html( $tab_name );
				echo '</a>';
			}
			?>
		</nav>
		<?php
	}

	/**
	 * Get the current tab.
	 *
	 * @since 3.2.4
	 *
	 * @return string
	 */
	private function get_current_tab() {
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
		if ( ! array_key_exists( $tab, $this->tabs ) ) {
			$tab = $this->default_tab;
		}

		return $tab;
	}

	/**
	 * Render the specific tab section content.
	 *
	 * @since 3.2.4
	 *
	 * @param string $tab The tab to render.
	 */
	private function render_tab( $tab ) {
		switch ( $tab ) {
			case 'getting_started':
				$this->render_tab_getting_started();
				break;
			case 'general':
			default:
				$this->render_tab_general();
				break;
		}
	}

	/**
	 * Renders the standard About Us tab content.
	 *
	 * @since 3.2.4
	 */
	private function render_tab_general() {
		?>
		<div class="edd-admin-about-section edd-admin-columns welcome-message">

			<div class="column column--50 m-r-15">
				<h2>
					<?php esc_html_e( 'Welcome to Easy Digital Downloads, the #1 WordPress eCommerce plugin for easily collecting payments. At Easy Digital Downloads, we build software that helps you sell your digital products and services in minutes.', 'easy-digital-downloads' ); ?>
				</h2>
				<p>
					<?php esc_html_e( 'Over the years we\'ve watched as many plugins and platforms have been created that say they focus on "digital products", but take huge fees out of each sale, charge for each product created, and even charge "relisting fees". We take a different approach, to create the most powerful digital eCommerce solution, with no hidden fees or monthly costs.', 'easy-digital-downloads' ); ?>
				</p>
				<p>
					<?php esc_html_e( 'Our goal is to remove the pain from running your own eCommerce store and make it effortless, so you can get back to doing what you do best.', 'easy-digital-downloads' ); ?>
				</p>
				<p>
					<?php esc_html_e( 'Since 2012, we\'ve been building the best solution for selling digital products, services, and memberships with WordPress.', 'easy-digital-downloads' ); ?>
				</p>
				<p>
					<?php esc_html_e( 'Thanks for being the best part of Easy Digital Downloads!', 'easy-digital-downloads' ); ?>
				</p>
			</div>

			<div class="column column--50 m-l-15 align--middle">
				<figure>
					<img class="shadow" src="<?php echo EDD_PLUGIN_URL; ?>assets/images/promo/am-team.jpg" alt="<?php esc_attr_e( 'The Awesome Motive Team photo', 'easy-digital-downloads' ); ?>">
					<figcaption>
						<?php esc_html_e( 'The Awesome Motive Team', 'easy-digital-downloads' ); ?><br>
					</figcaption>
				</figure>
			</div>

		</div>
		<?php
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$all_plugins         = get_plugins();
		$am_plugins          = $this->get_am_plugins();
		$can_install_plugins = current_user_can( 'install_plugins' );
		?>
		<div id="edd-admin-addons">
			<div class="addons-container">
				<?php
				foreach ( $am_plugins as $plugin => $details ) :
					$plugin_data = $this->get_plugin_data( $plugin, $details, $all_plugins );
					?>
					<div class="addon-container">
						<div class="addon-item">
							<div class="details">
								<span class="leftcol">
									<img src="<?php echo esc_url( $plugin_data['details']['icon'] ); ?>" alt="<?php echo esc_attr( $plugin_data['details']['name'] ); ?>">
								</span>
								<span class="rightcol">
									<h5 class="addon-name">
										<?php echo esc_html( $plugin_data['details']['name'] ); ?>
									</h5>
									<p class="addon-desc">
										<?php echo wp_kses_post( $plugin_data['details']['desc'] ); ?>
									</p>
								</span>
							</div>
							<div class="actions edd-extension-manager__actions">
								<div class="status">
									<?php
									printf(
										/* translators: %s - status label. */
										esc_html__( 'Status: %s', 'easy-digital-downloads' ),
										'<span class="status-label ' . esc_attr( $plugin_data['plugin_status'] ) . '">' . wp_kses_post( $plugin_data['status_text'] ) . '</span>'
									);
									?>
								</div>
								<div class="action-button">
									<div class="edd-extension-manager__step">
										<?php
										if ( 'active' === $plugin_data['plugin_status'] ) {
											?>
											<button class="button button-secondary disabled"><?php echo esc_html( $plugin_data['button_text'] ); ?> ♥ </button>
											<?php
										} elseif ( 'goto' === $plugin_data['action'] || ! $can_install_plugins ) {
											?>
											<a class="button button-primary" href="<?php echo esc_url( $plugin_data['plugin'] ); ?>" target="_blank" rel="noopener noreferrer">
												<?php echo esc_html( $plugin_data['button_text'] ); ?>
											</a>
											<?php
										} else {
											$this->manager->button( $plugin_data['button_parameters'] );
										}
										?>
									</div>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Renders the Getting Started tab content.
	 *
	 * @since 3.2.4
	 */
	private function render_tab_getting_started() {
		?>
		<div class="edd-admin-about-section edd-admin-columns welcome-message">

			<div class="column column--50 m-r-15">
				<h3>
					<?php esc_html_e( 'How to Get Started', 'easy-digital-downloads' ); ?>
				</h3>
				<h2>
					<?php esc_html_e( 'Welcome to Easy Digital Downloads', 'easy-digital-downloads' ); ?>
				</h2>
				<p>
					<?php esc_html_e( 'The best WordPress eCommerce plugin for selling digital downloads. Start selling eBooks, software, music, digital art, and more within minutes. Accept payments, manage subscriptions, advanced access control, and more.', 'easy-digital-downloads' ); ?>
				</p>

				<div class="row">
					<a class="button edd-pro-upgrade" href="<?php echo esc_url( edd_get_admin_url( array( 'page' => 'edd-onboarding-wizard' ) ) ); ?>"><?php esc_html_e( 'Launch Setup Wizard', 'easy-digital-downloads' ); ?></a>
					&nbsp;&nbsp;<a href="https://easydigitaldownloads.com/docs/quickstart-guide/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Read the Setup Guide', 'easy-digital-downloads' ); ?></a>
				</div>
			</div>

			<div class="column column--50 m-l-15 align--middle">
				<figure>
					<img src="<?php echo EDD_PLUGIN_URL; ?>assets/images/promo/about/getting-started-welcome.svg" alt="<?php esc_attr_e( 'Welcome to Easy Digital Downloads', 'easy-digital-downloads' ); ?>">
				</figure>
			</div>

		</div>
		<?php
		$pass_manager = new \EDD\Admin\Pass_Manager();
		if ( ! edd_is_pro() && ! $pass_manager->has_pass() ) {
			?>
			<div class="edd-admin-about-section edd-admin-about-section-hero">

				<div class="edd-admin-about-section-hero-main">
					<h2>
						<?php esc_html_e( 'Get Easy Digital Downloads (Pro) and Unlock your Growth Potential', 'easy-digital-downloads' ); ?>
					</h2>

					<p class="bigger">
						<?php
						echo wp_kses(
							__( 'Thanks for being a loyal Easy Digital Downloads user. <strong>Upgrade to Easy Digital Downloads (Pro)</strong> to unlock all the awesome features and experience why Easy Digital Downloads is regarded as the best eCommerce plugin for digital products and services.', 'easy-digital-downloads' ),
							array(
								'br'     => array(),
								'strong' => array(),
							)
						);
						?>
					</p>

					<p>
						<?php
						printf(
							wp_kses( /* translators: %s - stars. */
								__( 'We know that you will truly love Easy Digital Downloads. It has over <strong>450+ five star ratings</strong> (%s) and over 50,000+ professionals and creators use it to run their businesses and projects.', 'easy-digital-downloads' ),
								array(
									'strong' => array(),
								)
							),
							'<span class="dashicons dashicons-star-filled" aria-hidden="true"></span>' .
							'<span class="dashicons dashicons-star-filled" aria-hidden="true"></span>' .
							'<span class="dashicons dashicons-star-filled" aria-hidden="true"></span>' .
							'<span class="dashicons dashicons-star-filled" aria-hidden="true"></span>' .
							'<span class="dashicons dashicons-star-filled" aria-hidden="true"></span>'
						);
						?>
					</p>
				</div>

				<div class="edd-admin-about-section-hero-extra">
					<div class="edd-admin-columns">
						<div class="column--50">
							<ul class="list-features list-plain">
								<li>
									<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/icons/edd-blue-checkmark.svg'; ?>" aria-hidden="true" />
									<?php esc_html_e( 'Sell subscriptions for products and services', 'easy-digital-downloads' ); ?>
								</li>
								<li>
									<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/icons/edd-blue-checkmark.svg'; ?>" aria-hidden="true" />
									<?php esc_html_e( 'Remove additional transaction fees', 'easy-digital-downloads' ); ?>
								</li>
								<li>
									<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/icons/edd-blue-checkmark.svg'; ?>" aria-hidden="true" />
									<?php esc_html_e( 'Add customers to your email subscriber list', 'easy-digital-downloads' ); ?>
								</li>
								<li>
									<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/icons/edd-blue-checkmark.svg'; ?>" aria-hidden="true" />
									<?php esc_html_e( 'Give away lead magnets to grow your email list', 'easy-digital-downloads' ); ?>
								</li>
								<li>
									<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/icons/edd-blue-checkmark.svg'; ?>" aria-hidden="true" />
									<?php esc_html_e( 'Build advanced product bundles', 'easy-digital-downloads' ); ?>
								</li>
							</ul>
						</div>
						<div class="column--50 edd-admin-column-last">
							<ul class="list-features list-plain">
								<li>
									<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/icons/edd-blue-checkmark.svg'; ?>" aria-hidden="true" />
									<?php esc_html_e( 'Connect with over 6,000+ apps and services', 'easy-digital-downloads' ); ?>
								</li>
								<li>
									<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/icons/edd-blue-checkmark.svg'; ?>" aria-hidden="true" />
									<?php esc_html_e( 'Support selling in multiple currencies', 'easy-digital-downloads' ); ?>
								</li>
								<li>
									<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/icons/edd-blue-checkmark.svg'; ?>" aria-hidden="true" />
									<?php esc_html_e( 'Collect product reviews from your customers', 'easy-digital-downloads' ); ?>
								</li>
								<li>
									<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/icons/edd-blue-checkmark.svg'; ?>" aria-hidden="true" />
									<?php esc_html_e( 'Add product recommendations, cross-sells, and upsells', 'easy-digital-downloads' ); ?>
								</li>
								<li>
									<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/icons/edd-blue-checkmark.svg'; ?>" aria-hidden="true" />
									<?php esc_html_e( 'Create advanced purchase receipt conditions', 'easy-digital-downloads' ); ?>
								</li>
							</ul>
						</div>
					</div>

					<hr />

					<h3 class="call-to-action">
						<?php
						$upgrade_link = edd_link_helper(
							'https://easydigitaldownloads.com/lite-upgrade',
							array(
								'utm_medium'  => 'about-us',
								'utm-content' => 'getting-started',
							)
						);
						echo sprintf(
							// translators: %1$s - opening link tag, %2$s - closing link tag.
							__( '%1$sUpgrade to Pro Today%2$s', 'easy-digital-downloads' ),
							'<a class="button edd-pro-upgrade" href="' . $upgrade_link . '" target="_blank" rel="noopener noreferrer">',
							'</a>'
						);
						?>
					</h3>

					<p>
						<?php
						echo wp_kses(
							__( 'Bonus: Free Easy Digital Downloads users get <span class="edd-pro-upgrade">50% off regular price</span>, automatically applied at checkout.', 'easy-digital-downloads' ),
							array(
								'span' => array(
									'class' => array(),
								),
							)
						);
						?>
					</p>
				</div>

			</div>
		<?php } ?>

		<?php
		$links = array(
			array(
				'title'       => esc_html__( 'An Introduction to Easy Digital Downloads', 'easy-digital-downloads' ),
				'url'         => 'https://easydigitaldownloads.com/docs/easy-digital-downloads-introduction/',
				'description' => esc_html__( 'If you\'re already generally familiar with eCommerce in WordPress then this document should help you get up to speed with Easy Digital Downloads quite quickly. Unless otherwise noted, everything in this document deals with core features.', 'easy-digital-downloads' ),
				'image'       => 'introduction-to-edd.svg',
			),
			array(
				'title'       => esc_html__( 'How to Install and Activate EDD Extensions', 'easy-digital-downloads' ),
				'url'         => 'https://easydigitaldownloads.com/docs/how-do-i-install-an-extension/',
				'description' => esc_html__( 'Would you like to access Easy Digital Downloads extensions to extend the functionality of your store? Each EDD pass level comes with its own set of extensions to help you get the most out of your store.', 'easy-digital-downloads' ),
				'image'       => 'how-to-install-activate.svg',
			),
			array(
				'title'       => esc_html__( 'Using the Included Easy Digital Downloads Blocks', 'easy-digital-downloads' ),
				'url'         => 'https://easydigitaldownloads.com/docs/easy-digital-downloads-blocks/',
				'description' => esc_html__( 'Creating your store has never been easier than when using the included Easy Digital Downloads Blocks, fully integrated with the WordPress Block Editor. Learn about what blocks come with Easy Digital Downloads and how you can use them on your store.', 'easy-digital-downloads' ),
				'image'       => 'using-edd-blocks.svg',
			),
			array(
				'title'       => esc_html__( 'Configuring Cache for Easy Digital Downloads', 'easy-digital-downloads' ),
				'url'         => 'https://easydigitaldownloads.com/docs/configure-cache/',
				'description' => esc_html__( 'Caching plugins and services are designed to help ensure your site responds as quickly as possible. We understand that a fast store converts better than a slow store. We\'ve worked with multiple caching solutions to write up guides on how to configure their plugin or services to work best with Easy Digital Downloads.', 'easy-digital-downloads' ),
				'image'       => 'configuring-caching.svg',
			),
		);

		foreach ( $links as $link ) {
			?>
			<div class="edd-admin-about-section edd-admin-about-section-squashed edd-admin-about-section-post edd-admin-columns">
				<div class="column--20 align--middle image">
					<img src="<?php echo esc_url( EDD_PLUGIN_URL . 'assets/images/promo/about/' . $link['image'] ); ?>" alt="">
				</div>
				<div class="column--80 content">
					<h2>
						<?php echo $link['title']; ?>
					</h2>

					<p>
						<?php echo $link['description']; ?>
					</p>

					<a href="<?php echo esc_url( $link['url'] ); ?>" target="_blank" rel="noopener noreferrer" class="button button-secondary">
						<?php esc_html_e( 'Read Documentation', 'easy-digital-downloads' ); ?>
					</a>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * List of AM plugins that we propose to install.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	private function get_am_plugins() {

		$images_url = EDD_PLUGIN_URL . 'assets/images/promo/brands/';

		return array(

			'optinmonster/optin-monster-wp-api.php'        => array(
				'icon'  => $images_url . 'plugin-om.png',
				'name'  => __( 'OptinMonster', 'easy-digital-downloads' ),
				'desc'  => esc_html__( 'Instantly get more subscribers, leads, and sales with the #1 conversion optimization toolkit. Create high converting popups, announcement bars, spin a wheel, and more with smart targeting and personalization.', 'easy-digital-downloads' ),
				'wporg' => 'https://wordpress.org/plugins/optinmonster/',
				'url'   => 'https://downloads.wordpress.org/plugin/optinmonster.zip',
			),

			'google-analytics-for-wordpress/googleanalytics.php' => array(
				'icon'  => $images_url . 'plugin-mi.png',
				'name'  => __( 'MonsterInsights', 'easy-digital-downloads' ),
				'desc'  => esc_html__( 'The leading WordPress analytics plugin that shows you how people find and use your website, so you can make data driven decisions to grow your business. Properly set up Google Analytics without writing code.', 'easy-digital-downloads' ),
				'wporg' => 'https://wordpress.org/plugins/google-analytics-for-wordpress/',
				'url'   => 'https://downloads.wordpress.org/plugin/google-analytics-for-wordpress.zip',
				'pro'   => array(
					'plug' => 'google-analytics-premium/googleanalytics-premium.php',
					'name' => __( 'MonsterInsights Pro', 'easy-digital-downloads' ),
					'url'  => 'https://www.monsterinsights.com/?utm_source=eddplugin&utm_medium=link&utm_campaign=About%20EDD',
					'act'  => 'go-to-url',
				),
			),

			'wp-mail-smtp/wp_mail_smtp.php'                => array(
				'icon'  => $images_url . 'plugin-smtp.png',
				'name'  => __( 'WP Mail SMTP', 'easy-digital-downloads' ),
				'desc'  => esc_html__( "Improve your WordPress email deliverability and make sure that your website emails reach user's inbox with the #1 SMTP plugin for WordPress. Over 3 million websites use it to fix WordPress email issues.", 'easy-digital-downloads' ),
				'wporg' => 'https://wordpress.org/plugins/wp-mail-smtp/',
				'url'   => 'https://downloads.wordpress.org/plugin/wp-mail-smtp.zip',
				'pro'   => array(
					'plug' => 'wp-mail-smtp-pro/wp_mail_smtp.php',
					'name' => __( 'WP Mail SMTP Pro', 'easy-digital-downloads' ),
					'url'  => 'https://wpmailsmtp.com/?utm_source=eddplugin&utm_medium=link&utm_campaign=About%20EDD',
					'act'  => 'go-to-url',
				),
			),

			'all-in-one-seo-pack/all_in_one_seo_pack.php'  => array(
				'icon'  => $images_url . 'plugin-aioseo.png',
				'name'  => __( 'AIOSEO', 'easy-digital-downloads' ),
				'desc'  => esc_html__( "The original WordPress SEO plugin and toolkit that improves your website's search rankings. Comes with all the SEO features like Local SEO, WooCommerce SEO, sitemaps, SEO optimizer, schema, and more.", 'easy-digital-downloads' ),
				'wporg' => 'https://wordpress.org/plugins/all-in-one-seo-pack/',
				'url'   => 'https://downloads.wordpress.org/plugin/all-in-one-seo-pack.zip',
				'pro'   => array(
					'plug' => 'all-in-one-seo-pack-pro/all_in_one_seo_pack.php',
					'name' => __( 'AIOSEO Pro', 'easy-digital-downloads' ),
					'url'  => 'https://aioseo.com/?utm_source=eddplugin&utm_medium=link&utm_campaign=About%20EDD',
					'act'  => 'go-to-url',
				),
			),

			'coming-soon/coming-soon.php'                  => array(
				'icon'  => $images_url . 'plugin-seedprod.png',
				'name'  => __( 'SeedProd', 'easy-digital-downloads' ),
				'desc'  => esc_html__( 'The fastest drag & drop landing page builder for WordPress. Create custom landing pages without writing code, connect them with your CRM, collect subscribers, and grow your audience. Trusted by 1 million sites.', 'easy-digital-downloads' ),
				'wporg' => 'https://wordpress.org/plugins/coming-soon/',
				'url'   => 'https://downloads.wordpress.org/plugin/coming-soon.zip',
				'pro'   => array(
					'plug' => 'seedprod-coming-soon-pro-5/seedprod-coming-soon-pro-5.php',
					'name' => __( 'SeedProd Pro', 'easy-digital-downloads' ),
					'url'  => 'https://www.seedprod.com/?utm_source=eddplugin&utm_medium=link&utm_campaign=About%20EDD',
					'act'  => 'go-to-url',
				),
			),

			'rafflepress/rafflepress.php'                  => array(
				'icon'  => $images_url . 'plugin-rp.png',
				'name'  => __( 'RafflePress', 'easy-digital-downloads' ),
				'desc'  => esc_html__( 'Turn your website visitors into brand ambassadors! Easily grow your email list, website traffic, and social media followers with the most powerful giveaways & contests plugin for WordPress.', 'easy-digital-downloads' ),
				'wporg' => 'https://wordpress.org/plugins/rafflepress/',
				'url'   => 'https://downloads.wordpress.org/plugin/rafflepress.zip',
				'pro'   => array(
					'plug' => 'rafflepress-pro/rafflepress-pro.php',
					'icon' => $images_url . 'plugin-rp.png',
					'name' => __( 'RafflePress Pro', 'easy-digital-downloads' ),
					'desc' => esc_html__( 'Turn your website visitors into brand ambassadors! Easily grow your email list, website traffic, and social media followers with the most powerful giveaways & contests plugin for WordPress.', 'easy-digital-downloads' ),
					'url'  => 'https://rafflepress.com/?utm_source=eddplugin&utm_medium=link&utm_campaign=About%20EDD',
					'act'  => 'go-to-url',
				),
			),

			'pushengage/main.php'                          => array(
				'icon'  => $images_url . 'plugin-pushengage.png',
				'name'  => __( 'PushEngage', 'easy-digital-downloads' ),
				'desc'  => esc_html__( 'Connect with your visitors after they leave your website with the leading web push notification software. Over 10,000+ businesses worldwide use PushEngage to send 15 billion notifications each month.', 'easy-digital-downloads' ),
				'wporg' => 'https://wordpress.org/plugins/pushengage/',
				'url'   => 'https://downloads.wordpress.org/plugin/pushengage.zip',
			),

			'instagram-feed/instagram-feed.php'            => array(
				'icon'  => $images_url . 'plugin-sb-instagram.png',
				'name'  => __( 'Smash Balloon Instagram Feeds', 'easy-digital-downloads' ),
				'desc'  => esc_html__( 'Easily display Instagram content on your WordPress site without writing any code. Comes with multiple templates, ability to show content from multiple accounts, hashtags, and more. Trusted by 1 million websites.', 'easy-digital-downloads' ),
				'wporg' => 'https://wordpress.org/plugins/instagram-feed/',
				'url'   => 'https://downloads.wordpress.org/plugin/instagram-feed.zip',
				'pro'   => array(
					'plug' => 'instagram-feed-pro/instagram-feed.php',
					'name' => __( 'Smash Balloon Instagram Feeds Pro', 'easy-digital-downloads' ),
					'url'  => 'https://smashballoon.com/instagram-feed/?utm_source=eddplugin&utm_medium=link&utm_campaign=About%20EDD',
					'act'  => 'go-to-url',
				),
			),

			'custom-facebook-feed/custom-facebook-feed.php' => array(
				'icon'  => $images_url . 'plugin-sb-fb.png',
				'name'  => __( 'Smash Balloon Facebook Feeds', 'easy-digital-downloads' ),
				'desc'  => esc_html__( 'Easily display Facebook content on your WordPress site without writing any code. Comes with multiple templates, ability to embed albums, group content, reviews, live videos, comments, and reactions.', 'easy-digital-downloads' ),
				'wporg' => 'https://wordpress.org/plugins/custom-facebook-feed/',
				'url'   => 'https://downloads.wordpress.org/plugin/custom-facebook-feed.zip',
				'pro'   => array(
					'plug' => 'custom-facebook-feed-pro/custom-facebook-feed.php',
					'icon' => $images_url . 'plugin-sb-fb.png',
					'name' => __( 'Smash Balloon Facebook Feeds Pro', 'easy-digital-downloads' ),
					'desc' => esc_html__( 'Easily display Facebook content on your WordPress site without writing any code. Comes with multiple templates, ability to embed albums, group content, reviews, live videos, comments, and reactions.', 'easy-digital-downloads' ),
					'url'  => 'https://smashballoon.com/custom-facebook-feed/?utm_source=eddplugin&utm_medium=link&utm_campaign=About%20EDD',
					'act'  => 'go-to-url',
				),
			),

			'feeds-for-youtube/youtube-feed.php'           => array(
				'icon'  => $images_url . 'plugin-sb-youtube.png',
				'name'  => __( 'Smash Balloon YouTube Feeds', 'easy-digital-downloads' ),
				'desc'  => esc_html__( 'Easily display YouTube videos on your WordPress site without writing any code. Comes with multiple layouts, ability to embed live streams, video filtering, ability to combine multiple channel videos, and more.', 'easy-digital-downloads' ),
				'wporg' => 'https://wordpress.org/plugins/feeds-for-youtube/',
				'url'   => 'https://downloads.wordpress.org/plugin/feeds-for-youtube.zip',
				'pro'   => array(
					'plug' => 'youtube-feed-pro/youtube-feed.php',
					'name' => __( 'Smash Balloon YouTube Feeds Pro', 'easy-digital-downloads' ),
					'url'  => 'https://smashballoon.com/youtube-feed/?utm_source=eddplugin&utm_medium=link&utm_campaign=About%20EDD',
					'act'  => 'go-to-url',
				),
			),

			'custom-twitter-feeds/custom-twitter-feed.php' => array(
				'icon'  => $images_url . 'plugin-sb-twitter.png',
				'name'  => __( 'Smash Balloon Twitter Feeds', 'easy-digital-downloads' ),
				'desc'  => esc_html__( 'Easily display Twitter content in WordPress without writing any code. Comes with multiple layouts, ability to combine multiple Twitter feeds, Twitter card support, tweet moderation, and more.', 'easy-digital-downloads' ),
				'wporg' => 'https://wordpress.org/plugins/custom-twitter-feeds/',
				'url'   => 'https://downloads.wordpress.org/plugin/custom-twitter-feeds.zip',
				'pro'   => array(
					'plug' => 'custom-twitter-feeds-pro/custom-twitter-feed.php',
					'name' => __( 'Smash Balloon Twitter Feeds Pro', 'easy-digital-downloads' ),
					'url'  => 'https://smashballoon.com/custom-twitter-feeds/?utm_source=eddplugin&utm_medium=link&utm_campaign=About%20EDD',
					'act'  => 'go-to-url',
				),
			),

			'trustpulse-api/trustpulse.php'                => array(
				'icon'  => $images_url . 'plugin-trustpulse.png',
				'name'  => __( 'TrustPulse', 'easy-digital-downloads' ),
				'desc'  => esc_html__( 'Boost your sales and conversions by up to 15% with real-time social proof notifications. TrustPulse helps you show live user activity and purchases to help convince other users to purchase.', 'easy-digital-downloads' ),
				'wporg' => 'https://wordpress.org/plugins/trustpulse-api/',
				'url'   => 'https://downloads.wordpress.org/plugin/trustpulse-api.zip',
			),

			'searchwp/index.php'                           => array(
				'icon'  => $images_url . 'plugin-searchwp.png',
				'name'  => __( 'SearchWP', 'easy-digital-downloads' ),
				'desc'  => esc_html__( 'The most advanced WordPress search plugin. Customize your WordPress search algorithm, reorder search results, track search metrics, and everything you need to leverage search to grow your business.', 'easy-digital-downloads' ),
				'wporg' => false,
				'url'   => 'https://searchwp.com/?utm_source=eddplugin&utm_medium=link&utm_campaign=About%20EDD',
				'act'   => 'go-to-url',
			),

			'affiliate-wp/affiliate-wp.php'                => array(
				'icon'  => $images_url . 'plugin-affwp.png',
				'name'  => __( 'AffiliateWP', 'easy-digital-downloads' ),
				'desc'  => esc_html__( 'The #1 affiliate management plugin for WordPress. Easily create an affiliate program for your eCommerce store or membership site within minutes and start growing your sales with the power of referral marketing.', 'easy-digital-downloads' ),
				'wporg' => false,
				'url'   => 'https://affiliatewp.com/?utm_source=eddplugin&utm_medium=link&utm_campaign=About%20EDD',
				'act'   => 'go-to-url',
			),

			'stripe/stripe-checkout.php'                   => array(
				'icon'  => $images_url . 'plugin-wp-simple-pay.png',
				'name'  => __( 'WP Simple Pay', 'easy-digital-downloads' ),
				'desc'  => esc_html__( 'The #1 Stripe payments plugin for WordPress. Start accepting one-time and recurring payments on your WordPress site without setting up a shopping cart. No code required.', 'easy-digital-downloads' ),
				'wporg' => 'https://wordpress.org/plugins/stripe/',
				'url'   => 'https://downloads.wordpress.org/plugin/stripe.zip',
				'pro'   => array(
					'plug' => 'wp-simple-pay-pro-3/simple-pay.php',
					'name' => __( 'WP Simple Pay Pro', 'easy-digital-downloads' ),
					'url'  => 'https://wpsimplepay.com/?utm_source=eddplugin&utm_medium=link&utm_campaign=About%20EDD',
					'act'  => 'go-to-url',
				),
			),

			'wpforms-lite/wpforms.php'                     => array(
				'icon'  => $images_url . 'plugin-wpf.png',
				'name'  => __( 'WPForms', 'easy-digital-downloads' ),
				'desc'  => esc_html__( 'The best drag & drop WordPress form builder. Easily create beautiful contact forms, surveys, payment forms, and more with our 100+ form templates. Trusted by over 4 million websites as the best forms plugin.', 'easy-digital-downloads' ),
				'wporg' => 'https://wordpress.org/plugins/wpforms-lite/',
				'url'   => 'https://downloads.wordpress.org/plugin/wpforms-lite.zip',
				'pro'   => array(
					'plug' => 'wpforms/wpforms.php',
					'name' => __( 'WPForms Pro', 'easy-digital-downloads' ),
					'url'  => 'https://wpforms.com/?utm_source=eddplugin&utm_medium=link&utm_campaign=About%20EDD',
					'act'  => 'go-to-url',
				),
			),

			'sugar-calendar-lite/sugar-calendar-lite.php'  => array(
				'icon'  => $images_url . 'plugin-sugarcalendar.png',
				'name'  => __( 'Sugar Calendar', 'easy-digital-downloads' ),
				'desc'  => esc_html__( 'A simple & powerful event calendar plugin for WordPress that comes with all the event management features including payments, scheduling, timezones, ticketing, recurring events, and more.', 'easy-digital-downloads' ),
				'wporg' => 'https://wordpress.org/plugins/sugar-calendar-lite/',
				'url'   => 'https://downloads.wordpress.org/plugin/sugar-calendar-lite.zip',
				'pro'   => array(
					'plug' => 'sugar-calendar/sugar-calendar.php',
					'name' => __( 'Sugar Calendar Pro', 'easy-digital-downloads' ),
					'url'  => 'https://sugarcalendar.com/?utm_source=eddplugin&utm_medium=link&utm_campaign=About%20EDD',
					'act'  => 'go-to-url',
				),
			),

			'charitable/charitable.php'                    => array(
				'icon'  => $images_url . 'plugin-charitable.png',
				'name'  => __( 'WP Charitable', 'easy-digital-downloads' ),
				'desc'  => esc_html__( 'Top-rated WordPress donation and fundraising plugin. Over 10,000+ non-profit organizations and website owners use Charitable to create fundraising campaigns and raise more money online.', 'easy-digital-downloads' ),
				'wporg' => 'https://wordpress.org/plugins/charitable/',
				'url'   => 'https://downloads.wordpress.org/plugin/charitable.zip',
			),

			'insert-headers-and-footers/ihaf.php'          => array(
				'icon'  => $images_url . 'plugin-wpcode.png',
				'name'  => __( 'WPCode', 'easy-digital-downloads' ),
				'desc'  => esc_html__( 'Future proof your WordPress customizations with the most popular code snippet management plugin for WordPress. Trusted by over 1,500,000+ websites for easily adding code to WordPress right from the admin area.', 'easy-digital-downloads' ),
				'wporg' => 'https://wordpress.org/plugins/insert-headers-and-footers/',
				'url'   => 'https://downloads.wordpress.org/plugin/insert-headers-and-footers.zip',
				'pro'   => array(
					'plug' => 'wpcode-premium/wpcode.php',
					'name' => __( 'WPCode Pro', 'easy-digital-downloads' ),
					'url'  => 'https://wpcode.com/?utm_source=eddplugin&utm_medium=link&utm_campaign=About%20EDD',
					'act'  => 'go-to-url',
				),
			),

			'duplicator/duplicator.php'                    => array(
				'icon'  => $images_url . 'plugin-duplicator.png',
				'name'  => __( 'Duplicator', 'easy-digital-downloads' ),
				'desc'  => esc_html__( 'Leading WordPress backup & site migration plugin. Over 1,500,000+ smart website owners use Duplicator to make reliable and secure WordPress backups to protect their websites. It also makes website migration really easy.', 'easy-digital-downloads' ),
				'wporg' => 'https://wordpress.org/plugins/duplicator/',
				'url'   => 'https://downloads.wordpress.org/plugin/duplicator.zip',
				'pro'   => array(
					'plug' => 'duplicator-pro/duplicator-pro.php',
					'name' => __( 'Duplicator Pro', 'easy-digital-downloads' ),
					'url'  => 'https://duplicator.com/?utm_source=eddplugin&utm_medium=link&utm_campaign=About%20EDD',
					'act'  => 'go-to-url',
				),
			),
		);
	}

	/**
	 * Get AM plugin data to display in the Addons section of About tab.
	 *
	 * @since 3.2.4
	 *
	 * @param string $plugin      Plugin slug.
	 * @param array  $details     Plugin details.
	 * @param array  $all_plugins List of all plugins.
	 *
	 * @return array
	 */
	private function get_plugin_data( $plugin, $details, $all_plugins ) {

		$have_pro = ( ! empty( $details['pro'] ) && ! empty( $details['pro']['plug'] ) );
		$show_pro = false;

		$plugin_data = array();

		if ( $have_pro ) {
			if ( array_key_exists( $plugin, $all_plugins ) ) {
				if ( is_plugin_active( $plugin ) ) {
					$show_pro = true;
				}
			}

			if ( array_key_exists( $details['pro']['plug'], $all_plugins ) ) {
				$show_pro = true;
			}

			if ( $show_pro ) {
				// Pull out the pro plugin details, and remove it from the array.
				$pro_details = $details['pro'];
				unset( $details['pro'] );

				// Now merge the pro details with the main details.
				$details = array_merge( $details, $pro_details );

				// Now unset the 'worg' url.
				unset( $details['worg'] );

				// Set the plugin slug as the pro plugin.
				$plugin = $details['plug'];
			}
		}

		if ( array_key_exists( $plugin, $all_plugins ) ) {
			if ( is_plugin_active( $plugin ) ) {
				$plugin_data['status_text']   = esc_html__( 'Active', 'easy-digital-downloads' );
				$plugin_data['button_text']   = esc_html__( 'Installed & Active', 'easy-digital-downloads' );
				$plugin_data['plugin_status'] = 'active';
				$plugin_data['plugin']        = esc_attr( $plugin );
				$plugin_data['action']        = false;
			} else {
				$plugin_data['status_text']   = esc_html__( 'Inactive', 'easy-digital-downloads' );
				$plugin_data['button_text']   = esc_html__( 'Activate', 'easy-digital-downloads' );
				$plugin_data['plugin_status'] = 'inactive';
				$plugin_data['action']        = 'activate';
				$plugin_data['plugin']        = esc_attr( $plugin );
			}
		} else {
			$plugin_data['status_text']   = esc_html__( 'Not Installed', 'easy-digital-downloads' );
			$plugin_data['button_text']   = esc_html__( 'Install Plugin', 'easy-digital-downloads' );
			$plugin_data['plugin_status'] = 'not-installed';
			$plugin_data['action']        = 'install';
			$plugin_data['plugin']        = esc_url( $details['url'] );

			if ( isset( $details['act'] ) && 'go-to-url' === $details['act'] ) {
				$plugin_data['action'] = 'goto';
				$plugin_data['plugin'] = esc_url( $details['url'] );
			}
		}

		$plugin_data['details']           = $details;
		$plugin_data['button_parameters'] = array(
			'plugin'      => $plugin_data['plugin'],
			'action'      => $plugin_data['action'],
			'button_text' => $plugin_data['button_text'],
		);

		return $plugin_data;
	}
}
