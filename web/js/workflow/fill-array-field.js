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
	

	$("#add-field-button").click(function(){
		input_html=$('.hidden_new_input_html').html()
		// window.alert(input_html);
		$(input_html).insertBefore(".fields-end");
	})


	$("#select-confirm-button").click(function(){

		var retVal='';
		var unique={};
		$('.value_input').each(function(){
			var value=$.trim($(this).val());
			if (!(value===""))
			{
				unique[value]=1;
			}
			
			// window.alert(value);
			
		});
		console.log(unique);
		for (var key in unique)
		{
			retVal+= key + ';';
		}
		retVal=retVal.slice(0,-1);
		// if (typeof retVal === 'undefined')
		// {
		// 	retVal='';
		// }
		// window.alert(retVal);
		var caller=$("#hidden_caller").val();
		var selector="#" + caller;
		// var caller=window.opener.$(selector).val();
		window.opener.$(selector).val(retVal);
		window.opener.$(selector).trigger('change');
		self.close();

	
	});


	$("#select-close-button").click(function(){

		self.close();

	
	});
	

});