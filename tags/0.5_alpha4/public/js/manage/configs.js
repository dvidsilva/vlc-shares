
$(document).ready(function() {
	
	debug.log('document.ready: /public/js/manage/index.js');
	
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
	
	
});