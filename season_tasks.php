<?php
declare(strict_types=1);

use PepperAttackBot\AccountsReader;
use PepperAttackBot\Bot;
use PepperAttackBot\Writer;

require __DIR__.'/vendor/autoload.php';

echo "--------------------------------------------------------------------------------------\n";
echo "Start at: " . date("Y-m-d H:i:s")."\n";
echo "--------------------------------------------------------------------------------------\n";

$accounts = new AccountsReader(__DIR__."/accounts.json");

if (isset($argv[1])) {
    $account = $accounts->getAccount($argv[1]);
    $accounts->setAccounts([$account]);
}

foreach ($accounts->getAccounts() as $account) {
    try {
        $bot = new Bot($account);
        $bot->setupTeamPvP();

        $inventory = $bot->getInventory();

        if ($account->getBoostsConfig()) {
            $bot->upgradeHero($account->getBoostsConfig(), 8);
        }
    } catch (Exception $e) {
        Writer::red("Exception %s", $e->getMessage());
        continue;
    }

}

echo "--------------------------------------------------------------------------------------\n";
echo "End at: " . date("Y-m-d H:i:s")."\n";
echo "--------------------------------------------------------------------------------------\n";
