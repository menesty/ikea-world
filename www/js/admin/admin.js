$(function () {
    $('#downloadImages').on('click', function () {
        $("#main-content").mask("Loading...");

        var artNumber = $('#artNumber').val();
        $.get(contextUrl + "admin/downloadPhotos/" + artNumber, function (data) {
            $("#main-content").unmask();ß
        });
    });
});