$(document).ready(function() {
	$("#software-start-run-button").click(function(){

		var disabled = $(this).attr('disabled');
		if (!disabled)
		{
			totalFields=Number($("#hidden_fieldsNum").val());
			for (i=1; i<=totalFields; i++)
	        {
	        	var fieldID="#field-" + i;
	            var field=$(fieldID);
	            field.addClass('disabled-box');
	            field.prop('readonly',true);
	        }
	        $("#systemmount").addClass('disabled-box');
	        $("#memory").addClass('disabled-box');
	        $("#cores").addClass('disabled-box');
	        $("#processes").addClass('disabled-box');
	        $("#pernode").addClass('disabled-box');
	        $("#pernode").prop('readonly',true);
	        $("#systemmount").prop('readonly',true);
	        $("#memory").prop('readonly',true);
	        $("#processes").prop('readonly',true);
	        $("#cores").prop('readonly',true);
			$("#command-text-box").prop('readonly',true)
			$("#command-text-box").addClass('disabled-box');
			$(".add-example-link").hide();
			$("#software_commands_form").submit();
		}

	
	});

	//if the example button is pressed make the command box active
	// $(".software-example-button").click(function(){
	// 	$("#active-run-form").show();
	// 	$("#select-active-form-btn").addClass('active-tab');
	// 	$("#non-active-run-form").hide();
	// 	$("#select-non-active-form-btn").removeClass('active-tab');
		
	// 	var commands=$("#hidden_example_input").val();
	// 	$("#command-text-box").val(commands);
	// 	$("#command-text-box").prop('readonly',true)
	// 	$("#command-text-box").addClass('disabled-box');
	// 	$("#software_commands_form").submit();
	
	// });

	//on submit show command box
	// if ($("#command-text-box").prop('readonly')==true)
	// {
	// 	$("#command-text-box").addClass('disabled-box');
	// 	$("#active-run-form").show();
	// 	$("#non-active-run-form").hide();
	// 	// $("#select-active-form-btn").addClass('disabled-btn');
	// 	// $("#select-non-active-form-btn").addClass('disabled-btn');
	// }

	// $("#select-active-form-btn").click(function(){
	// 	if ($("#select-active-form-btn").hasClass('disabled-btn')==false)
	// 	{
	// 		$("#active-run-form").show();
	// 		$("#select-active-form-btn").addClass('active-tab');
	// 		$("#non-active-run-form").hide();
	// 		$("#select-non-active-form-btn").removeClass('active-tab');
			
	// 	}

	// });

	// $("#select-non-active-form-btn").click(function(){
	// 	if ($("#select-non-active-form-btn").hasClass('disabled-btn')==false)
	// 	{
	// 		// var command=$("#command-text-box").val();
	// 		// var tokens=command.split(" ");
	// 		// var total=tokens.length;
	// 		// var i;

	// 		// for (i=0; i<=total; i++)
	// 		// {
	// 		// 	$("#field-" + i).val(tokens[i]);
	// 		// }
	// 		$("#active-run-form").hide();
	// 		$("#select-active-form-btn").removeClass('active-tab');
	// 		$("#non-active-run-form").show();
	// 		$("#select-non-active-form-btn").addClass('active-tab');
	// 	}

	// });

	$(".select-mount-button").click(function(){
		var disabled = $(this).attr('disabled');
		if (!disabled)
		{
			var caller=$(this).parent().children('.mount-field').attr('id');
			$("#mountcaller").val("#" + caller);
			// window.alert(caller);
			var link = $("#selectmounturl").val();
			window.open(link, "Ratting",
				"height=500,width=800,left=100,top=100,resizable=yes,scrollbars=yes,toolbar=no,menubar=no,location=no,directories=no, status=no");
		}
	});

	$(".clear-mount-button").click(function(){
		var mountf=$(this).parent().children('.mount-field');
		mountf.val('');
		mountf.trigger("change");
	});

	$("#isystemmount").change(function(){
		if ($(".mount-exist-error").length)
		{
			$(".mount-exist-error").hide();
			$("#software-start-run-button").removeClass('hidden-element');
		}

	});

	$("#osystemmount").change(function(){
		if ($(".mount-exist-error").length)
		{
			$(".mount-exist-error").hide();
			$("#software-start-run-button").removeClass('hidden-element');
		}

	});

	$("#iosystemmount").change(function(){
		if ($(".mount-exist-error").length)
		{
			$(".mount-exist-error").hide();
			$("#software-start-run-button").removeClass('hidden-element');
		}

	});

	$(".btn-default-values").click(function(){
		var disabled = $("#software-start-run-button").attr('disabled');
		if (!disabled)
		{
			var grandparent=$(this).parent().parent();
			var hidden_val=grandparent.children("input[name^='default_field_values']").val();
			grandparent.find("input[name^='field_values']").val(hidden_val);
		}
	});

	$("#software-run-example-button").click(function(){
		var disabled = $(this).attr('disabled');
		if (!disabled)
		{

			$("#hidden_example_input").val("1");
			$(".add-example-link").hide();
			$("#software_commands_form").submit();
		}
		
	});

	function triggerFromChild()
	{
		$("#systemmount").trigger('change');
	}

});