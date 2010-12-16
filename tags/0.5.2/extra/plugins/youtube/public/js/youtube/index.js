
$(document).ready(function() {
	
	debug.log('document.ready: /public/js/youtube/index.js');
	
	$('.quickbox-add-category').click(function (event) {
		$('#p_youtube_framecontainer').empty().load(baseUrl + '/youtube/category #p_youtube_categoryform', function () {
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
					$('#p_youtube_framecontainer #thumbnail').parent().parent().fadeIn('slow');
				} else {
					$('#p_youtube_framecontainer #thumbnail').parent().parent().fadeOut('slow');
				}
			});
		});
	});

	$('.quickbox-add-account').click(function (event) {
		$('#p_youtube_framecontainer').empty().load(baseUrl + '/youtube/account #p_youtube_accountform');
	});
	
	$('.quickbox-add-video').click(function (event) {
		$('#p_youtube_framecontainer').empty().load(baseUrl + '/youtube/video #p_youtube_videoform');
	});

	$('.quickbox-options').click(function (event) {
		$(location).attr('href', baseUrl+'/config/index/key/youtube/r/'+redirectUrl);
	});

	
	$('.p_youtube_category').click(function (event) {
		event.preventDefault();
		event.stopPropagation();
		$(location).attr('href', $(this).find('a').eq(0).attr('href'));
	});
	
});