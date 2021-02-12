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