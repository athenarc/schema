
$(document).ready(function() {
		
		$(".switch").change(function() {
			var id=$(this).attr("id");
			var provider_input_id=$(".provider-input"+id);
			if ($("#enabled-" + id).val()==1)
			{
				$(".hid-" + id).hide();
				$("#enabled-" + id).val(0);
				$("#enabled-text-" + id).html("Enabled: false");
			}
			else
			{
				$(".hid-"+id).removeClass('provider-div-hidden');
				$(".hid-"+id).show();
				$("#enabled-" + id).val(1);

				$("#enabled-text-" + id).html("Enabled: true");
				
			}
			
		 });
	
	

});