<?php
declare(strict_types=1);

namespace PepperAttackBot\Model;

class Details
{
    public function __construct(
        private int $freeBeers
    ) { }

    public function getFreeBeers(): int
    {
        return $this->freeBeers;
    }
}
