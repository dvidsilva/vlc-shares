/**
 * 
 */

$(document).ready(function() {
	
	$('form#installer #lang').change(function (event) {
		
		var value = $(this).find('option:selected').text().trim();
		
		var url = baseUrl + '/installer/index/lang/'+value;
		
		$(location).attr('href', url);
		
	});
	
	function loadImg() {
		$('.installer-plugin-thumb[data-src!=""]').each(function (i, item) {
			var $item = $(item);
			var $img = $('<img />')
			$img.load(function (e) {
				$item.attr('src', $item.attr('data-src'));
			}).attr('src', $item.attr('data-src'));
		});
	}
	
	// set image loading after 2 seconds
	setTimeout(loadImg, 2000);
	
	
});