
/*
 * Add CSS on page load to display fonts
 */
var fontsCss = $('<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Source+Sans+Pro" type="text/css" />'),
	head = $('head');

$(function() {
	setTimeout(function() { head.append(fontsCss); });
});


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
        style: "/vendor/SCEditor/minified/jquery.sceditor.default.min.css",
        emoticonsRoot: '/',
        toolbar:
            "source|bold,italic,underline,strike,subscript,superscript|left,center,right,justify" +
                "|font,size,removeformat|bulletlist,orderedlist" +
                "|table|quote|link,unlink|image,youtube|maximize",
        emoticons: {
            dropdown: {
                ">:(": "src/img/emoticons/angry.png",
                ":aw:": "src/img/emoticons/aw.png",
                "8)": "src/img/emoticons/cool.png",
                ":D": "src/img/emoticons/ecstatic.png",
                ">:D": "src/img/emoticons/furious.png",
                ":O": "src/img/emoticons/gah.png",
                ":)": "src/img/emoticons/happy.png",
                "<3": "src/img/emoticons/heart.png",
                ":/": "src/img/emoticons/hm.png",
                ":3": "src/img/emoticons/kiss.png",
                ":|": "src/img/emoticons/meh.png",
                ":x": "src/img/emoticons/mmf.png",
                ":(": "src/img/emoticons/sad.png",
                ":P": "src/img/emoticons/tongue.png",
                ":o": "src/img/emoticons/what.png",
                ";)": "src/img/emoticons/wink.png"
            },
            hidden: {
                ">:[": "src/img/emoticons/angry.png",
                "8]": "src/img/emoticons/cool.png",
                "D:": "src/img/emoticons/gah.png",
                ":]": "src/img/emoticons/happy.png",
                ":\\": "src/img/emoticons/hm.png",
                "-.-": "src/img/emoticons/meh.png",
                "-_-": "src/img/emoticons/meh.png",
                ":X": "src/img/emoticons/mmf.png",
                ":[": "src/img/emoticons/sad.png",
                ":\'(": "src/img/emoticons/sad.png",
                ":\'[": "src/img/emoticons/sad.png",
                ":p": "src/img/emoticons/tongue.png",
                ":?": "src/img/emoticons/what.png",
                ";]": "src/img/emoticons/wink.png",
                ";D": "src/img/emoticons/wink.png"
            }
        }
    });

    // Load SCeditor limited
    sceditorLimited.sceditor({
        plugins: "bbcode",
        style: "/vendor/SCEditor/minified/jquery.sceditor.default.min.css",
        emoticonsRoot: '/',
        toolbar:
            "source|bold,italic,underline,strike|left,center,right,justify|link,unlink|maximize",
        emoticons: {
            dropdown: {
                ">:(": "src/img/emoticons/angry.png",
                ":aw:": "src/img/emoticons/aw.png",
                "8)": "src/img/emoticons/cool.png",
                ":D": "src/img/emoticons/ecstatic.png",
                ">:D": "src/img/emoticons/furious.png",
                ":O": "src/img/emoticons/gah.png",
                ":)": "src/img/emoticons/happy.png",
                "<3": "src/img/emoticons/heart.png",
                ":/": "src/img/emoticons/hm.png",
                ":3": "src/img/emoticons/kiss.png",
                ":|": "src/img/emoticons/meh.png",
                ":x": "src/img/emoticons/mmf.png",
                ":(": "src/img/emoticons/sad.png",
                ":P": "src/img/emoticons/tongue.png",
                ":o": "src/img/emoticons/what.png",
                ";)": "src/img/emoticons/wink.png"
            },
            hidden: {
                ">:[": "src/img/emoticons/angry.png",
                "8]": "src/img/emoticons/cool.png",
                "D:": "src/img/emoticons/gah.png",
                ":]": "src/img/emoticons/happy.png",
                ":\\": "src/img/emoticons/hm.png",
                "-.-": "src/img/emoticons/meh.png",
                "-_-": "src/img/emoticons/meh.png",
                ":X": "src/img/emoticons/mmf.png",
                ":[": "src/img/emoticons/sad.png",
                ":\'(": "src/img/emoticons/sad.png",
                ":\'[": "src/img/emoticons/sad.png",
                ":p": "src/img/emoticons/tongue.png",
                ":?": "src/img/emoticons/what.png",
                ";]": "src/img/emoticons/wink.png",
                ";D": "src/img/emoticons/wink.png"
            }
        }
    });

	// Users autocomplete
	if (usersAutocomplete) {
		usersAutocomplete.autocomplete({
			minLength: 3,
			source: function(request, response) {
				$.getJSON(
                    Routing.generate('user_ajax_search'),
                    {
                        term: request.term
                    },
					function(data)
                    {
						var users = data.response.users;

						response($.map(users, function(item) {
							return {
								label: item.firstName + ' ' + item.lastName,
								value: item.firstName + ' ' + item.lastName,
								user: item
							}
						}));
					}
                );
			},
            select: function( event, ui ) {
                var input = $(event.target);

                input.attr('data-login', ui.item.user.login);
                input.attr('data-name', ui.item.user.fullName);
            }
		});

		if (usersAutocomplete.data("ui-autocomplete")) {
			usersAutocomplete.data("ui-autocomplete")._renderItem = function(ul, item) {
                var imageLink, link;

                for (var i = 0; i < item.user._links.length; i++) {
                    link = item.user._links[i];

                    if (link.rel == 'user.image') {
                        imageLink = link;
                    }
                }

				return $("<li style=\"margin-bottom: 3px;\">")
					.append(
						"<a>" +
							"<img src=\" "+ imageLink.uri + "\" style=\"float: left; max-height: 25px; max-width: 25px; margin-right: 5px;\" />" +
							"<span style=\"display: block; float: left; margin-top: 0;\">" + item.label + "</span>" +
							"<div style=\"clear: both;\"></div>" +
                        "</a>"
					)
					.appendTo(ul);
			};
		}
	}
});
