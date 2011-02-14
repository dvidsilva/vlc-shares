

$(document).ready(function() {
	
	debug.log('document.ready: /public/js/profiles/index.js');
	/*
	$('.quickbox-add').click(function (event) {
		$('#label').val("");
		$('#arg').val("");
		$('#weight').val(0);
		$('#audio option:selected').attr('selected', false);
		$('#audio option:eq(0)').attr('selected', true);
		$('#video option:selected').attr('selected', false);
		$('#video option:eq(0)').attr('selected', true);
		$('#device option:selected').attr('selected', false);
		$('#device option:eq(0)').attr('selected', true);
		$('#id').val("");
		$('#formbox').slideDown('slow');
	});
	*/
	/*

	$('.ui-icon-trash').click(function (event) {
		var profileId = $(this).parentsUntil('.table-body').last().attr('id').split('-')[1];
		$(location).attr('href', baseUrl+'/profiles/remove/profileId/'+profileId);
	});
	
	$('.ui-icon-pencil').click(function (event) {
		var profileId = $(this).parentsUntil('.table-body').last().attr('id').split('-')[1];
		var label = $(this).parentsUntil('.table-body').last().find('.label').text().trim();
		var codec = $(this).parentsUntil('.table-body').last().find('.codec').attr('title');
		var device = $(this).parentsUntil('.table-body').last().find('.device').attr('title');
		var arg = $(this).parentsUntil('.table-body').last().find('.arg').attr('title');
		var priority = $(this).parentsUntil('.table-body').last().find('.priority').attr('title');

		debug.log('codec: '+codec);
		debug.log('Device: '+device);
		
		
		if ( codec == '' || typeof(codec) == 'undefined' ) codec = 'unknown+unknown';
		if ( device == '' || typeof(device) == 'undefined' ) device = 'unknown';
		
		var vcodec = codec.split('+', 2)[0];
		var acodec = codec.split('+', 2)[1];
		
		debug.log('Vcodec: '+vcodec);
		debug.log('Device: '+device);
		
		$('#label').val(label);
		$('#arg').val(arg);
		$('#weight').val(priority);
		$('#audio option:selected').attr('selected', false);
		$('#audio option[value="' +acodec+'"]').attr('selected', true);
		$('#video option:selected').attr('selected', false);
		$('#video option[value="' +vcodec+'"]').attr('selected', true);
		$('#device option:selected').attr('selected', false);
		$('#device option[value="' +device+'"]').attr('selected', true);
		$('#id').val(profileId);
		
		$('#formbox').slideDown('slow');
	});
	
	$('#formbox #abort').click(function (event) {
		$('#formbox').slideUp('slow');
	});
	*/
	
	$('#test').click(function (event) {
		// reset old highlighted row
		
		$('.highlight').removeClass('highlight');
		
		var vcodec = $('#test_vcodec option:selected').val();
		var acodec = $('#test_acodec option:selected').val();
		var device = $('#test_device option:selected').val();

		$.getJSON(baseUrl+'/profiles/test/video/'+vcodec+'/audio/'+acodec+'/device/'+device, function (data, textStatus) {
			$('#profileId-'+data.profileId).addClass('highlight');
		});
		
	});
	
	$('#test-reset').click(function (event) {
		
		$('.highlight').removeClass('highlight');
		$('#test_vcodec option:selected').attr('selected', false);
		$('#test_vcodec option:eq(0)').attr('selected', true);
		$('#test_acodec option:selected').attr('selected', false);
		$('#test_acodec option:eq(0)').attr('selected', true);
		$('#test_device option:selected').attr('selected', false);
		$('#test_device option:eq(0)').attr('selected', true);
		
	});
	
});