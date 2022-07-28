<?php
/**
 * Upgrade Functions
 *
 * @package     EDD
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.1
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Perform automatic database upgrades when necessary
 *
 * @since 2.6
 * @return void
 */
function edd_do_automatic_upgrades() {

	$did_upgrade = false;
	$edd_version = edd_get_db_version();

	if ( version_compare( $edd_version, EDD_VERSION, '<' ) ) {

		// Let us know that an upgrade has happened
		$did_upgrade = true;
	}

	if ( $did_upgrade ) {
		edd_update_db_version();

		// Send a check in. Note: this only sends if data tracking has been enabled
		$tracking = new EDD_Tracking;
		$tracking->send_checkin( false, true );
	}

	// 2.9.2 to 2.9.3
	$fix_show_privacy_policy_setting = edd_get_option( 'show_agree_to_privacy_policy_on_checkout', false );
	if ( ! empty( $fix_show_privacy_policy_setting ) ) {
		edd_update_option( 'show_privacy_policy_on_checkout', $fix_show_privacy_policy_setting );

		edd_delete_option( 'show_agree_to_privacy_policy_on_checkout' );
	}

	// 3.0: deactivate Manual Purchases addon
	if ( function_exists( 'edd_load_manual_purchases' ) ) {
		deactivate_plugins( 'edd-manual-purchases/edd-manual-purchases.php' );
		delete_option( 'edd_manual_purchases_license_active' );
	}
}
add_action( 'admin_init', 'edd_do_automatic_upgrades' );

/**
 * Display Upgrade Notices
 *
 * @since 1.3.1
 * @return void
 */
