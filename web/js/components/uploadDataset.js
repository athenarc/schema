$(document).ready(function()
{

	var provider=$(".provider-dropdown").val();
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

	$(".select-mount-button-helix").click(function(){
		
			var caller=$(this).parent().children('.mount-field-helix').attr('id');
			$("#mountcaller").val("#" + caller);
			var link = $("#selectmounturl").val();
			window.open(link, "Ratting",
				"height=500,width=800,left=100,top=100,resizable=yes,scrollbars=yes,toolbar=no,menubar=no,location=no,directories=no, status=no");
		
	});

	$(".clear-mount-button-helix").click(function(){
		var mountf=$(this).parent().children('.mount-field-helix');
		mountf.val('');
		mountf.trigger("change");
	});

	$(".select-mount-button-zenodo").click(function(){
		
			var caller=$(this).parent().children('.mount-field-zenodo').attr('id');
			$("#mountcaller").val("#" + caller);
			var link = $("#selectmounturl-zenodo").val();
			// window.alert(link);
			window.open(link, "Ratting",
				"height=500,width=800,left=100,top=100,resizable=yes,scrollbars=yes,toolbar=no,menubar=no,location=no,directories=no, status=no");
		
	});

	$(".clear-mount-button-zenodo").click(function(){
		var mountf=$(this).parent().children('.mount-field-zenodo');
		mountf.val('');
		mountf.trigger("change");
	});

	$("#download-button").click(function(){
		$(".modal-loading").removeClass('hidden');
	});

	 

	$("#helix-form").submit(function(event){
		


		var datasetfolder=$("#helix-mount").val();
		var submit=0;

		if($(".hidden_element_box").is(':empty'))
		{
			
			event.preventDefault();
			$("#message-subject").removeClass('hidden');
			$(".blue-rounded-textbox").addClass('red-border');
		}

		if(datasetfolder=='')
		{
			
			event.preventDefault();
			$("#message-folder-helix").removeClass('hidden');
			$(".mount-field-helix").css("border-color", "#a94442");
		}

		if(!$(".hidden_element_box").is(':empty') && !(datasetfolder==''))
		{
			
			$('.modal').modal({
    			backdrop: 'static',
   				keyboard: false
			})
			$('.modal').modal();


			setTimeout(function(){
        		if ($('#helix-form').find('.has-error').length!=0)
        		{
        			$('.modal').modal('hide');
        		}
        	}, 2000);
		}

		
	});


	
	$("#user_search_box").focusout(function(){
		
		if($(".hidden_element_box").is(':empty'))
		{
			
			$("#message-subject").removeClass('hidden');
			$(".blue-rounded-textbox").addClass('red-border');
		}
		else
		{

			$("#message-subject").addClass('hidden');
			$(".blue-rounded-textbox").removeClass('red-border');
		}
	
	});

	$(".upload-type").change(function(){
		var value=$(this).val();
		if(value=='publication')
		{
			$("#publication-type").removeClass('hidden');
			$("#image-type").addClass('hidden');
		}
		else if(value=='image')
		{
			$("#image-type").removeClass('hidden');
			$("#publication-type").addClass('hidden');
		}
		else
		{
			$("#image-type").addClass('hidden');
			$("#publication-type").addClass('hidden');
		}
	});

	$(".access-rights").change(function(){
		var value=$(this).val();
		if(value=='open')
		{
			$("#embargo-date").addClass('hidden');
			$("#zenodo-license").removeClass('hidden');
			$("#access-conditions").addClass('hidden');
		}
		else if(value=='embargoed')
		{
			$("#embargo-date").removeClass('hidden');
			$("#zenodo-license").removeClass('hidden');
			$("#access-conditions").addClass('hidden');
		}
		else if(value=='restricted')
		{
			$("#access-conditions").removeClass('hidden');
			$("#embargo-date").addClass('hidden');
			$("#zenodo-license").addClass('hidden');
		}
		else
		{
			$("#access-conditions").addClass('hidden');
			$("#embargo-date").addClass('hidden');
			$("#zenodo-license").addClass('hidden');
		}
	});

	$("#zenodo-form").submit(function(event){
		
		var datasetfolder=$("#zenodo-mount").val();
		var submit=0;


		if(datasetfolder=='')
		{
			event.preventDefault();
			$("#message-folder-zenodo").removeClass('hidden');
			$(".mount-field-zenodo").css("border-color", "#a94442");
			
		}
		

		if(!(datasetfolder==''))
		{
			
			$('.modal').modal({
    			backdrop: 'static',
   				keyboard: false
			})
			$('.modal').modal();


			setTimeout(function(){
        		if ($('#zenodo-form').find('.has-error').length!=0)
        		{
        			$('.modal').modal('hide');
        		}
        	}, 2000);
		}

	});



	$('#zenodo-form .add-items').click(function(){

        var n = $('.creator').length + 1;
        var divid="creator-" + n;
        var trashid="trash-"+n;
        // window.alert(n);
        var box_html='<div class="col-md-offset-4 col-md-8 creator-div" id='+ divid + '>' + $('.creator-div').html() + '<div class="col-md-1" style="padding-top:15px;"><i class="far fa-trash-alt fa-lg remove-items" id='+ trashid + '></i></div></div>';
       $('#first-creator').after(box_html);
       	return false;
    });

    $('#zenodo-form ').on('click', '.remove-items', function(){
    var trashid=$(this).attr('id');
    var id = trashid.substr(trashid.indexOf("-") + 1);
    var divid="creator-" + id;
    var div=document.getElementById(divid);
    div.remove();
    
    return false;

	});

	
})


	