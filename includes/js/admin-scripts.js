jQuery(document).ready(function($) {
	//$('#edit-slug-box').remove();
	
	// date picker
	if($('.form-table .edd_datepicker').length > 0 ) {
		var dateFormat = 'mm/dd/yy';
		$('.edd_datepicker').datepicker({dateFormat: dateFormat});
	}

	// add new repeatable upload field
	$(".edd_add_new_upload_field").on('click', function() {	
		var $this = $(this);
		var container = $this.closest('tr');
		var field = $this.closest('td').find("div.edd_repeatable_upload_wrapper:last").clone(true);
		var fieldLocation = $this.closest('td').find('div.edd_repeatable_upload_wrapper:last');
		
		// get the hidden field that has the name value
		var name_field = $("input.edd_repeatable_upload_name_field", container);
		var file_field = $("input.edd_repeatable_upload_file_field", container);
		
		// set the base of the new field name
		var name = $(name_field).attr("id");
		var file_name = $(file_field).attr("id");

		// set the new field val to blank
		$('input[type="text"]', field).val("");
		
		// set up a count var
		var count = 0;
		$('.edd_repeatable_upload_field', container).each(function() {
			count = count + 1;
		});
		
		name = name + '[' + count + '][name]';
		file_name = file_name + '[' + count + '][file]';
		
		$('input.edd_repeatable_name_field', field).attr("name", name).attr("id", name);
		$('input.edd_repeatable_upload_field', field).attr("name", file_name).attr("id", file_name);
		
		field.insertAfter(fieldLocation, $this.closest('td'));

		return false;
	});

	// remove repeatable field
	$('.edd_remove_repeatable').on('click', function(e) {
		e.preventDefault();
		var field = $(this).parent();
		$('input', field).val("");
		field.remove();				
		return false;
	});											
	
	if($('.edd_upload_image_button').length > 0 ) {
		// Media Uploader
		window.formfield = '';

		$('.edd_upload_image_button').on('click', function(e) {
			e.preventDefault();
			window.formfield = $('.edd_upload_field',$(this).parent());
            tb_show('', 'media-upload.php?post_id='+edd_vars.post_id+'&TB_iframe=true');
        });
		window.send_to_editor = function(html) {
			if (window.formfield) {
				imgurl = $('a','<div>'+html+'</div>').attr('href');
				window.formfield.val(imgurl);
				tb_remove();
			}
			else {
				window.send_to_editor(html);
			}
			window.formfield = '';
			window.imagefield = false;
		}
	}
});