function edd_show_upgrade_notices() {
	global $wpdb;

	// Don't show notices on the upgrades page
	if ( ! empty( $_GET['page'] ) && ( 'edd-upgrades' === $_GET['page'] ) ) {
		return;
	}

	$edd_version = edd_get_db_version();

	if ( ! get_option( 'edd_payment_totals_upgraded' ) && !edd_get_db_version() ) {
		if ( wp_count_posts( 'edd_payment' )->publish < 1 ) {
			return; // No payment exist yet
		}

		// The payment history needs updated for version 1.2
		$url            = add_query_arg( 'edd-action', 'upgrade_payments' );
		$upgrade_notice = sprintf( __( 'The Payment History needs to be updated. %s', 'easy-digital-downloads' ), '<a href="' . wp_nonce_url( esc_url( $url ), 'edd_upgrade_payments_nonce' ) . '">' . __( 'Click to Upgrade', 'easy-digital-downloads' ) . '</a>' );

		EDD()->notices->add_notice( array(
			'id'      => 'edd-payments-upgrade',
			'class'   => 'error',
			'message' => $upgrade_notice
		) );
	}

	if ( version_compare( $edd_version, '1.3.2', '<' ) && ! get_option( 'edd_logs_upgraded' ) ) {
		printf(
			'<div class="notice notice-warning"><p>' . esc_html__( 'The Purchase and File Download History in Easy Digital Downloads needs to be upgraded, click %shere%s to start the upgrade.', 'easy-digital-downloads' ) . '</p></div>',
			'<a href="' . esc_url( admin_url( 'options.php?page=edd-upgrades' ) ) . '">',
			'</a>'
		);
	}

	if ( version_compare( $edd_version, '1.3.0', '<' ) || version_compare( $edd_version, '1.4', '<' ) ) {
		printf(
			'<div class="notice notice-warning"><p>' . esc_html__( 'Easy Digital Downloads needs to upgrade the plugin pages, click %shere%s to start the upgrade.', 'easy-digital-downloads' ) . '</p></div>',
			'<a href="' . esc_url( admin_url( 'options.php?page=edd-upgrades' ) ) . '">',
			'</a>'
		);
	}

	if ( version_compare( $edd_version, '1.5', '<' ) ) {
		printf(
			'<div class="notice notice-warning"><p>' . esc_html__( 'Easy Digital Downloads needs to upgrade the database, click %shere%s to start the upgrade.', 'easy-digital-downloads' ) . '</p></div>',
			'<a href="' . esc_url( admin_url( 'options.php?page=edd-upgrades' ) ) . '">',
			'</a>'
		);
	}

	if ( version_compare( $edd_version, '2.0', '<' ) ) {
		printf(
			'<div class="notice notice-warning"><p>' . esc_html__( 'Easy Digital Downloads needs to upgrade the database, click %shere%s to start the upgrade.', 'easy-digital-downloads' ) . '</p></div>',
			'<a href="' . esc_url( admin_url( 'options.php?page=edd-upgrades' ) ) . '">',
			'</a>'
		);
	}

	// Sequential Orders was the first stepped upgrade, so check if we have a stalled upgrade
	$resume_upgrade = edd_maybe_resume_upgrade();
	if ( ! empty( $resume_upgrade ) ) {

		$resume_url = add_query_arg( $resume_upgrade, admin_url( 'index.php' ) );
		printf(
			'<div class="error"><p>' . __( 'Easy Digital Downloads needs to complete a database upgrade that was previously started, click <a href="%s">here</a> to resume the upgrade.', 'easy-digital-downloads' ) . '</p></div>',
			esc_url( $resume_url )
		);

	} else {

		// Include all 'Stepped' upgrade process notices in this else statement,
		// to avoid having a pending, and new upgrade suggested at the same time

		if ( get_option( 'edd_upgrade_sequential' ) && edd_get_payments( array( 'fields' => 'ids' ) ) ) {
			printf(
				'<div class="notice notice-warning"><p>' . __( 'Easy Digital Downloads needs to upgrade past orders to make them sequential. <a href="%s">Start the order numbers upgrade</a>.', 'easy-digital-downloads' ) . '</p></div>',
				esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=upgrade_sequential_payment_numbers' ) )
			);
		}

		if ( version_compare( $edd_version, '2.1', '<' ) ) {
			printf(
				'<div class="notice notice-warning"><p>' . esc_html__( 'Easy Digital Downloads needs to upgrade the customer database, click %shere%s to start the upgrade.', 'easy-digital-downloads' ) . '</p></div>',
				'<a href="' . esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=upgrade_customers_db' ) ) . '">',
				'</a>'
			);
		}

		if ( version_compare( $edd_version, '2.2.6', '<' ) ) {
			printf(
				'<div class="notice notice-warning"><p>' . __( 'Easy Digital Downloads needs to upgrade the payment database, click <a href="%s">here</a> to start the upgrade.', 'easy-digital-downloads' ) . '</p></div>',
				esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=upgrade_payments_price_logs_db' ) )
			);
		}

		if ( version_compare( $edd_version, '2.3', '<' ) ) {
			if ( ! edd_has_upgrade_completed( 'upgrade_customer_payments_association' ) ) {
				printf(
					'<div class="notice notice-warning"><p>' . __( 'Easy Digital Downloads needs to upgrade the customer database, click <a href="%s">here</a> to start the upgrade.', 'easy-digital-downloads' ) . '</p></div>',
					esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=upgrade_customer_payments_association' ) )
				);
			}
		}

		if ( version_compare( $edd_version, '2.3', '<' ) ) {
			if ( ! edd_has_upgrade_completed( 'upgrade_payment_taxes' ) ) {
				printf(
					'<div class="notice notice-warning"><p>' . __( 'Easy Digital Downloads needs to upgrade the payment database, click <a href="%s">here</a> to start the upgrade.', 'easy-digital-downloads' ) . '</p></div>',
					esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=upgrade_payment_taxes' ) )
				);
			}
		}

		if ( version_compare( $edd_version, '2.4', '<' ) ) {
			if ( ! edd_has_upgrade_completed( 'upgrade_user_api_keys' ) ) {
				printf(
					'<div class="notice notice-warning"><p>' . __( 'Easy Digital Downloads needs to upgrade the API Key database, click <a href="%s">here</a> to start the upgrade.', 'easy-digital-downloads' ) . '</p></div>',
					esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=upgrade_user_api_keys' ) )
				);
			}
		}

		if ( version_compare( $edd_version, '2.4.3', '<' ) ) {
			if ( ! edd_has_upgrade_completed( 'remove_refunded_sale_logs' ) ) {
				printf(
					'<div class="notice notice-warning"><p>' . __( 'Easy Digital Downloads needs to upgrade the payments database, click <a href="%s">here</a> to start the upgrade.', 'easy-digital-downloads' ) . '</p></div>',
					esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=remove_refunded_sale_logs' ) )
				);
			}
		}

		if ( version_compare( $edd_version, '2.9.2', '<' ) ) {
			if ( ! edd_has_upgrade_completed( 'update_file_download_log_data' ) ) {
				printf(
					'<div class="notice notice-warning"><p>' . __( 'Easy Digital Downloads needs to upgrade the file download logs database, click <a href="%s">here</a> to start the upgrade.', 'easy-digital-downloads' ) . '</p></div>',
					esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=update_file_download_log_data' ) )
				);
			}
		}

		/** 3.0 Upgrades ******************************************************/

		// Check if we need to do any upgrades.
		if ( ! edd_v30_is_migration_complete() ) {

			// If any EDD 2.x data exists, the migration should be run.
			$needs_migration = _edd_needs_v3_migration();
			$version         = false;
			// If the migration doesn't need to be run, mark the upgrades as complete.
			if ( ! $needs_migration ) {
				$upgrades = edd_get_v30_upgrades();
				$upgrades = array_keys( $upgrades );
				foreach ( $upgrades as $upgrade ) {
					edd_set_upgrade_complete( $upgrade );
				}
			} else {
				$component = edd_get_component( 'order' );
				$table     = $component->get_interface( 'table' );
				if ( ! empty( $table ) ) {
					$version = $table->get_version();
				}
			}

			// The migration needs to be run, and the database table exists.
			if ( $needs_migration && $version ) {
				?>
				<div class="updated">
					<?php if ( get_option( 'edd_v30_cli_migration_running' ) ) { ?>
						<p><?php esc_html_e( 'Easy Digital Downloads is performing a database migration via WP-CLI. This message will be removed when the migration is complete.', 'easy-digital-downloads' ); ?></p>
						<?php
					} else {
						?>
						<p>
							<?php
							printf(
								__( 'Easy Digital Downloads needs to upgrade the database. %sLearn more about this upgrade%s.', 'easy-digital-downloads' ),
								'<a href="#" onClick="jQuery(this).parent().next(\'div\').slideToggle()">',
								'</a>'
							);
							?>
						</p>
						<div style="display: none;">
							<h3>
								<?php esc_html_e( 'About this upgrade:', 'easy-digital-downloads' ); ?>
							</h3>
							<p>
								<?php
								printf(
									/* translators: 1. Opening strong/italics tag; do not translate. 2. Closing strong/italics tag; do not translate. */
									esc_html__( 'This is a %1$smandatory%2$s update that will migrate all Easy Digital Downloads data to custom database tables. This upgrade will provide better performance and scalability.', 'easy-digital-downloads' ),
									'<strong><em>',
									'</em></strong>'
								);
								?>
							</p>
							<p>
								<?php
								printf(
									/* translators: 1. Opening strong tag; do not translate. 2. Closing strong tag; do not translate. */
									esc_html__( '%1$sPlease back up your database before starting this upgrade.%2$s This upgrade routine will make irreversible changes to the database.', 'easy-digital-downloads' ),
									'<strong>',
									'</strong>'
								);
								?>
							</p>
							<p>
								<?php
								printf(
									/* translators: 1. Opening strong tag; do not translate. 2. Closing strong tag; do not translate. 3. Line break; do not translate. 4. CLI command example; do not translate. */
									esc_html__( '%1$sAdvanced User?%2$s This upgrade can also be run via WP-CLI with the following command:%3$s%3$s%4$s', 'easy-digital-downloads' ),
									'<strong>',
									'</strong>',
									'<br />',
									'<code>wp edd v30_migration</code>'
								);
								?>
							</p>
							<p>
								<?php esc_html_e( 'For large sites, this is the recommended method of upgrading.', 'easy-digital-downloads' ); ?>
							</p>
						</div>
						<?php
						$url = add_query_arg(
							array(
								'page'        => 'edd-upgrades',
								'edd-upgrade' => 'v30_migration',
							),
							admin_url()
						);
						?>
						<p>
							<a class="button button-secondary" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Begin the upgrade', 'easy-digital-downloads' ); ?></a>
						</p>
						<?php
					}
					?>
				</div>
				<?php
			} elseif ( $needs_migration && ! $version ) {

				// The orders database table is missing (we assume all primary tables have failed to create).
				$message          = __( 'Easy Digital Downloads was unable to create the necessary database tables to complete this update. Your site may not meet the minimum requirements for EDD 3.0.', 'easy-digital-downloads' );
				$database_version = $wpdb->db_version();

				// The database version is the problem.
				if ( version_compare( $database_version, '5.6', '<' ) ) {
					$message .= ' ' . sprintf(
						/* translators: 1. opening anchor tag, do not translate; 2. closing anchor tag, do not translate; 3. MySQL database version, do not translate */
						__( 'Please contact your host and ask them to upgrade your environment to meet our %1$sminimum technical requirements%2$s. Your MySQL version is %3$s and needs to be updated.', 'easy-digital-downloads' ),
						'<a href="https://easydigitaldownloads.com/recommended-wordpress-hosting/">',
						'</a>',
						$database_version
					);
				} else {
					$message .= ' ' . sprintf(
						/* translators: 1. opening anchor tag, do not translate; 2. closing anchor tag, do not translate */
						__( '%1$sContact our support team%2$s for help with next steps.', 'easy-digital-downloads' ),
						'<a href="https://easydigitaldownloads.com/support/">',
						'</a>'
					);
				}
				?>
				<div class="notice notice-error">
					<p><?php echo wp_kses_post( $message ); ?></p>
				</div>
				<?php
			}
		}

		/*
		 * NOTICE:
		 *
		 * When adding new upgrade notices, please be sure to put the action
		 * into the upgrades array during install: /includes/install.php @ Appox Line 156
		 */

		// End 'Stepped' upgrade process notices
	}
}
add_action( 'admin_notices', 'edd_show_upgrade_notices' );

/**
 * Triggers all upgrade functions
 *
 * This function is usually triggered via AJAX
 *
 * @since 1.3.1
 * @return void
 */
function edd_trigger_upgrades() {

	// Bail if user is not capable
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	// Get the current version from the database
	$edd_version = edd_get_db_version();

	// 1.3 is the first version to use this option so we must add it here, but
	// only if settings exist and this is not a new install
	if ( empty( $edd_version ) && ! get_option( 'edd_settings' ) ) {
		$edd_version = '1.3';
		add_option( 'edd_version', $edd_version );
	}

	// Get the current version
	$current_version = edd_format_db_version( EDD_VERSION );

	if ( version_compare( $current_version, $edd_version, '>' ) ) {
		edd_v131_upgrades();
	}

	if ( version_compare( $edd_version, '1.3.0', '<' ) ) {
		edd_v134_upgrades();
	}

	if ( version_compare( $edd_version, '1.4', '<' ) ) {
		edd_v14_upgrades();
	}

	if ( version_compare( $edd_version, '1.5', '<' ) ) {
		edd_v15_upgrades();
	}

	if ( version_compare( $edd_version, '2.0', '<' ) ) {
		edd_v20_upgrades();
	}

	edd_update_db_version();

	// Let AJAX know that the upgrade is complete
	if ( edd_doing_ajax() ) {
		die( 'complete' );
	}
}
add_action( 'wp_ajax_edd_trigger_upgrades', 'edd_trigger_upgrades' );

