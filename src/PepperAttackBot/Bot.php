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
        Writer::yellow("Account: %s.", $this->account->getEmail());
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
            $rank = $this->client->getMyRank($tournament['id']);
            $myte = 0;
            if ($tournament['name'] !== 'Boosters') {
                $myte = $this->getMYTEByRank($rank);
            }
            Writer::white("Tournament %s rank: %d (MYTE: %d)", $tournament['name'], $rank, $myte);
        }

        $balance = $this->client->getBalance();
        Writer::magenta("Balance: %d", $balance);
    }

    private function getMYTEByRank(int $rank): int
    {
        try {
            return match(true) {
                $rank === 1 => 150000,
                $rank === 2 => 100000,
                $rank === 3 => 70000,
                $rank >= 4 && $rank <= 5 => 50000,
                $rank >= 6 && $rank <= 10 => 25000,
                $rank >= 11 && $rank <= 20 => 15000,
                $rank >= 21 && $rank <= 50 => 10000,
                $rank >= 51 && $rank <= 100 => 5000,
                $rank >= 101 && $rank <= 200 => 1000,
                $rank >= 201 && $rank <= 300 => 500,
                $rank >= 301 && $rank <= 500 => 100,
                $rank >= 501 && $rank <= 1000 => 50
            };
        } catch (\UnhandledMatchError $e) {
            return 0;
        }

    }

    public function admire(): void
    {
        Writer::white("Admire potions.");
        $tournaments = $this->client->currentSeason();
        foreach ($tournaments as $tournament) {
            $this->client->admireTournament($tournament['id']);
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
            if ($quest['isCompleted'] == 1 && $quest['isClaimed'] != 1) {
                $toClaim[] = $quest['id'];
            }
        }

        $this->client->claimDailyQuests($toClaim);

        for ($i = 1; $i <= 4; $i++) {
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
                'pos' => $this->getPepperPosition([], $pepper->getType(), $usedPositions),
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

    public function setupTeamPvE(array $positionConfig = []): void
    {
        $heroes = $this->client->getPeppers();
        $usedPositions = [];
        $positions = array_map(function (Pepper $pepper) use (&$usedPositions, $positionConfig) {
            return [
                'pos' => $this->getPepperPosition($positionConfig, $pepper->getType(), $usedPositions),
                'id' => $pepper->getId()
            ];
        }, $heroes);


        Writer::magenta("Set up adventure team.");
        $this->client->setUpTeamPvE([
            'pepper_positions' => $positions
        ]);
    }

    private function getPepperPosition(array $positionConfig, string $type, array &$usedPositions): int
    {
        if (isset($positionConfig[$type])) {
            return $positionConfig[$type];
        }

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
        sleep(rand(1, 2));
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

            if (!isset($upgradedQueue['Chilli2'])) {
                $heroesStats['Chilli']['atk'] = -1;
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
                        if (!isset($data['data'])) {
                            break;
                        }
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
    public function upgradeHeroDry(array $heroesStats, int $steams, int $minStims = 50): void
    {
        /** @var \PepperAttackBot\Model\Pepper[] $heroes */
        $heroes = $this->client->getPeppers(true);

        $upgradedQueue = [];
        foreach ($heroes as $hero) {
            if (!array_key_exists($hero->getType(), $upgradedQueue)) {
                $upgradedQueue[$hero->getType()] = $hero;
            } else {
                $upgradedQueue['Chilli2'] = $hero;
            }
        }
        $stimsLeft = $steams;

        /**
         * @var string $name
         * @var \PepperAttackBot\Model\Pepper $hero
         */
        foreach ($upgradedQueue as $name => $hero) {

            Writer::magenta("Hero stats: (atk: %s),(def: %s),(crit: %s),(enr: %s),(eva: %s),(vit: %s) ",
                $hero->getStat('atk'),
                $hero->getStat('def'),
                $hero->getStat('crit'),
                $hero->getStat('enr'),
                $hero->getStat('eva'),
                $hero->getStat('vit')
            );

            $hero->clearBoostedCount();

            if (!isset($upgradedQueue['Chilli2'])) {
                $heroesStats['Chilli']['atk'] = -1;
            }

            if (isset($heroesStats[$name])) {
                Writer::green("Upgrade: %s", $name);
                foreach ($heroesStats[$name] as $state => $value) {
                    if ($value > 0 && $hero->getStat($state) >= $value) {
                        continue;
                    }
                    $stateBoostedCnt = 0;
                    while ($stimsLeft >= $minStims) {
                        $stateBoostedCnt++;
                        $stimsLeft = $stimsLeft - match (true) {
                                $hero->getBoostedCount() <= 19 => 1,
                                $hero->getBoostedCount() <= 29 => 2,
                                $hero->getBoostedCount() <= 39 => 3,
                                $hero->getBoostedCount() <= 49 => 5,
                                $hero->getBoostedCount() <= 59 => 8,
                                $hero->getBoostedCount() <= 69 => 13,
                                $hero->getBoostedCount() <= 79 => 21,
                                $hero->getBoostedCount() <= 89 => 34,
                                $hero->getBoostedCount() <= 99 => 55,
                            };
                        $hero->incrementBoostedCount();
                        $hero->setStat($state, $hero->getStat($state) + 2);
                        if ($stimsLeft < $minStims && $value > 0) {
                            Writer::red("Not enough stims!");
                        }
                        if($stimsLeft < $minStims && in_array($name, ['Chilli', 'Chilli2']) && $state == 'atk') {
                            Writer::magenta("Chilly atk stat boosted %d times! (akt: %d)", $stateBoostedCnt, $hero->getStat('atk'));
                        }

                        if ($hero->getBoostedCount() >= 90 || ($value > 0 && $hero->getStat($state) >= $value)) {
                            break;
                        }
                    }
                    Writer::blue("Upgrade hero %s, Stat %d %s, Left stims: %d", $name, $hero->getStat($state), $state, $stimsLeft);
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
        $result = $this->healPeppers();
        if (!$result) {
            exit;
        }

        $battleResult = $this->client->battlePvE($this->account->getMap(), $this->account->getStage());

        if (!isset($battleResult['data'])) {
            print_r($battleResult);
            exit;
        }

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
            sleep(rand(4, 5));
        }

        return $isWin;
    }

    public function battlePvE(?int $maxBattles = 50, bool $toFirstLost = false): bool
    {
        $inventory = $this->client->getInventory();
        $this->healPeppers();

        $isWin = false;
        $battles = 0;
        while ($inventory->getRation() >= 100) {
            $battleResult = $this->client->battlePvE($this->account->getMap(), $this->account->getStage());

            if (!isset($battleResult['data'])) {
                break;
            }

            $actions = count($battleResult['data']['combatActions']);
            $inventory->consumeRation((int)$battleResult['data']['rationCost']);

            if ((int)$battleResult['data']['totalExp'] > 0) {
                $isWin = true;
                Writer::green("Win (%d EXP) (Stage %d).", (int)$battleResult['data']['totalExp'], $this->account->getStage());
                if ($toFirstLost) {
                    $this->account->setStage($this->account->getStage() + 1);
                }
            } else {
                Writer::red("Lost! (Stage %d)", $this->account->getStage());
                if ($toFirstLost) {
                    break;
                }
            }

            $rewards = $battleResult['data']['rewards'];
            foreach ($rewards as $reward) {
                if ($reward['code'] == 'hp_potion') {
                    $inventory->addPotions((int)$reward['value']);
                        Writer::yellow("You got x%d Heal Potions.", (int)$reward['value']);
                }
                if ($reward['code'] == 'stim') {
                        Writer::magenta("You got x%d Stims.", (int)$reward['value']);
                }
            }

            $this->healPeppers();

            $battles++;
            if ($battles >= $maxBattles) {
                break;
            }

            if ($inventory->getRation() >= 100) {
                Writer::white("Left rations: %d. Waiting: %ds.", $inventory->getRation(), $actions * 4);
                if (!$toFirstLost) {
                    sleep($actions * 4);
                } else {
                    sleep(rand(2,3));
                }
            }
        }

        return $isWin;
    }

    private function healPeppers(bool $info = true): bool
    {
        $inventory = $this->client->getInventory();
        Writer::magenta("Potions %d.", $inventory->getPotions());
        if ($inventory->getPotions() == 0) {
            Writer::red("Not enough potions");
            return false;
        }

        /** @var \PepperAttackBot\Model\Pepper[] $peppers */
        $peppers = $this->client->getPeppers();

        if ($inventory->getPotions() > 200) {
            $this->client->healPeppers(array_map(function (Pepper $pepper) {
                return $pepper->getId();
            }, $peppers));
            Writer::yellow("Heal all team!");
        } else {
            foreach ($peppers as $pepper) {
                $healedTimes = 0;
                while ($pepper->getMaxHP() - $pepper->getCurrentHP() >= 30) {
                    $isHealed = $this->client->healPepper($pepper->getId());
                    $healedTimes++;
                    if ($isHealed) {
                        !$info ?? Writer::green("Heal pepper %s (%d/%d HP)", $pepper->getId(), min($pepper->getCurrentHP() + 100, $pepper->getMaxHP()), $pepper->getMaxHP());
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
            !$info ?? Writer::white("Left potions %d", $inventory->getPotions());
        }
        return true;
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

    public function heroInfo(): void
    {
        $peppers = $this->client->getPeppers();

        /** @var Pepper $pepper */
        foreach ($peppers as $pepper) {
            Writer::magenta("Hero %s stats: (boosts: %d), (atk: %s),(def: %s),(crit: %s),(enr: %s),(eva: %s),(vit: %s) ",
                $pepper->getType(),
                $pepper->getBoostedCount(),
                $pepper->getStat('atk'),
                $pepper->getStat('def'),
                $pepper->getStat('crit'),
                $pepper->getStat('enr'),
                $pepper->getStat('eva'),
                $pepper->getStat('vit')
            );
        }
    }
}
