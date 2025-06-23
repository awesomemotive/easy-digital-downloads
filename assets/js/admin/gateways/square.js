/**
 * Square Admin JavaScript
 *
 * Handles Square gateway administration functionality.
 *
 * @package EDD\Gateways\Square
 * @since 3.4.0
 */

(function($) {
'use strict';

const eddSquareAdmin = {
	/**
	 * Initialize admin functionality
	 */
	init: function() {
		this.bindEvents();
	},

	/**
	 * Bind events
	 */
	bindEvents: function() {
		// Handle OAuth connection buttons
		$(document).on('click', '#edd-square-connect', this.initiateConnection.bind(this));
		$(document).on('click', '#edd-square-reconnect', this.reconnectSquare.bind(this));
		$(document).on('click', '#edd-square-disconnect', this.disconnectSquare.bind(this));

		// Handle webhook connection modal buttons.
		$(document).on('click', '#edd-square-register-webhooks', this.registerWebhooks.bind(this));

		// If we're not connected, hide the 'Save Changes' button.
		if ( ! edd_square_admin.is_connected ) {
			$('#submit').hide();
		}
	},

	/**
	 * Initiate OAuth connection
	 */
	initiateConnection: function(event) {
		event.preventDefault();

		// Use EDD test mode state from localized scripts
		const mode = edd_square_admin.is_test_mode ? 'test' : 'live';
		const $button = $(event.target);
		const originalText = $button.text();

		$button.prop('disabled', true).text(edd_square_admin.i18n.connecting);

		$.ajax({
			url: edd_square_admin.ajax_url,
			type: 'POST',
			data: {
				action: 'edd_square_initiate_connection',
				mode: mode,
				nonce: edd_square_admin.nonce
			},
			success: function(response) {
				if (response.success) {
					// Navigate to OAuth URL in the same window
					window.location.href = response.data.oauth_url;
				} else {
					alert(response.data.message || edd_square_admin.i18n.connection_error);
				}
			},
			error: function() {
				alert(edd_square_admin.i18n.connection_error);
				$button.prop('disabled', false).text(originalText);
			}
		});
	},

	/**
	 * Reconnect to Square
	 */
	reconnectSquare: function(event) {
		event.preventDefault();

		const mode = $(event.target).data('mode') || 'test';
		const $link = $(event.target);
		const originalText = $link.text();

		$link.addClass('disabled').text(edd_square_admin.i18n.reconnecting);

		$.ajax({
			url: edd_square_admin.ajax_url,
			type: 'POST',
			data: {
				action: 'edd_square_initiate_connection',
				mode: mode,
				nonce: edd_square_admin.nonce
			},
			success: function(response) {
				if (response.success) {
					// Navigate to OAuth URL in the same window
					window.location.href = response.data.oauth_url;
				} else {
					alert(response.data.message || edd_square_admin.i18n.connection_error);
				}
			},
			error: function() {
				alert(edd_square_admin.i18n.connection_error);
				$link.removeClass('disabled').text(originalText);
			}
		});
	},

	/**
	 * Disconnect Square
	 */
	disconnectSquare: function(event) {
		event.preventDefault();

		// Stop the event from bubbling up to the parent element.
		event.stopPropagation();

		// Confirm the disconnect.
		if (!confirm(edd_square_admin.i18n.disconnect_confirm)) {
			return;
		}

		const $link = $(event.target);
		const originalText = $link.text();

		$link.addClass('disabled').text(edd_square_admin.i18n.disconnecting);

		$.ajax({
			url: edd_square_admin.ajax_url,
			type: 'POST',
			data: {
				action: 'edd_square_disconnect',
				nonce: edd_square_admin.nonce
			},
			success: function(response) {
				if (response.success) {
					alert(response.data.message);
					window.location.reload();
				} else {
					alert(response.data.message || edd_square_admin.i18n.connection_error);
				}
			},
			error: function(response) {
				alert(response.responseJSON.data || edd_square_admin.i18n.connection_error);
				$link.removeClass('disabled').text(originalText);
			}
		});
	},

	/**
	 * Register webhooks
	 */
	registerWebhooks: function(event) {
		event.preventDefault();

		const actionsContainer = $(event.target).closest('.edd-promo-notice__actions'),
			nonce = $(event.target).data('nonce'),
			token = $('#edd-square-personal-access-token').val(),
			spinner = $('#edd-square-webhooks-spinner'),
			message = $('#edd-square-webhooks-message');

		// Clear any messages hide the message container.
		message.html('').addClass('edd-hidden');

		// Show the spinner.
		spinner.removeClass('edd-hidden');

		// Disable the buttons.
		actionsContainer.find('button').prop('disabled', true);

		if ( ! token || ! nonce ) {
			actionsContainer.find('#edd-square-webhooks-message').text('Missing required fields. Refresh the page and try again.').show();
			actionsContainer.find('button').prop('disabled', false);
			spinner.addClass('edd-hidden');
			return;
		}

		$.ajax({
			url: edd_square_admin.ajax_url,
			type: 'POST',
			data: {
				action: 'edd_square_register_webhooks',
				nonce: nonce,
				token: token,
			}
		}).success(function(response) {
			message.html(response.data).removeClass('edd-hidden');
			spinner.addClass('edd-hidden');
			// Show the message for 3 seconds, then hide the spinner and refresh the page.
			setTimeout(function() {
				window.location.reload();
			}, 3000);
		}).fail(function(response) {
			message.html(response.responseJSON.data).removeClass('edd-hidden');
		}).always(function() {
			actionsContainer.find('button').prop('disabled', false);
			spinner.addClass('edd-hidden');
		});
	},
};

// Initialize when document is ready
$(document).ready(function() {
	if (typeof edd_square_admin !== 'undefined') {
		eddSquareAdmin.init();
	}
});

// Make available globally for debugging
window.eddSquareAdmin = eddSquareAdmin;

})(jQuery);
