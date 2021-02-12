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


$(document).ready(function () {

	$('.fas.fa-times').click(function(){
				var container=$(this).parent();
				// window.alert(container.html());
				container.remove();
	});
	
	$("#doi-add-btn").click(function() 
	{
		var value=$("#doi-input").val();

		if (value!='')
		{
			var previousHtml=$(".doi-list").html();
			var doi="<div class='doi-entry-container'><i class='fas fa-times'></i>  " 
						+ value + "<input type='hidden' name='dois[]' value='" + value + "'></div>";
			var newHtml= doi + previousHtml;

			$(".doi-list").html(newHtml);

			$('.fas.fa-times').click(function(){
				var container=$(this).parent();
				container.remove();
			});
			$("#doi-input").val('');
		}



	});

	$("#iomount").click(function()
	{
		if ($(this).prop("checked"))
		{
			$(".mount-fields").show();
		}
		else
		{
			$(".mount-fields").hide();

		}
	});


})