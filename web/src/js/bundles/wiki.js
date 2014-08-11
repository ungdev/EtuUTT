

var currentCategories = $('.wiki-nested-item-category-current');

$(function() {
	currentCategories
		.removeClass('wiki-nested-item-category-up').addClass('wiki-nested-item-category-down');

	currentCategories
		.find('.wiki-nested-list-depth-0, .wiki-nested-list-depth-1, .wiki-nested-list-depth-2, .wiki-nested-list-depth-3')
		.show();

	currentCategories
		.find('.wiki-nested-link-category').attr('data-state', 'down');
});

$('.wiki-nested-link-category').click(function() {
	if ($(this).attr('data-state') == 'up') {
		$(this).parent().removeClass('wiki-nested-item-category-up').addClass('wiki-nested-item-category-down');
		$(this).parent().find('.wiki-nested-list-depth-'+$(this).attr('data-depth')).show();
		$(this).attr('data-state', 'down');
	} else {
		$(this).parent().removeClass('wiki-nested-item-category-down').addClass('wiki-nested-item-category-up');
		$(this).parent().find('.wiki-nested-list-depth-'+$(this).attr('data-depth')).hide();
		$(this).attr('data-state', 'up');
	}

	$(this).parent().find('.wiki-nested-item-page').css('list-style-image', 'none');

	return false;
});
