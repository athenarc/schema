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


$(document).ready(function(){
	
	$("#mark_all_seen").click(function(){
		$.ajax({
            url: "index.php?r=site/mark-all-notifications-seen",
            type: "GET",
            data: {},
            dataType: "html",
            success: function (data) 
            {
               $('.notification-menu-header').html("<i class='fas fa-bell'></i>&nbsp;&nbsp;0</span>")
               $('.notification-menu-header').removeClass('color-notification-bell');
               $('.notification-menu-header').addClass('grey-notification-bell');
               $('.notification').remove();
               $('#mark_all_seen').hide();
               $('.dropdown-header').html('You have 0 notifications');


            }

        });
	});
	
	$(".project-dropdown").change(function()
	{
		var project=$(this).val();
		var jobs='#' + project;
		$(".jobs-div").addClass('invisible');
		$(jobs).removeClass('invisible');
     		var job_number=$(jobs).html();
	   $.ajax({
            			url: "index.php?r=site/change-project-session&project=" + project + "&jobs=" + job_number,
            			type: "GET",		
	   });
		
		//$(".software-button-container").each(function()
		//{
		//	var version=$(this).parent().children(".software-versions").children('.versionsDropDown').children('option:selected').text();
		//	var name=$(this).parent().children('.software-name-column').children('.software-name').html();
		//	var selector ='hidden-run-link-'+ name + '-' + version;
		//	selector=$.escapeSelector(selector);
		//	var runInput=$('#' + selector).val();
		//	//window.alert(runInput);
			
		//	var runLink=runInput + '&version=' + version + '&project=' + project;
		//	$(this).children('.run-button').attr('href', runLink);

		//});

   });


})