/**
 * Internal dependencies.
 */
import { getChosenVars } from 'utils/chosen.js';
import { edd_attach_tooltips } from 'admin/components/tooltips';
import './bulk-edit.js';

/**
 * Download Configuration Metabox
 */
var EDD_Download_Configuration = {
	init: function() {
		this.add();
		this.move();
		this.remove();
		this.type();
		this.prices();
		this.files();
		this.updatePrices();
		this.showAdvanced();
	},
	clone_repeatable: function( row ) {
		// Retrieve the highest current key
		let key = 1;
		let highest = 1;
		row.parent().find( '.edd_repeatable_row' ).each( function() {
			const current = $( this ).data( 'key' );
			if ( parseInt( current ) > highest ) {
				highest = current;
			}
		} );
		key = highest += 1;

		const clone = row.clone();

		clone.removeClass( 'edd_add_blank' );

		clone.attr( 'data-key', key );
		clone.find( 'input, select, textarea' ).val( '' ).each( function() {
			let elem = $( this ),
				name = elem.attr( 'name' ),
				id = elem.attr( 'id' );

			if ( name ) {
				name = name.replace( /\[(\d+)\]/, '[' + parseInt( key ) + ']' );
				elem.attr( 'name', name );
			}

			elem.attr( 'data-key', key );

			if ( typeof id !== 'undefined' ) {
				id = id.replace( /(\d+)/, parseInt( key ) );
				elem.attr( 'id', id );
			}
		} );

		/** manually update any select box values */
		clone.find( 'select' ).each( function() {
			$( this ).val( row.find( 'select[name="' + $( this ).attr( 'name' ) + '"]' ).val() );
		} );

		/** manually uncheck any checkboxes */
		clone.find( 'input[type="checkbox"]' ).each( function() {
			// Make sure checkboxes are unchecked when cloned
			const checked = $( this ).is( ':checked' );
			if ( checked ) {
				$( this ).prop( 'checked', false );
			}

			// reset the value attribute to 1 in order to properly save the new checked state
			$( this ).val( 1 );
		} );

		clone.find( 'span.edd_price_id' ).each( function() {
			$( this ).text( parseInt( key ) );
		} );

		clone.find( 'input.edd_repeatable_index' ).each( function() {
			$( this ).val( parseInt( $( this ).data( 'key' ) ) );
		} );

		clone.find( 'span.edd_file_id' ).each( function() {
			$( this ).text( parseInt( key ) );
		} );

		clone.find( '.edd_repeatable_default_input' ).each( function() {
			$( this ).val( parseInt( key ) ).removeAttr( 'checked' );
		} );

		clone.find( '.edd_repeatable_condition_field' ).each( function() {
			$( this ).find( 'option:eq(0)' ).prop( 'selected', 'selected' );
		} );

		clone.find( 'label' ).each( function () {
			var labelFor = $( this ).attr( 'for' );
			if ( labelFor ) {
				$( this ).attr( 'for', labelFor.replace( /(\d+)/, parseInt( key ) ) );
			}
		} );

		// Remove Chosen elements
		clone.find( '.search-choice' ).remove();
		clone.find( '.chosen-container' ).remove();
		edd_attach_tooltips( clone.find( '.edd-help-tip' ) );

		return clone;
	},

	add: function() {
		$( document.body ).on( 'click', '.edd_add_repeatable', function( e ) {
			e.preventDefault();

			const button = $( this ),
				row = button.closest( '.edd_repeatable_table' ).find( '.edd_repeatable_row' ).last(),
				clone = EDD_Download_Configuration.clone_repeatable( row );

			clone.insertAfter( row ).find( 'input, textarea, select' ).filter( ':visible' ).eq( 0 ).focus();

			// Setup chosen fields again if they exist
			clone.find( '.edd-select-chosen' ).each( function() {
				const el = $( this );
				el.chosen( getChosenVars( el ) );
			} );
			clone.find( '.edd-select-chosen' ).css( 'width', '100%' );
			clone.find( '.edd-select-chosen .chosen-search input' ).attr( 'placeholder', edd_vars.search_placeholder );
		} );
	},

	move: function() {
		$( '.edd_repeatable_table .edd-repeatables-wrap' ).sortable( {
			axis: 'y',
			handle: '.edd-draghandle-anchor',
			items: '.edd_repeatable_row',
			cursor: 'move',
			tolerance: 'pointer',
			containment: 'parent',
			distance: 2,
			opacity: 0.7,
			scroll: true,

			update: function() {
				let count = 0;
				$( this ).find( '.edd_repeatable_row' ).each( function() {
					$( this ).find( 'input.edd_repeatable_index' ).each( function() {
						$( this ).val( count );
					} );
					count++;
				} );
			},
			start: function( e, ui ) {
				ui.placeholder.height( ui.item.height() - 2 );
			},
		} );
	},

	remove: function() {
		$( document.body ).on( 'click', '.edd-remove-row, .edd_remove_repeatable', function( e ) {
			e.preventDefault();

			let row = $( this ).parents( '.edd_repeatable_row' ),
				count = row.parent().find( '.edd_repeatable_row' ).length,
				type = $( this ).data( 'type' ),
				repeatable = 'div.edd_repeatable_' + type + 's',
				focusElement,
				focusable,
				firstFocusable;

			// Set focus on next element if removing the first row. Otherwise set focus on previous element.
			if ( $( this ).is( '.ui-sortable .edd_repeatable_row:first-child .edd-remove-row, .ui-sortable .edd_repeatable_row:first-child .edd_remove_repeatable' ) ) {
				focusElement = row.next( '.edd_repeatable_row' );
			} else {
				focusElement = row.prev( '.edd_repeatable_row' );
			}

			focusable = focusElement.find( 'select, input, textarea, button' ).filter( ':visible' );
			firstFocusable = focusable.eq( 0 );

			if ( type === 'price' ) {
				const price_row_id = row.data( 'key' );
				/** remove from price condition */
				$( '.edd_repeatable_condition_field option[value="' + price_row_id + '"]' ).remove();
			}

			if ( count > 1 ) {
				$( 'input, select', row ).val( '' );
				row.fadeOut( 'fast' ).remove();
				firstFocusable.focus();
			} else {
				switch ( type ) {
					case 'price' :
						alert( edd_vars.one_price_min );
						break;
					case 'file' :
						$( 'input, select', row ).val( '' );
						break;
					default:
						alert( edd_vars.one_field_min );
						break;
				}
			}

			/* re-index after deleting */
			$( repeatable ).each( function( rowIndex ) {
				$( this ).find( 'input, select' ).each( function() {
					let name = $( this ).attr( 'name' );
					name = name.replace( /\[(\d+)\]/, '[' + rowIndex + ']' );
					$( this ).attr( 'name', name ).attr( 'id', name );
				} );
			} );
		} );
	},

	type: function() {
		$( document.body ).on( 'change', '#_edd_product_type', function( e ) {
			const edd_products = $( '#edd_products' ),
				edd_download_files = $( '#edd_download_files' ),
				edd_download_limit_wrap = $( '#edd_download_limit_wrap' );

			if ( 'bundle' === $( this ).val() ) {
				edd_products.show();
				edd_download_files.hide();
				edd_download_limit_wrap.hide();
			} else {
				edd_products.hide();
				edd_download_files.show();
				edd_download_limit_wrap.show();
			}
		} );
	},

	prices: function() {
		$( document.body ).on( 'change', '#edd_variable_pricing', function( e ) {
			const checked = $( this ).is( ':checked' ),
				single = $( '#edd_regular_price_field' ),
				variable = $( '#edd_variable_price_fields, .edd_repeatable_table .pricing' ),
				bundleRow = $( '.edd-bundled-product-row, .edd-repeatable-row-standard-fields' );

			if ( checked ) {
				single.hide();
				variable.show();
				bundleRow.addClass( 'has-variable-pricing' );
			} else {
				single.show();
				variable.hide();
				bundleRow.removeClass( 'has-variable-pricing' );
			}
		} );
	},

	files: function() {
		var file_frame;
		window.formfield = '';

		$( document.body ).on( 'click', '.edd_upload_file_button', function( e ) {
			e.preventDefault();

			const button = $( this );

			window.formfield = button.closest( '.edd_repeatable_upload_wrapper' );

			// If the media frame already exists, reopen it.
			if ( file_frame ) {
				file_frame.open();
				return;
			}

			// Create the media frame.
			file_frame = wp.media.frames.file_frame = wp.media( {
				title: button.data( 'uploader-title' ),
				frame: 'post',
				state: 'insert',
				button: { text: button.data( 'uploader-button-text' ) },
				multiple: $( this ).data( 'multiple' ) === '0' ? false : true, // Set to true to allow multiple files to be selected
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

					let selectedSize = 'image' === attachment.type ? $( '.attachment-display-settings .size option:selected' ).val() : false,
						selectedURL = attachment.url,
						selectedName = attachment.title.length > 0 ? attachment.title : attachment.filename;

					if ( selectedSize && typeof attachment.sizes[ selectedSize ] !== 'undefined' ) {
						selectedURL = attachment.sizes[ selectedSize ].url;
					}

					if ( 'image' === attachment.type ) {
						if ( selectedSize && typeof attachment.sizes[ selectedSize ] !== 'undefined' ) {
							selectedName = selectedName + '-' + attachment.sizes[ selectedSize ].width + 'x' + attachment.sizes[ selectedSize ].height;
						} else {
							selectedName = selectedName + '-' + attachment.width + 'x' + attachment.height;
						}
					}

					if ( 0 === index ) {
						// place first attachment in field
						window.formfield.find( '.edd_repeatable_attachment_id_field' ).val( attachment.id );
						window.formfield.find( '.edd_repeatable_thumbnail_size_field' ).val( selectedSize );
						window.formfield.find( '.edd_repeatable_upload_field' ).val( selectedURL );
						window.formfield.find( '.edd_repeatable_name_field' ).val( selectedName );
					} else {
						// Create a new row for all additional attachments
						const row = window.formfield,
							clone = EDD_Download_Configuration.clone_repeatable( row );

						clone.find( '.edd_repeatable_attachment_id_field' ).val( attachment.id );
						clone.find( '.edd_repeatable_thumbnail_size_field' ).val( selectedSize );
						clone.find( '.edd_repeatable_upload_field' ).val( selectedURL );
						clone.find( '.edd_repeatable_name_field' ).val( selectedName );
						clone.insertAfter( row );
					}
				} );
			} );

			// Finally, open the modal
			file_frame.open();
		} );

		// @todo Break this out and remove jQuery.
		$( '.edd_repeatable_upload_field' )
			.on( 'focus', function() {
				const input = $( this );

				input.data( 'originalFile', input.val() );
			} )
			.on( 'change', function() {
				const input = $( this );
				const originalFile = input.data( 'originalFile' );

				if ( originalFile !== input.val() ) {
					input
						.closest( '.edd-repeatable-row-standard-fields' )
						.find( '.edd_repeatable_attachment_id_field' )
						.val( 0 );
				}
			} );

		var file_frame;
		window.formfield = '';
	},

	updatePrices: function() {
		$( '#edd_price_fields' ).on( 'keyup', '.edd_variable_prices_name', function() {
			const key = $( this ).parents( '.edd_repeatable_row' ).data( 'key' ),
				name = $( this ).val(),
				field_option = $( '.edd_repeatable_condition_field option[value=' + key + ']' );

			if ( field_option.length > 0 ) {
				field_option.text( name );
			} else {
				$( '.edd_repeatable_condition_field' ).append(
					$( '<option></option>' )
						.attr( 'value', key )
						.text( name )
				);
			}
		} );
	},

	showAdvanced: function() {
		// Toggle display of entire custom settings section for a price option
		$( document.body ).on( 'click', '.toggle-custom-price-option-section', function( e ) {
			e.preventDefault();

			const toggle = $( this ),
				  show = toggle.html() === edd_vars.show_advanced_settings ?
					  true :
					  false;

			if ( show ) {
				toggle.html( edd_vars.hide_advanced_settings );
			} else {
				toggle.html( edd_vars.show_advanced_settings );
			}

			const header = toggle.parents( '.edd-repeatable-row-header' );
			header.siblings( '.edd-custom-price-option-sections-wrap' ).slideToggle();

			let first_input;
			if ( show ) {
				first_input = $( ':input:not(input[type=button],input[type=submit],button):visible:first', header.siblings( '.edd-custom-price-option-sections-wrap' ) );
			} else {
				first_input = $( ':input:not(input[type=button],input[type=submit],button):visible:first', header.siblings( '.edd-repeatable-row-standard-fields' ) );
			}
			first_input.focus();
		} );
	}
};

jQuery( document ).ready( function( $ ) {
	EDD_Download_Configuration.init();
} );
