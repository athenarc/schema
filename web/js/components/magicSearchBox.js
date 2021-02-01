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

