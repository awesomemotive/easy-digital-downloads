<?php
/**
 * Tools
 *
 * These are functions used for displaying EDD tools such as the import/export system.
 *
 * @package     EDD
 * @subpackage  Admin/Tools
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Shows the tools panel which contains EDD-specific tools including the built-in import/export system.
 *
 * @since 1.8
 * @author Daniel J Griffiths
 */
function edd_tools_page() {

	// Get tabs and active tab
	$tabs       = edd_get_tools_tabs();
	$active_tab = isset( $_GET['tab'] )
		? sanitize_key( $_GET['tab'] )
		: 'general';

	wp_enqueue_script( 'edd-admin-tools' );

	if ( 'import_export' === $active_tab ) {
		wp_enqueue_script( 'edd-admin-tools-import' );
		wp_enqueue_script( 'edd-admin-tools-export' );
	}
?>

	<div class="wrap">
		<h1><?php esc_html_e( 'Tools', 'easy-digital-downloads' ); ?></h1>
		<hr class="wp-header-end">

		<nav class="nav-tab-wrapper edd-nav-tab-wrapper" aria-label="<?php esc_attr_e( 'Secondary menu', 'easy-digital-downloads' ); ?>">
		<?php

		foreach ( $tabs as $tab_id => $tab_name ) {

			$tab_url = edd_get_admin_url(
				array(
					'page' => 'edd-tools',
					'tab'  => sanitize_key( $tab_id ),
				)
			);

			$tab_url = remove_query_arg(
				array(
					'edd-message',
				),
				$tab_url
			);

			// System Info is now found in Site Health.
			if ( 'system_info' === $tab_id ) {
				$tab_url = add_query_arg(
					array(
						'tab' => 'debug',
						'edd' => 'filter',
					),
					admin_url( 'site-health.php' )
				);
			}

			$active = ( $active_tab === $tab_id )
				? ' nav-tab-active'
				: '';

			echo '<a href="' . esc_url( $tab_url ) . '" class="nav-tab' . esc_attr( $active ) . '">' . esc_html( $tab_name ) . '</a>';
		}

		?>
		</nav>

		<div class="metabox-holder">
			<?php
			do_action( 'edd_tools_tab_' . esc_attr( $active_tab ) );
			?>
		</div><!-- .metabox-holder -->
	</div><!-- .wrap -->

	<?php
}

/**
 * Retrieve tools tabs.
 *
 * @since 2.0
 *
 * @return array Tabs for the 'Tools' page.
 */
function edd_get_tools_tabs() {
	static $tabs = array();

	// Set tabs if empty
	if ( empty( $tabs ) ) {

		// Define all tabs
		$tabs = array(
			'general'       => __( 'General',       'easy-digital-downloads' ),
			'api_keys'      => __( 'API Keys',      'easy-digital-downloads' ),
			'betas'         => __( 'Beta Versions', 'easy-digital-downloads' ),
			'logs'          => __( 'Logs',          'easy-digital-downloads' ),
			'system_info'   => __( 'System Info',   'easy-digital-downloads' ),
			'debug_log'     => __( 'Debug Log',     'easy-digital-downloads' ),
			'import_export' => __( 'Import/Export', 'easy-digital-downloads' )
		);

		// Unset the betas tab if not allowed
		if ( count( edd_get_beta_enabled_extensions() ) <= 0 ) {
			unset( $tabs['betas'] );
		}
	}

	// Filter & return
	return apply_filters( 'edd_tools_tabs', $tabs );
}

/**
 * Display the recount stats.
 *
 * @since 2.5
 */
