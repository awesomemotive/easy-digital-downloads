<?php
/**
 * Defines the direct SiteHealth tests for EDD.
 */

namespace EDD\Admin\SiteHealth;

defined( 'ABSPATH' ) || exit;

class Direct extends Test {

	public function get() {
		$tests = array();

		if ( current_user_can( 'manage_shop_settings' ) ) {
			$tests['edd_purchase_page'] = array(
				'label' => __( 'EDD Checkout Page', 'easy-digital-downloads' ),
				'test'  => array( $this, 'get_test_missing_purchase_page' ),
			);

			$tests['edd_uploads_url_protected'] = array(
				'label'     => __( 'Protected Download Files', 'easy-digital-downloads' ),
				'test'      => array( $this, 'get_test_uploads_url_protected' ),
				'skip_cron' => true,
			);

			$tests['edd_gateways_enabled']      = array(
				'label' => __( 'Enabled Gateways', 'easy-digital-downloads' ),
				'test'  => array( $this, 'get_test_gateways_enabled' ),
			);
		}

		$tests['edd_cron_enabled'] = array(
			'label' => __( 'Cron Events', 'easy-digital-downloads' ),
			'test'  => array( $this, 'get_test_cron_enabled' ),
		);

		$licenses      = new Licenses();
		$licenses_test = $licenses->get();

		if ( ! empty( $licenses_test ) ) {
			$tests['edd_licenses'] = $licenses_test;
		}

		return $tests;
	}

