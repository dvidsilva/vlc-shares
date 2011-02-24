
$(document).ready(function() {
	
	debug.log('document.ready: /public/js/manage/index.js');
	
	$('#manage-statistics .statistic .clickable').click(function (event){
		debug.log('click');
		var $this = $(this);
		var $folded = $this.parent().find('.folded');
		$('#manage-statistics .statistic .folded').not($folded).slideUp('slow', function (event) {
			$folded.slideDown('slow');
		});
	}); 

	$('#manage-statistics .statistic .folded').not('#manage-statistics .statistic .folded:eq(0)').hide();
	
	// clickable in statistics has different event
	$('.clickable').not('#manage-statistics .statistic .clickable').click(function (event) {
		var $this = $(this);
		$(location).attr('href', $this.find('a').attr('href'));
	});
	
});