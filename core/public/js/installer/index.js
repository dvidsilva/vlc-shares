/**
 * 
 */

$(document).ready(function() {
	
	$('form#installer #lang').change(function (event) {
		
		var value = $(this).find('option:selected').text().trim();
		
		var url = baseUrl + '/installer/index/lang/'+value;
		
		$(location).attr('href', url);
		
	});
	
});