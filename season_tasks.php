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

    if ($inventory->getStim() >= 2000) {
        $bot->upgradeHero([
            'Ghost' => [
                'atk' => 150,
                'eva' => 90,
                'crit' => 50
            ],
            'Bell' => [
                'atk' => 40,
                'def' => 150,
                'vit' => 100
            ],
            'Chilli' => [
                'def' => 120,
                'atk' => 100
            ],
            'Chilli2' => [
                'def' => 120,
                'atk' => -1
            ]
        ]);
    } elseif ($inventory->getStim() >= 1000) {
        $bot->upgradeHero([
            'Ghost' => [
                'atk' => 100,
                'eva' => 90,
                'crit' => 30
            ],
            'Bell' => [
                'atk' => 40,
                'def' => 150,
                'vit' => 70
            ],
            'Chilli' => [
                'def' => 90,
                'atk' => 100
            ],
            'Chilli2' => [
                'def' => 90,
                'atk' => -1
            ]
        ]);
    } elseif ($inventory->getStim() >= 100) {
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
                'atk' => 90
            ],
            'Chilli2' => [
                'def' => 90,
                'atk' => -1
            ]
        ]);
    }
}

echo "--------------------------------------------------------------------------------------\n";
echo "End at: " . date("Y-m-d H:i:s")."\n";
echo "--------------------------------------------------------------------------------------\n";
