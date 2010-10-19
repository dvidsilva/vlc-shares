/*
window.addEventListener("load", function() { setTimeout(loaded, 100); }, false);  
function loaded() {  
	if((navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i)) || (navigator.userAgent.match(/iAndroid/i))) {
		document.getElementById("wrapper").style.visibility = "visible";  
		window.scrollTo(0, 1); // pan to the bottom, hides the location bar  
	}
}
*/
$().ready(function(){
	$('.item.search').click(function (event) {
		event.stopPropagation();
		event.preventDefault();
		
		var desc = $(this).find('p.desc');
		if ( desc.size() > 0 ) {
			desc = desc.text().trim();
		} else {
			desc = defaultDesc;
		}
		
		var value = prompt(desc);
		if ( value !== null ) {
			var href = $(this).find('a').attr('href');
			$(location).attr('href', href + value);
		}
		
	});
});	
