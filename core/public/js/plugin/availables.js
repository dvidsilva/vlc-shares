/**
 * 
 */

$(document).ready(function() {
	
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
	
	
	$('.installtrigger').click(function (e) {
		var reqUrl = baseUrl + '/plugin/iconfirm/key/';
		var $this = $(this);
		var pKey = $this.val();
		$(location).attr('href', reqUrl + pKey);
	});
	
});