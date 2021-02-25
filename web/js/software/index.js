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

	// if((empty($projectsDropdown))){
	// 		$(".run-button").prop('disabled', true);
	// }

	// $(".run-button").click(function (){

	// 	var grandparent=$(this).parent().parent();
	// 	var name=grandparent.children('.software-name-column').children('.software-name').html();
	// 	var version=grandparent.children('.software-versions').children('select').children('option:selected').html();
	// 	var origin=$('.hidden_url').val()
	// 	var project = $('.project-dropdown').children('option:selected').val();
	// 	// window.alert(project);

	// 	url=origin + "/index.php?r=software%2Frun&name=" + name + "&version=" + version + '&project=' + project;
	// 	// window.alert(project);
	// 	// $(this).attr("href",url)
	// 	// $(this).submit();
	// 	window.location.replace(url);
	// });
	// $(".edit-button").click(function (){

	// 	var grandparent=$(this).parent().parent();
	// 	var name=grandparent.children('.software-name-column').children('.software-name').html();
	// 	var version=grandparent.children('.software-versions').children('select').children('option:selected').html();
	// 	// var origin=$('.hidden_url').val()
		
	// 	url=origin + "/index.php?r=software%2Fedit-software&name=" + name + "&version=" + version;
	// 	// $(this).attr("href",url)
	// 	// $(this).submit();
	// 	window.location.replace(url);
	// });
	$(".delete-button").click(function (event){

		event.preventDefault();
		var grandparent=$(this).parent().parent();
		var name=grandparent.children('.software-name-column').children('.software-name').html();
		var version=grandparent.children('.software-versions').children('select').children('option:selected').html();
		var origin=$('.hidden_url').val();
		var modal=$(this).parent().children('#delete-modal');
		url=origin + "/index.php?r=software%2Fremove-software&name=" + name + "&version=" + version;
		modal.find('.modal-body').html('Are you sure you want to delete <b>' + name + ' v.' + version + '</b>?');
		modal.find('.confirm-delete').attr("href",url);
		modal.modal('show');
		
		// $(this).attr("href",url)
		// $(this).submit();
	});

	$(".confirm-delete").click(function (){
		$(".modal-loading").show()

	});

	$(".versionsDropDown").change(function (){
			var dropValue=$(this).val();
			var splitValue=dropValue.split('|');
			var visibility=splitValue[1];
			var version=splitValue[0];
			var project=$('.project-dropdown').val();

			var buttonContainer=$(this).parent().parent().children('.software-button-container');
			var name=buttonContainer.parent().children('.software-name-column').children('.software-name').html();
			var selector ='hidden-run-link-'+ name + '-' + version;
			selector=$.escapeSelector(selector);
			var runInput=$('#' + selector).val();
			var runLink=runInput + '&version=' + version + '&project=' + project;
			// alert(buttonContainer.children('.run-button').html());
			buttonContainer.children('.run-button').attr('href', runLink);
			
			var selector ='hidden-edit-link-'+ name + '-' + version;
			selector=$.escapeSelector(selector);
			var editLink=$('#' + selector).val();


			var selector ='hidden-analyze-link-'+ name + '-' + version;
			selector=$.escapeSelector(selector);
			var editLink=$('#' + selector).val();
			// var editInput=buttonContainer.children('.edit_hidden').val();
			// window.alert(editInput);
			// var editLink=editInput + '&version=' + version;
			// alert(buttonContainer.children('.run-button').html());
			buttonContainer.children('.analyze-button').attr('href', editLink);

			var indicatorSelector ='hidden-indicators-'+ name + '-' + version;
			selector=$.escapeSelector(indicatorSelector);
			var indicators=$('#' + selector).html();
			var indicatorsDiv=$(this).parent().parent().children('.software-name-column').children('.indicators-div');

			indicatorsDiv.html(indicators);

			var imageDiv=buttonContainer.parent().children('.software-image').children('.image-field');
			var imageSelector='hidden-image-'+ name + '-' + version;
			imageSelector=$.escapeSelector(imageSelector);
			// window.alert(imageSelector);
			var image=$('#' + imageSelector).html();
			// window.alert(imageDiv.html);
			imageDiv.html(image);

			var vIcon;
			
			if (visibility=='public')
			{
				vIcon='<i class="fas fa-lock-open" title="This software is publicly available"></i>';
			}
			else
			{
				vIcon='<i class="fas fa-lock" title="This software is private"></i>';
			}

			lockDiv=$(this).parent().parent().children('.software-name-column').children('.software-lock').html(vIcon);
			

			// window.alert(lockDiv);

	});

	$(".software-description").click(function() { 

		var grandparent=$(this).parent().parent();
		var name=grandparent.children('.software-name-column').children('.software-name').html();
		var version=grandparent.children('.software-versions').children('select').children('option:selected').html();

		// var modal=$("#descr-modal-" + name + "-" + version.replace(/\./g, '\\\\.'));
		var modal=$('#descr-modal-' + name + '-' + version.replace(/\./g, '\\.'));
		// window.alert(modal.html());
		modal.modal();

	});
})