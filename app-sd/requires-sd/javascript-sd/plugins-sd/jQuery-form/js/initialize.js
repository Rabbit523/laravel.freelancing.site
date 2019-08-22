$(document).on('change', '#profile_picture', function () {
    var progressBar = $('.progressBar'), bar = $('.progressBar .bar'), percent = $('.progressBar .percent');

    $('#update_profile_pic_form').ajaxForm({
        beforeSend: function () {
            progressBar.fadeIn();
            var percentVal = '0%';
            bar.width(percentVal)
            percent.html(percentVal);
        },
        uploadProgress: function (event, position, total, percentComplete) {
            var percentVal = percentComplete + '%';
            bar.width(percentVal)
            percent.html(percentVal);
        },
        success: function (html, statusText, xhr, $form) {
            obj = $.parseJSON(html);
            if (obj.status) {
                var percentVal = '100%';
                bar.width(percentVal)
                percent.html(percentVal);
                $(".profile_picture").prop('src', obj.image_medium);
                $(".user_profile_img_small").prop('src', obj.image_small);
                $('.remove_profile_picture').removeClass("hidden");
                toastr["success"](obj.success);
            } else {
                //alert(obj.error);
                toastr["error"](obj.error);
            }
        },
        complete: function (xhr) {
            progressBar.fadeOut();
        }
    }).submit();

});