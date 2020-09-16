$(document).ready(function () {

	$('.delete-doi-btn').click(function(){
				var container=$(this).parent();
				container.remove();
	});
	
	$("#doi-add-btn").click(function() 
	{
		var value=$("#doi-input").val();

		if (value!='')
		{
			var previousHtml=$(".doi-list").html();
			var doi="<div class='doi-entry-container'><i class='fas fa-times delete-doi-btn'></i>  " 
						+ value + "<input type='hidden' name='dois[]' value='" + value + "'></div>";
			var newHtml= doi + previousHtml;

			$(".doi-list").html(newHtml);

			$('.delete-doi-btn').click(function(){
				var container=$(this).parent();
				container.remove();
			});
			$("#doi-input").val('');
		}



	});



})