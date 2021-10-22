<?php
/**
 * Displays a single notification.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 *
 * @var \EDD\Models\Notification $notification
 */
?>
<div class="edd-notification">
	<div class="edd-notification--header">
		<h4 class="edd-notification--title">
			<?php echo esc_html( $notification->title ); ?>
		</h4>

		<div class="edd-notification--date">
			<?php
			/* Translators: %s - a length of time (e.g. "1 second") */
			echo esc_html( sprintf( __( '%s ago', 'easy-digital-downloads' ), human_time_diff( strtotime( $notification->date_created ) ) ) );
			?>
		</div>
	</div>

	<div class="edd-notification--content">
		<?php echo wp_kses_post( $notification->content ); ?>
	</div>

	<div class="edd-notification--actions">
		<button type="button" class="button edd-notification--dismiss" data-id="<?php echo esc_attr( $notification->id ); ?>">
			<?php esc_html_e( 'Dismiss', 'easy-digital-downloads' ); ?>
		</button>
	</div>
</div>
