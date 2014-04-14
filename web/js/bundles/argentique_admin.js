
var starting = $('.starting'),
    ending = $('.ending'),
    importing = $('.importing'),
    progressbar = $('.progress .bar'),
    current = $('.current'),
    total = $('total'),
    logs = $('.logs'),
    noPhoto = $('.no_photo');

var totalCount = 0,
    photos = [],
    position = 0,
    received = 0;

$.getJSON(Routing.generate('argentique_admin_synchronize_start'), function(data) {
    photos = data.photos;

    starting.hide();

    if (data.count == 0) {
        noPhoto.show();
        return;
    }

    importing.find('.stats .total').text(data.count);
    importing.show();

    totalCount = data.count;
    total.text(totalCount);

    var max = (totalCount > 10) ? 10 : totalCount;

    for (var i = 0; i < max; i++) {
        upload(photos[i]);
    }
});

function upload(photo) {
    $.get(Routing.generate('argentique_admin_synchronize_photo', { photoId: photo.id }), function() {
        received++;

        current.text(received);

        var percent = (received / totalCount) * 100;
        progressbar.css('width', percent + '%');

        if (typeof photos[position] != 'undefined') {
            upload(photos[position]);
        } else if (received == totalCount) {
            importing.hide();
            ending.show();

            $.get(Routing.generate('argentique_admin_synchronize_end'), function() {
                window.location.replace(Routing.generate('argentique_index'));
            });
        }
    });

    position++;
}