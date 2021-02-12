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


/* 
 * Basic functionality for magic seach box classes.
 * Do everything after the document has fully loaded.
 */

$(document).ready(function()
{
    /*
     * Register function for close buttons, that will:
     * a) Remove current div, as well as sibling hidden input
     * b) Re-submit parent form
     */
    $(".fas.fa-times").click(function()
    {
        /*
         * Get the div containing image tag & selected name + remove it.
         * Also get the parent form, and resubmit its action
         */
        // window.alert("Hello");
        $(this).parent().remove();
        // window.alert(parentHiddenDiv.html());
        // $("#loading_div").show();
        // $("#not_loading_div").hide();

        // parentForm.submit();

    });

});

