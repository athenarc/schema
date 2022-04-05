$(document).ready(function()
{

    $(".submit-btn").click(function(){
        password=$("#jupyterserver-password").val();
        if (password.length!=0){
            $("#creatingModal").modal({backdrop: 'static', keyboard: false});
        }
    });
});