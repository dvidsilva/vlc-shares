window.addEventListener("load", function() { setTimeout(loaded, 100); }, false);

function loaded() {  
	document.getElementById("wrapper").style.visibility = "visible";
	if((navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i)) || (navigator.userAgent.match(/iAndroid/i))) {
		window.scrollTo(0,1); // pan to the bottom, hides the location bar  
	}
}

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
});	