/**
 * For use when doing 'stepped' upgrade routines, to see if we need to start somewhere in the middle
 * @since 2.2.6
 * @return mixed   When nothing to resume returns false, otherwise starts the upgrade where it left off
 */
function edd_maybe_resume_upgrade() {

	$doing_upgrade = get_option( 'edd_doing_upgrade', false );

	if ( empty( $doing_upgrade ) ) {
		return false;
	}

	return $doing_upgrade;
}

/**
 * Adds an upgrade action to the completed upgrades array
 *
 * @since  2.3
 * @param  string $upgrade_action The action to add to the copmleted upgrades array
 * @return bool                   If the function was successfully added
 */
function edd_set_upgrade_complete( $upgrade_action = '' ) {

	if ( empty( $upgrade_action ) ) {
		return false;
	}

	$completed_upgrades   = edd_get_completed_upgrades();
	$completed_upgrades[] = $upgrade_action;

	// Remove any blanks, and only show uniques
	$completed_upgrades = array_unique( array_values( $completed_upgrades ) );

	return update_option( 'edd_completed_upgrades', $completed_upgrades );
}

/**
 * Converts old sale and file download logs to new logging system
 *
 * @since 1.3.1
 * @uses WP_Query
 * @uses EDD_Logging
 * @return void
 */
function edd_v131_upgrades() {
	if ( get_option( 'edd_logs_upgraded' ) ) {
		return;
	}

	$edd_version = edd_get_db_version();

	if ( version_compare( $edd_version, '1.3', '>=' ) ) {
		return;
	}

	edd_set_time_limit();

	$query = new WP_Query( array(
		'post_type' 		=> 'download',
		'posts_per_page' 	=> -1,
		'post_status' 		=> 'publish'
	) );
	$downloads = $query->get_posts();

	if ( $downloads ) {
		$edd_log = new EDD_Logging();
		foreach ( $downloads as $download ) {
			// Convert sale logs
			$sale_logs = edd_get_download_sales_log( $download->ID, false );

			if ( $sale_logs ) {
				foreach ( $sale_logs['sales'] as $sale ) {
					$log_data = array(
						'post_parent'	=> $download->ID,
						'post_date'		=> $sale['date'],
						'log_type'		=> 'sale'
					);

					$log_meta = array(
						'payment_id'=> $sale['payment_id']
					);

					$log = $edd_log->insert_log( $log_data, $log_meta );
				}
			}

			// Convert file download logs
			$file_logs = edd_get_file_download_log( $download->ID, false );

			if ( $file_logs ) {
				foreach ( $file_logs['downloads'] as $log ) {
					$log_data = array(
						'post_parent'	=> $download->ID,
						'post_date'		=> $log['date'],
						'log_type'		=> 'file_download'

					);

					$log_meta = array(
						'user_info'	=> $log['user_info'],
						'file_id'	=> $log['file_id'],
						'ip'		=> $log['ip']
					);

					$log = $edd_log->insert_log( $log_data, $log_meta );
				}
			}
		}
	}
	add_option( 'edd_logs_upgraded', '1' );
}

/**
 * Upgrade routine for v1.3.0
 *
 * @since 1.3.0
 * @return void
 */
function edd_v134_upgrades() {
	$general_options = get_option( 'edd_settings_general' );

	// Settings already updated
	if ( isset( $general_options['failure_page'] ) ) {
		return;
	}

	// Failed Purchase Page
	$failed = wp_insert_post(
		array(
			'post_title'     => __( 'Transaction Failed', 'easy-digital-downloads' ),
			'post_content'   => __( 'Your transaction failed, please try again or contact site support.', 'easy-digital-downloads' ),
			'post_status'    => 'publish',
			'post_author'    => 1,
			'post_type'      => 'page',
			'post_parent'    => $general_options['purchase_page'],
			'comment_status' => 'closed'
		)
	);

	$general_options['failure_page'] = $failed;

	update_option( 'edd_settings_general', $general_options );
}

/**
 * Upgrade routine for v1.4
 *
 * @since 1.4
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_v14_upgrades() {

	/** Add [edd_receipt] to success page **/
	$success_page = get_post( edd_get_option( 'success_page' ) );

	// Check for the [edd_receipt] shortcode and add it if not present
	if ( strpos( $success_page->post_content, '[edd_receipt' ) === false ) {
		$page_content = $success_page->post_content .= "\n[edd_receipt]";
		wp_update_post( array( 'ID' => edd_get_option( 'success_page' ), 'post_content' => $page_content ) );
	}

	/** Convert Discounts to new Custom Post Type **/
	$discounts = get_option( 'edd_discounts' );

	if ( $discounts ) {
		foreach ( $discounts as $discount ) {

			$discount_id = wp_insert_post( array(
				'post_type'   => 'edd_discount',
				'post_title'  => isset( $discount['name'] ) ? $discount['name'] : '',
				'post_status' => 'active'
			) );

			$meta = array(
				'code'        => isset( $discount['code'] ) ? $discount['code'] : '',
				'uses'        => isset( $discount['uses'] ) ? $discount['uses'] : '',
				'max_uses'    => isset( $discount['max'] ) ? $discount['max'] : '',
				'amount'      => isset( $discount['amount'] ) ? $discount['amount'] : '',
				'start'       => isset( $discount['start'] ) ? $discount['start'] : '',
				'expiration'  => isset( $discount['expiration'] ) ? $discount['expiration'] : '',
				'type'        => isset( $discount['type'] ) ? $discount['type'] : '',
				'min_price'   => isset( $discount['min_price'] ) ? $discount['min_price'] : ''
			);

			foreach ( $meta as $meta_key => $value ) {
				update_post_meta( $discount_id, '_edd_discount_' . $meta_key, $value );
			}
		}

		// Remove old discounts from database
		delete_option( 'edd_discounts' );
	}
}


/**
 * Upgrade routine for v1.5
 *
 * @since 1.5
 * @return void
 */
function edd_v15_upgrades() {
	// Update options for missing tax settings
	$tax_options = get_option( 'edd_settings_taxes' );

	// Set include tax on checkout to off
	$tax_options['checkout_include_tax'] = 'no';

	// Check if prices are displayed with taxes
	$tax_options['prices_include_tax'] = isset( $tax_options['taxes_on_prices'] )
		? 'yes'
		: 'no';

	update_option( 'edd_settings_taxes', $tax_options );

	// Flush the rewrite rules for the new /edd-api/ end point
	flush_rewrite_rules( false );
}

/**
 * Upgrades for EDD v2.0
 *
 * @since 2.0
 * @return void
 */
function edd_v20_upgrades() {
	global $edd_options, $wpdb;

	edd_set_time_limit();

	// Upgrade for the anti-behavior fix - #2188
	if ( ! empty( $edd_options['disable_ajax_cart'] ) ) {
		unset( $edd_options['enable_ajax_cart'] );
	} else {
		$edd_options['enable_ajax_cart'] = '1';
	}

	// Upgrade for the anti-behavior fix - #2188
	if ( ! empty( $edd_options['disable_cart_saving'] ) ) {
		unset( $edd_options['enable_cart_saving'] );
	} else {
		$edd_options['enable_cart_saving'] = '1';
	}

	// Properly set the register / login form options based on whether they were enabled previously - #2076
	if ( ! empty( $edd_options['show_register_form'] ) ) {
		$edd_options['show_register_form'] = 'both';
	} else {
		$edd_options['show_register_form'] = 'none';
	}

	// Remove all old, improperly expired sessions. See https://github.com/easydigitaldownloads/Easy-Digital-Downloads/issues/2031
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_wp_session_expires_%' AND option_value+0 < 2789308218" );

	update_option( 'edd_settings', $edd_options );
}

/**
 * Upgrades for EDD v2.0 and sequential payment numbers
 *
 * @since 2.0
 * @return void
 */
