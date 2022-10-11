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


$quests = $client->getDailyQuests();

$toClaim = [];

foreach ($quests as $quest) {
    if($quest['isCompleted'] == 1 && $quest['isClaimed'] != 1) {
        $toClaim[] = $quest['id'];
    }
}

$client->claimDailyQuests($toClaim);

echo "--------------------------------------------------------------------------------------\n";
echo "End at: " . date("Y-m-d H:i:s")."\n";
echo "--------------------------------------------------------------------------------------\n";
