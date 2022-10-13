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


$quests = $client->getDailyQuests();

$toClaim = [];

foreach ($quests as $quest) {
    if($quest['isCompleted'] == 1 && $quest['isClaimed'] != 1) {
        echo "Claim quest: ". $quest['id'] . "\n";
        $toClaim[] = $quest['id'];
    }
}

$client->claimDailyQuests($toClaim);

for($i=1;$i<=4;$i++) {
    echo "Claim reward: ". $i . "\n";
    $client->claimRewards($i);
}

echo "--------------------------------------------------------------------------------------\n";
echo "End at: " . date("Y-m-d H:i:s")."\n";
echo "--------------------------------------------------------------------------------------\n";