function edd_v20_upgrade_sequential_payment_numbers() {

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	edd_set_time_limit();

	$step   = isset( $_GET['step'] )  ? absint( $_GET['step'] )  : 1;
	$total  = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;

	if ( empty( $total ) || $total <= 1 ) {
		$payments = edd_count_payments();
		foreach ( $payments as $status ) {
			$total += $status;
		}
	}

	$orders = edd_get_orders( array(
		'number' => 100,
		'offset' => $step == 1 ? 0 : ( $step - 1 ) * 100,
		'order'  => 'asc',
	) );

	if ( $orders ) {
		$prefix  = edd_get_option( 'sequential_prefix' );
		$postfix = edd_get_option( 'sequential_postfix' );
		$number  = ! empty( $_GET['custom'] ) ? absint( $_GET['custom'] ) : intval( edd_get_option( 'sequential_start', 1 ) );

		foreach ( $orders as $order ) {

			// Re-add the prefix and postfix
			$payment_number = $prefix . $number . $postfix;

			edd_update_order( $order->id, array(
				'order_number' => $payment_number
            ) );

			// Increment the payment number
			$number++;
		}

		// Payments found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'upgrade_sequential_payment_numbers',
			'step'        => urlencode( $step ),
			'custom'      => urlencode( $number ),
			'total'       => urlencode( $total ),
		), admin_url( 'index.php' ) );

		edd_redirect( $redirect );

	// No more payments found, finish up
	} else {
		delete_option( 'edd_upgrade_sequential' );
		delete_option( 'edd_doing_upgrade' );

		edd_redirect( admin_url() );
	}
}
add_action( 'edd_upgrade_sequential_payment_numbers', 'edd_v20_upgrade_sequential_payment_numbers' );

/**
 * Upgrades for EDD v2.1 and the new customers database
 *
 * @since 2.1
 * @return void
 */
function edd_v21_upgrade_customers_db() {
	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	edd_set_time_limit();

	$number = 20;
	$step   = isset( $_GET['step'] )
		? absint( $_GET['step'] )
		: 1;
	$offset = $step == 1
		? 0
		: ( $step - 1 ) * $number;

	$emails = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_edd_payment_user_email' LIMIT %d,%d;", $offset, $number ) );

	if ( $emails ) {

		foreach ( $emails as $email ) {

			if ( EDD()->customers->exists( $email ) ) {
				continue; // Allow the upgrade routine to be safely re-run in the case of failure
			}

			$payments = new EDD_Payments_Query( array(
				'user'    => $email,
				'order'   => 'ASC',
				'orderby' => 'ID',
				'number'  => 9999999,
				'page'    => $step
			) );

			$payments = $payments->get_payments();

			if ( $payments ) {

				$total_value = 0.00;
				$total_count = 0;

				foreach ( $payments as $payment ) {

					if ( 'revoked' == $payment->status || 'complete' == $payment->status ) {
						$total_value += $payment->total;
						$total_count += 1;
					}
				}

				$ids  = wp_list_pluck( $payments, 'ID' );

				$user = get_user_by( 'email', $email );

				$args = array(
					'email'          => $email,
					'user_id'        => $user ? $user->ID : 0,
					'name'           => $user ? $user->display_name : '',
					'purchase_count' => $total_count,
					'purchase_value' => round( $total_value, 2 ),
					'payment_ids'    => implode( ',', array_map( 'absint', $ids ) ),
					'date_created'   => $payments[0]->date
				);

				$customer_id = EDD()->customers->add( $args );

				foreach ( $ids as $id ) {
					update_post_meta( $id, '_edd_payment_customer_id', $customer_id );
				}
			}
		}

		// Customers found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'upgrade_customers_db',
			'step'        => urlencode( $step ),
		), admin_url( 'index.php' ) );

		edd_redirect( $redirect );

	// No more customers found, finish up
	} else {
		edd_update_db_version();
		delete_option( 'edd_doing_upgrade' );

		edd_redirect( admin_url() );
	}
}
add_action( 'edd_upgrade_customers_db', 'edd_v21_upgrade_customers_db' );

/**
 * Fixes the edd_log meta for 2.2.6
 *
 * @since 2.2.6
 * @return void
 */
function edd_v226_upgrade_payments_price_logs_db() {
	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	edd_set_time_limit();

	$number = 25;
	$step   = isset( $_GET['step'] )
		? absint( $_GET['step'] )
		: 1;
	$offset = $step == 1
		? 0
		: ( $step - 1 ) * $number;

	if ( 1 === $step ) {
		// Check if we have any variable price products on the first step
		$sql = "SELECT ID FROM {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} m ON p.ID = m.post_id WHERE m.meta_key = '_variable_pricing' AND m.meta_value = 1 LIMIT 1";
		$has_variable = $wpdb->get_col( $sql );
		if ( empty( $has_variable ) ) {
			// We had no variable priced products, so go ahead and just complete
			edd_update_db_version();
			delete_option( 'edd_doing_upgrade' );
			edd_redirect( admin_url() );
		}
	}

	$payment_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'edd_payment' ORDER BY post_date DESC LIMIT %d,%d;", $offset, $number ) );
	if ( ! empty( $payment_ids ) ) {
		foreach ( $payment_ids as $payment_id ) {
			$payment_downloads  = edd_get_payment_meta_downloads( $payment_id );
			$variable_downloads = array();

			// May not be an array due to some very old payments, move along
			if ( ! is_array( $payment_downloads ) ) {
				continue;
			}

			foreach ( $payment_downloads as $download ) {
				// Don't care if the download is a single price id
				if ( ! isset( $download['options']['price_id'] ) ) {
					continue;
				}
				$variable_downloads[] = array( 'id' => $download['id'], 'price_id' => $download['options']['price_id'] );
			}
			$variable_download_ids = array_unique( wp_list_pluck( $variable_downloads, 'id' ) );
			$unique_download_ids   = implode( ',', $variable_download_ids );

			// If there were no downloads, just fees, move along
			if ( empty( $unique_download_ids ) ) {
				continue;
			}

			// Get all Log Ids where the post parent is in the set of download IDs we found in the cart meta
			$logs = $wpdb->get_results( "SELECT m.post_id AS log_id, p.post_parent AS download_id FROM {$wpdb->postmeta} m LEFT JOIN {$wpdb->posts} p ON m.post_id = p.ID WHERE meta_key = '_edd_log_payment_id' AND meta_value = $payment_id AND p.post_parent IN ($unique_download_ids)", ARRAY_A );
			$mapped_logs = array();

			// Go through each cart item
			foreach ( $variable_downloads as $cart_item ) {
				// Itterate through the logs we found attached to this payment
				foreach ( $logs as $key => $log ) {
					// If this Log ID is associated with this download ID give it the price_id
					if ( (int) $log['download_id'] === (int) $cart_item['id'] ) {
						$mapped_logs[$log['log_id']] = $cart_item['price_id'];
						// Remove this Download/Log ID from the list, for multipurchase compatibility
						unset( $logs[$key] );
						// These aren't the logs we're looking for. Move Along, Move Along.
						break;
					}
				}
			}

			if ( ! empty( $mapped_logs ) ) {
				$update  = "UPDATE {$wpdb->postmeta} SET meta_value = ";
				$case    = "CASE post_id ";
				foreach ( $mapped_logs as $post_id => $value ) {
					$case .= "WHEN {$post_id} THEN {$value} ";
				}
				$case   .= "END ";
				$log_ids = implode( ',', array_keys( $mapped_logs ) );
				$where   = "WHERE post_id IN ({$log_ids}) AND meta_key = '_edd_log_price_id'";
				$sql     = $update . $case . $where;

				// Execute our query to update this payment
				$wpdb->query( $sql );
			}
		}

		// More Payments found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'upgrade_payments_price_logs_db',
			'step'        => urlencode( $step ),
		), admin_url( 'index.php' ) );

		edd_redirect( $redirect );
	} else {
		edd_update_db_version();
		delete_option( 'edd_doing_upgrade' );
		edd_redirect( admin_url() );
	}
}
add_action( 'edd_upgrade_payments_price_logs_db', 'edd_v226_upgrade_payments_price_logs_db' );