function edd_tools_recount_stats_display() {

	// Bail if the user does not have the required capabilities.
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	do_action( 'edd_tools_recount_stats_before' );
	?>

	<div class="postbox">
		<h3><span><?php esc_html_e( 'Recount Stats', 'easy-digital-downloads' ); ?></span></h3>
		<div class="inside recount-stats-controls">
			<p><?php esc_html_e( 'Use these tools to recount / reset store stats.', 'easy-digital-downloads' ); ?></p>
			<form method="post" id="edd-tools-recount-form" class="edd-export-form edd-import-export-form">
				<span>
					<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>

					<select name="edd-export-class" id="recount-stats-type">
						<option value="0" selected="selected"
								disabled="disabled"><?php esc_html_e( 'Please select an option', 'easy-digital-downloads' ); ?></option>
						<option data-type="recount-store"
								value="EDD_Tools_Recount_Store_Earnings"><?php esc_html_e( 'Recount Store Earnings and Sales', 'easy-digital-downloads' ); ?></option>
						<option data-type="recount-download"
								value="EDD_Tools_Recount_Download_Stats">
								<?php
								/* translators: %s: Singular download label, lowercase */
								printf( esc_html__( 'Recount Earnings and Sales for a %s', 'easy-digital-downloads' ), esc_html( edd_get_label_singular( true ) ) );
								?>
						</option>
						<option data-type="recount-all"
								value="EDD_Tools_Recount_All_Stats">
								<?php
								/* translators: %s: Plural download label, lowercase */
								printf( esc_html__( 'Recount Earnings and Sales for All %s', 'easy-digital-downloads' ), esc_html( edd_get_label_plural( true ) ) );
								?>
						</option>
						<option data-type="recount-customer-stats"
								value="EDD_Tools_Recount_Customer_Stats"><?php esc_html_e( 'Recount Customer Stats', 'easy-digital-downloads' ); ?></option>
						<?php do_action( 'edd_recount_tool_options' ); ?>
						<option data-type="reset-stats"
								value="EDD_Tools_Reset_Stats"><?php esc_html_e( 'Reset Store', 'easy-digital-downloads' ); ?></option>
					</select>

					<span id="tools-product-dropdown" style="display: none">
						<?php
						$args = array(
							'name'   => 'download_id',
							'chosen' => true,
						);
						echo EDD()->html->product_dropdown( $args );
						?>
					</span>

					<button type="submit" id="recount-stats-submit" class="button button-secondary">
						<?php esc_html_e( 'Submit', 'easy-digital-downloads' ); ?>
					</button>

					<br/>

					<span class="edd-recount-stats-descriptions">
						<span id="recount-store"><?php _e( 'Recalculates the total store earnings and sales.', 'easy-digital-downloads' ); ?></span>
						<span id="recount-download">
							<?php
							/* translators: %s: Singular wownload label, lowercase */
							printf( esc_html__( 'Recalculates the earnings and sales stats for a specific %s.', 'easy-digital-downloads' ), esc_html( edd_get_label_singular( true ) ) );
							?>
						</span>
						<span id="recount-all">
							<?php
							/* translators: %s: Pural download label, lowercase */
							printf( esc_html__( 'Recalculates the earnings and sales stats for all %s.', 'easy-digital-downloads' ), esc_html( edd_get_label_plural( true ) ) );
							?>
						</span>
						<span id="recount-customer-stats"><?php esc_html_e( 'Recalculates the lifetime value and purchase counts for all customers.', 'easy-digital-downloads' ); ?></span>
						<?php do_action( 'edd_recount_tool_descriptions' ); ?>
						<span id="reset-stats"><?php echo wp_kses_post( __( '<strong>Deletes</strong> all payment records, customers, and related log entries.', 'easy-digital-downloads' ) ); ?></span>
					</span>

					<span class="spinner"></span>

				</span>
			</form>
			<?php do_action( 'edd_tools_recount_forms' ); ?>
		</div><!-- .inside -->
	</div><!-- .postbox -->

	<?php
	do_action( 'edd_tools_recount_stats_after' );
}

add_action( 'edd_tools_tab_general', 'edd_tools_recount_stats_display' );

/**
 * Display the clear upgrades tab.
 *
 * @since 2.3.5
 */
function edd_tools_clear_doing_upgrade_display() {
	if ( ! current_user_can( 'manage_shop_settings' ) || false === get_option( 'edd_doing_upgrade' ) ) {
		return;
	}

	do_action( 'edd_tools_clear_doing_upgrade_before' );
	?>
	<div class="postbox">
		<h3><span><?php esc_html_e( 'Clear Incomplete Upgrade Notice', 'easy-digital-downloads' ); ?></span></h3>
		<div class="inside">
			<p><?php esc_html_e( 'Sometimes a database upgrade notice may not be cleared after an upgrade is completed due to conflicts with other extensions or other minor issues.', 'easy-digital-downloads' ); ?></p>
			<p><?php esc_html_e( 'If you\'re certain these upgrades have been completed, you can clear these upgrade notices by clicking the button below. If you have any questions about this, please contact the Easy Digital Downloads support team and we\'ll be happy to help.', 'easy-digital-downloads' ); ?></p>
			<form method="post"
				action="<?php echo esc_url( edd_get_admin_url( array( 'page' => 'edd-tools', 'tab' => 'general' ) ) ); ?>">
				<p>
					<input type="hidden" name="edd_action" value="clear_doing_upgrade"/>
					<?php wp_nonce_field( 'edd_clear_upgrades_nonce', 'edd_clear_upgrades_nonce' ); ?>
					<?php submit_button( __( 'Clear Incomplete Upgrade Notice', 'easy-digital-downloads' ), 'secondary', 'submit', false ); ?>
				</p>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->
	<?php
	do_action( 'edd_tools_clear_doing_upgrade_after' );
}

