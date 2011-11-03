/**
 * 
 */
$(document).ready(function() {
	$('.onlinelibrary-category-rename').click(function () {
		str_p_onlinelibrary_manage_newcategoryname = typeof(str_p_onlinelibrary_manage_newcategoryname) == 'undefined' ? 'New category name' : str_p_onlinelibrary_manage_newcategoryname;
		event.preventDefault();
		var categoryName = prompt(str_p_onlinelibrary_manage_newcategoryname, 'Default');
		if ( categoryName.trim() != "")
			window.location = $(this).attr('href') + escape(categoryName.trim());
	});
	
});