/**
 * Upgrades payment taxes for 2.3
 *
 * @since 2.3
 * @return void
 */
function edd_v23_upgrade_payment_taxes() {
	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	edd_set_time_limit();

	$number = 50;
	$step   = isset( $_GET['step'] )
		? absint( $_GET['step'] )
		: 1;
	$offset = $step == 1
		? 0
		: ( $step - 1 ) * $number;

	if ( $step < 2 ) {
		// Check if we have any payments before moving on
		$sql = "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'edd_payment' LIMIT 1";
		$has_payments = $wpdb->get_col( $sql );

		if ( empty( $has_payments ) ) {
			// We had no payments, just complete
			edd_update_db_version();
			edd_set_upgrade_complete( 'upgrade_payment_taxes' );
			delete_option( 'edd_doing_upgrade' );
			edd_redirect( admin_url() );
		}
	}

	$total = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;
	if ( empty( $total ) || $total <= 1 ) {
		$total_sql = "SELECT COUNT(ID) as total_payments FROM {$wpdb->posts} WHERE post_type = 'edd_payment'";
		$results   = $wpdb->get_row( $total_sql, 0 );

		$total     = $results->total_payments;
	}

	$payment_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'edd_payment' ORDER BY post_date DESC LIMIT %d,%d;", $offset, $number ) );

	if ( $payment_ids ) {

		// Add the new _edd_payment_meta item
		foreach ( $payment_ids as $payment_id ) {
			$payment_tax = edd_get_payment_tax( $payment_id );
			edd_update_payment_meta( $payment_id, '_edd_payment_tax', $payment_tax );
		}

		// Payments found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'upgrade_payment_taxes',
			'step'        => urlencode( $step ),
			'number'      => urlencode( $number ),
			'total'       => urlencode( $total ),
		), admin_url( 'index.php' ) );

		edd_redirect( $redirect );

	// No more payments found, finish up
	} else {
		edd_update_db_version();
		edd_set_upgrade_complete( 'upgrade_payment_taxes' );
		delete_option( 'edd_doing_upgrade' );
		edd_redirect( admin_url() );
	}
}
add_action( 'edd_upgrade_payment_taxes', 'edd_v23_upgrade_payment_taxes' );

/**
 * Run the upgrade for the customers to find all payment attachments
 *
 * @since  2.3
 * @return void
 */
function edd_v23_upgrade_customer_purchases() {
	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	edd_set_time_limit();

	$number = 50;
	$step   = isset( $_GET['step'] )
		? absint( $_GET['step'] )
		: 1;
	$offset = $step == 1
		? 0
		: ( $step - 1 ) * $number;

	if ( $step < 2 ) {
		// Check if we have any payments before moving on
		$sql = "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'edd_payment' LIMIT 1";
		$has_payments = $wpdb->get_col( $sql );

		if ( empty( $has_payments ) ) {
			// We had no payments, just complete
			edd_update_db_version();
			edd_set_upgrade_complete( 'upgrade_customer_payments_association' );
			delete_option( 'edd_doing_upgrade' );
			edd_redirect( admin_url() );
		}
	}

	$total = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;

	if ( empty( $total ) || $total <= 1 ) {
		$total = EDD()->customers->count();
	}

	$customers = edd_get_customers( array( 'number' => $number, 'offset' => $offset ) );

	if ( ! empty( $customers ) ) {

		foreach ( $customers as $customer ) {

			// Get payments by email and user ID
			$select = "SELECT ID FROM {$wpdb->posts} p ";
			$join   = "LEFT JOIN {$wpdb->postmeta} m ON p.ID = m.post_id ";
			$where  = "WHERE p.post_type = 'edd_payment' ";

			if ( ! empty( $customer->user_id ) && intval( $customer->user_id ) > 0 ) {
				$where .= "AND ( ( m.meta_key = '_edd_payment_user_email' AND m.meta_value = '{$customer->email}' ) OR ( m.meta_key = '_edd_payment_customer_id' AND m.meta_value = '{$customer->id}' ) OR ( m.meta_key = '_edd_payment_user_id' AND m.meta_value = '{$customer->user_id}' ) )";
			} else {
				$where .= "AND ( ( m.meta_key = '_edd_payment_user_email' AND m.meta_value = '{$customer->email}' ) OR ( m.meta_key = '_edd_payment_customer_id' AND m.meta_value = '{$customer->id}' ) ) ";
			}

			$sql            = $select . $join . $where;
			$found_payments = $wpdb->get_col( $sql );

			$unique_payment_ids  = array_unique( array_filter( $found_payments ) );

			if ( ! empty( $unique_payment_ids ) ) {

				$unique_ids_string  = implode( ',', $unique_payment_ids );
				$customer_data      = array( 'payment_ids' => $unique_ids_string );

				$purchase_value_sql = "SELECT SUM( m.meta_value ) FROM {$wpdb->postmeta} m LEFT JOIN {$wpdb->posts} p ON m.post_id = p.ID WHERE m.post_id IN ( {$unique_ids_string} ) AND p.post_status IN ( 'publish', 'revoked' ) AND m.meta_key = '_edd_payment_total'";
				$purchase_value     = $wpdb->get_col( $purchase_value_sql );

				$purchase_count_sql = "SELECT COUNT( m.post_id ) FROM {$wpdb->postmeta} m LEFT JOIN {$wpdb->posts} p ON m.post_id = p.ID WHERE m.post_id IN ( {$unique_ids_string} ) AND p.post_status IN ( 'publish', 'revoked' ) AND m.meta_key = '_edd_payment_total'";
				$purchase_count     = $wpdb->get_col( $purchase_count_sql );

				if ( ! empty( $purchase_value ) && ! empty( $purchase_count ) ) {

					$purchase_value = $purchase_value[0];
					$purchase_count = $purchase_count[0];

					$customer_data['purchase_count'] = $purchase_count;
					$customer_data['purchase_value'] = $purchase_value;
				}

			} else {
				$customer_data['purchase_count'] = 0;
				$customer_data['purchase_value'] = 0;
				$customer_data['payment_ids']    = '';
			}

			if ( ! empty( $customer_data ) ) {
				$customer = new EDD_Customer( $customer->id );
				$customer->update( $customer_data );
			}
		}

		// More Payments found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'upgrade_customer_payments_association',
			'step'        => urlencode( $step ),
			'number'      => urlencode( $number ),
			'total'       => urlencode( $total ),
		), admin_url( 'index.php' ) );

		edd_redirect( $redirect );

	// No more customers found, finish up
	} else {
		edd_update_db_version();
		edd_set_upgrade_complete( 'upgrade_customer_payments_association' );
		delete_option( 'edd_doing_upgrade' );

		edd_redirect( admin_url() );
	}
}
add_action( 'edd_upgrade_customer_payments_association', 'edd_v23_upgrade_customer_purchases' );

/**
 * Upgrade the User meta API Key storage to swap keys/values for performance
 *
 * @since  2.4
 * @return void
 */
