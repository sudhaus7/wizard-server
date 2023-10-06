<?php

use Sudhaus7\WizardServer\Simple;

require '../vendor/autoload.php';

$server = new Simple();
$server(realpath( __DIR__.'/../.env'));
