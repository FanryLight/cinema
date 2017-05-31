$(document).ready(function () {
    $('.roleSelect').change(function () {
        var path = "change_role";
        var value=$(this).val();
        var id = $(this).attr('id');
        var json = '{"role":"'+value+'", "id":'+id+'}';
        $.ajax({
            type: "post",
            url:  path,
            data : json,
            dataType    : "html",
            success: function (data, status, object) {
                document.write(data);
            }
        })
    });
});
