
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