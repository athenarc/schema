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
	

	$(".selectable").click(function(){

		// $('.active').children('i').removeClass('fa-folder-open');
		// $('.active').children('i').addClass('fa-folder');
		$('.active').removeClass('active');
		$(this).addClass('active');
		// $(this).children('i').addClass('fa-folder-open');
		// $(this).children('i').removeClass('fa-folder');

	
	});

	$(".selectable").dblclick(function(){

		var retVal;
		retVal=$(this).children('input[name=hiddenPath]').val();
		if (typeof retVal === 'undefined')
		{
			retVal='';
		}
		
		var caller=$("#hidden_caller").val();
		var selector="#" + caller; 
		// window.alert(selector);
		// var caller=window.opener.$(selector).val();
		window.opener.$(selector).val(retVal);
		window.opener.$(selector).trigger('change');
		self.close();

	
	});

	$("#select-confirm-button").click(function(){

		var retVal;
		retVal=$('.active').children('input[name=hiddenPath]').val();
		if (typeof retVal === 'undefined')
		{
			retVal='';
		}
		
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