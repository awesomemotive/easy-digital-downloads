<?php
/**
 * Email Editor: Recipient
 *
 * @package     EDD
 * @subpackage  Admin/Emails/Views
 * @since       3.3.0
 */

defined( 'ABSPATH' ) || exit;

// If the recipient cannot be modified, just show a fake recipient.
if (
	'admin' !== $email->recipient ||
	( ! array_key_exists( 'recipients', $email->meta ) && ! $email->can_edit( 'recipient' ) )
	) {
	?>
	<div class="edd-form-group edd-email__recipient">
		<div class="edd-form-group__label">
			<?php esc_html_e( 'Send To', 'easy-digital-downloads' ); ?>
		</div>
		<div class="edd-form-group__control">
			<?php
			if ( 'admin' === $email->recipient ) {
				$recipient = get_bloginfo( 'admin_email' );
			} else {
				$url       = str_replace( array( 'http://', 'https://' ), '', home_url() );
				$recipient = "sample{$email->recipient}@{$url}";
			}
			echo esc_attr( $recipient );
			?>
		</div>
	</div>
	<?php
	return;
}

// If the recipient can be modified, show the recipient selector. It may be disabled if the user cannot edit it.
$custom_recipients = $email->get_metadata( 'recipients' );
$recipients        = $registry->get_recipients();
$recipient_label   = $recipients[ $email->recipient ];
if ( 'admin' === $email->recipient ) {
	$recipient_label .= ': ' . get_bloginfo( 'admin_email' );
}
$options = array(
	''        => array(
		'label'    => $recipient_label,
		'selected' => empty( $custom_recipients ),
	),
	'default' => array(
		'label'    => __( 'Admin Email Recipients', 'easy-digital-downloads' ),
		'selected' => 'admin' === $custom_recipients,
	),
	'custom'  => array(
		'label'    => __( 'Custom Recipients', 'easy-digital-downloads' ),
		'selected' => ! empty( $custom_recipients ) && 'admin' !== $custom_recipients,
	),
);
?>
<div class="edd-form-group edd-email__recipient">
	<label for="edd-email-recipient" class="edd-form-group__label">
		<?php esc_html_e( 'Send To', 'easy-digital-downloads' ); ?>
	</label>
	<div class="edd-form-group__control">
		<?php
		if ( ! $email->can_edit( 'recipient' ) ) {
			$selected_option = array_filter(
				$options,
				function ( $option ) {
					return $option['selected'];
				}
			);
			$selected        = reset( $selected_option );
			echo esc_html( $selected['label'] );
		} else {
			?>
			<select id="edd-email-recipient" name="admin_recipient" class="edd-select">
				<?php
				foreach ( $options as $value => $option ) {
					?>
					<option value="<?php echo esc_attr( $value ); ?>"<?php selected( $option['selected'], true ); ?>>
						<?php echo esc_html( $option['label'] ); ?>
					</option>
					<?php
				}
				?>
			</select>
		<?php } ?>
	</div>
</div>
<?php
$admin_style  = ! empty( $options['default']['selected'] ) ? '' : 'display: none;';
$custom_style = ! empty( $options['custom']['selected'] ) ? '' : 'display:none;';
if ( ! empty( $options['default']['selected'] ) ) {
	$custom_recipients = '';
}
?>
<div class="edd-form-group edd-email__recipient--admin" style="<?php echo esc_attr( $admin_style ); ?>">
	<label for="edd-email-recipient-admin" class="edd-form-group__label">
		<?php esc_html_e( 'Recipients', 'easy-digital-downloads' ); ?>
	</label>
	<div class="edd-form-group__control">
		<?php
		$admin_recipients = edd_get_admin_notice_emails();
		foreach ( $admin_recipients as $admin_recipient ) {
			echo esc_html( $admin_recipient ) . '<br>';
		}
		?>
		<br>
		<p class="description">
			<?php
			printf(
				/* translators: 1: opening anchor tag; 2: closing anchor tag */
				esc_html__( 'Update the admin email recipients in the %1$semail settings%2$s.', 'easy-digital-downloads' ),
				'<a href="' . esc_url(
					edd_get_admin_url(
						array(
							'page' => 'edd-emails',
							'tab'  => 'settings',
						)
					)
				) . '">',
				'</a>'
			);
			?>
		</p>
	</div>
</div>
<div class="edd-form-group edd-email__recipient--custom" style="<?php echo esc_attr( $custom_style ); ?>">
	<label for="edd-email-recipients" class="edd-form-group__label">
		<?php esc_html_e( 'Custom Recipients', 'easy-digital-downloads' ); ?>
	</label>
	<div class="edd-form-group__control">
		<textarea id="edd-email-recipients" name="recipients" class="regular-text" rows="3"><?php echo esc_textarea( $custom_recipients ); ?></textarea>
		<p class="description"><?php esc_html_e( 'Enter one email per line.', 'easy-digital-downloads' ); ?></p>
	</div>
</div>
