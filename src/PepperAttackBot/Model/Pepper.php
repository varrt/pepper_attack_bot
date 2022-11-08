<?php
declare(strict_types=1);

namespace PepperAttackBot\Model;

class Pepper
{
    public function __construct(
        private string $id,
        private int $currentHP,
        private int $maxHP,
        private string $type,
        private int $boostedAtk,
        private int $boostedDef,
        private int $boostedCrit,
        private int $boostedEva,
        private int $boostedVit,
        private int $boostedEnr,
        private int $boostedCount
    )
    {

    }

    public function getBoostedCount(): int
    {
        return $this->boostedCount;
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

    public function getType(): string
    {
        return $this->type;
    }

    public function heal(int $heal = 100): void
    {
        $this->currentHP = min($this->currentHP + $heal, $this->maxHP);
    }

    public static function calculateMaxHP(int $vit, int $tempVit):int
    {
        return 100 + (($vit + $tempVit) * 2);
    }

    public function incrementBoostedCount(): void
    {
        $this->boostedCount++;
    }

    public function getStat(string $state): int
    {
        switch ($state) {
            case 'atk': return $this->boostedAtk;
            case 'def': return $this->boostedDef;
            case 'crit': return $this->boostedCrit;
            case 'enr': return $this->boostedEnr;
            case 'eva': return $this->boostedEva;
            case 'vit': return $this->boostedVit;
        }
        throw new \Exception("Invalid state");
    }

    public function setStat(string $state, int $value): void
    {
        switch ($state) {
            case 'atk': $this->boostedAtk = $value; break;
            case 'def': $this->boostedDef = $value; break;
            case 'crit': $this->boostedCrit = $value; break;
            case 'enr': $this->boostedEnr = $value; break;
            case 'eva': $this->boostedEva = $value; break;
            case 'vit': $this->boostedVit = $value; break;
        }
    }

    public function clearBoostedCount(): void
    {
        $this->boostedCount = 0;
    }
}
