/* global edd_vars, ajaxurl, wpAjax, postboxes, pagenow */
jQuery(document).ready(function ($) {

	// Variables for setting up the typing timer
	// Time in ms, Slow - 521ms, Moderate - 342ms, Fast - 300ms
	var userInteractionInterval = 342,
		typingTimerElements     = '.edd-select-chosen .chosen-search input, .edd-select-chosen .search-field input',
		typingTimer;

	// Cancel user-search.blur when picking a user
	var edd_user_search_mouse_down = false;
	$('.edd_user_search_results').mousedown(function() {
		edd_user_search_mouse_down = true;
	});

	// Tooltips
	var tooltips = $('.edd-help-tip');
	edd_attach_tooltips( tooltips );

	// Chosen vars
	var chosen_vars = {
		disable_search_threshold:  13,
		search_contains:           true,
		inherit_select_classes:    true,
		single_backstroke_delete:  false,
		placeholder_text_single:   edd_vars.one_option,
		placeholder_text_multiple: edd_vars.one_or_more_option,
		no_results_text:           edd_vars.no_results_text
	};

	// Create "global" to store intermediate data.
	var edd_admin_globals = {};

	// Setup Chosen menus
	$('.edd-select-chosen').chosen( chosen_vars );

	$('.edd-select-chosen .chosen-search input').each( function() {

		// Bail if placeholder already set
		if ( $( this ).attr( 'placeholder' ) ) {
			return;
		}

		var selectElem  = $( this ).parent().parent().parent().prev('select.edd-select-chosen'),
			placeholder = selectElem.data('search-placeholder');

		if ( placeholder ) {
			console.log( placeholder );
			$( this ).attr( 'placeholder', placeholder );
		}
	});

	// Add placeholders for Chosen input fields
	$( '.chosen-choices' ).on( 'click', function () {
		var placeholder = $( this ).parent().prev().data('search-placeholder');
		if ( typeof placeholder === "undefined" ) {
			placeholder = edd_vars.type_to_search;
		}
		$( this ).children('li').children('input').attr( 'placeholder', placeholder );
	});

	// Hides the section content.
	$( '.edd-vertical-sections.use-js .section-content' ).hide();

	// Shows the first section's content.
	$( '.edd-vertical-sections.use-js .section-content:first-child' ).show();

	// Makes the 'aria-selected' attribute true for the first section nav item.
	$( '.edd-vertical-sections.use-js .section-nav :first-child' ).attr( 'aria-selected', 'true' );

	// Copies the current section item title to the box header.
	$( '.which-section' ).text( $( '.section-nav :first-child a' ).text() );

	// When a section nav item is clicked.
	$( '.edd-vertical-sections.use-js .section-nav li a' ).on( 'click',
		function( j ) {

			// Prevent the default browser action when a link is clicked.
			j.preventDefault();

			// Get the `href` attribute of the item.
			var them  = $( this ),
				href  = them.attr( 'href' ),
				rents = them.parents( '.edd-vertical-sections' );

			// Hide all section content.
			rents.find( '.section-content' ).hide();

			// Find the section content that matches the section nav item and show it.
			rents.find( href ).show();

			// Set the `aria-selected` attribute to false for all section nav items.
			rents.find( '.section-title' ).attr( 'aria-selected', 'false' );

			// Set the `aria-selected` attribute to true for this section nav item.
			them.parent().attr( 'aria-selected', 'true' );

			// Maybe re-Chosen
			rents.find( 'div.chosen-container' ).css( 'width', '100%' );

			// Copy the current section item title to the box header.
			$( '.which-section' ).text( them.text() );
		}
	); // click()

	// Toggle advanced filters on Orders page.
	$( '.edd-advanced-filters-button' ).on( 'click', function( e ) {

		// Prevnt submit action
		e.preventDefault();

		$( '#edd-advanced-filters' ).toggleClass( 'open' );
	} );

	/**
	 * Download Configuration Metabox
	 */
	var EDD_Download_Configuration = {
		init : function() {
			this.add();
			this.move();
			this.remove();
			this.type();
			this.prices();
			this.files();
			this.updatePrices();
		},
		clone_repeatable : function(row) {

			// Retrieve the highest current key
			var key = highest = 1;
			row.parent().find( '.edd_repeatable_row' ).each(function() {
				var current = $( this ).data( 'key' );
				if ( parseInt( current ) > highest ) {
					highest = current;
				}
			});
			key = highest += 1;

			clone = row.clone();

			clone.removeClass( 'edd_add_blank' );

			clone.attr( 'data-key', key );
			clone.find( 'input, select, textarea' ).val( '' ).each(function() {
				var elem = $( this ),
					name = elem.attr( 'name' ),
					id   = elem.attr( 'id' );

				if ( name ) {
					name = name.replace( /\[(\d+)\]/, '[' + parseInt( key ) + ']');
					elem.attr( 'name', name );
				}

				elem.attr( 'data-key', key );

				if ( typeof id !== 'undefined' ) {
					id = id.replace( /(\d+)/, parseInt( key ) );
					elem.attr( 'id', id );
				}
			});

			/** manually update any select box values */
			clone.find( 'select' ).each(function() {
				$( this ).val( row.find( 'select[name="' + $( this ).attr( 'name' ) + '"]' ).val() );
			});

			/** manually uncheck any checkboxes */
			clone.find( 'input[type="checkbox"]' ).each(function() {

				// Make sure checkboxes are unchecked when cloned
				var checked = $( this ).is(':checked');
				if ( checked ) {
					$( this ).prop('checked', false);
				}

				// reset the value attribute to 1 in order to properly save the new checked state
				$( this ).val(1);
			});

			clone.find( 'span.edd_price_id' ).each(function() {
				$( this ).text( parseInt( key ) );
			});

			clone.find( 'span.edd_file_id' ).each(function() {
				$( this ).text( parseInt( key ) );
			});

			clone.find( '.edd_repeatable_default_input' ).each( function() {
				$( this ).val( parseInt( key ) ).removeAttr('checked');
			});

			clone.find( '.edd_repeatable_condition_field' ).each ( function() {
				$( this ).find( 'option:eq(0)' ).prop( 'selected', 'selected' );
			});

			// Remove Chosen elements
			clone.find( '.search-choice' ).remove();
			clone.find( '.chosen-container' ).remove();
			edd_attach_tooltips(clone.find('.edd-help-tip'));

			return clone;
		},

		add : function() {
			$( document.body ).on( 'click', '.edd_add_repeatable', function(e) {
				e.preventDefault();

				var button = $( this ),
					row    = button.parent().prev().children( '.edd_repeatable_row:last-child' ),
					clone  = EDD_Download_Configuration.clone_repeatable( row );

				clone.insertAfter( row ).find('input, textarea, select').filter(':visible').eq(0).focus();

				// Setup chosen fields again if they exist
				clone.find( '.edd-select-chosen' ).chosen( chosen_vars );
				clone.find( '.edd-select-chosen' ).css( 'width', '100%' );
				clone.find( '.edd-select-chosen .chosen-search input' ).attr( 'placeholder', edd_vars.search_placeholder );
			});
		},

		move : function() {

			$(".edd_repeatable_table .edd-repeatables-wrap").sortable({
				axis:        'y',
				handle:      '.edd-draghandle-anchor',
				items:       '.edd_repeatable_row',
				cursor:      'move',
				tolerance:   'pointer',
				containment: 'parent',
				distance:    2,
				opacity:     0.7,
				scroll:      true,

				update: function() {
					var count  = 0;
					$( this ).find( '.edd_repeatable_row' ).each(function() {
						$( this ).find( 'input.edd_repeatable_index' ).each(function() {
							$( this ).val( count );
						});
						count++;
					});
				},
				start: function(e, ui){
					ui.placeholder.height(ui.item.height()-2);
				}
			});
		},

		remove : function() {
			$( document.body ).on( 'click', '.edd-remove-row, .edd_remove_repeatable', function(e) {
				e.preventDefault();

				var row        = $( this ).parents( '.edd_repeatable_row' ),
					count      = row.parent().find( '.edd_repeatable_row' ).length,
					type       = $( this ).data('type'),
					repeatable = 'div.edd_repeatable_' + type + 's',
					focusElement,
					focusable,
					firstFocusable;

					// Set focus on next element if removing the first row. Otherwise set focus on previous element.
					if ( $( this ).is( '.ui-sortable .edd_repeatable_row:first-child .edd-remove-row, .ui-sortable .edd_repeatable_row:first-child .edd_remove_repeatable' ) ) {
						focusElement  = row.next( '.edd_repeatable_row' );
					} else {
						focusElement  = row.prev( '.edd_repeatable_row' );
					}

					focusable  = focusElement.find( 'select, input, textarea, button' ).filter( ':visible' );
					firstFocusable = focusable.eq(0);

				if ( type === 'price' ) {
					var price_row_id = row.data('key');
					/** remove from price condition */
					$( '.edd_repeatable_condition_field option[value="' + price_row_id + '"]' ).remove();
				}

				if ( count > 1 ) {
					$( 'input, select', row ).val( '' );
					row.fadeOut( 'fast' ).remove();
					firstFocusable.focus();
				} else {
					switch( type ) {
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
				$(repeatable).each( function( rowIndex ) {
					$( this ).find( 'input, select' ).each(function() {
						var name = $( this ).attr( 'name' );
						name = name.replace( /\[(\d+)\]/, '[' + rowIndex+ ']');
						$( this ).attr( 'name', name ).attr( 'id', name );
					});
				});
			});
		},

		type : function() {
			$( document.body ).on( 'change', '#_edd_product_type', function(e) {
				var edd_products            = $( '#edd_products' ),
					edd_download_files      = $( '#edd_download_files' ),
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
			});
		},

		prices : function() {
			$( document.body ).on( 'change', '#edd_variable_pricing', function(e) {
				var checked   = $( this ).is(':checked'),
					single    = $( '#edd_regular_price_field' ),
					variable  = $( '#edd_variable_price_fields, .edd_repeatable_table .pricing' ),
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
			});
		},

		files : function() {
			var file_frame;
			window.formfield = '';

			$( document.body ).on('click', '.edd_upload_file_button', function(e) {
				e.preventDefault();

				var button = $( this );

				window.formfield = button.closest('.edd_repeatable_upload_wrapper');

				// If the media frame already exists, reopen it.
				if ( file_frame ) {
					file_frame.open();
					return;
				}

				// Create the media frame.
				file_frame = wp.media.frames.file_frame = wp.media( {
					title:    button.data( 'uploader-title' ),
					library:  { type: 'image' },
					button:   { text: button.data( 'uploader-button-text' ) },
					multiple: $( this ).data( 'multiple' ) === '0' ? false : true  // Set to true to allow multiple files to be selected
				});

				file_frame.on( 'menu:render:default', function( view ) {
					// Store our views in an object.
					var views = {};

					// Unset default menu items
					view.unset( 'library-separator' );
					view.unset( 'gallery' );
					view.unset( 'featured-image' );
					view.unset( 'embed' );

					// Initialize the views in our view object.
					view.set( views );
				});

				// When an image is selected, run a callback.
				file_frame.on( 'select', function() {

					var selection = file_frame.state().get('selection');
					selection.each( function( attachment, index ) {
						attachment = attachment.toJSON();

						var selectedSize = 'image' === attachment.type ? $('.attachment-display-settings .size option:selected').val() : false,
							selectedURL  = attachment.url,
							selectedName = attachment.title.length > 0 ? attachment.title : attachment.filename;

						if ( selectedSize && typeof attachment.sizes[selectedSize] !== "undefined" ) {
							selectedURL = attachment.sizes[selectedSize].url;
						}

						if ( 'image' === attachment.type ) {
							if ( selectedSize && typeof attachment.sizes[selectedSize] !== "undefined" ) {
								selectedName = selectedName + '-' + attachment.sizes[selectedSize].width + 'x' + attachment.sizes[selectedSize].height;
							} else {
								selectedName = selectedName + '-' + attachment.width + 'x' + attachment.height;
							}
						}

						if ( 0 === index ) {
							// place first attachment in field
							window.formfield.find( '.edd_repeatable_attachment_id_field' ).val( attachment.id );
							window.formfield.find( '.edd_repeatable_thumbnail_size_field').val( selectedSize );
							window.formfield.find( '.edd_repeatable_upload_field' ).val( selectedURL );
							window.formfield.find( '.edd_repeatable_name_field' ).val( selectedName );
						} else {
							// Create a new row for all additional attachments
							var row = window.formfield,
								clone = EDD_Download_Configuration.clone_repeatable( row );

							clone.find( '.edd_repeatable_attachment_id_field' ).val( attachment.id );
							clone.find( '.edd_repeatable_thumbnail_size_field' ).val( selectedSize );
							clone.find( '.edd_repeatable_upload_field' ).val( selectedURL );
							clone.find( '.edd_repeatable_name_field' ).val( selectedName );
							clone.insertAfter( row );
						}
					});
				});

				// Finally, open the modal
				file_frame.open();
			});

			var file_frame;
			window.formfield = '';
		},

		updatePrices: function() {
			$( '#edd_price_fields' ).on( 'keyup', '.edd_variable_prices_name', function() {
				var key = $( this ).parents( '.edd_repeatable_row' ).data( 'key' ),
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
			});
		}
	};

	// Toggle display of entire custom settings section for a price option
	$( document.body ).on( 'click', '.toggle-custom-price-option-section', function(e) {
		e.preventDefault();

		var toggle = $( this ),
			show   = toggle.html() === edd_vars.show_advanced_settings
				? true
				: false;

		if ( show ) {
			toggle.html( edd_vars.hide_advanced_settings );
		} else {
			toggle.html( edd_vars.show_advanced_settings );
		}

		var header = toggle.parents('.edd-repeatable-row-header');
		header.siblings('.edd-custom-price-option-sections-wrap').slideToggle();

		var first_input;
		if ( show ) {
			first_input = $(":input:not(input[type=button],input[type=submit],button):visible:first", header.siblings('.edd-custom-price-option-sections-wrap'));
		} else {
			first_input = $(":input:not(input[type=button],input[type=submit],button):visible:first", header.siblings('.edd-repeatable-row-standard-fields'));
		}
		first_input.focus();
	});

	EDD_Download_Configuration.init();

	//$('#edit-slug-box').remove();

	/**
	 * Date picker
	 *
	 * This juggles a few CSS classes to avoid styling collisions with other
	 * third-party plugins.
	 */
	var edd_datepicker = $( 'input.edd_datepicker' );
	if ( edd_datepicker.length > 0 ) {
		edd_datepicker

			// Disable autocomplete to avoid it covering the calendar
			.attr( 'autocomplete', 'off' )

			// Invoke the datepickers
			.datepicker( {
				dateFormat: edd_vars.date_picker_format,
				beforeShow: function() {
					$( '#ui-datepicker-div' )
						.removeClass( 'ui-datepicker' )
						.addClass( 'edd-datepicker' );
				}
			});
	}

	/**
	 * Sortables
	 *
	 * This makes certain settings sortable, and attempts to stash the results
	 * in the nearest .edd-order input value.
	 */
	var edd_sortables = $( 'ul.edd-sortable-list' );
	if ( edd_sortables.length > 0 ) {
		edd_sortables.sortable({
			axis:        'y',
			items:       'li',
			cursor:      'move',
			tolerance:   'pointer',
			containment: 'parent',
			distance:    2,
			opacity:     0.7,
			scroll:      true,

			start: function(e, ui){
				ui.placeholder.height(ui.item.height());
			},

			/**
			 * When sorting stops, assign the value to the previous input.
			 * This input should be a hidden text field
			 */
			stop: function () {
				var keys = $.map( $( this ).children( 'li' ), function(el) {
					 return $(el).data( 'key' );
				});

				$( this ).prev( 'input.edd-order' ).val( keys );
			}
		});
	}

	/**
	 * Notes
	 */
	var EDD_Notes = {
		init : function() {
			this.enter_key();
			this.add_note();
			this.remove_note();
		},

		enter_key : function() {
			$(document.body).on('keydown', '#edd-note', function(e) {
				if (e.keyCode === 13 && ( e.metaKey || e.ctrlKey ) ) {
					e.preventDefault();
					$('#edd-add-note').click();
				}
			});
		},

		/**
		 * Ajax handler for adding new notes
		 *
		 * @since 3.0
		 */
		add_note : function() {
			$('#edd-add-note').on('click', function(e) {
				e.preventDefault();

				var edd_button     = $( this ),
					edd_note       = $('#edd-note'),
					edd_notes      = $('.edd-notes'),
					edd_no_notes   = $('.edd-no-notes'),
					edd_spinner    = $('.edd-add-note .spinner'),
					edd_note_nonce = $('#edd_note_nonce');

				var postData = {
					action:      'edd_add_note',
					nonce:       edd_note_nonce.val(),
					object_id:   edd_button.data('object-id'),
					object_type: edd_button.data('object-type'),
					note:        edd_note.val()
				};

				if ( postData.note ) {
					edd_button.prop('disabled', true);
					edd_spinner.css('visibility', 'visible');

					$.ajax({
						type: 'POST',
						data: postData,
						url:  ajaxurl,
						success: function (response) {
							var res = wpAjax.parseAjaxResponse( response );
							res = res.responses[0];

							edd_notes.append( res.data );
							edd_no_notes.hide();
							edd_button.prop('disabled', false);
							edd_spinner.css('visibility', 'hidden');
							edd_note.val('');
						}
					}).fail(function (data) {
						if ( window.console && window.console.log ) {
							console.log( data );
						}
						edd_button.prop('disabled', false);
						edd_spinner.css('visibility', 'hidden');
					});

				} else {
					var border_color = edd_note.css('border-color');

					edd_note.css('border-color', 'red');

					setTimeout( function() {
						edd_note.css('border-color', border_color );
					}, userInteractionInterval );
				}
			});
		},

		/**
		 * Ajax handler for deleting existing notes
		 *
		 * @since 3.0
		 */
		remove_note : function() {
			$( document.body ).on('click', '.edd-delete-note', function(e) {
				e.preventDefault();

				var edd_link       = $( this ),
					edd_notes      = $('.edd-note'),
					edd_note       = edd_link.parents( '.edd-note' ),
					edd_no_notes   = $('.edd-no-notes'),
					edd_note_nonce = $('#edd_note_nonce');

				if ( confirm( edd_vars.delete_note ) ) {
					var postData = {
						action:  'edd_delete_note',
						nonce:   edd_note_nonce.val(),
						note_id: edd_link.data('note-id')
					};

					edd_note.addClass('deleting');

					$.ajax({
						type: "POST",
						data: postData,
						url:  ajaxurl,
						success: function (response) {
							if ( '1' === response ) {
								edd_note.remove();
							}

							if ( edd_notes.length === 1 ) {
								edd_no_notes.show();
							}

							return false;
						}
					}).fail(function (data) {
						if ( window.console && window.console.log ) {
							console.log( data );
						}
						edd_note.removeClass('deleting');
					});
					return true;
				}
			});
		}
	};

	EDD_Notes.init();

	/**
	 * Edit payment screen JS
	 */
	var EDD_Edit_Payment = {

		init : function() {
			this.edit_address();
			this.remove_download();
			this.add_download();
			this.change_customer();
			this.new_customer();
			this.edit_price();
			this.recalculate_total();
			this.variable_prices_check();
			this.resend_receipt();
			this.copy_download_link();
		},

		edit_address : function() {

			// Update base state field based on selected base country
			$('select[name="edd-payment-address[0][country]"]').change(function() {
				var select = $( this ),
					data   = {
						action:     'edd_get_shop_states',
						country:    select.val(),
						nonce:      select.data('nonce'),
						field_name: 'edd-payment-address[0][region]'
					};

				$.post(ajaxurl, data, function (response) {
					var state_wrapper = $( '#edd-order-address-state-wrap select, #edd-order-address-state-wrap input' );

					// Remove any chosen containers here too
					$( '#edd-order-address-state-wrap .chosen-container' ).remove();

					if ( 'nostates' === response ) {
						state_wrapper.replaceWith( '<input type="text" name="edd-payment-address[0][region]" value="" class="edd-edit-toggles medium-text"/>' );
					} else {
						state_wrapper.replaceWith( response );
						$( '#edd-order-address-state-wrap select' ).chosen( chosen_vars );
					}
				});

				return false;
			});
		},

		remove_download : function() {

			// Remove a download from a purchase
			$('#edd-order-items').on('click', '.edd-order-remove-download', function() {
				var count = $( document.body ).find( '#edd-order-items > .row:not(.header)' ).length;

				if ( count === 1 ) {
					alert( edd_vars.one_download_min );
					return false;
				}

				if ( confirm( edd_vars.delete_payment_download ) ) {
					var key           = $( this ).data('key'),
						download_id   = $('input[name="edd-payment-details-downloads['+key+'][id]"]').val(),
						price_id      = $('input[name="edd-payment-details-downloads['+key+'][price_id]"]').val(),
						quantity      = $('input[name="edd-payment-details-downloads['+key+'][quantity]"]').val(),
						amount        = $('input[name="edd-payment-details-downloads['+key+'][amount]"]').val(),
						order_item_id = $('input[name="edd-payment-details-downloads['+key+'][order_item_id]"]').val();;

					if ( $('input[name="edd-payment-details-downloads['+key+'][tax]"]') ) {
						var fees = $('input[name="edd-payment-details-downloads['+key+'][tax]"]').val();
					}

					if ( $('input[name="edd-payment-details-downloads['+key+'][fees]"]') ) {
						var fees = $.parseJSON( $('input[name="edd-payment-details-downloads['+key+'][fees]"]').val() );
					}

					var currently_removed  = $('input[name="edd-payment-removed"]').val();
					currently_removed      = $.parseJSON(currently_removed);
					if ( currently_removed.length < 1 ) {
						currently_removed  = {};
					}

					var removed_item       = [ { 'order_item_id' : order_item_id, 'id': download_id, 'price_id': price_id, 'quantity': quantity, 'amount': amount, 'cart_index': key } ];
					currently_removed[key] = removed_item;

					$('input[name="edd-payment-removed"]').val(JSON.stringify(currently_removed));

					$( this ).parent().parent().remove();
					if ( fees && fees.length) {
						$.each( fees, function( key, value ) {
							$('*li[data-fee-id="' + value + '"]').remove();
						});
					}

					// Flag the Downloads section as changed
					$('#edd-payment-downloads-changed').val(1);
					$('.edd-order-payment-recalc-totals').show();
				}
				return false;
			});
		},

		change_customer : function() {

			$('#edd-customer-details').on('click', '.edd-payment-change-customer, .edd-payment-change-customer-cancel', function(e) {
				e.preventDefault();

				var change_customer = $( this ).hasClass('edd-payment-change-customer'),
					cancel          = $( this ).hasClass('edd-payment-change-customer-cancel');

				if ( change_customer ) {
					$('.order-customer-info').hide();
					$('.change-customer').show();
					setTimeout( function() {
						$('.edd-payment-change-customer-input').css('width', '300');
					}, 1 );
				} else if ( cancel) {
					$('.order-customer-info').show();
					$('.change-customer').hide();
				}
			});
		},

		new_customer : function() {

			$('#edd-customer-details').on('click', '.edd-payment-new-customer, .edd-payment-new-customer-cancel', function(e) {
				e.preventDefault();

				var new_customer = $( this ).hasClass('edd-payment-new-customer'),
					cancel       = $( this ).hasClass('edd-payment-new-customer-cancel');

				if ( new_customer ) {
					$('.order-customer-info').hide();
					$('.new-customer').show();
				} else if ( cancel ) {
					$('.order-customer-info').show();
					$('.new-customer').hide();
				}

				var new_customer = $( '#edd-new-customer' );

				if ($('.new-customer').is(":visible")) {
					new_customer.val(1);
				} else {
					new_customer.val(0);
				}
			});
		},

		add_download : function() {

			// Add a New Download from the Add Downloads to Purchase Box
			$('.edd-edit-purchase-element').on('click', '#edd-order-add-download', function(e) {
				e.preventDefault();

				var order_download_select     = $( '#edd_order_download_select' ),
					order_download_quantity   = $( '#edd-order-download-quantity' ),
					order_download_price      = $( '#edd-order-download-price' ),
					order_download_tax        = $( '#edd-order-download-tax' ),
					selected_price_option     = $( '.edd_price_options_select option:selected' );

				var download_id    = order_download_select.val(),
					download_title = order_download_select.find(':selected').text(),
					quantity       = order_download_quantity.val(),
					item_price     = order_download_price.val(),
					item_tax       = order_download_tax.val(),
					price_id       = selected_price_option.val(),
					price_name     = selected_price_option.text();

				if ( download_id < 1 ) {
					return false;
				}

				if ( ! item_price ) {
					item_price = 0;
				}

				item_price = parseFloat( item_price );
				if ( isNaN( item_price ) ) {
					alert( edd_vars.numeric_item_price );
					return false;
				}

				item_tax = parseFloat( item_tax );
				if ( isNaN( item_tax ) ) {
					alert( edd_vars.numeric_item_tax );
					return false;
				}

				if ( isNaN( parseInt( quantity ) ) ) {
					alert( edd_vars.numeric_quantity );
					return false;
				}

				if ( price_name ) {
					download_title = download_title + ' - ' + price_name;
				}

				var count = $('#edd-order-items div.row').length,
					clone = $('#edd-order-items div.row:last').clone();

				clone.find( '.download span' ).html( '<a href="post.php?post=' + download_id + '&action=edit"></a>' );
				clone.find( '.download span a' ).text( download_title );
				clone.find( '.edd-payment-details-download-item-price' ).val( item_price.toFixed( edd_vars.currency_decimals ) );
				clone.find( '.edd-payment-details-download-item-tax').val( item_tax.toFixed( edd_vars.currency_decimals ) );
				clone.find( 'input.edd-payment-details-download-id' ).val( download_id );
				clone.find( 'input.edd-payment-details-download-price-id' ).val( price_id );

				var item_total = ( item_price * quantity ) + item_tax;
				item_total     = item_total.toFixed( edd_vars.currency_decimals );
				clone.find( 'span.edd-payment-details-download-amount' ).text( item_total );
				clone.find( 'input.edd-payment-details-download-amount' ).val( item_total );
				clone.find( 'input.edd-payment-details-download-quantity' ).val( quantity );
				clone.find( 'input.edd-payment-details-download-has-log').val(0);
				clone.find( 'input.edd-payment-details-download-order-item-id').val(0);

				clone.find( '.edd-copy-download-link-wrapper' ).remove();

				// Replace the name / id attributes
				clone.find( 'input' ).each(function() {
					var name = $( this ).attr( 'name' );

					name = name.replace( /\[(\d+)\]/, '[' + parseInt( count ) + ']');

					$( this ).attr( 'name', name ).attr( 'id', name );
				});

				clone.find('a.edd-order-remove-download').attr( 'data-key', parseInt( count ) );

				// Flag the Downloads section as changed
				$('#edd-payment-downloads-changed').val(1);

				$(clone).insertAfter( '#edd-order-items div.row:last' );
				$( '.edd-order-payment-recalc-totals' ).show();
				$( '.edd-add-download-field' ).val('');
			});
		},

		edit_price : function() {

			$(document.body).on('change keyup', '.edd-payment-item-input', function () {
				var row        = $( this ).parents('ul.edd-purchased-files-list-wrapper'),
					quantity   = row.find('input.edd-payment-details-download-quantity').val().replace(edd_vars.thousands_separator,''),
					item_price = row.find('input.edd-payment-details-download-item-price').val().replace(edd_vars.thousands_separator,''),
					item_tax   = row.find('input.edd-payment-details-download-item-tax').val().replace(edd_vars.thousands_separator,'');

				$( '.edd-order-payment-recalc-totals' ).show();

				item_price = parseFloat( item_price );
				if ( isNaN( item_price ) ) {
					alert( edd_vars.numeric_item_price );
					return false;
				}

				item_tax = parseFloat( item_tax );
				if ( isNaN( item_tax ) ) {
					item_tax = 0.00;
				}

				if ( isNaN( parseInt( quantity ) ) ) {
					quantity = 1;
				}

				var item_total = ( item_price * quantity ) + item_tax;
				item_total     = item_total.toFixed( edd_vars.currency_decimals );
				row.find('input.edd-payment-details-download-amount').val( item_total );
				row.find('span.edd-payment-details-download-amount').text( item_total );
			});
		},

		recalculate_total : function() {

			// Update taxes and totals for any changes made.
			$('#edd-order-recalc-total').on('click', function(e) {
				e.preventDefault();

				var total  = 0,
					tax    = 0,
					totals = $('#edd-order-items .row input.edd-payment-details-download-amount'),
					taxes  = $('#edd-order-items .row input.edd-payment-details-download-item-tax');

				if ( totals.length ) {
					totals.each(function() {
						total += parseFloat( $( this ).val() );
					});
				}

				if ( taxes.length ) {
					taxes.each(function() {
						tax += parseFloat( $( this ).val() );
					});
				}

				if ( $('.edd-payment-fees').length ) {
					$('.edd-payment-fees span.fee-amount').each(function() {
						total += parseFloat( $( this ).data('fee') );
					});
				}
				$('input[name=edd-payment-total]').val(total.toFixed(edd_vars.currency_decimals));
				$('input[name=edd-payment-tax]').val(tax.toFixed(edd_vars.currency_decimals));
			});
		},

		variable_prices_check : function() {

			// On Download Select, Check if Variable Prices Exist
			$('.edd-edit-purchase-element').on('change', 'select#edd_order_download_select', function() {
				var select      = $( this ),
					download_id = select.val();

				if(parseInt(download_id) > 0) {
					var postData = {
						action : 'edd_check_for_download_price_variations',
						download_id: download_id
					};

					$.ajax({
						type: "POST",
						data: postData,
						url: ajaxurl,
						success: function (response) {
							$('.edd_price_options_select').remove();
							$(response).insertAfter( select.next() );
						}
					}).fail(function (data) {
						if ( window.console && window.console.log ) {
							console.log( data );
						}
					});
				}
			});
		},

		resend_receipt : function() {
			var emails_wrap = $('.edd-order-resend-receipt-addresses');

			$( document.body ).on( 'click', '#edd-select-receipt-email', function( e ) {
				e.preventDefault();
				emails_wrap.slideDown();
			});

			$( document.body ).on( 'change', '.edd-order-resend-receipt-email', function() {
				var href = $('#edd-select-receipt-email').prop( 'href' ) + '&email=' + $( this ).val();

				if ( confirm( edd_vars.resend_receipt ) ) {
					window.location = href;
				}
			});

			$( document.body ).on( 'click', '#edd-resend-receipt', function( e ) {
				return confirm( edd_vars.resend_receipt );
			});
		},

		copy_download_link : function() {
			$( document.body ).on( 'click', '.edd-copy-download-link', function( e ) {
				e.preventDefault();

				var link     = $( this ),
					postData = {
						action      : 'edd_get_file_download_link',
						payment_id  : $('input[name="edd_payment_id"]').val(),
						download_id : link.data('download-id'),
						price_id    : link.data('price-id')
					};

				$.ajax({
					type: "POST",
					data: postData,
					url: ajaxurl,
					success: function (link) {
						$( "#edd-download-link" ).dialog({
							width: 400
						}).html( '<textarea rows="10" cols="40" id="edd-download-link-textarea">' + link + '</textarea>' );
						$( "#edd-download-link-textarea" ).focus().select();
						return false;
					}
				}).fail(function (data) {
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});
			});
		}
	};

	EDD_Edit_Payment.init();

	/**
	 * Add order
	 */

	var EDD_Add_Order = {
		init : function() {
			this.add_order_item();
			this.add_adjustment();
			this.override();
			this.remove();
			this.fetch_addresses();
			this.select_address();
			this.recalculate_total();
			this.validate();
		},

		add_order_item : function () {
			var button = $( '.edd-add-order-item-button' );

			// Toggle form.
			$( '#edd-order-items' ).on( 'click', 'h3 .edd-metabox-title-action', function(e) {
				e.preventDefault();
				$( '#edd-order-items' ).children( '.edd-add-download-to-purchase' ).slideToggle();
			} );

			button.prop( 'disabled', 'disabled' );

			$( '.edd-order-add-download-select' ).on( 'change', function() {
				button.removeAttr( 'disabled' );
			} );

			// Add item.
			button.on( 'click', function( e ) {
				e.preventDefault();

				var select = $( '.edd-order-add-download-select' ),
					spinner = $( '.edd-add-download-to-purchase .spinner' ),
					data = {
						action: 'edd_add_order_item',
						nonce: $( '#edd_add_order_nonce' ).val(),
						country: $( '.edd-order-address-country' ).val(),
						region: $( '.edd-order-address-region' ).val(),
						download: select.val(),
						quantity: $( '.edd-add-order-quantity' ).val()
					};

				spinner.css( 'visibility', 'visible' );

				$.post( ajaxurl, data, function( response ) {
					$( '.orderitems .no-items' ).hide();
					$( '.orderitems tbody' ).append( response.html );

					EDD_Add_Order.update_totals();
					EDD_Add_Order.reindex();

					spinner.css( 'visibility', 'hidden' );

					// Display `Override` button if it exists.
					$( '.edd-override' ).removeAttr( 'disabled' );
				}, 'json' );
			} );
		},

		add_adjustment : function () {
			// Toggle form.
			$( '#edd-order-adjustments' ).on( 'click', 'h3 .edd-metabox-title-action', function(e) {
				e.preventDefault();
				$( '#edd-order-adjustments' ).children( '.edd-add-adjustment-to-purchase' ).slideToggle();
			} );

			$( '.edd-order-add-adjustment-select' ).on( 'change', function() {
				var type = $( this ).val();

				$( '.edd-add-adjustment-to-purchase li.fee, .edd-add-adjustment-to-purchase li.discount, .edd-add-adjustment-to-purchase li.fee, .edd-add-adjustment-to-purchase li.credit' ).hide();

				$( '.' + type, '.edd-add-adjustment-to-purchase' ).show();
			} );

			$( '.edd-add-order-adjustment-button' ).on( 'click', function( e ) {
				e.preventDefault();

				var data = {
						action: 'edd_add_adjustment_to_order',
						nonce: $( '#edd_add_order_nonce' ).val(),
						type: $( '.edd-order-add-adjustment-select' ).val(),
						adjustment_data: {
							fee: $( '.edd-order-add-fee-select' ).val(),
							discount: $( '.edd-order-add-discount-select' ).val(),
							credit: {
								description: $( '.edd-add-order-credit-description' ).val(),
								amount: $( '.edd-add-order-credit-amount' ).val()
							}
						}
					},
					spinner = $( '.edd-add-adjustment-to-purchase .spinner' );

				spinner.css( 'visibility', 'visible' );

				$.post( ajaxurl, data, function( response ) {
					$( '.orderadjustments .no-items' ).hide();
					$( '.orderadjustments tbody' ).append( response.html );

					EDD_Add_Order.update_totals();
					EDD_Add_Order.reindex();

					spinner.css( 'visibility', 'hidden' );
				}, 'json' );
			} );
		},

		override : function() {
			$( '.edd-override' ).on( 'click', function() {
				$( this ).prop( 'disabled', 'disabled' );

				$( this ).attr( 'data-override', 'true' );

				$( document.body ).on( 'click', '.orderitems tr td .value', EDD_Add_Order.switchToInput );

				$( '<input>' ).attr({
					type: 'hidden',
					name: 'edd_add_order_override',
					value: 'true'
				}).appendTo( '#edd-add-order-form' );
			} );
		},

		switchToInput : function() {
			var input = $( '<input>', {
				val: $( this ).text(),
				type: 'text'
			});

			$( this ).replaceWith( input );
			input.on( 'blur', EDD_Add_Order.switchToSpan );
			input.select();
		},

		switchToSpan : function() {
			var span = $( '<span>', {
				text: parseFloat( $( this ).val() ).toLocaleString( edd_vars.currency, {
					style:                 'decimal',
					currency:              edd_vars.currency,
					minimumFractionDigits: edd_vars.currency_decimals,
					maximumFractionDigits: edd_vars.currency_decimals
				} )
			});

			var type = $( this ).parent().data( 'type' ),
				input = $( this ).parents( 'tr' ).find( '.download-' + type );

			if ( 'quantity' === type ) {
				span.text( parseInt( $( this ).val() ) );
			}

			input.val( span.text() );

			span.addClass( 'value' );
			$( this ).replaceWith( span );

			EDD_Add_Order.update_totals();

			span.on( 'click', EDD_Add_Order.switchToInput );
		},

		remove : function() {
			$( document.body ).on( 'click', '.orderitems .remove-item, .orderadjustments .remove-item', function( e ) {
				e.preventDefault();

				var $this = $( this ),
					tbody = $this.parents( 'tbody' );

				$this.parents( 'tr' ).remove();

				if ( 1 === $( 'tr', tbody ).length ) {
					$( '.no-items', tbody ).show();
				}

				EDD_Add_Order.update_totals();
				EDD_Add_Order.reindex();

				return false;
			} );
		},

		fetch_addresses : function() {
			$( '.edd-payment-change-customer-input' ).on( 'change', function() {
				var $this = $( this ),
					data = {
						action: 'edd_customer_addresses',
						customer_id: $this.val(),
						nonce: $( '#edd_add_order_nonce' ).val()
					};

				$.post( ajaxurl, data, function( response ) {

					// Store response for later use.
					edd_admin_globals.customer_address_ajax_result = response;

					if ( response.html ) {
						$( '.customer-address-select-wrap' ).html( response.html ).show();
						$( '.customer-address-select-wrap select' ).chosen( chosen_vars );
					} else {
						$( '.customer-address-select-wrap' ).html( '' ).hide();
					}
				}, 'json' );

				return false;
			} );
		},

		select_address : function() {
			$( document.body ).on( 'change', '.customer-address-select-wrap .add-order-customer-address-select', function() {
				var $this = $( this ),
					val   = $this.val(),
					select = $( '#edd-add-order-form select#edd_order_address_country' ),
					address = edd_admin_globals.customer_address_ajax_result.addresses[ val ];

				$( '#edd-add-order-form input[name="edd_order_address[address]"]' ).val( address.address );
				$( '#edd-add-order-form input[name="edd_order_address[address2]"]' ).val( address.address2 );
				$( '#edd-add-order-form input[name="edd_order_address[city]"]' ).val( address.city );
				select.val( address.country ).trigger( 'chosen:updated' );
				$( '#edd-add-order-form input[name="edd_order_address[address_id]"]' ).val( val );

				var data = {
						action:    'edd_get_shop_states',
						country:    select.val(),
						nonce:      $( '.add-order-customer-address-select' ).data( 'nonce' ),
						field_name: 'edd_order_address_region'
					};

				$.post( ajaxurl, data, function ( response ) {
					$( 'select#edd_order_address_region' ).find( 'option:gt(0)' ).remove();

					if ( 'nostates' !== response ) {
						$( response ).find( 'option:gt(0)' ).appendTo( 'select#edd_order_address_region' );
					}

					$( 'select#edd_order_address_region' ).trigger( 'chosen:updated' );
					$( 'select#edd_order_address_region' ).val( address.region ).trigger( 'chosen:updated' );
				});

				return false;
			} );

			$( '.edd-order-address-country' ).on( 'change', function() {
				var select = $( this ),
					data   = {
						action:    'edd_get_shop_states',
						country:    select.val(),
						nonce:      select.data( 'nonce' ),
						field_name: 'edd-order-address-country'
					};

				$.post( ajaxurl, data, function ( response ) {
					$( 'select.edd-order-address-region' ).find( 'option:gt(0)' ).remove();

					if ( 'nostates' !== response ) {
						$( response ).find( 'option:gt(0)' ).appendTo( 'select.edd-order-address-region' );
					}

					$( 'select.edd-order-address-region' ).trigger( 'chosen:updated' );
				} ).done(function ( response ) {
					EDD_Add_Order.recalculate_taxes();
				} );

				return false;
			} );

			$( '.edd-order-address-region' ).on( 'change', function() {
				EDD_Add_Order.recalculate_taxes();
			} );
		},

		reindex : function () {
			var key = 0;

			$( '.orderitems tbody tr:not(.no-items), .orderadjustments tbody tr:not(.no-items)' ).each( function() {
				$( this ).attr( 'data-key', key );

				$( this ).find( 'input' ).each( function() {
					var name = $( this ).attr( 'name' );

					if ( name ) {
						name = name.replace( /\[(\d+)\]/, '[' + parseInt( key ) + ']');
						$( this ).attr( 'name', name );
					}
				} );

				key++;
			});
		},

		recalculate_taxes : function() {
			$( '#publishing-action .spinner' ).css( 'visibility', 'visible' );

			var data = {
				action: 'edd_add_order_recalculate_taxes',
				country: $( '.edd-order-address-country' ).val(),
				region: $( '.edd-order-address-region' ).val(),
				nonce: $( '#edd_add_order_nonce' ).val()
			};

			$.post( ajaxurl, data, function ( response ) {
				if ( '' !== response.tax_rate ) {
					var tax_rate = parseFloat( response.tax_rate );

					$( '.orderitems tbody tr:not(.no-items)' ).each( function() {
						var amount = parseFloat( $( '.amount .value', this ).text() ),
							quantity = parseFloat( $( '.quantity .value', this ).text() ),
							calculated = amount * quantity,
							tax = 0;

						if ( response.prices_include_tax ) {
							var pre_tax = parseFloat( calculated / ( 1 + tax_rate ) );
							tax = parseFloat( calculated - pre_tax );
						} else {
							tax = calculated * tax_rate;
						}

						var storeCurrency = edd_vars.currency,
							decimalPlaces = edd_vars.currency_decimals,
							total         = calculated + tax;

						$( '.tax .value', this ).text( tax.toLocaleString( storeCurrency, { style: 'decimal', currency: storeCurrency, minimumFractionDigits: decimalPlaces, maximumFractionDigits: decimalPlaces } ) );
						$( '.total .value', this ).text( total.toLocaleString( storeCurrency, { style: 'decimal', currency: storeCurrency, minimumFractionDigits: decimalPlaces, maximumFractionDigits: decimalPlaces } ) );
					} );
				}
			}, 'json' ).done( function() {
				$( '#publishing-action .spinner' ).css( 'visibility', 'hidden' );

				EDD_Add_Order.update_totals();
			} );
		},

		recalculate_total : function() {
			$( '#edd-add-order' ).on( 'click', '#edd-order-recalc-total', function() {
				EDD_Add_Order.update_totals();
			} );
		},

		update_totals : function() {
			var subtotal = 0,
				tax = 0,
				adjustments = 0,
				total = 0;

			$( '.orderitems tbody tr:not(.no-items)' ).each( function() {
				var row = $( this ),
					item_amount,
					item_quantity = 1,
					item_tax = 0,
					item_total;

				item_amount = parseFloat( row.find( '.amount .value' ).text() );

				if ( row.find( '.quantity' ).length ) {
					item_quantity = parseFloat( row.find( '.quantity .value' ).text() );
				}

				subtotal += item_amount * item_quantity;

				if ( row.find( '.tax' ).length ) {
					item_tax = parseFloat( row.find( '.tax .value' ).text() );

					if ( ! isNaN( item_tax ) && ! edd_vars.taxes_included ) {
						item_amount += item_tax;
						tax += item_tax;
					}
				}

				item_total = item_amount * item_quantity;

				total += item_total;
			});

			$( '.orderadjustments tbody tr:not(.no-items)' ).each( function() {
				var row = $( this ),
					type,
					amount = 0;

				type = row.data( 'adjustment' );

				switch ( type ) {
					case 'credit':
						amount = parseFloat( row.find( 'input.credit-amount', row ).val() );
						adjustments += amount;
						total -= amount;
						break;
					case 'discount':
						amount = parseFloat( row.find( 'input.discount-amount', row ).val() );

						if ( 'percent' === row.find( 'input.discount-type' ).val() ) {
							$( '.orderitems tbody tr:not(.no-items)' ).each( function () {
								var item_amount = $( this ).find( '.amount .value' ).text(),
									quantity = 1;

								if ( $( this ).find( '.quantity' ).length ) {
									quantity = parseFloat( $( this ).find( '.quantity' ).text() );
								}

								item_amount *= quantity;

								var reduction = parseFloat( ( item_amount / 100 ) * amount );

								if ( $( this ).find( '.tax' ).length ) {
									var item_tax = parseFloat( $( this ).find( '.tax .value' ).text() ),
										item_tax_reduction = parseFloat( item_tax / 100 * amount );

									tax -= item_tax_reduction;
									total -= item_tax_reduction;
								}

								adjustments += reduction;
								total -= reduction;
							} );
						} else {
							adjustments += amount;
							total -= amount;
						}

						break;
				}
			});

			if ( isNaN( total ) ) {
				total = 0;
			}

			if ( isNaN( subtotal ) ) {
				subtotal = 0;
			}

			if ( isNaN( tax ) ) {
				tax = 0;
			}

			if ( isNaN( adjustments ) ) {
				adjustments = 0;
			}

			$(' .edd-order-subtotal .value').html( subtotal.toFixed( edd_vars.currency_decimals ) );
			$(' .edd-order-taxes .value').html( tax.toFixed( edd_vars.currency_decimals ) );
			$(' .edd-order-discounts .value').html( adjustments.toFixed( edd_vars.currency_decimals ) );
			$(' .edd-order-total .value ').html( total.toFixed( edd_vars.currency_decimals ) );
		},

		validate : function() {
			$( '#edd-add-order-form' ).on( 'submit', function() {
				$( '#publishing-action .spinner' ).css( 'visibility', 'visible' );

				if ( $( '.orderitems tr.no-items' ).is( ':visible' ) ) {
					$( '#edd-add-order-no-items-error' ).slideDown();
				} else {
					$( '#edd-add-order-no-items-error' ).slideUp();
				}

				if ( $( '.order-customer-info' ).is( ':visible' ) ) {
					$( '#edd-add-order-customer-error' ).slideDown();
				} else {
					$( '#edd-add-order-customer-error' ).slideUp();
				}

				if ( $( '.notice' ).is( ':visible' ) ) {
					$( '#publishing-action .spinner' ).css( 'visibility', 'hidden' );
					return false;
				}
			} );
		}
	};

	EDD_Add_Order.init();

	/**
	 * Discount add / edit screen JS
	 */
	var EDD_Discount = {

		init : function() {
			this.product_requirements();
		},

		product_requirements : function() {
			$('#edd-products').change(function() {
				var product_conditions = $( '#edd-discount-product-conditions' );

				if ( $( this ).val() ) {
					product_conditions.show();
				} else {
					product_conditions.hide();
				}
			});
		}
	};

	EDD_Discount.init();

	/**
	 * Reports / Exports screen JS
	 */
	var EDD_Reports = {

		init : function() {
			this.meta_boxes();
			this.date_options();
			this.customers_export();
			this.filters();
		},

		meta_boxes : function() {
			$( '.edd-reports-wrapper .postbox .handlediv' ).remove();
			$( '.edd-reports-wrapper .postbox' ).removeClass( 'closed' );

			// Use a timeout to ensure this happens after core binding
			setTimeout( function() {
				$( '.edd-reports-wrapper .postbox .hndle' ).unbind( 'click.postboxes' );
			}, 1 );
		},

		date_options : function() {

			// Show hide extended date options
			$( 'select.edd-graphs-date-options' ).on( 'change', function( event ) {
				var	select             = $( this ),
					date_range_options = select.parent().siblings( '.edd-date-range-options' );

				if ( 'other' === select.val() ) {
					date_range_options.removeClass( 'screen-reader-text' );
				} else {
					date_range_options.addClass( 'screen-reader-text' );
				}
			});
		},

		customers_export : function() {

			// Show / hide Download option when exporting customers
			$( '#edd_customer_export_download' ).change( function() {

				var $this = $( this ),
					download_id = $('option:selected', $this).val(),
					customer_export_option = $( '#edd_customer_export_option' );

				if ( '0' === $this.val() ) {
					customer_export_option.show();
				} else {
					customer_export_option.hide();
				}

				// On Download Select, Check if Variable Prices Exist
				if ( parseInt( download_id ) !== 0 ) {
					var data = {
						action : 'edd_check_for_download_price_variations',
						download_id: download_id,
						all_prices: true
					};

					var price_options_select = $('.edd_price_options_select');

					$.post(ajaxurl, data, function(response) {
						price_options_select.remove();
						$('#edd_customer_export_download_chosen').after( response );
					});
				} else {
					price_options_select.remove();
				}
			});
		},

		filters : function() {
			$('.edd_countries_filter').on( 'change', function() {
				var select = $( this ),
					data   = {
						action:    'edd_get_shop_states',
						country:    select.val(),
						nonce:      select.data('nonce'),
						field_name: 'edd_regions_filter'
					};

				$.post( ajaxurl, data, function ( response ) {
					$( 'select.edd_regions_filter' ).find( 'option' ).remove();

					if ( 'nostates' !== response ) {
						$( response ).find( 'option' ).appendTo( 'select.edd_regions_filter' );
					}

					$( 'select.edd_regions_filter' ).trigger( 'chosen:updated' );
				});

				return false;
			} );
		}
	};

	EDD_Reports.init();


	/**
	 * Settings screen JS
	 */
	var EDD_Settings = {

		init : function() {
			this.general();
			this.emails();
			this.misc();
			this.location();
		},

		general : function() {
			var edd_color_picker = $('.edd-color-picker');

			if ( edd_color_picker.length ) {
				edd_color_picker.wpColorPicker();
			}

			// Settings Upload field JS
			if ( typeof wp === "undefined" || '1' !== edd_vars.new_media_ui ) {

				// Old Thickbox uploader
				var edd_settings_upload_button = $( '.edd_settings_upload_button' );
				if ( edd_settings_upload_button.length > 0 ) {
					window.formfield = '';

					$( document.body ).on('click', edd_settings_upload_button, function(e) {
						e.preventDefault();
						window.formfield = $( this ).parent().prev();
						window.tbframe_interval = setInterval(function() {
							jQuery('#TB_iframeContent').contents().find('.savesend .button').val(edd_vars.use_this_file).end().find('#insert-gallery, .wp-post-thumbnail').hide();
						}, 2000);
						tb_show( edd_vars.add_new_download, 'media-upload.php?TB_iframe=true' );
					});

					window.edd_send_to_editor = window.send_to_editor;
					window.send_to_editor = function (html) {
						if (window.formfield) {
							imgurl = $('a', '<div>' + html + '</div>').attr('href');
							window.formfield.val(imgurl);
							window.clearInterval(window.tbframe_interval);
							tb_remove();
						} else {
							window.edd_send_to_editor(html);
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

				$( document.body ).on('click', '.edd_settings_upload_button', function(e) {

					e.preventDefault();

					var button = $( this );

					window.formfield = $( this ).parent().prev();

					// If the media frame already exists, reopen it.
					if ( file_frame ) {
						//file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
						file_frame.open();
						return;
					}

					// Create the media frame.
					file_frame = wp.media.frames.file_frame = wp.media({
						title:    button.data( 'uploader_title' ),
						library:  { type: 'image' },
						button:   { text: button.data( 'uploader_button_text' ) },
						multiple: false
					});

					file_frame.on( 'menu:render:default', function( view ) {
						// Store our views in an object.
						var views = {};

						// Unset default menu items
						view.unset( 'library-separator' );
						view.unset( 'gallery' );
						view.unset( 'featured-image' );
						view.unset( 'embed' );

						// Initialize the views in our view object.
						view.set( views );
					});

					// When an image is selected, run a callback.
					file_frame.on( 'select', function() {

						var selection = file_frame.state().get('selection');
						selection.each( function( attachment, index ) {
							attachment = attachment.toJSON();
							window.formfield.val(attachment.url);
						});
					});

					// Finally, open the modal
					file_frame.open();
				});

				// WP 3.5+ uploader
				var file_frame;
				window.formfield = '';
			}
		},

		emails : function() {

			// Show the email template previews
			var email_preview_wrap = $('#email-preview-wrap');
			if ( email_preview_wrap.length ) {
				var emailPreview = $('#email-preview');
				email_preview_wrap.colorbox({
					inline: true,
					href: emailPreview,
					width: '80%',
					height: 'auto'
				});
			}
		},

		misc : function() {
			var downloadMethod = $('select[name="edd_settings[download_method]"]'),
				symlink = downloadMethod.parent().parent().next();

			// Hide Symlink option if Download Method is set to Direct
			if ( downloadMethod.val() === 'direct' ) {
				symlink.css( 'opacity', '0.4' );
				symlink.find('input').prop('checked', false).prop('disabled', true);
			}

			// Toggle download method option
			downloadMethod.on('change', function() {
				if ( $( this ).val() === 'direct' ) {
					symlink.css( 'opacity', '0.4' );
					symlink.find('input').prop('checked', false).prop('disabled', true);
				} else {
					symlink.find('input').prop('disabled', false);
					symlink.css( 'opacity', '1' );
				}
			});
		},

		location : function() {
			$('select.edd_countries_filter').on( 'change', function() {
				var select = $( this ),
					data   = {
						action:    'edd_get_shop_states',
						country:    select.val(),
						nonce:      select.data('nonce'),
						field_name: 'edd_regions_filter'
					};

				$.post( ajaxurl, data, function ( response ) {
					$( 'select.edd_regions_filter' ).find( 'option:gt(0)' ).remove();

					if ( 'nostates' !== response ) {
						$( response ).find( 'option:gt(0)' ).appendTo( 'select.edd_regions_filter' );
					}

					$( 'select.edd_regions_filter' ).trigger( 'chosen:updated' );
				});

				return false;
			} );
		}
	};

	EDD_Settings.init();

	$('.download_page_edd-payment-history table.orders .row-actions .delete a, a.edd-delete-payment').on('click', function() {
		if ( confirm( edd_vars.delete_payment ) ) {
			return true;
		}
		return false;
	});

	$('.download_page_edd-payment-history table.orderitems .row-actions .delete a').on('click', function() {
		if ( confirm( edd_vars.delete_order_item ) ) {
			return true;
		}
		return false;
	});

	$('.download_page_edd-payment-history table.orderadjustments .row-actions .delete a').on('click', function() {
		if ( confirm( edd_vars.delete_order_adjustment ) ) {
			return true;
		}
		return false;
	});

	$('body').on('click', '#the-list .editinline', function() {
		var post_id = $( this ).closest('tr').attr('id');

		post_id = post_id.replace("post-", "");

		var $edd_inline_data = $('#post-' + post_id);

		var regprice = $edd_inline_data.find('.column-price .downloadprice-' + post_id).val();

		// If variable priced product disable editing, otherwise allow price changes
		if ( regprice !== $('#post-' + post_id + '.column-price .downloadprice-' + post_id).val() ) {
			$('.regprice', '#edd-download-data').val(regprice).attr('disabled', false);
		} else {
			$('.regprice', '#edd-download-data').val( edd_vars.quick_edit_warning ).attr('disabled', 'disabled');
		}
	});

	// Bulk edit save
	$( document.body ).on( 'click', '#bulk_edit', function() {

		// define the bulk edit row
		var $bulk_row = $( '#bulk-edit' );

		// get the selected post ids that are being edited
		var $post_ids = new Array();
		$bulk_row.find( '#bulk-titles' ).children().each( function() {
			$post_ids.push( $( this ).attr( 'id' ).replace( /^(ttle)/i, '' ) );
		});

		// get the stock and price values to save for all the product ID's
		var $price = $( '#edd-download-data input[name="_edd_regprice"]' ).val();

		var data = {
			action:         'edd_save_bulk_edit',
			edd_bulk_nonce: $post_ids,
			post_ids:       $post_ids,
			price:          $price
		};

		// save the data
		$.post( ajaxurl, data );
	});

	// Replace options with search results
	$( document.body ).on( 'keyup', typingTimerElements, function(e) {
		var	element     = $( this ),
			val         = element.val(),
			container   = element.closest( '.edd-select-chosen' ),

			select      = container.prev(),
			select_type = select.data( 'search-type' ),
			no_bundles  = container.hasClass( 'no-bundles' ),
			variations  = container.hasClass( 'variations' ),

			lastKey     = e.which,
			search_type = 'edd_download_search';

		// String replace the chosen container IDs
		container.attr('id').replace( '_chosen', '' );

		// Detect if we have a defined search type, otherwise default to downloads
		if ( typeof select_type !== 'undefined' ) {

			// Don't trigger AJAX if this select has all options loaded
			if ( 'no_ajax' === select_type ) {
				return;
			}

			search_type = 'edd_' + select_type + '_search';
		} else {
			return;
		}

		// Don't fire if short or is a modifier key (shift, ctrl, apple command key, or arrow keys)
		if(
			( val.length <= 3 && 'edd_download_search' === search_type ) ||
			(
				lastKey === 16 ||
				lastKey === 13 ||
				lastKey === 91 ||
				lastKey === 17 ||
				lastKey === 37 ||
				lastKey === 38 ||
				lastKey === 39 ||
				lastKey === 40
			)
		) {
			container.children( '.spinner' ).remove();
			return;
		}

		// Maybe append a spinner
		if ( ! container.children( '.spinner' ).length ) {
			container.append( '<span class="spinner is-active"></span>' );
		}

		clearTimeout(typingTimer);

		typingTimer = setTimeout( function() {
			$.ajax({
				type:     'GET',
				dataType: 'json',
				url:      ajaxurl,
				data: {
					s:          val,
					action:     search_type,
					no_bundles: no_bundles,
					variations: variations
				},

				beforeSend: function() {
					select.closest('ul.chosen-results').empty();
				},

				success: function( data ) {

					// Remove all options but those that are selected
					$('option:not(:selected)', select).remove();

					// Add any option that doesn't already exist
					$.each( data, function( key, item ) {
						if ( ! $('option[value="' + item.id + '"]', select).length ) {
							select.prepend( '<option value="' + item.id + '">' + item.name + '</option>' );
						}
					});

					// Get the text immediately before triggering an update.
					// Any sooner will cause the text to jump around.
					var val = element.val();

					// Update the options
					select.trigger('chosen:updated');

					element.val(val);
				}
			}).fail(function (response) {
				if ( window.console && window.console.log ) {
					console.log( response );
				}
			}).done(function (response) {
				container.children( '.spinner' ).remove();
			});
		}, userInteractionInterval );
	});

	// This fixes the Chosen box being 0px wide when the thickbox is opened
	$( '#post' ).on( 'click', '.edd-thickbox', function() {
		$( '.edd-select-chosen', '#choose-download' ).css( 'width', '100%' );
	});

	/**
	 * Tools screen JS
	 */
	var EDD_Tools = {

		init : function() {
			this.revoke_api_key();
			this.regenerate_api_key();
			this.create_api_key();
			this.recount_stats();
		},

		revoke_api_key : function() {
			$( document.body ).on( 'click', '.edd-revoke-api-key', function( e ) {
				return confirm( edd_vars.revoke_api_key );
			});
		},
		regenerate_api_key : function() {
			$( document.body ).on( 'click', '.edd-regenerate-api-key', function( e ) {
				return confirm( edd_vars.regenerate_api_key );
			});
		},
		create_api_key : function() {
			$( document.body).on( 'submit', '#api-key-generate-form', function( e ) {
				var input = $( 'input[type="text"][name="user_id"]' );

				input.css('border-color', '#ddd');

				var user_id = input.val();
				if ( user_id.length < 1 || user_id === 0 ) {
					input.css('border-color', '#ff0000');
					return false;
				}
			});
		},
		recount_stats : function() {
			$( document.body).on( 'change', '#recount-stats-type', function() {
				var export_form   = $('#edd-tools-recount-form'),
					selected_type = $('option:selected', this).data('type'),
					submit_button = $('#recount-stats-submit'),
					products      = $('#tools-product-dropdown');

				// Reset the form
				export_form.find('.notice-wrap').remove();
				submit_button.removeClass('button-disabled').attr('disabled', false);
				products.hide();
				$('.edd-recount-stats-descriptions span').hide();

				if ( 'recount-download' === selected_type ) {
					products.show();
					products.find('.edd-select-chosen').css('width', 'auto');

				} else if ( 'reset-stats' === selected_type ) {
					export_form.append('<div class="notice-wrap"></div>');
					var notice_wrap = export_form.find('.notice-wrap');
					notice_wrap.html('<div class="notice notice-warning"><p><input type="checkbox" id="confirm-reset" name="confirm_reset_store" value="1" /> <label for="confirm-reset">' + edd_vars.reset_stats_warn + '</label></p></div>');

					$('#recount-stats-submit').addClass('button-disabled').attr('disabled', 'disabled');

				} else {
					products.hide();
					products.val(0);
				}

				$('#' + selected_type ).show();
			});

			$(document.body).on('change', '#confirm-reset', function() {
				var checked = $( this ).is(':checked');
				if ( checked ) {
					$('#recount-stats-submit').removeClass('button-disabled').removeAttr('disabled');
				} else {
					$('#recount-stats-submit').addClass('button-disabled').attr('disabled', 'disabled');
				}
			});

			$( '#edd-tools-recount-form' ).submit( function(e) {
				var selection     = $('#recount-stats-type').val(),
					export_form   = $( this ),
					selected_type = $('option:selected', this).data('type');

				if ( 'reset-stats' === selected_type ) {
					var is_confirmed = $('#confirm-reset').is(':checked');
					if ( is_confirmed ) {
						return true;
					} else {
						has_errors = true;
					}
				}

				export_form.find('.notice-wrap').remove();
				export_form.append('<div class="notice-wrap"></div>');

				var notice_wrap = export_form.find('.notice-wrap'),
					has_errors  = false;

				if ( null === selection || 0 === selection ) {
					// Needs to pick a method edd_vars.batch_export_no_class
					notice_wrap.html('<div class="updated error"><p>' + edd_vars.batch_export_no_class + '</p></div>');
					has_errors = true;
				}

				if ( 'recount-download' === selected_type ) {
					var selected_download = $('select[name="download_id"]').val();
					if ( selected_download === 0 ) {
						// Needs to pick download edd_vars.batch_export_no_reqs
						notice_wrap.html('<div class="updated error"><p>' + edd_vars.batch_export_no_reqs + '</p></div>');
						has_errors = true;
					}
				}

				if ( has_errors ) {
					export_form.find('.button-disabled').removeClass('button-disabled');
					return false;
				}
			});
		}
	};

	EDD_Tools.init();

	/**
	 * Export screen JS
	 */
	var EDD_Export = {

		init : function() {
			this.submit();
		},

		submit : function() {
			var self = this;

			$( document.body ).on( 'submit', '.edd-export-form', function(e) {
				e.preventDefault();

				var form         = $( this ),
					submitButton = form.find( 'input[type="submit"]' ).first();

				if ( submitButton.hasClass( 'button-disabled' ) || submitButton.is( ':disabled' ) ) {
					return;
				}

				var data = form.serialize();

				submitButton.addClass( 'button-disabled' );
				form.find('.notice-wrap').remove();
				form.append( '<div class="notice-wrap"><span class="spinner is-active"></span><div class="edd-progress"><div></div></div></div>' );

				// start the process
				self.process_step( 1, data, self );
			});
		},

		process_step : function( step, data, self ) {

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					form: data,
					action: 'edd_do_ajax_export',
					step: step
				},
				dataType: "json",
				success: function( response ) {
					if ( 'done' === response.step || response.error || response.success ) {

						// We need to get the actual in progress form, not all forms on the page
						var export_form    = $('.edd-export-form').find('.edd-progress').parent().parent();
						var notice_wrap    = export_form.find('.notice-wrap');

						export_form.find('.button-disabled').removeClass('button-disabled');

						if ( response.error ) {
							var error_message = response.message;
							notice_wrap.html('<div class="updated error"><p>' + error_message + '</p></div>');

						} else if ( response.success ) {
							var success_message = response.message;
							notice_wrap.html('<div id="edd-batch-success" class="updated notice"><p>' + success_message + '</p></div>');

						} else {
							notice_wrap.remove();
							window.location = response.url;
						}

					} else {
						$('.edd-progress div').animate({
							width: response.percentage + '%'
						}, 50, function() {
							// Animation complete.
						});
						self.process_step( parseInt( response.step ), data, self );
					}

				}
			}).fail(function (response) {
				if ( window.console && window.console.log ) {
					console.log( response );
				}
			});
		}
	};

	EDD_Export.init();

	/**
	 * Import screen JS
	 */
	var EDD_Import = {

		init : function() {
			this.submit();
		},

		submit : function() {
			var self = this;

			$('.edd-import-form').ajaxForm({
				beforeSubmit: self.before_submit,
				success:      self.success,
				complete:     self.complete,
				dataType:     'json',
				error:        self.error
			});
		},

		before_submit : function( arr, form, options ) {
			form.find('.notice-wrap').remove();
			form.append( '<div class="notice-wrap"><span class="spinner is-active"></span><div class="edd-progress"><div></div></div></div>' );

			//check whether client browser fully supports all File API
			if ( window.File && window.FileReader && window.FileList && window.Blob ) {

				// HTML5 File API is supported by browser

			} else {

				var import_form = $('.edd-import-form').find('.edd-progress').parent().parent();
				var notice_wrap = import_form.find('.notice-wrap');

				import_form.find('.button-disabled').removeClass('button-disabled');

				//Error for older unsupported browsers that doesn't support HTML5 File API
				notice_wrap.html('<div class="update error"><p>' + edd_vars.unsupported_browser + '</p></div>');
				return false;
			}
		},

		success: function( responseText, statusText, xhr, form ) {},

		complete: function( xhr ) {
			var self     = $( this ),
				response = jQuery.parseJSON( xhr.responseText );

			if ( response.success ) {
				var form = $('.edd-import-form .notice-wrap').parent();

				form.find('.edd-import-file-wrap,.notice-wrap').remove();
				form.find('.edd-import-options').slideDown();

				// Show column mapping
				var select  = form.find('select.edd-import-csv-column'),
					row     = select.parents( 'tr' ).first(),
					options = '',
					columns = response.data.columns.sort(function(a,b) {
						if ( a < b ) return -1;
						if ( a > b ) return 1;
						return 0;
					});

				$.each( columns, function( key, value ) {
					options += '<option value="' + value + '">' + value + '</option>';
				});

				select.append( options );

				select.on('change', function() {
					var key = $( this ).val();

					if ( ! key ) {
						$( this ).parent().next().html( '' );
					} else {

						if ( false !== response.data.first_row[key] ) {
							$( this ).parent().next().html( response.data.first_row[key] );
						} else {
							$( this ).parent().next().html( '' );
						}
					}
				});

				$.each( select, function() {
					$( this ).val( $( this ).attr( 'data-field' ) ).change();
				});

				$(document.body).on('click', '.edd-import-proceed', function(e) {
					e.preventDefault();

					form.append( '<div class="notice-wrap"><span class="spinner is-active"></span><div class="edd-progress"><div></div></div></div>' );

					response.data.mapping = form.serialize();

					EDD_Import.process_step( 1, response.data, self );
				});

			} else {
				EDD_Import.error( xhr );
			}
		},

		error : function( xhr ) {

			// Something went wrong. This will display error on form

			var response    = jQuery.parseJSON( xhr.responseText );
			var import_form = $('.edd-import-form').find('.edd-progress').parent().parent();
			var notice_wrap = import_form.find('.notice-wrap');

			import_form.find('.button-disabled').removeClass('button-disabled');

			if ( response.data.error ) {
				notice_wrap.html('<div class="update error"><p>' + response.data.error + '</p></div>');
			} else {
				notice_wrap.remove();
			}
		},

		process_step : function( step, import_data, self ) {
			$.ajax({
				type: 'POST',
				url:  ajaxurl,
				data: {
					form:    import_data.form,
					nonce:   import_data.nonce,
					class:   import_data.class,
					upload:  import_data.upload,
					mapping: import_data.mapping,
					action:  'edd_do_ajax_import',
					step:    step
				},
				dataType: "json",
				success: function( response ) {

					if ( 'done' === response.data.step || response.data.error ) {

						// We need to get the actual in progress form, not all forms on the page
						var import_form  = $('.edd-import-form').find('.edd-progress').parent().parent();
						var notice_wrap  = import_form.find('.notice-wrap');

						import_form.find('.button-disabled').removeClass('button-disabled');

						if ( response.data.error ) {
							notice_wrap.html('<div class="update error"><p>' + response.data.error + '</p></div>');

						} else {
							import_form.find( '.edd-import-options' ).hide();
							$('html, body').animate({
								scrollTop: import_form.parent().offset().top
							}, 500 );

							notice_wrap.html('<div class="updated"><p>' + response.data.message + '</p></div>');
						}

					} else {
						$('.edd-progress div').animate({
							width: response.data.percentage + '%'
						}, 50, function() {
							// Animation complete.
						});

						EDD_Import.process_step( parseInt( response.data.step ), import_data, self );
					}
				}
			}).fail(function (response) {
				if ( window.console && window.console.log ) {
					console.log( response );
				}
			});
		}
	};

	EDD_Import.init();

	/**
	 * Customer management screen JS
	 */
	var EDD_Customer = {

		vars: {
			customer_card_wrap_editable:  $( '#edit-customer-info .editable' ),
			customer_card_wrap_edit_item: $( '#edit-customer-info .edit-item' ),
			user_id: $('input[name="customerinfo[user_id]"]')
		},
		init : function() {
			this.edit_customer();
			this.add_email();
			this.user_search();
			this.remove_user();
			this.cancel_edit();
			this.change_country();
			this.delete_checked();
		},
		edit_customer: function() {
			$( document.body ).on( 'click', '#edit-customer', function( e ) {
				e.preventDefault();

				EDD_Customer.vars.customer_card_wrap_editable.hide();
				EDD_Customer.vars.customer_card_wrap_edit_item.show().css( 'display', 'block' );
			});
		},
		add_email: function() {
			$( document.body ).on( 'click', '#add-customer-email', function(e) {
				e.preventDefault();
				var button  = $( this ),
					wrapper = button.parent().parent().parent().parent(),
					customer_id = wrapper.find('input[name="customer-id"]').val(),
					email       = wrapper.find('input[name="additional-email"]').val(),
					primary     = wrapper.find('input[name="make-additional-primary"]').is(':checked'),
					nonce       = wrapper.find('input[name="add_email_nonce"]').val(),
					postData = {
						edd_action:  'customer-add-email',
						customer_id: customer_id,
						email:       email,
						primary:     primary,
						_wpnonce:    nonce
					};

				wrapper.parent().find('.notice-container').remove();
				wrapper.find('.spinner').css('visibility', 'visible');
				button.attr('disabled', true);

				$.post(ajaxurl, postData, function( response ) {
					setTimeout( function() {
						if ( true === response.success ) {
							window.location.href=response.redirect;
						} else {
							button.attr('disabled', false);
							wrapper.before('<div class="notice-container"><div class="notice notice-error inline"><p>' + response.message + '</p></div></div>');
							wrapper.find('.spinner').css('visibility', 'hidden');
						}
					}, userInteractionInterval );
				}, 'json');
			});
		},
		user_search: function() {
			// Upon selecting a user from the dropdown, we need to update the User ID
			$( document.body ).on('click.eddSelectUser', '.edd_user_search_results a', function( e ) {
				e.preventDefault();
				var user_id = $( this ).data('userid');
				EDD_Customer.vars.user_id.val(user_id);
			});
		},
		remove_user: function() {
			$( document.body ).on( 'click', '#disconnect-customer', function( e ) {
				e.preventDefault();

				if ( confirm( edd_vars.disconnect_customer ) ) {
					var customer_id = $('input[name="customerinfo[id]"]').val(),
						postData    = {
							edd_action:  'disconnect-userid',
							customer_id: customer_id,
							_wpnonce:    $( '#edit-customer-info #_wpnonce' ).val()
						};

					$.post(ajaxurl, postData, function( response ) {
						// Weird
						window.location.href = window.location.href;
					}, 'json');
				}
			});
		},
		cancel_edit: function() {
			$( document.body ).on( 'click', '#edd-edit-customer-cancel', function( e ) {
				e.preventDefault();
				EDD_Customer.vars.customer_card_wrap_edit_item.hide();
				EDD_Customer.vars.customer_card_wrap_editable.show();

				$( '.edd_user_search_results' ).html('');
			});
		},
		change_country: function() {
			$('select[name="customerinfo[country]"]').change(function() {
				var select = $( this ),
					data = {
						action:     'edd_get_shop_states',
						country:    select.val(),
						chosen:     false,
						nonce:      select.data('nonce'),
						field_name: 'customerinfo[region]'
					};

				$.post(ajaxurl, data, function (response) {
					var state_element = $( 'input[name="customerinfo[region]"], select[name="customerinfo[region]"]' );

					if ( 'nostates' === response ) {
						var new_element = '<input type="text" name="' + data.field_name + '" value="" class="edd-edit-toggles medium-text"/>';
					} else {
						var new_element = response;
					}

					state_element.replaceWith( new_element );
				});

				return false;
			});
		},
		delete_checked: function() {
			$( '#edd-customer-delete-confirm' ).change( function() {
				var records_input = $('#edd-customer-delete-records');
				var submit_button = $('#edd-delete-customer');

				if ( $( this ).prop('checked') ) {
					records_input.attr('disabled', false);
					submit_button.attr('disabled', false);
				} else {
					records_input.attr('disabled', true);
					records_input.prop('checked', false);
					submit_button.attr('disabled', true);
				}
			});
		}
	};

	EDD_Customer.init();

	// AJAX user search
	$('.edd-ajax-user-search')

		// Search
		.keyup(function() {
			var user_search = $( this ).val(),
				exclude     = '';

			if ( $( this ).data('exclude') ) {
				exclude = $( this ).data('exclude');
			}

			$('.edd_user_search_wrap').addClass('loading');

			var data = {
				action:    'edd_search_users',
				user_name: user_search,
				exclude:   exclude
			};

			$.ajax({
				type:     "POST",
				data:     data,
				dataType: "json",
				url:      ajaxurl,

				success: function (search_response) {
					$('.edd_user_search_wrap').removeClass('loading');
					$('.edd_user_search_results').removeClass('hidden');
					$('.edd_user_search_results span').html('');
					if (search_response.results) {
						$(search_response.results).appendTo('.edd_user_search_results span');
					}
				}
			});
		})

		// Hide
		.blur(function() {
			if ( edd_user_search_mouse_down ) {
				edd_user_search_mouse_down = false;
			} else {
				$( this ).removeClass('loading');
				$('.edd_user_search_results').addClass('hidden');
			}
		})

		// Show
		.focus(function() {
			$( this ).keyup();
		});

	$( document.body ).on('click.eddSelectUser', '.edd_user_search_results span a', function(e) {
		e.preventDefault();
		var login = $( this ).data('login');
		$('.edd-ajax-user-search').val(login);
		$('.edd_user_search_results').addClass('hidden');
		$('.edd_user_search_results span').html('');
	});

	$( document.body ).on('click.eddCancelUserSearch', '.edd_user_search_results a.edd-ajax-user-cancel', function(e) {
		e.preventDefault();
		$('.edd-ajax-user-search').val('');
		$('.edd_user_search_results').addClass('hidden');
		$('.edd_user_search_results span').html('');
	});

	if ( $('#edd_dashboard_sales').length ) {
		$.ajax({
			type: "GET",
			data: {
				action: 'edd_load_dashboard_widget'
			},
			url: ajaxurl,
			success: function (response) {
				$('#edd_dashboard_sales .inside').html( response );
			}
		});
	}

	// Enable reports meta box toggle states.
	if ( typeof postboxes !== 'undefined' && /edd-reports/.test( pagenow ) ) {
		postboxes.add_postbox_toggles( pagenow );
	}
});

// Graphing Helper Functions

var eddFormatCurrency = function (value) {
	var numeric       = parseFloat(value), // Convert the value to a floating point number in case it arrives as a string.
		storeCurrency = edd_vars.currency, // Specify the local currency.
		decimalPlaces = edd_vars.currency_decimals;

	return numeric.toLocaleString(storeCurrency, { style: 'currency', currency: storeCurrency, minimumFractionDigits: decimalPlaces, maximumFractionDigits: decimalPlaces });
};

var eddFormatNumber = function(value) {
	var numeric       = parseFloat(value), // Convert the value to a floating point number in case it arrives as a string.
		storeCurrency = edd_vars.currency; // Specify the local currency.

	return numeric.toLocaleString(storeCurrency, { style: 'decimal', minimumFractionDigits: 0, maximumFractionDigits: 0 });
};

var eddLabelFormatter = function (label, series) {
	return '<div style="font-size:12px; text-align:center; padding:2px">' + label + '</div>';
};

var eddLegendFormatterSales = function (label, series) {
	var slug  = label.toLowerCase().replace(/\s/g, '-'),
		color = '<div class="edd-legend-color" style="background-color: ' + series.color + '"></div>',
		value = '<div class="edd-pie-legend-item">' + label + ': ' + Math.round(series.percent) + '% (' + eddFormatNumber(series.data[0][1]) + ')</div>',
		item  = '<div id="' + series.edd_vars.id + slug + '" class="edd-legend-item-wrapper">' + color + value + '</div>';

	jQuery('#edd-pie-legend-' + series.edd_vars.id).append( item );

	return item;
};

var eddLegendFormatterEarnings = function (label, series) {
	var slug  = label.toLowerCase().replace(/\s/g, '-'),
		color = '<div class="edd-legend-color" style="background-color: ' + series.color + '"></div>',
		value = '<div class="edd-pie-legend-item">' + label + ': ' + Math.round(series.percent) + '% (' + eddFormatCurrency(series.data[0][1]) + ')</div>',
		item = '<div id="' + series.edd_vars.id + slug + '" class="edd-legend-item-wrapper">' + color + value + '</div>';

	jQuery('#edd-pie-legend-' + series.edd_vars.id).append( item );

	return item;
};

/**
 * Attach tooltips
 *
 * @param {string} selector
 */
function edd_attach_tooltips( selector ) {
	selector.tooltip({
		content: function() {
			return jQuery(this).prop('title');
		},
		tooltipClass: 'edd-ui-tooltip',
		position: {
			my:        'center top',
			at:        'center bottom+10',
			collision: 'flipfit'
		},
		hide: {
			duration: 200
		},
		show: {
			duration: 200
		}
	});
}
