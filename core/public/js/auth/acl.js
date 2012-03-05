
$(document).ready(function() {
	
	function commitChange(key, className) {
		var apiUrl = baseUrl + '/acl/change/key/'+key+'/class/'+className+'/csrf/'+csrf;
		$.getJSON(apiUrl, function (data, status, xhr) {
			if ( data.success ) {
				csrf = data.csrf;
				$('.wait-change').remove();
			} else {
				alert(data.message);
				location.reload();
			}
		});
		
	}
	
	var $filter = $('#permission-table-filter-generator');
	var $filterGrp = $('#permission-table-filter-pluginsgroup');
	if ( $filter.length > 0 ) {
		$('.filterable-generator').each(function (i, item) {
			var $item = $(item);
			var generator = $item.attr('data-generator');
			if ( generator != '' && generator != 'auth' ) {
				if ( $filter.find('option[value="'+generator+'"]').length == 0 ) {
					var $option = $('<option />');
					$option.attr('value', generator);
					$option.text(generator);
					$filterGrp.append($option);
				}
			}
		});
	}
	
	$filter.change(function(e) {
		var val = $(this).find('option:selected').val();
		if ( val == '' ) {
			// show all
			$('.filterable-generator').show();
		} else {
			$('.filterable-generator[data-generator="'+val+'"]').show();
			$('.filterable-generator[data-generator!="'+val+'"]').hide();			
		}
	});
	
	$('.permission-select').change(function(e) {
		var val = $(this).find('option:selected').val();
		var key = $(this).attr('id');
		$(this).before('<img class="wait-change" src="'+baseUrl+'/images/ajax-loader.gif" />');		
		commitChange(key, val);
	});
	
});