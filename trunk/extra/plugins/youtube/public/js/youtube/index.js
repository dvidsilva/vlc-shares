
function youtube_newcategory() {
	$('#p_youtube_framecontainer').empty().load(baseUrl + '/youtube/category #p_youtube_categoryform', function () {
		//Elastic.reset();
		Elastic.refresh();
		$('#p_youtube_framecontainer #thumbselect').change(function (event) {
			var $this = $(this);
			var value = $(this).val();
			$('#p_youtube_framecontainer #p_youtube_thumbnailpreview').fadeOut('slow', function () {
				$(this).find('img').remove();
				if ( value != '' ) {
					$(this).append('<img src="' + baseUrl + '/images/youtube/uploads/' + value  + '" />').fadeIn('slow');
				}
			});
			if ( value == 'upload' ) {
				$('#p_youtube_framecontainer #thumbnail').parents('.row').fadeIn('slow');
			} else {
				$('#p_youtube_framecontainer #thumbnail').parents('.row').fadeOut('slow');
			}
		});
	});
}

function youtube_newaccount() {
	$('#p_youtube_framecontainer').empty().load(baseUrl + '/youtube/account #p_youtube_accountform', function() {
		//Elastic.reset();
		Elastic.refresh();
		$('#p_youtube_framecontainer input[type="reset"]').click(function() {
			$('#p_youtube_framecontainer').empty();
		});
	});
}

function youtube_newvideo() {
	$('#p_youtube_framecontainer').empty().load(baseUrl + '/youtube/video #p_youtube_videoform', function() {
		//Elastic.reset();
		Elastic.refresh();
		$('#p_youtube_framecontainer input[type="reset"]').click(function() {
			$('#p_youtube_framecontainer').empty();
		});
	});
}


$(document).ready(function() {
	
	debug.log('document.ready: /public/js/youtube/index.js');
	
	$('.p_youtube_category').click(function (event) {
		event.preventDefault();
		event.stopPropagation();
		$(location).attr('href', $(this).find('a').eq(0).attr('href'));
	});
	
});