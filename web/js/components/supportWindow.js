
	
	$(".support-arrow-right").click(function()
	{
		$(".support-window-wrapper").hide();
		$(".support-minimized").show();
		$(".support-minimized").removeClass('hidden');
		

	});
	
	$(".support-arrow-left").click(function()
	{
		$(".support-minimized").addClass('hidden');
		$(".support-window-wrapper").show();
		
	});