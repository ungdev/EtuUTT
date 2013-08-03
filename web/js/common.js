
/*
 * jQuery selectors
 */
var facebox = $('a[rel*=facebox]'),
	tip = $('.tip'),
	tipTransparent = $('.tip-transparent'),
	overlay = $('#overlay'),
	more = $('#more'),
	page = $('body'),
	ckeditor = $('.redactor'),
	usersAutocomplete = $('.user-autocomplete'),
	changeLocale = {
		link: $('.change-locale'),
		box: $('#change-locale-choices')
	},
	userbox = {
		box: $('.userbox'),
		link: $('.userbox-link'),
		menu: $('.userbox-menu')
	},
	menu = {
		head: {
			list: $('#head-menu-list'),
			items: $('#head-menu-list li')
		},
		mobile: $('#menu-mobile')
	},
	subscriptions = {
		follow: $('.subscription-subscribe'),
		unfollow: $('.subscription-unsubscribe')
	};


/*
 * Script
 */

var title = document.title;

function setCountTitle(count) {
	if (/\([\d]+\)/.test(title)) {
		title = title.split(') ');
		document.title = '(' + count + ') ' + title[1];
	} else {
		document.title = '(' + count + ') ' + title;
	}
}

function removeCountTitle() {
	if (/\([\d]+\)/.test(title)) {
		title = title.split(') ');
		document.title = title[1];
	} else {
		document.title =title;
	}
}

userbox.link.click(function() {
	userbox.box.toggleClass('userbox-clicked');
	userbox.menu.toggleClass('userbox-menu-clicked');
	userbox.menu.toggle();
	return false;
});

more.click(function() {
	if (! $(this).hasClass('active')) {
		menu.head.items.each(function() {
			if ($(this).hasClass('active')) {
				$(this).removeClass('active').addClass('old-active');
			}
		});

		$(this).addClass('active');
	} else {
		menu.head.items.each(function() {
			if ($(this).hasClass('old-active')) {
				$(this).removeClass('old-active').addClass('active');
			}
		});

		$(this).removeClass('active');
	}

	overlay.toggle();
	menu.mobile.toggle();

	return false;
});

changeLocale.link.click(function() {
	menu.head.items.each(function() {
		if ($(this).hasClass('old-active')) {
			$(this).removeClass('old-active').addClass('active');
		}
	});

	$(this).removeClass('active');
	menu.mobile.hide();

	overlay.show();
	changeLocale.box.show();

	return false;
});

page.on('click', function(e) {
	userbox.box.removeClass('userbox-clicked');
	userbox.menu.removeClass('userbox-menu-clicked');
	userbox.menu.hide();

	menu.head.items.each(function() {
		if ($(this).hasClass('old-active')) {
			$(this).removeClass('old-active').addClass('active');
		}
	});

	$(this).removeClass('active');

	overlay.hide();
	menu.mobile.hide();
	changeLocale.box.hide();
});

$(document).keypress(function(event) {
	if (event.keyCode == 27) {
		userbox.box.removeClass('userbox-clicked');
		userbox.menu.removeClass('userbox-menu-clicked');
		userbox.menu.hide();

		menu.head.items.each(function() {
			if ($(this).hasClass('old-active')) {
				$(this).removeClass('old-active').addClass('active');
			}
		});

		$(this).removeClass('active');

		overlay.hide();
		menu.mobile.hide();
		changeLocale.box.hide();
	}
});

$('.userbox a, #menu-mobile a, #change-locale-choices a').click(function() {
	return true;
});

// Suscribe
subscriptions.follow.click(function() {
	var url = Routing.generate('notifs_subscribe', {
		'entityType': $(this).attr('data-entityType'),
		'entityId': $(this).attr('data-entityId')
	});

	var id = $(this).attr('id').replace('-subscribe', '');

	$('#'+ id +'-subscribe').hide();
	$('#'+ id +'-loader').show();

	$.getJSON(url, function(data) {
		$('#'+ id +'-loader').hide();

		if (typeof data.status != 'undefined' && data.status == 200) {
			$('#'+ id +'-unsubscribe').show();
		} else {
			$('#'+ id +'-subscribe').show();

			alert('Une erreur s\'est produite. Veuillez réessayer ou signaler le problème.');
		}
	});

	return false;
});


// Unsubscribe
subscriptions.unfollow.click(function() {
	var url = Routing.generate('notifs_unsubscribe', {
		'entityType': $(this).attr('data-entityType'),
		'entityId': $(this).attr('data-entityId')
	});

	var id = $(this).attr('id').replace('-unsubscribe', '');

	$('#'+ id +'-unsubscribe').hide();
	$('#'+ id +'-loader').show();

	$.getJSON(url, function(data) {
		$('#'+ id +'-loader').hide();

		if (typeof data.status != 'undefined' && data.status == 200) {
			$('#'+ id +'-subscribe').show();
		} else {
			$('#'+ id +'-unsubscribe').show();

			alert('Une erreur s\'est produite. Veuillez réessayer ou signaler le problème.');
		}
	});

	return false;
});

$('.date-picker').datepicker({
	dateFormat: 'dd/mm/yy',
	changeMonth: true,
	changeYear: true
});

$('.birthday-picker').datepicker({
	dateFormat: 'dd/mm/yy',
	changeMonth: true,
	changeYear: true,
	yearRange: "-100:-10"
});


$(function() {
	facebox.facebox();

	tip.tipsy({
		gravity: 's',
		html: true,
		opacity: 1
	});

	tipTransparent.tipsy({
		gravity: 's',
		html: true,
		opacity: 0.5
	});

	// Load CKEditor
	ckeditor.sceditor({
		plugins: "bbcode",
		style: "/sceditor/minified/jquery.sceditor.default.min.css",
		emoticonsRoot: '/sceditor/',
		toolbar:
			"source|bold,italic,underline,strike,subscript,superscript|left,center,right,justify" +
			"|font,size,removeformat|bulletlist,orderedlist" +
			"|table|quote|link,unlink|image,youtube|maximize"
	});

	// Users autocomplete
	if (usersAutocomplete) {
		usersAutocomplete.autocomplete({
			minLength: 3,
			source: function(request, response) {
				$.getJSON(Routing.generate('api_user_search', {term: request.term}),
					{ format: 'json', token: _t },
					function(data) {
						var users = data.body.users;

						response($.map(users, function( item ) {
							return {
								label: item.firstName+' '+item.lastName,
								value: item.firstName+' '+item.lastName,
								user: item
							}
						}));
					});
			}
		});

		if (usersAutocomplete.data("ui-autocomplete")) {
			usersAutocomplete.data("ui-autocomplete")._renderItem = function(ul, item) {
				return $("<li style=\"margin-bottom: 3px;\">")
					.append(
						"<a>" +
							"<img src=\"/photos/"+ item.user.picture +"\" style=\"float: left; max-height: 25px; max-width: 25px; margin-right: 5px;\" />" +
							"<span style=\"display: block; float: left; margin-top: 0;\">" + item.label + "</span>" +
							"<div style=\"clear: both;\"></div>" +
							"</a>"
					)
					.appendTo(ul);
			};
		}
	}
});
