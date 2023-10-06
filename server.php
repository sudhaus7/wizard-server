<?php

use Sudhaus7\WizardServer\Server;

require 'vendor/autoload.php';

$server = new Server();
$server(__DIR__.'/.env');
