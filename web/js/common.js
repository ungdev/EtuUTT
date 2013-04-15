
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

	$('#change-locale-link').click(function() {
		$('#change-locale-link').toggleClass('change-locale-link');
		$('#next-change-locale-link').toggleClass('next-change-locale-link');
		$('#change-locale').toggle();
		return false;
	});

	$('.userbox-link').click(function() {
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
		$('#menu-mobile').toggle();

		return false;
	});

	$('.change-locale').click(function() {
		$('#head-menu-list li').each(function() {
			if ($(this).hasClass('old-active')) {
				$(this).removeClass('old-active').addClass('active');
			}
		});

		$(this).removeClass('active');
		$('#menu-mobile').hide();

		$('#overlay').show();
		$('#change-locale-choices').show();

		return false;
	});

	$('body').click(function() {
		$('.userbox').removeClass('userbox-clicked');
		$('.userbox-menu').removeClass('userbox-menu-clicked');
		$('.userbox-menu').hide();

		$('#head-menu-list li').each(function() {
			if ($(this).hasClass('old-active')) {
				$(this).removeClass('old-active').addClass('active');
			}
		});

		$(this).removeClass('active');

		$('#overlay').hide();
		$('#menu-mobile').hide();
		$('#change-locale-choices').hide();
	});

	$(document).keypress(function(event) {
		if (event.keyCode == 27) {
			$('.userbox').removeClass('userbox-clicked');
			$('.userbox-menu').removeClass('userbox-menu-clicked');
			$('.userbox-menu').hide();

			$('#head-menu-list li').each(function() {
				if ($(this).hasClass('old-active')) {
					$(this).removeClass('old-active').addClass('active');
				}
			});

			$(this).removeClass('active');

			$('#overlay').hide();
			$('#menu-mobile').hide();
			$('#change-locale-choices').hide();
		}
	});

	$('.userbox a').click(function() {
		return true;
	});

	// Load Redactor
	$('.redactor').redactor({
		fixed: true,
		lang: 'fr',
		buttons: ['formatting', '|', 'bold', 'italic', 'deleted', '|',
			'unorderedlist', 'orderedlist', 'outdent', 'indent', '|',
			'image', 'video', 'file', 'table', 'link', '|',
			'fontcolor', 'backcolor', '|', 'alignment']
	});


	// Suscribe
	$('.subscription-subscribe').click(function() {
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
				$('#'+ id +'-subscribe').hide();
				$('#'+ id +'-unsubscribe').show();
			} else {
				$('#'+ id +'-subscribe').show();

				alert('Une erreur s\'est produite. Veuillez réessayer ou signaler le problème.');
			}
		});
	});


	// Unsuscribe
	$('.subscription-unsubscribe').click(function() {
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
				$('#'+ id +'-unsubscribe').hide();
				$('#'+ id +'-subscribe').show();
			} else {
				$('#'+ id +'-unsubscribe').show();

				alert('Une erreur s\'est produite. Veuillez réessayer ou signaler le problème.');
			}
		});
	});

	// Users autocomplete
	$('.user-autocomplete').
		autocomplete({
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
		})
		.data( "ui-autocomplete" )._renderItem = function(ul, item) {
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
});
