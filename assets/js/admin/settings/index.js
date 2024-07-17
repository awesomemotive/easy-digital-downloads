import { recaptureRemoteInstall } from './recapture';
import './gateways/paypal';

/**
 * Settings screen JS
 */
const EDD_Settings = {
	init: function() {
		this.general();
		this.misc();
		this.gateways();
		this.emails();
	},

	general: function() {
		const edd_color_picker = $( '.edd-color-picker' );

		if ( edd_color_picker.length ) {
			edd_color_picker.wpColorPicker();
		}

		var file_frame;
		window.formfield = '';

		$( document.body ).on( 'click', '.edd_settings_upload_button', function( e ) {
			e.preventDefault();

			const button = $( this );
			window.formfield = $( button.data('input') );

			// If the media frame already exists, reopen it.
			if ( file_frame ) {
				//file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
				file_frame.open();
				return;
			}

			// Create the media frame.
			file_frame = wp.media.frames.file_frame = wp.media( {
				title: button.data( 'uploader_title' ),
				library: { type: 'image' },
				button: { text: button.data( 'uploader_button_text' ) },
				multiple: false,
			} );

			file_frame.on( 'menu:render:default', function( view ) {
				// Store our views in an object.
				const views = {};

				// Unset default menu items
				view.unset( 'library-separator' );
				view.unset( 'gallery' );
				view.unset( 'featured-image' );
				view.unset( 'embed' );
				view.unset( 'playlist' );
				view.unset( 'video-playlist' );

				// Initialize the views in our view object.
				view.set( views );
			} );

			// When an image is selected, run a callback.
			file_frame.on( 'select', function() {
				const selection = file_frame.state().get( 'selection' );
				selection.each( function( attachment, index ) {
					attachment = attachment.toJSON();
					window.formfield.val( attachment.url );
				} );
			} );

			// Finally, open the modal
			file_frame.open();
		} );

		var file_frame;
		window.formfield = '';
	},

	misc: function() {
		const downloadMethod = $( 'select[name="edd_settings[download_method]"]' ),
			symlink = downloadMethod.parent().parent().next(),
			telemetry = $( 'input[name="edd_settings[allow_tracking]"]' );

		// Hide Symlink option if Download Method is set to Direct
		if ( downloadMethod.val() === 'direct' ) {
			symlink.css( 'opacity', '0.4' );
			symlink.find( 'input' ).prop( 'checked', false ).prop( 'disabled', true );
		}

		// Toggle download method option
		downloadMethod.on( 'change', function() {
			if ( $( this ).val() === 'direct' ) {
				symlink.css( 'opacity', '0.4' );
				symlink.find( 'input' ).prop( 'checked', false ).prop( 'disabled', true );
			} else {
				symlink.find( 'input' ).prop( 'disabled', false );
				symlink.css( 'opacity', '1' );
			}
		} );

		telemetry.on( 'change', function() {
			$('.allow_tracking.edd-heart').toggleClass( 'edd-hidden' );
		} );
	},

	gateways: function() {
		$( '#edd-payment-gateways input[type="checkbox"]' ).on( 'change', function() {
			const gateway = $( this ),
				gateway_key = gateway.data( 'gateway-key' ),
				default_gateway = $( '#edd_settings\\[default_gateway\\]' ),
				option = default_gateway.find( 'option[value="' + gateway_key + '"]' );

			// Toggle enable/disable based
			option.prop( 'disabled', function( i, v ) {
				return ! v;
			} );

			// Maybe deselect
			if ( option.prop( 'selected' ) ) {
				option.prop( 'selected', false );
			}

			default_gateway.trigger( 'chosen:updated' );
		} );
	},

	emails: function() {
		$('#edd-recapture-connect').on('click', function(e) {
			e.preventDefault();
			$(this).html( edd_vars.wait + ' <span class="edd-loading"></span>' );
			document.body.style.cursor = 'wait';
			recaptureRemoteInstall();
		});

		/**
		 * Email Summaries
		 */

			// Toggle Email Recipient option
			const emailSummaryRecipient           = $( 'select[name="edd_settings[email_summary_recipient]"]' ),
			    emailSummaryRecipientInitialValue = emailSummaryRecipient.val(),
				emailSummaryCustomRecipients      = $( 'textarea[name="edd_settings[email_summary_custom_recipients]"]' ).parents( 'tr' ),
				emailSummarySaveChangesNotice     = $( '#edd-send-test-summary-save-changes-notice' ),
				emailSummaryTestButton            = $( '#edd-send-test-summary' ),
				emailSummaryTestNotice            = $( '#edd-send-test-summary-notice' );

			emailSummaryRecipient.on( 'change', function() {
				emailSummaryCustomRecipients.toggleClass( 'hidden' );
				emailSummaryTestButton.removeClass( 'hidden updated-message' );
				emailSummaryTestNotice.empty();
				emailSummarySaveChangesNotice.empty();

				if ( emailSummaryRecipientInitialValue !== emailSummaryRecipient.val() ) {
					emailSummaryTestButton.addClass( 'hidden' );
					emailSummarySaveChangesNotice.html( '<div class="notice notice-info"><p>' + edd_vars.test_email_save_changes + '</p></div>' );
				}

			} );

			// Send test email.

			emailSummaryTestButton.on( 'click', function( e ) {
				e.preventDefault();

				$.ajax( {
					type: 'GET',
					dataType: 'json',
					url: ajaxurl,
					data: {
						action: 'edd_send_test_email_summary',
					},
					beforeSend: function() {
						emailSummaryTestNotice.empty();
						emailSummaryTestButton.addClass( 'updating-message' ).prop( 'disabled', true );
					},
					success: function( data ) {
						if ( 'error' == data.status ) {
							emailSummaryTestNotice.html( '<div class="updated ' + data.status + '"><p>' + data.message + '</p></div>' );
						} else {
							emailSummaryTestButton.addClass( 'updated-message' );
							setTimeout( function() {
								emailSummaryTestButton.removeClass( 'updated-message' );
							}, 3000 );
						}
					},
				} ).fail( function( response ) {
					if ( window.console && window.console.log ) {
						console.log( response );
					}
				} ).done( function( response ) {
					emailSummaryTestButton.removeClass( 'updating-message' ).prop( 'disabled', false );
				} );


			} );
	}
};

jQuery( document ).ready( function( $ ) {
	EDD_Settings.init();
} );
