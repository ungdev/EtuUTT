
var generator = {
    unique: {
        input: $('#generator-unique-input'),
        button: $('.generator-unique-add-button')
    },

    import: {
        button: $('.import-file-btn'),
        box: $('.comparison-generator-file')
    }
};

generator.unique.input.keypress(function(e) {
    // Press enter
    if (e.which == 13) {
        generator.unique.button.click();
    }
});

generator.unique.button.click(function() {
    logins.push(generator.unique.input.attr('data-login'));
    window.location.href = root + '?q=' + logins.join(':');
});

generator.import.button.click(function() {
    generator.import.box.show();
});