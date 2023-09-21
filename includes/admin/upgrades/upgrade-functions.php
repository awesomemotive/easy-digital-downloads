<?php
/**
 * Upgrade Functions
 * Note: Do not move, rename, or delete this file as many extensions will attempt
 * to require it during an installation or upgrade process.
 *
 * @package     EDD
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.1
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// edd_do_automatic upgrades is defined in includes/upgrades/functions.php
add_action( 'admin_init', 'edd_do_automatic_upgrades' );

/**
 * Display Upgrade Notices
 *
 * @since 1.3.1
 * @return void
 */
function edd_show_upgrade_notices() {
	// Don't show notices on the upgrades page
	$page = wp_strip_all_tags( filter_input( INPUT_GET, 'page' ) );
	if ( ! empty( $page ) && ( 'edd-upgrades' === $page ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( 'site-health' === $screen->id ) {
		return;
	}

	// Sequential Orders was the first stepped upgrade, so check if we have a stalled upgrade
	$resume_upgrade = edd_maybe_resume_upgrade();
	if ( ! empty( $resume_upgrade ) ) {
		EDD()->notices->add_notice(
			array(
				'id'             => 'edd-resume-upgrade',
				'class'          => 'error',
				'message'        => sprintf(
					/* translators: %s: Resume upgrade link */
					__( 'Easy Digital Downloads needs to complete a database upgrade that was previously started, click <a href="%s">here</a> to resume the upgrade.', 'easy-digital-downloads' ),
					esc_url( add_query_arg( $resume_upgrade, admin_url( 'index.php' ) ) )
				),
				'is_dismissible' => false,
			)
		);
	} else {

		// Include all 'Stepped' upgrade process notices in this else statement,
		// to avoid having a pending, and new upgrade suggested at the same time

		if ( get_option( 'edd_upgrade_sequential' ) ) {
			delete_option( 'edd_upgrade_sequential' );
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
				if ( ! empty( $table ) && $table->exists() ) {
					$version = $table->get_version();
				}
			}

			// The migration needs to be run, and the database table exists.
			if ( $needs_migration && $version ) {
				?>
				<div class="updated">
					<?php if ( get_option( 'edd_v30_cli_migration_running' ) ) { ?>
						<p><?php esc_html_e( 'Easy Digital Downloads is performing a database migration via WP-CLI. Sales and earnings data for your store will be updated when all orders have been migrated. This message will be removed when the migration is complete.', 'easy-digital-downloads' ); ?></p>
						<?php
					} else {
						?>
						<p>
							<?php
							printf(
								wp_kses_post(
									/* translators: 1. Opening strong tag; do not translate. 2. Closing strong tag; do not translate. */
									__( 'Easy Digital Downloads needs to upgrade the database. %1$sLearn more about this upgrade%2$s.', 'easy-digital-downloads' )
								),
								'<button class="button button-link" onClick="jQuery(this).parent().next(\'div\').slideToggle()">',
								'</button>'
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
									/* translators: 1. Opening strong tag; do not translate. 2. Closing strong tag; do not translate. 3. Plural download label */
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

				global $wpdb;
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
		 * into the upgrades array in `edd_get_all_upgrades`.
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

	if ( ! edd_doing_ajax() ) {
		return;
	}

	// Bail if user is not capable
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		delete_option( 'edd_doing_upgrade' );
		die( 'complete' );
	}

	// Bail if nonce is not set
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'edd-upgrade' ) ) {
		delete_option( 'edd_doing_upgrade' );
		die( 'complete' );
	}

	delete_option( 'edd_doing_upgrade' );
	die( 'complete' );
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

/** 3.0 Upgrades *************************************************************/

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
		<?php
			printf(
				/* translators: %s: Plural label for downloads */
				esc_html__( 'Sales and earnings data for %s and customers will be updated once orders have finished migrating.', 'easy-digital-downloads' ),
				esc_html( edd_get_label_plural( true ) )
			);
		?>
		<strong><?php esc_html_e( 'Please create a full backup of your website before proceeding.', 'easy-digital-downloads' ); ?></strong>
	</p>

	<p>
		<?php
		/* translators: %s: WP-CLI command */
		printf( esc_html__( 'This migration can also be run via WP-CLI with the following command: %s. This is the recommended method for large sites.', 'easy-digital-downloads' ), '<code>wp edd v30_migration</code>' );
		?>
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
 * This function also clears out options used to indicate that the upgrade is in progress.
 *
 * @since 3.0
 * @return bool
 */
function edd_v30_is_migration_complete() {
	if ( ! EDD\Upgrades\Utilities\MigrationCheck::is_v30_migration_complete() ) {
		return false;
	}

	// If the migration is complete, delete the pending option.
	delete_option( 'edd_v3_migration_pending' );

	// Delete the CLI option as well.
	delete_option( 'edd_v30_cli_migration_running' );

	return true;
}
