$(document).ready(function() {
	

	$("#add-field-button").click(function(){
		input_html=$('.hidden_new_input_html').html()
		// window.alert(input_html);
		$(input_html).insertBefore(".fields-end");
	})


	$("#select-confirm-button").click(function(){

		var retVal='';
		var unique={};
		$('.value_input').each(function(){
			var value=$.trim($(this).val());
			if (!(value===""))
			{
				unique[value]=1;
			}
			
			// window.alert(value);
			
		});
		console.log(unique);
		for (var key in unique)
		{
			retVal+= key + ';';
		}
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