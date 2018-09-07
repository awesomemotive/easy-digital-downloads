/**
 * Settings screen JS
 */
const EDD_Settings = {

	init: function() {
		this.general();
		this.emails();
		this.misc();
	},

	general: function() {
		const edd_color_picker = $( '.edd-color-picker' );

		if ( edd_color_picker.length ) {
			edd_color_picker.wpColorPicker();
		}

		// Settings Upload field JS
		if ( typeof wp === "undefined" || '1' !== edd_vars.new_media_ui ) {
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
					frame: 'post',
					state: 'insert',
					title: button.data( 'uploader_title' ),
					button: {
						text: button.data( 'uploader_button_text' ),
					},
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
				file_frame.on( 'insert', function() {
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

	emails: function() {
		// Show the email template previews
		const email_preview_wrap = $( '#email-preview-wrap' );
		if ( email_preview_wrap.length ) {
			const emailPreview = $( '#email-preview' );
			email_preview_wrap.colorbox( {
				inline: true,
				href: emailPreview,
				width: '80%',
				height: 'auto',
			} );
		}
	},

	misc: function() {
		let downloadMethod = $( 'select[name="edd_settings[download_method]"]' ),
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
};

jQuery( document ).ready( function( $ ) {
	EDD_Settings.init();
} );
