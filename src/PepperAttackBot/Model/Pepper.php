<?php
declare(strict_types=1);

namespace PepperAttackBot\Model;

class Pepper
{
    public function __construct(
        private string $id,
        private int $currentHP,
        private int $maxHP
    )
    {

    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCurrentHP(): int
    {
        return $this->currentHP;
    }

    public function getMaxHP(): int
    {
        return $this->maxHP;
    }

    public function heal(int $heal = 100): void
    {
        $this->currentHP = min($this->currentHP + $heal, $this->maxHP);
    }

    public static function calculateMaxHP(int $vit, int $tempVit):int
    {
        return 100 + (($vit + $tempVit) * 2);
    }
}