	/**
	 * Adds a test for the purchase/checkout page.
	 *
	 * @since 3.1.2
	 * @return array
	 */
	public function get_test_missing_purchase_page() {
		$result = array(
			'label'       => __( 'You have a checkout page set', 'easy-digital-downloads' ),
			'status'      => 'good',
			'badge'       => $this->get_default_badge(),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'Your checkout page is set up and ready to process orders.', 'easy-digital-downloads' )
			),
			'actions'     => '',
			'test'        => 'edd_missing_purchase_page',
		);

		$purchase_page = (int) edd_get_option( 'purchase_page', false );
		if ( empty( $purchase_page ) ) {
			$result['label'] = __( 'Your checkout page is missing', 'easy-digital-downloads' );

			$result['status']         = 'critical';
			$result['badge']['color'] = 'red';

			$result['description'] = sprintf(
				'<p>%s</p>',
				__( 'Easy Digital Downloads requires a specific checkout page to be set to easily handle user interactions.', 'easy-digital-downloads' )
			);
			$result['actions'] = sprintf(
				'<a class="button button-primary" href="%s">%s</a>',
				edd_get_admin_url(
					array(
						'page'    => 'edd-settings',
						'tab'     => 'general',
						'section' => 'pages',
					)
				),
				__( 'Fix the Checkout Page', 'easy-digital-downloads' )
			);
		} else {
			if ( ! has_block( 'edd/checkout', $purchase_page ) ) {
				$result['label'] = __( 'Your checkout page is using the legacy shortcode', 'easy-digital-downloads' );
				$result['status']         = 'recommended';
				$result['badge']['color'] = 'orange';

				$result['description'] = wpautop(
					sprintf(
						/* translators: %1$s - opening <code> tag, %2$s - closing <code> tag */
						__( 'Your checkout page is configured; however, it is currently using the legacy %1$s[download_checkout]%2$s shortcode. We recommend changing your checkout to use the EDD Checkout Block.', 'easy-digital-downloads' ),
						'<code>',
						'</code>'
					)
				);

				// Link the action to edit the checkout page.
				$result['actions'] = sprintf(
					'<a class="button button-secondary" href="%s">%s</a>',
					admin_url( 'post.php?post=' . $purchase_page . '&action=edit' ),
					__( 'Edit Checkout Page', 'easy-digital-downloads' )
				);
			}
		}

		return $result;
	}

	/**
	 * Adds a test for whether the EDD uploads directory is protected.
	 *
	 * @return array
	 */
	public function get_test_uploads_url_protected() {
		$result = array(
			'label'       => __( 'Your download files are protected', 'easy-digital-downloads' ),
			'status'      => 'good',
			'badge'       => $this->get_default_badge(),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'Your checkout page is a critical part of your store.', 'easy-digital-downloads' )
			),
			'actions'     => '',
			'test'        => 'edd_uploads_url_protected',
		);

		if ( edd_is_uploads_url_protected() ) {
			return $result;
		}

		// Attempt to get the main index.php file. If we get a 403, the downloads are protected after all.
		$check = wp_safe_remote_get( trailingslashit( edd_get_upload_url() ) . 'index.php' );
		if ( 403 === wp_remote_retrieve_response_code( $check ) ) {
			return $result;
		}

		// Get the upload directory.
		$upload_directory = edd_get_upload_dir();

		// Running NGINX.
		$show_nginx_notice = apply_filters( 'edd_show_nginx_redirect_notice', true );
		if ( $show_nginx_notice && ! empty( $GLOBALS['is_nginx'] ) ) {
			// The default NGINX recommendation for users.
			$result['label']          = __( 'Your download files may not be protected', 'easy-digital-downloads' );
			$result['status']         = 'recommended';
			$result['badge']['color'] = 'orange';
			$result['description']    = sprintf(
				'<p>%s %s</p>',
				sprintf(
					/* translators: 1. opening link tag; 2. closing link tag */
					__( 'To ensure the best protection, you should use this doc to add this %1$sNGINX redirect rule%2$s.', 'easy-digital-downloads' ),
					'<a href="https://easydigitaldownloads.com/docs/download-files-not-protected-on-nginx/">',
					'</a>'
				),
				__( 'If you have already done this, you can disregard this notice.', 'easy-digital-downloads' )
			);

			$download_method = edd_get_option( 'download_method', 'direct' );
			$symlink         = edd_get_option( 'symlink_file_downloads', false );

			$additional_description = '';

			if ( 'direct' === $download_method ) {
				// If using the 'direct' download method, let the customer know that we are already obfuscating the URL, but for the best protection, make the recommended changes.
				$additional_description .= sprintf(
					'<p>%s</p>',
					__( 'No need to worry, you are using the recommended \'Forced\' download method, and customers should never see the direct path to the files. The following action is still recommended, however.', 'easy-digital-downloads' )
				);
			} elseif ( 'redirect' === $download_method && false === $symlink ) {
				// If using the 'redirect' download method but not the symlink, they need to make this change. Adjust to a critical notice with a link to make suggested changes.
				$result['badge']['color'] = 'red';
				$result['status']         = 'critical';
				$additional_description   = sprintf(
					'<p>%s</p>',
					__( 'You currently are using the \'Redirect\' download method, which may expose your downloadable products. Either switch to the \'Forced\' method or enable \'Symlinks\'.', 'easy-digital-downloads' )
				);

				$result['actions'] = sprintf(
					'<a class="button button-primary" href="%s">%s</a>',
					edd_get_admin_url(
						array(
							'page'    => 'edd-settings',
							'tab'     => 'misc',
							'section' => 'file-downloads',
						)
					),
					__( 'Protect your files', 'easy-digital-downloads' )
				);
			} else {
				// If using the 'redirect' download method and the symlink, they are already protected, but we can let them know that they can make the recommended changes.
				$additional_description = sprintf(
					'<p>%s</p>',
					__( 'Your current download method creates a temporary copy of the file for the customer to download. After they successfully download it, it is removed, ensuring they never have direct access to your product files.', 'easy-digital-downloads' )
				);
			}

			if ( ! empty( $additional_description ) ) {
				$result['description'] = $additional_description . $result['description'];
			}

			return $result;
		}

		// Running Apache.
		if ( ! empty( $GLOBALS['is_apache'] ) && ! edd_htaccess_exists() && ! get_user_meta( get_current_user_id(), '_edd_htaccess_missing_dismissed', true ) ) {
			$result['label']          = __( 'Your download files are currently not protected', 'easy-digital-downloads' );
			$result['status']         = 'critical';
			$result['badge']['color'] = 'orange';
			$result['description']    = sprintf(
				'<p>%s %s</p>',
				sprintf(
					/* translators: the upload directory */
					__( 'The .htaccess file is missing from: %s', 'easy-digital-downloads' ),
					'<strong>' . $upload_directory . '</strong>'
				),
				sprintf(
					/* translators: the upload directory */
					__( 'First, please resave the Misc settings tab a few times. If this warning continues to appear, create a file called ".htaccess" in the %s directory, and copy the following into it:', 'easy-digital-downloads' ),
					'<strong>' . $upload_directory . '</strong>'
				)
			);
			$result['actions'] = sprintf(
				'<a href="%s">%s</a>',
				edd_get_admin_url(
					array(
						'page' => 'edd-settings',
						'tab'  => 'misc',
					)
				),
				__( 'Miscellaneous Settings', 'easy-digital-downloads' )
			);

			return $result;
		}

		return $result;
	}

	/**
	 * Adds a test for enabled gateways.
	 *
	 * @since 3.1.2
	 * @return array
	 */
	public function get_test_gateways_enabled() {
		$result = array(
			'label'       => __( 'You have at least one gateway enabled', 'easy-digital-downloads' ),
			'status'      => 'good',
			'badge'       => $this->get_default_badge(),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'Fantastic! You have enabled a gateway and can accept orders.', 'easy-digital-downloads' )
			),
			'actions'     => '',
			'test'        => 'edd_gateways_enabled',
		);

		if ( edd_get_option( 'gateways' ) ) {
			return $result;
		}

		$result['status']         = 'critical';
		$result['badge']['color'] = 'red';
		$result['actions']        = sprintf(
			'<a href="%s">%s</a>',
			edd_get_admin_url(
				array(
					'page' => 'edd-settings',
					'tab'  => 'gateways',
				)
			),
			__( 'Please enable at least one gateway in order to allow customers to make purchases.', 'easy-digital-downloads' )
		);

		if ( edd_is_test_mode() ) {
			$result['status']         = 'recommended';
			$result['badge']['color'] = 'gray';
		}

		return $result;
	}

	/**
	 * Adds a test for checking whether cron events are disabled.
	 *
	 * @since 3.1.2
	 * @return array
	 */
	public function get_test_cron_enabled() {
		$result = array(
			'label'       => __( 'Scheduled events are running', 'easy-digital-downloads' ),
			'status'      => 'good',
			'badge'       => $this->get_default_badge(),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'Easy Digital Downloads uses scheduled events in a number of ways to help maintain performance and stability.', 'easy-digital-downloads' )
			),
			'actions'     => '',
			'test'        => 'edd_cron_enabled',
		);

		if ( ! defined( 'WP_DISABLE_CRON' ) || empty( WP_DISABLE_CRON ) ) {
			return $result;
		}

		$result['label']          = __( 'Scheduled events are not running', 'easy-digital-downloads' );
		$result['status']         = 'critical';
		$result['badge']['color'] = 'red';
		$result['description']   .= sprintf(
			'<p>%s</p><p>%s</p>',
			__( 'Your site has cron events disabled. WordPress cron events should run at least every ten minutes for your store to manage order related events.', 'easy-digital-downloads' ),
			__( 'Some hosting providers disable cron events by default, in favor of their own solution to running WP_CRON. Please contact your hosting provider to confirm any necessary changes.', 'easy-digital-downloads' )
		);

		return $result;
	}
}
