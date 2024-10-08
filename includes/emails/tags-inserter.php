<?php
/**
 * Add a button to wp_editor() instances to allow easier tag insertion.
 *
 * @package     EDD
 * @subpackage  Email
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Get registered emails.
 *
 * This assumes emails are "registered" by using a section in the Emails tab.
 *
 * @since 3.0
 *
 * @return array $emails Registered emails.
 */
function edd_email_tags_inserter_get_registered_emails() {
	$settings = edd_get_registered_settings();
	$emails   = $settings['emails'];

	unset( $emails['main'] );
	unset( $emails['templates'] );
	unset( $emails['purchase_receipts'] );
	unset( $emails['sale_notifications'] );
	unset( $emails['email_summaries'] );

	return array_keys( $emails );
}

/**
 * Wait until the admin has loaded (so edd_get_registered_settings() works)
 * and hook in to WordPress for each registered email.
 *
 * @since 3.0
 */
function edd_email_tags_inserter_register() {
	foreach ( edd_email_tags_inserter_get_registered_emails() as $email ) {
		// Add Thickbox button.
		add_action( 'edd_settings_tab_top_emails_' . $email, 'edd_email_tags_inserter_media_button' );
	}
}
add_action( 'load-download_page_edd-settings', 'edd_email_tags_inserter_register' );

/**
 * Wait until `media_buttons` action is called.
 *
 * @see edd_email_tags_inserter_media_button_output()
 *
 * @since 3.0
 *
 * @param bool $allow_html Whether to allow HTML tags in the email.
 */
function edd_email_tags_inserter_media_button( $allow_html = true ) {
	edd_load_email_tags();

	remove_all_actions( 'media_buttons' );
	if ( $allow_html ) {
		add_action( 'media_buttons', 'media_buttons' );
	}
	add_action( 'media_buttons', 'edd_email_tags_inserter_media_button_output' );
}

/**
 * Adds an 'Insert Tag' button above the TinyMCE Editor on email-related
 * `wp_editor()` instances.
 *
 * @since 3.0
 */
function edd_email_tags_inserter_media_button_output() {
	?>
	<a href="#TB_inline?width=640&inlineId=edd-insert-email-tag" class="edd-email-tags-inserter thickbox button edd-thickbox" style="padding-left: 0.4em;">
		<span class="wp-media-buttons-icon dashicons dashicons-editor-code"></span>
		<?php esc_html_e( 'Insert Tag', 'easy-digital-downloads' ); ?>
	</a>
	<?php
	$page      = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS );
	$email_id  = filter_input( INPUT_GET, 'email', FILTER_SANITIZE_SPECIAL_CHARS );
	$context   = '';
	$recipient = '';
	if ( 'edd-emails' === $page && ! empty( $email_id ) ) {
		?>
		<button type="button" class="button button-secondary edd-email-action-reset edd-promo-notice__trigger" data-email="<?php echo esc_attr( $email_id ); ?>">
			<?php esc_html_e( 'Restore Default', 'easy-digital-downloads' ); ?>
		</button>
		<?php
		$email = edd_get_email( $email_id );
		if ( $email ) {
			$context   = $email->context;
			$recipient = $email->recipient;
			if ( ! $email->get_template()->supports_html() ) {
				$content = __( 'This email will be sent as a plain text email and does not support images or HTML markup.', 'easy-digital-downloads' );
				if ( 'text/html' === EDD()->emails->get_content_type() ) {
					$content .= ' ' . __( 'This is specific to this email and does not affect other emails.', 'easy-digital-downloads' );
				}
				$tooltip = new \EDD\HTML\Tooltip(
					array(
						'content'  => $content,
						'dashicon' => 'dashicons-warning',
					)
				);
				$tooltip->output();
			}
		}
	}
	if ( wp_script_is( 'edd-admin-email-tags' ) ) {
		return;
	}
	// Output Thickbox content.
	edd_email_tags_inserter_thickbox_content( $context, $recipient );
	// Enqueue scripts.
	edd_email_tags_inserter_enqueue_scripts( $context, $recipient );
}

/**
 * Enqueue scripts for clicking a tag inside of Thickbox.
 *
 * @since 3.0
 * @param string $context The context to get tags for.
 * @param string $recipient The recipient to get tags for.
 */
function edd_email_tags_inserter_enqueue_scripts( $context = '', $recipient = '' ) {

	wp_enqueue_style( 'edd-admin-email-tags' );
	wp_enqueue_script( 'edd-admin-email-tags' );

	// Send information about tags to script.
	$items = array();
	$tags  = edd_get_email_tags( $context, $recipient );

	foreach ( $tags as $tag ) {
		$items[] = array(
			'title'    => $tag['label'] ? $tag['label'] : $tag['tag'],
			'tag'      => $tag['tag'],
			'keywords' => array_merge(
				explode( ' ', $tag['description'] ),
				array( $tag['tag'] )
			),
		);
	}

	wp_localize_script(
		'edd-admin-email-tags',
		'eddEmailTagsInserter',
		array(
			'items' => $items,
		)
	);
}

/**
 * Output Thickbox content.
 *
 * @since 3.0
 * @param string $context The context to get tags for.
 * @param string $recipient The recipient to get tags for.
 */
function edd_email_tags_inserter_thickbox_content( $context = '', $recipient = '' ) {
	$tags = edd_get_email_tags( $context, $recipient );
	?>
	<div id="edd-insert-email-tag" style="display: none;">
		<div class="edd-email-tags-filter">
			<input type="search" class="edd-email-tags-filter-search" placeholder="<?php echo esc_attr( __( 'Find a tag...', 'easy-digital-downloads' ) ); ?>" />
		</div>

		<ul class="edd-email-tags-list">
			<?php foreach ( $tags as $tag ) : ?>
			<li id="<?php echo esc_attr( $tag['tag'] ); ?>" data-tag="<?php echo esc_attr( $tag['tag'] ); ?>" class="edd-email-tags-list-item">
				<button class="edd-email-tags-list-button" data-to_insert="{<?php echo esc_attr( $tag['tag'] ); ?>}">
					<strong><?php echo esc_html( $tag['label'] ); ?></strong><code><?php echo '{' . esc_html( $tag['tag'] ) . '}'; ?></code>
					<span><?php echo esc_html( $tag['description'] ); ?></span>
				</button>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}
