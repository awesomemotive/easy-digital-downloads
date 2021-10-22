<?php
/**
 * Displays a list of notifications.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 *
 * @var \EDD\Models\Notification[] $notifications
 */
?>
<div id="edd-notifications">
	<h3><?php esc_html_e( 'Notifications', 'easy-digital-downloads' ); ?></h3>

	<?php
	if ( ! empty( $notifications ) ) {
		?>
		<div id="edd-notifications-list">
			<?php
			foreach( $notifications as $notification ) {
				require EDD_PLUGIN_DIR . '/includes/admin/views/notification.php';
			}
			?>
		</div>
		<?php
	} else {

	}
	?>
</div>