function edd_upgrade_user_api_keys() {
	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	edd_set_time_limit();

	$number = 10;
	$step   = isset( $_GET['step'] )
		? absint( $_GET['step'] )
		: 1;
	$offset = $step == 1
		? 0
		: ( $step - 1 ) * $number;

	if ( $step < 2 ) {
		// Check if we have any users with API Keys before moving on
		$sql     = "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'edd_user_public_key' LIMIT 1";
		$has_key = $wpdb->get_col( $sql );

		// We had no key, just complete
		if ( empty( $has_key ) ) {
			edd_update_db_version();
			edd_set_upgrade_complete( 'upgrade_user_api_keys' );
			delete_option( 'edd_doing_upgrade' );
			edd_redirect( admin_url() );
		}
	}

	$total = isset( $_GET['total'] )
		? absint( $_GET['total'] )
		: false;

	if ( empty( $total ) || $total <= 1 ) {
		$total = $wpdb->get_var( "SELECT count(user_id) FROM $wpdb->usermeta WHERE meta_key = 'edd_user_public_key'" );
	}

	$keys_sql   = $wpdb->prepare( "SELECT user_id, meta_key, meta_value FROM $wpdb->usermeta WHERE meta_key = 'edd_user_public_key' OR meta_key = 'edd_user_secret_key' ORDER BY user_id ASC LIMIT %d,%d;", $offset, $number );
	$found_keys = $wpdb->get_results( $keys_sql );

	if ( ! empty( $found_keys ) ) {
		foreach ( $found_keys as $key ) {
			$user_id    = $key->user_id;
			$meta_key   = $key->meta_key;
			$meta_value = $key->meta_value;

			// Generate a new entry
			update_user_meta( $user_id, $meta_value, $meta_key );

			// Delete the old one
			delete_user_meta( $user_id, $meta_key );
		}

		// More Payments found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'upgrade_user_api_keys',
			'step'        => urlencode( $step ),
			'number'      => urlencode( $number ),
			'total'       => urlencode( $total ) ) );

		edd_redirect( $redirect );

	// No more customers found, finish up
	} else {
		edd_update_db_version();
		edd_set_upgrade_complete( 'upgrade_user_api_keys' );
		delete_option( 'edd_doing_upgrade' );
		edd_redirect( admin_url() );
	}
}
add_action( 'edd_upgrade_user_api_keys', 'edd_upgrade_user_api_keys' );

/**
 * Remove sale logs from refunded orders
 *
 * @since  2.4.3
 * @return void
 */
function edd_remove_refunded_sale_logs() {
	$edd_logs = EDD()->debug_log;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	edd_set_time_limit();

	$step    = isset( $_GET['step']  ) ? absint( $_GET['step']  ) : 1;
	$total   = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : edd_count_payments()->refunded;

	$refunds = edd_get_payments( array(
		'status' => 'refunded',
		'number' => 20,
		'page'   => $step
	) );

	if ( ! empty( $refunds ) ) {

		// Refunded Payments found so process them
		foreach ( $refunds as $refund ) {

			// Remove related sale log entries
			$edd_logs->delete_logs(
				null,
				'sale',
				array(
					array(
						'key'   => '_edd_log_payment_id',
						'value' => $refund->ID
					)
				)
			);
		}

		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'remove_refunded_sale_logs',
			'step'        => urlencode( $step ),
			'total'       => urlencode( $total ),
		), admin_url( 'index.php' ) );

		edd_redirect( $redirect );

	// No more refunded payments found, finish up
	} else {
		edd_update_db_version();
		edd_set_upgrade_complete( 'remove_refunded_sale_logs' );
		delete_option( 'edd_doing_upgrade' );
		edd_redirect( admin_url() );
	}
}
add_action( 'edd_remove_refunded_sale_logs', 'edd_remove_refunded_sale_logs' );

/** 2.9.2 Upgrades ***********************************************************/

/**
 * Output the results of the file-download log data update
 *
 * @since 2.9.2
 */
function edd_upgrade_render_update_file_download_log_data() {
	$migration_complete = edd_has_upgrade_completed( 'update_file_download_log_data' );

	if ( $migration_complete ) : ?>
		<div id="edd-sl-migration-complete" class="notice notice-success">
			<p>
				<?php _e( '<strong>Migration complete:</strong> You have already completed the update to the file download logs.', 'easy-digital-downloads' ); ?>
			</p>
		</div>
		<?php

		delete_option( 'edd_doing_upgrade' );
		return;
	endif; ?>

	<div id="edd-migration-ready" class="notice notice-success" style="display: none;">
		<p><?php _e( '<strong>Upgrades Complete:</strong> You may now safely navigate away from this page.', 'easy-digital-downloads' ); ?></p>
	</div>

	<div id="edd-migration-nav-warn" class="notice notice-warning">
		<p><?php _e( '<strong>Important:</strong> Do not navigate away from this page until all upgrades complete.', 'easy-digital-downloads' ); ?></p>
	</div>

	<style>
		.dashicons.dashicons-yes {
			display: none;
			color: rgb(0, 128, 0);
			vertical-align: middle;
		}
	</style>

	<script>
		jQuery( function($) {
			$(document).ready(function () {
				$(document).on("DOMNodeInserted", function (e) {
					var element = e.target;

					if (element.id === 'edd-batch-success') {
						element = $(element);

						element.parent().prev().find('.edd-migration.allowed').hide();
						element.parent().prev().find('.edd-migration.unavailable').show();

						var element_wrapper   = element.parents().eq(4),
							next_step_wrapper = element_wrapper.next();

						element_wrapper.find('.dashicons.dashicons-yes').show();

						if (next_step_wrapper.find('.postbox').length) {
							next_step_wrapper.find('.edd-migration.allowed').show();
							next_step_wrapper.find('.edd-migration.unavailable').hide();

							if (auto_start_next_step) {
								next_step_wrapper.find('.edd-export-form').submit();
							}
						} else {
							$('#edd-migration-nav-warn').hide();
							$('#edd-migration-ready').slideDown();
						}
					}
				});
			});
		});
	</script>

	<div class="metabox-holder">
		<div class="postbox">
			<h2 class="hndle">
				<span><?php _e( 'Update file download logs', 'easy-digital-downloads' ); ?></span>
				<span class="dashicons dashicons-yes"></span>
			</h2>
			<div class="inside migrate-file-download-logs-control">
				<p>
					<?php _e( 'This will update the file download logs to remove some <abbr title="Personally Identifiable Information">PII</abbr> and make file download counts more accurate.', 'easy-digital-downloads' ); ?>
				</p>
				<form method="post" id="edd-fix-file-download-logs-form" class="edd-export-form edd-import-export-form">
					<span class="step-instructions-wrapper">

						<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>

						<?php if ( ! $migration_complete ) : ?>
							<span class="edd-migration allowed">
								<input type="submit" id="migrate-logs-submit" value="<?php _e( 'Update File Download Logs', 'easy-digital-downloads' ); ?>" class="button-primary"/>
							</span>
						<?php else: ?>
							<input type="submit" disabled id="migrate-logs-submit" value="<?php _e( 'Update File Download Logs', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
							&mdash; <?php _e( 'File download logs have already been updated.', 'easy-digital-downloads' ); ?>
						<?php endif; ?>

						<input type="hidden" name="edd-export-class" value="EDD_File_Download_Log_Migration" />
						<span class="spinner"></span>

					</span>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
	</div>

	<?php
}

/**
 * Register the batch file-download log migration
 *
 * @since 2.9.2
 */
function edd_register_batch_file_download_log_migration() {
	add_action( 'edd_batch_export_class_include', 'edd_include_file_download_log_migration_batch_processor', 10, 1 );
}
add_action( 'edd_register_batch_exporter', 'edd_register_batch_file_download_log_migration', 10 );

/**
 * Include the file-download log batch processor
 *
 * @since 2.9.2
 *
 * @param string $class
 */
function edd_include_file_download_log_migration_batch_processor( $class = '' ) {
	if ( 'EDD_File_Download_Log_Migration' === $class ) {
		require_once EDD_PLUGIN_DIR . 'includes/admin/upgrades/classes/class-file-download-log-migration.php';
	}
}

/** 3.0 Upgrades *************************************************************/

/**
 * Returns an array of upgrades for 3.0
 *
 * Key is the name of the upgrade, which can be used in `edd_has_upgrade_completed()` completed functions.
 * The value is the name of the associated batch processor class for that upgrade.
 *
 * @since 3.0
 * @return array
 */
