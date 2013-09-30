<?php

$_SERVER['REQUEST_URI'] = str_replace('/api', '', $_SERVER['REQUEST_URI']);

require __DIR__ . '/../../api/bootstrap.php';
