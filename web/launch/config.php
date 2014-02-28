<?php

// Share
$facebookUrl = 'http://www.facebook.com/sharer.php';
$facebookUrl .= '?u=' . urlencode('http://etu.utt.fr');
$facebookUrl .= '&t=' . urlencode('EtuUTT arrive bientôt ...');

$twitterUrl = 'https://twitter.com/share';
$twitterUrl .= '?url=' . urlencode('http://etu.utt.fr');
$twitterUrl .= '&text=' . urlencode('EtuUTT arrive bientôt ...');
$twitterUrl .= '&hashtags=EtuUTT,v10';

$mailto = 'mailto:?subject=EtuUTT arrive bientôt ...&body=http://etu.utt.fr';


// Launch date
$launch = mktime(16, 28, 0, 9, 12, 2013);
$now = time();

$acceptTesters = false;