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
    var status="Init";
    var refreshId;

    $(".cancel-button-container").css('display','inline-block');

    function enableForm()
    {
        totalFields=Number($("#hidden_fieldsNum").val());
        var i=0;
        for (i=0; i<totalFields; i++)
        {
            var fieldID="#field-" + i;
            var field=$(fieldID);
            field.removeClass('disabled-box');
            if ( (!field.hasClass('file_field')) && (!field.hasClass('folder_field')) && (!field.hasClass('array_field')) )
            {
                field.prop('readonly',false);
            }
            
        }
        $(".run-gif-img").hide();
        
        $("#outFolder").removeClass('disabled-box');
        $("#outFolder").prop('readonly',false);
        $("#memory").removeClass('disabled-box');
        $("#cores").removeClass('disabled-box');
        $("#systemmount").prop('readonly',false);
        $("#memory").prop('readonly',false);
        $("#cores").prop('readonly',false);
        $(".cancel-button-container").css('display','none');
        $(".select-output-button").removeAttr('disabled');
        $(".clear-output-button").removeAttr('disabled');
        $("#software-start-run-button").removeAttr('disabled');
        $("#software-cancel-button").hide();
        $(".clear-file-btn").each(function(){
            $(this).removeAttr('disabled');
        });
        $(".select-file-btn").each(function(){
            $(this).removeAttr('disabled');
        });
        $(".clear-folder-btn").each(function(){
            $(this).removeAttr('disabled');
        });
        $(".select-folder-btn").each(function(){
            $(this).removeAttr('disabled');
        });
        $(".btn-default-values").each(function(){
            $(this).removeAttr('disabled');
        });
        if ($("#has_example").length)
        {
            $("#software-run-example-button").removeAttr('disabled');
        }
        else
        {
            if ($(".add-example-link").length)
            {
                $(".add-example-link").show();
            }
        }
    }
    function sendRequest(refId)
    {
        var jobid=$("#hidden_jobid_input").val();
        $.ajax({
                url: "index.php?r=software/get-logs",
                type: "GET",
                data: {"jobid" : jobid },
                dataType: "html",
                success: function (data) 
                {
                    $("#initial-status").hide();
                    $('#pod-logs').html(data);
                    $('.container-logs').scrollTop($('.container-logs').prop('scrollHeight')); 
              
              
                    status=$("#status-value").text(); 
                    if ( (status == "COMPLETE") || (status == "SYSTEM_ERROR") || (status == "EXECUTOR_ERROR") || (status == "CANCELED") )
                    {
                        clearInterval(refId);
                        setTimeout(enableForm(),2000);
                    }
                        

                },
                retries: 2,
            });
    }

    $("#software-cancel-button").click(function(){

        var jobid=$("#hidden_jobid_input").val();
        $.ajax({
            url: "index.php?r=software/cancel-job",
            type: "GET",
            data: { "jobid": jobid,},
            dataType: "html",
            success: function (data) 
            {
                // clearInterval(refreshId);
                // setTimeout(enableForm(),2000);
                // $(".job-output").hide();
                // $(".running-logo").hide();
                // $("#command-text-box").removeClass('disabled-box');
                // $("#command-text-box").prop('readonly',false);
                // $(".cancel-button-container").css('display','none');
                // $(".select-mount-button").removeAttr('disabled');
                // $(".clear-mount-button").removeAttr('disabled');
                // $("#software-start-run-button").removeAttr('disabled');
            }
        });

        // clearInterval(refreshId);
        // cancelClean();
        // totalFields=Number($("#hidden_fieldsNum").val());
        // for (i=0; i<=totalFields; i++)
        // {
        //     var fieldID="#field-" + i;
        //     var field=$(fieldID);
        //     field.removeClass('disabled-box');
        //     if ( (!field.hasClass('file_field')) && (!field.hasClass('folder_field')) && (!field.hasClass('array_field')) )
        //     {
        //         field.prop('readonly',false);
        //     } 
        // }
        // $(".job-output").hide()
        // $("#isystemmount").removeClass('disabled-box');
        // $("#iosystemmount").removeClass('disabled-box');
        // $("#osystemmount").removeClass('disabled-box');
        // $(".select-mount-button").removeAttr('disabled');
        // $(".clear-mount-button").removeAttr('disabled');
        // $("#memory").removeClass('disabled-box');
        // $("#cores").removeClass('disabled-box');
        // $("#systemmount").prop('readonly',false);
        // $("#memory").prop('readonly',false);
        // $("#cores").prop('readonly',false);
        // // $("#command-text-box").removeClass('disabled-box');
        // $(".clear-file-btn").each(function(){
        //     $(this).removeAttr('disabled');
        // });
        // $(".select-file-btn").each(function(){
        //     $(this).removeAttr('disabled');
        // });
        // $(".clear-folder-btn").each(function(){
        //     $(this).removeAttr('disabled');
        // });
        // $(".select-folder-btn").each(function(){
        //     $(this).removeAttr('disabled');
        // });
        // $(".btn-default-values").each(function(){
        //     $(this).removeAttr('disabled');
        // });
        // // $("#command-text-box").prop('readonly',false);
        // $("#select-active-form-btn").removeClass('disabled-btn');
        // $("#select-non-active-form-btn").removeClass('disabled-btn');
        // if ($("#has_example").length)
        // {
        //     $("#software-run-example-button").removeAttr('disabled');
        // }
        // else
        // {
        //     if ($(".add-example-link").length)
        //     {
        //         $(".add-example-link").show();
        //     }
        // }
        enableForm();

    
    });

    /*
     * This part is executed once the view file is rendered.
     * and starts the ajax calls for the logs.
     */
    var refreshId = setInterval(function() 
    {
      sendRequest(refreshId);
      
    }, 2000);
    
    

})