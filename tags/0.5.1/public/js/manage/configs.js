
function setVlcPath(path) {
	$('#vlc_path').val(path);
	$('#iframe_frm-vlc-path').fadeIn().remove();
	$('#browse_frm-vlc-path, #autosearch_frm-vlc-path').fadeIn();
}

function setMediainfoPath(path) {
	$('#helpers_mediainfo_path').val(path);
	$('#iframe_frm-helpers_mediainfo_path').fadeIn().remove();
	$('#browse_frm-helpers_mediainfo_path').fadeIn();
}

function setFFMpegPath(path) {
	$('#helpers_ffmpeg_path').val(path);
	$('#iframe_frm-helpers_ffmpeg_path').fadeIn().remove();
	$('#browse_frm-helpers_ffmpeg_path').fadeIn();
}


$(document).ready(function() {
	
	debug.log('document.ready: /public/js/manage/configs.js');
	
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
	
	$showAdv = $('<span style="float: right;"><label for="showAdvaced">'+showAdvLabel+'</label><input type="checkbox" id="showAdvanced" /></span>');
	$('#configs-container h2').before($showAdv);
	
	$('#showAdvanced').change(function (event){
		if ( $(this).is(':checked') ) {
			$('form#configs dd.advanced').fadeIn();
			$('form#configs label.advanced').parent().fadeIn();
		} else {
			$('form#configs dd.advanced').fadeOut();
			$('form#configs label.advanced').parent().fadeOut();
		}
	});
	
	$('form#configs dd.advanced').hide();
	$('form#configs label.advanced').parent().hide();
	
	$('.ui-state-default').hover(function (event) {
		$(this).toggleClass('ui-state-hover');
	});
	
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
	
	
	$('form#configs #vlc_path').parent().append('<input id="autosearch_frm-vlc-path" type="button" value="Auto-search" />');
	$('#autosearch_frm-vlc-path').click(function (event){
		$.getJSON(baseUrl + '/manage/autosearch', function(data){
			if ( !data.error ) {
				$('form#configs #vlc_path').val(data.path);
			}
		});
	});

	$('form#configs #vlc_path').parent().append('<input id="browse_frm-vlc-path" type="button" value="Browse" />');
	$('#browse_frm-vlc-path').click(function (event){
		$('#browse_frm-vlc-path').after('<iframe id="iframe_frm-vlc-path" style="display:none;" src="' + baseUrl + '/manage/browse/f/file/c/setVlcPath" width="100%" height="300px"></iframe>');
		$('#browse_frm-vlc-path, #autosearch_frm-vlc-path').fadeOut();
		$('#iframe_frm-vlc-path').fadeIn();
	});
	
	$('form#configs #helpers_mediainfo_path').parent().append('<input id="browse_frm-helpers_mediainfo_path" type="button" value="Browse" />');
	$('#browse_frm-helpers_mediainfo_path').click(function (event){
		$('#browse_frm-helpers_mediainfo_path').after('<iframe id="iframe_frm-helpers_mediainfo_path" style="display:none;" src="' + baseUrl + '/manage/browse/f/file/c/setMediainfoPath" width="100%" height="300px"></iframe>');
		$('#browse_frm-helpers_mediainfo_path').fadeOut();
		$('#iframe_frm-helpers_mediainfo_path').fadeIn();
	});
	
	$('form#configs #helpers_ffmpeg_path').parent().append('<input id="browse_frm-helpers_ffmpeg_path" type="button" value="Browse" />');
	$('#browse_frm-helpers_ffmpeg_path').click(function (event){
		$('#browse_frm-helpers_ffmpeg_path').after('<iframe id="iframe_frm-helpers_ffmpeg_path" style="display:none;" src="' + baseUrl + '/manage/browse/f/file/c/setFFMpegPath" width="100%" height="300px"></iframe>');
		$('#browse_frm-helpers_ffmpeg_path').fadeOut();
		$('#iframe_frm-helpers_ffmpeg_path').fadeIn();
	});
	
	
	var $apply = $('<button id="apply" type="button" name="apply">'+applyLabel+'</button>');
	$('#submit').after($apply);
	
	$apply.click(function (event) {
		
		$('form#configs #isapply').val('1');
		$('form#configs #submit').click();
		
	});
	
});