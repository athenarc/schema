$(document).ready(function() {
	

	$(".selectable").click(function(){

		$('.active').children('i').removeClass('fa-folder-open');
		$('.active').children('i').addClass('fa-folder');
		$('.active').removeClass('active');
		$(this).addClass('active');
		$(this).children('i').addClass('fa-folder-open');
		$(this).children('i').removeClass('fa-folder');

	
	});

	$("#select-confirm-button").click(function(){

		var retVal;
		retVal=$('.active').children('input[name=hiddenPath]').val();
		if (typeof retVal === 'undefined')
		{
			retVal='';
		}
		
		var caller=window.opener.$("#mountcaller").val();
		window.opener.$(caller).val(retVal);
		window.opener.$(caller).trigger('change');
		self.close();

	
	});

	$(".selectable").dblclick(function(){

		var retVal;
		retVal=$('.active').children('input[name=hiddenPath]').val();
		if (typeof retVal === 'undefined')
		{
			retVal='';
		}
		
		var caller=window.opener.$("#mountcaller").val();
		window.opener.$(caller).val(retVal);
		window.opener.$(caller).trigger('change');
		self.close();

	
	});


	$("#select-close-button").click(function(){

		self.close();

	
	});
	

});