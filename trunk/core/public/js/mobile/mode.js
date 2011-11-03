/*
window.addEventListener("load", function() { setTimeout(loaded, 100) }, false);  
function loaded() {  
	if((navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i)) || (navigator.userAgent.match(/iAndroid/i))) {
		document.getElementById("wrapper").style.visibility = "visible";  
		window.scrollTo(0, 1); // pan to the bottom, hides the location bar  
	}
}
*/

$(document).ready(function(){
	// show/hide search form label
	if ($("#search").val()=="") {
		$("#searchLabel").css("visibility","visible");
	}
	$("#search").focus( function() {
		$("#searchLabel").css("visibility","hidden");
	});
	$("#search").blur( function() {
		if ($("#search").val()=="") {
			$("#searchLabel").css("visibility","visible");
		}
	});
	
	$(".item.option ul").hide();
	
	$(".item.option a").each(function (i, item) {
		var $item = $(item);
		$item.attr('id', $item.attr('href'));
		$item.attr('href', '#');
		$item.click(function(event) {
			event.preventDefault();
		});
	});
	
	$(".item.option").click(function (event) {
		event.stopPropagation();
		event.preventDefault();
		
		$parent = $(this);
		//event.stopPropagation();
		if ( $parent.find('ul.items li').size() > 0 ) return; // already unfolded
		
		var $ul = $(".item.option").not($parent).find('ul');
		
		var href = $parent.find('a').attr('id');
		
		var $thisul = $parent.find('ul.items');
		$ul.slideUp('slow', function () {
			$ul.find('li').remove();
		});

		$thisul.load(href + ' ul.items>*', function () {
			$thisul.find('a').click(function (event) { event.stopPropagation(); });
			$thisul.slideDown('slow');
		});
		
		return false;
	});
	
});	
