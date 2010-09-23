
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
	
});