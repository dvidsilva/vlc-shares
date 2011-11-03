$(document).ready(function() {
	
	debug.log('document.ready: /public/js/backupper/index.js');
	
	$('#select-all').click(function (event) {
		$(this).parent().parent().find('input[type="checkbox"]').attr('checked', 'checked');
	});
	
});