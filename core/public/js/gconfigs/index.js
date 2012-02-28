
function setVlcPath(path) {
	$('#vlc_path').val(path);
	$('#iframe_frm-vlc-path, #close_frm-vlc-path').fadeOut().remove();
	$('#browse_frm-vlc-path, #autosearch_frm-vlc-path').fadeIn();
}

function setSopCastPath(path) {
	$('#helpers_sopcast_path').val(path);
	$('#iframe_frm-helpers_sopcast_path, #close_frm-helpers_sopcast_path').fadeOut().remove();
	$('#browse_frm-helpers_sopcast_path').fadeIn();
}

function setRtmpDumpPath(path) {
	$('#helpers_rtmpdump_path').val(path);
	$('#iframe_frm-helpers_rtmpdump_path, #close_frm-helpers_rtmpdump_path').fadeOut().remove();
	$('#browse_frm-helpers_rtmpdump_path').fadeIn();
}

function setFFMpegPath(path) {
	$('#helpers_ffmpeg_path').val(path);
	$('#iframe_frm-helpers_ffmpeg_path, #close_frm-helpers_ffmpeg_path').fadeOut().remove();
	$('#browse_frm-helpers_ffmpeg_path').fadeIn();
}

var toggleAdvancedReady = false;
function toggleAdvanced(element) {
	if ( toggleAdvancedReady ) {
		$('form#autoconfigs .advanced').slideToggle();
	}
	//return false;
} 



$(document).ready(function() {
	
	debug.log('document.ready: /public/js/gconfigs/index.js');
	
	toggleAdvancedReady = true;
	
	$('form#autoconfigs .advanced').slideUp();
	
	
	//$('form#autoconfigs label.advanced').parent().hide();
	
	/*
	$('.ui-state-default').hover(function (event) {
		$(this).toggleClass('ui-state-hover');
	});
	*/

	/*
	$('form#autoconfigs #vlc_path').css({'max-width':'75%'}).after('<input id="browse_frm-vlc-path" width="24%" type="button" value="Browse" />');
	$('#browse_frm-vlc-path').click(function (event){
		var $iframe = $('<iframe id="iframe_frm-vlc-path" class="iframe-browser" src="' + baseUrl + '/configs/browse/f/file/c/setVlcPath" width="100%" height="300px"></iframe>')
		var $closeB = $('<input type="button" id="close_frm-vlc-path" value="Close" />').click(function() {
			$iframe.fadeOut(function() {
				$(this).remove();
			});
			$closeB.fadeOut(function() {
				$(this).remove();
			});
			$('#browse_frm-vlc-path, #autosearch_frm-vlc-path').fadeIn();
		});
		$('form#autoconfigs #vlc_path').after($iframe).after($closeB);
		$('#browse_frm-vlc-path, #autosearch_frm-vlc-path').fadeOut();
		//Elastic.refresh($iframe);
		//$('#iframe_frm-vlc-path').fadeIn();
	});
	
	$('form#autoconfigs #helpers_ffmpeg_path').css({'max-width':'75%'}).after('<input id="browse_frm-helpers_ffmpeg_path" type="button" value="Browse" />');
	$('#browse_frm-helpers_ffmpeg_path').click(function (event){
		var $iframe = $('<iframe id="iframe_frm-helpers_ffmpeg_path" class="iframe-browser" src="' + baseUrl + '/configs/browse/f/file/c/setFFMpegPath" width="100%" height="300px"></iframe>');
		var $closeB = $('<input id="close_frm-helpers_ffmpeg_path" type="button" value="Close" />').click(function() {
			$iframe.fadeOut(function() {
				$(this).remove();
			});
			$closeB.fadeOut(function() {
				$(this).remove();
			});
			$('#browse_frm-helpers_ffmpeg_path').fadeIn();
		});
		$('form#autoconfigs #helpers_ffmpeg_path').after($iframe).after($closeB);
		$('#browse_frm-helpers_ffmpeg_path').fadeOut();
		//$('#iframe_frm-helpers_ffmpeg_path').fadeIn();
	});

	$('form#autoconfigs #helpers_rtmpdump_path').css({'max-width':'75%'}).after('<input id="browse_frm-helpers_rtmpdump_path" type="button" value="Browse" />');
	$('#browse_frm-helpers_rtmpdump_path').click(function (event){
		var $iframe = $('<iframe id="iframe_frm-helpers_rtmpdump_path" class="iframe-browser" src="' + baseUrl + '/configs/browse/f/file/c/setRtmpDumpPath" width="100%" height="300px"></iframe>');
		var $closeB = $('<input type="button" id="close_frm-helpers_rtmpdump_path" value="Close" />').click(function() {
			$iframe.fadeOut(function() {
				$(this).remove();
			});
			$closeB.fadeOut(function() {
				$(this).remove();
			});
			$('#browse_frm-helpers_rtmpdump_path').fadeIn();
		});
		$('form#autoconfigs #helpers_rtmpdump_path').after($iframe).after($closeB);
		$('#browse_frm-helpers_rtmpdump_path').fadeOut();
		//$('#iframe_frm-helpers_ffmpeg_path').fadeIn();
	});
	
	
	$('form#autoconfigs #helpers_sopcast_path').css({'max-width':'75%'}).after('<input id="browse_frm-helpers_sopcast_path" type="button" value="Browse" />');
	$('#browse_frm-helpers_sopcast_path').click(function (event){
		var $iframe = $('<iframe id="iframe_frm-helpers_sopcast_path" class="iframe-browser" src="' + baseUrl + '/configs/browse/f/file/c/setSopCastPath" width="100%" height="300px"></iframe>');
		var $closeB = $('<input type="button" id="close_frm-helpers_sopcast_path" value="Close" />').click(function() {
			$iframe.fadeOut(function() {
				$(this).remove();
			});
			$closeB.fadeOut(function() {
				$(this).remove();
			});
			$('#browse_frm-helpers_sopcast_path').fadeIn();
		});
		$('form#autoconfigs #helpers_sopcast_path').after($iframe).after($closeB);
		$('#browse_frm-helpers_sopcast_path').fadeOut();
		//$('#iframe_frm-helpers_ffmpeg_path').fadeIn();
	});
	*/
	
	$('form#autoconfigs .row.fileBrowser').each(function (i, row) {
		var $row = $(row);
		var $input = $(row).find('input');
		var $newInput = $('<input />');
		var callback = 'callback_set_'+$input.attr('id');
		var apiUrl = baseUrl + "/gconfigs/browse/f/" + ($row.hasClass('fileBrowser-dir') ? 'folder' : 'file') + '/c/'+callback;
		
		window[callback] = function (path) {
			$input.val(path);
			$row.find('.iframe-browser').remove();
			$row.find('.iframe-close').remove();
			$row.find('.iframe-browse').fadeIn();
		};
		
		$newInput.attr({
			type	:	'button',
			value	:	'Browse',
			class	:	'iframe-browse'
		}).click(function (event) {
			var $iframe = $('<iframe></iframe>');
			$iframe
				.attr("src", apiUrl)
				.addClass('iframe-browser')
				.css({
					width	:	'100%',
					height	:	'300px'
				});
			var $closeB = $('<input />');
			$closeB.attr({
					type	:	'button',
					value	:	'Close',
				})
				.addClass('iframe-close')
				.click(function (innerE) {
					$iframe.fadeOut(function () {$(this).remove();});
					$closeB.fadeOut(function () {$(this).remove();});
				});
			$input.after($iframe).after($closeB);
			$newInput.fadeOut();
		});
		$input.css({'max-width':'75%'}).after($newInput);
		
	});
	
	
	
});