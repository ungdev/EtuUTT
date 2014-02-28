
$('#1').show();

setTimeout(function() {
	$('#1').fadeOut(800, function() {
		$('#2').fadeIn(800);
	})
}, 2500);

setTimeout(function() {
	$('#2').fadeOut(800, function() {
		$('#3').fadeIn(800);
	})
}, 5800);

setTimeout(function() {
	$('#3').fadeOut(800, function() {
		$('#4').fadeIn(800);
	})
}, 9100);

setTimeout(function() {
	$('#4').fadeOut(800, function() {
		$('#5').fadeIn(800);
	})
}, 12400);