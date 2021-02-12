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

    function cleanUp()
    {
        var jobid=$("#hidden_jobid_input").val();
        
        $.ajax({
            url: "index.php?r=software-mpi/cancel",
            type: "GET",
            data: { "jobid": jobid},
            dataType: "html",
            success: function(data)
            {
                $('#hidden_cluster_input').val('down');
                cleanInterface();
            }
            
        });
    }
    function cleanInterface()
    {
        // $(".container-logs").hide();
        $(".running-logo").css('display','none');
        $(".cancel-button-container").css('display','none');
        $(".select-mount-button").removeAttr('disabled');
        $(".clear-mount-button").removeAttr('disabled');
        $("#software-start-run-button").removeAttr('disabled');
        totalFields=Number($("#hidden_fieldsNum").val());
        for (i=0; i<=totalFields; i++)
        {
            var fieldID="#field-" + i;
            var field=$(fieldID);
            field.removeClass('disabled-box');
            field.prop('readonly',false);
        }
        // $(".job-output").hide()
        $("#isystemmount").removeClass('disabled-box');
        $("#iosystemmount").removeClass('disabled-box');
        $("#osystemmount").removeClass('disabled-box');
        $("#memory").removeClass('disabled-box');
        $("#pernode").removeClass('disabled-box');
        $("#cores").removeClass('disabled-box');
        $("#processes").removeClass('disabled-box');
        $("#systemmount").prop('readonly',false);
        $("#memory").prop('readonly',false);
        $("#pernode").prop('readonly',false);
        $("#processes").prop('readonly',false);
        $("#cores").prop('readonly',false);
        $("#initial-status").hide();
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
    

    $("#software-cancel-button").click(function(){


        $("#pod-logs").hide()
        $(".status-init").html('Cancelling, please do not close this window...');
        $("#initial-status").show();
        clearInterval(logRefreshId);
        cleanUp();
        // cleanInterface();
        // $(".status-init").hide();
        $("#status-value").html("Canceled");
        
        

    
    });

    // var refreshId = setInterval(function() 
    // {
      
    //   sendRequest(refreshId);
      
    // }, 2000);
    
    function setupClusterJobStart()
    {
        var jobid=$('#hidden_jobid_input').val();
        $.ajax({
                url: "index.php?r=software-mpi/setup-cluster",
                type: "GET",
                data: { "jobid": jobid },
                dataType: "html",
                success: function (data) 
                {
                    // var clusterStatus=$('#hidden_cluster_input').val('up');
                    // startJob();
                    logRefreshId=startLogs();

                },
                retries: 2,
            // complete: function() 
            // {
            // // Schedule the next request when the current one's complete
            //   setInterval(sendRequest, 5000); // The interval set to 5 seconds
            // },
            });
    }

    function startJob()
    {
        var jobid=$('#hidden_jobid_input').val();
        var clusterStatus=$('#hidden_cluster_input').val('running');
        $.ajax({
                url: "index.php?r=software-mpi/start-job",
                type: "GET",
                data: { "jobid": jobid },
                dataType: "html",
                success: function (data) 
                {
                    // var clusterStatus=$('#hidden_cluster_input').val('done');
                    // $(".running-logo").css('display','none');
                    // clearInterval(logRefreshId);
                    // cleanInterface();
                    // $("#status-value").html("Completed");
                    // $('#hidden_cluster_input').val('down');
                    
                    

                },
                retries: 2,
            // complete: function() 
            // {
            // // Schedule the next request when the current one's complete
            //   setInterval(sendRequest, 5000); // The interval set to 5 seconds
            // },
            });
        
        logRefreshId=startLogs();
    }

    function getLogs(refId)
    {
        var jobid=$('#hidden_jobid_input').val();
        $.ajax({
                url: "index.php?r=software-mpi/get-logs",
                type: "GET",
                data: { "jobid": jobid },
                dataType: "html",
                success: function (data) 
                {  
                    $("#initial-status").hide();
                    $('#pod-logs').html(data);
                    $(".container-logs").scrollTop($(".container-logs").prop('scrollHeight'));
                    if ($("#status-value").html()=="Completed")
                    {
                        clearInterval(refId);
                        cleanInterface();
                    }

                },
                retries: 2,
            // complete: function() 
            // {
            // // Schedule the next request when the current one's complete
            //   setInterval(sendRequest, 5000); // The interval set to 5 seconds
            // },
            });
    }

    function startLogs()
    {
        var refreshId ='';
        
        refreshId= setInterval(function() 
        {
      
            getLogs(refreshId);
          
        }, 5000);
    

        return refreshId;
    }

    var clusterStatus=$('#hidden_cluster_input').val();
    var logRefreshId;

    if (clusterStatus=='start')
    {
        setupClusterJobStart();
    }
    if (clusterStatus=='running')
    {
        getLogs();

        if ($("#status-value").html()!="Completed")
        {
            startLogs();
        }
        else
        {
            clearInterval(logRefreshId);
            cleanInterface();
        }

    }

})