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

$heroes = $client->getPeppers();

$usedPositions = [];
function getPepperPosition(string $type, array &$usedPositions) {
    switch ($type) {
        case 'Ghost':
            $usedPositions[] = 4;
            return 4;
        case 'Chilli':
            $position = in_array(5, $usedPositions) ? 2 : 5;
            $usedPositions[] = $position;
            return $position;
        case 'Bell':
            $usedPositions[] = 6;
            return 6;
    }
    return 1;
}

$positions = array_map(function (\PepperAttackBot\Model\Pepper $pepper) use (&$usedPositions) {
    return [
        'pos' => getPepperPosition($pepper->getType(), $usedPositions),
        'id' => $pepper->getId()
    ];
}, $heroes);


echo "Set up as defenders\n";
$client->setUpTeamPvP([
    'pepper_positions' => $positions,
    'type' => 1
]);

sleep(rand(1,2));
echo "Set up as fighters\n";
$client->setUpTeamPvP([
    'pepper_positions' => $positions,
    'type' => 0
]);

echo "--------------------------------------------------------------------------------------\n";
echo "End at: " . date("Y-m-d H:i:s")."\n";
echo "--------------------------------------------------------------------------------------\n";