add_action( 'edd_tools_tab_general', 'edd_tools_clear_doing_upgrade_display' );

/**
 * Display the API Keys
 *
 * @since 2.0
 */
function edd_tools_api_keys_display() {
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	do_action( 'edd_tools_api_keys_before' );

	require_once EDD_PLUGIN_DIR . 'includes/admin/class-api-keys-table.php';

	$api_keys_table = new EDD_API_Keys_Table();
	$api_keys_table->prepare_items();
	$api_keys_table->display();
	$docs_link = edd_link_helper(
		'https://easydigitaldownloads.com/categories/docs/api-reference/',
		array(
			'utm_medium'  => 'tools',
			'utm_content' => 'api-documentation',
		)
	);

	$ios_link = edd_link_helper(
		'https://easydigitaldownloads.com/downloads/ios-sales-earnings-tracker/',
		array(
			'utm_medium'  => 'tools',
			'utm_content' => 'ios-app',
		)
	);
	?>
    <p>
		<?php
		printf(
			wp_kses_post(
				/* translators: %1$s: API documentation linktag , %2$s: iOS app link tag, %3$s: closing link tag */
				__( 'These API keys allow you to use the %1$sEDD REST API%3$s to retrieve store data in JSON or XML for external applications or devices, such as the %2$sEDD mobile app%3$s.', 'easy-digital-downloads' )
			),
			'<a href="' . esc_url( $docs_link ) . '">',
			'<a href="' . esc_url( $ios_link ) . '">',
			'</a>'
		);
		?>
	</p>
	<?php

	do_action( 'edd_tools_api_keys_after' );
}

add_action( 'edd_tools_tab_api_keys', 'edd_tools_api_keys_display' );


/**
 * Display beta opt-ins
 *
 * @since 2.6.11
 */
function edd_tools_betas_display() {
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	$has_beta = edd_get_beta_enabled_extensions();

	do_action( 'edd_tools_betas_before' );
	?>

	<div class="postbox edd-beta-support">
		<h3><span><?php esc_html_e( 'Enable Beta Versions', 'easy-digital-downloads' ); ?></span></h3>
		<div class="inside">
			<p><?php esc_html_e( 'Checking any of the below checkboxes will opt you in to receive pre-release update notifications. You can opt-out at any time. Pre-release updates do not install automatically, you will still have the opportunity to ignore update notifications.', 'easy-digital-downloads' ); ?></p>
			<form method="post"
				action="<?php echo esc_url( edd_get_admin_url( array( 'page' => 'edd-tools', 'tab' => 'betas' ) ) ); ?>">
				<table class="form-table edd-beta-support">
					<tbody>
					<?php foreach ( $has_beta as $slug => $product ) : ?>
						<tr>
							<?php $checked = edd_extension_has_beta_support( $slug ); ?>
							<th scope="row"><?php echo esc_html( $product ); ?></th>
							<td>
								<input type="checkbox" name="enabled_betas[<?php echo esc_attr( $slug ); ?>]"
										id="enabled_betas[<?php echo esc_attr( $slug ); ?>]"<?php echo checked( $checked, true, false ); ?>
										value="1"/>
								<label for="enabled_betas[<?php echo esc_attr( $slug ); ?>]">
									<?php
									/* translators: %s: Product name */
									printf( esc_html__( 'Get updates for pre-release versions of %s', 'easy-digital-downloads' ), esc_html( $product ) );
									?>
								</label>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<input type="hidden" name="edd_action" value="save_enabled_betas"/>
				<?php wp_nonce_field( 'edd_save_betas_nonce', 'edd_save_betas_nonce' ); ?>
				<?php submit_button( __( 'Save', 'easy-digital-downloads' ), 'secondary', 'submit', false ); ?>
			</form>
		</div>
	</div>

	<?php
	do_action( 'edd_tools_betas_after' );
}

add_action( 'edd_tools_tab_betas', 'edd_tools_betas_display' );

/**
 * Return an array of all extensions with beta support.
 *
 * Extensions should be added as 'extension-slug' => 'Extension Name'
 *
 * @since 2.6.11
 *
 * @return array $extensions The array of extensions
 */
function edd_get_beta_enabled_extensions() {
	return (array) apply_filters( 'edd_beta_enabled_extensions', array() );
}

/**
 * Check if a given extensions has beta support enabled
 *
 * @since 2.6.11
 *
 * @param string $slug The slug of the extension to check
 *
 * @return bool True if enabled, false otherwise
 */
