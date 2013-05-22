
var courses = $('.cumul-student-course');

$('.cumul-student-name').hover(function() {
	courses.css('opacity', 0.3);
	$('.cumul-student-course-'+$(this).attr('id')).css({
		'opacity': 1,
		'-moz-box-shadow': '0 0 5px #888',
		'-webkit-box-shadow': '0 0 5px #888',
		'box-shadow': '0 0 5px #888'
	});
}, function() {
	courses.css({
		'opacity': 1,
		'-moz-box-shadow': 'none',
		'-webkit-box-shadow': 'none',
		'box-shadow': 'none'
	});
});