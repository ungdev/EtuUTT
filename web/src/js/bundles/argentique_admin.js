
var inputFiles = $('#fileupload'),
    dropzone = $('#dropzone');

inputFiles.fileupload({
    dataType: 'json',
    dropZone: dropzone,

    done: function (e, data) {
        $.each(data.result.files, function (index, file) {
            console.log(file);
        });
    }
});

dropzone.bind('dragover', function (e) {
    $('#dropzone').addClass('hover');
});

dropzone.bind('drop', function (e) {
    $('#dropzone').removeClass('hover');
});

/*
 {
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
 }
 */

var readTree = $('#read-tree'),
    loader = $('#photos-loader'),
    explainations = $('#explainations'),
    photos = $('#photos');

readTree.jstree({
    core: {
        data: collectionsTree
    }
});

readTree.on('select_node.jstree', function(node, selected) {
    explainations.hide();
    loader.show();
    photos.hide();

    var item = selected.node.data;

    $.post(
        Routing.generate('argentique_admin_photos', { p: encodeURI(item.pathname) }),
        { item: item },
        function(data) {
            loader.hide();
            photos.html(data);
            photos.show();

            photos.find('.argentique-gallery').justifiedGallery({
                waitThumbnailsLoad: false,
                rowHeight: 80
            });
        }
    );
});
