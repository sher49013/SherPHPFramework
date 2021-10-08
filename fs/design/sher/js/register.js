$(document).ready(function() {

    $('#register_submit').click(function (){
        $.ajax({
            type: "POST",
            url: '/api.php?action=register',
            dataType:"json",
            data: $('#register').serialize(),
            success: function(response){
                if (response.success) {
                    window.location.href = 'login.php';
                } else {
                    $('#error').text(response.error_message)
                }
            }
        });
    });

    $('#login_submit').click(function (){
        $.ajax({
            type: "POST",
            url: '/api.php?action=login',
            dataType:"json",
            data: $('#login').serialize(),
            success: function(response){
                if (response.success) {
                    window.location.href = 'dashboard.php';
                } else {
                    $('#error').text(response.error_message)
                }
            }
        });
    });

    $('.delete-item').click(function (){
        var id = $(this).data('id');
        $.ajax({
            type: "POST",
            url: '/api.php?action=delete_user',
            dataType:"json",
            data: {
                'id': id
            },
            success: function(response){
                if (response.success) {
                    window.location.href = 'dashboard.php';
                }
            }
        });
    });
});