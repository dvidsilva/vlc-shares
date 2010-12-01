/*
window.addEventListener("load", function() { setTimeout(loaded, 100) }, false);  
function loaded() {  
	if((navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i)) || (navigator.userAgent.match(/iAndroid/i))) {
		document.getElementById("wrapper").style.visibility = "visible";  
		window.scrollTo(0, 1); // pan to the bottom, hides the location bar  
	}
}
*/

$().ready(function(){
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
	
	$(".item.option > a").click(function (event) {
		event.preventDefault();
		$parent = $(this).parent();
		//event.stopPropagation();
		if ( $parent.find('ul.items li').size() > 0 ) return; // already unfolded
		var $ul = $(".item.option").not($parent).find('ul');
		var href = $parent.find('a').attr('href');
		$thisul = $parent.find('ul.items');
		$ul.slideUp('slow', function () {
			$ul.find('li').remove();
			$thisul.load(href + ' ul.items>*', function () {
				$thisul.slideDown('slow');
			});
		});
		
		$thisul.find('a').click(function (event) { event.stopPropagation(); });
		
	});
	
});	
