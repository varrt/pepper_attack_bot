<?php
declare(strict_types=1);

use PepperAttackBot\AccountsReader;
use PepperAttackBot\Bot;

require __DIR__.'/vendor/autoload.php';

echo "--------------------------------------------------------------------------------------\n";
echo "Start at: " . date("Y-m-d H:i:s")."\n";
echo "--------------------------------------------------------------------------------------\n";

$accounts = new AccountsReader(__DIR__."/accounts.json");
if (isset($argv[1])) {
    $account = $accounts->getAccount($argv[1]);
    $accounts->setAccounts([$account]);
}

$stims = 1000;
if (isset($argv[2])) {
    $stims = (int)$argv[2];
}

foreach ($accounts->getAccounts() as $account) {
    $bot = new Bot($account);
    $inventory = $bot->getInventory();
    if ($account->getBoostsConfig()) {
        $bot->upgradeHeroDry($account->getBoostsConfig(), $inventory->getStim(), 8);
    } else {
        \PepperAttackBot\Writer::red("Config is null.");
    }

}

echo "--------------------------------------------------------------------------------------\n";
echo "End at: " . date("Y-m-d H:i:s")."\n";
echo "--------------------------------------------------------------------------------------\n";
