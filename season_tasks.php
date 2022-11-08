<?php
declare(strict_types=1);

use PepperAttackBot\AccountsReader;
use PepperAttackBot\Bot;

require __DIR__.'/vendor/autoload.php';

echo "--------------------------------------------------------------------------------------\n";
echo "Start at: " . date("Y-m-d H:i:s")."\n";
echo "--------------------------------------------------------------------------------------\n";

$accounts = new AccountsReader(__DIR__."/accounts.json");
foreach ($accounts->getAccounts() as $account) {
    $bot = new Bot($account);
    $bot->setupTeamPvP();

    $inventory = $bot->getInventory();

    if ($account->getBoostsConfig()) {
        $bot->upgradeHero($account->getBoostsConfig(), 8);
    }
}

echo "--------------------------------------------------------------------------------------\n";
echo "End at: " . date("Y-m-d H:i:s")."\n";
echo "--------------------------------------------------------------------------------------\n";
