
var generator = {
    unique: {
        input: $('.generator-unique-input'),
        hidden: $('.generator-unique-hidden'),
        button: $('.generator-unique-add-button')
    },

    multiple: {},

    list: {
        selectors: {
            item: '.generator-list-item'
        }
    }
};

generator.unique.input.autocomplete({
    minLength: 2,
    source: logins,

    focus: function( event, ui ) {
        generator.unique.input.val(ui.item.label);
        return false;
    },

    select: function( event, ui ) {
        generator.unique.input.val( ui.item.label );
        generator.unique.hidden.val( ui.item.value );
        return false;
    }
}).data( "ui-autocomplete" )._renderItem = function(ul, item) {
    return $("<li style=\"margin-bottom: 3px;\">")
        .append(
            "<a>" +
                "<img src=\" "+ item.avatar + "\" style=\"float: left; max-height: 25px; max-width: 25px; margin-right: 5px;\" />" +
                "<span style=\"display: block; float: left; margin-top: 0;\">" + item.label + "</span>" +
                "<div style=\"clear: both;\"></div>" +
                "</a>"
        )
        .appendTo(ul);
};

generator.unique.input.keypress(function(e) {
    // Press enter
    if (e.which == 13) {
        generator.unique.button.click();
    }
});

generator.unique.button.click(function() {
    var login = generator.unique.hidden.val(),
        user = logins[login];
});