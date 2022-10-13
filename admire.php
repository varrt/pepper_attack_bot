<?php
declare(strict_types=1);

use PepperAttackBot\Client;

require __DIR__.'/vendor/autoload.php';

echo "--------------------------------------------------------------------------------------\n";
echo "Start at: " . date("Y-m-d H:i:s")."\n";
echo "--------------------------------------------------------------------------------------\n";

if (!isset($argv[1])) {
    echo "You must provide an email as first argument.\n";
    exit;
}

if (!isset($argv[2])) {
    echo "You must provide a password as second argument\n";
    exit;
}

echo "Account: ". $argv[1].".\n";

$client = new Client();
$client->login($argv[1], $argv[2]);
sleep(rand(1,3));

$tournamentsIds = $client->currentSeason();
foreach ($tournamentsIds as $tournamentsId) {
    echo "Admire for tournament: " . $tournamentsId . "\n";
    $client->admireTournament($tournamentsId);
}

echo "--------------------------------------------------------------------------------------\n";
echo "End at: " . date("Y-m-d H:i:s")."\n";
echo "--------------------------------------------------------------------------------------\n";
