$(document).ready(function()
{
	$(".provider-dropdown").change(function()
	{
		var provider=$(this).val();
		if (provider=='Helix')
		{
			$(".helix_field").removeClass("helix_hide");
			$(".zenodo_field").addClass("zenodo_hide");
		}
		else
		{
			$(".helix_field").addClass("helix_hide");
			$(".zenodo_field").removeClass("zenodo_hide");
		}
		
	});

	$(".select-mount-button").click(function(){
		
			 var caller=$(this).parent().children('.mount-field').attr('id');
			 $("#mountcaller").val("#" + caller);
			var link = $("#selectmounturl").val();
			window.open(link, "Ratting",
				"height=500,width=800,left=100,top=100,resizable=yes,scrollbars=yes,toolbar=no,menubar=no,location=no,directories=no, status=no");
		
	});

	$(".clear-mount-button").click(function(){
		var mountf=$(this).parent().children('.mount-field');
		mountf.val('');
		mountf.trigger("change");
	});

	$("#download-button").click(function(){
		$(".modal-loading").removeClass('hidden');
	});

	$(".select-mount-button-helix").click(function(){
		
			var caller=$(this).parent().children('.mount-field').attr('id');
			$("#mountcaller-helix").val("#" + caller);
			var link = $("#selectmounturl-helix").val();
			window.open(link, "Ratting",
				"height=500,width=800,left=100,top=100,resizable=yes,scrollbars=yes,toolbar=no,menubar=no,location=no,directories=no, status=no");
		
	});

	$(".clear-mount-button-helix").click(function(){
		var mountf=$(this).parent().children('.mount-field');
		mountf.val('');
		mountf.trigger("change");
	});

	$("#download-button").click(function(){
		$(".modal-loading").removeClass('hidden');
	});

})
	