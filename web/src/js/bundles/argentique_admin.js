

$('#fileupload').fileupload({
    dataType: 'json',
    dropZone: $('#dropzone'),

    done: function (e, data) {
        $.each(data.result.files, function (index, file) {
            console.log(file);
        });
    }
});

$('#dropzone').bind('dragover', function (e) {
    $('#dropzone').addClass('hover');
});

$('#dropzone').bind('drop', function (e) {
    $('#dropzone').removeClass('hover');
});

$('#tree').jstree({
    plugins: [ 'dnd', 'types' ],

    core: {
        data: collectionsTree,
        check_callback: true
    },

    types: {
        '#': {
            max_depth: 2
        }
    }
});
