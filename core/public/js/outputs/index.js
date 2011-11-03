

$(document).ready(function() {
	
	debug.log('document.ready: /public/js/outputs/index.js');
	$('.quickbox-add').click(function (event) {
		$('#label').val("");
		$('#link').val("");
		$('#arg').val("");
		$('#priority').val("0");
		$('#cond_devices option:eq(0)').attr('selected', true);
		$('#id').val("");
		$('#formbox').css('top', event.pageY+'px').css('left', (event.pageX - 420)+'px').fadeIn('slow');
	});

	$('.ui-icon-trash').click(function (event) {
		var shareId = $(this).parentsUntil('.boxes').last().attr('id').split('-')[1];
		$(location).attr('href', baseUrl+'/outputs/remove/outputId/'+shareId);
	});
	
	$('.ui-icon-pencil').click(function (event) {
		var outputId = $(this).parentsUntil('.boxes').last().attr('id').split('-')[1];
		var label = $(this).parentsUntil('.boxes').last().find('.label').text().trim();
		var link = $(this).parentsUntil('.boxes').last().find('.link').attr('title').trim();
		var arg = $(this).parentsUntil('.boxes').last().find('.arg').attr('title').trim();
		var priority = $(this).parentsUntil('.boxes').last().find('.priority').val();
		var category = $(this).parentsUntil('.container').last().attr('id').split('-')[1];
		
		if ( category == 'null' ) category = '-1';
		
		$('#label').val(label);
		$('#link').val(link);
		$('#arg').val(arg);
		$('#priority').val(priority);
		$('#cond_devices option:selected').attr('selected', false);
		$('#cond_devices option[value="' +category+'"]').attr('selected', true);
		$('#id').val(outputId);
		
		$('#formbox').css('top', event.pageY+'px').css('left', (event.pageX - 420)+'px').fadeIn('slow');
	});
	
	$('#formbox #abort').click(function (event) {
		$('#formbox').fadeOut('slow');
	});
	
});