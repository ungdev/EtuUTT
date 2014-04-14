
var uploadHere = $('.upload-here'),
    loader = $('#loader'),
    dropbox = $('#dropbox'),
    message = $('.message', dropbox),
    sendDropbox = {
        container: $('.send-dropbox-holder'),
        send: $('.send-dropbox'),
        cancel: $('.cancel-dropbox')
    };

uploadHere.click(function() {
    dropbox.toggle();
    $(this).parent().toggleClass('active').toggleClass('page-tabs-resolved-active');
    sendDropbox.container.toggle();
    return false;
});

sendDropbox.send.click(function() {
    loader.show();

    $.getJSON(Routing.generate('argentique_admin_upload_save', { id: gallery.id }), function() {
        location.reload();
    });

    return false;
});

sendDropbox.cancel.click(function() {
    loader.show();

    $.getJSON(Routing.generate('argentique_admin_upload_cancel'), function() {
        location.reload();
    });

    return false;
});

$(function(){
    dropbox.filedrop({
        paramname: 'photo',
        maxfiles: 99,
        maxfilesize: 5,
        url: Routing.generate('argentique_admin_upload'),

        uploadFinished:function(i, file){
            $.data(file).addClass('done');
        },

        error: function(err, file) {
            switch(err) {
                case 'BrowserNotSupported':
                    alert('Ce navigateur n\'est pas supporte');
                    break;
                case 'TooManyFiles':
                    alert('Vous ne pouvez pas ajouter autant de fichiers à la fois');
                    break;
                case 'FileTooLarge':
                    alert(file.name+' est trop grosse ! Les images ne peuvent pas faire plus de 5 MB chacune.');
                    break;
                default:
                    break;
            }
        },

        beforeEach: function(file){
            if (!file.type.match(/^image\//)){
                alert('Seules les images sont autorisées');
                return false;
            }
        },

        uploadStarted:function(i, file){
            createImage(file);
        },

        progressUpdated: function(i, file, progress) {
            $.data(file).find('.progress-bar').width(progress + '%');
        },

        afterAll: function() {
            sendDropbox.container.find('button').attr('disabled', false);
        }
    });

    var template =
        '<div class="preview">'+
            '<span class="imageHolder">'+
                '<div class="thumb"></div>'+
                '<span class="uploaded"></span>'+
            '</span>'+
            '<div class="progress-holder">'+
                '<div class="progress-bar"></div>'+
            '</div>'+
        '</div>';


    function createImage(file) {
        var preview = $(template),
            image = $('.thumb', preview);

        var reader = new FileReader();

        image.width = 100;
        image.height = 100;

        reader.onload = function(e){

            // e.target.result holds the DataURL which
            // can be used as a source of the image:

            console.log(e.target.result);
            image.css({
                'background-image': 'url('+ e.target.result +')'
            });
        };

        // Reading the file as a DataURL. When finished,
        // this will trigger the onload function above:
        reader.readAsDataURL(file);

        message.hide();
        preview.appendTo(dropbox);

        // Associating a preview container
        // with the file, using jQuery's $.data():

        $.data(file,preview);
    }
});