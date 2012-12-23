jQuery(document).ready(function($) {
	
	$('#download').click(function(e){

		$.generateFile({
			filename	: 'systeminfo.txt',
			content		: $('textarea').val(),
			script		: '../wp-content/plugins/easy-digital-downloads/includes/admin/download-system-info.php'
		});
		
		e.preventDefault();
	});
});