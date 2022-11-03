<?php
declare(strict_types=1);

namespace PepperAttackBot;

use PepperAttackBot\Model\Account;
use PepperAttackBot\Model\Pepper;
use PepperAttackBot\Model\Inventory;

class Bot
{
    private Client $client;

    public function __construct(
        private Account $account
    )
    {
        Writer::blue("Account: %s.", $this->account->getEmail());
        $this->client = new Client();
        $this->client->login($account->getEmail(), $account->getPassword());
        $this->wait();
    }

    public function info(): void
    {
        $inventory = $this->client->getInventory();
        $details = $this->client->getDetails();
        Writer::white("Rations: %d", $inventory->getRation());
        if ($inventory->getPotions() > 40) {
            Writer::green("Potions: %d", $inventory->getPotions());
        } else {
            Writer::red("Potions: %d", $inventory->getPotions());
        }
        Writer::white("Stim: %d", $inventory->getStim());
        Writer::white("Crows: %d", $inventory->getCrowCnt());
        Writer::white("Beer tickets: %d", $inventory->getBeerTickets());
        Writer::white("Free beers: %d", $details->getFreeBeers());

        $tournaments = $this->client->currentSeason();
        foreach ($tournaments as $tournament) {
            $rank = $this->client->getMyRank($tournament);
            Writer::white("Tournament %s rank: %d", $tournament, $rank);
        }
    }

    public function admire(): void
    {
        Writer::white("Admire potions.");
        $tournamentsIds = $this->client->currentSeason();
        foreach ($tournamentsIds as $tournamentsId) {
            $this->client->admireTournament($tournamentsId);
        }
    }

    public function treasureHuntRoll(): void
    {
        $inventory = $this->client->getInventory();
        while ($inventory->getCrowCnt() > 0) {
            $roll = $this->client->treasureHuntRoll();
            if (isset($roll['data']['nextPos']['qty'])) {
                Writer::green("You won x%d %s", $roll['data']['nextPos']['qty'], $roll['data']['nextPos']['code']);
            } else {
                Writer::red("You roll.");
            }
            $inventory->consumeCrow();
            $this->wait();
        }
    }

    public function dailyQuests(): void
    {
        Writer::yellow("Claim quests!");
        $quests = $this->client->getDailyQuests();
        $toClaim = [];
        foreach ($quests as $quest) {
            if($quest['isCompleted'] == 1 && $quest['isClaimed'] != 1) {
                $toClaim[] = $quest['id'];
            }
        }

        $this->client->claimDailyQuests($toClaim);

        for($i=1;$i<=4;$i++) {
            $this->client->claimRewards($i);
        }
    }

    public function collectRotions(): void
    {
        Writer::white("Collect ration.");
        $this->client->collectRation();
    }

    public function setupTeamPvP(): void
    {
        $heroes = $this->client->getPeppers();
        $usedPositions = [];
        $positions = array_map(function (Pepper $pepper) use (&$usedPositions) {
            return [
                'pos' => $this->getPepperPosition($pepper->getType(), $usedPositions),
                'id' => $pepper->getId()
            ];
        }, $heroes);


        Writer::magenta("Set up as defenders.");
        $this->client->setUpTeamPvP([
            'pepper_positions' => $positions,
            'type' => 1
        ]);

        $this->wait();

        Writer::yellow("Set up as fighters.");
        $this->client->setUpTeamPvP([
            'pepper_positions' => $positions,
            'type' => 0
        ]);
    }

    public function setupTeamPvE(): void
    {
        $heroes = $this->client->getPeppers();
        $usedPositions = [];
        $positions = array_map(function (Pepper $pepper) use (&$usedPositions) {
            return [
                'pos' => $this->getPepperPosition($pepper->getType(), $usedPositions),
                'id' => $pepper->getId()
            ];
        }, $heroes);


        Writer::magenta("Set up adventure team.");
        $this->client->setUpTeamPvE([
            'pepper_positions' => $positions
        ]);
    }

    private function getPepperPosition(string $type, array &$usedPositions): int {
        switch ($type) {
            case 'Ghost':
                $usedPositions[] = 1;
                return 1;
            case 'Chilli':
                $position = in_array(2, $usedPositions) ? 5 : 2;
                $usedPositions[] = $position;
                return $position;
            case 'Bell':
                $usedPositions[] = 3;
                return 3;
        }
        return 4;
    }

    private function wait(): void
    {
        sleep(rand(1,2));
    }

    public function upgradeHero(array $heroesStats, int $minStims = 50): void
    {
        /** @var \PepperAttackBot\Model\Pepper[] $heroes */
        $heroes = $this->client->getPeppers();

        $upgradedQueue = [];
        foreach ($heroes as $hero) {
            if (!array_key_exists($hero->getType(), $upgradedQueue)) {
                $upgradedQueue[$hero->getType()] = $hero;
            } else {
                $upgradedQueue['Chilli2'] = $hero;
            }
        }

        /**
         * @var string $name
         * @var \PepperAttackBot\Model\Pepper $hero
         */
        foreach ($upgradedQueue as $name => $hero) {
            if ($hero->getBoostedCount() >= 90) {
                continue;
            }

            if (isset($heroesStats[$name])) {
                Writer::green("Upgrade: %s", $name);
                foreach ($heroesStats[$name] as $state => $value) {
                    if ($value > 0 && $hero->getStat($state) >= $value) {
                        continue;
                    }
                    $stimsLeft = 1000;
                    while ($stimsLeft >= $minStims) {
                        $data = $this->client->useStim($hero->getId(), (string)$state);
                        $stimsLeft = (int)$data['data']['stim']['quantity'];
                        $this->wait();
                        $boostedValue = (int)$data['data']['pepper']['boosted_' . $state];
                        $hero->incrementBoostedCount();
                        $hero->setStat($state, $boostedValue);
                        Writer::blue("Upgrade: %s (%s) (stims: %s)", $state, $boostedValue, $stimsLeft);
                        if ($hero->getBoostedCount() >= 90 || ($value > 0 && $boostedValue >= $value)) {
                            break;
                        }
                    }
                }
            }
        }
    }

