
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

    if (data.count == 0) {
        starting.hide();
        noPhoto.show();
        return;
    }

    starting.hide();

    importing.find('.stats .total').text(data.count);
    importing.show();

    totalCount = data.count;
    total.text(totalCount);

    var max = (totalCount > 10) ? 10 : totalCount;

    for (var i = 0; i < max; i++) {
        upload(photos[i]);
    }
});

function upload(photoId) {
    $.get(Routing.generate('argentique_admin_synchronize_photo', { photoId: photoId }), function() {
        console.log(photoId);

        received++;

        current.text(received);

        var percent = (received / totalCount) * 100;
        progressbar.css('width', percent + '%');

        if (typeof photos[position] != 'undefined') {
            upload(photos[position]);
        } else if (received == totalCount) {
            importing.hide();
            ending.show();
            window.location.replace(Routing.generate('argentique_admin_synchronize_end'));
        }
    });

    position++;
}