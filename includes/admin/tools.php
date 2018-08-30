<?php
/**
 * Tools
 *
 * These are functions used for displaying EDD tools such as the import/export system.
 *
 * @package     EDD
 * @subpackage  Admin/Tools
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Tools
 *
 * Shows the tools panel which contains EDD-specific tools including the
 * built-in import/export system.
 *
 * @since       1.8
 * @author      Daniel J Griffiths
 * @return      void
 */
function edd_tools_page() {
	$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
?>
	<div class="wrap">
		<h2><?php _e( 'Easy Digital Downloads Tools', 'easy-digital-downloads' ); ?></h2>
		<h2 class="nav-tab-wrapper">
			<?php
			foreach( edd_get_tools_tabs() as $tab_id => $tab_name ) {

				$tab_url = add_query_arg( array(
					'tab' => $tab_id
				) );

				$tab_url = remove_query_arg( array(
					'edd-message'
				), $tab_url );

				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';
				echo '<a href="' . esc_url( $tab_url ) . '" class="nav-tab' . $active . '">' . esc_html( $tab_name ) . '</a>';

			}
			?>
		</h2>
		<div class="metabox-holder">
			<?php
			do_action( 'edd_tools_tab_' . $active_tab );
			?>
		</div><!-- .metabox-holder -->
	</div><!-- .wrap -->
<?php
}


/**
 * Retrieve tools tabs
 *
 * @since       2.0
 * @return      array
 */
function edd_get_tools_tabs() {

	$tabs                  = array();
	$tabs['general']       = __( 'General', 'easy-digital-downloads' );
	$tabs['api_keys']      = __( 'API Keys', 'easy-digital-downloads' );

	if( count( edd_get_beta_enabled_extensions() ) > 0 ) {
		$tabs['betas'] = __( 'Beta Versions', 'easy-digital-downloads' );
	}

	$tabs['system_info']   = __( 'System Info', 'easy-digital-downloads' );

	if( edd_is_debug_mode() ) {
		$tabs['debug_log'] = __( 'Debug Log', 'easy-digital-downloads' );
	}

	$tabs['import_export'] = __( 'Import/Export', 'easy-digital-downloads' );

	return apply_filters( 'edd_tools_tabs', $tabs );
}


/**
 * Display the ban emails tab
 *
 * @since       2.0
 * @return      void
 */
