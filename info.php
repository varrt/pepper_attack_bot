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

$inventory = $client->getInventory();
echo "Rations: " . $inventory->getRation() . "\n";
echo "Potions: ". $inventory->getPotions()."\n";
echo "Stim: " . $inventory->getStim()."\n";
echo "Crows: " . $inventory->getCrowCnt()."\n";
echo "Beer tickets: " . $inventory->getBeerTickets()."\n";
$details = $client->getDetails();
echo "Free beers: " . $details->getFreeBeers() . "\n";

$tournaments = $client->currentSeason();
foreach ($tournaments as $tournament) {
    $rank = $client->getMyRank($tournament);
    echo "Tournament " . $tournament . " rank: ".$rank."\n";
}

echo "--------------------------------------------------------------------------------------\n";
echo "End at: " . date("Y-m-d H:i:s")."\n";
echo "--------------------------------------------------------------------------------------\n";
