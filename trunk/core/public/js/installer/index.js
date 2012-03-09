/**
 * 
 */

$(document).ready(function() {
	
	$('form#installer #lang').change(function (event) {
		
		var value = $(this).find('option:selected').text().trim();
		
		var url = baseUrl + '/installer/index/lang/'+value;
		
		$(location).attr('href', url);
		
	});
	
	// hide hidden fields
	$('.hidden').parents('.row').hide();
	
	// disable the install button until checks completed
	$('form#installer #submit').attr('disabled', 'disabled');

	$('#content').prepend('<div id="waitpls"><img src="' + baseUrl + '/images/ajax-loader.gif"/> ...wait please...</div>');
	
	var check1 = false;
	var check2 = false;
	var hide1 = false;
	var hide2 = false;
	$(document).bind('_check_finished', function (e) {
		if ( check1 && check2 ) {
			$('form#installer #submit').attr('disabled', null);
		}
		if ( hide1 && hide2 ) {
			$('#waitpls').remove();
		}
	});
	
	function loadImg() {
		$('.installer-plugin-thumb[data-src!=""]').each(function (i, item) {
			var $item = $(item);
			var $img = $('<img />');
			$img.load(function (e) {
				$item.attr('src', $item.attr('data-src'));
			}).attr('src', $item.attr('data-src'));
		});
	}
	
	function checks() {
		$.post(threads_check_url, {'pings' : threads_check_params}, function (data, status, jqxhr) {
			if ( data.success ) {
				$('#threads').val(data.valid);
			} else {
				$($('#threads').parents('.row')).append('<div class="column"><div class="container full-height notes"><ul class="errors"><li>'+threads_check_error+'</li></ul></div></div>');
				$($('#threads').parents('.row')).show();
				Elastic.refresh();
			}
			
			check1 = true;
			hide1 = true;
			$(document).trigger('_check_finished');
		});
		$.get(rewrite_check_url, function (data, status, jqxhr) {
			if ( data.success ) {
				check2 = true;
			} else {
				if ( confirm('Looks like you got a problem with mod_rewrite or .htaccess file. Check project site for help, solve the problem and then refresh the page to continue. Do you want to be redirected to the help page?') ) {
					$(location).attr('href', 'https://code.google.com/p/vlc-shares/wiki/FaqTroubleshottingEn#Mod_Rewrite_Problems');
				}
			}
			hide2 = true;
			$(document).trigger('_check_finished');
		});
		
	}
	
	// set image loading after 2 seconds
	setTimeout(loadImg, 2000);
	
	setTimeout(checks, 4000);
	
	
});