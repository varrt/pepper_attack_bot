<?php
declare(strict_types=1);

use PepperAttackBot\Client;

require __DIR__.'/vendor/autoload.php';

if (!isset($argv[1])) {
    echo "You must provide an email as first argument.\n";
}

if (!isset($argv[2])) {
    echo "You must provide a password as second argument\n";
}

$client = new Client();
$client->login($argv[1], $argv[2]);
echo "Collect ration start\n";
$client->collectRation();
echo "Collect ration end\n";