function edd_tools_banned_emails_display() {

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	do_action( 'edd_tools_banned_emails_before' );
?>
	<div class="postbox">
		<h3><span><?php _e( 'Banned Emails', 'easy-digital-downloads' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Emails placed in the box below will not be allowed to make purchases.', 'easy-digital-downloads' ); ?></p>
			<form method="post" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-tools&tab=general' ); ?>">
				<p>
					<textarea name="banned_emails" rows="10" class="large-text"><?php echo implode( "\n", edd_get_banned_emails() ); ?></textarea>
					<span class="description"><?php _e( 'Enter emails and/or domains (starting with "@") and/or TLDs (starting with ".") to disallow, one per line.', 'easy-digital-downloads' ); ?></span>
				</p>
				<p>
					<input type="hidden" name="edd_action" value="save_banned_emails" />
					<?php wp_nonce_field( 'edd_banned_emails_nonce', 'edd_banned_emails_nonce' ); ?>
					<?php submit_button( __( 'Save', 'easy-digital-downloads' ), 'secondary', 'submit', false ); ?>
				</p>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->
<?php
	do_action( 'edd_tools_banned_emails_after' );
	do_action( 'edd_tools_after' );
}
add_action( 'edd_tools_tab_general', 'edd_tools_banned_emails_display' );


/**
 * Display the recount stats
 *
 * @since       2.5
 * @return      void
 */
function edd_tools_recount_stats_display() {

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	do_action( 'edd_tools_recount_stats_before' );
?>
	<div class="postbox">
		<h3><span><?php _e( 'Recount Stats', 'easy-digital-downloads' ); ?></span></h3>
		<div class="inside recount-stats-controls">
			<p><?php _e( 'Use these tools to recount / reset store stats.', 'easy-digital-downloads' ); ?></p>
			<form method="post" id="edd-tools-recount-form" class="edd-export-form edd-import-export-form">
				<span>

					<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>

					<select name="edd-export-class" id="recount-stats-type">
						<option value="0" selected="selected" disabled="disabled"><?php _e( 'Please select an option', 'easy-digital-downloads' ); ?></option>
						<option data-type="recount-store" value="EDD_Tools_Recount_Store_Earnings"><?php _e( 'Recount Store Earnings and Sales', 'easy-digital-downloads' ); ?></option>
						<option data-type="recount-download" value="EDD_Tools_Recount_Download_Stats"><?php printf( __( 'Recount Earnings and Sales for a %s', 'easy-digital-downloads' ), edd_get_label_singular( true ) ); ?></option>
						<option data-type="recount-all" value="EDD_Tools_Recount_All_Stats"><?php printf( __( 'Recount Earnings and Sales for All %s', 'easy-digital-downloads' ), edd_get_label_plural( true ) ); ?></option>
						<option data-type="recount-customer-stats" value="EDD_Tools_Recount_Customer_Stats"><?php _e( 'Recount Customer Stats', 'easy-digital-downloads' ); ?></option>
						<?php do_action( 'edd_recount_tool_options' ); ?>
						<option data-type="reset-stats" value="EDD_Tools_Reset_Stats"><?php _e( 'Reset Store', 'easy-digital-downloads' ); ?></option>
					</select>

					<span id="tools-product-dropdown" style="display: none">
						<?php
							$args = array(
								'name'   => 'download_id',
								'number' => -1,
								'chosen' => true,
							);
							echo EDD()->html->product_dropdown( $args );
						?>
					</span>

					<input type="submit" id="recount-stats-submit" value="<?php _e( 'Submit', 'easy-digital-downloads' ); ?>" class="button-secondary"/>

					<br />

					<span class="edd-recount-stats-descriptions">
						<span id="recount-store"><?php _e( 'Recalculates the total store earnings and sales.', 'easy-digital-downloads' ); ?></span>
						<span id="recount-download"><?php printf( __( 'Recalculates the earnings and sales stats for a specific %s.', 'easy-digital-downloads' ), edd_get_label_singular( true ) ); ?></span>
						<span id="recount-all"><?php printf( __( 'Recalculates the earnings and sales stats for all %s.', 'easy-digital-downloads' ), edd_get_label_plural( true ) ); ?></span>
						<span id="recount-customer-stats"><?php _e( 'Recalculates the lifetime value and purchase counts for all customers.', 'easy-digital-downloads' ); ?></span>
						<?php do_action( 'edd_recount_tool_descriptions' ); ?>
						<span id="reset-stats"><?php _e( '<strong>Deletes</strong> all payment records, customers, and related log entries.', 'easy-digital-downloads' ); ?></span>
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
 * Display the clear upgrades tab
 *
 * @since       2.3.5
 * @return      void
 */
function edd_tools_clear_doing_upgrade_display() {

	if( ! current_user_can( 'manage_shop_settings' ) || false === get_option( 'edd_doing_upgrade' ) ) {
		return;
	}

	do_action( 'edd_tools_clear_doing_upgrade_before' );
?>
	<div class="postbox">
		<h3><span><?php _e( 'Clear Incomplete Upgrade Notice', 'easy-digital-downloads' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Sometimes a database upgrade notice may not be cleared after an upgrade is completed due to conflicts with other extensions or other minor issues.', 'easy-digital-downloads' ); ?></p>
			<p><?php _e( 'If you\'re certain these upgrades have been completed, you can clear these upgrade notices by clicking the button below. If you have any questions about this, please contact the Easy Digital Downloads support team and we\'ll be happy to help.', 'easy-digital-downloads' ); ?></p>
			<form method="post" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-tools&tab=general' ); ?>">
				<p>
					<input type="hidden" name="edd_action" value="clear_doing_upgrade" />
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
 * @since       2.0
 * @return      void
 */
function edd_tools_api_keys_display() {

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	do_action( 'edd_tools_api_keys_before' );

	require_once EDD_PLUGIN_DIR . 'includes/admin/class-api-keys-table.php';

	$api_keys_table = new EDD_API_Keys_Table();
	$api_keys_table->prepare_items();
	$api_keys_table->display();
?>
	<p>
	<?php printf(
		__( 'These API keys allow you to use the <a href="%s">EDD REST API</a> to retrieve store data in JSON or XML for external applications or devices, such as the <a href="%s">EDD mobile app</a>.', 'easy-digital-downloads' ),
		'http://docs.easydigitaldownloads.com/article/544-edd-api-reference/',
		'https://easydigitaldownloads.com/downloads/ios-sales-earnings-tracker/?utm_source=plugin-tools-page&utm_medium=api_keys_tab&utm_term=ios-app&utm_campaign=EDDMobileApp'
	); ?>
	</p>
<?php

	do_action( 'edd_tools_api_keys_after' );
}
add_action( 'edd_tools_tab_api_keys', 'edd_tools_api_keys_display' );


/**
 * Display beta opt-ins
 *
 * @since       2.6.11
 * @return      void
 */
function edd_tools_betas_display() {
	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	$has_beta = edd_get_beta_enabled_extensions();

	do_action( 'edd_tools_betas_before' );
	?>

	<div class="postbox edd-beta-support">
		<h3><span><?php _e( 'Enable Beta Versions', 'easy-digital-downloads' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Checking any of the below checkboxes will opt you in to receive pre-release update notifications. You can opt-out at any time. Pre-release updates do not install automatically, you will still have the opportunity to ignore update notifications.', 'easy-digital-downloads' ); ?></p>
			<form method="post" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-tools&tab=betas' ); ?>">
				<table class="form-table edd-beta-support">
					<tbody>
						<?php foreach( $has_beta as $slug => $product ) : ?>
							<tr>
								<?php $checked = edd_extension_has_beta_support( $slug ); ?>
								<th scope="row"><?php echo esc_html( $product ); ?></th>
								<td>
									<input type="checkbox" name="enabled_betas[<?php echo esc_attr( $slug ); ?>]" id="enabled_betas[<?php echo esc_attr( $slug ); ?>]"<?php echo checked( $checked, true, false ); ?> value="1" />
									<label for="enabled_betas[<?php echo esc_attr( $slug ); ?>]"><?php printf( __( 'Get updates for pre-release versions of %s', 'easy-digital-downloads' ), $product ); ?></label>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<input type="hidden" name="edd_action" value="save_enabled_betas" />
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
 * Return an array of all extensions with beta support
 *
 * Extensions should be added as 'extension-slug' => 'Extension Name'
 *
 * @since       2.6.11
 * @return      array $extensions The array of extensions
 */
function edd_get_beta_enabled_extensions() {
	return apply_filters( 'edd_beta_enabled_extensions', array() );
}


/**
 * Check if a given extensions has beta support enabled
 *
 * @since       2.6.11
 * @param       string $slug The slug of the extension to check
 * @return      bool True if enabled, false otherwise
 */
function edd_extension_has_beta_support( $slug ) {
	$enabled_betas = edd_get_option( 'enabled_betas', array() );
	$return        = false;

	if( array_key_exists( $slug, $enabled_betas ) ) {
		$return = true;
	}

	return $return;
}


/**
 * Save enabled betas
 *
 * @since       2.6.11
 * @return      void
 */
function edd_tools_enabled_betas_save() {
	if( ! wp_verify_nonce( $_POST['edd_save_betas_nonce'], 'edd_save_betas_nonce' ) ) {
		return;
	}

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	if( ! empty( $_POST['enabled_betas'] ) ) {
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
 * @param mixed $value The value being sent in, determining if beta support is enabled.
 *
 * @return bool
 */
function edd_tools_enabled_betas_sanitize_value( $value ) {
	return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
}


/**
 * Save banned emails
 *
 * @since       2.0
 * @return      void
 */
function edd_tools_banned_emails_save() {

	if( ! wp_verify_nonce( $_POST['edd_banned_emails_nonce'], 'edd_banned_emails_nonce' ) ) {
		return;
	}

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	if( ! empty( $_POST['banned_emails'] ) ) {

		// Sanitize the input
		$emails = array_map( 'trim', explode( "\n", $_POST['banned_emails'] ) );
		$emails = array_unique( $emails );
		$emails = array_map( 'sanitize_text_field', $emails );

		foreach( $emails as $id => $email ) {
			if( ! is_email( $email ) && $email[0] != '@' && $email[0] != '.' ) {
				unset( $emails[$id] );
			}
		}
	} else {
		$emails = '';
	}

	edd_update_option( 'banned_emails', $emails );
}
add_action( 'edd_save_banned_emails', 'edd_tools_banned_emails_save' );

/**
 * Execute upgrade notice clear
 *
 * @since       2.3.5
 * @return      void
 */
function edd_tools_clear_upgrade_notice() {
	if( ! wp_verify_nonce( $_POST['edd_clear_upgrades_nonce'], 'edd_clear_upgrades_nonce' ) ) {
		return;
	}

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	delete_option( 'edd_doing_upgrade' );
}
add_action( 'edd_clear_doing_upgrade', 'edd_tools_clear_upgrade_notice' );


/**
 * Display the tools import/export tab
 *
 * @since       2.0
 * @return      void
 */
function edd_tools_import_export_display() {

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	do_action( 'edd_tools_import_export_before' );
?>

	<div class="postbox edd-import-payment-history">
		<h3><span><?php _e( 'Import Payment History', 'easy-digital-downloads' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Import a CSV file of payment records.', 'easy-digital-downloads' ); ?></p>
			<form id="edd-import-payments" class="edd-import-form edd-import-export-form" action="<?php echo esc_url( add_query_arg( 'edd_action', 'upload_import_file', admin_url() ) ); ?>" method="post" enctype="multipart/form-data">

				<div class="edd-import-file-wrap">
					<?php wp_nonce_field( 'edd_ajax_import', 'edd_ajax_import' ); ?>
					<input type="hidden" name="edd-import-class" value="EDD_Batch_Payments_Import"/>
					<p>
						<input name="edd-import-file" id="edd-payments-import-file" type="file" />
					</p>
					<span>
						<input type="submit" value="<?php _e( 'Import CSV', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
						<span class="spinner"></span>
					</span>
				</div>

				<div class="edd-import-options" id="edd-import-payments-options" style="display:none;">

					<p>
						<?php
						printf(
							__( 'Each column loaded from the CSV needs to be mapped to a payment field. Select the column that should be mapped to each field below. Any columns not needed can be ignored. See <a href="%s" target="_blank">this guide</a> for assistance with importing payment records.', 'easy-digital-downloads' ),
							'http://docs.easydigitaldownloads.com/category/1337-importexport'
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
									<select name="edd-import-field[currency]" class="edd-import-csv-column" data-field="Currency">
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
								<td><?php _e( 'First Name', 'easy-digital-downloads' ); ?></td>
								<td>
									<select name="edd-import-field[first_name]" class="edd-import-csv-column" data-field="First Name">
										<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
									</select>
								</td>
								<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Last Name', 'easy-digital-downloads' ); ?></td>
								<td>
									<select name="edd-import-field[last_name]" class="edd-import-csv-column" data-field="Last Name">
										<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
									</select>
								</td>
								<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Customer ID', 'easy-digital-downloads' ); ?></td>
								<td>
									<select name="edd-import-field[customer_id]" class="edd-import-csv-column" data-field="Customer ID">
										<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
									</select>
								</td>
								<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Discount Code(s)', 'easy-digital-downloads' ); ?></td>
								<td>
									<select name="edd-import-field[discounts]" class="edd-import-csv-column" data-field="Discount Code">
										<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
									</select>
								</td>
								<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'IP Address', 'easy-digital-downloads' ); ?></td>
								<td>
									<select name="edd-import-field[ip]" class="edd-import-csv-column" data-field="IP Address">
										<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
									</select>
								</td>
								<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Mode (Live|Test)', 'easy-digital-downloads' ); ?></td>
								<td>
									<select name="edd-import-field[mode]" class="edd-import-csv-column" data-field="Mode (Live|Test)">
										<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
									</select>
								</td>
								<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Parent Payment ID', 'easy-digital-downloads' ); ?></td>
								<td>
									<select name="edd-import-field[parent_payment_id]" class="edd-import-csv-column" data-field="">
										<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
									</select>
								</td>
								<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Payment Method', 'easy-digital-downloads' ); ?></td>
								<td>
									<select name="edd-import-field[gateway]" class="edd-import-csv-column" data-field="Payment Method">
										<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
									</select>
								</td>
								<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Payment Number', 'easy-digital-downloads' ); ?></td>
								<td>
									<select name="edd-import-field[number]" class="edd-import-csv-column" data-field="Payment Number">
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
									<select name="edd-import-field[key]" class="edd-import-csv-column" data-field="Purchase Key">
										<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
									</select>
								</td>
								<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Purchased Product(s)', 'easy-digital-downloads' ); ?></td>
								<td>
									<select name="edd-import-field[downloads]" class="edd-import-csv-column" data-field="Products (Raw)">
										<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
									</select>
								</td>
								<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Status', 'easy-digital-downloads' ); ?></td>
								<td>
									<select name="edd-import-field[status]" class="edd-import-csv-column" data-field="Status">
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
									<select name="edd-import-field[total]" class="edd-import-csv-column" data-field="Amount ($)">
										<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
									</select>
								</td>
								<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Transaction ID', 'easy-digital-downloads' ); ?></td>
								<td>
									<select name="edd-import-field[transaction_id]" class="edd-import-csv-column" data-field="Transaction ID">
										<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
									</select>
								</td>
								<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'User', 'easy-digital-downloads' ); ?></td>
								<td>
									<select name="edd-import-field[user_id]" class="edd-import-csv-column" data-field="User">
										<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
									</select>
								</td>
								<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Address Line 1', 'easy-digital-downloads' ); ?></td>
								<td>
									<select name="edd-import-field[line1]" class="edd-import-csv-column" data-field="Address">
										<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
									</select>
								</td>
								<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Address Line 2', 'easy-digital-downloads' ); ?></td>
								<td>
									<select name="edd-import-field[line2]" class="edd-import-csv-column" data-field="Address (Line 2)">
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
									<select name="edd-import-field[zip]" class="edd-import-csv-column" data-field="Zip / Postal Code">
										<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
									</select>
								</td>
								<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Country', 'easy-digital-downloads' ); ?></td>
								<td>
									<select name="edd-import-field[country]" class="edd-import-csv-column" data-field="Country">
										<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
									</select>
								</td>
								<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
							</tr>
						</tbody>
					</table>
					<p class="submit">
						<button class="edd-import-proceed button-primary"><?php _e( 'Process Import', 'easy-digital-downloads' ); ?></button>
					</p>
				</div>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->

	<div class="postbox edd-import-payment-history">
		<h3><span><?php _e( 'Import Download Products', 'easy-digital-downloads' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Import a CSV file of products.', 'easy-digital-downloads' ); ?></p>
			<form id="edd-import-downloads" class="edd-import-form edd-import-export-form" action="<?php echo esc_url( add_query_arg( 'edd_action', 'upload_import_file', admin_url() ) ); ?>" method="post" enctype="multipart/form-data">

				<div class="edd-import-file-wrap">
					<?php wp_nonce_field( 'edd_ajax_import', 'edd_ajax_import' ); ?>
					<input type="hidden" name="edd-import-class" value="EDD_Batch_Downloads_Import"/>
					<p>
						<input name="edd-import-file" id="edd-downloads-import-file" type="file" />
					</p>
					<span>
						<input type="submit" value="<?php _e( 'Import CSV', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
						<span class="spinner"></span>
					</span>
				</div>

				<div class="edd-import-options" id="edd-import-downloads-options" style="display:none;">

					<p>
						<?php
						printf(
							__( 'Each column loaded from the CSV needs to be mapped to a Download product field. Select the column that should be mapped to each field below. Any columns not needed can be ignored. See <a href="%s" target="_blank">this guide</a> for assistance with importing Download products.', 'easy-digital-downloads' ),
							'http://docs.easydigitaldownloads.com/category/1337-importexport'
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
									<select name="edd-import-field[post_author]" class="edd-import-csv-column" data-field="Author">
										<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
									</select>
								</td>
								<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Product Categories', 'easy-digital-downloads' ); ?></td>
								<td>
									<select name="edd-import-field[categories]" class="edd-import-csv-column" data-field="Categories">
										<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
									</select>
								</td>
								<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Product Creation Date', 'easy-digital-downloads' ); ?></td>
								<td>
									<select name="edd-import-field[post_date]" class="edd-import-csv-column" data-field="Date Created">
										<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
									</select>
								</td>
								<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Product Description', 'easy-digital-downloads' ); ?></td>
								<td>
									<select name="edd-import-field[post_content]" class="edd-import-csv-column" data-field="Description">
										<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
									</select>
								</td>
								<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Product Excerpt', 'easy-digital-downloads' ); ?></td>
								<td>
									<select name="edd-import-field[post_excerpt]" class="edd-import-csv-column" data-field="Excerpt">
										<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
									</select>
								</td>
								<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Product Image', 'easy-digital-downloads' ); ?></td>
								<td>
									<select name="edd-import-field[featured_image]" class="edd-import-csv-column" data-field="Featured Image">
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
									<select name="edd-import-field[post_name]" class="edd-import-csv-column" data-field="Slug">
										<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
									</select>
								</td>
								<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Product Status', 'easy-digital-downloads' ); ?></td>
								<td>
									<select name="edd-import-field[post_status]" class="edd-import-csv-column" data-field="Status">
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
									<select name="edd-import-field[post_title]" class="edd-import-csv-column" data-field="Name">
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
									<select name="edd-import-field[download_limit]" class="edd-import-csv-column" data-field="File Download Limit">
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
									<select name="edd-import-field[earnings]" class="edd-import-csv-column" data-field="Earnings">
										<option value=""><?php _e( '- Ignore this field -', 'easy-digital-downloads' ); ?></option>
									</select>
								</td>
								<td class="edd-import-preview-field"><?php _e( '- select field to preview data -', 'easy-digital-downloads' ); ?></td>
							</tr>
						</tbody>
					</table>
					<p class="submit">
						<button class="edd-import-proceed button-primary"><?php _e( 'Process Import', 'easy-digital-downloads' ); ?></button>
					</p>
				</div>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->

	<div class="postbox">
		<h3><span><?php _e( 'Export Settings', 'easy-digital-downloads' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Export the Easy Digital Downloads settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'easy-digital-downloads' ); ?></p>
			<p><?php printf( __( 'To export shop data (purchases, customers, etc), visit the <a href="%s">Reports</a> page.', 'easy-digital-downloads' ), admin_url( 'edit.php?post_type=download&page=edd-reports&tab=export' ) ); ?></p>
			<form method="post" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-tools&tab=import_export' ); ?>">
				<p><input type="hidden" name="edd_action" value="export_settings" /></p>
				<p>
					<?php wp_nonce_field( 'edd_export_nonce', 'edd_export_nonce' ); ?>
					<?php submit_button( __( 'Export', 'easy-digital-downloads' ), 'secondary', 'submit', false ); ?>
				</p>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->

	<div class="postbox">
		<h3><span><?php _e( 'Import Settings', 'easy-digital-downloads' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Import the Easy Digital Downloads settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.', 'easy-digital-downloads' ); ?></p>
			<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-tools&tab=import_export' ); ?>">
				<p>
					<input type="file" name="import_file"/>
				</p>
				<p>
					<input type="hidden" name="edd_action" value="import_settings" />
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
 * @since       1.7
 * @return      void
 */
function edd_tools_import_export_process_export() {

	if( empty( $_POST['edd_export_nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_POST['edd_export_nonce'], 'edd_export_nonce' ) )
		return;

	if( ! current_user_can( 'manage_shop_settings' ) )
		return;

	$edd_settings  = get_option( 'edd_settings' );
	$edd_tax_rates = get_option( 'edd_tax_rates' );
	$settings = array(
		'edd_settings'  => $edd_settings,
		'edd_tax_rates' => $edd_tax_rates,
	);

	ignore_user_abort( true );

	if ( ! edd_is_func_disabled( 'set_time_limit' ) )
		set_time_limit( 0 );

	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . apply_filters( 'edd_settings_export_filename', 'edd-settings-export-' . date( 'm-d-Y' ) ) . '.json' );
	header( "Expires: 0" );

	echo json_encode( $settings );
	exit;
}
add_action( 'edd_export_settings', 'edd_tools_import_export_process_export' );


/**
 * Process a settings import from a json file
 *
 * @since 1.7
 * @return void
 */
function edd_tools_import_export_process_import() {

	if( empty( $_POST['edd_import_nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_POST['edd_import_nonce'], 'edd_import_nonce' ) )
		return;

	if( ! current_user_can( 'manage_shop_settings' ) )
		return;

	if( edd_get_file_extension( $_FILES['import_file']['name'] ) != 'json' ) {
		wp_die( __( 'Please upload a valid .json file', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 400 ) );
	}

	$import_file = $_FILES['import_file']['tmp_name'];

	if( empty( $import_file ) ) {
		wp_die( __( 'Please upload a file to import', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 400 ) );
	}

	// Retrieve the settings from the file and convert the json object to an array
	$settings = edd_object_to_array( json_decode( file_get_contents( $import_file ) ) );

	if ( ! isset( $settings['edd_settings'] ) ) {

		// Process a settings export from a pre 2.8 version of EDD
		update_option( 'edd_settings', $settings );

	} else {

		// Update the settings from a 2.8+ export file
		$edd_settings  = $settings['edd_settings'];
		update_option( 'edd_settings', $edd_settings );

		$edd_tax_rates = $settings['edd_tax_rates'];
		update_option( 'edd_tax_rates', $edd_tax_rates );

	}



	wp_safe_redirect( admin_url( 'edit.php?post_type=download&page=edd-tools&edd-message=settings-imported' ) ); exit;

}
add_action( 'edd_import_settings', 'edd_tools_import_export_process_import' );


/**
 * Display the debug log tab
 *
 * @since       2.8.7
 * @return      void
 */
function edd_tools_debug_log_display() {

	global $edd_logs;

	if( ! current_user_can( 'manage_shop_settings' ) || ! edd_is_debug_mode() ) {
		return;
	}

?>
	<div class="postbox">
		<h3><span><?php esc_html_e( 'Debug Log', 'easy-digital-downloads' ); ?></span></h3>
		<div class="inside">
			<form id="edd-debug-log" method="post">
				<p><?php _e( 'Use this tool to help debug Easy Digital Downloads functionality. Developers may use the <a href="https://github.com/easydigitaldownloads/easy-digital-downloads/blob/master/includes/class-edd-logging.php">EDD_Logging class</a> to record debug data.', 'easy-digital-downloads' ); ?></p>
				<textarea readonly="readonly" class="large-text" rows="15" name="edd-debug-log-contents"><?php echo esc_textarea( $edd_logs->get_file_contents() ); ?></textarea>
				<p class="submit">
					<input type="hidden" name="edd_action" value="submit_debug_log" />
					<?php
					submit_button( __( 'Download Debug Log File', 'easy-digital-downloads' ), 'primary', 'edd-download-debug-log', false );
					submit_button( __( 'Clear Log', 'easy-digital-downloads' ), 'secondary edd-inline-button', 'edd-clear-debug-log', false );
					submit_button( __( 'Copy Entire Log', 'easy-digital-downloads' ), 'secondary edd-inline-button', 'edd-copy-debug-log', false, array( 'onclick' => "this.form['edd-debug-log-contents'].focus();this.form['edd-debug-log-contents'].select();document.execCommand('copy');return false;" ) );
					?>
				</p>
				<?php wp_nonce_field( 'edd-debug-log-action' ); ?>
			</form>
			<p><?php _e( 'Log file', 'easy-digital-downloads' ); ?>: <code><?php echo $edd_logs->get_log_file_path(); ?></code></p>
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

	global $edd_logs;

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

		wp_safe_redirect( admin_url( 'edit.php?post_type=download&page=edd-tools&tab=debug_log' ) );
		exit;

	}
}
add_action( 'edd_submit_debug_log', 'edd_handle_submit_debug_log' );

/**
 * Display the system info tab
 *
 * @since       2.0
 * @return      void
 */
function edd_tools_sysinfo_display() {

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

?>
	<form action="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-tools&tab=system_info' ) ); ?>" method="post" dir="ltr">
		<textarea readonly="readonly" onclick="this.focus(); this.select()" id="system-info-textarea" name="edd-sysinfo"><?php echo edd_tools_sysinfo_get(); ?></textarea>
		<p class="submit">
			<input type="hidden" name="edd-action" value="download_sysinfo" />
			<?php submit_button( 'Download System Info File', 'primary', 'edd-download-sysinfo', false ); ?>
		</p>
	</form>
<?php
}
add_action( 'edd_tools_tab_system_info', 'edd_tools_sysinfo_display' );


/**
 * Get system info
 *
 * @since       2.0
 * @global      object $wpdb Used to query the database using the WordPress Database API
 * @return      string $return A string containing the info to output
 */
function edd_tools_sysinfo_get() {
	global $wpdb;

	if( !class_exists( 'Browser' ) )
		require_once EDD_PLUGIN_DIR . 'includes/libraries/browser.php';

	$browser = new Browser();

	// Get theme info
	$theme_data   = wp_get_theme();
	$theme        = $theme_data->Name . ' ' . $theme_data->Version;
	$parent_theme = $theme_data->Template;
	if ( ! empty( $parent_theme ) ) {
		$parent_theme_data = wp_get_theme( $parent_theme );
		$parent_theme      = $parent_theme_data->Name . ' ' . $parent_theme_data->Version;
	}

	// Try to identify the hosting provider
	$host = edd_get_host();

	$return  = '### Begin System Info (Generated ' . date( 'Y-m-d H:i:s' ) . ') ###' . "\n\n";

	// Start with the basics...
	$return .= '-- Site Info' . "\n\n";
	$return .= 'Site URL:                 ' . site_url() . "\n";
	$return .= 'Home URL:                 ' . home_url() . "\n";
	$return .= 'Multisite:                ' . ( is_multisite() ? 'Yes' : 'No' ) . "\n";

	$return  = apply_filters( 'edd_sysinfo_after_site_info', $return );

	// Can we determine the site's host?
	if( $host ) {
		$return .= "\n" . '-- Hosting Provider' . "\n\n";
		$return .= 'Host:                     ' . $host . "\n";

		$return  = apply_filters( 'edd_sysinfo_after_host_info', $return );
	}

	// The local users' browser information, handled by the Browser class
	$return .= "\n" . '-- User Browser' . "\n\n";
	$return .= $browser;

	$return  = apply_filters( 'edd_sysinfo_after_user_browser', $return );

	$locale = get_locale();

	// WordPress configuration
	$return .= "\n" . '-- WordPress Configuration' . "\n\n";
	$return .= 'Version:                  ' . get_bloginfo( 'version' ) . "\n";
	$return .= 'Language:                 ' . ( !empty( $locale ) ? $locale : 'en_US' ) . "\n";
	$return .= 'Permalink Structure:      ' . ( get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default' ) . "\n";
	$return .= 'Active Theme:             ' . $theme . "\n";
	if ( $parent_theme !== $theme ) {
		$return .= 'Parent Theme:             ' . $parent_theme . "\n";
	}
	$return .= 'Show On Front:            ' . get_option( 'show_on_front' ) . "\n";

	// Only show page specs if frontpage is set to 'page'
	if( get_option( 'show_on_front' ) == 'page' ) {
		$front_page_id = get_option( 'page_on_front' );
		$blog_page_id = get_option( 'page_for_posts' );

		$return .= 'Page On Front:            ' . ( $front_page_id != 0 ? get_the_title( $front_page_id ) . ' (#' . $front_page_id . ')' : 'Unset' ) . "\n";
		$return .= 'Page For Posts:           ' . ( $blog_page_id != 0 ? get_the_title( $blog_page_id ) . ' (#' . $blog_page_id . ')' : 'Unset' ) . "\n";
	}

	$return .= 'ABSPATH:                  ' . ABSPATH . "\n";

	// Make sure wp_remote_post() is working
	$request['cmd'] = '_notify-validate';

	$params = array(
		'sslverify'     => false,
		'timeout'       => 60,
		'user-agent'    => 'EDD/' . EDD_VERSION,
		'body'          => $request
	);

	$response = wp_remote_post( 'https://www.paypal.com/cgi-bin/webscr', $params );

	if( !is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
		$WP_REMOTE_POST = 'wp_remote_post() works';
	} else {
		$WP_REMOTE_POST = 'wp_remote_post() does not work';
	}

	$return .= 'Remote Post:              ' . $WP_REMOTE_POST . "\n";
	$return .= 'Table Prefix:             ' . 'Length: ' . strlen( $wpdb->prefix ) . '   Status: ' . ( strlen( $wpdb->prefix ) > 16 ? 'ERROR: Too long' : 'Acceptable' ) . "\n";
	// Commented out per https://github.com/easydigitaldownloads/Easy-Digital-Downloads/issues/3475
	//$return .= 'Admin AJAX:               ' . ( edd_test_ajax_works() ? 'Accessible' : 'Inaccessible' ) . "\n";
	$return .= 'WP_DEBUG:                 ' . ( defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' ) . "\n";
	$return .= 'Memory Limit:             ' . WP_MEMORY_LIMIT . "\n";
	$return .= 'Registered Post Stati:    ' . implode( ', ', get_post_stati() ) . "\n";

	$return  = apply_filters( 'edd_sysinfo_after_wordpress_config', $return );

	// EDD configuration
	$return .= "\n" . '-- EDD Configuration' . "\n\n";
	$return .= 'Version:                  ' . EDD_VERSION . "\n";
	$return .= 'Upgraded From:            ' . get_option( 'edd_version_upgraded_from', 'None' ) . "\n";
	$return .= 'Test Mode:                ' . ( edd_is_test_mode() ? "Enabled\n" : "Disabled\n" );
	$return .= 'AJAX:                     ' . ( ! edd_is_ajax_disabled() ? "Enabled\n" : "Disabled\n" );
	$return .= 'Guest Checkout:           ' . ( edd_no_guest_checkout() ? "Disabled\n" : "Enabled\n" );
	$return .= 'Symlinks:                 ' . ( apply_filters( 'edd_symlink_file_downloads', edd_get_option( 'symlink_file_downloads', false ) ) && function_exists( 'symlink' ) ? "Enabled\n" : "Disabled\n" );
	$return .= 'Download Method:          ' . ucfirst( edd_get_file_download_method() ) . "\n";
	$return .= 'Currency Code:            ' . edd_get_currency() . "\n";
	$return .= 'Currency Position:        ' . edd_get_option( 'currency_position', 'before' ) . "\n";
	$return .= 'Decimal Separator:        ' . edd_get_option( 'decimal_separator', '.' ) . "\n";
	$return .= 'Thousands Separator:      ' . edd_get_option( 'thousands_separator', ',' ) . "\n";
	$return .= 'Upgrades Completed:       ' . implode( ',', edd_get_completed_upgrades() ) . "\n";
	$return .= 'Download Link Expiration: ' . edd_get_option( 'download_link_expiration' ) . " hour(s)\n";

	$return  = apply_filters( 'edd_sysinfo_after_edd_config', $return );

	// EDD pages
	$purchase_page = edd_get_option( 'purchase_page', '' );
	$success_page  = edd_get_option( 'success_page', '' );
	$failure_page  = edd_get_option( 'failure_page', '' );

	$return .= "\n" . '-- EDD Page Configuration' . "\n\n";
	$return .= 'Checkout:                 ' . ( !empty( $purchase_page ) ? "Valid\n" : "Invalid\n" );
	$return .= 'Checkout Page:            ' . ( !empty( $purchase_page ) ? get_permalink( $purchase_page ) . "\n" : "Unset\n" );
	$return .= 'Success Page:             ' . ( !empty( $success_page ) ? get_permalink( $success_page ) . "\n" : "Unset\n" );
	$return .= 'Failure Page:             ' . ( !empty( $failure_page ) ? get_permalink( $failure_page ) . "\n" : "Unset\n" );
	$return .= 'Downloads Slug:           ' . ( defined( 'EDD_SLUG' ) ? '/' . EDD_SLUG . "\n" : "/downloads\n" );

	$return  = apply_filters( 'edd_sysinfo_after_edd_pages', $return );

	// EDD gateways
	$return .= "\n" . '-- EDD Gateway Configuration' . "\n\n";

	$active_gateways = edd_get_enabled_payment_gateways();
	if( $active_gateways ) {
		$default_gateway_is_active = edd_is_gateway_active( edd_get_default_gateway() );
		if( $default_gateway_is_active ) {
			$default_gateway = edd_get_default_gateway();
			$default_gateway = $active_gateways[$default_gateway]['admin_label'];
		} else {
			$default_gateway = 'Test Payment';
		}

		$gateways        = array();
		foreach( $active_gateways as $gateway ) {
			$gateways[] = $gateway['admin_label'];
		}

		$return .= 'Enabled Gateways:         ' . implode( ', ', $gateways ) . "\n";
		$return .= 'Default Gateway:          ' . $default_gateway . "\n";
	} else {
		$return .= 'Enabled Gateways:         None' . "\n";
	}

	$return  = apply_filters( 'edd_sysinfo_after_edd_gateways', $return );


	// EDD Taxes
	$return .= "\n" . '-- EDD Tax Configuration' . "\n\n";
	$return .= 'Taxes:                    ' . ( edd_use_taxes() ? "Enabled\n" : "Disabled\n" );
	$return .= 'Tax Rate:                 ' . edd_get_tax_rate() * 100 . "\n";
	$return .= 'Display On Checkout:      ' . ( edd_get_option( 'checkout_include_tax', false ) ? "Displayed\n" : "Not Displayed\n" );
	$return .= 'Prices Include Tax:       ' . ( edd_prices_include_tax() ? "Yes\n" : "No\n" );

	$rates = edd_get_tax_rates();
	if( !empty( $rates ) ) {
		$return .= 'Country / State Rates:    ' . "\n";
		foreach( $rates as $rate ) {
			$return .= '                          Country: ' . $rate['country'] . ', State: ' . $rate['state'] . ', Rate: ' . $rate['rate'] . "\n";
		}
	}

	$return  = apply_filters( 'edd_sysinfo_after_edd_taxes', $return );

	// EDD Templates
	$dir = get_stylesheet_directory() . '/edd_templates/*';
	if( is_dir( $dir ) && ( count( glob( "$dir/*" ) ) !== 0 ) ) {
		$return .= "\n" . '-- EDD Template Overrides' . "\n\n";

		foreach( glob( $dir ) as $file ) {
			$return .= 'Filename:                 ' . basename( $file ) . "\n";
		}

		$return  = apply_filters( 'edd_sysinfo_after_edd_templates', $return );
	}

	// Get plugins that have an update
	$updates = get_plugin_updates();

	// Must-use plugins
	// NOTE: MU plugins can't show updates!
	$muplugins = get_mu_plugins();
	if( count( $muplugins ) > 0 ) {
		$return .= "\n" . '-- Must-Use Plugins' . "\n\n";

		foreach( $muplugins as $plugin => $plugin_data ) {
			$return .= $plugin_data['Name'] . ': ' . $plugin_data['Version'] . "\n";
		}

		$return = apply_filters( 'edd_sysinfo_after_wordpress_mu_plugins', $return );
	}

	// WordPress active plugins
	$return .= "\n" . '-- WordPress Active Plugins' . "\n\n";

	$plugins = get_plugins();
	$active_plugins = get_option( 'active_plugins', array() );

	foreach( $plugins as $plugin_path => $plugin ) {
		if( !in_array( $plugin_path, $active_plugins ) )
			continue;

		$update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[$plugin_path]->update->new_version . ')' : '';
		$return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
	}

	$return  = apply_filters( 'edd_sysinfo_after_wordpress_plugins', $return );

	// WordPress inactive plugins
	$return .= "\n" . '-- WordPress Inactive Plugins' . "\n\n";

	foreach( $plugins as $plugin_path => $plugin ) {
		if( in_array( $plugin_path, $active_plugins ) )
			continue;

		$update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[$plugin_path]->update->new_version . ')' : '';
		$return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
	}

	$return  = apply_filters( 'edd_sysinfo_after_wordpress_plugins_inactive', $return );

	if( is_multisite() ) {
		// WordPress Multisite active plugins
		$return .= "\n" . '-- Network Active Plugins' . "\n\n";

		$plugins = wp_get_active_network_plugins();
		$active_plugins = get_site_option( 'active_sitewide_plugins', array() );

		foreach( $plugins as $plugin_path ) {
			$plugin_base = plugin_basename( $plugin_path );

			if( !array_key_exists( $plugin_base, $active_plugins ) )
				continue;

			$update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[$plugin_path]->update->new_version . ')' : '';
			$plugin  = get_plugin_data( $plugin_path );
			$return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
		}

		$return  = apply_filters( 'edd_sysinfo_after_wordpress_ms_plugins', $return );
	}

	// Server configuration (really just versioning)
	$return .= "\n" . '-- Webserver Configuration' . "\n\n";
	$return .= 'PHP Version:              ' . PHP_VERSION . "\n";
	$return .= 'MySQL Version:            ' . $wpdb->db_version() . "\n";
	$return .= 'Webserver Info:           ' . $_SERVER['SERVER_SOFTWARE'] . "\n";

	$return  = apply_filters( 'edd_sysinfo_after_webserver_config', $return );

	// PHP configs... now we're getting to the important stuff
	$return .= "\n" . '-- PHP Configuration' . "\n\n";
	$return .= 'Memory Limit:             ' . ini_get( 'memory_limit' ) . "\n";
	$return .= 'Upload Max Size:          ' . ini_get( 'upload_max_filesize' ) . "\n";
	$return .= 'Post Max Size:            ' . ini_get( 'post_max_size' ) . "\n";
	$return .= 'Upload Max Filesize:      ' . ini_get( 'upload_max_filesize' ) . "\n";
	$return .= 'Time Limit:               ' . ini_get( 'max_execution_time' ) . "\n";
	$return .= 'Max Input Vars:           ' . ini_get( 'max_input_vars' ) . "\n";
	$return .= 'Display Errors:           ' . ( ini_get( 'display_errors' ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A' ) . "\n";
	$return .= 'PHP Arg Separator:        ' . edd_get_php_arg_separator_output() . "\n";

	$return  = apply_filters( 'edd_sysinfo_after_php_config', $return );

	// PHP extensions and such
	$return .= "\n" . '-- PHP Extensions' . "\n\n";
	$return .= 'cURL:                     ' . ( function_exists( 'curl_init' ) ? 'Supported' : 'Not Supported' ) . "\n";
	$return .= 'fsockopen:                ' . ( function_exists( 'fsockopen' ) ? 'Supported' : 'Not Supported' ) . "\n";
	$return .= 'SOAP Client:              ' . ( class_exists( 'SoapClient' ) ? 'Installed' : 'Not Installed' ) . "\n";
	$return .= 'Suhosin:                  ' . ( extension_loaded( 'suhosin' ) ? 'Installed' : 'Not Installed' ) . "\n";

	$return  = apply_filters( 'edd_sysinfo_after_php_ext', $return );

	// Session stuff
	$return .= "\n" . '-- Session Configuration' . "\n\n";
	$return .= 'EDD Use Sessions:         ' . ( defined( 'EDD_USE_PHP_SESSIONS' ) && EDD_USE_PHP_SESSIONS ? 'Enforced' : ( EDD()->session->use_php_sessions() ? 'Enabled' : 'Disabled' ) ) . "\n";
	$return .= 'Session:                  ' . ( isset( $_SESSION ) ? 'Enabled' : 'Disabled' ) . "\n";

	// The rest of this is only relevant is session is enabled
	if( isset( $_SESSION ) ) {
		$return .= 'Session Name:             ' . esc_html( ini_get( 'session.name' ) ) . "\n";
		$return .= 'Cookie Path:              ' . esc_html( ini_get( 'session.cookie_path' ) ) . "\n";
		$return .= 'Save Path:                ' . esc_html( ini_get( 'session.save_path' ) ) . "\n";
		$return .= 'Use Cookies:              ' . ( ini_get( 'session.use_cookies' ) ? 'On' : 'Off' ) . "\n";
		$return .= 'Use Only Cookies:         ' . ( ini_get( 'session.use_only_cookies' ) ? 'On' : 'Off' ) . "\n";
	}

	$return  = apply_filters( 'edd_sysinfo_after_session_config', $return );

	$return .= "\n" . '### End System Info ###';

	return $return;
}


/**
 * Generates a System Info download file
 *
 * @since       2.0
 * @return      void
 */
function edd_tools_sysinfo_download() {

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	nocache_headers();

	header( 'Content-Type: text/plain' );
	header( 'Content-Disposition: attachment; filename="edd-system-info.txt"' );

	echo wp_strip_all_tags( $_POST['edd-sysinfo'] );
	edd_die();
}
add_action( 'edd_download_sysinfo', 'edd_tools_sysinfo_download' );
