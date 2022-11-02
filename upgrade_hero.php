<?php
declare(strict_types=1);

use PepperAttackBot\Client;

require __DIR__ . '/vendor/autoload.php';

echo "--------------------------------------------------------------------------------------\n";
echo "Start at: " . date("Y-m-d H:i:s") . "\n";
echo "--------------------------------------------------------------------------------------\n";

if (!isset($argv[1])) {
    echo "You must provide an email as first argument.\n";
    exit;
}

if (!isset($argv[2])) {
    echo "You must provide a password as second argument\n";
    exit;
}

$account = $argv[1];

echo "Account: " . $account . ".\n";
$client = new Client();
$client->login($account, $argv[2]);
sleep(rand(1, 3));

/** @var \PepperAttackBot\Model\Pepper[] $heroes */
$heroes = $client->getPeppers();

$upgradedQueue = [];
foreach ($heroes as $hero) {
    if (!array_key_exists($hero->getType(), $upgradedQueue)) {
        $upgradedQueue[$hero->getType()] = $hero;
    } else {
        $upgradedQueue['Chilli2'] = $hero;
    }
}

$heroesStats = [
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
];

/**
 * @var string $name
 * @var \PepperAttackBot\Model\Pepper $hero
 */
foreach ($upgradedQueue as $name => $hero) {

    if ($hero->getBoostedCount() >= 90) {
        continue;
    }

    if (isset($heroesStats[$account][$name])) {
        echo "Upgrade: " . $name . "\n";
        foreach ($heroesStats[$name] as $state => $value) {
            if ($hero->getStat($state) >= $value) {
                continue;
            }
            $stimsLeft = 1000;
            while (true && $stimsLeft > 50) {
                $data = $client->useStim($hero->getId(), (string)$state);
                $stimsLeft = (int)$data['data']['stim']['quantity'];
                sleep(rand(1, 2));
                $boostedValue = (int)$data['data']['pepper']['boosted_' . $state];
                $hero->incrementBoostedCount();
                $hero->setStat($state, $boostedValue);
                echo "Upgrade: " . $state . " (" . $boostedValue . ") (stims: " . $stimsLeft . ")\n";
                if ($hero->getBoostedCount() >= 90 || ($value > 0 && $boostedValue >= $value)) {
                    break;
                }
            }
        }
    }
}

echo "--------------------------------------------------------------------------------------\n";
echo "End at: " . date("Y-m-d H:i:s") . "\n";
echo "--------------------------------------------------------------------------------------\n";
