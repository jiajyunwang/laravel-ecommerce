
/*====================================
    Account Popup
======================================*/
$(document).ready(function() {
    $('#accountForm').on('submit', function(event) {
        event.preventDefault(); 
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: $(this).attr('action'),
            method: $(this).attr('method'),
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#overlay').show();
                    setTimeout(function() {
                        $('#overlay').hide(); 
                    }, 3000);
                } else {
                    alert('提交失败，请重试。');
                    $('#overlay').hide();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('提交失败，请重试。');
                $('#overlay').hide();
            }
        });
    });
});



