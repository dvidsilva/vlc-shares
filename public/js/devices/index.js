
(function ($) {
    $.extend({
    	/*
        postJSON: function (url, jsonData, success, options) {
            var config = {
                url: url,
                type: "POST",
                data: json ? JSON.stringify(json) : null,
                dataType: "json",
                contentType: "application/json; charset=utf-8",
                success: callback
            };
            $.ajax($.extend(options, config));
        }
        */
    	postJSON : function(url, data, callback) {
    		$.post(url, data, callback, "json");
    	}
    });
})(jQuery);

$(document).ready(function() {
	
	debug.log('document.ready: /public/js/devices/index.js');

	$('#test').click(function (event) {
		// reset old highlighted row
		
		$('.highlight').removeClass('highlight');
		
		var device = $('#test_device option:selected').val();

		$.postJSON(baseUrl+'/devices/test/', {'user-agent' : device}, function (data, textStatus) {
			if ( data.success ) {
				$('#deviceId-'+data.deviceId).addClass('highlight');
			} else {
				alert('Device profile not found. Default preferences will be used for this device');
			}
		});
		
	});
	
	$('#test-reset').click(function (event) {
		$('.highlight').removeClass('highlight');
	});
	
	
});