function edd_get_v30_upgrades() {
	return array(
		'migrate_tax_rates'                => array(
			'name'  => __( 'Tax Rates', 'easy-digital-downloads' ),
			'class' => 'EDD\\Admin\\Upgrades\\v3\\Tax_Rates'
		),
		'migrate_discounts'                => array(
			'name'  => __( 'Discounts', 'easy-digital-downloads' ),
			'class' => 'EDD\\Admin\\Upgrades\\v3\\Discounts'
		),
		'migrate_orders'                   => array(
			'name'  => __( 'Orders', 'easy-digital-downloads' ),
			'class' => 'EDD\\Admin\\Upgrades\\v3\\Orders'
		),
		'migrate_customer_addresses'       => array(
			'name'  => __( 'Customer Addresses', 'easy-digital-downloads' ),
			'class' => 'EDD\\Admin\\Upgrades\\v3\\Customer_Addresses'
		),
		'migrate_customer_email_addresses' => array(
			'name'  => __( 'Customer Email Addresses', 'easy-digital-downloads' ),
			'class' => 'EDD\\Admin\\Upgrades\\v3\\Customer_Email_Addresses'
		),
		'migrate_customer_notes'           => array(
			'name'  => __( 'Customer Notes', 'easy-digital-downloads' ),
			'class' => 'EDD\\Admin\\Upgrades\\v3\\Customer_Notes'
		),
		'migrate_logs'                     => array(
			'name'  => __( 'Logs', 'easy-digital-downloads' ),
			'class' => 'EDD\\Admin\\Upgrades\\v3\\Logs'
		),
		'migrate_order_notes'              => array(
			'name'  => __( 'Order Notes', 'easy-digital-downloads' ),
			'class' => 'EDD\\Admin\\Upgrades\\v3\\Order_Notes'
		),
		'v30_legacy_data_removed'          => array(
			'name'  => __( 'Remove Legacy Data', 'easy-digital-downloads' ),
			'class' => 'EDD\\Admin\\Upgrades\\v3\\Remove_Legacy_Data'
		)
	);
}

/**
 * Render 3.0 upgrade page.
 *
 * @since 3.0
 */
function edd_upgrade_render_v30_migration() {

	$upgrades         = edd_get_v30_upgrades();
	$upgrade_statuses = array_fill_keys( array_keys( $upgrades ), false );
	$number_complete  = 0;

	foreach ( $upgrade_statuses as $upgrade_key => $status ) {
		if ( edd_has_upgrade_completed( $upgrade_key ) ) {
			$upgrade_statuses[ $upgrade_key ] = true;
			$number_complete++;

			continue;
		}

		// Let's see if we have a step in progress.
		$current_step = get_option( 'edd_v3_migration_step_' . $upgrade_key );
		if ( ! empty( $current_step ) ) {
			$upgrade_statuses[ $upgrade_key ] = absint( $current_step );
		}
	}

	$migration_complete = $number_complete === count( $upgrades );

	/*
	 * Determine if legacy data can be removed.
	 * It can be if all upgrades except legacy data have been completed.
	 */
	$can_remove_legacy_data = array_key_exists( 'v30_legacy_data_removed', $upgrade_statuses ) && $upgrade_statuses[ 'v30_legacy_data_removed' ] !== true;
	if ( $can_remove_legacy_data ) {
		foreach( $upgrade_statuses as $upgrade_key => $status ) {
			if ( 'v30_legacy_data_removed' === $upgrade_key ) {
				continue;
			}

			// If there's at least one upgrade still unfinished, we can't remove legacy data.
			if ( true !== $status ) {
				$can_remove_legacy_data = false;
				break;
			}
		}
	}

	if ( $migration_complete ) {
		?>
		<div id="edd-migration-ready" class="notice notice-success">
			<p>
				<?php echo wp_kses( __( '<strong>Database Upgrade Complete:</strong> All database upgrades have been completed.', 'easy-digital-downloads' ), array( 'strong' => array() ) ); ?>
				<br /><br />
				<?php esc_html_e( 'You may now leave this page.', 'easy-digital-downloads' ); ?>
			</p>
		</div>

		<p>
			<a href="<?php echo esc_url( admin_url() ); ?>" class="button button-primary"><?php esc_html_e( 'Return to the dashboard', 'easy-digital-downloads' ); ?></a>
		</p>
		<?php
		return;
	}
	?>
	<div id="edd-migration-nav-warn" class="notice notice-warning">
		<p><?php echo wp_kses( __( '<strong>Important:</strong> Do not navigate away from this page until all upgrades have completed.', 'easy-digital-downloads' ), array( 'strong' => array() ) ); ?></p>
	</div>

	<p>
		<?php esc_html_e( 'Easy Digital Downloads needs to perform upgrades to your WordPress database. Your store data will be migrated to custom database tables to improve performance and efficiency. This process may take a while.', 'easy-digital-downloads' ); ?>
		<strong><?php esc_html_e( 'Please create a full backup of your website before proceeding.', 'easy-digital-downloads' ); ?></strong>
	</p>

	<p>
		<?php printf( esc_html__( 'This migration can also be run via WP-CLI with the following command: %s. This is the recommended method for large sites.', 'easy-digital-downloads' ), '<code>wp edd v30_migration</code>' ); ?>
	</p>

	<?php
	// Only show the migration form if there are still upgrades to do.
	if ( ! $can_remove_legacy_data ) : ?>
	<form id="edd-v3-migration" class="edd-v3-migration" method="POST">
		<p>
			<label for="edd-v3-migration-confirmation">
				<input type="checkbox" id="edd-v3-migration-confirmation" class="edd-v3-migration-confirmation" name="backup_confirmation" value="1">
				<?php esc_html_e( 'I have secured a backup of my website data.', 'easy-digital-downloads' ); ?>
			</label>
		</p>
		<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'edd_process_v3_upgrade' ) ); ?>">
		<button type="submit" id="edd-v3-migration-button" class="button button-primary disabled" disabled>
			<?php esc_html_e( 'Upgrade Easy Digital Downloads', 'easy-digital-downloads' ); ?>
		</button>
		<div class="edd-v3-migration-error edd-hidden"></div>
	</form>
	<?php endif

	/*
	 * Progress is only shown immediately if the upgrade is in progress. Otherwise it's hidden by default
	 * and only revealed via JavaScript after the process has started.
	 */
	?>
	<div id="edd-migration-progress" <?php echo count( array_filter( $upgrade_statuses ) ) ? '' : 'class="edd-hidden"'; ?>>
		<ul>
			<?php foreach ( $upgrades as $upgrade_key => $upgrade_details ) :
				// We skip the one to remove legacy data. We'll handle that separately later.
				if ( 'v30_legacy_data_removed' === $upgrade_key ) {
					continue;
				}
				?>
				<li id="edd-v3-migration-<?php echo esc_attr( sanitize_html_class( $upgrade_key ) ); ?>" <?php echo true === $upgrade_statuses[ $upgrade_key ] ? 'class="edd-upgrade-complete"' : ''; ?> data-upgrade="<?php echo esc_attr( $upgrade_key ); ?>">
					<span class="edd-migration-status">
						<?php
						if ( true === $upgrade_statuses[ $upgrade_key ] ) {
							?>
							<span class="dashicons dashicons-yes"></span>
							<span class="screen-reader-text"><?php esc_html_e( 'Migration complete', 'easy-digital-downloads' ); ?></span>
							<?php
						} else {
							?>
							<span class="dashicons dashicons-minus"></span>
							<span class="screen-reader-text"><?php esc_html_e( 'Migration pending', 'easy-digital-downloads' ); ?></span>
							<?php
						}
						?>
					</span>
					<span class="edd-migration-name">
						<?php echo esc_html( $upgrade_details['name'] ); ?>
					</span>
					<span class="edd-migration-percentage edd-hidden">
						&ndash;
						<span class="edd-migration-percentage-value">0</span>%
					</span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<div id="edd-v3-migration-complete" class="notice inline notice-success<?php echo ! $can_remove_legacy_data ? ' edd-hidden' : ''; ?>">
		<p>
			<?php esc_html_e( 'The data migration has been successfully completed. You may now leave this page or proceed to remove legacy data below.', 'easy-digital-downloads' ); ?>
		</p>
	</div>

	<?php
	/**
	 * Display the form for removing legacy data.
	 */
	edd_v3_remove_legacy_data_form( ! $can_remove_legacy_data );
}

