jQuery(document).ready(function ($) {

	/**
	 * Download Configuration Metabox
	 */
	var EDD_Download_Configuration = {
		init : function() {
			this.add();
			this.remove();
			this.prices();
			this.files();
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

		remove : function() {
			$( 'body' ).on( 'click', '.edd_remove_repeatable', function(e) {
				e.preventDefault();

				var row   = $(this).parent().parent( 'tr' ),
					count = row.parent().find( 'tr' ).length - 1,
					type  = $(this).data('type'),
					repeatable = 'tr.edd_repeatable_' + type + 's';

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

		prices : function() {
			$( 'body' ).on( 'change', '#edd_variable_pricing', function(e) {
				$( '.edd_pricing_fields' ).toggle();
				$( '.edd_repeatable_condition_field' ).toggle();
				$( '#edd_download_files table .pricing' ).toggle();
			});
		},

		files : function() {
			if( typeof wp == "undefined" || edd_vars.new_media_ui != '1' ){
				//Old Thickbox uploader
				if ( $( '.edd_upload_image_button' ).length > 0 ) {
					window.formfield = '';

					$('body').on('click', '.edd_upload_image_button', function(e) {
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
					}
				}
			} else {
				// WP 3.5+ uploader
				var file_frame;
				window.formfield = '';

				$('body').on('click', '.edd_upload_image_button', function(e) {

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
					file_frame = wp.media.frames.file_frame = wp.media({
						frame: 'post',
						state: 'insert',
						title: button.data( 'uploader_title' ),
						button: {
							text: button.data( 'uploader_button_text' ),
						},
						multiple: true  // Set to true to allow multiple files to be selected
					});

					file_frame.on( 'menu:render:default', function(view) {
				        // Store our views in an object.
				        var views = {};

				        // Unset default menu items
				        view.unset('library-separator');
				        view.unset('gallery');
				        view.unset('featured-image');
				        view.unset('embed');

				        // Initialize the views in our view object.
				        view.set(views);
				    });

					// When an image is selected, run a callback.
					file_frame.on( 'insert', function() {

						var selection = file_frame.state().get('selection');
						selection.each( function( attachment, index ) {
							attachment = attachment.toJSON();
							if(index == 0){
								// place first attachment in field
								window.formfield.find('.edd_repeatable_upload_field').val(attachment.url);
								window.formfield.find('.edd_repeatable_name_field').val(attachment.title);
							} else{
								// Create a new row for all additional attachments
								var row = window.formfield,
								clone = EDD_Download_Configuration.clone_repeatable(row);
								clone.find('.edd_repeatable_upload_field').val(attachment.url);
								if(attachment.title.length > 0){
									clone.find('.edd_repeatable_name_field').val(attachment.title);
								}else{
									clone.find('.edd_repeatable_name_field').val(attachment.filename);
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

		}

	}

	EDD_Download_Configuration.init();

	//$('#edit-slug-box').remove();

	// Date picker
	if ($('.form-table .edd_datepicker').length > 0) {
		var dateFormat = 'mm/dd/yy';
		$('.edd_datepicker').datepicker({
			dateFormat: dateFormat
		});
	}

	$('#edd-add-download').on('click', function() {
		var downloads = [];
		$('.edd-download-to-add').each(function() {
			if($(this).is(':checked')) {
				var id = $(this).val();
				data = {
					action: 'edd_get_download_title',
					download_id: id
				};
				$.post(ajaxurl, data, function (response) {
					if (response != 'fail') {
						var html = '<div class="purchased_download_' + id + '"><input type="hidden" name="edd-purchased-downloads[]" value="' + id + '"/><strong>' + response + '</strong> - <a href="#" class="edd-remove-purchased-download" data-action="remove_purchased_download" data-id="' + id + '">Remove</a></div>';
						$(html).insertBefore('#edit-downloads');
					}
				});
			}
		});
		tb_remove();
		return false;
	});

	$('#purchased-downloads').on('click', '.edd-remove-purchased-download', function() {
		var $this = $(this);
		data = {
			action: $this.data('action'),
			download_id: $this.data('id')
		};
		$.post(ajaxurl, data, function (response) {
			if (response != 'fail') {
				$('.purchased_download_' + $this.data('id')).remove();
			}
		});
		return false;
	});

	// Add a New Download from the Add Downloads to Purchase Box
	$('#edd-add-downloads-to-purchase').on('click', '.edd-add-another-download', function() {
		var downloads_select_elem = $('#edd-add-downloads-to-purchase select:last').parent().clone();
		var count = $('#edd-add-downloads-to-purchase select').length;
		var download_section = $('#edd-add-downloads-to-purchase select:last').parent();

		$(downloads_select_elem).children('select').prop('name', 'downloads[' + count + '][id]');
		downloads_select_elem.insertAfter(download_section);

		return false;
	});

	// On Download Select, Check if Variable Prices Exist
	$('#edd-add-downloads-to-purchase').on('change', 'select.edd-downloads-list', function() {

	});

	// Show / hide the send purchase receipt check box on the Edit payment screen
	$('#edd_payment_status').change(function() {
		if( $('#edd_payment_status option:selected').val() == 'publish' ) {
			$('#edd_payment_notification').slideDown();
		} else {
			$('#edd_payment_notification').slideUp();
		}
	});

	$('.edd_payment .row-actions .delete a').on('click', function() {
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

	// Show the email template previews
	if( $('#email-preview-wrap').length ) {
		$('#open-email-preview').colorbox({
			inline: true,
			href: '#email-preview',
			width: '80%',
			height: 'auto'
		});
	}

	// Reporting
	$( '#edd-graphs-date-options' ).change( function() {
		var $this = $(this);
		if( $this.val() == 'other' ) {
			$( '#edd-date-range-options' ).show();
		} else {
			$( '#edd-date-range-options' ).hide();
		}
	});

	// Hide local tax opt in
    if( $('input[name="edd_settings_taxes[tax_condition]"]:checked').val() != 'local' ) {
        $('input[name="edd_settings_taxes[tax_condition]"]').parent().parent().next().hide();
    }
    // Toggle local tax option
    $('input[name="edd_settings_taxes[tax_condition]"]').on('change', function() {
        var tax_opt_in = $(this).parent().parent().next();
        if( $(this).val() == 'local' ) {
            tax_opt_in.fadeIn();
        } else {
            tax_opt_in.fadeOut();
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

});
