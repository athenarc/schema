$(document).ready(function() {
	$("#add-example-sumbit-button").click(function(){

		// if ($("#select-non-active-form-btn").hasClass('active-tab'))
		// {
			// var i, total, command;

			// command=$("#hidden_fieldScript").val();
			// totalFields=Number($("#hidden_fieldNum").val());
			
			// for (i=1; i<=totalFields; i++)
			// {
			// 	var fieldID="#field-" + i;
			// 	var fieldValue=$(fieldID).val();
				
			// 	command+= ' ' + fieldValue
			// }
			// console.log(command);
			// $("#command-text-box").val(command);

		// }
		
		$(".example_form").submit();

	
	});

});