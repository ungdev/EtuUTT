
var searchForm = {
    priceMax: {
        input: $('#etu_module_covoitbundle_search_priceMax'),
        label: $('label[for=etu_module_covoitbundle_search_priceMax]'),
        removePriceLink: false,
        valueBox: false,
        slider: false
    }
};

/* **************************
 *   Price max slider
 * **************************/

searchForm.priceMax.slider = $('<div></div>');
searchForm.priceMax.slider.addClass('price-max-slider');
searchForm.priceMax.input.parent().append(searchForm.priceMax.slider);

searchForm.priceMax.valueBox = $('<span></span>');
searchForm.priceMax.valueBox.addClass('price-max-value-box');

searchForm.priceMax.removePriceLink = $('<a href="javascript:void(0);" class="price-max-remove"></a>');

searchForm.priceMax.label.append(searchForm.priceMax.valueBox);
searchForm.priceMax.label.append(searchForm.priceMax.removePriceLink);

searchForm.priceMax.slider.slider({
    min: 0,
    max: 150,
    values: [ 20 ],
    slide: function( event, ui ) {
        searchForm.priceMax.valueBox.html(ui.values[0] + ' €');
        searchForm.priceMax.removePriceLink.html('<i class="fa fa-times"></i>');
        searchForm.priceMax.input.val(ui.values[0]);
    }
});

searchForm.priceMax.removePriceLink.click(function() {
    searchForm.priceMax.valueBox.html('');
    searchForm.priceMax.removePriceLink.html('');
    searchForm.priceMax.input.val('');
});

if (searchForm.priceMax.input.val() != '') {
    searchForm.priceMax.valueBox.html(searchForm.priceMax.input.val() + ' €');
    searchForm.priceMax.removePriceLink.html('<i class="fa fa-times"></i>');
}

searchForm.priceMax.input.hide();
