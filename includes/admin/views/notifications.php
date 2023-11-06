<?php
/**
 * Displays a list of notifications.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 * @since     2.11.4
 */
?>
<div
	id="edd-notifications"
	class="edd-hidden"
	x-data
	x-init="function() { if ( 'undefined' !== typeof $store.eddNotifications ) { $el.classList.remove( 'edd-hidden' ); } }"
>
	<div
		class="edd-overlay"
		x-show="$store.eddNotifications.isPanelOpen"
		x-on:click="$store.eddNotifications.closePanel()"
	></div>

	<div
		id="edd-notifications-panel"
		x-show="$store.eddNotifications.isPanelOpen"
		x-transition:enter-start="edd-slide-in"
		x-transition:leave-end="edd-slide-in"
	>
		<div id="edd-notifications-header" tabindex="-1">
			<h3>
				<?php
				echo wp_kses(
					sprintf(
					/* Translators: %s - number of notifications */
						__( '(%s) New Notifications', 'easy-digital-downloads' ),
						'<span x-text="$store.eddNotifications.numberActiveNotifications"></span>'
					),
					array( 'span' => array( 'x-text' => true ) )
				);
				?>
			</h3>

			<button
				type="button"
				class="edd-close"
				x-on:click="$store.eddNotifications.closePanel()"
			>
				<span class="dashicons dashicons-no-alt"></span>
				<span class="screen-reader-text"><?php esc_html_e( 'Close panel', 'easy-digital-downloads' ); ?></span>
			</button>
		</div>

		<div id="edd-notifications-body">
			<template x-if="$store.eddNotifications.notificationsLoaded && $store.eddNotifications.activeNotifications.length">
				<template x-for="(notification, index) in $store.eddNotifications.activeNotifications" :key="notification.id">
					<div class="edd-notification">
						<div class="edd-notification--icon" :class="'edd-notification--icon-' + notification.type">
							<span class="dashicons" :class="'dashicons-' + notification.icon_name"></span>
						</div>

						<div class="edd-notification--body">
							<div class="edd-notification--header">
								<h4 class="edd-notification--title" x-text="notification.title"></h4>

								<div class="edd-notification--date" x-text="notification.relative_date"></div>
							</div>

							<div class="edd-notification--content" x-html="notification.content"></div>

							<div class="edd-notification--actions">
								<template x-for="button in notification.buttons">
									<a
										:href="button.url"
										:class="button.type === 'primary' ? 'button button-primary' : 'button button-secondary'"
										target="_blank"
										x-text="button.text"
									></a>
								</template>

								<button
									type="button"
									class="edd-notification--dismiss"
									x-on:click="$store.eddNotifications.dismiss( $event, index )"
								>
									<?php esc_html_e( 'Dismiss', 'easy-digital-downloads' ); ?>
								</button>
							</div>
						</div>
					</div>
				</template>
			</template>

			<template x-if="$store.eddNotifications.notificationsLoaded && ! $store.eddNotifications.activeNotifications.length">
				<div id="edd-notifications-none">
					<?php esc_html_e( 'You have no new notifications.', 'easy-digital-downloads' ); ?>
				</div>
			</template>

			<template x-if="! $store.eddNotifications.notificationsLoaded">
				<div>
					<?php esc_html_e( 'Loading notifications...', 'easy-digital-downloads' ); ?>
				</div>
			</template>
		</div>
	</div>
</div>
