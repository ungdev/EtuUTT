
/*
 * jQuery selectors
 */
var facebox = $('a[rel*=facebox]'),
	tip = $('.tip'),
	tipTransparent = $('.tip-transparent'),
	overlay = $('#overlay'),
	more = $('#more'),
	page = $('body'),
    sceditor = $('.redactor'),
    sceditorLimited = $('.redactor-limited'),
	redactorHtml = $('.redactor-html'),
	usersAutocomplete = $('.user-autocomplete'),
	changeLocale = {
		link: $('.change-locale'),
		box: $('#change-locale-choices')
	},
	userbox = {
		box: $('.userbox'),
		link: $('.userbox-link'),
		menu: $('.userbox-menu'),
        avatar: $('.userbox-avatar-link')
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

userbox.avatar.click(function() {
    userbox.link.click();
    return false;
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
    firstDay: 1,
	dateFormat: 'dd/mm/yy',
	changeMonth: true,
	changeYear: true,
    dayNamesMin: [ "Di", "Lu", "Ma", "Me", "Je", "Ve", "Sa" ],
    monthNamesShort: [ "Jan", "Fev", "Mar", "Avr", "Mai", "Juin", "Juil", "Aou", "Sep", "Oct", "Nov", "Dec" ]
});

$('.birthday-picker').datepicker({
    firstDay: 1,
	dateFormat: 'dd/mm/yy',
	changeMonth: true,
	changeYear: true,
	yearRange: "-100:-10",
    dayNamesMin: [ "Di", "Lu", "Ma", "Me", "Je", "Ve", "Sa" ],
    monthNamesShort: [ "Jan", "Fev", "Mar", "Avr", "Mai", "Juin", "Juil", "Aou", "Sep", "Oct", "Nov", "Dec" ]
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

    // Load SCeditor
    sceditor.sceditor({
        plugins: "bbcode",
        style: "/sceditor/minified/jquery.sceditor.default.min.css",
        emoticonsRoot: '/',
        toolbar:
            "source|bold,italic,underline,strike,subscript,superscript|left,center,right,justify" +
                "|font,size,removeformat|bulletlist,orderedlist" +
                "|table|quote|link,unlink|image,youtube|maximize",
        emoticons: {
            dropdown: {
                ">:(": "emoticons/angry.png",
                ":aw:": "emoticons/aw.png",
                "8)": "emoticons/cool.png",
                ":D": "emoticons/ecstatic.png",
                ">:D": "emoticons/furious.png",
                ":O": "emoticons/gah.png",
                ":)": "emoticons/happy.png",
                "<3": "emoticons/heart.png",
                ":/": "emoticons/hm.png",
                ":3": "emoticons/kiss.png",
                ":|": "emoticons/meh.png",
                ":x": "emoticons/mmf.png",
                ":(": "emoticons/sad.png",
                ":P": "emoticons/tongue.png",
                ":o": "emoticons/what.png",
                ";)": "emoticons/wink.png"
            },
            hidden: {
                ">:[": "emoticons/angry.png",
                "8]": "emoticons/cool.png",
                "D:": "emoticons/gah.png",
                ":]": "emoticons/happy.png",
                ":\\": "emoticons/hm.png",
                "-.-": "emoticons/meh.png",
                "-_-": "emoticons/meh.png",
                ":X": "emoticons/mmf.png",
                ":[": "emoticons/sad.png",
                ":\'(": "emoticons/sad.png",
                ":\'[": "emoticons/sad.png",
                ":p": "emoticons/tongue.png",
                ":?": "emoticons/what.png",
                ";]": "emoticons/wink.png",
                ";D": "emoticons/wink.png"
            }
        }
    });

    // Load SCeditor limited
    sceditorLimited.sceditor({
        plugins: "bbcode",
        style: "/sceditor/minified/jquery.sceditor.default.min.css",
        emoticonsRoot: '/',
        toolbar:
            "source|bold,italic,underline,strike|left,center,right,justify|link,unlink|maximize",
        emoticons: {
            dropdown: {
                ">:(": "emoticons/angry.png",
                ":aw:": "emoticons/aw.png",
                "8)": "emoticons/cool.png",
                ":D": "emoticons/ecstatic.png",
                ">:D": "emoticons/furious.png",
                ":O": "emoticons/gah.png",
                ":)": "emoticons/happy.png",
                "<3": "emoticons/heart.png",
                ":/": "emoticons/hm.png",
                ":3": "emoticons/kiss.png",
                ":|": "emoticons/meh.png",
                ":x": "emoticons/mmf.png",
                ":(": "emoticons/sad.png",
                ":P": "emoticons/tongue.png",
                ":o": "emoticons/what.png",
                ";)": "emoticons/wink.png"
            },
            hidden: {
                ">:[": "emoticons/angry.png",
                "8]": "emoticons/cool.png",
                "D:": "emoticons/gah.png",
                ":]": "emoticons/happy.png",
                ":\\": "emoticons/hm.png",
                "-.-": "emoticons/meh.png",
                "-_-": "emoticons/meh.png",
                ":X": "emoticons/mmf.png",
                ":[": "emoticons/sad.png",
                ":\'(": "emoticons/sad.png",
                ":\'[": "emoticons/sad.png",
                ":p": "emoticons/tongue.png",
                ":?": "emoticons/what.png",
                ";]": "emoticons/wink.png",
                ";D": "emoticons/wink.png"
            }
        }
    });

	// Users autocomplete
	if (usersAutocomplete) {
		usersAutocomplete.autocomplete({
			minLength: 3,
			source: function(request, response) {
				$.getJSON(Routing.generate('api_user_list'),
					{ name: request.term, token: _t },
					function(data) {
						var users = data.response.data;

						response($.map(users, function( item ) {
							return {
								label: item.firstName+' '+item.lastName,
								value: item.firstName+' '+item.lastName,
								user: item
							}
						}));
					});
			},
            select: function( event, ui ) {
                var input = $(event.target);

                input.attr('data-login', ui.item.user.login);
                input.attr('data-name', ui.item.user.fullName);
            }
		});

		if (usersAutocomplete.data("ui-autocomplete")) {
			usersAutocomplete.data("ui-autocomplete")._renderItem = function(ul, item) {
				return $("<li style=\"margin-bottom: 3px;\">")
					.append(
						"<a>" +
							"<img src=\" "+ item.user.image.custom + "\" style=\"float: left; max-height: 25px; max-width: 25px; margin-right: 5px;\" />" +
							"<span style=\"display: block; float: left; margin-top: 0;\">" + item.label + "</span>" +
							"<div style=\"clear: both;\"></div>" +
                        "</a>"
					)
					.appendTo(ul);
			};
		}
	}
});