function edd_extension_has_beta_support( $slug ) {
	$enabled_betas = edd_get_option( 'enabled_betas', array() );
	$return        = false;

	if ( array_key_exists( $slug, $enabled_betas ) ) {
		$return = true;
	}

	return $return;
}

/**
 * Save enabled betas.
 *
 * @since 2.6.11
 */
function edd_tools_enabled_betas_save() {
	if ( ! wp_verify_nonce( $_POST['edd_save_betas_nonce'], 'edd_save_betas_nonce' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	if ( ! empty( $_POST['enabled_betas'] ) ) {
		$enabled_betas = array_filter( array_map( 'edd_tools_enabled_betas_sanitize_value', $_POST['enabled_betas'] ) );
		edd_update_option( 'enabled_betas', $enabled_betas );
	} else {
		edd_delete_option( 'enabled_betas' );
	}
}

add_action( 'edd_save_enabled_betas', 'edd_tools_enabled_betas_save' );

/**
 * Sanitize the supported beta values by making them booleans
 *
 * @since 2.6.11
 *
 * @param mixed $value The value being sent in, determining if beta support is enabled.
 *
 * @return bool
 */
function edd_tools_enabled_betas_sanitize_value( $value ) {
	return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
}

/**
 * Save banned emails.
 *
 * @since 2.0
 */
function edd_tools_banned_emails_save() {
	if ( ! wp_verify_nonce( $_POST['edd_banned_emails_nonce'], 'edd_banned_emails_nonce' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	if ( ! empty( $_POST['banned_emails'] ) ) {
		// Sanitize the input
		$emails = array_map( 'trim', explode( "\n", $_POST['banned_emails'] ) );
		$emails = array_unique( $emails );
		$emails = array_map( 'sanitize_text_field', $emails );

		foreach ( $emails as $id => $email ) {
			if ( ! is_email( $email ) && $email[0] != '@' && $email[0] != '.' ) {
				unset( $emails[ $id ] );
			}
		}
	} else {
		$emails = '';
	}

	edd_update_option( 'banned_emails', $emails );
}
add_action( 'edd_save_banned_emails', 'edd_tools_banned_emails_save' );

/**
 * Execute upgrade notice clear.
 *
 * @since 2.3.5
 */
function edd_tools_clear_upgrade_notice() {
	if ( ! wp_verify_nonce( $_POST['edd_clear_upgrades_nonce'], 'edd_clear_upgrades_nonce' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	delete_option( 'edd_doing_upgrade' );
}
add_action( 'edd_clear_doing_upgrade', 'edd_tools_clear_upgrade_notice' );

/**
 * Display the tools import/export tab.
 *
 * @since 2.0
 */
function edd_tools_import_export_display() {
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	do_action( 'edd_tools_import_export_before' );
	?>

	<div class="postbox edd-import-payment-history">
		<h3><span><?php esc_html_e( 'Import Orders', 'easy-digital-downloads' ); ?></span></h3>
		<div class="inside">
			<p><?php esc_html_e( 'Import a CSV file of orders.', 'easy-digital-downloads' ); ?></p>
			<form id="edd-import-payments" class="edd-import-form edd-import-export-form"
					action="<?php echo esc_url( add_query_arg( 'edd_action', 'upload_import_file', admin_url() ) ); ?>"
					method="post" enctype="multipart/form-data">

				<div class="edd-import-file-wrap">
					<?php wp_nonce_field( 'edd_ajax_import', 'edd_ajax_import' ); ?>
					<input type="hidden" name="edd-import-class" value="EDD_Batch_Payments_Import"/>
					<p>
						<input name="edd-import-file" id="edd-payments-import-file" type="file" accept=".csv" required/>
					</p>
					<span>
						<input type="submit" value="<?php esc_html_e( 'Import CSV', 'easy-digital-downloads' ); ?>"
								class="button-secondary"/>
						<span class="spinner"></span>
					</span>
				</div>

				<div class="edd-import-options" id="edd-import-payments-options" style="display:none;">

					<p>
						<?php
						printf(
							wp_kses_post(
								/* translators: %1$s opening anchor tag, %2$s closing anchor tag */
								__( 'Each column loaded from the CSV needs to be mapped to an order field. Select the column that should be mapped to each field below. Any columns not needed can be ignored. See %1$sthis guide%2$s for assistance with importing payment records.', 'easy-digital-downloads' )
							),
							'<a href="https://easydigitaldownloads.com/docs/importing-exporting-orders/" target="_blank">',
							'</a>'
						);
						?>
					</p>

					<table class="widefat edd_repeatable_table striped" width="100%" cellpadding="0" cellspacing="0">
						<thead>
						<tr>
							<th><strong><?php _e( 'Payment Field', 'easy-digital-downloads' ); ?></strong></th>
							<th><strong><?php _e( 'CSV Column', 'easy-digital-downloads' ); ?></strong></th>
							<th><strong><?php _e( 'Data Preview', 'easy-digital-downloads' ); ?></strong></th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td><?php _e( 'Currency Code', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[currency]" class="edd-import-csv-column"
										data-field="Currency">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Email', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[email]" class="edd-import-csv-column" data-field="Email">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Name', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[name]" class="edd-import-csv-column"
										data-field="Name">
									<option value=""><?php esc_html_e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php esc_html_e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'First Name', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[first_name]" class="edd-import-csv-column"
										data-field="First Name">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Last Name', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[last_name]" class="edd-import-csv-column"
										data-field="Last Name">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Customer ID', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[customer_id]" class="edd-import-csv-column"
										data-field="Customer ID">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Discount Code(s)', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[discounts]" class="edd-import-csv-column"
										data-field="Discount Code">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'IP Address', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[ip]" class="edd-import-csv-column"
										data-field="IP Address">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Mode (Live|Test)', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[mode]" class="edd-import-csv-column"
										data-field="Mode (Live|Test)">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Parent Payment ID', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[parent_payment_id]" class="edd-import-csv-column"
										data-field="">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Payment Method', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[gateway]" class="edd-import-csv-column"
										data-field="Payment Method">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Payment Number', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[number]" class="edd-import-csv-column"
										data-field="Payment Number">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Date', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[date]" class="edd-import-csv-column" data-field="Date">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Purchase Key', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[key]" class="edd-import-csv-column"
										data-field="Purchase Key">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Purchased Product(s)', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[downloads]" class="edd-import-csv-column"
										data-field="Products (Raw)">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Status', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[status]" class="edd-import-csv-column"
										data-field="Status">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Subtotal', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[subtotal]" class="edd-import-csv-column" data-field="">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Tax', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[tax]" class="edd-import-csv-column" data-field="Tax ($)">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Total', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[total]" class="edd-import-csv-column"
										data-field="Amount ($)">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Transaction ID', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[transaction_id]" class="edd-import-csv-column"
										data-field="Transaction ID">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'User', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[user_id]" class="edd-import-csv-column"
										data-field="User">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Address Line 1', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[line1]" class="edd-import-csv-column"
										data-field="Address">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Address Line 2', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[line2]" class="edd-import-csv-column"
										data-field="Address (Line 2)">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'City', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[city]" class="edd-import-csv-column" data-field="City">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'State / Province', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[state]" class="edd-import-csv-column" data-field="State">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Zip / Postal Code', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[zip]" class="edd-import-csv-column"
										data-field="Zip / Postal Code">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Country', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[country]" class="edd-import-csv-column"
										data-field="Country">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						</tbody>
					</table>
					<p class="submit">
						<button class="button edd-import-proceed button-primary"><?php esc_html_e( 'Process Import', 'easy-digital-downloads' ); ?></button>
					</p>
				</div>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->

	<div class="postbox edd-import-payment-history">
		<h3><span><?php _e( 'Import Download Products', 'easy-digital-downloads' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Import a CSV file of products.', 'easy-digital-downloads' ); ?></p>
			<form id="edd-import-downloads" class="edd-import-form edd-import-export-form"
					action="<?php echo esc_url( add_query_arg( 'edd_action', 'upload_import_file', admin_url() ) ); ?>"
					method="post" enctype="multipart/form-data">

				<div class="edd-import-file-wrap">
					<?php wp_nonce_field( 'edd_ajax_import', 'edd_ajax_import' ); ?>
					<input type="hidden" name="edd-import-class" value="EDD_Batch_Downloads_Import"/>
					<p>
						<input name="edd-import-file" id="edd-downloads-import-file" type="file" accept=".csv" required/>
					</p>
					<span>
						<input type="submit" value="<?php _e( 'Import CSV', 'easy-digital-downloads' ); ?>"
								class="button-secondary"/>
						<span class="spinner"></span>
					</span>
				</div>

				<div class="edd-import-options" id="edd-import-downloads-options" style="display:none;">

					<p>
						<?php
						printf(
							wp_kses_post(
								/* translators: %1$s and %2$s opening and closing anchor tags respectively */
								__( 'Each column loaded from the CSV needs to be mapped to a Download product field. Select the column that should be mapped to each field below. Any columns not needed can be ignored. See %1$sthis guide%2$s for assistance with importing Download products.', 'easy-digital-downloads' )
							),
							'<a href="https://easydigitaldownloads.com/docs/importing-exporting-products/" target="_blank">',
							'</a>'
						);
						?>
					</p>

					<table class="widefat edd_repeatable_table striped" width="100%" cellpadding="0" cellspacing="0">
						<thead>
						<tr>
							<th><strong><?php _e( 'Product Field', 'easy-digital-downloads' ); ?></strong></th>
							<th><strong><?php _e( 'CSV Column', 'easy-digital-downloads' ); ?></strong></th>
							<th><strong><?php _e( 'Data Preview', 'easy-digital-downloads' ); ?></strong></th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td><?php _e( 'Product Author', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[post_author]" class="edd-import-csv-column"
										data-field="Author">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Product Categories', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[categories]" class="edd-import-csv-column"
										data-field="Categories">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Product Creation Date', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[post_date]" class="edd-import-csv-column"
										data-field="Date Created">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Product Description', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[post_content]" class="edd-import-csv-column"
										data-field="Description">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Product Excerpt', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[post_excerpt]" class="edd-import-csv-column"
										data-field="Excerpt">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Product Image', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[featured_image]" class="edd-import-csv-column"
										data-field="Featured Image">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Product Notes', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[notes]" class="edd-import-csv-column" data-field="Notes">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Product Price(s)', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[price]" class="edd-import-csv-column" data-field="Price">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Product SKU', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[sku]" class="edd-import-csv-column" data-field="SKU">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Product Slug', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[post_name]" class="edd-import-csv-column"
										data-field="Slug">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Product Status', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[post_status]" class="edd-import-csv-column"
										data-field="Status">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Product Tags', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[tags]" class="edd-import-csv-column" data-field="Tags">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Product Title', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[post_title]" class="edd-import-csv-column"
										data-field="Name">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Download Files', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[files]" class="edd-import-csv-column" data-field="Files">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'File Download Limit', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[download_limit]" class="edd-import-csv-column"
										data-field="File Download Limit">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Sale Count', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[sales]" class="edd-import-csv-column" data-field="Sales">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Total Earnings', 'easy-digital-downloads' ); ?></td>
							<td>
								<select name="edd-import-field[earnings]" class="edd-import-csv-column"
										data-field="Earnings">
									<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
							<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
						</tr>
						</tbody>
					</table>
					<p class="submit">
						<button class="button edd-import-proceed button-primary"><?php esc_html_e( 'Process Import', 'easy-digital-downloads' ); ?></button>
					</p>
				</div>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->

	<div class="postbox">
		<h3><span><?php _e( 'Export Settings', 'easy-digital-downloads' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Export the Easy Digital Downloads settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'easy-digital-downloads' ); ?></p>
			<p>
				<?php
				printf(
					wp_kses_post(
						/* translators: %s: Reports page URL */
						__( 'To export shop data (purchases, customers, etc), visit the <a href="%s">Reports</a> page.', 'easy-digital-downloads' )
					),
					esc_url(
						edd_get_admin_url(
							array(
							'page' => 'edd-reports',
							'view' => 'export',
							)
						)
					)
				);
				?>
			</p>
			<form method="post"
				action="<?php echo esc_url( edd_get_admin_url( array( 'page' => 'edd-tools', 'tab' => 'import_export' ) ) ); ?>">
				<p><input type="hidden" name="edd_action" value="export_settings"/></p>
				<p>
					<?php wp_nonce_field( 'edd_export_nonce', 'edd_export_nonce' ); ?>
					<?php submit_button( __( 'Export', 'easy-digital-downloads' ), 'secondary', 'submit', false ); ?>
				</p>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->

	<div class="postbox">
		<h3><span><?php esc_html_e( 'Import Settings', 'easy-digital-downloads' ); ?></span></h3>
		<div class="inside">
			<p><?php esc_html_e( 'Import the Easy Digital Downloads settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.', 'easy-digital-downloads' ); ?></p>
			<form method="post" enctype="multipart/form-data"
				action="<?php echo esc_url( edd_get_admin_url( array( 'page' => 'edd-tools', 'tab' => 'import_export' ) ) ); ?>">
				<p>
					<input type="file" name="import_file" accept=".json" required/>
				</p>
				<p>
					<input type="hidden" name="edd_action" value="import_settings"/>
					<?php wp_nonce_field( 'edd_import_nonce', 'edd_import_nonce' ); ?>
					<?php submit_button( __( 'Import', 'easy-digital-downloads' ), 'secondary', 'submit', false ); ?>
				</p>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->
	<?php
	do_action( 'edd_tools_import_export_after' );
}
add_action( 'edd_tools_tab_import_export', 'edd_tools_import_export_display' );

/**
 * Process a settings export that generates a .json file of the shop settings
 *
 * @since 1.7
 */
function edd_tools_import_export_process_export() {

	// Bail if no nonce
	if ( empty( $_POST['edd_export_nonce'] ) ) {
		return;
	}

	// Bail if nonce does not verify
	if ( ! wp_verify_nonce( $_POST['edd_export_nonce'], 'edd_export_nonce' ) ) {
		return;
	}

	// Bail if user cannot manage shop
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	/**
	 * Filter the settings export filename
	 *
	 * @since 1.7
	 *
	 * @param string $filename The file name to export settings to
	 */
	$filename      = apply_filters( 'edd_settings_export_filename', 'edd-settings-export-' . date( 'm-d-Y' ) ) . '.json';
	$edd_settings  = get_option( 'edd_settings' );
	$edd_tax_rates = edd_get_tax_rates();

	edd_set_time_limit();

	nocache_headers();

	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header( 'Expires: 0' );

	wp_send_json( array(
		'edd_settings'  => $edd_settings,
		'edd_tax_rates' => $edd_tax_rates
	) );
}
add_action( 'edd_export_settings', 'edd_tools_import_export_process_export' );

/**
 * Process a settings import from a json file
 *
 * @since 1.7
 * @return void
 */
function edd_tools_import_export_process_import() {

	if ( empty( $_POST['edd_import_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['edd_import_nonce'], 'edd_import_nonce' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	if ( edd_get_file_extension( $_FILES['import_file']['name'] ) != 'json' ) {
		wp_die( __( 'Please upload a valid .json file', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 400 ) );
	}

	$import_file = $_FILES['import_file']['tmp_name'];

	if ( empty( $import_file ) ) {
		wp_die( __( 'Please upload a file to import', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 400 ) );
	}

	// Retrieve the settings from the file and convert the json object to an array
	$settings = edd_object_to_array( json_decode( file_get_contents( $import_file ) ) );

	if ( ! isset( $settings['edd_settings'] ) ) {

		// Process a settings export from a pre 2.8 version of EDD
		update_option( 'edd_settings', $settings );

	} else {

		// Update the settings from a 2.8+ export file
		$edd_settings = $settings['edd_settings'];
		update_option( 'edd_settings', $edd_settings );

		$edd_tax_rates = $settings['edd_tax_rates'];
		if ( ! empty( $edd_tax_rates ) ) {
			foreach( $edd_tax_rates as $rate ) {
				$scope = 'country';
				if ( ! empty( $rate['scope'] ) ) {
					$scope = $rate['scope'];
				} elseif ( empty( $rate['global'] ) && ! empty( $rate['state'] ) ) {
					$scope = 'region';
				} elseif ( empty( $rate['country'] && empty( $rate['state'] ) ) ) {
					$scope = 'global';
				}
				edd_add_tax_rate(
					array(
						'name'        => esc_attr( $rate['country'] ),
						'status'      => ! empty( $rate['status'] ) ? esc_attr( $rate['status'] ) : 'active',
						'description' => esc_attr( $rate['state'] ),
						'amount'      => floatval( $rate['rate'] ),
						'scope'       => esc_attr( $scope ),
					)
				);
			}
		}

	}

	edd_redirect( edd_get_admin_url( array(
		'page'        => 'edd-tools',
		'edd-message' => 'settings-imported',
		'tab'         => 'import_export',
	) ) );
}
add_action( 'edd_import_settings', 'edd_tools_import_export_process_import' );

/**
 * Display the debug log tab
 *
 * @since       2.8.7
 */
function edd_tools_debug_log_display() {
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	$edd_logs = EDD()->debug_log;

	// Setup fallback incase no file exists
	$path        = $edd_logs->get_log_file_path();
	$log         = $edd_logs->get_file_contents();
	$path_output = ! empty( $path )
		? wp_normalize_path( $path )
		: esc_html__( 'No File', 'easy-digital-downloads' );
	$log_output  = ! empty( $log )
		? wp_normalize_path( $log )
		: esc_html__( 'Log is Empty', 'easy-digital-downloads' );
	?>

	<div class="postbox">
		<h3><span><?php esc_html_e( 'Debug Log', 'easy-digital-downloads' ); ?></span></h3>
		<div class="inside">
			<form id="edd-debug-log" method="post">
				<p>
					<?php
					printf(
						wp_kses_post(
							/* translators: 1. opening anchor tag, do not translate; 2. function name, do not translate; 3. closing anchor tag, do not translate */
							__( 'When debug mode is enabled, specific information will be logged here. (%1$sLearn how to use %2$s in your own code.%3$s)', 'easy-digital-downloads' )
						),
						'<a href="https://easydigitaldownloads.com/docs/edd-debug-log">',
						'<code>edd_debug_log</code>',
						'</a>'
					);
					?>
				</p>
				<textarea
					readonly="readonly"
					class="edd-tools-textarea"
					rows="15"
					name="edd-debug-log-contents"><?php echo esc_textarea( $log_output ); ?></textarea>
				<p>
					<input type="hidden" name="edd_action" value="submit_debug_log"/>
					<?php
					submit_button( __( 'Download Debug Log File', 'easy-digital-downloads' ), 'primary', 'edd-download-debug-log', false );
					submit_button( __( 'Copy to Clipboard', 'easy-digital-downloads' ), 'secondary edd-inline-button', 'edd-copy-debug-log', false, array( 'onclick' => "this.form['edd-debug-log-contents'].focus();this.form['edd-debug-log-contents'].select();document.execCommand('copy');return false;" ) );

					// Only show the "Clear Log" button if there is a log to clear
					if ( ! empty( $log ) ) {
						submit_button( __( 'Clear Log', 'easy-digital-downloads' ), 'secondary edd-inline-button', 'edd-clear-debug-log', false );
					}

					?>
				</p>
				<?php wp_nonce_field( 'edd-debug-log-action' ); ?>
			</form>

			<p>
				<?php esc_html_e( 'Log file', 'easy-digital-downloads' ); ?>:
				<code><?php echo esc_html( $path_output ); ?></code>
			</p>
		</div><!-- .inside -->
	</div><!-- .postbox -->

	<?php
}
add_action( 'edd_tools_tab_debug_log', 'edd_tools_debug_log_display' );

/**
 * Handles submit actions for the debug log.
 *
 * @since 2.8.7
 */
function edd_handle_submit_debug_log() {
	$edd_logs = EDD()->debug_log;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	check_admin_referer( 'edd-debug-log-action' );

	if ( isset( $_REQUEST['edd-download-debug-log'] ) ) {
		nocache_headers();

		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment; filename="edd-debug-log.txt"' );

		echo wp_strip_all_tags( $_REQUEST['edd-debug-log-contents'] );
		exit;

	} elseif ( isset( $_REQUEST['edd-clear-debug-log'] ) ) {

		// Clear the debug log.
		$edd_logs->clear_log_file();

		edd_redirect( edd_get_admin_url( array(
			'page' => 'edd-tools',
			'tab'  => 'debug_log'
		) ) );
	}
}
add_action( 'edd_submit_debug_log', 'edd_handle_submit_debug_log' );

/**
 * Redirects requests to the old sales log to the orders page.
 *
 * @since 3.0
 */
function edd_redirect_sales_log() {
	if ( edd_is_admin_page( 'tools', 'logs' ) && ! empty( $_GET['view'] ) && 'sales' === $_GET['view'] ) {
		$query_args = array(
			'page' => 'edd-payment-history'
		);

		$args_to_remap = array(
			'download'   => 'product-id',
			'start-date' => 'start-date',
			'end-date'   => 'end-date'
		);

		foreach( $args_to_remap as $old_arg => $new_arg ) {
			if ( ! empty( $_GET[ $old_arg ] ) ) {
				$query_args[ $new_arg ] = urlencode( $_GET[ $old_arg ] );
			}
		}

		wp_safe_redirect( esc_url_raw( add_query_arg( $query_args, edd_get_admin_base_url() ) ) );
		exit;
	}
}
add_action( 'admin_init', 'edd_redirect_sales_log' );

/**
 * Renders the Logs tab in the Tools screen.
 *
 * @since 3.0
 */
function edd_tools_tab_logs() {
	if ( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	require_once EDD_PLUGIN_DIR . 'includes/admin/tools/logs.php';

	$current_view = 'file_downloads';
	$log_views    = edd_log_default_views();

	if ( isset( $_GET['view'] ) && array_key_exists( $_GET['view'], $log_views ) ) {
		$current_view = sanitize_text_field( $_GET['view'] );
	}

	/**
	 * Fires when a given logs view should be rendered.
	 *
	 * The dynamic portion of the hook name, `$current_view`, represents the slug
	 * of the logs view to render.
	 *
	 * @since 1.4
	 */
	do_action( 'edd_logs_view_' . $current_view );
}
add_action( 'edd_tools_tab_logs', 'edd_tools_tab_logs' );
