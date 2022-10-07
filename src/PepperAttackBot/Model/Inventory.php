<?php
declare(strict_types=1);

namespace PepperAttackBot\Model;

class Inventory
{
    public function __construct(
        private int $ration,
        private int $stim,
        private int $potions
    )
    {

    }

    public function getRation(): int
    {
        return $this->ration;
    }

    public function getStim(): int
    {
        return $this->stim;
    }

    public function getPotions(): int
    {
        return $this->potions;
    }

    public function consumeRation(int $rations): void
    {
        $this->ration = max($this->ration - $rations, 0);
    }

    public function addPotions(int $potions): void
    {
        $this->potions += $potions;
    }

    public function usePotion(): void
    {
        $this->potions--;
    }
}
