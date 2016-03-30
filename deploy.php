<?php

// Under WTFPL license.
// Thanks @Punkeel :^)

file_put_contents('log.log', var_export($_SERVER, true));
file_put_contents('post.log', var_export($_POST, true));

$travis_token = '…';
$hook_url = 'https://hooks.slack.com/services/…/…;

$repository = $_SERVER['HTTP_TRAVIS_REPO_SLUG'] ?? '';
$authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

if(empty($repository) || empty($authorization) || $repository != 'ungdev/EtuUTT' || ! hash_equals(hash('sha256', $repository.$travis_token), $authorization)){
	exit;
}

chdir('/var/www/EtuUTT');

$data = json_decode($_POST['payload'], true);

if($data['branch'] != 'master')
	exit;

$commit = $data['commit'];

function tell_slack($step, $error, $color, $pretext = ''){
	global $hook_url;
	$payload = ['attachments' => [[ 'color' => $color, 'title' => $step, 'text' => $error, 'pretext' => $pretext]]];
	$ch = curl_init($hook_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	curl_exec($ch);
}

touch('web/.maintenance');

$output = [];
function run($command){
	global $output;
	$output[] = '> ' . $command;
	exec($command, $output, $ret);
	if($ret != 0){
		tell_slack('Error on "'.$command.'"', implode(PHP_EOL, $output), 'danger');
	}
	$output[] = '';
}

run('git fetch 2>&1');
run('git checkout '.$commit.' 2>&1');

run('php app/console cache:clear --env=prod --no-debug');
run('php app/console cache:warmup --env=prod --no-debug');
run('php app/console assetic:dump --env=prod --no-debug');

unlink('web/.maintenance');

tell_slack('New version deployed !', implode(PHP_EOL, $output), 'good', 'Deployed commit '.$commit);
