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

$details = $client->getDetails();
$inventory = $client->getInventory();
echo "Free beers: " . $details->getFreeBeers() . "\n";
echo "Beers tickets: " . $inventory->getBeerTickets() . "\n";

$leftBeers = $details->getFreeBeers() + $inventory->getBeerTickets();

while ($leftBeers > 0) {
    echo "Find new match.\n";
    $matchId = $client->findMatch();
    if (!$matchId) {
        echo "Limit Exceeded.\n";
        break;
    }
    sleep(rand(1,3));
    $battleResult = $client->battlePvP();
    $actions = count($battleResult['data']['combatActions']);
    sleep(rand(1,3));
    $inventory = $client->getInventory();
    $leftBeers = (int)$battleResult['data']['numFreeBeers'] + $inventory->getBeerTickets();
    echo "Left beers: " .$leftBeers. ". Waiting: ".($actions * 4)."s.\n";
    sleep($actions * 4);
}


echo "--------------------------------------------------------------------------------------\n";
echo "End at: " . date("Y-m-d H:i:s")."\n";
echo "--------------------------------------------------------------------------------------\n";
