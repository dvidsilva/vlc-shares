
function setPath(path) {
	$('form #path').val(path);
	$('#close_frm-path').fadeOut().remove();
	$('#iframe_frm-path').fadeOut().remove();
	$('#browse_frm-path').fadeIn();
}


$(document).ready(function() {
	
	debug.log('document.ready: /public/js/filesystem/index.js');

	$('form #path').css({'max-width':'75%'}).after('<input id="browse_frm-path" width="24%" type="button" value="Browse" />');
	$('#browse_frm-path').click(function (event){
		var $iframe = $('<iframe id="iframe_frm-path" class="iframe-browser" src="' + baseUrl + '/configs/browse/f/folder/c/setPath" width="100%" height="300px"></iframe>')
		var $closeB = $('<input type="button" value="Close" id="close_frm-path" />').click(function() {
			$iframe.fadeOut(function() {
				$(this).remove();
			});
			$closeB.fadeOut(function() {
				$(this).remove();
			});
			$('#browse_frm-path, #autosearch_frm-path').fadeIn();
		});
		$('form #path').after($iframe).after($closeB);
		$('#browse_frm-path').fadeOut();
		//Elastic.refresh($iframe);
		//$('#iframe_frm-vlc-path').fadeIn();
	});
	
});