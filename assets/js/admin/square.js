/**
 * Square Admin JavaScript
 *
 * Handles Square admin interface interactions
 */

(function($) {
	'use strict';

	/**
	 * Square Admin object
	 */
	var SquareAdmin = {

		/**
		 * Initialize
		 */
		init: function() {
			this.bindEvents();
		},

		/**
		 * Bind events
		 */
		bindEvents: function() {
			$(document).on('click', '#edd-square-disconnect', this.handleDisconnect);
		},

		/**
		 * Handle disconnect button click
		 */
		handleDisconnect: function(e) {
			e.preventDefault();

			var $button = $(this);
			var nonce = $button.data('nonce');

			if (!confirm('Are you sure you want to disconnect from Square?')) {
				return;
			}

			$button.prop('disabled', true).text(eddSquareAdmin.strings.disconnecting);

			$.ajax({
				url: eddSquareAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'edd_square_disconnect',
					nonce: nonce
				},
				success: function(response) {
					if (response.success) {
						location.reload();
					} else {
						alert(response.data || eddSquareAdmin.strings.error);
						$button.prop('disabled', false).text('Disconnect from Square');
					}
				},
				error: function() {
					alert(eddSquareAdmin.strings.error);
					$button.prop('disabled', false).text('Disconnect from Square');
				}
			});
		}
	};

	// Initialize when document is ready
	$(document).ready(function() {
		SquareAdmin.init();
	});

})(jQuery);
