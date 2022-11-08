<?php
declare(strict_types=1);

use PepperAttackBot\Writer;
use PepperAttackBot\AccountsReader;
use PepperAttackBot\Bot;

require __DIR__.'/vendor/autoload.php';

echo "--------------------------------------------------------------------------------------\n";
echo "Start at: " . date("Y-m-d H:i:s")."\n";
echo "--------------------------------------------------------------------------------------\n";

$minRations = 4000;
if (isset($argv[1])) {
    $minRations = (int)$argv[1];
    Writer::magenta("Minimum rations set to %d", $minRations);
}

$accounts = new AccountsReader(__DIR__."/accounts.json");
if (isset($argv[2])) {
    $account = $accounts->getAccount($argv[2]);
    if (isset($argv[3]) && (int)$argv[3] > 0) {
        $account->setStage((int)$argv[3]);
    }
    $accounts->setAccounts([$account]);
}

foreach ($accounts->getAccounts() as $account) {
    $bot = new Bot($account);

    if ($bot->getInventory()->getPotions() <= 30) {
        Writer::red("Too low potions");
    }

    if (!$bot->checkRations($minRations)) {
        Writer::red("Waiting for more rations");
        continue;
    }

    $bot->battlePvE();
}

echo "--------------------------------------------------------------------------------------\n";
echo "End at: " . date("Y-m-d H:i:s")."\n";
echo "--------------------------------------------------------------------------------------\n";
