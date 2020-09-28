/**
 * Tools screen JS
 */
const EDD_Tools = {

	init: function() {
		this.revoke_api_key();
		this.regenerate_api_key();
		this.create_api_key();
		this.recount_stats();
	},

	revoke_api_key: function() {
		$( document.body ).on( 'click', '.edd-revoke-api-key', function( e ) {
			return confirm( edd_vars.revoke_api_key );
		} );
	},
	regenerate_api_key: function() {
		$( document.body ).on( 'click', '.edd-regenerate-api-key', function( e ) {
			return confirm( edd_vars.regenerate_api_key );
		} );
	},
	create_api_key: function() {
		$( document.body ).on( 'submit', '#api-key-generate-form', function( e ) {
			const input = $( 'input[type="text"][name="user_id"]' );

			input.css( 'border-color', '#ddd' );

			const user_id = input.val();
			if ( user_id.length < 1 || user_id === 0 ) {
				input.css( 'border-color', '#ff0000' );
				return false;
			}
		} );
	},
	recount_stats: function() {
		$( document.body ).on( 'change', '#recount-stats-type', function() {
			const export_form = $( '#edd-tools-recount-form' ),
				selected_type = $( 'option:selected', this ).data( 'type' ),
				submit_button = $( '#recount-stats-submit' ),
				products = $( '#tools-product-dropdown' );

			// Reset the form
			export_form.find( '.notice-wrap' ).remove();
			submit_button.attr( 'disabled', false ).removeClass( 'updated-message' );
			products.hide();
			$( '.edd-recount-stats-descriptions span' ).hide();

			if ( 'recount-download' === selected_type ) {
				products.show();
				products.find( '.edd-select-chosen' ).css( 'width', 'auto' );
			} else if ( 'reset-stats' === selected_type ) {
				export_form.append( '<div class="notice-wrap"></div>' );
				const notice_wrap = export_form.find( '.notice-wrap' );
				notice_wrap.html( '<div class="notice notice-warning"><p><input type="checkbox" id="confirm-reset" name="confirm_reset_store" value="1" /> <label for="confirm-reset">' + edd_vars.reset_stats_warn + '</label></p></div>' );

				$( '#recount-stats-submit' ).attr( 'disabled', true );
			} else {
				products.hide();
				products.val( 0 );
			}

			$( '#' + selected_type ).show();
		} );

		$( document.body ).on( 'change', '#confirm-reset', function() {
			const checked = $( this ).is( ':checked' );
			if ( checked ) {
				$( '#recount-stats-submit' ).attr( 'disabled', false );
			} else {
				$( '#recount-stats-submit' ).attr( 'disabled', true );
			}
		} );

		$( '#edd-tools-recount-form' ).submit( function( e ) {
			e.preventDefault();

			const selection = $( '#recount-stats-type' ).val(),
				export_form = $( this ),
				selected_type = $( 'option:selected', this ).data( 'type' );

			if ( 'reset-stats' === selected_type ) {
				const is_confirmed = $( '#confirm-reset' ).is( ':checked' );
				if ( is_confirmed ) {
					return true;
				}
				has_errors = true;
			}

			export_form.find( '.notice-wrap' ).remove();
			export_form.append( '<div class="notice-wrap"></div>' );

			var notice_wrap = export_form.find( '.notice-wrap' ),
				has_errors = false;

			if ( null === selection || 0 === selection ) {
				// Needs to pick a method edd_vars.batch_export_no_class
				notice_wrap.html( '<div class="updated error"><p>' + edd_vars.batch_export_no_class + '</p></div>' );
				has_errors = true;
			}

			if ( 'recount-download' === selected_type ) {
				const selected_download = $( 'select[name="download_id"]' ).val();
				if ( selected_download === 0 ) {
					// Needs to pick download edd_vars.batch_export_no_reqs
					notice_wrap.html( '<div class="updated error"><p>' + edd_vars.batch_export_no_reqs + '</p></div>' );
					has_errors = true;
				}
			}

			if ( has_errors ) {
				export_form.find( 'button:disabled' ).attr( 'disabled', false ).removeClass( 'updated-message' );
				return false;
			}
		} );
	},
};

jQuery( document ).ready( function( $ ) {
	EDD_Tools.init();
} );
