jQuery(document).ready(function ($) {
    //$('#edit-slug-box').remove();
    
    // date picker
    if ($('.form-table .edd_datepicker').length > 0) {
        var dateFormat = 'mm/dd/yy';
        $('.edd_datepicker').datepicker({
            dateFormat: dateFormat
        });
    }

	// toggle the pricing options
	$('#edd_variable_pricing').on('click', function() {
		$('.edd_pricing_fields').toggle();
		$('.edd_repeatable_condition_field').toggle();
	});
	
	// toggle the color options
	$('#edd_purchase_style input').change(function() {
		$('#edd_purchase_color').toggle();
	});
	
	// add new price option 
    $(".edd_add_new_price").on('click', function (e) {
        var $this = $(this),
            container = $this.closest('tr'),
            field = $this.closest('td').find("div.edd_variable_prices_wrapper:last").clone(true),
            fieldLocation = $this.closest('td').find('div.edd_variable_prices_wrapper:last');

        // get the hidden field that has the name value
        var name_field = $("input.edd_variable_prices_name_field", container);

        // set the base of the new field name
        var id = $(name_field).attr("id");

        // set the new field val to blank
        $('input[type="text"]', field).val("");

        // set up a count var
        var count = $('.edd_variable_prices_wrapper', container).size();
        
		field.find( 'input, select' ).each(function() {
			var name = $( this ).attr( 'name' );
			
			name = name.replace( /\[(\d+)\]/, function() {
				return '[' + count + ']'
			});
			
			$( this )
				.attr( 'name', name )
				.attr( 'id', name );
		});

        field.insertAfter(fieldLocation, $this.closest('td'));

        return false;
    });
	
    // add new repeatable upload field
    $(".edd_add_new_upload_field").on('click', function () {
        var $this = $(this),
            container = $this.closest('tr'),
            field = $this.closest('td').find("div.edd_repeatable_upload_wrapper:last").clone(true),
            fieldLocation = $this.closest('td').find('div.edd_repeatable_upload_wrapper:last');

        // get the hidden field that has the name value
        var name_field = $("input.edd_repeatable_upload_name_field", container);

        // set the base of the new field name
        var name = $(name_field).attr("id");

        // set the new field val to blank
        $('input[type="text"]', field).val("");

        // set up a count var
        var count = $('.edd_repeatable_upload_wrapper', container).size();

        file_name = name + '[' + count + '][name]';
        file_url = name + '[' + count + '][file]';
		condition = name + '[' + count + '][condition]';

        $('input.edd_repeatable_name_field', field).attr("name", file_name).attr("id", file_name);
        $('input.edd_repeatable_upload_field', field).attr("name", file_url).attr("id", file_url);
        $('select.edd_repeatable_condition_field', field).attr("name", condition).attr("id", condition);

        field.insertAfter(fieldLocation, $this.closest('td'));

        return false;
    });

    // remove repeatable field
    $('.edd_remove_repeatable').on('click', function (e) {
        e.preventDefault();
        var field = $(this).parent();
        $('input', field).val("");
        field.remove();
        return false;
    });

    if ($('.edd_upload_image_button').length > 0) {
        // Media Uploader
        window.formfield = '';
        
        $('.edd_upload_image_button').on('click', function (e) {
            e.preventDefault();
            window.formfield = $('.edd_upload_field', $(this).parent());
    		window.tbframe_interval = setInterval(function() {
    		    jQuery('#TB_iframeContent').contents().find('.savesend .button').val(edd_vars.use_this_file).end().find('#insert-gallery, .wp-post-thumbnail').hide();
    		}, 2000);
            if (edd_vars.post_id != null ) {
                var post_id = 'post_id=' + edd_vars.post_id + '&';
            }
            tb_show(edd_vars.add_new_download, 'media-upload.php?' + post_id +'TB_iframe=true');
        });
        
        window.original_send_to_editor = window.send_to_editor;
        window.send_to_editor = function (html) {            
            if (window.formfield) {
                imgurl = $('a', '<div>' + html + '</div>').attr('href');
                window.formfield.val(imgurl);
                window.clearInterval(window.tbframe_interval);
                tb_remove();
            } else {
                window.original_send_to_editor(html);
            }
            window.formfield = '';
            window.imagefield = false;
        }
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
	
	// show / hide the send purchase receipt check box on the Edit payment screen
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

    // show the email template previews
    if( $('#email-preview-wrap').length ) {
        $('#open-email-preview').colorbox({
            inline: true,
            href: '#email-preview',
            width: '80%',
            height: 'auto'
        });
    }
	
});