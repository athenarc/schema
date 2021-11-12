$(document).ready(function() {
	
	
	
	//alert(original_link);
	$("#search-button").click(function() { 

		var search_parameter=$('#text-search').val();
		var original_link=window.location.toString();
		var edited_link=original_link.replace('search_parameter','');
		var edited_link = edited_link.substring(0, edited_link.lastIndexOf('='));
		//window.alert(output);
		var search_link=edited_link + 'search_parameter=' + search_parameter;
		location.replace(search_link);
	});

	$("#text-search").keyup(function(event) {
    if (event.keyCode === 13) {
        $("#search-button").click();
    }
	});

	


});