
/*
 * File upload
 */
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
 * Collections list
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




/*
 * Collections edition
 */
var modal = $('#argentique-manage-collections');

modal.on('shown', function() {
    var writeTree = modal.find('#write-tree');
    var transaction = [];

    writeTree.jstree({
        plugins: [ 'dnd', 'types', 'unique' ],

        core: {
            data: collectionsTree,
            check_callback: true,
            html_titles: true
        },

        types: {
            '#': {
                max_depth: 3
            }
        }
    });

    writeTree.on('ready.jstree refresh.jstree', function(event) {
        var container = $(event.target);

        container.find('.item-action').remove();

        container.find('li').each(function(key, li) {
            li = $(li);

            var createLink = $('<a>');

            createLink.attr('href', 'javascript:void(0)');
            createLink.addClass('item-action').addClass('tip');
            createLink.attr('title', 'Créer un enfant');
            createLink.html('<i class="fa fa-plus"></i>');

            createLink.click(function() {
                console.log(li.attr('id'));
            });


            var renameLink = $('<a>');

            renameLink.attr('href', 'javascript:void(0)');
            renameLink.addClass('item-action').addClass('tip');
            renameLink.attr('title', 'Renommer');
            renameLink.html('<i class="fa fa-edit"></i>');

            renameLink.click(function() {
                console.log(li.attr('id'));
            });


            var removeLink = $('<a>');

            removeLink.attr('href', 'javascript:void(0)');
            removeLink.addClass('item-action').addClass('tip');
            removeLink.attr('title', 'Supprimer');
            removeLink.html('<i class="fa fa-times"></i>');

            removeLink.click(function() {
                console.log(li.attr('id'));
            });

            li.find('> .jstree-anchor:nth-child(2)')
                .after(removeLink)
                .after(renameLink)
                .after(createLink);
        });
    });
});
