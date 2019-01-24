/**
 * Notes
 */
const EDD_Notes = {
	init: function() {
		this.enter_key();
		this.add_note();
		this.remove_note();
	},

	enter_key: function() {
		$( document.body ).on( 'keydown', '#edd-note', function( e ) {
			if ( e.keyCode === 13 && ( e.metaKey || e.ctrlKey ) ) {
				e.preventDefault();
				$( '#edd-add-note' ).click();
			}
		} );
	},

	/**
	 * Ajax handler for adding new notes
	 *
	 * @since 3.0
	 */
	add_note: function() {
		$( '#edd-add-note' ).on( 'click', function( e ) {
			e.preventDefault();

			const edd_button = $( this ),
				edd_note = $( '#edd-note' ),
				edd_notes = $( '.edd-notes' ),
				edd_no_notes = $( '.edd-no-notes' ),
				edd_spinner = $( '.edd-add-note .spinner' ),
				edd_note_nonce = $( '#edd_note_nonce' );

			const postData = {
				action: 'edd_add_note',
				nonce: edd_note_nonce.val(),
				object_id: edd_button.data( 'object-id' ),
				object_type: edd_button.data( 'object-type' ),
				note: edd_note.val(),
			};

			if ( postData.note ) {
				edd_button.prop( 'disabled', true );
				edd_spinner.css( 'visibility', 'visible' );

				$.ajax( {
					type: 'POST',
					data: postData,
					url: ajaxurl,
					success: function( response ) {
						let res = wpAjax.parseAjaxResponse( response );
						res = res.responses[ 0 ];

						edd_notes.append( res.data );
						edd_no_notes.hide();
						edd_button.prop( 'disabled', false );
						edd_spinner.css( 'visibility', 'hidden' );
						edd_note.val( '' );
					},
				} ).fail( function( data ) {
					if ( window.console && window.console.log ) {
						console.log( data );
					}
					edd_button.prop( 'disabled', false );
					edd_spinner.css( 'visibility', 'hidden' );
				} );
			} else {
				const border_color = edd_note.css( 'border-color' );

				edd_note.css( 'border-color', 'red' );

				setTimeout( function() {
					edd_note.css( 'border-color', border_color );
				}, userInteractionInterval );
			}
		} );
	},

	/**
	 * Ajax handler for deleting existing notes
	 *
	 * @since 3.0
	 */
	remove_note: function() {
		$( document.body ).on( 'click', '.edd-delete-note', function( e ) {
			e.preventDefault();

			const edd_link = $( this ),
				edd_notes = $( '.edd-note' ),
				edd_note = edd_link.parents( '.edd-note' ),
				edd_no_notes = $( '.edd-no-notes' ),
				edd_note_nonce = $( '#edd_note_nonce' );

			if ( confirm( edd_vars.delete_note ) ) {
				const postData = {
					action: 'edd_delete_note',
					nonce: edd_note_nonce.val(),
					note_id: edd_link.data( 'note-id' ),
				};

				edd_note.addClass( 'deleting' );

				$.ajax( {
					type: 'POST',
					data: postData,
					url: ajaxurl,
					success: function( response ) {
						if ( '1' === response ) {
							edd_note.remove();
						}

						if ( edd_notes.length === 1 ) {
							edd_no_notes.show();
						}

						return false;
					},
				} ).fail( function( data ) {
					if ( window.console && window.console.log ) {
						console.log( data );
					}
					edd_note.removeClass( 'deleting' );
				} );
				return true;
			}
		} );
	},
};

jQuery( document ).ready( function( $ ) {
	EDD_Notes.init();
} );
