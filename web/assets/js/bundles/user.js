
var upload = {
    form: {
        form: $('#avatar-upload-form'),
        input: $('#form_file'),
    },
    frame: $('#avatar-upload-frame'),
    link: $('#avatar-upload-link')
};

var loader = $('#loader');

var avatar = $('#avatar-image'),
    topBarAvatar = $('#top-bar-avatar');

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

        avatar.attr('src', file);
        topBarAvatar.attr('src', file);

        avatar.load(function() {
            loader.hide();
        });
    } else {
        loader.hide();
        error.message.html(data.message);
        error.box.show();
    }
}
