
$('#change-locale-link').click(function() {
	$('#change-locale-link').toggleClass('change-locale-link');
	$('#next-change-locale-link').toggleClass('next-change-locale-link');
	$('#change-locale').toggle();
	return false;
});

$('.userbox').click(function() {
	$('.userbox').toggleClass('userbox-clicked');
	$('.userbox-menu').toggleClass('userbox-menu-clicked');
	$('.userbox-menu').toggle();
	return false;
});

$('#more').click(function() {
	if (! $(this).hasClass('active')) {
		$('#head-menu-list li').each(function() {
			if ($(this).hasClass('active')) {
				$(this).removeClass('active').addClass('old-active');
			}
		});

		$(this).addClass('active');
	} else {
		$('#head-menu-list li').each(function() {
			if ($(this).hasClass('old-active')) {
				$(this).removeClass('old-active').addClass('active');
			}
		});

		$(this).removeClass('active');
	}

	$('#overlay').toggle();
	$('#overlay-content').toggle();

	return false;
});

$('body').click(function() {
	$('.userbox').removeClass('userbox-clicked');
	$('.userbox-menu').removeClass('userbox-menu-clicked');
	$('.userbox-menu').hide();
	$('#change-locale-link').removeClass('change-locale-link');
	$('#next-change-locale-link').removeClass('next-change-locale-link');
	$('#change-locale').hide();

	$('#head-menu-list li').each(function() {
		if ($(this).hasClass('old-active')) {
			$(this).removeClass('old-active').addClass('active');
		}
	});

	$(this).removeClass('active');

	$('#overlay').hide();
	$('#overlay-content').hide();
});