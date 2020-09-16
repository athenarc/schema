$(document).ready(function() {
	

	$(".selectable").click(function(){

		// $('.active').children('i').removeClass('fa-folder-open');
		// $('.active').children('i').addClass('fa-folder');
		$('.active').removeClass('active');
		$(this).addClass('active');
		// $(this).children('i').addClass('fa-folder-open');
		// $(this).children('i').removeClass('fa-folder');

	
	});

	$(".selectable").dblclick(function(){

		var retVal;
		retVal=$(this).children('input[name=hiddenPath]').val();
		if (typeof retVal === 'undefined')
		{
			retVal='';
		}
		
		var caller=$("#hidden_caller").val();
		var selector="#" + caller; 
		// window.alert(selector);
		// var caller=window.opener.$(selector).val();
		window.opener.$(selector).val(retVal);
		window.opener.$(selector).trigger('change');
		self.close();

	
	});

	$("#select-confirm-button").click(function(){

		var retVal;
		retVal=$('.active').children('input[name=hiddenPath]').val();
		if (typeof retVal === 'undefined')
		{
			retVal='';
		}
		
		var caller=$("#hidden_caller").val();
		var selector="#" + caller;
		// var caller=window.opener.$(selector).val();
		window.opener.$(selector).val(retVal);
		window.opener.$(selector).trigger('change');
		self.close();

	
	});


	$("#select-close-button").click(function(){

		self.close();

	
	});
	

});