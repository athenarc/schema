$(document).ready(function() {
	

	$(".selectable").click(function(){

		// $('.active').children('i').removeClass('fa-folder-open');
		// $('.active').children('i').addClass('fa-folder');
		if ($(this).hasClass('active'))
		{
			$(this).removeClass('active');
		}
		else
		{
			$(this).addClass('active');
		}
		
		// $(this).children('i').addClass('fa-folder-open');
		// $(this).children('i').removeClass('fa-folder');

	
	});

	$("#select-confirm-button").click(function(){

		var retVal='';
		$('.active').each(function(){
			// window.alert($(this).children('input[name=hiddenPath]').val())
			retVal+=$(this).children('input[name=hiddenPath]').val() + ';';
			
			
		});
		retVal=retVal.slice(0,-1);
		// if (typeof retVal === 'undefined')
		// {
		// 	retVal='';
		// }
		// window.alert(retVal);
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