    public function checkRations(int $minRations): bool
    {
        $inventory = $this->client->getInventory();
        Writer::white("Rations: %d", $inventory->getRation());
        return $inventory->getRation() >= $minRations;
    }

    public function singlePvEBattle(): bool
    {
        $inventory = $this->client->getInventory();
        $this->healPeppers($inventory, 30, false);
        $battleResult = $this->client->battlePvE($this->account->getMap(), $this->account->getStage());
        $actions = count($battleResult['data']['combatActions']);
        $inventory->consumeRation((int)$battleResult['data']['rationCost']);

        if ((int)$battleResult['data']['totalExp'] > 0) {
            Writer::green("Win (%d EXP).", (int)$battleResult['data']['totalExp']);
            $isWin = true;
        } else {
            Writer::red("Lost!");
            $isWin = false;
        }

        $rewards = $battleResult['data']['rewards'];
        foreach ($rewards as $reward) {
            if ($reward['code'] == 'stim') {
                Writer::magenta("You got x%d Stims.", (int)$reward['value']);
            }
        }

        if ($inventory->getRation() >= 100) {
            sleep(rand(4,5));
        }

        return $isWin;
    }

    public function battlePvE($info = true): bool
    {
        $inventory = $this->client->getInventory();
        $this->healPeppers($inventory, 30, $info);

        $isWin = false;
        while($inventory->getRation() >= 100) {
            $battleResult = $this->client->battlePvE($this->account->getMap(), $this->account->getStage());

            $actions = count($battleResult['data']['combatActions']);
            $inventory->consumeRation((int)$battleResult['data']['rationCost']);

            if ((int)$battleResult['data']['totalExp'] > 0) {
                $isWin = true;
                Writer::green("Win (%d EXP).", (int)$battleResult['data']['totalExp']);
            } else {
                Writer::red("Lost!");
            }

            $rewards = $battleResult['data']['rewards'];
            foreach ($rewards as $reward) {
                if ($reward['code'] == 'hp_potion') {
                    $inventory->addPotions((int)$reward['value']);
                    !$info ?? Writer::yellow("You got x%d Heal Potions.", (int)$reward['value']);
                }
                if ($reward['code'] == 'stim') {
                    !$info ?? Writer::magenta("You got x%d Stims.", (int)$reward['value']);
                }
            }

            $this->healPeppers($inventory, 30, $info);

            if ($inventory->getRation() >= 100) {
                Writer::white("Left rations: %d. Waiting: %ds.", $inventory->getRation(), $actions * 4);
                sleep($actions * 4);
            }
        }

        return $isWin;
    }

    private function healPeppers(Inventory $inventory, int $defaultHealPointsLeft = 30, bool $debug = true): void {
        if ($inventory->getPotions() == 0) {
            !$debug ?? Writer::red("Not enough potions");
            return;
        }

        /** @var \PepperAttackBot\Model\Pepper[] $peppers */
        $peppers = $this->client->getPeppers();

        foreach ($peppers as $pepper) {
            $healedTimes = 0;
            while ($pepper->getMaxHP() - $pepper->getCurrentHP() >= $defaultHealPointsLeft) {
                $isHealed = $this->client->healPepper($pepper->getId());
                $healedTimes++;
                if ($isHealed) {
                    !$debug ?? Writer::green("Heal pepper %s (%d/%d HP)", $pepper->getId(), min($pepper->getCurrentHP()+100, $pepper->getMaxHP()), $pepper->getMaxHP());
                    $pepper->heal();
                    $inventory->usePotion();
                }

                if ($healedTimes > 10) {
                    break;
                }
                $this->wait();
            }
            $this->wait();
        }
        !$debug ?? Writer::white("Left potions %d", $inventory->getPotions());
    }

    public function battlePvP(): void
    {
        $details = $this->client->getDetails();
        $inventory = $this->client->getInventory();
        Writer::white("Free beers: %d", $details->getFreeBeers());
        Writer::white("Beers tickets: %d", $inventory->getBeerTickets());
        $leftBeers = $details->getFreeBeers() + $inventory->getBeerTickets();

        while ($leftBeers > 0) {
            $matchId = $this->client->findMatch();
            if (!$matchId) {
                Writer::red("Limit Exceeded!");
                break;
            }
            $this->wait();
            $battleResult = $this->client->battlePvP();
            $actions = count($battleResult['data']['combatActions']);
            $this->wait();

            $inventory = $this->client->getInventory();
            $leftBeers = (int)$battleResult['data']['numFreeBeers'] + $inventory->getBeerTickets();

            if ($leftBeers > 0) {
                Writer::white("Left beers: %d. Waiting: %ds.", $leftBeers, $actions * 4);
                sleep($actions * 4);
            }
        }

    }

    public function getInventory(): Inventory
    {
        return $this->client->getInventory();
    }
}
