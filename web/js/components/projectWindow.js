$(".project-dropdown").change(function()
	{
		var project=$(this).val();
		var jobs='#' + project;
		$(".jobs-div").addClass('invisible');
		$(jobs).removeClass('invisible');
		$(".software-button-container").each(function()
		{
			var version=$(this).parent().children(".software-versions").children('.versionsDropDown').children('option:selected').text();
			var name=$(this).parent().children('.software-name-column').children('.software-name').html();
			var selector ='hidden-run-link-'+ name + '-' + version;
			selector=$.escapeSelector(selector);
			var runInput=$('#' + selector).val();
			// window.alert(runInput);
			
			var runLink=runInput + '&version=' + version + '&project=' + project;
			$(this).children('.run-button').attr('href', runLink);

		});
		

	});
	
	$(".arrow-right").click(function()
	{
		$(".project-egci").hide();
		$(".minimized").show();
		$(".minimized").removeClass('hidden');
		

	});
	
	$(".arrow-left").click(function()
	{
		$(".minimized").addClass('hidden');
		$(".project-egci").show();
		
	});