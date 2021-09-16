import { sendwpRemoteInstall, sendwpDisconnect } from './sendwp';
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

		// Settings Upload field JS
		if ( typeof wp === 'undefined' || '1' !== edd_vars.new_media_ui ) {
			// Old Thickbox uploader
			const edd_settings_upload_button = $( '.edd_settings_upload_button' );
			if ( edd_settings_upload_button.length > 0 ) {
				window.formfield = '';

				$( document.body ).on( 'click', edd_settings_upload_button, function( e ) {
					e.preventDefault();
					window.formfield = $( this ).parent().prev();
					window.tbframe_interval = setInterval( function() {
						jQuery( '#TB_iframeContent' ).contents().find( '.savesend .button' ).val( edd_vars.use_this_file ).end().find( '#insert-gallery, .wp-post-thumbnail' ).hide();
					}, 2000 );
					tb_show( edd_vars.add_new_download, 'media-upload.php?TB_iframe=true' );
				} );

				window.edd_send_to_editor = window.send_to_editor;
				window.send_to_editor = function( html ) {
					if ( window.formfield ) {
						imgurl = $( 'a', '<div>' + html + '</div>' ).attr( 'href' );
						window.formfield.val( imgurl );
						window.clearInterval( window.tbframe_interval );
						tb_remove();
					} else {
						window.edd_send_to_editor( html );
					}
					window.send_to_editor = window.edd_send_to_editor;
					window.formfield = '';
					window.imagefield = false;
				};
			}
		} else {
			// WP 3.5+ uploader
			var file_frame;
			window.formfield = '';

			$( document.body ).on( 'click', '.edd_settings_upload_button', function( e ) {
				e.preventDefault();

				const button = $( this );

				window.formfield = $( this ).parent().prev();

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

			// WP 3.5+ uploader
			var file_frame;
			window.formfield = '';
		}
	},

	misc: function() {
		const downloadMethod = $( 'select[name="edd_settings[download_method]"]' ),
			symlink = downloadMethod.parent().parent().next();

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
		$('#edd-sendwp-connect').on('click', function(e) {
			e.preventDefault();
			$(this).html( edd_vars.wait + ' <span class="edd-loading"></span>' );
			document.body.style.cursor = 'wait';
			sendwpRemoteInstall();
		});

		$('#edd-sendwp-disconnect').on('click', function(e) {
			e.preventDefault();
			$(this).html( edd_vars.wait + ' <span class="edd-loading dark"></span>' );
			document.body.style.cursor = 'wait';
			sendwpDisconnect();
		});

		$('#edd-recapture-connect').on('click', function(e) {
			e.preventDefault();
			$(this).html( edd_vars.wait + ' <span class="edd-loading"></span>' );
			document.body.style.cursor = 'wait';
			recaptureRemoteInstall();
		});
	}
};

jQuery( document ).ready( function( $ ) {
	EDD_Settings.init();
} );
