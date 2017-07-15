$('#loginForm').submit(function (e) {
    e.preventDefault();
    $.ajax({
        type: $('form').attr('method'),
        url:  $('form').attr('action'),
        data: $('form').serialize(),
        dataType    : "json",
        success: function (data, status, object) {
            if (data.success === true) {
                window.location.href = data.targetUrl;
            } else {
                if(data.error) $('.error').html(data.message);
            }

        },
        error: function (data, status, object) {
            if(data.error) $('.error').html(data.message);
        }
    });
});

$('#registerForm').submit(function (e) {
    e.preventDefault();
    $.ajax({
        type: $('form').attr('method'),
        url:  $('form').attr('action'),
        data: $('form').serialize(),
        dataType    : "json",
        success: function (data, status, object) {
            if (data.success === "true") {
                window.location.href = data.targetUrl;
            } else {
                $('.error').html(data.message);
            }
        },
        error: function (data, status, object) {
            $('.error').html(data.message);
        }
    });
});

$('#contactForm').submit(function (e) {
    e.preventDefault();
    $.ajax({
        type: $('form').attr('method'),
        url:  $('form').attr('action'),
        data: $('form').serialize(),
        dataType    : "json",
        success: function (data, status, object) {
            window.location.href = data.targetUrl;
        },
        error: function (data, status, object) {
        }
    });
});



