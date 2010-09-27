

$(document).ready(function() {
	
	debug.log('document.ready: /public/js/filesystem/index.js');
	$('.quickbox-add').click(function (event) {
		$('#label').val("");
		$('#path').val("");
		$('#id').val("");
		$('#formbox').css('top', event.pageY+'px').css('left', (event.pageX - 280)+'px').fadeIn('slow');
	});

	$('.ui-icon-trash').click(function (event) {
		var shareId = $(this).parentsUntil('.boxes').last().attr('id').split('-')[1];
		$(location).attr('href', baseUrl+'/filesystem/remove/shareId/'+shareId);
	});
	
	$('.ui-icon-pencil').click(function (event) {
		var shareId = $(this).parentsUntil('.boxes').last().attr('id').split('-')[1];
		var label = $(this).parentsUntil('.boxes').last().find('.label').text().trim();
		var path = $(this).parentsUntil('.boxes').last().find('.path').text().trim();
		
		$('#label').val(label);
		$('#path').val(path);
		$('#id').val(shareId);
		
		$('#formbox').css('top', event.pageY+'px').css('left', (event.pageX - 280)+'px').fadeIn('slow');
	});
	
	$('#formbox #abort').click(function (event) {
		$('#formbox').fadeOut('slow');
	});
	
});