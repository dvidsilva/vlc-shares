/**
 * 
 */
$(document).ready(function() {
	/*
	$('#megavideo-categories tr[@class!="header"] td:first-child a').click(function (event) {
		debug.log('click');
		event.preventDefault();
		var $this = $(this);
		var requestUrl = $this.attr('href');
		debug.log('Request: '+requestUrl);
		$('#megavideo-categories tr[@class!="header"] td:first-child div').slideUp(function () {
			$(this).remove();
		});
		debug.log('Slided');
		$div = $('<div></div>').css('margin', '1em inherit').hide();
		$div.load(requestUrl + ' #megavideo-videos table.table', function () {
			// success trigger
			$this.parent().append($div);
			$div.slideDown();
		});
	});
	*/
	$('.megavideo-category-rename').click(function () {
		str_p_megavideo_manage_newcategoryname = typeof(str_p_megavideo_manage_newcategoryname) == 'undefined' ? 'New category name' : str_p_megavideo_manage_newcategoryname;
		event.preventDefault();
		var categoryName = prompt(str_p_megavideo_manage_newcategoryname, 'Default');
		if ( categoryName.trim() != "")
			window.location = $(this).attr('href') + escape(categoryName.trim());
	});
	
});