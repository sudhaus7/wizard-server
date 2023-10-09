<?php

$path = realpath(__DIR__.'/../vendor/autoload.php');
require($path);

$server = new \Sudhaus7\WizardServer\Simple();
$server(realpath( __DIR__.'/../.env'));
