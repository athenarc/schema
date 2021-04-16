$(document).ready(function(){
    $(".delete-button").click(function(){
        var hidden=$(this).parent().children('.hidden_page_id');
        var id=hidden.val();

        var modal=$('#delete-modal-' + id);

        modal.modal();
    });
})