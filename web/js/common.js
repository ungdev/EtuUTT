
/*
 * jQuery selectors
 */
var facebox = $('a[rel*=facebox]'),
	tip = $('.tip'),
	overlay = $('#overlay'),
	more = $('#more'),
	page = $('body'),
	redactor = $('.redactor'),
	redactorLimited = $('.redactor-limited'),
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

$(function() {

	facebox.facebox();

	tip.tipsy({
		gravity: 's',
		html: true
	});

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

	page.bind('click', function(e) {
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

	// Load Redactor
	redactor.redactor({
		fixed: true,
		lang: 'fr',
		buttons: [
			'formatting', '|', 'bold', 'italic', 'deleted', 'underline', '|',
			'fontcolor', 'backcolor', '|', 'alignment', '|',
			'unorderedlist', 'orderedlist', '|', 'image', 'video', 'file', 'table', 'link', '|',
			'horizontalrule', '|', 'html'
		]

		/*
		 'html', '|', 'formatting', '|', 'bold', 'italic', 'deleted', '|',
		 'unorderedlist', 'orderedlist', 'outdent', 'indent', '|',
		 'image', 'video', 'file', 'table', 'link', '|',
		 'fontcolor', 'backcolor', '|', 'alignment'
		 */
	});

	redactorLimited.redactor({
		fixed: true,
		lang: 'fr',
		buttons: [
			'bold', 'italic', 'deleted', 'underline', 'fontcolor', '|',
			'alignleft', 'aligncenter', 'alignright', '|',
			'unorderedlist', '|', 'image', 'link'
		]
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

	// Users autocomplete
	if (usersAutocomplete) {
		usersAutocomplete.autocomplete({
			minLength: 3,
			source: function(request, response) {
				$.getJSON(Routing.generate('api_users_search'), { term: request.term }, function(data) {
					response($.map(data, function( item ) {
						return {
							label: item.fullName,
							value: item.fullName,
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
							"<img src=\"/photos/"+ item.user.avatar +"\" style=\"float: left; max-height: 25px; max-width: 25px; margin-right: 5px;\" />" +
							"<span style=\"display: block; float: left; margin-top: 0;\">" + item.label + "</span>" +
							"<div style=\"clear: both;\"></div>" +
							"</a>"
					)
					.appendTo(ul);
			};
		}
	}
});
