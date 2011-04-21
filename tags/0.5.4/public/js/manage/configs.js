
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
function toggleAdvanced() {
	if ( toggleAdvancedReady ) {
		$('form#configs .advanced').slideToggle();
	}
	//return false;
} 



$(document).ready(function() {
	
	debug.log('document.ready: /public/js/manage/configs.js');
	
	toggleAdvancedReady = true;
	
	$('.clickable').click(function (event){
		var $this = $(this);
		var $folded = $this.parent().find('.folded');
		$('.folded').not($folded).slideUp('slow', function (event) {
			$folded.slideDown('slow');
		});
	}); 

	$('.folded').not('.folded:eq(0)').hide();
	
	// clickable in statistics has different event
	/*
	$('.clickable').not('#manage-statistics .statistic .clickable').click(function (event) {
		var $this = $(this);
		$(location).attr('href', $this.find('a').attr('href'));
	});
	*/
	/*
	$showAdv = $('<span style="float: right;"><label for="showAdvaced">'+showAdvLabel+'</label><input type="checkbox" id="showAdvanced" /></span>');
	$('#configs-container h2').before($showAdv);
	
	$('#showAdvanced').change(function (event){
		if ( $(this).is(':checked') ) {
			$('form#configs .advanced').slideDown();
			//$('form#configs label.advanced').parent().fadeIn();
		} else {
			$('form#configs .advanced').slideUp();
			//$('form#configs label.advanced').parent().fadeOut();
		}
	});
	*/
	
	$('form#configs .advanced').slideUp();
	//$('form#configs label.advanced').parent().hide();
	
	/*
	$('.ui-state-default').hover(function (event) {
		$(this).toggleClass('ui-state-hover');
	});
	*/
	
	// uninstall
	$('.plugin .ui-icon-trash').click(function (event) {
		event.stopPropagation(); // no unfold
		event.preventDefault(); // no unfold
		var pluginId = $(this).parentsUntil('.boxes').last().attr('id').split('-')[1];
		$(location).attr('href', baseUrl+'/manage/uninstall/pluginId/'+pluginId);
	});

	// disable
	$('.plugin .ui-icon-cancel').click(function (event) {
		event.stopPropagation(); // no unfold
		event.preventDefault(); // no unfold
		var pluginId = $(this).parentsUntil('.boxes').last().attr('id').split('-')[1];
		$(location).attr('href', baseUrl+'/manage/disable/pluginId/'+pluginId);
	});
	
	// enable
	$('.plugin .ui-icon-check').click(function (event) {
		event.stopPropagation(); // no unfold
		event.preventDefault(); // no unfold
		var pluginId = $(this).parentsUntil('.boxes').last().attr('id').split('-')[1];
		$(location).attr('href', baseUrl+'/manage/enable/pluginId/'+pluginId);
	});

	$('form#configs #vlc_path').css({'max-width':'75%'}).after('<input id="browse_frm-vlc-path" width="24%" type="button" value="Browse" />');
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
		$('form#configs #vlc_path').after($iframe).after($closeB);
		$('#browse_frm-vlc-path, #autosearch_frm-vlc-path').fadeOut();
		//Elastic.refresh($iframe);
		//$('#iframe_frm-vlc-path').fadeIn();
	});
	
	$('form#configs #helpers_ffmpeg_path').css({'max-width':'75%'}).after('<input id="browse_frm-helpers_ffmpeg_path" type="button" value="Browse" />');
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
		$('form#configs #helpers_ffmpeg_path').after($iframe).after($closeB);
		$('#browse_frm-helpers_ffmpeg_path').fadeOut();
		//$('#iframe_frm-helpers_ffmpeg_path').fadeIn();
	});

	$('form#configs #helpers_rtmpdump_path').css({'max-width':'75%'}).after('<input id="browse_frm-helpers_rtmpdump_path" type="button" value="Browse" />');
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
		$('form#configs #helpers_rtmpdump_path').after($iframe).after($closeB);
		$('#browse_frm-helpers_rtmpdump_path').fadeOut();
		//$('#iframe_frm-helpers_ffmpeg_path').fadeIn();
	});
	
	
	$('form#configs #helpers_sopcast_path').css({'max-width':'75%'}).after('<input id="browse_frm-helpers_sopcast_path" type="button" value="Browse" />');
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
		$('form#configs #helpers_sopcast_path').after($iframe).after($closeB);
		$('#browse_frm-helpers_sopcast_path').fadeOut();
		//$('#iframe_frm-helpers_ffmpeg_path').fadeIn();
	});
	
	
	var $apply = $('<button id="apply" type="button" name="apply">'+applyLabel+'</button>');
	$('#submit').after($apply);
	
	$apply.click(function (event) {
		
		$('form#configs #isapply').val('1');
		$('form#configs #submit').click();
		
	});
	
});