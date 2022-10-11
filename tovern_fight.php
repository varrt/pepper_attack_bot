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

$client = new Client();
$client->login($argv[1], $argv[2]);
sleep(rand(1,3));

$details = $client->getDetails();
echo "Free beers: " . $details->getFreeBeers() . "\n";

$leftBeers = $details->getFreeBeers();

while ($leftBeers > 0) {
    echo "Find new match.\n";
    $client->findMatch();
    sleep(rand(1,3));
    $battleResult = $client->battlePvP();
    $actions = count($battleResult['data']['combatActions']);
    $leftBeers = (int)$battleResult['data']['numFreeBeers'];
    echo "Left beers: " .$leftBeers. ". Waiting: ".($actions * 4)."s.\n";
    sleep($actions * 4);
}


echo "--------------------------------------------------------------------------------------\n";
echo "End at: " . date("Y-m-d H:i:s")."\n";
echo "--------------------------------------------------------------------------------------\n";
