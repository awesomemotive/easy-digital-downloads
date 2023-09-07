; ( function ( document, $, undefined ) {
	'use strict';

	var custom_uploader,
		targetInputClass = '.upload-image-id',
		previewClass = 'upload-image-preview',
		target_input,
		TermImage = {};

	TermImage.upload = function () {
		$( '.upload-image' ).on( 'click.upload', _uploadMedia );
		$( '.delete-image' ).on( 'click.delete', _deleteMedia );
		$( '#addtag #submit' ).on( 'click.term', _termImages );

		function _uploadMedia ( e ) {
			e.preventDefault();
			target_input = $( this ).prev( targetInputClass );

			//If the uploader object has already been created, reopen the dialog
			if ( custom_uploader ) {
				custom_uploader.reset();
			}

			//Extend the wp.media object
			custom_uploader = wp.media.frames.file_frame = wp.media( {
				title: ( [ TermImage.params.text ] ),
				button: {
					text: ( [ TermImage.params.text ] )
				},
				multiple: false,
				library: { type: 'image' }
			} );

			//When a file is selected, grab the URL and set it as the text field's value
			custom_uploader.on( 'select', function () {

				var attachment = custom_uploader.state().get( 'selection' ).first().toJSON(),
					preview = $( target_input ).prevAll( '.' + previewClass ),
					deleteButton = $( target_input ).siblings( '.delete-image' ),
					previewImage = $( '<div />', {
						class: previewClass
					} ).append( $( '<img/>', {
						style: 'max-width:300px;',
						src: _getImageURL( attachment ),
						alt: ''
					} ) );
				$( target_input ).val( attachment.id );
				if ( preview.length ) {
					preview.remove();
				}
				$( target_input ).before( previewImage );
				$( deleteButton ).show();
			} );

			//Open the uploader dialog
			custom_uploader.open();
		}

		/**
		 * Get the medium size image, if it exists.
		 * @param image
		 * @return {*}
		 * @private
		 */
		function _getImageURL ( image ) {
			return image.sizes.medium ? image.sizes.medium.url : image.url;
		}

		function _deleteMedia ( e ) {
			e.preventDefault();
			target_input = $( this ).prevAll( targetInputClass );
			var previewView = $( this ).prevAll( '.' + previewClass );

			$( target_input ).val( '' );
			$( previewView ).remove();
			$( this ).hide();
		}

		function _termImages ( e ) {
			e.preventDefault();
			var submitButton = $( this ).parentsUntil( '#addtag' ),
				previewView = submitButton.siblings( '.term-image-wrap' ).children( '.' + previewClass ),
				clearInput = submitButton.siblings( '.term-image-wrap' ).children( targetInputClass );

			if ( $( previewView ).length && $( submitButton ).length ) {
				$( previewView ).delay( 1000 ).fadeOut( 200, function () {
					$( this ).remove();
					$( clearInput ).val( '' );
				} );
			}
		}
	};

	TermImage.params = typeof EDDTermImages === 'undefined' ? '' : EDDTermImages;
	if ( typeof TermImage.params !== 'undefined' ) {
		TermImage.upload();
	}

} )( document, jQuery );
