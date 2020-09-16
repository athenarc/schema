$(document).ready(function(){
	
	$("#mark_all_seen").click(function(){
		$.ajax({
            url: "index.php?r=site/mark-all-notifications-seen",
            type: "GET",
            data: {},
            dataType: "html",
            success: function (data) 
            {
               $('.notification-menu-header').html("<i class='fas fa-bell'></i>&nbsp;&nbsp;0</span>")
               $('.notification-menu-header').removeClass('color-notification-bell');
               $('.notification-menu-header').addClass('grey-notification-bell');
               $('.notification').remove();
               $('#mark_all_seen').hide();
               $('.dropdown-header').html('You have 0 notifications');


            }

        });
	});


})