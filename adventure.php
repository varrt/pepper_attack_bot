<?php
declare(strict_types=1);

use PepperAttackBot\Client;
use PepperAttackBot\Model\Inventory;

require __DIR__.'/vendor/autoload.php';

if (!isset($argv[1])) {
    echo "You must provide an email as first argument.\n";
    exit;
}

if (!isset($argv[2])) {
    echo "You must provide a password as second argument\n";
    exit;
}

if (!isset($argv[3])) {
    echo "You must provide a map id\n";
    exit;
}

if (!isset($argv[4])) {
    echo "You must provide a stage id\n";
    exit;
}

$client = new Client();
$client->login($argv[1], $argv[2]);
sleep(rand(1,3));

$inventory = $client->getInventory();
echo "Rations: " . $inventory->getRation() . "\n";

function healPeppers(Client $client, Inventory $inventory) {

    /** @var \PepperAttackBot\Model\Pepper[] $peppers */
    $peppers = $client->getPeppers();

    if ($inventory->getPotions() == 0) {
        echo "Not enough potions".
        exit;
    }

    foreach ($peppers as $pepper) {
        $healedTimes = 0;
        while ($pepper->getMaxHP() - $pepper->getCurrentHP() > 50) {
            $isHealed = $client->healPepper($pepper->getId());
            if ($isHealed) {
                echo "Healed pepper ". $pepper->getId()."(". ($pepper->getCurrentHP()+100). "/" .$pepper->getMaxHP()."HP)\n";
                $healedTimes++;
                $pepper->heal();
                $inventory->usePotion();
            }

            if ($healedTimes > 5) {
                break;
            }
            sleep(1);
        }
        sleep(1);
    }

    echo "Left potions: ". $inventory->getPotions()."\n";
}

echo "Heal peppers:\n";
healPeppers($client, $inventory);

$mapId = (int)$argv[3];
$stageId = (int)$argv[4];
while($inventory->getRation() >= 100) {
    echo "Battle: ". $mapId . " - " . $stageId . "\n";
    $battleResult = $client->battlePvE($mapId, $stageId);

    $actions = count($battleResult['data']['combatActions']);
    $inventory->consumeRation((int)$battleResult['data']['rationCost']);

    if ((int)$battleResult['data']['totalExp'] > 0) {
        echo "Win! (".(int)$battleResult['data']['totalExp']."EXP)\n";
    }

    $rewards = $battleResult['data']['rewards'];
    foreach ($rewards as $reward) {
        if ($reward['code'] == 'hp_potion') {
            $inventory->addPotions((int)$reward['value']);
        }
    }

    healPeppers($client, $inventory);

    echo "Left rations: " .$inventory->getRation(). ". Waiting: ".($actions * 4)."s.\n";
    sleep($actions * 4);
}