/**
 * Renders the form for removing legacy data.
 *
 * @param bool $hide_wrapper_initially Whether or not to hide the wrapper on initial load.
 *
 * @since 3.0
 */
function edd_v3_remove_legacy_data_form( $hide_wrapper_initially = false ) {
	$is_tools_page = ! empty( $_GET['page'] ) && 'edd-tools' === $_GET['page'];
	$classes       = $hide_wrapper_initially ? array( 'edd-hidden' ) : array();
	?>
	<div id="edd-v3-remove-legacy-data"<?php echo ! empty( $classes ) ? 'class="' . esc_attr( implode( ' ', $classes ) ) . '"' : ''; ?>>
		<?php if ( ! $is_tools_page ) : ?>
			<h2><?php esc_html_e( 'Remove Legacy Data', 'easy-digital-downloads' ); ?></h2>
		<?php endif; ?>
		<p>
			<?php echo wp_kses( __( '<strong>Important:</strong> This removes all legacy data from where it was previously stored in custom post types and post meta. This is an optional step that is <strong><em>not reversible</em></strong>. Please back up your database and ensure your store is operational before completing this step.', 'easy-digital-downloads' ), array( 'strong' => array() ) ); ?>
		</p>
		<?php if ( ! $is_tools_page ) : ?>
		<p>
			<?php
			printf(
				esc_html__( 'You can complete this step later by navigating to %sDownloads &raquo; Tools%s.', 'easy-digital-downloads' ),
				'<a href="' . esc_url( edd_get_admin_url( array( 'page' => 'edd-tools' ) ) ) . '">',
				'</a>'
			);
			?>
		</p>
		<?php endif; ?>
		<form class="edd-v3-migration" method="POST">
			<p>
				<label for="edd-v3-remove-legacy-data-confirmation">
					<input type="checkbox" id="edd-v3-remove-legacy-data-confirmation" class="edd-v3-migration-confirmation" name="backup_confirmation" value="1">
					<?php esc_html_e( 'I have confirmed my store is operational and I have a backup of my website data.', 'easy-digital-downloads' ); ?>
				</label>
			</p>
			<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'edd_process_v3_upgrade' ) ); ?>">
			<input type="hidden" name="upgrade_key" value="v30_legacy_data_removed">

			<div id="edd-v3-migration-remove-legacy-data-submit-wrap">
				<button type="submit" class="button button-primary disabled" disabled>
					<?php esc_html_e( 'Permanently Remove Legacy Data', 'easy-digital-downloads' ); ?>
				</button>
				<span id="edd-v3-migration-v30_legacy_data_removed">
						<span class="edd-migration-percentage edd-hidden">
							<span class="edd-migration-percentage-value">0</span>%
						</span>
					</span>
			</div>
		</form>
		<div id="edd-v3-legacy-data-removal-complete" class="edd-hidden notice inline notice-success">
			<p>
				<?php esc_html_e( 'Legacy data has been successfully removed. You may now leave this page.', 'easy-digital-downloads' ); ?>
			</p>
		</div>
		<div class="edd-v3-migration-error edd-hidden"></div>
	</div>
	<?php
}

/**
 * Adds the Remove Legacy Data form to the Tools page.
 *
 * @since 3.0
 * @return void
 */
function edd_v3_remove_legacy_data_tool() {
	// Tool not available if they've already done it.
	if ( edd_has_upgrade_completed( 'v30_legacy_data_removed' ) ) {
		return;
	}

	$v3_upgrades = edd_get_v30_upgrades();
	unset( $v3_upgrades['v30_legacy_data_removed'] );
	$v3_upgrades = array_keys( $v3_upgrades );

	// If even one upgrade hasn't completed, they cannot delete legacy data.
	foreach( $v3_upgrades as $v3_upgrade ) {
		if ( ! edd_has_upgrade_completed( $v3_upgrade ) ) {
			return;
		}
	}
	?>
	<div class="postbox">
		<h3><span><?php esc_html_e( 'Remove Legacy Data', 'easy-digital-downloads' ); ?></span></h3>

		<div class="inside">
			<?php edd_v3_remove_legacy_data_form(); ?>
		</div>
	</div>
	<?php
}
add_action( 'edd_tools_recount_stats_after', 'edd_v3_remove_legacy_data_tool' );

/**
 * Register batch processors for upgrade routines for EDD 3.0.
 *
 * @since 3.0
 */
function edd_register_batch_processors_for_v30_upgrade() {
	add_action( 'edd_batch_export_class_include', 'edd_load_batch_processors_for_v30_upgrade', 10, 1 );
}
add_action( 'edd_register_batch_exporter', 'edd_register_batch_processors_for_v30_upgrade', 10 );

/**
 * Load the batch processor for upgrade routines for EDD 3.0.
 *
 * @param $class string Class name.
 */
function edd_load_batch_processors_for_v30_upgrade( $class ) {
	switch ( $class ) {
		case 'EDD\Admin\Upgrades\v3\Orders':
			require_once  EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-base.php';
			require_once  EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-data-migrator.php';
			require_once  EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-orders.php';
			break;
		case 'EDD\Admin\Upgrades\v3\Customer_Addresses':
			require_once  EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-base.php';
			require_once  EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-data-migrator.php';
			require_once  EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-customer-addresses.php';
			break;
		case 'EDD\Admin\Upgrades\v3\Customer_Email_Addresses':
			require_once  EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-base.php';
			require_once  EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-data-migrator.php';
			require_once  EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-customer-email-addresses.php';
			break;
		case 'EDD\Admin\Upgrades\v3\Logs':
			require_once  EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-base.php';
			require_once  EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-data-migrator.php';
			require_once  EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-logs.php';
			break;
		case 'EDD\Admin\Upgrades\v3\Tax_Rates':
			require_once  EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-base.php';
			require_once  EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-data-migrator.php';
			require_once  EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-tax-rates.php';
			break;
		case 'EDD\Admin\Upgrades\v3\Discounts':
			require_once  EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-base.php';
			require_once  EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-data-migrator.php';
			require_once  EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-discounts.php';
			break;
		case 'EDD\Admin\Upgrades\v3\Order_Notes':
			require_once  EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-base.php';
			require_once  EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-data-migrator.php';
			require_once  EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-order-notes.php';
			break;
		case 'EDD\Admin\Upgrades\v3\Customer_Notes':
			require_once  EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-base.php';
			require_once  EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-data-migrator.php';
			require_once  EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-customer-notes.php';
			break;
		case 'EDD\Admin\Upgrades\v3\Remove_Legacy_Data':
			require_once  EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-base.php';
			require_once  EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-data-migrator.php';
			require_once  EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/class-remove-legacy-data.php';
			break;
	}
}

/**
 * Checks whether all 3.0 migrations have run, ignoring the legacy data removal.
 *
 * @since 3.0
 * @return bool
 */
function edd_v30_is_migration_complete() {
	$upgrades = edd_get_v30_upgrades();
	unset( $upgrades['v30_legacy_data_removed'] );
	$upgrades = array_keys( $upgrades );
	foreach ( $upgrades as $upgrade ) {
		// If any migration has not completed, return false.
		if ( ! edd_has_upgrade_completed( $upgrade ) ) {
			return false;
		}
	}
	// If the migration is complete, delete the pending option.
	delete_option( 'edd_v3_migration_pending' );

	// Delete the CLI option as well.
	delete_option( 'edd_v30_cli_migration_running' );

	return true;
}
