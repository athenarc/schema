$(document).ready(function () {

	$('.fas.fa-times').click(function(){
				var container=$(this).parent();
				// window.alert(container.html());
				container.remove();
	});
	
	$("#doi-add-btn").click(function() 
	{
		var value=$("#doi-input").val();

		if (value!='')
		{
			var previousHtml=$(".doi-list").html();
			var doi="<div class='doi-entry-container'><i class='fas fa-times'></i>  " 
						+ value + "<input type='hidden' name='dois[]' value='" + value + "'></div>";
			var newHtml= doi + previousHtml;

			$(".doi-list").html(newHtml);

			$('.fas.fa-times').click(function(){
				var container=$(this).parent();
				container.remove();
			});
			$("#doi-input").val('');
		}



	});

	$("#iomount").click(function()
	{
		if ($(this).prop("checked"))
		{
			$(".mount-fields").show();
		}
		else
		{
			$(".mount-fields").hide();

		}
	});


})