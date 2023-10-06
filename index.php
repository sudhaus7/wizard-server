<?php

require 'vendor/autoload.php';

$server = new \Sudhaus7\WizardServer\Server();
$server(__DIR__.'/.env');
