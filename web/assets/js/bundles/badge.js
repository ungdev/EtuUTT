
var upload = {
    form: {
        form: $('#picture-upload-form'),
        input: $('#form_file'),
    },
    frame: $('#picture-upload-frame'),
    link: $('#picture-upload-link')
};

var loader = $('#loader');

var picture = $('#picture-image'),
    topBarPicture = $('#top-bar-picture');

upload.link.click(function() {
    upload.form.input.click();
    return false;
});

upload.form.input.change(function() {
    loader.show();
    upload.form.form.submit();
    upload.form.input.val('');
});


function uploadEnd(result, data) {
    if (result == 'success') {
        var file = data.filename+'?'+Math.random();

        picture.attr('src', file);
        topBarPicture.attr('src', file);

        picture.load(function() {
            loader.hide();
        });
    } else {
        loader.hide();
        error.message.html(data.message);
        error.box.show();
    }
}
