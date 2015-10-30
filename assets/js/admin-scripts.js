jQuery(document).ready(function ($) {

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
			row.parent().find( 'tr.edd_repeatable_row' ).each(function() {
				var current = $(this).data( 'key' );
				if( parseInt( current ) > highest ) {
					highest = current;
				}
			});
			key = highest += 1;

			clone = row.clone();

			/** manually update any select box values */
			clone.find( 'select' ).each(function() {
				$( this ).val( row.find( 'select[name="' + $( this ).attr( 'name' ) + '"]' ).val() );
			});

			clone.removeClass( 'edd_add_blank' );

			clone.attr( 'data-key', key );
			clone.find( 'td input, td select, textarea' ).val( '' );
			clone.find( 'input, select, textarea' ).each(function() {
				var name = $( this ).attr( 'name' );

				if( name ) {

					name = name.replace( /\[(\d+)\]/, '[' + parseInt( key ) + ']');
					$( this ).attr( 'name', name ).attr( 'id', name );

				}

			});

			clone.find( 'span.edd_price_id' ).each(function() {
				$( this ).text( parseInt( key ) );
			});

			clone.find( '.edd_repeatable_default_input' ).each( function() {
				$( this ).val( parseInt( key ) ).removeAttr('checked');
			})

			// Remove Chosen elements
			clone.find( '.search-choice' ).remove();
			clone.find( '.chosen-container' ).remove();

			return clone;
		},

		add : function() {
			$( document.body ).on( 'click', '.submit .edd_add_repeatable', function(e) {
				e.preventDefault();
				var button = $( this ),
				row = button.parent().parent().prev( 'tr' ),
				clone = EDD_Download_Configuration.clone_repeatable(row);

				clone.insertAfter( row ).find('input, textarea, select').filter(':visible').eq(0).focus();

				// Setup chosen fields again if they exist
				clone.find('.edd-select-chosen').chosen({
					inherit_select_classes: true,
					placeholder_text_single: edd_vars.one_option,
					placeholder_text_multiple: edd_vars.one_or_more_option,
				});
				clone.find( '.edd-select-chosen' ).css( 'width', '100%' );
			});
		},

		move : function() {

			$(".edd_repeatable_table tbody").sortable({
				handle: '.edd_draghandle', items: '.edd_repeatable_row', opacity: 0.6, cursor: 'move', axis: 'y', update: function() {
					var count  = 0;
					$(this).find( 'tr' ).each(function() {
						$(this).find( 'input.edd_repeatable_index' ).each(function() {
							$( this ).val( count );
						});
						count++;
					});
				}
			});

		},

		remove : function() {
			$( document.body ).on( 'click', '.edd_remove_repeatable', function(e) {
				e.preventDefault();

				var row   = $(this).parent().parent( 'tr' ),
					count = row.parent().find( 'tr' ).length - 1,
					type  = $(this).data('type'),
					repeatable = 'tr.edd_repeatable_' + type + 's';

				if ( type === 'price' ) {
					var price_row_id = row.data('key');
					/** remove from price condition */
					$( '.edd_repeatable_condition_field option[value="' + price_row_id + '"]' ).remove();
				}

				if( count > 1 ) {
					$( 'input, select', row ).val( '' );
					row.fadeOut( 'fast' ).remove();
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
					$(this).find( 'input, select' ).each(function() {
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
				$( '.edd_pricing_fields,.edd_repeatable_table .pricing' ).toggle();
			});
		},

		files : function() {
			if( typeof wp === "undefined" || '1' !== edd_vars.new_media_ui ){
				//Old Thickbox uploader
				if ( $( '.edd_upload_file_button' ).length > 0 ) {
					window.formfield = '';

					$( document.body ).on('click', '.edd_upload_file_button', function(e) {
						e.preventDefault();
						window.formfield = $(this).parent().prev();
						window.tbframe_interval = setInterval(function() {
							jQuery('#TB_iframeContent').contents().find('.savesend .button').val(edd_vars.use_this_file).end().find('#insert-gallery, .wp-post-thumbnail').hide();
						}, 2000);
						if (edd_vars.post_id != null ) {
							var post_id = 'post_id=' + edd_vars.post_id + '&';
						}
						tb_show(edd_vars.add_new_download, 'media-upload.php?' + post_id +'TB_iframe=true');
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

				$( document.body ).on('click', '.edd_upload_file_button', function(e) {

					e.preventDefault();

					var button = $(this);

					window.formfield = $(this).closest('.edd_repeatable_upload_wrapper');

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
						title: button.data( 'uploader-title' ),
						button: {
							text: button.data( 'uploader-button-text' )
						},
						multiple: $( this ).data( 'multiple' ) == '0' ? false : true  // Set to true to allow multiple files to be selected
					} );

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
					} );

					// When an image is selected, run a callback.
					file_frame.on( 'insert', function() {

						var selection = file_frame.state().get('selection');
						selection.each( function( attachment, index ) {
							attachment = attachment.toJSON();
							if ( 0 === index ) {
								// place first attachment in field
								window.formfield.find( '.edd_repeatable_attachment_id_field' ).val( attachment.id );
								window.formfield.find( '.edd_repeatable_upload_field' ).val( attachment.url );
								window.formfield.find( '.edd_repeatable_name_field' ).val( attachment.title );
							} else {
								// Create a new row for all additional attachments
								var row = window.formfield,
									clone = EDD_Download_Configuration.clone_repeatable( row );

								clone.find( '.edd_repeatable_attachment_id_field' ).val( attachment.id );
								clone.find( '.edd_repeatable_upload_field' ).val( attachment.url );
								if ( attachment.title.length > 0 ) {
									clone.find( '.edd_repeatable_name_field' ).val( attachment.title );
								} else {
									clone.find( '.edd_repeatable_name_field' ).val( attachment.filename );
								}
								clone.insertAfter( row );
							}
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

		updatePrices: function() {
			$( '#edd_price_fields' ).on( 'keyup', '.edd_variable_prices_name', function() {

				var key = $( this ).parents( 'tr' ).data( 'key' ),
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
		}

	};

	EDD_Download_Configuration.init();

	//$('#edit-slug-box').remove();

	// Date picker
	var edd_datepicker = $( '.edd_datepicker' );
	if ( edd_datepicker.length > 0 ) {
		var dateFormat = 'mm/dd/yy';
		edd_datepicker.datepicker( {
			dateFormat: dateFormat
		} );
	}

	/**
	 * Edit payment screen JS
	 */
	var EDD_Edit_Payment = {

		init : function() {
			this.edit_address();
			this.remove_download();
			this.add_download();
			this.new_customer();
			this.recalculate_total();
			this.variable_prices_check();
			this.add_note();
			this.remove_note();
			this.resend_receipt();
			this.copy_download_link();
		},


		edit_address : function() {

			// Update base state field based on selected base country
			$('select[name="edd-payment-address[0][country]"]').change(function() {
				var $this = $(this);
				data = {
					action: 'edd_get_shop_states',
					country: $this.val(),
					field_name: 'edd-payment-address[0][state]'
				};
				$.post(ajaxurl, data, function (response) {
					var state_wrapper = $( '#edd-order-address-state-wrap select, #edd-order-address-state-wrap input' );
					if( 'nostates' == response ) {
						state_wrapper.replaceWith( '<input type="text" name="edd-payment-address[0][state]" value="" class="edd-edit-toggles medium-text"/>' );
					} else {
						state_wrapper.replaceWith( response );
					}
				});

				return false;
			});

		},

		remove_download : function() {

			// Remove a download from a purchase
			$('#edd-purchased-files').on('click', '.edd-order-remove-download', function() {

				var count = $( document.body ).find( '#edd-purchased-files > .row' ).length;

				if ( count === 1 ) {
					alert( edd_vars.one_download_min );
					return false;
				}

				if( confirm( edd_vars.delete_payment_download ) ) {
					var key = $(this).data('key');

					var purchase_id = $('.edd-payment-id').val();
					var download_id = $('input[name="edd-payment-details-downloads['+key+'][id]"]').val();
					var price_id    = $('input[name="edd-payment-details-downloads['+key+'][price_id]"]').val();
					var quantity    = $('input[name="edd-payment-details-downloads['+key+'][quantity]"]').val();
					var amount      = $('input[name="edd-payment-details-downloads['+key+'][amount]"]').val();

					var currently_removed  = $('input[name="edd-payment-removed"]').val();
					currently_removed      = $.parseJSON(currently_removed);
					if ( currently_removed.length < 1 ) {
						currently_removed  = {};
					}

					var removed_item       = [ { 'id': download_id, 'price_id': price_id, 'quantity': quantity, 'amount': amount } ];
					currently_removed[key] = removed_item

					$('input[name="edd-payment-removed"]').val(JSON.stringify(currently_removed));

					$(this).parent().parent().parent().remove();

					// Flag the Downloads section as changed
					$('#edd-payment-downloads-changed').val(1);
					$('.edd-order-payment-recalc-totals').show();
				}
				return false;
			});

		},

		new_customer : function() {

			$('#edd-customer-details').on('click', '.edd-payment-new-customer, .edd-payment-new-customer-cancel', function(e) {
				e.preventDefault();
				$('.customer-info').toggle();
				$('.new-customer').toggle();

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
			$('#edd-purchased-files').on('click', '#edd-order-add-download', function(e) {

				e.preventDefault();

				var order_download_select   = $( '#edd_order_download_select' ),
					order_download_quantity = $( '#edd-order-download-quantity' ),
					order_download_amount   = $( '#edd-order-download-amount' ),
					selected_price_option   = $( '.edd_price_options_select option:selected' );

				var download_id    = order_download_select.val();
				var download_title = order_download_select.find(':selected').text();
				var quantity       = order_download_quantity.val();
				var amount         = order_download_amount.val();
				var price_id       = selected_price_option.val();
				var price_name     = selected_price_option.text();

				if( download_id < 1 ) {
					return false;
				}

				if( ! amount ) {
					amount = 0;
				}

				amount = parseFloat( amount );
				if ( isNaN( amount ) ) {
					alert( edd_vars.numeric_item_price );
					return false;
				}

				if ( edd_vars.quantities_enabled === '1' ) {
					if ( !isNaN( parseInt( quantity ) ) ) {
						amount = amount * quantity;
					} else {
						alert( edd_vars.numeric_quantity );
						return false;
					}
				}


				amount = amount.toFixed( edd_vars.currency_decimals );

				var formatted_amount = amount + edd_vars.currency_sign;
				if ( 'before' === edd_vars.currency_pos ) {
					formatted_amount = edd_vars.currency_sign + amount;
				}

				if( price_name ) {
					download_title = download_title + ' - ' + price_name;
				}

				var count = $('#edd-purchased-files div.row').length;
				var clone = $('#edd-purchased-files div.row:last').clone();

				clone.find( '.download span' ).html( '<a href="post.php?post=' + download_id + '&action=edit"></a>' );
				clone.find( '.download span a' ).text( download_title );
				clone.find( '.price-text' ).text( formatted_amount );
				clone.find( '.item-quantity' ).text( quantity );
				clone.find( '.item-price' ).text( edd_vars.currency_sign + ( amount / quantity ).toFixed( edd_vars.currency_decimals ) );
				clone.find( 'input.edd-payment-details-download-id' ).val( download_id );
				clone.find( 'input.edd-payment-details-download-price-id' ).val( price_id );
				clone.find( 'input.edd-payment-details-download-amount' ).val( amount );
				clone.find( 'input.edd-payment-details-download-quantity' ).val( quantity );
				clone.find( 'input.edd-payment-details-download-has-log').val(0);

				// Replace the name / id attributes
				clone.find( 'input' ).each(function() {
					var name = $( this ).attr( 'name' );

					name = name.replace( /\[(\d+)\]/, '[' + parseInt( count ) + ']');

					$( this ).attr( 'name', name ).attr( 'id', name );
				});

				// Flag the Downloads section as changed
				$('#edd-payment-downloads-changed').val(1);

				$(clone).insertAfter( '#edd-purchased-files div.row:last' );
				$( '.edd-order-payment-recalc-totals' ).show();

			});
		},

		recalculate_total : function() {

			// Remove a download from a purchase
			$('#edd-order-recalc-total').on('click', function(e) {
				e.preventDefault();
				var total           = 0,
					purchased_files = $( '#edd-purchased-files .row .edd-payment-details-download-amount' );

				if( purchased_files.length ) {
					purchased_files.each(function() {
						total += parseFloat( $(this).val() );
					});
				}
				if( $('.edd-payment-fees').length ) {
					$('.edd-payment-fees span.fee-amount').each(function() {
						total += parseFloat( $(this).data('fee') );
					});
				}
				$('input[name=edd-payment-total]').val( total );
			});

		},

		variable_prices_check : function() {

			// On Download Select, Check if Variable Prices Exist
			$('#edd-purchased-files').on('change', 'select#edd_order_download_select', function() {

				var $this = $(this), download_id = $this.val();

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
							$(response).insertAfter( $this.next() );
						}
					}).fail(function (data) {
						if ( window.console && window.console.log ) {
							console.log( data );
						}
					});

				}
			});

		},

		add_note : function() {

			$('#edd-add-payment-note').on('click', function(e) {
				e.preventDefault();
				var postData = {
					action : 'edd_insert_payment_note',
					payment_id : $(this).data('payment-id'),
					note : $('#edd-payment-note').val()
				};

				if( postData.note ) {

					$.ajax({
						type: "POST",
						data: postData,
						url: ajaxurl,
						success: function (response) {
							$('#edd-payment-notes-inner').append( response );
							$('.edd-no-payment-notes').hide();
							$('#edd-payment-note').val('');
						}
					}).fail(function (data) {
						if ( window.console && window.console.log ) {
							console.log( data );
						}
					});

				} else {
					var border_color = $('#edd-payment-note').css('border-color');
					$('#edd-payment-note').css('border-color', 'red');
					setTimeout( function() {
						$('#edd-payment-note').css('border-color', border_color );
					}, 500 );
				}

			});

		},

		remove_note : function() {

			$( document.body ).on('click', '.edd-delete-payment-note', function(e) {

				e.preventDefault();

				if( confirm( edd_vars.delete_payment_note) ) {

					var postData = {
						action : 'edd_delete_payment_note',
						payment_id : $(this).data('payment-id'),
						note_id : $(this).data('note-id')
					};

					$.ajax({
						type: "POST",
						data: postData,
						url: ajaxurl,
						success: function (response) {
							$('#edd-payment-note-' + postData.note_id ).remove();
							if( ! $('.edd-payment-note').length ) {
								$('.edd-no-payment-notes').show();
							}
							return false;
						}
					}).fail(function (data) {
						if ( window.console && window.console.log ) {
							console.log( data );
						}
					});
					return true;
				}

			});

		},

		resend_receipt : function() {
			$( document.body ).on( 'click', '#edd-resend-receipt', function( e ) {
				return confirm( edd_vars.resend_receipt );
			} );
		},

		copy_download_link : function() {
			$( document.body ).on( 'click', '.edd-copy-download-link', function( e ) {
				e.preventDefault();
				var $this    = $(this);
				var postData = {
					action      : 'edd_get_file_download_link',
					payment_id  : $('input[name="edd_payment_id"]').val(),
					download_id : $this.data('download-id'),
					price_id    : $this.data('price-id')
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

			} );
		}

	};
	EDD_Edit_Payment.init();


	/**
	 * Discount add / edit screen JS
	 */
	var EDD_Discount = {

		init : function() {
			this.type_select();
			this.product_requirements();
		},

		type_select : function() {

			$('#edd-edit-discount #edd-type, #edd-add-discount #edd-type').change(function() {

				$('.edd-amount-description').toggle();

			});

		},

		product_requirements : function() {

			$('#products').change(function() {

				var product_conditions = $( '#edd-discount-product-conditions' );

				if( $(this).val() ) {
					product_conditions.show();
				} else {
					product_conditions.hide();
				}

			});

		},

	};
	EDD_Discount.init();


	/**
	 * Reports / Exports screen JS
	 */
	var EDD_Reports = {

		init : function() {
			this.date_options();
			this.customers_export();
		},

		date_options : function() {

			// Show hide extended date options
			$( '#edd-graphs-date-options' ).change( function() {
				var $this = $(this),
					date_range_options = $( '#edd-date-range-options' );

				if ( 'other' === $this.val() ) {
					date_range_options.show();
				} else {
					date_range_options.hide();
				}
			});

		},

		customers_export : function() {

			// Show / hide Download option when exporting customers

			$( '#edd_customer_export_download' ).change( function() {

				var $this = $(this),
					download_id = $('option:selected', $this).val(),
					customer_export_option = $( '#edd_customer_export_option' );

				if ( '0' === $this.val() ) {
					customer_export_option.show();
				} else {
					customer_export_option.hide();
				}

				// On Download Select, Check if Variable Prices Exist
				if ( parseInt( download_id ) != 0 ) {
					var data = {
						action : 'edd_check_for_download_price_variations',
						download_id: download_id
					};

					var price_options_select = $('.edd_price_options_select');

					$.post(ajaxurl, data, function(response) {
						price_options_select.remove();
						$this.after( response );
					});
				} else {
					price_options_select.remove();
				}
			});

		}

	};
	EDD_Reports.init();


	/**
	 * Settings screen JS
	 */
	var EDD_Settings = {

		init : function() {
			this.general();
			this.taxes();
			this.emails();
			this.misc();
		},

		general : function() {

			var edd_color_picker = $('.edd-color-picker');

			if( edd_color_picker.length ) {
				edd_color_picker.wpColorPicker();
			}

			// Settings Upload field JS
			if ( typeof wp === "undefined" || '1' !== edd_vars.new_media_ui ) {
				//Old Thickbox uploader
				var edd_settings_upload_button = $( '.edd_settings_upload_button' );
				if ( edd_settings_upload_button.length > 0 ) {
					window.formfield = '';

					$( document.body ).on('click', edd_settings_upload_button, function(e) {
						e.preventDefault();
						window.formfield = $(this).parent().prev();
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

					var button = $(this);

					window.formfield = $(this).parent().prev();

					// If the media frame already exists, reopen it.
					if ( file_frame ) {
						//file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
						file_frame.open();
						return;
					}

					// Create the media frame.
					file_frame = wp.media.frames.file_frame = wp.media({
						frame: 'post',
						state: 'insert',
						title: button.data( 'uploader_title' ),
						button: {
							text: button.data( 'uploader_button_text' )
						},
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
					} );

					// When an image is selected, run a callback.
					file_frame.on( 'insert', function() {

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

		taxes : function() {
			var no_states = $('select.edd-no-states');
			if( no_states.length ) {
				no_states.closest('tr').hide();
			}

			// Update base state field based on selected base country
			$('select[name="edd_settings[base_country]"]').change(function() {
				var $this = $(this), $tr = $this.closest('tr');
				data = {
					action: 'edd_get_shop_states',
					country: $(this).val(),
					field_name: 'edd_settings[base_state]'
				};
				$.post(ajaxurl, data, function (response) {
					if( 'nostates' == response ) {
						$tr.next().hide();
					} else {
						$tr.next().show();
						$tr.next().find('select').replaceWith( response );
					}
				});

				return false;
			});

			// Update tax rate state field based on selected rate country
			$( document.body ).on('change', '#edd_tax_rates select.edd-tax-country', function() {
				var $this = $(this);
				data = {
					action: 'edd_get_shop_states',
					country: $(this).val(),
					field_name: $this.attr('name').replace('country', 'state')
				};
				$.post(ajaxurl, data, function (response) {
					if( 'nostates' == response ) {
						var text_field = '<input type="text" name="' + data.field_name + '" value=""/>';
						$this.parent().next().find('select').replaceWith( text_field );
					} else {
						$this.parent().next().find('input,select').show();
						$this.parent().next().find('input,select').replaceWith( response );
					}
				});

				return false;
			});

			// Insert new tax rate row
			$('#edd_add_tax_rate').on('click', function() {
				var row = $('#edd_tax_rates tr:last');
				var clone = row.clone();
				var count = row.parent().find( 'tr' ).length;
				clone.find( 'td input' ).not(':input[type=checkbox]').val( '' );
				clone.find( 'td [type="checkbox"]' ).attr('checked', false);
				clone.find( 'input, select' ).each(function() {
					var name = $( this ).attr( 'name' );
					name = name.replace( /\[(\d+)\]/, '[' + parseInt( count ) + ']');
					$( this ).attr( 'name', name ).attr( 'id', name );
				});
				clone.find( 'label' ).each(function() {
					var name = $( this ).attr( 'for' );
					name = name.replace( /\[(\d+)\]/, '[' + parseInt( count ) + ']');
					$( this ).attr( 'for', name );
				});
				clone.insertAfter( row );
				return false;
			});

			// Remove tax row
			$( document.body ).on('click', '#edd_tax_rates .edd_remove_tax_rate', function() {
				if( confirm( edd_vars.delete_tax_rate ) ) {
					var tax_rates = $('#edd_tax_rates tr:visible');
					var count     = tax_rates.length;

					if( count === 2 ) {
						$('#edd_tax_rates select').val('');
						$('#edd_tax_rates input[type="text"]').val('');
						$('#edd_tax_rates input[type="number"]').val('');
						$('#edd_tax_rates input[type="checkbox"]').attr('checked', false);
					} else {
						$(this).closest('tr').remove();
					}

					/* re-index after deleting */
					$('#edd_tax_rates tr').each( function( rowIndex ) {
						$(this).children().find( 'input, select' ).each(function() {
							var name = $( this ).attr( 'name' );
							name = name.replace( /\[(\d+)\]/, '[' + ( rowIndex - 1 ) + ']');
							$( this ).attr( 'name', name ).attr( 'id', name );
						});
					});
				}
				return false;
			});

		},

		emails : function() {

			// Show the email template previews
			var email_preview_wrap = $('#email-preview-wrap');
			if( email_preview_wrap.length ) {
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

			var downloadMethod = $('select[name="edd_settings[download_method]"]');
			var symlink = downloadMethod.parent().parent().next();

			// Hide Symlink option if Download Method is set to Direct
			if( downloadMethod.val() == 'direct' ) {
				symlink.hide();
				symlink.find('input').prop('checked', false);
			}
			// Toggle download method option
			downloadMethod.on('change', function() {
				if( $(this).val() == 'direct' ) {
					symlink.hide();
					symlink.find('input').prop('checked', false);
				} else {
					symlink.show();
				}
			});
		}

	}
	EDD_Settings.init();


	$('.download_page_edd-payment-history .row-actions .delete a').on('click', function() {
		if( confirm( edd_vars.delete_payment ) ) {
			return true;
		}
		return false;
	});


	$('#the-list').on('click', '.editinline', function() {

		var post_id = $(this).closest('tr').attr('id');

		post_id = post_id.replace("post-", "");

		var $edd_inline_data = $('#post-' + post_id);

		var regprice = $edd_inline_data.find('.column-price .downloadprice-' + post_id).val();

		// If variable priced product disable editing, otherwise allow price changes
		if ( regprice != $('#post-' + post_id + '.column-price .downloadprice-' + post_id).val() ) {
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

	// Setup Chosen menus
	$('.edd-select-chosen').chosen({
		inherit_select_classes: true,
		placeholder_text_single: edd_vars.one_option,
		placeholder_text_multiple: edd_vars.one_or_more_option,
	});

	// Add placeholders for Chosen input fields
	$( '.chosen-choices' ).on( 'click', function () {
		$(this).children('li').children('input').attr( 'placeholder', edd_vars.type_to_search );
	});

	// Variables for setting up the typing timer
	var typingTimer;               // Timer identifier
	var doneTypingInterval = 342;  // Time in ms, Slow - 521ms, Moderate - 342ms, Fast - 300ms

	// Replace options with search results
	$('.edd-select.chosen-container .chosen-search input, .edd-select.chosen-container .search-field input').keyup(function(e) {

		var val = $(this).val(), container = $(this).closest( '.edd-select-chosen' );
		var menu_id = container.attr('id').replace( '_chosen', '' );
		var lastKey = e.which;
		var search_type = 'edd_download_search';

		if( container.attr( 'id' ).indexOf( "customer" ) >= 0 ) {
			search_type = 'edd_customer_search';
		}

		// Don't fire if short or is a modifier key (shift, ctrl, apple command key, or arrow keys)
		if(
			( val.length <= 3 && 'edd_download_search' == search_type ) ||
			(
				e.which == 16 ||
				e.which == 13 ||
				e.which == 91 ||
				e.which == 17 ||
				e.which == 37 ||
				e.which == 38 ||
				e.which == 39 ||
				e.which == 40
			)
		) {
			return;
		}
		clearTimeout(typingTimer);
		typingTimer = setTimeout(
			function(){
				$.ajax({
					type: 'GET',
					url: ajaxurl,
					data: {
						action: search_type,
						s: val,
						current_id: edd_vars.post_id,
					},
					dataType: "json",
					beforeSend: function(){
						$('ul.chosen-results').empty();
					},
					success: function( data ) {

						// Remove all options but those that are selected
						$('#' + menu_id + ' option:not(:selected)').remove();
						$.each( data, function( key, item ) {
							// Add any option that doesn't already exist
							if( ! $('#' + menu_id + ' option[value="' + item.id + '"]').length ) {
								$('#' + menu_id).prepend( '<option value="' + item.id + '">' + item.name + '</option>' );
							}
						});
						 // Update the options
						$('.edd-select-chosen').trigger('chosen:updated');
						$('#' + menu_id).next().find('input').val(val);
					}
				}).fail(function (response) {
					if ( window.console && window.console.log ) {
						console.log( response );
					}
				}).done(function (response) {

				});
			},
			doneTypingInterval
		);
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
		},

		revoke_api_key : function() {
			$( document.body ).on( 'click', '.edd-revoke-api-key', function( e ) {
				return confirm( edd_vars.revoke_api_key );
			} );
		},
		regenerate_api_key : function() {
			$( document.body ).on( 'click', '.edd-regenerate-api-key', function( e ) {
				return confirm( edd_vars.regenerate_api_key );
			} );
		},
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

				var submitButton = $(this).find( 'input[type="submit"]' );

				if ( ! submitButton.hasClass( 'button-disabled' ) ) {

					var data = $(this).serialize();

					submitButton.addClass( 'button-disabled' );
					$(this).find('.notice-wrap').remove();
					$(this).append( '<div class="notice-wrap"><span class="spinner is-active"></span><div class="edd-progress"><div></div></div></div>' );

					// start the process
					self.process_step( 1, data, self );

				}

			});
		},

		process_step : function( step, data, self ) {

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					form: data,
					action: 'edd_do_ajax_export',
					step: step,
				},
				dataType: "json",
				success: function( response ) {

					if( 'done' == response.step || response.error ) {

						// We need to get the actual in progress form, not all forms on the page
						var export_form    = $('.edd-export-form').find('.edd-progress').parent().parent();
						var notice_wrap    = export_form.find('.notice-wrap');

						export_form.find('.button-disabled').removeClass('button-disabled');

						if ( response.error ) {

							var error_message = response.message;
							notice_wrap.html('<div class="update error"><p>' + error_message + '</p></div>');

						} else {

							notice_wrap.remove();
							window.location = response.url;

						}

					} else {
						$('.edd-progress div').animate({
							width: response.percentage + '%',
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
	 * Customer management screen JS
	 */
	var EDD_Customer = {

		vars: {
			customer_card_wrap_editable:  $( '#edd-customer-card-wrapper .editable' ),
			customer_card_wrap_edit_item: $( '#edd-customer-card-wrapper .edit-item' ),
			user_id: $('input[name="customerinfo[user_id]"]'),
			state_input: $(':input[name="customerinfo[state]"]'),
			note: $( '#customer-note' ),
		},
		init : function() {
			this.edit_customer();
			this.user_search();
			this.remove_user();
			this.cancel_edit();
			this.change_country();
			this.add_note();
			this.delete_checked();
		},
		edit_customer: function() {
			$( document.body ).on( 'click', '#edit-customer', function( e ) {
				e.preventDefault();

				EDD_Customer.vars.customer_card_wrap_editable.hide();
				EDD_Customer.vars.customer_card_wrap_edit_item.fadeIn().css( 'display', 'block' );
			});
		},
		user_search: function() {
			// Upon selecting a user from the dropdown, we need to update the User ID
			$( document.body ).on('click.eddSelectUser', '.edd_user_search_results a', function( e ) {
				e.preventDefault();
				var user_id = $(this).data('userid');
				EDD_Customer.vars.user_id.val(user_id);
			});
		},
		remove_user: function() {
			$( document.body ).on( 'click', '#disconnect-customer', function( e ) {
				e.preventDefault();
				var customer_id = $('input[name="customerinfo[id]"]').val();

				var postData = {
					edd_action:   'disconnect-userid',
					customer_id: customer_id,
					_wpnonce:     $( '#edit-customer-info #_wpnonce' ).val()
				};

				$.post(ajaxurl, postData, function( response ) {

					window.location.href=window.location.href;

				}, 'json');

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
				var $this = $(this);
				data = {
					action: 'edd_get_shop_states',
					country: $this.val(),
					field_name: 'customerinfo[state]'
				};
				$.post(ajaxurl, data, function (response) {
					if( 'nostates' == response ) {
						EDD_Customer.vars.state_input.replaceWith( '<input type="text" name="' + data.field_name + '" value="" class="edd-edit-toggles medium-text"/>' );
					} else {
						EDD_Customer.vars.state_input.replaceWith( response );
					}
				});

				return false;
			});
		},
		add_note : function() {
			$( document.body ).on( 'click', '#add-customer-note', function( e ) {
				e.preventDefault();
				var postData = {
					edd_action : 'add-customer-note',
					customer_id : $( '#customer-id' ).val(),
					customer_note : EDD_Customer.vars.note.val(),
					add_customer_note_nonce: $( '#add_customer_note_nonce' ).val()
				};

				if( postData.customer_note ) {

					$.ajax({
						type: "POST",
						data: postData,
						url: ajaxurl,
						success: function ( response ) {
							$( '#edd-customer-notes' ).prepend( response );
							$( '.edd-no-customer-notes' ).hide();
							EDD_Customer.vars.note.val( '' );
						}
					}).fail( function ( data ) {
						if ( window.console && window.console.log ) {
							console.log( data );
						}
					});

				} else {
					var border_color = EDD_Customer.vars.note.css( 'border-color' );
					EDD_Customer.vars.note.css( 'border-color', 'red' );
					setTimeout( function() {
						EDD_Customer.vars.note.css( 'border-color', border_color );
					}, 500 );
				}
			});
		},
		delete_checked: function() {
			$( '#edd-customer-delete-confirm' ).change( function() {
				var records_input = $('#edd-customer-delete-records');
				var submit_button = $('#edd-delete-customer');

				if ( $(this).prop('checked') ) {
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

	// Ajax user search
	$('.edd-ajax-user-search').keyup(function() {
		var user_search = $(this).val();
		var exclude     = '';

		if ( $(this).data('exclude') ) {
			exclude = $(this).data('exclude');
		}

		$('.edd-ajax').show();
		data = {
			action: 'edd_search_users',
			user_name: user_search,
			exclude: exclude
		};

		document.body.style.cursor = 'wait';

		$.ajax({
			type: "POST",
			data: data,
			dataType: "json",
			url: ajaxurl,
			success: function (search_response) {

				$('.edd-ajax').hide();
				$('.edd_user_search_results').removeClass('hidden');
				$('.edd_user_search_results span').html('');
				$(search_response.results).appendTo('.edd_user_search_results span');
				document.body.style.cursor = 'default';
			}
		});
	});

	$( document.body ).on('click.eddSelectUser', '.edd_user_search_results span a', function(e) {
		e.preventDefault();
		var login = $(this).data('login');
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

	if( $('.edd_dashboard_sales').length ) {
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

	$(document).on('keydown', '.customer-note-input', function(e) {
		if(e.keyCode == 13 && (e.metaKey || e.ctrlKey)) {
			$('#add-customer-note').click();
		}
	});

});

// Graphing Helper Functions

var eddFormatCurrency = function (value) {
	// Convert the value to a floating point number in case it arrives as a string.
	var numeric = parseFloat(value);
	// Specify the local currency.
	var storeCurrency = edd_vars.currency;
	var decimalPlaces = edd_vars.currency_decimals;
	return numeric.toLocaleString(storeCurrency, { style: 'currency', currency: storeCurrency, minimumFractionDigits: decimalPlaces, maximumFractionDigits: decimalPlaces });
}

var eddFormatNumber = function(value) {
	// Convert the value to a floating point number in case it arrives as a string.
	var numeric = parseFloat(value);
	// Specify the local currency.
	var storeCurrency = edd_vars.currency;
	var decimalPlaces = edd_vars.currency_decimals;
	return numeric.toLocaleString(storeCurrency, { style: 'decimal', minimumFractionDigits: 0, maximumFractionDigits: 0 });
}

var eddLabelFormatter = function (label, series) {
	return '<div style="font-size:12px; text-align:center; padding:2px">' + label + '</div>';
}

var eddLegendFormatterSales = function (label, series) {
	var slug  = label.toLowerCase().replace(/\s/g, '-');
	var color = '<div class="edd-legend-color" style="background-color: ' + series.color + '"></div>';
	var value = '<div class="edd-pie-legend-item">' + label + ': ' + Math.round(series.percent) + '% (' + eddFormatNumber(series.data[0][1]) + ')</div>';
	var item = '<div id="' + series.edd_vars.id + slug + '" class="edd-legend-item-wrapper">' + color + value + '</div>';

	jQuery('#edd-pie-legend-' + series.edd_vars.id).append( item );
	return item;
}

var eddLegendFormatterEarnings = function (label, series) {
	var slug  = label.toLowerCase().replace(/\s/g, '-');
	var color = '<div class="edd-legend-color" style="background-color: ' + series.color + '"></div>';
	var value = '<div class="edd-pie-legend-item">' + label + ': ' + Math.round(series.percent) + '% (' + eddFormatCurrency(series.data[0][1]) + ')</div>';
	var item = '<div id="' + series.edd_vars.id + slug + '" class="edd-legend-item-wrapper">' + color + value + '</div>';

	jQuery('#edd-pie-legend-' + series.edd_vars.id).append( item );
	return item;
}
