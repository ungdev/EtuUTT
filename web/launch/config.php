<?php

// Share
$facebookUrl = 'http://www.facebook.com/sharer.php';
$facebookUrl .= '?u=' . urlencode('http://openutt.utt.fr/launch/index.php');
$facebookUrl .= '&t=' . urlencode('EtuUTT arrive bientôt ...');

$twitterUrl = 'https://twitter.com/share';
$twitterUrl .= '?url=' . urlencode('http://openutt.utt.fr/launch/index.php');
$twitterUrl .= '&text=' . urlencode('EtuUTT arrive bientôt ...');
$twitterUrl .= '&hashtags=EtuUTT,v10';

$mailto = 'mailto:?subject=EtuUTT arrive bientôt ...&body=http://openutt.utt.fr/launch/index.php';


// Launch date
$launch = mktime(20, 0, 0, 9, 12, 2013);
$now = time();

$acceptTesters = (($now - $launch) / (24 * 3600)) > 31;