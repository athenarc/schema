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


$(document).ready(function() {
	
	

	$(".experiment").click(function() { 

		var datatarget=$(this).attr('data-target');
		var modal=$(datatarget);
		modal.modal();
		var jobid=datatarget.split('-').pop();
		var submitbutton=$("#submit-" + jobid);
		submitbutton.removeClass("disabled");
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

	$(".experiment-submit-btn").click(function(){
		form=$(this).closest('form');
		modal=$(this).closest('.modal');
		form.submit();
		// $(this).addClass('disabled');
		
	});

	$(".edit-rocrate").click(function(){
		modal=$(this).closest('.modal');
		form=$(this).closest('form');
		var id=form.attr('id');
		var inputs = document.getElementById(form.attr('id')).getElementsByTagName("input");
		$(inputs).each(function() {
		$(this).removeAttr('disabled');
   		});
   		submitbutton=$(this).parent().parent().children().removeClass('hidden');
   		//window.alert(submitbutton.attr('id'));
   		editbuttons=$(this).parent().addClass('hidden');
   		
   	});







	
	

});