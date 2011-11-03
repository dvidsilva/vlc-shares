
function youtube_newvideo() {
	$('#p_youtube_framecontainer').empty().load(baseUrl + '/youtube/video #p_youtube_videoform', function() {
		idCategory = $('#p_youtube_categoryform form #id').val();
		$('#p_youtube_framecontainer form #idCategory').parents('.row').hide();
		$('#p_youtube_framecontainer form #idCategory option[value="' + idCategory + '"]').attr('selected', 'selected');
		
		$('#p_youtube_framecontainer input[type="reset"]').click(function() {
			$('#p_youtube_framecontainer').empty();
		});
		Elastic.reset();
		Elastic.refresh();
	});
}


$(document).ready(function() {
	
	debug.log('document.ready: /public/js/youtube/category.js');
	
	$('#thumbselect').change(function (event) {
		var $this = $(this);
		var value = $(this).val();
		$('#p_youtube_thumbnailpreview').fadeOut('slow', function () {
			$(this).find('img').remove();
			if ( value != 'upload' ) {
				$(this).append('<img src="' + baseUrl + '/images/youtube/uploads/' + value  + '" />').fadeIn('slow');
			}
		});
		if ( value == 'upload' ) {
			$('#thumbnail').parents('.row').fadeIn('slow');
		} else {
			$('#thumbnail').parents('.row').fadeOut('slow');
		}
	});
	
	$('#thumbselect').change();
	
	$('.p_youtube_video').hover(function (event) {
		var $this = $(this);
		debug.log($this.find('.image'));
		$this.find('.image').clone().css('z-index', 10).appendTo('#p_youtube_thumbnailpreview_video');
		$('#p_youtube_thumbnailpreview_video').css('top', $this.offset().top - 10).css('left', $this.offset().left + 700).show();
	}, function (event) {
		$('#p_youtube_thumbnailpreview_video').hide().find('.image').remove();
	});
	
	$('.quickbox-delete').click(function (event){
		if ( confirm(p_youtube_delete_category_confirm) ) {
			idCategory = $('#p_youtube_categoryform form.zend_form #id').val();
			$(location).attr('href', baseUrl+'/youtube/dcategory/idCategory/'+idCategory);
		}
	});

	$('.quickbox-back').click(function (event){
		$(location).attr('href', baseUrl+'/youtube/index');
	});
	
	$('.quickbox-add-video').click(function (event) {
		$('#p_youtube_framecontainer').empty().load(baseUrl + '/youtube/video #p_youtube_videoform', function (){
			idCategory = $('#p_youtube_categoryform form #id').val();
			$('#p_youtube_framecontainer form #idCategory').parent().parent().hide();
			$('#p_youtube_framecontainer form #idCategory option[value="' + idCategory + '"]').attr('selected', 'selected');
		});
	});
	
});