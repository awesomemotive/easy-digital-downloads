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

			clone = row.clone();

			/** manually update any select box values */
			clone.find( 'select' ).each(function() {
				$( this ).val( row.find( 'select[name="' + $( this ).attr( 'name' ) + '"]' ).val() );
			});

			var count  = row.parent().find( 'tr' ).length - 1;

			clone.removeClass( 'edd_add_blank' );

			clone.find( 'td input, td select' ).val( '' );
			clone.find( 'input, select' ).each(function() {
				var name 	= $( this ).attr( 'name' );

				name = name.replace( /\[(\d+)\]/, '[' + parseInt( count ) + ']');

				$( this ).attr( 'name', name ).attr( 'id', name );
			});

			return clone;
		},

		add : function() {
			$( 'body' ).on( 'click', '.submit .edd_add_repeatable', function(e) {
				e.preventDefault();
				var button = $( this ),
				row = button.parent().parent().prev( 'tr' ),
				clone = EDD_Download_Configuration.clone_repeatable(row);
				clone.insertAfter( row );
			});
		},

		move : function() {
			/*
			* Disabled until we can work out a way to solve the issues raised here: https://github.com/easydigitaldownloads/Easy-Digital-Downloads/issues/1066
			if( ! $('.edd_repeatable_table').length )
				return;

			$(".edd_repeatable_table tbody").sortable({
				handle: '.edd_draghandle', items: '.edd_repeatable_row', opacity: 0.6, cursor: 'move', axis: 'y', update: function() {
					var count  = 0;
					$(this).find( 'tr' ).each(function() {
						$(this).find( 'input, select' ).each(function() {
							var name   = $( this ).attr( 'name' );
							name       = name.replace( /\[(\d+)\]/, '[' + count + ']');
							$( this ).attr( 'name', name ).attr( 'id', name );
						});
						count++;
					});
				}
			});
			*/
		},

		remove : function() {
			$( 'body' ).on( 'click', '.edd_remove_repeatable', function(e) {
				e.preventDefault();

				var row   = $(this).parent().parent( 'tr' ),
					count = row.parent().find( 'tr' ).length - 1,
					type  = $(this).data('type'),
					repeatable = 'tr.edd_repeatable_' + type + 's';

				/** remove from price condition */
			    $( '.edd_repeatable_condition_field option[value=' + row.index() + ']' ).remove();

				if( count > 1 ) {
					$( 'input, select', row ).val( '' );
					row.fadeOut( 'fast' ).remove();
				} else {
					switch( type ) {
						case 'price' :
							alert( edd_vars.one_price_min );
							break;
						case 'file' :
							alert( edd_vars.one_file_min );
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

			$( 'body' ).on( 'change', '#_edd_product_type', function(e) {

				if ( 'bundle' === $( this ).val() ) {
					$( '#edd_products' ).show();
					$( '#edd_download_files' ).hide();
					$( '#edd_download_limit_wrap' ).hide();
				} else {
					$( '#edd_products' ).hide();
					$( '#edd_download_files' ).show();
					$( '#edd_download_limit_wrap' ).show();
				}

			});

		},

		prices : function() {
			$( 'body' ).on( 'change', '#edd_variable_pricing', function(e) {
				$( '.edd_pricing_fields,.edd_repeatable_table .pricing' ).toggle();
			});
		},

		files : function() {
			if( typeof wp === "undefined" || '1' !== edd_vars.new_media_ui ){
				//Old Thickbox uploader
				if ( $( '.edd_upload_file_button' ).length > 0 ) {
					window.formfield = '';

					$('body').on('click', '.edd_upload_file_button', function(e) {
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

				$('body').on('click', '.edd_upload_file_button', function(e) {

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
						title: button.data( 'uploader_title' ),
						button: {
							text: button.data( 'uploader_button_text' )
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
								window.formfield.find( '.edd_repeatable_upload_field' ).val( attachment.url );
								window.formfield.find( '.edd_repeatable_name_field' ).val( attachment.title );
							} else {
								// Create a new row for all additional attachments
								var row = window.formfield,
									clone = EDD_Download_Configuration.clone_repeatable( row );
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

				var key = $( this ).parents( 'tr' ).index(),
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
	if ( $( '.edd_datepicker' ).length > 0 ) {
		var dateFormat = 'mm/dd/yy';
		$( '.edd_datepicker' ).datepicker( {
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
			this.recalculate_total();
			this.variable_prices_check();
			this.status_change();
			this.add_note();
			this.remove_note();
			this.resend_receipt();
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
					if( 'nostates' == response ) {
						$('#edd-order-address-state-wrap select, #edd-order-address-state-wrap input').replaceWith( '<input type="text" name="edd-payment-address[0][state]" value="" class="edd-edit-toggles medium-text"/>' );
					} else {
						$('#edd-order-address-state-wrap select, #edd-order-address-state-wrap input').replaceWith( response );
					}
				});

				return false;
			});

		},

		remove_download : function() {

			// Remove a download from a purchase
			$('#edd-purchased-files').on('click', '.edd-order-remove-download', function() {
				if( confirm( edd_vars.delete_payment_download ) ) {
					$(this).parent().parent().parent().remove();
					// Flag the Downloads section as changed
					$('#edd-payment-downloads-changed').val(1);
					$('.edd-order-payment-recalc-totals').show();
				}
				return false;
			});

		},


		add_download : function() {

			// Add a New Download from the Add Downloads to Purchase Box
			$('#edd-purchased-files').on('click', '#edd-order-add-download', function(e) {

				e.preventDefault();

				var download_id    = $('#edd_order_download_select').val();
				var download_title = $('.chosen-single span').text();
				var amount         = $('#edd-order-download-amount').val();
				var price_id       = $('.edd_price_options_select option:selected').val();
				var price_name     = $('.edd_price_options_select option:selected').text();
				var quantity       = $('#edd-order-download-quantity').val();

				var extra_columns = $('.extra-column input');

				if( download_id < 1 ) {
					return false;
				}

				if( ! amount ) {
					amount = '0.00';
				}

				var formatted_amount = amount + edd_vars.currency_sign;
				if ( 'before' === edd_vars.currency_pos ) {
					formatted_amount = edd_vars.currency_sign + amount;
				}

				if( price_name ) {
					download_title = download_title + ' - ' + price_name;
				}

				var count = $('#edd-purchased-files div.row').length;
				var clone = $('#edd-purchased-files div.row:last').clone();

				clone.find( '.download span' ).text( download_title );
				clone.find( '.price' ).text( formatted_amount );
				clone.find( '.quantity span' ).text( quantity );
				clone.find( 'input.edd-payment-details-download-id' ).val( download_id );
				clone.find( 'input.edd-payment-details-download-price-id' ).val( price_id );
				clone.find( 'input.edd-payment-details-download-amount' ).val( amount );
				clone.find( 'input.edd-payment-details-download-quantity' ).val( quantity );

				// Process the extra columns so the input value is copied into the item row
				extra_columns.each(function() {
					var name = $( this ).attr( 'name' );
					var value = $( this ).val();
					name = name.replace("edd-order-download-", "");
					clone.find( 'input.edd-payment-details-download-' + name ).val( value );
					clone.find( '.extra-column' ).text( value );
				});

				// Replace the name / id attributes
				clone.find( 'input' ).each(function() {
					var name = $( this ).attr( 'name' );
					name = name.replace( /\[(\d+)\]/, '[' + parseInt( count ) + ']');

					$( this ).attr( 'name', name ).attr( 'id', name );
				});

				// Flag the Downloads section as changed
				$('#edd-payment-downloads-changed').val(1);

				$(clone).insertAfter( '#edd-purchased-files div.row:last' );
				$('.edd-order-payment-recalc-totals').show();

			});
		},

		recalculate_total : function() {

			// Remove a download from a purchase
			$('#edd-order-recalc-total').on('click', function(e) {
				e.preventDefault();
				var total = 0;
				$('#edd-purchased-files .edd-payment-details-download-amount').each(function() {
					var quantity = $(this).next().val();
					total += ( parseFloat( $(this).val() ) * parseInt( quantity ) );
				});
				$('.edd-payment-fees span.fee-amount').each(function() {
					total += parseFloat( $(this).data('fee') );
				});
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

		status_change : function() {

			// Show / hide the send purchase receipt check box on the Edit payment screen
			$('#edd_payment_status').change(function() {
				if ( 'publish' === $( '#edd_payment_status option:selected' ).val() ) {
					$( '#edd_payment_notification' ).slideDown();
				} else {
					$( '#edd_payment_notification' ).slideUp();
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

			$('body').on('click', '.edd-delete-payment-note', function(e) {

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
			$( 'body' ).on( 'click', '#edd-resend-receipt', function( e ) {
				return confirm( edd_vars.resend_receipt );
			} );
		}

	};
	EDD_Edit_Payment.init();


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
				var $this = $(this);
				if ( 'other' === $this.val() ) {
					$( '#edd-date-range-options' ).show();
				} else {
					$( '#edd-date-range-options' ).hide();
				}
			});

		},

		customers_export : function() {

			// Show / hide Download option when exporting customers

			$( '#edd_customer_export_download' ).change( function() {
				var $this = $(this);
				if ( '0' === $this.val() ) {
					$( '#edd_customer_export_option' ).show();
				} else {
					$( '#edd_customer_export_option' ).hide();
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

			if( $('.edd-color-picker').length ) {
				$('.edd-color-picker').wpColorPicker();
			}

			// Settings Upload field JS
			if ( typeof wp === "undefined" || '1' !== edd_vars.new_media_ui ) {
				//Old Thickbox uploader
				if ( $( '.edd_settings_upload_button' ).length > 0 ) {
					window.formfield = '';

					$('body').on('click', '.edd_settings_upload_button', function(e) {
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

				$('body').on('click', '.edd_settings_upload_button', function(e) {

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
			$('body').on('change', '#edd_tax_rates select.edd-tax-country', function() {
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
				clone.find( 'td input' ).val( '' );
				clone.find( 'input, select' ).each(function() {
					var name = $( this ).attr( 'name' );
					name = name.replace( /\[(\d+)\]/, '[' + parseInt( count ) + ']');
					$( this ).attr( 'name', name ).attr( 'id', name );
				});
				clone.insertAfter( row );
				return false;
			});

			// Remove tax row
			$('body').on('click', '#edd_tax_rates .edd_remove_tax_rate', function() {
				if( confirm( edd_vars.delete_tax_rate ) )
					$(this).closest('tr').remove();
				return false;
			});

		},

		emails : function() {

			// Show the email template previews
			if( $('#email-preview-wrap').length ) {
				var emailPreview = $('#email-preview');
				$('#open-email-preview').colorbox({
					inline: true,
					href: emailPreview,
					width: '80%',
					height: 'auto'
				});
			}

		},

		misc : function() {

			// Hide Symlink option if Download Method is set to Direct
			if( $('select[name="edd_settings[download_method]"]:selected').val() != 'direct' ) {
				$('select[name="edd_settings[download_method]"]').parent().parent().next().hide();
				$('select[name="edd_settings[download_method]"]').parent().parent().next().find('input').attr('checked', false);
			}
			// Toggle download method option
			$('select[name="edd_settings[download_method]"]').on('change', function() {
				var symlink = $(this).parent().parent().next();
				if( $(this).val() == 'direct' ) {
					symlink.hide();
				} else {
					symlink.show();
					symlink.find('input').attr('checked', false);
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
		inlineEditPost.revert();

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
    $( 'body' ).on( 'click', '#bulk_edit', function() {

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
			action: 		'edd_save_bulk_edit',
			edd_bulk_nonce:	$post_ids,
			post_ids:		$post_ids,
			price:			$price
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

	// Variables for setting up the typing timer
	var typingTimer;               // Timer identifier
	var doneTypingInterval = 342;  // Time in ms, Slow - 521ms, Moderate - 342ms, Fast - 300ms

    // Replace options with search results
	$('.edd-select.chosen-container .chosen-search input, .edd-select.chosen-container .search-field input').keyup(function(e) {

		var val = $(this).val(), container = $(this).closest( '.edd-select-chosen' );
		var menu_id = container.attr('id').replace( '_chosen', '' );
		var lastKey = e.which;

		// Don't fire if short or is a modifier key (shift, ctrl, apple command key, or arrow keys)
		if(
			val.length <= 3 ||
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
						action: 'edd_download_search',
						s: val,
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
						console.log( data );
					}
				}).done(function (response) {

		        });
			},
			doneTypingInterval
		);
	});

	// This fixes the Chosen box being 0px wide when the thickbox is opened
	$('.edd-thickbox').on('click', function() {
		$('#choose-download .edd-select-chosen').css('width', '100%');
	});

});
