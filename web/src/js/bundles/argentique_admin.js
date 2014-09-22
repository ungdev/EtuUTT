
var starting = $('.starting'),
    ending = $('.ending'),
    importing = $('.importing'),
    failing = $('.failing'),

    progressbar = $('.progress .bar'),
    current = $('.current'),
    total = $('total'),
    logs = $('.logs'),
    noPhoto = $('.no_photo');

var totalCount = 0,
    photos = [],
    position = 0,
    received = 0;

$.getJSON(Routing.generate('argentique_admin_synchronize_start'))
    .done(function(data) {
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
    })
    .fail(function(data) {
        starting.hide();
        failing.html('An error occured: <br /><br /><pre>' + JSON.stringify(data.responseJSON, null, 4) + '</pre>');
        failing.show();
    });

function upload(photoId) {
    $.getJSON(Routing.generate('argentique_admin_synchronize_photo', { photoId: photoId }))
        .done(function() {
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
        })
        .fail(function(data) {
            starting.hide();
            failing.html('An error occured: <br /><br /><pre>' + JSON.stringify(data.responseJSON, null, 4) + '</pre>');
            failing.show();
        });

    position++;
}