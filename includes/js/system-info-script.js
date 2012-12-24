jQuery(document).ready(function($) {
	
	$('#download').click(function(e){

		$.generateFile({
			filename	: 'systeminfo.txt',
			content		: $('textarea').val(),
			script		: edd_system_info_scripts_vars.content_url,
		});
		
		e.preventDefault();
	});
});