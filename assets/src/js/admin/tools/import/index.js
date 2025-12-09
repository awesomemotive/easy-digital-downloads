/**
 * Import screen JS
 */
var EDD_Import = {

	init: function() {
		this.submit();
	},

	submit: function() {
		const self = this;

		$( '.edd-import-form' ).ajaxForm( {
			beforeSubmit: self.before_submit,
			success: self.success,
			complete: self.complete,
			dataType: 'json',
			error: self.error,
		} );
	},

	before_submit: function( arr, form, options ) {
		form.find( '.notice-wrap' ).remove();
		form.append( '<div class="notice-wrap"><div class="edd-progress"><div></div></div></div>' );

		//check whether client browser fully supports all File API
		if ( window.File && window.FileReader && window.FileList && window.Blob ) {

			// HTML5 File API is supported by browser

		} else {
			const import_form = $( '.edd-import-form' ).find( '.edd-progress' ).parent().parent();
			const notice_wrap = import_form.find( '.notice-wrap' );

			import_form.find( '.button:disabled' ).attr( 'disabled', false );

			//Error for older unsupported browsers that doesn't support HTML5 File API
			notice_wrap.html( '<div class="update error"><p>' + edd_vars.unsupported_browser + '</p></div>' );
			return false;
		}
	},

	success: function( responseText, statusText, xhr, form ) {},

	complete: function( xhr ) {
		const self = $( this ),
			response = jQuery.parseJSON( xhr.responseText );

		if ( response.success ) {
			const form = $( '.edd-import-form .notice-wrap' ).parent();

			form.find( '.edd-import-file-wrap,.notice-wrap' ).remove();
			form.find( '.edd-import-options' ).slideDown();

			// Show column mapping
			let select = form.find( 'select.edd-import-csv-column' ),
				row = select.parents( 'tr' ).first(),
				options = '',
				columns = response.data.columns.sort( function( a, b ) {
					if ( a < b ) {
						return -1;
					}
					if ( a > b ) {
						return 1;
					}
					return 0;
				} );

			$.each( columns, function( key, value ) {
				options += '<option value="' + value + '">' + value + '</option>';
			} );

			select.append( options );

			select.on( 'change', function() {
				const key = $( this ).val();

				if ( ! key ) {
					$( this ).parent().next().html( '' );
				} else if ( false !== response.data.first_row[ key ] ) {
					$( this ).parent().next().html( response.data.first_row[ key ] );
				} else {
					$( this ).parent().next().html( '' );
				}
			} );

			$.each( select, function() {
				$( this ).val( $( this ).attr( 'data-field' ) ).change();
			} );

			$( document.body ).on( 'click', '.edd-import-proceed', function( e ) {
				e.preventDefault();

				form.find( '.edd-import-proceed.button-primary' ).addClass( 'updating-message' );
				form.append( '<div class="notice-wrap"><div class="edd-progress"><div></div></div></div>' );

				response.data.mapping = form.serialize();

				EDD_Import.process_step( 1, response.data, self );
			} );
		} else {
			EDD_Import.error( xhr );
		}
	},

	error: function( xhr ) {
		// Something went wrong. This will display error on form

		const response = jQuery.parseJSON( xhr.responseText );
		const import_form = $( '.edd-import-form' ).find( '.edd-progress' ).parent().parent();
		const notice_wrap = import_form.find( '.notice-wrap' );

		import_form.find( '.button:disabled' ).attr( 'disabled', false );

		if ( response.data.error ) {
			notice_wrap.html( '<div class="update error"><p>' + response.data.error + '</p></div>' );
		} else {
			notice_wrap.remove();
		}
	},

	process_step: function( step, import_data, self ) {
		$.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: {
				form: import_data.form,
				nonce: import_data.nonce,
				class: import_data.class,
				upload: import_data.upload,
				mapping: import_data.mapping,
				action: 'edd_do_ajax_import',
				step: step,
			},
			dataType: 'json',
			success: function( response ) {
				if ( 'done' === response.data.step || response.data.error ) {
					// We need to get the actual in progress form, not all forms on the page
					const import_form = $( '.edd-import-form' ).find( '.edd-progress' ).parent().parent();
					const notice_wrap = import_form.find( '.notice-wrap' );

					import_form.find( '.button:disabled' ).attr( 'disabled', false );

					if ( response.data.error ) {
						notice_wrap.html( '<div class="update error"><p>' + response.data.error + '</p></div>' );
					} else {
						import_form.find( '.edd-import-options' ).hide();
						$( 'html, body' ).animate( {
							scrollTop: import_form.parent().offset().top,
						}, 500 );

						notice_wrap.html( '<div class="updated"><p>' + response.data.message + '</p></div>' );
					}
				} else {
					$( '.edd-progress div' ).animate( {
						width: response.data.percentage + '%',
					}, 50, function() {
						// Animation complete.
					} );

					EDD_Import.process_step( parseInt( response.data.step ), import_data, self );
				}
			},
		} ).fail( function( response ) {
			if ( window.console && window.console.log ) {
				console.log( response );
			}
		} );
	},
};

jQuery( document ).ready( function( $ ) {
	EDD_Import.init();
} );
