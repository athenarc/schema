/************************************************************************************
 *
 *  Copyright (c) 2018 Thanasis Vergoulis & Konstantinos Zagganas &  Loukas Kavouras
 *  for the Information Management Systems Institute, "Athena" Research Center.
 *  
 *  This file is part of SCHeMa.
 *  
 *  SCHeMa is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *  
 *  SCHeMa is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with Foobar.  If not, see <https://www.gnu.org/licenses/>.
 *
 ************************************************************************************/

$(document).ready(function()
{
	
	var provider=$(".provider-dropdown").val();
	if (provider=='Helix')
	{
		$(".helix_field").removeClass("helix_hide");
		$(".zenodo_field").addClass("zenodo_hide");
		$(".url_field").addClass("url_hide");
	}
	else if (provider=='Zenodo')
	{
		$(".helix_field").addClass("helix_hide");
		$(".url_field").addClass("url_hide");
		$(".zenodo_field").removeClass("zenodo_hide");
	}
	else
	{
		$(".helix_field").addClass("helix_hide");
		$(".url_field").removeClass("url_hide");
		$(".zenodo_field").addClass("zenodo_hide");	
	}
	
	$(".provider-dropdown").change(function()
	{
		var provider=$(this).val();
		if (provider=='Helix')
		{
			$(".helix_field").removeClass("helix_hide");
			$(".zenodo_field").addClass("zenodo_hide");
			$(".url_field").addClass("url_hide");
		}
		else if (provider=='Zenodo')
		{
			$(".helix_field").addClass("helix_hide");
			$(".url_field").addClass("url_hide");
			$(".zenodo_field").removeClass("zenodo_hide");
		}
		else
		{
			$(".helix_field").addClass("helix_hide");
			$(".url_field").removeClass("url_hide");
			$(".zenodo_field").addClass("zenodo_hide");	
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
		


		if($(".hidden_element_box").is(':empty'))
		{
			
			event.preventDefault();
			$("#message-subject").removeClass('hidden');
			$(".blue-rounded-textbox").addClass('red-border');
		}


		if(!$(".hidden_element_box").is(':empty'))
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


	$(".select-mount-button-url").click(function(){
		
			var caller=$(this).parent().children('.mount-field-url').attr('id');
			$("#mountcaller").val("#" + caller);
			var link = $("#selectmounturl-url").val();
			//window.alert(caller);
			window.open(link, "Ratting",
				"height=500,width=800,left=100,top=100,resizable=yes,scrollbars=yes,toolbar=no,menubar=no,location=no,directories=no, status=no");
		
	});

	$(".clear-mount-button-url").click(function(){
		var mountf=$(this).parent().children('.mount-field-url');
		mountf.val('');
		mountf.trigger("change");
	});

	$("#url-form").submit(function(event){
		
		
			$('.modal').modal({
    			backdrop: 'static',
   				keyboard: false
			})
			$('.modal').modal();


			setTimeout(function(){
        		if ($('#url-form').find('.has-error').length!=0)
        		{
        			$('.modal').modal('hide');
        		}
        	}, 2000);
		

	});

	
})


	