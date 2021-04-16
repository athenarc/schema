$(document).ready(function(){
    
    $(".preview-btn").click(function(){
        var content=$("#content-area").val();

        var newWindow = window.open("", "MsgWindow", "width=1200,height=800");
        newWindow.document.write('<div class="container">' + content + '</div>');
    });
})