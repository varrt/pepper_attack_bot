<?php
declare(strict_types=1);

use PepperAttackBot\Writer;
use PepperAttackBot\AccountsReader;
use PepperAttackBot\Bot;
use PepperAttackBot\Model\Account;

require __DIR__.'/vendor/autoload.php';

echo "--------------------------------------------------------------------------------------\n";
echo "Start at: " . date("Y-m-d H:i:s")."\n";
echo "--------------------------------------------------------------------------------------\n";


$account = new Account($argv[1], $argv[2], 1, 1);
$account->setStage((int)$argv[3]);

$bot = new Bot($account);
$bot->admire();
$bot->setupTeamPvE();

// reach 11 stage
$lostCounter = 0;
while ($account->getStage() <= 11) {
    Writer::white("Run stage %d", $account->getStage());
    $result = $bot->singlePvEBattle();
    if ($result) {
        $account->setStage($account->getStage() + 1);
        $lostCounter = 0;
    } else {
        $lostCounter++;
    }

    if ($lostCounter >= 3) {
        Writer::red("Lost %d battles. (stage %d)", $lostCounter, $account->getStage());
    }
}
Writer::green("Reach 11 stage.");

$inventory = $bot->getInventory();

Writer::blue("Stims: %d", $inventory->getStim());

$account->setStage(11);
while ($inventory->getStim() < 50) {
    Writer::white("Run stage %d", $account->getStage());
    $result = $bot->singlePvEBattle();
    if ($result) {
        $inventory = $bot->getInventory();
    }
}
Writer::green("Collect more than 50 Stims.");

Writer::yellow("Upgrade heroes");
$bot->upgradeHero([
    'Ghost' => [
        'atk' => 72
    ],
    'Bell' => [
        'def' => 120,
        'vit' => 30
    ],
    'Chilli' => [
        'def' => 30,
        'atk' => -1
    ]
], 2);


// reach 16 stage
$lostCounter = 0;
while ($account->getStage() <= 16) {
    Writer::white("Run stage %d", $account->getStage());
    $result = $bot->singlePvEBattle();
    if ($result) {
        $account->setStage($account->getStage() + 1);
        $lostCounter = 0;
    } else {
        $lostCounter++;
    }

    if ($lostCounter >= 3) {
        Writer::red("Lost %d battles. (stage %d)", $lostCounter, $account->getStage());
    }
}
Writer::green("Reach 16 stage.");

$bot->setupTeamPvP();

echo "--------------------------------------------------------------------------------------\n";
echo "End at: " . date("Y-m-d H:i:s")."\n";
echo "--------------------------------------------------------------------------------------\n";
