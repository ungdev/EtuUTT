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
$launch = DateTime::createFromFormat('d-m-Y H:i:s', '01-09-2013 00:00:00');
$now = new DateTime();

$acceptTesters = $launch->diff($now)->days > 50;